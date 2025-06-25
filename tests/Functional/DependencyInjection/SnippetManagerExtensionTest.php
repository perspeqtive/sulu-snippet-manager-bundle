<?php

declare(strict_types=1);

namespace PERSPEQTIVE\SuluSnippetManagerBundle\Tests\Functional\DependencyInjection;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

use function restore_exception_handler;

class SnippetManagerExtensionTest extends KernelTestCase
{
    public function testLoad(): void
    {
        $settings = static::getContainer()->getParameter('sulu_snippet_manager.navigation');
        self::assertSame([
            'configuration' => [
                'navigation_title' => 'configuration',
                'order' => 39,
                'icon' => 'su-news',
                'children' => [
                    'settings' => [
                        'navigation_title' => 'Settings',
                        'type' => 'settings',
                        'order' => 42,
                        'icon' => 'su-settings',
                        'list_view_key' => 'snippets',
                    ],
                    'account_settings' => [
                        'navigation_title' => 'Account Settings',
                        'type' => 'account',
                        'order' => 43,
                        'icon' => 'su-account',
                        'list_view_key' => 'snippets',
                    ],
                ],
                'type' => null,
                'list_view_key' => 'snippets',
            ],
            'services' => [
                'navigation_title' => 'Services',
                'type' => 'services',
                'order' => 41,
                'icon' => 'su-services',
                'list_view_key' => 'snippets',
                'children' => [],
            ],
        ], $settings);
    }

    protected function tearDown(): void
    {
        restore_exception_handler();
        parent::tearDown();
    }
}
