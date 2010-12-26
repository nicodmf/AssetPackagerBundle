<?php

namespace Bundle\Tecbot\AssetPackagerBundle\Templating\Helper;

class StylesheetsHelper extends AssetPackagerHelper
{
    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'stylesheets';
    }

    /**
     * {@inheritDoc}
     */
    protected function renderTag($path, array $attributes = array())
    {
        $atts = '';
        foreach ($attributes as $key => $value) {
            $atts .= ' ' . sprintf('%s="%s"', $key, htmlspecialchars($value, ENT_QUOTES, $this->assetsHelper->getCharset()));
        }

        return sprintf('<link href="%s" rel="stylesheet" type="text/css"%s />', $path, $atts) . "\n";
    }

    /**
     * {@inheritDoc}
     */
    protected function getFormat()
    {
        return 'css';
    }
}