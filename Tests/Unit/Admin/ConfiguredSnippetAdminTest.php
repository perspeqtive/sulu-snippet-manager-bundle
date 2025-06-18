<?php

declare(strict_types=1);

namespace PERSPEQTIVE\SuluSnippetManagerBundle\Tests\Unit\Admin;

use PERSPEQTIVE\SuluSnippetManagerBundle\Admin\ConfiguredSnippetAdmin;
use PERSPEQTIVE\SuluSnippetManagerBundle\Security\PermissionTypes;
use PERSPEQTIVE\SuluSnippetManagerBundle\Tests\Assert\AssertView;
use PERSPEQTIVE\SuluSnippetManagerBundle\Tests\Mocks\MockFormToolbarBuilder;
use PERSPEQTIVE\SuluSnippetManagerBundle\Tests\Mocks\MockListToolbarBuilder;
use PERSPEQTIVE\SuluSnippetManagerBundle\Tests\Mocks\Sulu\MockLocalizationProvider;
use PERSPEQTIVE\SuluSnippetManagerBundle\Tests\Mocks\Sulu\MockSecurityChecker;
use PHPUnit\Framework\TestCase;
use Sulu\Bundle\ActivityBundle\Infrastructure\Sulu\Admin\View\ActivityViewBuilderFactory;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItem;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItemCollection;
use Sulu\Bundle\AdminBundle\Admin\View\ViewBuilderFactory;
use Sulu\Bundle\AdminBundle\Admin\View\ViewCollection;
use Sulu\Bundle\ReferenceBundle\Infrastructure\Sulu\Admin\View\ReferenceViewBuilderFactory;

class ConfiguredSnippetAdminTest extends TestCase
{
    private MockSecurityChecker $securityChecker;
    private ViewBuilderFactory $viewBuilderFactory;
    private MockLocalizationProvider $localizationProvider;
    private MockListToolbarBuilder $listToolbarBuilder;
    private MockFormToolbarBuilder $formToolbarBuilder;
    private ActivityViewBuilderFactory $activityViewBuilderFactory;
    private ReferenceViewBuilderFactory $referenceViewBuilderFactory;

    protected function setUp(): void
    {
        $this->securityChecker = new MockSecurityChecker();
        $this->viewBuilderFactory = new ViewBuilderFactory();
        $this->localizationProvider = new MockLocalizationProvider();
        $this->listToolbarBuilder = new MockListToolbarBuilder();
        $this->formToolbarBuilder = new MockFormToolbarBuilder();
        $this->activityViewBuilderFactory = new ActivityViewBuilderFactory($this->viewBuilderFactory, $this->securityChecker);
        $this->referenceViewBuilderFactory = new ReferenceViewBuilderFactory($this->viewBuilderFactory, $this->securityChecker);
    }

    public function testConfigureNavigationItemsWithoutPermission(): void
    {
        $this->securityChecker->hasPermission = [];
        $admin = $this->buildAdmin('testsnippet', 'My Title', 20, 'su-snippet');
        $navigationItemCollection = new NavigationItemCollection();
        $admin->configureNavigationItems($navigationItemCollection);

        self::assertCount(0, $navigationItemCollection->all());
    }

    public function testConfigureNavigationItemWithParentNavigationNotFound(): void
    {
        $admin = $this->buildAdmin('testsnippet', 'My Title', 20, 'su-snippet', 'parentNavigation');
        $navigationItemCollection = new NavigationItemCollection();
        $admin->configureNavigationItems($navigationItemCollection);

        self::assertCount(0, $navigationItemCollection->all());
    }

    public function testConfigureNavigationItemIsBuild(): void
    {
        $admin = $this->buildAdmin('testsnippet', 'My Title', 20, 'su-snippet');
        $navigationItemCollection = new NavigationItemCollection();
        $admin->configureNavigationItems($navigationItemCollection);

        $items = $navigationItemCollection->all();
        self::assertCount(1, $items);
        self::assertArrayHasKey('My Title', $items);
        $item = $items['My Title'];
        self::assertSame('My Title', $item->getName());
        self::assertSame('My Title', $item->getLabel());
        self::assertSame(20, $item->getPosition());
        self::assertSame('su-snippet', $item->getIcon());
        self::assertSame('sulu_snippet_manager_testsnippet.list', $item->getView());
    }

    public function testConfigureNavigationItemIsBuildUnderParent(): void
    {
        $admin = $this->buildAdmin('testsnippet', 'My Title', 20, 'su-snippet', 'parentNavigation');
        $navigationItemCollection = new NavigationItemCollection();
        $navigationItemCollection->add(new NavigationItem('parentNavigation'));
        $admin->configureNavigationItems($navigationItemCollection);

        $parentItems = $navigationItemCollection->all();
        self::assertCount(1, $parentItems);
        self::assertArrayHasKey('parentNavigation', $parentItems);
        $items = $parentItems['parentNavigation']->getChildren();
        self::assertCount(1, $items);
        $item = $items[0];
        self::assertSame('My Title', $item->getName());
        self::assertSame('My Title', $item->getLabel());
        self::assertSame(20, $item->getPosition());
        self::assertSame('su-snippet', $item->getIcon());
        self::assertSame('sulu_snippet_manager_testsnippet.list', $item->getView());
    }

    public function testConfigureViewCollection(): void
    {
        $admin = $this->buildAdmin('testsnippet', 'My Title', 20, 'su-snippet', 'parentNavigation');
        $navigationItemCollection = new NavigationItemCollection();
        $navigationItemCollection->add(new NavigationItem('parentNavigation'));
        $viewCollection = new ViewCollection();
        $admin->configureViews($viewCollection);

        $views = $viewCollection->all();

        self::assertCount(9, $views);
        self::assertArrayHasKey('sulu_snippet_manager_testsnippet.edit', $views);
        self::assertArrayHasKey('sulu_snippet_manager_testsnippet.edit.details', $views);
        self::assertArrayHasKey('sulu_snippet_manager_testsnippet.add', $views);
        self::assertArrayHasKey('sulu_snippet_manager_testsnippet.add.details', $views);
        self::assertArrayHasKey('sulu_snippet_manager_testsnippet.list', $views);
        self::assertArrayHasKey('sulu_snippet_manager_testsnippet.taxonomies', $views);
        self::assertArrayHasKey('sulu_snippet_manager_testsnippet.insights.activity', $views);
        self::assertArrayHasKey('sulu_snippet_manager_testsnippet.insights.reference', $views);

        $editView = $views['sulu_snippet_manager_testsnippet.edit'];
        AssertView::assertResourceView([
            'name' => 'sulu_snippet_manager_testsnippet.edit',
            'path' => '/testsnippet-snippets/:locale/:id',
            'routerAttributesToBackView' => ['locale'],
            'backView' => 'sulu_snippet_manager_testsnippet.list',
            'locales' => ['de', 'en'],
        ], $editView->getView());

        $addView = $views['sulu_snippet_manager_testsnippet.add'];
        AssertView::assertResourceView([
            'name' => 'sulu_snippet_manager_testsnippet.add',
            'path' => '/testsnippet-snippets/:locale/add',
            'backView' => 'sulu_snippet_manager_testsnippet.list',
            'locales' => ['de', 'en'],
        ], $addView->getView());

        $listView = $views['sulu_snippet_manager_testsnippet.list'];
        AssertView::assertListView([
            'name' => 'sulu_snippet_manager_testsnippet.list',
            'path' => '/testsnippet-snippets/:locale',
            'resourceKey' => 'snippets',
            'listKey' => 'snippets',
            'title' => 'Testsnippet Administration',
            'routerAttributesToListRequest' => ['locale'],
            'editView' => 'sulu_snippet_manager_testsnippet.edit',
            'addView' => 'sulu_snippet_manager_testsnippet.add',
            'toolbarActions' => ['save', 'delete'],
            'locales' => ['de', 'en'],
            'requestParameters' => ['types' => 'testsnippet'],
        ], $listView->getView());

        $editFormView = $views['sulu_snippet_manager_testsnippet.edit.details'];
        AssertView::assertFormView([
            'name' => 'sulu_snippet_manager_testsnippet.edit.details',
            'path' => '/details',
            'resourceKey' => 'snippets',
            'formKey' => 'snippets',
            'editView' => 'sulu_snippet_manager_testsnippet.edit',
            'toolbarActions' => ['save', 'delete'],
            'parent' => 'sulu_snippet_manager_testsnippet.edit',
        ], $editFormView->getView());

        $addFormView = $views['sulu_snippet_manager_testsnippet.add.details'];
        AssertView::assertFormView([
            'name' => 'sulu_snippet_manager_testsnippet.add.details',
            'path' => '/details',
            'resourceKey' => 'snippets',
            'formKey' => 'snippets',
            'editView' => 'sulu_snippet_manager_testsnippet.edit',
            'toolbarActions' => ['save', 'delete'],
            'metadataRequestParameters' => ['overwriteDefaultType' => 'testsnippet'],
            'parent' => 'sulu_snippet_manager_testsnippet.add',
        ], $addFormView->getView());
    }

    public function testConfigureViewCollectionHasNoInsights(): void
    {
        $this->securityChecker->hasPermission = ['snippet_manager.testsnippet' => [
            PermissionTypes::VIEW => true,
            PermissionTypes::EDIT => true,
            PermissionTypes::ADD => true,
        ],
            'snippet_manager.testsnippet_taxonomies' => [PermissionTypes::EDIT => true],
        ];
        $admin = $this->buildAdmin('testsnippet', 'My Title', 20, 'su-snippet', 'parentNavigation');
        $navigationItemCollection = new NavigationItemCollection();
        $navigationItemCollection->add(new NavigationItem('parentNavigation'));
        $viewCollection = new ViewCollection();
        $admin->configureViews($viewCollection);

        $views = $viewCollection->all();

        self::assertCount(6, $views);
        self::assertArrayHasKey('sulu_snippet_manager_testsnippet.edit', $views);
        self::assertArrayHasKey('sulu_snippet_manager_testsnippet.edit.details', $views);
        self::assertArrayHasKey('sulu_snippet_manager_testsnippet.add', $views);
        self::assertArrayHasKey('sulu_snippet_manager_testsnippet.add.details', $views);
        self::assertArrayHasKey('sulu_snippet_manager_testsnippet.list', $views);
        self::assertArrayHasKey('sulu_snippet_manager_testsnippet.taxonomies', $views);
    }

    public function testConfigureViewCollectionHasNoTaxonomies(): void
    {
        $this->securityChecker->hasPermission = ['snippet_manager.testsnippet' => [
            PermissionTypes::VIEW => true,
            PermissionTypes::EDIT => true,
            PermissionTypes::ADD => true,
        ],
            'snippet_manager.testsnippet_insights' => [PermissionTypes::EDIT => true],
            'sulu.references.references' => ['*' => true],
            'sulu.activities.activities' => ['*' => true],
        ];
        $admin = $this->buildAdmin('testsnippet', 'My Title', 20, 'su-snippet', 'parentNavigation');
        $navigationItemCollection = new NavigationItemCollection();
        $navigationItemCollection->add(new NavigationItem('parentNavigation'));
        $viewCollection = new ViewCollection();
        $admin->configureViews($viewCollection);

        $views = $viewCollection->all();

        self::assertCount(8, $views);
        self::assertArrayHasKey('sulu_snippet_manager_testsnippet.edit', $views);
        self::assertArrayHasKey('sulu_snippet_manager_testsnippet.edit.details', $views);
        self::assertArrayHasKey('sulu_snippet_manager_testsnippet.add', $views);
        self::assertArrayHasKey('sulu_snippet_manager_testsnippet.add.details', $views);
        self::assertArrayHasKey('sulu_snippet_manager_testsnippet.list', $views);
        self::assertArrayHasKey('sulu_snippet_manager_testsnippet.insights', $views);
        self::assertArrayHasKey('sulu_snippet_manager_testsnippet.insights.activity', $views);
        self::assertArrayHasKey('sulu_snippet_manager_testsnippet.insights.reference', $views);
    }

    public function testConfigureViewCollectionWithoutPermission(): void
    {
        $this->securityChecker->hasPermission = [];
        $admin = $this->buildAdmin('testsnippet', 'My Title', 20, 'su-snippet', 'parentNavigation');
        $navigationItemCollection = new NavigationItemCollection();
        $navigationItemCollection->add(new NavigationItem('parentNavigation'));
        $viewCollection = new ViewCollection();
        $admin->configureViews($viewCollection);

        $views = $viewCollection->all();
        self::assertCount(0, $views);
    }

    public function testGetSecurityContext(): void
    {
        $expected = [
            'Sulu' => [
                'Snippet Manager' => [
                    'snippet_manager.testsnippet' => [
                        PermissionTypes::VIEW,
                        PermissionTypes::ADD,
                        PermissionTypes::EDIT,
                        PermissionTypes::DELETE,
                    ],
                    'snippet_manager.testsnippet_taxonomies' => [
                        PermissionTypes::EDIT,
                    ],
                    'snippet_manager.testsnippet_insights' => [
                        PermissionTypes::EDIT,
                    ],
                ],
            ],
        ];

        $this->securityChecker->hasPermission = [];
        $admin = $this->buildAdmin('testsnippet', 'My Title', 20, 'su-snippet', 'parentNavigation');
        $context = $admin->getSecurityContexts();

        self::assertSame($expected, $context);
    }

    private function buildAdmin(
        string $snippetType,
        string $navigationTitle,
        int $position = 10,
        string $icon = 'su-icon',
        ?string $parentNavigation = null,
    ): ConfiguredSnippetAdmin {
        return new ConfiguredSnippetAdmin(
            $this->viewBuilderFactory,
            $this->securityChecker,
            $this->localizationProvider,
            $this->formToolbarBuilder,
            $this->listToolbarBuilder,
            $this->activityViewBuilderFactory,
            $this->referenceViewBuilderFactory,
            $snippetType,
            $navigationTitle,
            $position,
            $icon,
            $parentNavigation,
        );
    }
}