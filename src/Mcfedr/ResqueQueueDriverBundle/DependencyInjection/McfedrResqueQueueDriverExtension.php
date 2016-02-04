<?php

namespace Mcfedr\ResqueQueueDriverBundle\DependencyInjection;

use Mcfedr\ResqueQueueDriverBundle\Manager\ResqueQueueManager;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class McfedrResqueQueueDriverExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');
    }

    /**
     * Allow an extension to prepend the extension configurations.
     *
     * @param ContainerBuilder $container
     */
    public function prepend(ContainerBuilder $container)
    {
        // get all Bundles
        $bundles = $container->getParameter('kernel.bundles');
        // determine if McfedrQueueManagerBundle is registered
        if (isset($bundles['McfedrQueueManagerBundle'])) {
            $container->prependExtensionConfig('mcfedr_queue_manager', [
                'drivers' => [
                    'resque' => [
                        'class' => ResqueQueueManager::class,
                        'options' => [
                            'host' => '127.0.0.1',
                            'port' => 6379,
                            'kernel_options' => [
                                'kernel.root_dir'=> $container->getParameter('kernel.root_dir'),
                                'kernel.environment'=> $container->getParameter('kernel.environment'),
                                'kernel.debug'=> $container->getParameter('kernel.debug')
                            ],
                            'track_status' => false,
                            'default_queue' => 'default'
                        ]
                    ]
                ]
            ]);
        }
    }
}
