<?php

namespace Bundle\Tecbot\AssetPackagerBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class TecbotAssetPackagerBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->registerExtension(new DependencyInjection\AssetPackagerExtension());
    }

}