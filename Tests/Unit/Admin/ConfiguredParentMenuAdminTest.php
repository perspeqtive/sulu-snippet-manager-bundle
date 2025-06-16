<?php

declare(strict_types=1);

namespace PERSPEQTIVE\SuluSnippetManagerBundle\Tests\Unit\Admin;

use PERSPEQTIVE\SuluSnippetManagerBundle\Admin\ConfiguredParentMenuAdmin;
use PERSPEQTIVE\SuluSnippetManagerBundle\Tests\Mocks\Sulu\MockSecurityChecker;
use PHPUnit\Framework\TestCase;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItemCollection;
use Sulu\Component\Security\Authorization\PermissionTypes;

class ConfiguredParentMenuAdminTest extends TestCase
{
    private MockSecurityChecker $securityChecker;

    protected function setUp(): void
    {
        $this->securityChecker = new MockSecurityChecker();
    }

    public function testConfigureNavigationItemsWithoutPermission(): void
    {
        $this->securityChecker->hasPermission = [];

        $admin = new ConfiguredParentMenuAdmin($this->securityChecker, 'Title', 10, 'su-icon');

        $navigationItemCollection = new NavigationItemCollection();
        $admin->configureNavigationItems($navigationItemCollection);

        self::assertCount(0, $navigationItemCollection->all());
    }

    public function testConfigureNavigationItemsWithPermission(): void
    {
        $admin = new ConfiguredParentMenuAdmin($this->securityChecker, 'My Title', 10, 'su-icon');

        $navigationItemCollection = new NavigationItemCollection();
        $admin->configureNavigationItems($navigationItemCollection);

        $items = $navigationItemCollection->all();
        self::assertCount(1, $items);
        self::assertArrayHasKey('My Title', $items);
        $item = $items['My Title'];
        self::assertSame(10, $item->getPosition());
        self::assertSame('su-icon', $item->getIcon());
        self::assertSame('My Title', $item->getLabel());

        self::assertSame('sulu_snippet_manager_mytitle_security_context', $this->securityChecker->subjectName);
    }

    public function testGetSecurityContextsIsBuild(): void
    {
        $expected = [
            'Sulu' => [
                'Snippet Manager' => [
                    'sulu_snippet_manager_mytitle_security_context' => [
                        PermissionTypes::VIEW,
                    ],
                ],
            ],
        ];

        $admin = new ConfiguredParentMenuAdmin($this->securityChecker, 'My Title', 10, 'su-icon');

        $securityContext = $admin->getSecurityContexts();

        self::assertSame($expected, $securityContext);
    }

    public function testGetPriorityIsGreaterThanSnippetMenuItem(): void
    {
        $admin = new ConfiguredParentMenuAdmin($this->securityChecker, 'My Title', 10, 'su-icon');

        self::assertGreaterThan(20, $admin->getPriority());
    }
}
