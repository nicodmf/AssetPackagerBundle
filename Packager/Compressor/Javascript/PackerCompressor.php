<?php

namespace Bundle\Tecbot\AssetPackagerBundle\Packager\Compressor\Javascript;

use Bundle\Tecbot\AssetPackagerBundle\Packager\AssetPackager;
use Bundle\Tecbot\AssetPackagerBundle\Packager\Compressor\BaseCompressor;

class PackerCompressor extends BaseCompressor
{
    /**
     * Constructor.
     * 
     * @param Bundle\Tecbot\AssetPackagerBundle\Packager\AssetPackager $packager
     * @param array $options 
     */
    public function __construct(AssetPackager $packager, array $options = array())
    {
        $this->options = array(
            'encoding' => 'Normal',
            'fast_decode' => true,
            'special_chars' => false,
            'path' => $packager->getVendorPath() . DIRECTORY_SEPARATOR . 'packer' . DIRECTORY_SEPARATOR . 'packer.php',
        );

        // check option names
        if ($diff = array_diff(array_keys($options), array_keys($this->options))) {
            throw new \InvalidArgumentException(sprintf('The PackerCompressor does not support the following options: \'%s\'.', implode('\', \'', $diff)));
        }

        $this->options = array_merge($this->options, $options);

        // check vendor path
        if (false === is_file($this->options['path'])) {
            throw new \InvalidArgumentException(sprintf('The path of the packer.php not found (%s)', $this->options['path']));
        }
    }

    /**
     * {@inheritDoc}
     */
    public function compress($content)
    {
        require_once $this->options['path'];

        $packer = new \JavaScriptPacker($content, $this->options['encoding'], $this->options['fast_decode'], $this->options['special_chars']);

        return $packer->pack();
    }
}