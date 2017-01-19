<?php

namespace Itkg\DelayEventBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('itkg_delay_event');
        $rootNode
            ->fixXmlConfig('channel')
            ->children()
                ->scalarNode('command')
                    ->defaultValue('process dynamic command')
                ->end()
                ->arrayNode('mailer')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('from')->defaultValue('from')->end()
                        ->scalarNode('to')->defaultValue('to')->end()
                        ->scalarNode('subject')->defaultValue('subject')->end()
                    ->end()
                ->end()
                ->arrayNode('processor')
                    ->children()
                        ->arrayNode('retry_count')
                            ->children()
                                ->scalarNode('normal')->defaultValue(1)->end()
                                ->scalarNode('critic')->defaultValue(1)->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('events')
                    ->prototype('array')
                        ->children()
                            ->enumNode('type')
                                ->values(['normal', 'critic'])
                                ->defaultValue('normal')
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('channels')
                    ->addDefaultChildrenIfNoneSet('default')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                    ->children()
                        ->booleanNode('dynamic')
                            ->defaultFalse()
                        ->end()
                        ->integerNode('events_limit_per_run')
                            ->defaultNull()
                            ->min(0)
                        ->end()
                        ->integerNode('duration_limit_per_run')
                            ->defaultNull()
                            ->min(0)
                        ->end()
                        ->arrayNode('include')
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('exclude')
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
