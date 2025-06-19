<?php

declare(strict_types=1);

namespace PERSPEQTIVE\SuluSnippetManagerBundle\Tests\Unit\Admin;

use PERSPEQTIVE\SuluSnippetManagerBundle\Admin\ConfiguredParentMenuAdmin;
use PHPUnit\Framework\TestCase;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItemCollection;

class ConfiguredParentMenuAdminTest extends TestCase
{
    public function testConfigureNavigationItems(): void
    {
        $admin = new ConfiguredParentMenuAdmin('My Title', 10, 'su-icon');

        $navigationItemCollection = new NavigationItemCollection();
        $admin->configureNavigationItems($navigationItemCollection);

        $items = $navigationItemCollection->all();
        self::assertCount(1, $items);
        self::assertArrayHasKey('My Title', $items);
        $item = $items['My Title'];
        self::assertSame(10, $item->getPosition());
        self::assertSame('su-icon', $item->getIcon());
    }

    public function testGetSecurityContextsIsDefault(): void
    {
        $admin = new ConfiguredParentMenuAdmin('My Title', 10, 'su-icon');

        $securityContext = $admin->getSecurityContexts();

        self::assertSame([], $securityContext);
    }

    public function testGetPriorityIsGreaterThanSnippetMenuItem(): void
    {
        $admin = new ConfiguredParentMenuAdmin('My Title', 10, 'su-icon');

        self::assertGreaterThan(20, $admin->getPriority());
    }
}
