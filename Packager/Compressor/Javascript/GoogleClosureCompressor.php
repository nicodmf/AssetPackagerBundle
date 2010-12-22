<?php

namespace Bundle\Tecbot\AssetPackagerBundle\Packager\Compressor\Javascript;

use Bundle\Tecbot\AssetPackagerBundle\Packager\Packager;
use Bundle\Tecbot\AssetPackagerBundle\Packager\Compressor\BaseCompressor;
use Symfony\Component\Process\Process;

class GoogleClosureCompressor extends BaseCompressor
{
    protected $executable;
    protected $compilationLevels = array('WHITESPACE_ONLY', 'SIMPLE_OPTIMIZATIONS', 'ADVANCED_OPTIMIZATIONS');

    /**
     * Constructor.
     * 
     * @param Bundle\Tecbot\AssetPackagerBundle\Packager\Packager $packager
     * @param array $options 
     */
    public function __construct(Packager $packager, array $options = array())
    {
        $this->options = array(
            'compilation_level' => 'SIMPLE_OPTIMIZATIONS',
            'path' => $packager->getVendorDir() . DIRECTORY_SEPARATOR . 'google-closure-compiler' . DIRECTORY_SEPARATOR . 'closure-compiler.jar',
        );

        // check option names
        if ($diff = array_diff(array_keys($options), array_keys($this->options))) {
            throw new \InvalidArgumentException(sprintf('The GoogleClosureCompressor does not support the following options: \'%s\'.', implode('\', \'', $diff)));
        }

        $this->options = array_merge($this->options, $options);
        $this->options['compilation_level'] = strtoupper($this->options['compilation_level']);

        // check compilation_level
        if (!in_array($this->options['compilation_level'], $this->compilationLevels)) {
            throw new \InvalidArgumentException(sprintf('The GoogleClosureCompressor does not support the following compilation level: \'%s\'. Only %s.', $this->options['compilation_level'], implode(', ', $this->compilationLevels)));
        }

        // check vendor path
        if (!is_file($this->options['path'])) {
            throw new \InvalidArgumentException(sprintf('The path of the closure-compiler not found (%s)', $this->options['path']));
        }

        $this->executable = sprintf('java -jar %s --compilation_level %s', $this->options['path'], $this->options['compilation_level']);
    }

    /**
     * {@inheritdoc}
     */
    public function compress($content)
    {
        $process = new Process($this->executable, null, array(), $content);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException(sprintf('The GoogleClosureCompressor could not compress the package ([%s]: %s).', $process->getExitCode(), $process->getErrorOutput()));
        }

        return $process->getOutput();
    }
}