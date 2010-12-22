<?php

namespace Bundle\Tecbot\AssetPackagerBundle\Packager\Compressor;

class BaseCompressor implements CompressorInterface
{
    protected $options = array();

    /**
     * {@inheritdoc}
     */
    public function compress($content)
    {
        return $content;
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions()
    {
        return $this->options;
    }
}