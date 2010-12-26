<?php

namespace Bundle\Tecbot\AssetPackagerBundle\Packager\Dumper;

class Dumper implements DumperInterface
{
    /**
     * {@inheritDoc}
     */
    public function dump(array $files)
    {
        $content = '';

        foreach ($files as $file) {
            if (false === is_file($file)) {
                throw new \InvalidArgumentException(sprintf('The file "%s" does not exist', $file));
            }

            $content .= file_get_contents($file) . "\n";
        }

        return $content;
    }
}