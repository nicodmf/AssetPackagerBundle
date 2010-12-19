<?php

namespace Bundle\Tecbot\AssetPackagerBundle\Packager\Dumper\Javascript;

use Bundle\Tecbot\AssetPackagerBundle\Packager\Dumper\DumperInterface;

class JavascriptDumper implements DumperInterface
{
    /**
     * {@inheritdoc}
     */
    public function dump(array $files)
    {
        $content = '';

        foreach ($files as $file) {
            if (!is_file($file)) {
                throw new \InvalidArgumentException(sprintf('The file "%s" does not exist', $file));
            }

            $content .= file_get_contents($file) . "\n";
        }

        return $content;
    }
}