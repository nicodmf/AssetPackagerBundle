<?php

namespace Bundle\Tecbot\AssetPackagerBundle\Templating\Helper;

use Bundle\Tecbot\AssetPackagerBundle\Packager\Packager;
use Bundle\Tecbot\AssetPackagerBundle\Packager\Compressor\CompressorInterface;
use Bundle\Tecbot\AssetPackagerBundle\Packager\Dumper\DumperInterface;
use Symfony\Bundle\FrameworkBundle\Templating\Helper\AssetsHelper;
use Symfony\Bundle\FrameworkBundle\Templating\Helper\RouterHelper;
use Symfony\Component\DependencyInjection\Resource\FileResource;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Templating\Helper\Helper;

abstract class AssetPackagerHelper extends Helper
{
    /**
     * @var Symfony\Bundle\FrameworkBundle\Templating\Helper\AssetsHelper 
     */
    protected $assetsHelper;
    /**
     * @var Symfony\Bundle\FrameworkBundle\Templating\Helper\RouterHelper 
     */
    protected $routerHelper;
    /**
     * @var Bundle\Tecbot\AssetPackagerBundle\Packager\Packager
     */
    protected $packager;
    /**
     * @var Bundle\Tecbot\AssetPackagerBundle\Packager\Compressor\CompressorInterface 
     */
    protected $compressor;
    /**
     * @var Bundle\Tecbot\AssetPackagerBundle\Packager\Dumper\DumperInterface 
     */
    protected $dumper;
    /**
     * @var array
     */
    protected $options;
    /**
     * @var array
     */
    protected $packages = array();
    /**
     * @var array
     */
    protected $fileResources = array();

    /**
     * Constructor.
     * 
     * @param Symfony\Bundle\FrameworkBundle\Templating\Helper\AssetsHelper $assetsHelper
     * @param Symfony\Bundle\FrameworkBundle\Templating\Helper\RouterHelper $routerHelper
     * @param Bundle\Tecbot\AssetPackagerBundle\Packager\Packager           $packager
     * @param array                                                         $options 
     */
    public function __construct(AssetsHelper $assetsHelper, RouterHelper $routerHelper, Packager $packager, array $options = array())
    {
        $this->assetsHelper = $assetsHelper;
        $this->routerHelper = $routerHelper;
        $this->packager = $packager;

        $this->options = array(
            'cache_dir' => null,
            'debug' => false,
            'package_assets' => true,
            'compress_assets' => true,
            'embed_assets' => false,
        );

        // check option names
        if ($diff = array_diff(array_keys($options), array_keys($this->options))) {
            throw new \InvalidArgumentException(sprintf('The AssetPackagerHelper does not support the following options: \'%s\'.', implode('\', \'', $diff)));
        }

        $this->options = array_merge($this->options, $options);
    }

    /**
     * Set the compressor.
     * 
     * @param Bundle\Tecbot\AssetPackagerBundle\Packager\Compressor\CompressorInterface  $compressor 
     */
    public function setCompressor(CompressorInterface $compressor)
    {
        $this->compressor = $compressor;
    }

    /**
     * Set the dumper.
     * 
     * @param Bundle\Tecbot\AssetPackagerBundle\Packager\Dumper\DumperInterface $dumper 
     */
    public function setDumper(DumperInterface $dumper)
    {
        $this->dumper = $dumper;
    }

    /**
     * Adds a asset package.
     *
     * @param string $package A asset package
     * @param array  $attributes An array of attributes
     */
    public function add($package, $attributes = array())
    {
        $this->packages[$package] = $attributes;
    }

    /**
     * Returns all asset packages.
     *
     * @return array An array of asset packages to include
     */
    public function get()
    {
        return $this->packages;
    }

    /**
     * Returns HTML representation of the links to packages.
     *
     * @return string The HTML representation of the packages
     */
    public function render()
    {
        $html = '';
        foreach ($this->packages as $package => $attributes) {
            try {
                $files = $this->getPackageFiles($package);
                if (!$this->options['package_assets']) {
                    foreach ($files as $file) {
                        $html .= $this->renderTag($this->assetsHelper->getUrl($file), $attributes);
                    }
                    continue;
                }

                if ($this->options['debug']) {
                    foreach ($files as $file) {
                        $this->fileResources[$package][] = new FileResource($file);
                    }
                }

                $cacheHash = md5($package . implode($files));
                if ($this->needsReload($cacheHash)) {
                    $dump = $this->compress($this->dumper->dump($files));
                    $this->updateCache($package, $cacheHash, $dump);
                }

                $html .= $this->renderTag($this->generatePackageURL($cacheHash), $attributes);
            } catch (\InvalidArgumentException $ex) {
                // No Package found
                $html .= $this->renderTag($this->assetsHelper->getUrl($package), $attributes);
            }
        }

        return $html;
    }

    /**
     * Outputs HTML representation of the links to packages.
     *
     */
    public function output()
    {
        echo $this->render();
    }

    /**
     * Returns a string representation of this helper as HTML.
     *
     * @return string The HTML representation of the packages
     */
    public function __toString()
    {
        return $this->render();
    }

    /**
     * Returns a compressed package
     * 
     * @return sting A compressed package
     */
    protected function compress($content)
    {
        if ($this->options['compress_assets']) {
            $content = $this->compressor->compress($content);
        }

        return $content;
    }

    /**
     * Check the cache file.
     * 
     * @param string $file
     * @return boolean 
     */
    protected function needsReload($file)
    {
        $cacheFile = $this->getCacheFile($file, $this->getExtension());
        if (!file_exists($cacheFile)) {
            return true;
        }

        if (!$this->options['debug']) {
            return false;
        }

        $metadataFile = $this->getCacheFile($file, 'meta');
        if (!file_exists($metadataFile)) {
            return true;
        }

        $time = filemtime($cacheFile);
        $meta = unserialize(file_get_contents($metadataFile));
        foreach ($meta as $resource) {
            if (!$resource->isUptodate($time)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Update the cache file of the package.
     * 
     * @param string $package
     * @param string $file
     * @param string $dump 
     */
    protected function updateCache($package, $file, $dump)
    {
        $this->writeCacheFile($this->getCacheFile($file, $this->getExtension()), $dump);

        if ($this->options['debug']) {
            $this->writeCacheFile($this->getCacheFile($file, 'meta'), serialize($this->resources[$package]));
        }
    }

    /**
     * Write a package cache file to the filesystem .
     * 
     * @throws \RuntimeException When cache file can't be wrote
     */
    protected function writeCacheFile($file, $content)
    {
        $tmpFile = tempnam(dirname($file), basename($file));
        if (false !== @file_put_contents($tmpFile, $content) && @rename($tmpFile, $file)) {
            chmod($file, 0644);

            return;
        }

        throw new \RuntimeException(sprintf('Failed to write cache file "%s".', $file));
    }

    /**
     * Return the path of the cache file from a package.
     *
     * @param  string $file The cache file of the package
     * @param  string $extension The extension of the cache file
     *
     * @return string The path of the cach file
     */
    protected function getCacheFile($file, $extension)
    {
        return $this->options['cache_dir'] . $file . '.' . $extension;
    }

    /**
     * Render the html tag.
     *
     * @return String html tag
     */
    abstract protected function renderTag($path, array $attributes = array());

    /**
     * Returns all file paths for a package.
     * 
     * @return array An array of file paths
     */
    abstract protected function getPackageFiles($package);

    /**
     * Generates a URL for a package.
     *
     * @param  string $file The URL of the package
     *
     * @return string The generated URL
     */
    abstract protected function generatePackageURL($file);

    /**
     * Get a file extension.
     *
     * @return string The file extension
     */
    abstract protected function getExtension();
}