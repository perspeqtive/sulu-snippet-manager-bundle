<?php

declare(strict_types=1);

namespace PERSPEQTIVE\SuluSnippetManagerBundle\DefinitionBuilder;

use PERSPEQTIVE\SuluSnippetManagerBundle\Admin\ConfiguredParentMenuAdmin;
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
        $securityChecker = $container->getDefinition('sulu_security.security_checker');
        $definition = new Definition(
            ConfiguredParentMenuAdmin::class,
            [
                $securityChecker,
                $managerConfig['navigation_title'],
                $managerConfig['order'] ?? null,
                $managerConfig['icon'] ?? null,
            ],
        );
        $definition->addTag('sulu.context', ['context' => 'admin']);
        $definition->addTag('sulu.admin');

        return $definition;
    }
}
