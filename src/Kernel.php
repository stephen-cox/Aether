<?php
/**
 * This file is part of the Aether application.
 *
 * (c) Stephen Cox <web@stephencox.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App;

use App\DependencyInjection\IgnoreAnnotationsPass;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

/**
 * Symfony kernel.
 */
class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    /**
     * {@inheritDoc}
     */
    protected function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new IgnoreAnnotationsPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 1000);
    }

    /**
     * {@inheritDoc}
     */
    protected function configureContainer(ContainerConfigurator $container): void
    {
        $container->import('../config/{packages}/*.yaml');
        $container->import('../config/{packages}/'.$this->environment.'/*.yaml');

        if (is_file(\dirname(__DIR__).'/config/services.yaml')) {
            $container->import('../config/services.yaml');
            $container->import('../config/{services}_'.$this->environment.'.yaml');
        } elseif (is_file($path = \dirname(__DIR__).'/config/services.php')) {
            (require $path)($container->withPath($path), $this);
        }

        // Import services from plugins.
        $plugins = scandir(\dirname(__DIR__).'/plugins');
        foreach ($plugins as $plugin) {
            if (!in_array($plugin, ['.', '..']) and is_file(\dirname(__DIR__).'/plugins/'.$plugin.'/config/services.yaml')) {
                $container->import(\dirname(__DIR__).'/plugins/'.$plugin.'/config/services.yaml');
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import('../config/{routes}/'.$this->environment.'/*.yaml');
        $routes->import('../config/{routes}/*.yaml');

        if (is_file(\dirname(__DIR__).'/config/routes.yaml')) {
            $routes->import('../config/routes.yaml');
        } elseif (is_file($path = \dirname(__DIR__).'/config/routes.php')) {
            (require $path)($routes->withPath($path), $this);
        }

        // Import routes from plugins.
        $plugins = scandir(\dirname(__DIR__).'/plugins');
        foreach ($plugins as $plugin) {
            if (!in_array($plugin, ['.', '..']) and is_file(\dirname(__DIR__).'/plugins/'.$plugin.'/config/routes.yaml')) {
                $routes->import(\dirname(__DIR__).'/plugins/'.$plugin.'/config/routes.yaml');
            }
        }
    }
}
