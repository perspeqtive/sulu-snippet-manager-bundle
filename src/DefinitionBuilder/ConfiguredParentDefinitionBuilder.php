<?php

declare(strict_types=1);

namespace PERSPEQTIVE\SuluSnippetManagerBundle\DefinitionBuilder;

use PERSPEQTIVE\SuluSnippetManagerBundle\DefinitionBuilder\Definition as CloneDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class ConfiguredParentDefinitionBuilder
{
    /**
     * @param array{
     *      navigation_title: string,
     *      type: string,
     *      order: int,
     *      icon: string,
     *      children?: array<array-key, array{
     *          navigation_title: string,
     *          type: string,
     *          order: int,
     *          icon: string
     *      }>
     * } $managerConfig
     */
    public function generate(array $managerConfig, ContainerBuilder $container): Definition
    {
        $definition = CloneDefinition::fromDefinition($container->getDefinition('perspeqtive_sulu_snippet_manager.admin.configured_parent_menu_admin'));
        $definition->setAbstract(false);
        $definition->setArgument('$navigationTitle', $managerConfig['navigation_title']);
        $definition->setArgument('$position', $managerConfig['order']);
        $definition->setArgument('$icon', $managerConfig['icon']);
        $definition->addTag('sulu.admin');

        return $definition;
    }
}
