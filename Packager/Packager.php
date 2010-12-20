<?php

namespace Bundle\Tecbot\AssetPackagerBundle\Packager;

use Symfony\Component\HttpFoundation\Request;

class Packager
{
    protected $webPath;
    protected $cacheDir;
    protected $vendorDir;
    protected $packages = array();

    /**
     * Constructor.
     * 
     * @param Symfony\Component\HttpFoundation\Request $request
     * @param string $cacheDir
     * @param array $javascriptPackages
     * @param array $stylesheetPackages 
     */
    public function __construct(Request $request, $cacheDir, array $javascriptPackages = array(), array $stylesheetPackages = array())
    {
        $this->webPath = preg_replace('#' . preg_quote(basename($request->server->get('SCRIPT_NAME')), '#') . '$#', '', $request->server->get('SCRIPT_FILENAME'));

        $this->cacheDir = $cacheDir;
        $this->vendorDir = realpath(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'vendor');

        $this->packages['js'] = $this->createPackages($javascriptPackages);
        $this->packages['css'] = $this->createPackages($stylesheetPackages);
    }

    /**
     * Returns the web path
     * 
     * @return string
     */
    public function getWebPath()
    {
        return $this->webPath;
    }

    /**
     * Set the web path
     * 
     * @param string $webPath 
     */
    public function setWebPath($webPath)
    {
        $this->webPath = $webPath;
    }

    /**
     * Returns the cache dir
     * 
     * @return string
     */
    public function getCacheDir()
    {
        return $this->cacheDir;
    }

    /**
     * Set the cache dir
     * 
     * @param string $cacheDir 
     */
    public function setCacheDir($cacheDir)
    {
        $this->cacheDir = $cacheDir;
    }

    /**
     * Returns the vendor dir
     * 
     * @return string
     */
    public function getVendorDir()
    {
        return $this->vendorDir;
    }

    /**
     * Set the vendor dir
     * 
     * @param string $vendorDir 
     */
    public function setVendorDir($vendorDir)
    {
        $this->vendorDir = $vendorDir;
    }

    /**
     * Returns the content of the file
     * 
     * @param string $file
     * @param string $format
     * @return string 
     */
    public function getPackageContent($file, $format)
    {
        if (is_file($this->cacheDir . DIRECTORY_SEPARATOR . $format . DIRECTORY_SEPARATOR . $file . '.' . $format)) {
            return file_get_contents(
                $this->cacheDir . DIRECTORY_SEPARATOR . $format . DIRECTORY_SEPARATOR . $file . '.' . $format
            );
        }

        return false;
    }

    /**
     * Returns the files of the package
     * 
     * @param string $package
     * @param string $extension
     * @return array 
     */
    public function getFilesForPackage($package, $extension)
    {
        if (!isset($this->packages[$extension]) || !isset($this->packages[$extension][$package])) {
            throw new \InvalidArgumentException(sprintf('Package for \'%s\' with \'%s\' extension not found', $package, $extension));
        }

        return $this->packages[$extension][$package]['files'];
    }

    /**
     * Create a package
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
                $parsedPackages[$name]['files'][] = $this->webPath . $file;
            }
        }

        return $parsedPackages;
    }
}