<?php

declare(strict_types=1);

namespace PERSPEQTIVE\SuluSnippetManagerBundle\DependencyInjection;

use PERSPEQTIVE\SuluSnippetManagerBundle\Admin\ConfiguredParentMenuAdmin;
use PERSPEQTIVE\SuluSnippetManagerBundle\Admin\ConfiguredSnippetAdmin;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Definition;

class RegisterManagersCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {

        if (
            $container->hasDefinition('sulu_admin.view_builder_factory') === false ||
            $container->hasDefinition('sulu_security.security_checker') === false
        ) {
            return;
        }
        $viewBuilderFactory = $container->getDefinition('sulu_admin.view_builder_factory');
        $securityChecker = $container->getDefinition('sulu_security.security_checker');

        $snippetsAsManagers = $container->getParameter('sulu_snippet_manager.snippets');

        foreach ($snippetsAsManagers as $managerConfig) {
            $this->addConfigToContainer($managerConfig, $container, $viewBuilderFactory, $securityChecker);
        }
    }

    private function buildDefinitionForParentMenuItem(array $managerConfig, Definition $securityChecker): Definition {
        $definition = new Definition(
            ConfiguredParentMenuAdmin::class,
            [
                $securityChecker,
                $managerConfig['navigation_title'],
                $managerConfig['order'],
                $managerConfig['icon']
            ]
        );
        $definition->addTag('sulu.context', ['context' => 'admin']);
        $definition->addTag('sulu.admin');
        return $definition;
    }

    private function buildDefinitionForSnippet(array $managerConfig, Definition $viewBuilderFactory, Definition $securityChecker, ?string $parentName = null): Definition
    {
        $definition = new Definition(
            ConfiguredSnippetAdmin::class,
            [
                $viewBuilderFactory,
                $securityChecker,
                $managerConfig['type'],
                $managerConfig['navigation_title'],
                $managerConfig['order'],
                $managerConfig['icon'],
                $parentName
            ]
        );
        $definition->addTag('sulu.context', ['context' => 'admin']);
        $definition->addTag('sulu.admin');
        return $definition;
    }

    public function addConfigToContainer(array $managerConfig, ContainerBuilder $container, Definition $viewBuilderFactory, Definition $securityChecker, ?string $parentName = null): void
    {
        if (isset($managerConfig['children']) && count($managerConfig['children']) > 0) {
            $container->setDefinition(
                'perspeqtive_sulu_snippet_manager.' . $managerConfig['type'],
                $this->buildDefinitionForParentMenuItem($managerConfig, $viewBuilderFactory)
            );
            foreach($managerConfig['children'] as $childConfig) {
                $this->addConfigToContainer($childConfig, $container, $viewBuilderFactory, $securityChecker, $managerConfig['navigation_title']);
            }
            return;
        }
        $container->setDefinition(
            'perspeqtive_sulu_snippet_manager.' . $managerConfig['type'],
            $this->buildDefinitionForSnippet($managerConfig, $viewBuilderFactory, $securityChecker, $parentName)
        );
    }


}