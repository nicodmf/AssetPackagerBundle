<?php

namespace Bundle\Tecbot\AssetPackagerBundle\Packager\Compressor\Javascript;

use Bundle\Tecbot\AssetPackagerBundle\Packager\Packager;
use Bundle\Tecbot\AssetPackagerBundle\Packager\Compressor\BaseCompressor;
use Symfony\Component\Process\Process;

class YUIJavascriptCompressor extends BaseCompressor
{
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
        $this->options = array(
            'charset' => 'utf-8',
            'line_break' => 5000,
            'munge' => true,
            'optimize' => true,
            'preserve_semicolons' => false,
            'path' => $packager->getVendorDir() . DIRECTORY_SEPARATOR . 'yui-compressor' . DIRECTORY_SEPARATOR . 'yuicompressor.jar',
        );

        // check option names
        if ($diff = array_diff(array_keys($options), array_keys($this->options))) {
            throw new \InvalidArgumentException(sprintf('The YUIJavascriptCompressor does not support the following options: \'%s\'.', implode('\', \'', $diff)));
        }

        $this->options = array_merge($this->options, $options);

        // check vendor path
        if (!is_file($this->options['path'])) {
            throw new \InvalidArgumentException(sprintf('The path of the yui-compressor not found (%s)', $this->options['path']));
        }

        $this->executable = 'java -jar ' . $this->options['path'];
        $this->commandOptions = array('--type js', sprintf('--charset %s', $this->options['charset']), sprintf('--line-break %d', $this->options['line_break']));

        if ($this->options['munge'] === false) {
            $this->commandOptions[] = '--nomunge';
        }

        if ($this->options['optimize'] === false) {
            $this->commandOptions[] = '--disable-optimizations';
        }

        if ($this->options['preserve_semicolons'] === true) {
            $this->commandOptions[] = '--preserve-semi';
        }
    }

    /**
     * {@inheritdoc}
     */
    public function compress($content)
    {
        $process = new Process($this->getCommandLine(), null, array(), $content);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException(sprintf('The YUIJavascriptCompressor could not compress the package ([%s]: %s).', $process->getExitCode(), $process->getErrorOutput()));
        }

        return $process->getOutput();
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