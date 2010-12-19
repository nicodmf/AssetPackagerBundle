<?php

namespace Bundle\Tecbot\AssetPackagerBundle\Packager\Compressor;

interface CompressorInterface
{
    /**
     * Compress a string
     * 
     * @return string
     */
    function compress($content);
}