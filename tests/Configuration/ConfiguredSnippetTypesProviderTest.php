<?php

declare(strict_types=1);

namespace PERSPEQTIVE\SuluSnippetManagerBundle\Tests\Configuration;

use PERSPEQTIVE\SuluSnippetManagerBundle\Configuration\ConfiguredSnippetTypesProvider;
use PHPUnit\Framework\TestCase;

class ConfiguredSnippetTypesProviderTest extends TestCase
{
    public function testGetConfiguredSnippetTypesOnEmptyConfig(): void
    {
        $provider = new ConfiguredSnippetTypesProvider([]);
        $result = $provider->getConfiguredSnippetTypes();

        self::assertSame([], $result);
    }

    public function testGetConfiguredSnippetTypes(): void
    {
        $expected = ['service', 'shop', 'subsettings', 'settings'];

        $provider = new ConfiguredSnippetTypesProvider([
            [
                'type' => 'main',
                'children' => [
                    ['type' => 'service'],
                    ['type' => 'shop'],
                ],
            ],
            [
                'type' => 'subsettings',
                'children' => [],
            ],
            [
                'type' => 'settings',
            ],
        ]);

        self::assertSame($expected, $provider->getConfiguredSnippetTypes());
    }
}
