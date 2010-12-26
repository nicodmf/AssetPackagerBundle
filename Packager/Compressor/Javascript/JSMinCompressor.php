<?php

namespace Bundle\Tecbot\AssetPackagerBundle\Packager\Compressor\Javascript;

use Bundle\Tecbot\AssetPackagerBundle\Packager\AssetPackager;
use Bundle\Tecbot\AssetPackagerBundle\Packager\Compressor\BaseCompressor;

class JSMinCompressor extends BaseCompressor
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
            'path' => $packager->getVendorPath() . DIRECTORY_SEPARATOR . 'jsmin-php' . DIRECTORY_SEPARATOR . 'jsmin.php',
        );

        // check option names
        if ($diff = array_diff(array_keys($options), array_keys($this->options))) {
            throw new \InvalidArgumentException(sprintf('The JSMinCompressor does not support the following options: \'%s\'.', implode('\', \'', $diff)));
        }

        $this->options = array_merge($this->options, $options);

        // check vendor path
        if (false === is_file($this->options['path'])) {
            throw new \InvalidArgumentException(sprintf('The path of the jsmin.php not found (%s)', $this->options['path']));
        }
    }

    /**
     * {@inheritDoc}
     */
    public function compress($content)
    {
        require_once $this->options['path'];

        return \JSMin::minify($content);
    }
}