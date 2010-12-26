<?php

namespace Bundle\Tecbot\AssetPackagerBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Bundle\FrameworkBundle\Command\Command;
use Symfony\Bundle\FrameworkBundle\Util\Filesystem;

class AssetPackagerCompressPackagesCommand extends Command
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this->setName('assetpackager:compress-packages');
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $packager = $this->container->get('assetpackager.packager');

        $packages = $packager->all();

        $output->writeln(sprintf('compress %d javascript packages...', count($packages['js'])));
        foreach ($packages['js'] as $package => $files) {
            $packager->compress($package, 'js');
        }

        $output->writeln(sprintf('compress %d stylesheet packages...', count($packages['css'])));
        foreach ($packages['css'] as $package => $files) {
            $packager->compress($package, 'css');
        }

        $output->writeln('all packages compressed');
    }
}