<?php

declare(strict_types=1);

namespace PERSPEQTIVE\SuluSnippetManagerBundle\DefinitionBuilder;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class ConfiguredParentDefinitionBuilder
{
    /**
     * @param array{
     *      navigation_title: string,
     *      type: string,
     *      order?: int,
     *      icon?: string,
     *      children?: array<array-key, array{
     *          navigation_title: string,
     *          type?: string,
     *          order?: int,
     *          icon?: string
     *      }>
     * } $managerConfig
     */
    public function generate(array $managerConfig, ContainerBuilder $container): Definition
    {
        $definition = $container->getDefinition('perspeqtive_sulu_snippet_manager.admin.configured_parent_menu_admin');
        $definition->addArgument($managerConfig['navigation_title']);
        $definition->addArgument($managerConfig['order'] ?? null);
        $definition->addArgument($managerConfig['icon'] ?? null);

        return $definition;
    }
}