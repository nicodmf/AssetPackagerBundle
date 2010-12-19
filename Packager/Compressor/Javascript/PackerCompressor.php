<?php

namespace Bundle\Tecbot\AssetPackagerBundle\Packager\Compressor\Javascript;

use Bundle\Tecbot\AssetPackagerBundle\Packager\Compressor\CompressorInterface;

class PackerCompressor implements CompressorInterface
{
    /**
     * {@inheritdoc}
     */
    public function compress($content)
    {
        return $content;
    }
}