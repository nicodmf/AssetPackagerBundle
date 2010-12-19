<?php

namespace Bundle\Tecbot\AssetPackagerBundle\Templating\Helper;

class StylesheetsHelper extends AssetPackagerHelper
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'stylesheets';
    }
    
    /**
     * {@inheritdoc}
     */
    protected function compress($content)
    {
        $content = parent::compress($content);

        if ($this->options['embed_assets']) {
            // TODO: Embed Assets
        }

        return $content;
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

        return sprintf('<link href="%s" rel="stylesheet" type="text/css"%s />', $path, $atts) . "\n";
    }

    /**
     * {@inheritdoc}
     */
    protected function getPackageFiles($package)
    {
        return $this->packager->getFilesForPackage($package, 'css');
    }

    /**
     * {@inheritdoc}
     */
    protected function generatePackageURL($file)
    {
        return $this->routerHelper->generate('_assetpackager_get', array('file' => $file, '_format' => 'css'));
    }

    /**
     * {@inheritdoc}
     */
    protected function getExtension()
    {
        return 'css';
    }
}