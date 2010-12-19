<?php

namespace Bundle\Tecbot\AssetPackagerBundle\Packager\Compressor\Stylesheet;

use Bundle\Tecbot\AssetPackagerBundle\Packager\Compressor\CompressorInterface;

class YUIStylesheetCompressor implements CompressorInterface
{
    /**
     * {@inheritdoc}
     */
    public function compress($content)
    {
        return $content;
    }
}