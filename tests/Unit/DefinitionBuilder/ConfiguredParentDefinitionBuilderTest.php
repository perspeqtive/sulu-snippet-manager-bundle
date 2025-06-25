<?php

declare(strict_types=1);

namespace PERSPEQTIVE\SuluSnippetManagerBundle\Tests\Unit\DefinitionBuilder;

use PERSPEQTIVE\SuluSnippetManagerBundle\Admin\ConfiguredParentMenuAdmin;
use PERSPEQTIVE\SuluSnippetManagerBundle\DefinitionBuilder\ConfiguredParentDefinitionBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class ConfiguredParentDefinitionBuilderTest extends TestCase
{
    public function testGenerate(): void
    {
        $baseDefinition = new Definition(ConfiguredParentMenuAdmin::class, ['$someClass' => 'testargument']);
        $baseDefinition->setAbstract(true);
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->addDefinitions([
            'perspeqtive_sulu_snippet_manager.admin.configured_parent_menu_admin' => $baseDefinition,
        ]);
        $definitionBuilder = new ConfiguredParentDefinitionBuilder();
        $definition = $definitionBuilder->generate(['navigation_title' => 'Test', 'order' => 10, 'icon' => 'su-icon'], $containerBuilder);

        self::assertTrue($baseDefinition->isAbstract());
        self::assertFalse($definition->isAbstract());

        self::assertSame(ConfiguredParentMenuAdmin::class, $definition->getClass());
        self::assertSame(
            [
                '$someClass' => 'testargument',
                '$navigationTitle' => 'Test',
                '$position' => 10,
                '$icon' => 'su-icon',
            ],
            $definition->getArguments(),
        );
    }
}
