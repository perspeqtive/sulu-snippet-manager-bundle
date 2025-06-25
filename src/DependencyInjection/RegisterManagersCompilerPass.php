<?php

declare(strict_types=1);

namespace PERSPEQTIVE\SuluSnippetManagerBundle\DependencyInjection;

use PERSPEQTIVE\SuluSnippetManagerBundle\DefinitionBuilder\ConfiguredParentDefinitionBuilder;
use PERSPEQTIVE\SuluSnippetManagerBundle\DefinitionBuilder\ConfiguredSnippetAdminBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use function count;

class RegisterManagersCompilerPass implements CompilerPassInterface
{
    private ConfiguredParentDefinitionBuilder $configuredParentDefinitionBuilder;
    private ConfiguredSnippetAdminBuilder $configuredSnippetAdminDefinitionBuilder;

    public function __construct()
    {
        $this->configuredParentDefinitionBuilder = new ConfiguredParentDefinitionBuilder();
        $this->configuredSnippetAdminDefinitionBuilder = new ConfiguredSnippetAdminBuilder();
    }

    public function process(ContainerBuilder $container): void
    {
        if ($this->shouldBeExecuted($container) === false) {
            return;
        }

        $snippetsAsManagers = $this->fetchConfiguredTree($container);

        foreach ($snippetsAsManagers as $managerConfig) {
            $this->addConfigToContainer($managerConfig, $container);
        }
    }

    private function shouldBeExecuted(ContainerBuilder $container): bool
    {
        return $container->hasDefinition('sulu_admin.view_builder_factory') === true
            && $container->hasDefinition('sulu_security.security_checker') === true
            && $container->hasParameter('sulu_snippet_manager.navigation') === true;
    }

    /**
     * @param array{
     *      navigation_title: string,
     *      type: string,
     *      order: int,
     *      icon: string,
     *      list_view_key: string,
     *      children?: array<array-key, array{
     *          navigation_title: string,
     *          type: string,
     *          order: int,
     *          icon: string,
     *          list_view_key: string,
     *      }>
     * } $managerConfig
     */
    private function addConfigToContainer(array $managerConfig, ContainerBuilder $container, ?string $parentName = null): void
    {
        if (isset($managerConfig['children']) && count($managerConfig['children']) > 0) {
            $this->addDefinitionForDistributorNavigationItem($container, $managerConfig);
            foreach ($managerConfig['children'] as $childConfig) {
                $this->addConfigToContainer($childConfig, $container, $managerConfig['navigation_title']);
            }

            return;
        }
        $this->addDefinitionForDetailViews($container, $managerConfig, $parentName);
    }

    /**
     * @return array{
     *     array{
     *       navigation_title: string,
     *       type: string,
     *       order: int,
     *       icon: string,
     *       list_view_key: string,
     *       children?: array<array-key, array{
     *           navigation_title: string,
     *           type: string,
     *           order: int,
     *           icon: string,
     *           list_view_key: string,
     *       }>
     *     }
     * }
     */
    private function fetchConfiguredTree(ContainerBuilder $container): array
    {
        // @phpstan-ignore-next-line
        return $container->getParameter('sulu_snippet_manager.navigation');
    }

    /**
     * @param array{
     *       navigation_title: string,
     *       type: string,
     *       order: int,
     *       icon: string,
     *       list_view_key: string,
     *       children?: array<array-key, array{
     *           navigation_title: string,
     *           type: string,
     *           order: int,
     *           icon: string,
     *           list_view_key: string,
     *       }>
     * } $managerConfig
     */
    private function addDefinitionForDistributorNavigationItem(ContainerBuilder $container, array $managerConfig): void
    {
        $container->setDefinition(
            'perspeqtive_sulu_snippet_manager.' . $managerConfig['type'],
            $this->configuredParentDefinitionBuilder->generate($managerConfig, $container),
        );
    }

    /**
     * @param array{
     *           navigation_title: string,
     *           type: string,
     *           order: int,
     *           icon: string,
     *           list_view_key: string,
     *           children?: array<array-key, array{
     *               navigation_title: string,
     *               type: string,
     *               order: int,
     *               icon: string,
     *               list_view_key: string,
     *           }>
     * } $managerConfig
     */
    private function addDefinitionForDetailViews(ContainerBuilder $container, array $managerConfig, ?string $parentName): void
    {
        $container->setDefinition(
            'perspeqtive_sulu_snippet_manager.' . $managerConfig['type'],
            $this->configuredSnippetAdminDefinitionBuilder->build($managerConfig, $container, $parentName),
        );
    }
}