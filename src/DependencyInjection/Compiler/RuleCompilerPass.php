<?php

namespace App\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class RuleCompilerPass
 * @package App\DependencyInjection\Compiler
 */
class RuleCompilerPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $loaderIds = $container->findTaggedServiceIds('app.rule_type');

        $chainLoader = $container->getDefinition('App\Rule\RuleRegistry');

        foreach ($loaderIds as $id => $loader) {
            $chainLoader->addMethodCall('addRule', array(new Reference($id)));
        }
    }
}
