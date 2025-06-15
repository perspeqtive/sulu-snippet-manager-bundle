<?php

declare(strict_types=1);

namespace PERSPEQTIVE\SuluSnippetManagerBundle\DependencyInjection;

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
            $container->setDefinition(
                'perspeqtive_sulu_snippet_manager.' . $managerConfig['type'],
                $this->buildDefinitionForSnippet($managerConfig, $viewBuilderFactory, $securityChecker)
            );
        }
    }

    private function buildDefinitionForSnippet(array $managerConfig, Definition $viewBuilderFactory, Definition $securityChecker): Definition
    {
        $definition = new Definition(
            ConfiguredSnippetAdmin::class,
            [
                $viewBuilderFactory,
                $securityChecker,
                $managerConfig['type'],
                $managerConfig['navigation_title'],
                $managerConfig['order'],
                $managerConfig['icon']
            ]
        );
        $definition->addTag('sulu.context', ['context' => 'admin']);
        $definition->addTag('sulu.admin');
        return $definition;
    }


}