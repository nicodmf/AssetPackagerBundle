<?php

namespace Bundle\Tecbot\AssetPackagerBundle\Templating\Helper;

class JavascriptsHelper extends AssetPackagerHelper
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'javascripts';
    }
    
    /**
     * {@inheritdoc}
     */
    protected function renderTag($path, array $attributes = array())
    {
        $atts = '';
        foreach ($attributes as $key => $value) {
            $atts .= ' ' . sprintf('%s="%s"', $key, htmlspecialchars($value, ENT_QUOTES, $this->charset));
        }

        return sprintf('<script type="text/javascript" src="%s"%s></script>', $path, $atts) . "\n";
    }

    /**
     * {@inheritdoc}
     */
    protected function getPackageFiles($package)
    {
        return $this->packager->getFilesForPackage($package, 'js');
    }

    /**
     * {@inheritdoc}
     */
    protected function generatePackageURL($file)
    {
        return $this->routerHelper->generate('_assetpackager_get', array('file' => $file, '_format' => 'js'));
    }

    /**
     * {@inheritdoc}
     */
    protected function getExtension()
    {
        return 'js';
    }
}