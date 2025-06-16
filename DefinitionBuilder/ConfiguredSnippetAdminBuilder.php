<?php

declare(strict_types=1);

namespace PERSPEQTIVE\SuluSnippetManagerBundle\DefinitionBuilder;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class ConfiguredSnippetAdminBuilder
{
    /**
     * @param array{
     *      navigation_title: string,
     *      type: string,
     *      order?: int,
     *      icon?: string
     * } $managerConfig
     */
    public function build(array $managerConfig, ContainerBuilder $container, ?string $parentName): Definition
    {
        $definition = $container->getDefinition('perspeqtive_sulu_snippet_manager.admin.configured_snippet_admin');
        $definition->setAbstract(false);
        $definition->addArgument($managerConfig['type']);
        $definition->addArgument($managerConfig['navigation_title']);
        $definition->addArgument($managerConfig['order'] ?? null);
        $definition->addArgument($managerConfig['icon'] ?? null);
        $definition->addArgument($parentName);

        return $definition;
    }
}