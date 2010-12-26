<?php

namespace Bundle\Tecbot\AssetPackagerBundle\Templating\Helper;

use Bundle\Tecbot\AssetPackagerBundle\Packager\AssetPackager;
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
     * @var Bundle\Tecbot\AssetPackagerBundle\Packager\AssetPackager
     */
    protected $packager;
    /**
     * @var array
     */
    protected $options;
    /**
     * @var array 
     */
    protected $packages = array();

    /**
     * Constructor.
     * 
     * @param Symfony\Bundle\FrameworkBundle\Templating\Helper\AssetsHelper $assetsHelper
     * @param Symfony\Bundle\FrameworkBundle\Templating\Helper\RouterHelper $routerHelper
     * @param Bundle\Tecbot\AssetPackagerBundle\Packager\AssetPackager      $packager
     * @param array                                                         $options 
     */
    public function __construct(AssetsHelper $assetsHelper, RouterHelper $routerHelper, AssetPackager $packager, array $options = array())
    {
        $this->assetsHelper = $assetsHelper;
        $this->routerHelper = $routerHelper;
        $this->packager = $packager;

        $this->options = array(
            'package_assets' => true,
            'compress_assets' => true,
        );

        // check option names
        if ($diff = array_diff(array_keys($options), array_keys($this->options))) {
            throw new \InvalidArgumentException(sprintf('The AssetPackagerHelper does not support the following options: \'%s\'.', implode('\', \'', $diff)));
        }

        $this->options = array_merge($this->options, $options);
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
                if (false === $this->options['package_assets']) {
                    $packageData = $this->packager->get($package, $this->getFormat());
                    foreach ($packageData['paths'] as $file) {
                        $html .= $this->renderTag($this->assetsHelper->getUrl($file), $attributes);
                    }
                    continue;
                }

                $html .= $this->renderTag($this->generatePackageURL($this->packager->compress($package, $this->getFormat())), $attributes);
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
     * Generates a URL for a package.
     *
     * @param  string $file The URL of the package
     *
     * @return string The generated URL
     */
    protected function generatePackageURL($file)
    {
        return $this->routerHelper->generate('_assetpackager_get', array('file' => $file, '_format' => $this->getFormat()));
    }

    /**
     * Render the html tag.
     *
     * @return String html tag
     */
    abstract protected function renderTag($path, array $attributes = array());

    /**
     * Get a file format.
     *
     * @return string The file format
     */
    abstract protected function getFormat();
}