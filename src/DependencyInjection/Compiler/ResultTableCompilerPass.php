<?php

namespace App\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class ResultTableCompilerPass
 * @package App\DependencyInjection\Compiler
 */
class ResultTableCompilerPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $loaderIds = $container->findTaggedServiceIds('app.table_type');

        $chainLoader = $container->getDefinition('App\LegacyService\ResultTable\ResultTableRegistry');

        foreach ($loaderIds as $id => $loader) {
            $chainLoader->addMethodCall('addTable', array(new Reference($id)));
        }
    }
}
