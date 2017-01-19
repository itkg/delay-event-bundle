<?php

namespace Itkg\DelayEventBundle\DependencyInjection;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class ItkgDelayEventExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        $this->loadEventNames($container, $config);
        $this->loadProcessorConfiguration($container, $config);
        $this->loadChannels($container, $config);
    }

    /**
     * @param ContainerBuilder $container
     * @param array            $config
     */
    protected function loadEventNames(ContainerBuilder $container, array $config)
    {
        if (isset($config['events'])) {
            $container->setParameter('itkg_delay_event.event_names', array_keys($config['events']));
            $container->setParameter('itkg_delay_event.event_config', $config['events']);
        }
    }

    /**
     * @param ContainerBuilder $container
     * @param array            $config
     */
    protected function loadProcessorConfiguration(ContainerBuilder $container, array $config)
    {
        $container->setParameter('itkg_delay_event.processor.config', $config['processor']);
    }

    /**
     * @param ContainerBuilder $container
     * @param array            $config
     */
    private function loadChannels(ContainerBuilder $container, array $config)
    {
        $container->setParameter('itkg_delay_event.channels', $config['channels']);
    }
}
