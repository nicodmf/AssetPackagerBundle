<?php

namespace Bundle\Tecbot\AssetPackagerBundle\Packager\Compressor\Javascript;

use Bundle\Tecbot\AssetPackagerBundle\Packager\Compressor\CompressorInterface;

class JSMinCompressor implements CompressorInterface
{
    /**
     * {@inheritdoc}
     */
    public function compress($content)
    {
        return $content;
    }
}
