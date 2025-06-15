<?php

declare(strict_types=1);

namespace PERSPEQTIVE\SuluSnippetManagerBundle\Admin;

use Sulu\Bundle\AdminBundle\Admin\Admin;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItem;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItemCollection;
use Sulu\Bundle\AdminBundle\Admin\View\DropdownToolbarAction;
use Sulu\Bundle\AdminBundle\Admin\View\ToolbarAction;
use Sulu\Bundle\AdminBundle\Admin\View\ViewBuilderFactoryInterface;
use Sulu\Bundle\AdminBundle\Admin\View\ViewCollection;
use Sulu\Bundle\SnippetBundle\Document\SnippetDocument;
use Sulu\Component\Security\Authorization\PermissionTypes;
use Sulu\Component\Security\Authorization\SecurityCheckerInterface;

class ConfiguredSnippetAdmin extends Admin
{

    public function __construct(
        private readonly ViewBuilderFactoryInterface $viewBuilderFactory,
        private readonly SecurityCheckerInterface $securityChecker,
        private readonly string $snippetName
    )
    {
    }

    public function configureNavigationItems(NavigationItemCollection $navigationItemCollection): void
    {
        if(!$navigationItemCollection->has('Konfiguration')) {
            return;
        }

        if ($this->securityChecker->hasPermission($this->buildSecurityContext(), PermissionTypes::EDIT)) {
            $configurationItem = $navigationItemCollection->get('Konfiguration');
            $navigationItem = new NavigationItem($this->buildName());
            $navigationItem->setView($this->buildListViewName());
            $navigationItem->setIcon('su-snippet');
            $navigationItem->setPosition(10);

            $configurationItem->addChild($navigationItem);
        }
    }

    public function configureViews(ViewCollection $viewCollection): void
    {
        $this->buildResourceTabViews($viewCollection);
        $this->buildOverviewViewTemplates($viewCollection);
        $this->buildFormViewTemplates($viewCollection);
    }

    private function buildOverviewViewTemplates(ViewCollection $viewCollection): void
    {
        if ($this->securityChecker->hasPermission($this->buildSecurityContext(), PermissionTypes::EDIT) === false) {
            return;
        }
        $viewCollection->add(
            $this->viewBuilderFactory
                ->createListViewBuilder($this->buildListViewName(), '/' . $this->snippetName. '-snippets/:locale')
                ->setResourceKey(SnippetDocument::RESOURCE_KEY)
                ->setListKey('snippets')
                ->setTitle($this->buildName())
                ->addListAdapters(['table'])
                ->addRouterAttributesToListRequest(['locale'])
                ->addToolbarActions($this->buildListToolbarActions())
                ->setAddView($this->buildAddFormViewName())
                ->setEditView($this->buildEditFormViewName())
                ->setDefaultLocale('de')
                ->addLocales(['de', 'en'])
                ->enableFiltering()
                ->addRequestParameters(['types' => $this->snippetName])
        );
    }

    private function buildFormViewTemplates(ViewCollection $viewCollection): void
    {
        if ($this->securityChecker->hasPermission($this->buildSecurityContext(), PermissionTypes::EDIT) === false) {
            return;
        }
        $viewCollection->add(
            $this->viewBuilderFactory->createFormViewBuilder($this->buildAddFormViewName() . '.details', '/details')
                ->setResourceKey(SnippetDocument::RESOURCE_KEY)
                ->setFormKey('snippet')
                ->setTabTitle('sulu_admin.details')
                ->setEditView($this->buildEditFormViewName())
                ->addToolbarActions($this->buildFormToolbarActions($this->buildEditFormViewName()))
                ->setParent($this->buildAddFormViewName())
        );

        $viewCollection->add(
            $this->viewBuilderFactory
                ->createFormViewBuilder($this->buildEditFormViewName() . '.details', '/details')
                ->setResourceKey(SnippetDocument::RESOURCE_KEY)
                ->setFormKey('snippet')
                ->setTabTitle('sulu_admin.details')
                ->setEditView($this->buildEditFormViewName())
                ->addToolbarActions($this->buildFormToolbarActions($this->buildEditFormViewName()))
                ->setParent($this->buildEditFormViewName())

        );
    }

    private function buildResourceTabViews(ViewCollection $viewCollection): void
    {
        if ($this->securityChecker->hasPermission($this->buildSecurityContext(), PermissionTypes::EDIT) === false) {
            return;
        }
        $viewCollection->add(
            $this->viewBuilderFactory
                ->createResourceTabViewBuilder($this->buildEditFormViewName(), '/' . $this->snippetName. '-snippets/:locale/:id')
                ->setResourceKey(SnippetDocument::RESOURCE_KEY)
                ->addRouterAttributesToBackView(['locale'])
                ->setBackView($this->buildListViewName())
                ->addLocales(['de', 'en'])
                ->setTitleProperty('title')
        );
        $viewCollection->add(
            $this->viewBuilderFactory
                ->createResourceTabViewBuilder($this->buildAddFormViewName(), '/' . $this->snippetName. '-snippets/:locale/add')
                ->setResourceKey(SnippetDocument::RESOURCE_KEY)
                ->addLocales(['de', 'en'])
                ->setBackView($this->buildListViewName())
        );
    }

    private function buildFormToolbarActions(string $view): array {
        $formToolbarActions = [];
        if ($this->securityChecker->hasPermission($this->buildSecurityContext(), PermissionTypes::EDIT)) {
            $formToolbarActions[] =  new ToolbarAction('sulu_admin.save');
            $editDropdownToolbarActions[] = new ToolbarAction('sulu_admin.copy', [
                'visible_condition' => '!!id',
            ]);
            $formToolbarActions[] = new DropdownToolbarAction(
                'sulu_admin.edit',
                'su-pen',
                $editDropdownToolbarActions,
            );
        }
        if($view === $this->buildEditFormViewName() &&
            $this->securityChecker->hasPermission($this->buildSecurityContext(), PermissionTypes::DELETE)) {
            $formToolbarActions[] = new ToolbarAction('sulu_admin.delete');
        }
        return $formToolbarActions;
    }

    private function buildListToolbarActions(): array {
        $listToolbarActions = [];
        if ($this->securityChecker->hasPermission($this->buildSecurityContext(), PermissionTypes::ADD)) {
            $listToolbarActions[] = new ToolbarAction('sulu_admin.add');
        }
        if ($this->securityChecker->hasPermission($this->buildSecurityContext(), PermissionTypes::DELETE)) {
            $listToolbarActions[] = new ToolbarAction('sulu_admin.delete');
        }
        if ($this->securityChecker->hasPermission($this->buildSecurityContext(), PermissionTypes::VIEW)) {
            $listToolbarActions[] = new ToolbarAction('sulu_admin.export');
        }
        return $listToolbarActions;
    }

    private function buildName(): string {
        return ucwords(implode(' ', explode('-', $this->snippetName)));
    }

    private function buildSecurityContext(): string {
        return $this->snippetName . '_security_context';
    }

    private function buildListViewName(): string {
        return 'snippet_' . $this->snippetName . '.list';
    }

    private function buildAddFormViewName(): string {
        return 'snippet_' . $this->snippetName . '.add';
    }

    private function buildEditFormViewName(): string {
        return 'snippet_' . $this->snippetName . '.edit';
    }

    public function getSecurityContexts(): array
    {
        return [
            'Sulu' => [
                'Website' => [
                    $this->buildSecurityContext() => [
                        PermissionTypes::VIEW,
                        PermissionTypes::ADD,
                        PermissionTypes::EDIT,
                        PermissionTypes::DELETE,
                    ],
                ],
            ],
        ];
    }

    public static function getPriority(): int {
        return 20;
    }

}