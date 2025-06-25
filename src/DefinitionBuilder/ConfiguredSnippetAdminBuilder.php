<?php

declare(strict_types=1);

namespace PERSPEQTIVE\SuluSnippetManagerBundle\DefinitionBuilder;

use PERSPEQTIVE\SuluSnippetManagerBundle\DefinitionBuilder\Definition as CloneDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class ConfiguredSnippetAdminBuilder
{
    /**
     * @param array{
     *      navigation_title: string,
     *      type: string,
     *      order: int,
     *      icon: string,
     *      list_view_key: string,
     * } $managerConfig
     */
    public function build(array $managerConfig, ContainerBuilder $container, ?string $parentNavigation): Definition
    {
        $definition = CloneDefinition::fromDefinition($container->getDefinition('perspeqtive_sulu_snippet_manager.admin.configured_snippet_admin'));
        $definition->setAbstract(false);
        $definition->setArgument('$snippetType', $managerConfig['type']);
        $definition->setArgument('$navigationTitle', $managerConfig['navigation_title']);
        $definition->setArgument('$listViewKey', $managerConfig['list_view_key']);
        $definition->setArgument('$position', $managerConfig['order']);
        $definition->setArgument('$icon', $managerConfig['icon']);
        $definition->setArgument('$parentNavigation', $parentNavigation);
        $definition->addTag('sulu.admin');

        return $definition;
    }
}