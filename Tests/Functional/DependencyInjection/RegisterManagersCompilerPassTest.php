<?php

declare(strict_types=1);

namespace PERSPEQTIVE\SuluSnippetManagerBundle\Tests\Functional\DependencyInjection;

use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItem;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationRegistry;
use Sulu\Bundle\AdminBundle\Admin\View\View;
use Sulu\Bundle\AdminBundle\Admin\View\ViewRegistry;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class RegisterManagersCompilerPassTest extends KernelTestCase
{


    private static function assertHasView(string $name, ViewRegistry $viewRegistry)
    {
        try {
            $view = $viewRegistry->findViewByName($name);
            self::assertInstanceOf(View::class, $view);
        } catch(\Exception) {
            self::fail(sprintf('View "%s" does not exist.', $name));
        }
    }

    public function testNavigationItemsAreCreated(): void {

        /** @var NavigationRegistry $navigationRegistry */
        $navigationRegistry = static::getContainer()->get('sulu_admin.navigation_registry');

        $item = $this->getItemByName('Services', $navigationRegistry);
        self::assertSame('Services', $item->getName());
        self::assertSame('Services', $item->getLabel());
        self::assertSame('su-services', $item->getIcon());
        self::assertSame('sulu_snippet_manager_services.list', $item->getView());
        self::assertSame([], $item->getChildren());
        self::assertSame(41, $item->getPosition());

        $item = $this->getItemByName('configuration', $navigationRegistry);
        self::assertSame('configuration', $item->getName());
        self::assertSame('configuration', $item->getLabel());
        self::assertSame('su-news', $item->getIcon());
        self::assertSame(null, $item->getView());
        self::assertSame(39, $item->getPosition());
        self::assertCount(2, $item->getChildren());

        $children = $item->getChildren();
        $item = $children[0];
        self::assertSame('Settings', $item->getName());
        self::assertSame('Settings', $item->getLabel());
        self::assertSame('su-settings', $item->getIcon());
        self::assertSame('sulu_snippet_manager_settings.list', $item->getView());
        self::assertSame(42, $item->getPosition());
        self::assertCount(0, $item->getChildren());

        $item = $children[1];
        self::assertSame('Account Settings', $item->getName());
        self::assertSame('Account Settings', $item->getLabel());
        self::assertSame('su-account', $item->getIcon());
        self::assertSame('sulu_snippet_manager_account.list', $item->getView());
        self::assertSame(43, $item->getPosition());
        self::assertCount(0, $item->getChildren());

    }

    public function testViewsAreCreated(): void {

        /** @var ViewRegistry $viewRegistry */
        $viewRegistry = static::getContainer()->get('sulu_admin.view_registry');

        self::assertHasView('sulu_snippet_manager_settings.edit', $viewRegistry);
        self::assertHasView('sulu_snippet_manager_settings.edit.details', $viewRegistry);
        self::assertHasView('sulu_snippet_manager_settings.add', $viewRegistry);
        self::assertHasView('sulu_snippet_manager_settings.add.details', $viewRegistry);
        self::assertHasView('sulu_snippet_manager_settings.list', $viewRegistry);

        self::assertHasView('sulu_snippet_manager_account.edit', $viewRegistry);
        self::assertHasView('sulu_snippet_manager_account.edit.details', $viewRegistry);
        self::assertHasView('sulu_snippet_manager_account.add', $viewRegistry);
        self::assertHasView('sulu_snippet_manager_account.add.details', $viewRegistry);
        self::assertHasView('sulu_snippet_manager_account.list', $viewRegistry);

        self::assertHasView('sulu_snippet_manager_services.edit', $viewRegistry);
        self::assertHasView('sulu_snippet_manager_services.edit.details', $viewRegistry);
        self::assertHasView('sulu_snippet_manager_services.add', $viewRegistry);
        self::assertHasView('sulu_snippet_manager_services.add.details', $viewRegistry);
        self::assertHasView('sulu_snippet_manager_services.list', $viewRegistry);



    }


    public function getItemByName(string $name, NavigationRegistry $navigationRegistry): NavigationItem
    {
        $items = $navigationRegistry->getNavigationItems();
        foreach ($items as $navigationItem) {
            if ($navigationItem->getName() === $name) {
                return $navigationItem;
            }
        }
        self::fail('Navigation Item "' . $name . '" not found');
    }

    protected function tearDown(): void
    {
        restore_exception_handler();
        parent::tearDown();
    }


}