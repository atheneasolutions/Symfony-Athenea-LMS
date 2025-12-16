<?php

namespace Athenea\LMS\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('athenea_la_meva_salut');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->scalarNode('app_url')->defaultValue('https://default-url.com')->end()
                ->scalarNode('form_url')->defaultValue("")->end()
                ->scalarNode('app_logo')->defaultValue('/images/default-logo.png')->end()
                ->scalarNode('app_name')->defaultValue('My App Name')->end()
                ->scalarNode('lms_public_key')->defaultValue(null)->end()
                ->arrayNode('lms_public_keys')->scalarPrototype()->end()->defaultValue([])->end()
                ->scalarNode('app_public_key')->defaultValue('app_public_key')->end()
                ->scalarNode('app_private_key')->defaultValue('app_private_key')->end()
                ->scalarNode('app_private_key')->defaultValue('app_private_key')->end()
                ->booleanNode('verify_lms_signature')->defaultTrue()->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
