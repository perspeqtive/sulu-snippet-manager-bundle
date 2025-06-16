<?php

declare(strict_types=1);

namespace PERSPEQTIVE\SuluSnippetManagerBundle\Admin;

use PERSPEQTIVE\SuluSnippetManagerBundle\ToolbarActions\FormToolbarBuilder;
use PERSPEQTIVE\SuluSnippetManagerBundle\ToolbarActions\ListToolbarBuilder;
use Sulu\Bundle\AdminBundle\Admin\Admin;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItem;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItemCollection;
use Sulu\Bundle\AdminBundle\Admin\View\ViewBuilderFactoryInterface;
use Sulu\Bundle\AdminBundle\Admin\View\ViewCollection;
use Sulu\Bundle\SnippetBundle\Document\SnippetDocument;
use Sulu\Component\Security\Authorization\PermissionTypes;
use Sulu\Component\Security\Authorization\SecurityCheckerInterface;

use function explode;
use function implode;
use function ucwords;

class ConfiguredSnippetAdmin extends Admin
{
    public function __construct(
        private readonly ViewBuilderFactoryInterface $viewBuilderFactory,
        private readonly SecurityCheckerInterface $securityChecker,
        private readonly FormToolbarBuilder $formToolbarBuilder,
        private readonly ListToolbarBuilder $listToolbarBuilder,
        private readonly string $snippetType,
        private readonly string $navigationTitle,
        private readonly int $position = 40,
        private readonly string $icon = 'su-snippet',
        private readonly ?string $parentNavigation = null,
    ) {
    }

    public function configureNavigationItems(NavigationItemCollection $navigationItemCollection): void
    {
        if ($this->securityChecker->hasPermission($this->buildSecurityContext(), PermissionTypes::EDIT) === false) {
            return;
        }

        if ($this->parentNavigation !== null && $navigationItemCollection->has($this->parentNavigation) === false) {
            return;
        }

        $navigationItem = new NavigationItem($this->navigationTitle);
        $navigationItem->setView($this->buildListViewName());
        $navigationItem->setIcon($this->icon);
        $navigationItem->setPosition($this->position);

        if ($this->parentNavigation !== null) {
            $parentNavigationItem = $navigationItemCollection->get($this->parentNavigation);
            $parentNavigationItem->addChild($navigationItem);

            return;
        }

        $navigationItemCollection->add($navigationItem);
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
                ->createListViewBuilder($this->buildListViewName(), '/' . $this->snippetType . '-snippets/:locale')
                ->setResourceKey(SnippetDocument::RESOURCE_KEY)
                ->setListKey('snippets')
                ->setTitle($this->buildName())
                ->addListAdapters(['table'])
                ->addRouterAttributesToListRequest(['locale'])
                ->addToolbarActions(
                    $this->listToolbarBuilder->build($this->buildSecurityContext()),
                )
                ->setAddView($this->buildAddFormViewName())
                ->setEditView($this->buildEditFormViewName())
                ->setDefaultLocale('de')
                ->addLocales(['de', 'en'])
                ->enableFiltering()
                ->addRequestParameters(['types' => $this->snippetType]),
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
                ->addToolbarActions(
                    $this->formToolbarBuilder->build(
                        $this->buildSecurityContext(),
                        $this->buildAddFormViewName(),
                    ),
                )
                ->setParent($this->buildAddFormViewName()),
        );

        $viewCollection->add(
            $this->viewBuilderFactory
                ->createFormViewBuilder($this->buildEditFormViewName() . '.details', '/details')
                ->setResourceKey(SnippetDocument::RESOURCE_KEY)
                ->setFormKey('snippet')
                ->setTabTitle('sulu_admin.details')
                ->setEditView($this->buildEditFormViewName())
                ->addToolbarActions(
                    $this->formToolbarBuilder->build(
                        $this->buildSecurityContext(),
                        $this->buildEditFormViewName(),
                    ),
                )
                ->setParent($this->buildEditFormViewName()),
        );
    }

    private function buildResourceTabViews(ViewCollection $viewCollection): void
    {
        if ($this->securityChecker->hasPermission($this->buildSecurityContext(), PermissionTypes::EDIT) === false) {
            return;
        }
        $viewCollection->add(
            $this->viewBuilderFactory
                ->createResourceTabViewBuilder($this->buildEditFormViewName(), '/' . $this->snippetType . '-snippets/:locale/:id')
                ->setResourceKey(SnippetDocument::RESOURCE_KEY)
                ->addRouterAttributesToBackView(['locale'])
                ->setBackView($this->buildListViewName())
                ->addLocales(['de', 'en'])
                ->setTitleProperty('title'),
        );
        $viewCollection->add(
            $this->viewBuilderFactory
                ->createResourceTabViewBuilder($this->buildAddFormViewName(), '/' . $this->snippetType . '-snippets/:locale/add')
                ->setResourceKey(SnippetDocument::RESOURCE_KEY)
                ->addLocales(['de', 'en'])
                ->setBackView($this->buildListViewName()),
        );
    }

    private function buildName(): string
    {
        return ucwords(implode(' ', explode('-', $this->snippetType))) . ' Administration';
    }

    private function buildSecurityContext(): string
    {
        return 'sulu_snippet_manager_' . $this->snippetType . '_security_context';
    }

    private function buildListViewName(): string
    {
        return 'sulu_snippet_manager_' . $this->snippetType . '.list';
    }

    private function buildAddFormViewName(): string
    {
        return 'sulu_snippet_manager_' . $this->snippetType . '.add';
    }

    private function buildEditFormViewName(): string
    {
        return 'sulu_snippet_manager_' . $this->snippetType . '.edit';
    }

    public function getSecurityContexts(): array
    {
        return [
            'Sulu' => [
                'Snippet Manager' => [
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

    public static function getPriority(): int
    {
        return 20;
    }
}
