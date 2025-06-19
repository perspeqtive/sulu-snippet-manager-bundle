<?php

declare(strict_types=1);

namespace PERSPEQTIVE\SuluSnippetManagerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

use function count;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('sulu_snippet_manager');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('navigation')
                    ->isRequired()
                    ->useAttributeAsKey('name')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('navigation_title')->isRequired()->end()
                            ->scalarNode('type')->defaultNull()->end()
                            ->scalarNode('list_view_key')->defaultValue('snippets')->end()
                            ->integerNode('order')->defaultValue(0)->end()
                            ->scalarNode('icon')->defaultValue('su-snippet')->end()
                            ->append($this->addChildrenNode())
                        ->end()
                        ->validate()
                            ->ifTrue(function ($config) {
                                return $this->isInvalidNavigationEntry($config);
                            })
                            ->thenInvalid("The 'type' must be defined when no children are given.")
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }

    private function addChildrenNode(): ArrayNodeDefinition
    {
        $node = new ArrayNodeDefinition('children');

        $node
            ->useAttributeAsKey('name')
            ->arrayPrototype()
                ->children()
                    ->scalarNode('navigation_title')->isRequired()->end()
                    ->scalarNode('type')->isRequired()->end()
                    ->scalarNode('list_view_key')->defaultValue('snippets')->end()
                    ->integerNode('order')->defaultValue(0)->end()
                    ->scalarNode('icon')->defaultValue('su-snippet')->end()
                ->end()
            ->end();

        return $node;
    }

    private function isInvalidNavigationEntry(array $config): bool
    {
        $hasChildren = isset($config['children']) && count($config['children']) > 0;
        $hasType = isset($config['type']);

        return !$hasChildren && !$hasType;
    }
}
