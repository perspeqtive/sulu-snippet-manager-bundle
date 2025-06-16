<?php

declare(strict_types=1);

namespace PERSPEQTIVE\SuluSnippetManagerBundle\DefinitionBuilder;

use PERSPEQTIVE\SuluSnippetManagerBundle\Admin\ConfiguredSnippetAdmin;
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
        $viewBuilderFactory = $container->getDefinition('sulu_admin.view_builder_factory');
        $securityChecker = $container->getDefinition('sulu_security.security_checker');
        $formToolbarBuilder = $container->getDefinition('perspeqtive_sulu_snippet_manager.toolbar_actions.form_toolbar_builder');
        $listToolbarBuilder = $container->getDefinition('perspeqtive_sulu_snippet_manager.toolbar_actions.list_toolbar_builder');

        $definition = new Definition(
            ConfiguredSnippetAdmin::class,
            [
                $viewBuilderFactory,
                $securityChecker,
                $formToolbarBuilder,
                $listToolbarBuilder,
                $managerConfig['type'],
                $managerConfig['navigation_title'],
                $managerConfig['order'] ?? null,
                $managerConfig['icon'] ?? null,
                $parentName,
            ],
        );
        $definition->addTag('sulu.context', ['context' => 'admin']);
        $definition->addTag('sulu.admin');

        return $definition;
    }
}
