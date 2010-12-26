<?php

namespace Bundle\Tecbot\AssetPackagerBundle\Packager\Compressor\Stylesheet;

use Bundle\Tecbot\AssetPackagerBundle\Packager\AssetPackager;
use Bundle\Tecbot\AssetPackagerBundle\Packager\Compressor\BaseCompressor;

class CSSMinCompressor extends BaseCompressor
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
            'remove-empty-blocks'     => true,
            'remove-empty-rulesets'   => true,
            'remove-last-semicolons'  => true,
            'convert-css3-properties' => false,
            'convert-color-values'    => false,
            'compress-color-values'   => false,
            'compress-unit-values'    => false,
            'emulate-css3-variables'  => true,
            'path' => $packager->getVendorPath() . DIRECTORY_SEPARATOR . 'cssmin' . DIRECTORY_SEPARATOR . 'cssmin.php',
        );

        // check option names
        if ($diff = array_diff(array_keys($options), array_keys($this->options))) {
            throw new \InvalidArgumentException(sprintf('The CSSMinCompressor does not support the following options: \'%s\'.', implode('\', \'', $diff)));
        }

        $this->options = array_merge($this->options, $options);

        // check vendor path
        if (false === is_file($this->options['path'])) {
            throw new \InvalidArgumentException(sprintf('The path of the cssmin.php not found (%s)', $this->options['path']));
        }
    }
    
    /**
     * {@inheritDoc}
     */
    public function compress($content)
    {
        require_once $this->options['path'];
        
        return \CSSMin::minify($content, $this->options);
    }
}