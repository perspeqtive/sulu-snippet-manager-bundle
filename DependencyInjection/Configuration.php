<?php

declare(strict_types=1);

namespace PERSPEQTIVE\SuluSnippetManagerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('sulu_snippet_manager');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('snippets')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('navigation_title')
                                ->isRequired()
                            ->end()
                            ->scalarNode('type')
                                ->isRequired()
                            ->end()
                            ->integerNode('order')
                                ->defaultValue(40)
                            ->end()
                            ->scalarNode('icon')
                                ->defaultValue('su-snippet')
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}