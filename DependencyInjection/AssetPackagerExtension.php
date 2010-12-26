<?php

namespace Bundle\Tecbot\AssetPackagerBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class AssetPackagerExtension extends Extension
{
    /**
     * Loads the AssetPackager configuration.
     *
     * @param array $config  An array of configuration settings
     * @param Symfony\Component\DependencyInjection\ContainerBuilder $container A ContainerBuilder instance
     */
    public function configLoad($config, ContainerBuilder $container)
    {
        $this->loadDefaults($config, $container);
    }

    /**
     * Loads the default configuration.
     *
     * @param array $config An array of configuration settings
     * @param Symfony\Component\DependencyInjection\ContainerBuilder $container A ContainerBuilder instance
     */
    protected function loadDefaults(array $config, ContainerBuilder $container)
    {
        if (!$container->hasDefinition('assetpackager')) {
            $loader = new XmlFileLoader($container, __DIR__ . '/../Resources/config');
            $loader->load('assetpackager.xml');
            $loader->load('controller.xml');
            $loader->load('templating.xml');
        }
        
        // Allow these application configuration options to override the defaults
        $options = array(
            'assets_path',
            'cache_path',
            'compress_assets',
            'package_assets',
        );

        foreach ($options as $key) {
            if (isset($config[$key])) {
                $container->setParameter('assetpackager.options.' . $key, $config[$key]);
            }

            $nKey = str_replace('_', '-', $key);
            if (isset($config[$nKey])) {
                $container->setParameter('assetpackager.options.' . $key, $config[$nKey]);
            }
        }

        if (isset($config['js']['compressor']) && $container->has('assetpackager.compressor.javascript.' . strtolower($config['js']['compressor']))) {
            $config['js']['compressor'] = strtolower($config['js']['compressor']);
        } else {
            $config['js']['compressor'] = 'jsmin';
        }

        $container->setAlias('assetpackager.compressor.javascript', 'assetpackager.compressor.javascript.' . $config['js']['compressor']);

        if (isset($config['css']['compressor']) && $container->has('assetpackager.compressor.stylesheet.' . strtolower($config['css']['compressor']))) {
            $config['css']['compressor'] = strtolower($config['css']['compressor']);
        } else {
            $config['css']['compressor'] = 'cssmin';
        }

        $container->setAlias('assetpackager.compressor.stylesheet', 'assetpackager.compressor.stylesheet.' . $config['css']['compressor']);
        
        if (isset($config['js']['options'])) {
            $container->setParameter('assetpackager.compressor.javascript.options', $config['js']['options']);
        }

        if (isset($config['css']['options'])) {
            $container->setParameter('assetpackager.compressor.stylesheet.options', $config['css']['options']);
        }


        if (isset($config['js']['packages'])) {
            $container->setParameter('assetpackager.packages.javascript', $config['js']['packages']);
        }

        if (isset($config['css']['packages'])) {
            $container->setParameter('assetpackager.packages.stylesheet', $config['css']['packages']);
        }
    }

    /**
     * Returns the base path for the XSD files.
     *
     * @return string The XSD base path
     */
    public function getXsdValidationBasePath()
    {
        return __DIR__ . '/../Resources/config/schema';
    }

    public function getNamespace()
    {
        return 'http://tecbot.de/schema/dic/assetpackager';
    }

    public function getAlias()
    {
        return 'assetpackager';
    }
}