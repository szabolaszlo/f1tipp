<?php

namespace App\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class CalculatorCompilerPass
 * @package App\DependencyInjection\Compiler
 */
class CalculatorCompilerPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $loaderIds = $container->findTaggedServiceIds('app.calculator_type');

        $chainLoader = $container->getDefinition('app.calculator.registry');

        foreach ($loaderIds as $id => $loader) {
            $chainLoader->addMethodCall('addCalculator', array(new Reference($id)));
        }
    }
}
