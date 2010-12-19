<?php

namespace Bundle\Tecbot\AssetPackagerBundle\Packager\Compressor\Javascript;

use Bundle\Tecbot\AssetPackagerBundle\Packager\Compressor\CompressorInterface;

class YUIJavascriptCompressor implements CompressorInterface
{
    /**
     * {@inheritdoc}
     */
    public function compress($content)
    {
        return $content;
    }
}