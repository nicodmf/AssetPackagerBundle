<?php

namespace Bundle\Tecbot\AssetPackagerBundle\Packager\Compressor\Stylesheet;

use Bundle\Tecbot\AssetPackagerBundle\Packager\Packager;
use Bundle\Tecbot\AssetPackagerBundle\Packager\Compressor\CompressorInterface;
use Symfony\Component\Process\Process;

class YUIStylesheetCompressor implements CompressorInterface
{
    protected $packager;
    protected $options;
    protected $executable;
    protected $commandOptions;

    /**
     * Constructor.
     * 
     * @param Bundle\Tecbot\AssetPackagerBundle\Packager\Packager $packager
     * @param array $options 
     */
    public function __construct(Packager $packager, array $options = array())
    {
        $this->packager = $packager;

        $this->options = array(
            'charset' => 'utf-8',
            'line_break' => 0,
            'path' => $this->packager->getVendorDir() . DIRECTORY_SEPARATOR . 'yui-compressor' . DIRECTORY_SEPARATOR . 'yuicompressor.jar',
        );

        // check option names
        if ($diff = array_diff(array_keys($options), array_keys($this->options))) {
            throw new \InvalidArgumentException(sprintf('The YUIStylesheetCompressor does not support the following options: \'%s\'.', implode('\', \'', $diff)));
        }

        $this->options = array_merge($this->options, $options);

        if (!is_file($this->options['path'])) {
            throw new \InvalidArgumentException(sprintf('The path of the yui-compressor not found (%s)', $this->options['path']));
        }

        $this->executable = 'java -jar ' . $this->options['path'];
        $this->commandOptions = array('--type css', sprintf('--charset %s', $this->options['charset']), sprintf('--line-break %d', $this->options['line_break']));
    }

    /**
     * {@inheritdoc}
     */
    public function compress($content)
    {
        $process = new Process($this->getCommandLine(), null, array(), $content);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException(sprintf('The YUIStylesheetCompressor could not compress the package ([%s]: %s).', $process->getExitCode(), $process->getErrorOutput()));
        }

        $content = $process->getOutput();

        return $content;
    }

    /**
     * Returns the command line for the compress process
     * 
     * @return string
     */
    protected function getCommandLine()
    {
        return sprintf('%s %s', $this->executable, implode(' ', $this->commandOptions));
    }
}