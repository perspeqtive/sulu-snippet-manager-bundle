<?php

declare(strict_types=1);

namespace PERSPEQTIVE\SuluSnippetManagerBundle\Tests\Unit\ToolbarActions;

use PERSPEQTIVE\SuluSnippetManagerBundle\Tests\Mocks\Sulu\MockSecurityChecker;
use PERSPEQTIVE\SuluSnippetManagerBundle\ToolbarActions\ListToolbarBuilder;
use PHPUnit\Framework\TestCase;
use Sulu\Bundle\AdminBundle\Admin\View\ToolbarAction;
use Sulu\Component\Security\Authorization\PermissionTypes;

class ListToolbarBuilderTest extends TestCase
{
    private MockSecurityChecker $securityChecker;

    protected function setUp(): void
    {
        $this->securityChecker = new MockSecurityChecker();
    }

    public function testBuild(): void
    {
        $toolbarBuilder = new ListToolbarBuilder($this->securityChecker);
        $toolbars = $toolbarBuilder->build('security-context');
        self::assertEquals([
            new ToolbarAction('sulu_admin.add'),
            new ToolbarAction('sulu_admin.delete'),
            new ToolbarAction('sulu_admin.export'),
        ], $toolbars);
    }

    public function testBuildHasAddOnlyPermission(): void
    {
        $this->securityChecker->hasPermission = [PermissionTypes::ADD => true];
        $toolbarBuilder = new ListToolbarBuilder($this->securityChecker);
        $toolbars = $toolbarBuilder->build('security-context');
        self::assertEquals([
            new ToolbarAction('sulu_admin.add'),
        ], $toolbars);
    }

    public function testBuildHasDeleteOnlyPermission(): void
    {
        $this->securityChecker->hasPermission = [PermissionTypes::DELETE => true];
        $toolbarBuilder = new ListToolbarBuilder($this->securityChecker);
        $toolbars = $toolbarBuilder->build('security-context');
        self::assertEquals([
            new ToolbarAction('sulu_admin.delete'),
        ], $toolbars);
    }

    public function testBuildHasViewOnlyPermission(): void
    {
        $this->securityChecker->hasPermission = [PermissionTypes::VIEW => true];
        $toolbarBuilder = new ListToolbarBuilder($this->securityChecker);
        $toolbars = $toolbarBuilder->build('security-context');
        self::assertEquals([
            new ToolbarAction('sulu_admin.export'),
        ], $toolbars);
    }
}
