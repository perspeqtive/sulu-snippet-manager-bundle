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

        $rootNode->children()
            ->arrayNode('snippets')
                ->prototype('scalar')->end()
            ->end()
        ->end();

        return $treeBuilder;
    }
}