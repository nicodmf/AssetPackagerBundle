<?php

namespace Bundle\Tecbot\AssetPackagerBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Bundle\FrameworkBundle\Command\Command;
use Symfony\Bundle\FrameworkBundle\Util\Filesystem;

class ClearCacheCommand extends Command
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this->setName('assetpackager:clear-cache');
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filesystem = new Filesystem();

        $finder = new Finder();
        
        $cacheDir = $this->container->getParameter('kernel.cache_dir') . DIRECTORY_SEPARATOR . $this->container->getParameter('assetpackager.options.cache_dir');
        $finder = $finder->files()->in($cacheDir);

        $count = 0;

        foreach ($finder as $file) {
            $filesystem->remove($file);
            $count++;
        }

        $output->writeln("removed $count cache file(s)");
    }
}