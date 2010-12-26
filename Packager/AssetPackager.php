<?php

namespace Bundle\Tecbot\AssetPackagerBundle\Packager;

use Bundle\Tecbot\AssetPackagerBundle\Packager\Compressor\CompressorInterface;
use Bundle\Tecbot\AssetPackagerBundle\Packager\Dumper\DumperInterface;
use Symfony\Component\DependencyInjection\Resource\FileResource;
use Symfony\Component\HttpFoundation\Request;

class AssetPackager
{
    /**
     * @var Bundle\Tecbot\AssetPackagerBundle\Packager\Compressor\CompressorInterface
     */
    protected $javascriptCompressor;
    /**
     * @var Bundle\Tecbot\AssetPackagerBundle\Packager\Compressor\CompressorInterface
     */
    protected $stylesheetCompressor;
    /**
     * @var Bundle\Tecbot\AssetPackagerBundle\Packager\Dumper\DumperInterface
     */
    protected $dumper;
    /**
     * @var array
     */
    protected $options;
    /**
     * @var string 
     */
    protected $vendorPath;
    /**
     * @var array
     */
    protected $packages;
    /**
     * @var array
     */
    protected $fileResources = array();

    /**
     * Constructor.
     * 
     * @param array $options
     * @param array $packages 
     */
    public function __construct(array $options = array(), array $packages = array())
    {
        $this->options = array(
            'assets_path' => null,
            'cache_path' => null,
            'compress_assets' => true,
            'package_assets' => true,
            'debug' => false,
        );

        // check option names
        if ($diff = array_diff(array_keys($options), array_keys($this->options))) {
            throw new \InvalidArgumentException(sprintf('The AssetPackager does not support the following options: \'%s\'.', implode('\', \'', $diff)));
        }

        $this->options = array_merge($this->options, $options);
        $this->vendorPath = realpath(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'vendor');

        $this->createCacheDirectory();

        $this->packages = array_merge(array(
            'js' => array(),
            'css' => array(),
            ), $packages);

        $this->packages['js'] = $this->createPackages($this->packages['js']);
        $this->packages['css'] = $this->createPackages($this->packages['css']);
    }

    /**
     * Returns all packages.
     * 
     * @return array
     */
    public function all()
    {
        return $this->packages;
    }

    /**
     * Returns a package.
     * 
     * @param string $package
     * @param string $format
     * 
     * @return array 
     */
    public function get($package, $format)
    {
        if (false === isset($this->packages[$format]) || false === isset($this->packages[$format][$package])) {
            throw new \InvalidArgumentException(sprintf('Package for \'%s\' with format \'%s\' not found', $package, $format));
        }

        return $this->packages[$format][$package];
    }

    /**
     * Compress a package and returns the hash of the cache file.
     * 
     * @param string $package
     * @param string $format
     * 
     * @return string
     */
    public function compress($package, $format)
    {
        $packageData = $this->get($package, $format);
        if ($this->options['debug']) {
            foreach ($packageData['files'] as $file) {
                $this->fileResources[$file][] = new FileResource($file);
            }
        }

        $hash = md5($package . implode($packageData['files']));
        if ($this->needsReload($hash, $format)) {
            $dump = $this->dumper->dump($packageData['files']);
            
            if ($this->options['compress_assets']) {
                $dump = $this->getCompressor($format)->compress($dump);
            }

            $this->updateCache($file, $format, $hash, $dump);
        }

        return $hash;
    }

    /**
     * Returns the compressor for a format.
     * 
     * @param type $format
     * 
     * @return Bundle\Tecbot\AssetPackagerBundle\Packager\Compressor\CompressorInterface
     */
    public function getCompressor($format)
    {
        switch ($format) {
            case 'js':
                return $this->javascriptCompressor;
            case 'css':
                return $this->stylesheetCompressor;
        }

        throw new \InvalidArgumentException(sprintf('Compressor for format \'%s\' not found', $format));
    }

    /**
     * Returns the content of a package.
     * 
     * @param string $hash
     * @param string $format
     * 
     * @return string
     */
    public function getContent($hash, $format)
    {
        $file = $this->getCacheFile($hash, $format);
        if (false === is_file($file)) {
            return false;
        }

        return file_get_contents($file);
    }

    /**
     * Set the javascript compressor.
     * 
     * @param Bundle\Tecbot\AssetPackagerBundle\Packager\Compressor\CompressorInterface $javascriptCompressor 
     */
    public function setJavascriptCompressor(CompressorInterface $javascriptCompressor)
    {
        $this->javascriptCompressor = $javascriptCompressor;
    }

    /**
     * Set the stylesheet compressor.
     * 
     * @param Bundle\Tecbot\AssetPackagerBundle\Packager\Compressor\CompressorInterface $stylesheetCompressor 
     */
    public function setStylesheetCompressor(CompressorInterface $stylesheetCompressor)
    {
        $this->stylesheetCompressor = $stylesheetCompressor;
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
     * Returns the vendor path.
     * 
     * @return string 
     */
    public function getVendorPath()
    {
        return $this->vendorPath;
    }

    /**
     * Set the vendor path.
     * 
     * @param string $vendorPath 
     */
    public function setVendorPath($vendorPath)
    {
        $this->vendorPath = $vendorPath;
    }

    /**
     * Create all packages.
     * 
     * @param array $packages
     * @return array 
     */
    protected function createPackages(array $packages)
    {
        $parsedPackages = array();
        foreach ($packages as $name => $files) {
            $tempFiles = array_unique((!is_array($files)) ? array() : $files);
            foreach ($tempFiles as $file) {
                $parsedPackages[$name]['paths'][] = $file;
                $parsedPackages[$name]['files'][] = $this->options['assets_path'] . DIRECTORY_SEPARATOR . $file;
            }
        }

        return $parsedPackages;
    }

    /**
     * Check the cache file.
     * 
     * @param string $hash
     * @return boolean 
     */
    protected function needsReload($hash, $format)
    {
        $file = $this->getCacheFile($hash, $format);
        if (false === file_exists($file)) {
            return true;
        }

        if ($this->options['debug'] === false) {
            return false;
        }

        $metadataFile = $this->getCacheFile($hash, 'meta');
        if (false === file_exists($metadataFile)) {
            return true;
        }

        $metadata = unserialize(file_get_contents($metadataFile));

        if ($diff = array_diff_assoc($metadata['packager_options'], $this->options)) {
            return true;
        }
        
        $compressor = $this->getCompressor($format);
        if ($metadata['compressor'] !== get_class($compressor)) {
            return true;
        }
                
        if ($diff = array_diff_assoc($metadata['compressor_options'], $compressor->getOptions())) {
            return true;
        }

        $time = filemtime($file);
        foreach ($metadata['files'] as $resource) {
            if (false === $resource->isUptodate($time)) {
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
    protected function updateCache($package, $format, $file, $dump)
    {
        $this->writeCacheFile($this->getCacheFile($file, $format), $dump);

        $compressor = $this->getCompressor($format);
        if ($this->options['debug']) {
            $cacheData = array(
                'packager_options' => $this->options,
                'compressor' => get_class($compressor),
                'compressor_options' => $compressor->getOptions(),
                'files' => $this->fileResources[$package],
            );
            $this->writeCacheFile($this->getCacheFile($file, 'meta'), serialize($cacheData));
        }
    }

    /**
     * Write a package cache file to the filesystem.
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
     * @param  string $format The format of the cache file
     *
     * @return string The path of the cach file
     */
    protected function getCacheFile($file, $format)
    {
        return $this->options['cache_path'] . DIRECTORY_SEPARATOR . $file . '.' . $format;
    }

    /**
     * Create the AssetPackager cache directory.
     */
    protected function createCacheDirectory()
    {
        if (false === is_dir($this->options['cache_path'])) {
            if (false === @mkdir($this->options['cache_path'], 0777, true)) {
                throw new \RuntimeException(sprintf('Unable to create the AssetPackager cache directory (%s)', dirname($this->options['cache_path'])));
            }
        } elseif (false === is_writable($this->options['cache_path'])) {
            throw new \RuntimeException(sprintf('Unable to write in the AssetPackager cache directory (%s)', $this->options['cache_path']));
        }
    }
}