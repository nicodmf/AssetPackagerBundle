<?php

namespace Bundle\Tecbot\AssetPackagerBundle\Packager\Compressor;

class BaseCompressor implements CompressorInterface
{
    protected $options = array();

    /**
     * {@inheritDoc}
     */
    public function compress($content)
    {
        return $content;
    }

    /**
     * {@inheritDoc}
     */
    public function getOptions()
    {
        return $this->options;
    }
}