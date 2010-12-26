<?php

namespace Bundle\Tecbot\AssetPackagerBundle\Templating\Helper;

class JavascriptsHelper extends AssetPackagerHelper
{
    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'javascripts';
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

        return sprintf('<script type="text/javascript" src="%s"%s></script>', $path, $atts) . "\n";
    }

    /**
     * {@inheritDoc}
     */
    protected function getFormat()
    {
        return 'js';
    }
}