<?php

declare(strict_types=1);

namespace PERSPEQTIVE\SuluSnippetManagerBundle\Tests\Unit\ToolbarActions;

use PERSPEQTIVE\SuluSnippetManagerBundle\ToolbarActions\FormToolbarBuilder;
use PERSPEQTIVE\SuluSnippetManagerBundle\Tests\Mocks\Sulu\MockSecurityChecker;
use PHPUnit\Framework\TestCase;
use Sulu\Bundle\AdminBundle\Admin\View\DropdownToolbarAction;
use Sulu\Bundle\AdminBundle\Admin\View\ToolbarAction;
use Sulu\Component\Security\Authorization\PermissionTypes;

class FormToolbarBuilderTest extends TestCase
{
    private MockSecurityChecker $securityChecker;

    protected function setUp(): void
    {
        $this->securityChecker = new MockSecurityChecker();
    }

    public function testBuild(): void
    {
        $toolbarBuilder = new FormToolbarBuilder($this->securityChecker);
        $toolbars = $toolbarBuilder->build('security-context', 'some-view');
        self::assertEquals([
            new ToolbarAction('sulu_admin.save'),
            new DropdownToolbarAction('sulu_admin.edit', 'su-pen', [new ToolbarAction('sulu_admin.copy', ['visible_condition' => '!!id'])]),
        ], $toolbars);
    }

    public function testBuildWithoutPermission(): void
    {
        $this->securityChecker->hasPermission = [];
        $toolbarBuilder = new FormToolbarBuilder($this->securityChecker);
        $toolbars = $toolbarBuilder->build('security-context', 'some-view');
        self::assertEquals([], $toolbars);
    }

    public function testBuildWithOnlyEditPermission(): void
    {
        $this->securityChecker->hasPermission = [
            'security-context' => [
                PermissionTypes::EDIT => true,
            ],
        ];
        $toolbarBuilder = new FormToolbarBuilder($this->securityChecker);
        $toolbars = $toolbarBuilder->build('security-context', 'some-view');
        self::assertEquals([
            new ToolbarAction('sulu_admin.save'),
            new DropdownToolbarAction('sulu_admin.edit', 'su-pen', [new ToolbarAction('sulu_admin.copy', ['visible_condition' => '!!id'])]),
        ], $toolbars);
    }

    public function testBuildWithOnlyDeletePermissionNotOnEditView(): void
    {
        $this->securityChecker->hasPermission = [
            'security-context' => [
                PermissionTypes::DELETE => true,
            ],
        ];
        $toolbarBuilder = new FormToolbarBuilder($this->securityChecker);
        $toolbars = $toolbarBuilder->build('security-context', 'some-view');
        self::assertEquals([], $toolbars);
    }

    public function testBuildWithOnlyDeletePermissionOnEditView(): void
    {
        $this->securityChecker->hasPermission = [
            'security-context' => [
                PermissionTypes::DELETE => true,
            ],
        ];
        $toolbarBuilder = new FormToolbarBuilder($this->securityChecker);
        $toolbars = $toolbarBuilder->build('security-context', 'some-view.edit');
        self::assertEquals([new ToolbarAction('sulu_admin.delete')], $toolbars);
    }
}