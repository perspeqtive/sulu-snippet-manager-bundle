<?php

declare(strict_types=1);

namespace PERSPEQTIVE\SuluSnippetManagerBundle\Tests\Unit\DefinitionBuilder;

use PERSPEQTIVE\SuluSnippetManagerBundle\Admin\ConfiguredSnippetAdmin;
use PERSPEQTIVE\SuluSnippetManagerBundle\DefinitionBuilder\ConfiguredSnippetAdminBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class ConfiguredSnippetAdminBuilderTest extends TestCase
{
    public function testGenerate(): void
    {
        $baseDefinition = new Definition(ConfiguredSnippetAdmin::class, ['$someClass' => 'testargument']);
        $baseDefinition->setAbstract(true);
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->addDefinitions([
            'perspeqtive_sulu_snippet_manager.admin.configured_snippet_admin' => $baseDefinition,
        ]);
        $definitionBuilder = new ConfiguredSnippetAdminBuilder();
        $definition = $definitionBuilder->build(['navigation_title' => 'Test', 'type' => 'shop', 'order' => 10, 'icon' => 'su-icon', 'list_view_key' => 'snippets'], $containerBuilder, 'testparent');

        self::assertTrue($baseDefinition->isAbstract());
        self::assertFalse($definition->isAbstract());

        self::assertSame(ConfiguredSnippetAdmin::class, $definition->getClass());
        self::assertSame(
            [
                '$someClass' => 'testargument',
                '$snippetType' => 'shop',
                '$navigationTitle' => 'Test',
                '$listViewKey' => 'snippets',
                '$position' => 10,
                '$icon' => 'su-icon',
                '$parentNavigation' => 'testparent',
            ],
            $definition->getArguments(),
        );
    }
}