<?php

namespace App;

use App\DependencyInjection\Compiler\CalculatorCompilerPass;
use App\DependencyInjection\Compiler\ResultTableCompilerPass;
use App\DependencyInjection\Compiler\RuleCompilerPass;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    private const CONFIG_EXTS = '.{php,xml,yaml,yml}';

    protected function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new CalculatorCompilerPass());
        $container->addCompilerPass(new RuleCompilerPass());
        $container->addCompilerPass(new ResultTableCompilerPass());
    }

    public function getProjectDir(): string
    {
        return \dirname(__DIR__);
    }

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $confDir = $this->getProjectDir() . '/config';

        $container->import($confDir . '/{packages}/*' . self::CONFIG_EXTS);
        $container->import($confDir . '/{packages}/' . $this->environment . '/**/*' . self::CONFIG_EXTS);
        $container->import($confDir . '/{services}' . self::CONFIG_EXTS);
        $container->import($confDir . '/{services}_' . $this->environment . self::CONFIG_EXTS);
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $confDir = $this->getProjectDir() . '/config';

        $routes->import($confDir . '/{routes}/' . $this->environment . '/**/*' . self::CONFIG_EXTS);
        $routes->import($confDir . '/{routes}/*' . self::CONFIG_EXTS);
        $routes->import($confDir . '/{routes}' . self::CONFIG_EXTS);
    }
}
