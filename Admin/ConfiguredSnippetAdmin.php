<?php

declare(strict_types=1);

namespace PERSPEQTIVE\SuluSnippetManagerBundle\Admin;

use PERSPEQTIVE\SuluSnippetManagerBundle\Security\PermissionTypes;
use PERSPEQTIVE\SuluSnippetManagerBundle\View\ViewTypes;
use Sulu\Bundle\ActivityBundle\Infrastructure\Sulu\Admin\View\ActivityViewBuilderFactoryInterface;
use Sulu\Bundle\AdminBundle\Admin\Admin;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItem;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItemCollection;
use Sulu\Bundle\AdminBundle\Admin\View\ViewBuilderFactoryInterface;
use Sulu\Bundle\AdminBundle\Admin\View\ViewCollection;
use Sulu\Bundle\ReferenceBundle\Infrastructure\Sulu\Admin\View\ReferenceViewBuilderFactoryInterface;
use Sulu\Bundle\SnippetBundle\Document\SnippetDocument;
use Sulu\Component\Localization\Provider\LocalizationProviderInterface;
use Sulu\Component\Security\Authorization\SecurityCheckerInterface;

use function explode;
use function implode;
use function ucwords;

class ConfiguredSnippetAdmin extends Admin
{
    public function __construct(
        private readonly ViewBuilderFactoryInterface $viewBuilderFactory,
        private readonly SecurityCheckerInterface $securityChecker,
        private readonly LocalizationProviderInterface $localizationProvider,
        private readonly FormToolbarBuilderInterface $formToolbarBuilder,
        private readonly ListToolbarBuilderInterface $listToolbarBuilder,
        private readonly ActivityViewBuilderFactoryInterface $activityViewBuilderFactory,
        private readonly ReferenceViewBuilderFactoryInterface $referenceViewBuilderFactory,
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
        $navigationItem->setLabel($this->navigationTitle);
        $navigationItem->setView($this->buildViewName(ViewTypes::LIST));
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
        $this->buildTaxonomiesView($viewCollection);
        $this->buildInsightsView($viewCollection);
    }

    private function buildOverviewViewTemplates(ViewCollection $viewCollection): void
    {
        if ($this->securityChecker->hasPermission($this->buildSecurityContext(), PermissionTypes::EDIT) === false) {
            return;
        }
        $viewCollection->add(
            $this->viewBuilderFactory
                ->createListViewBuilder($this->buildViewName(ViewTypes::LIST), '/' . $this->snippetType . '-snippets/:locale')
                ->setResourceKey(SnippetDocument::RESOURCE_KEY)
                ->setListKey('snippets')
                ->setTitle($this->buildName())
                ->addListAdapters(['table'])
                ->addRouterAttributesToListRequest(['locale'])
                ->addToolbarActions(
                    $this->listToolbarBuilder->build($this->buildSecurityContext()),
                )
                ->setAddView($this->buildViewName(ViewTypes::ADD))
                ->setEditView($this->buildViewName(ViewTypes::EDIT))
                ->addLocales($this->localizationProvider->getAllLocales())
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
            $this->viewBuilderFactory->createFormViewBuilder($this->buildViewName(ViewTypes::ADD) . '.details', '/details')
                ->setResourceKey(SnippetDocument::RESOURCE_KEY)
                ->setFormKey('snippet')
                ->setTabTitle('sulu_admin.details')
                ->setEditView($this->buildViewName(ViewTypes::EDIT))
                ->addToolbarActions(
                    $this->formToolbarBuilder->build(
                        $this->buildSecurityContext(),
                        $this->buildViewName(ViewTypes::ADD),
                    ),
                )
                ->setParent($this->buildViewName(ViewTypes::ADD)),
        );

        $viewCollection->add(
            $this->viewBuilderFactory
                ->createFormViewBuilder($this->buildViewName(ViewTypes::EDIT) . '.details', '/details')
                ->setResourceKey(SnippetDocument::RESOURCE_KEY)
                ->setFormKey('snippet')
                ->setTabTitle('sulu_admin.details')
                ->setEditView($this->buildViewName(ViewTypes::EDIT))
                ->addToolbarActions(
                    $this->formToolbarBuilder->build(
                        $this->buildSecurityContext(),
                        $this->buildViewName(ViewTypes::EDIT),
                    ),
                )
                ->setParent($this->buildViewName(ViewTypes::EDIT)),
        );
    }

    private function buildResourceTabViews(ViewCollection $viewCollection): void
    {
        if ($this->securityChecker->hasPermission($this->buildSecurityContext(), PermissionTypes::EDIT) === false) {
            return;
        }
        $locales = $this->localizationProvider->getAllLocales();
        $viewCollection->add(
            $this->viewBuilderFactory
                ->createResourceTabViewBuilder($this->buildViewName(ViewTypes::EDIT), '/' . $this->snippetType . '-snippets/:locale/:id')
                ->setResourceKey(SnippetDocument::RESOURCE_KEY)
                ->addRouterAttributesToBackView(['locale'])
                ->setBackView($this->buildViewName(ViewTypes::LIST))
                ->addLocales($locales)
                ->setTitleProperty('title'),
        );
        $viewCollection->add(
            $this->viewBuilderFactory
                ->createResourceTabViewBuilder($this->buildViewName(ViewTypes::ADD), '/' . $this->snippetType . '-snippets/:locale/add')
                ->setResourceKey(SnippetDocument::RESOURCE_KEY)
                ->addRouterAttributesToBackView(['locale'])
                ->setBackView($this->buildViewName(ViewTypes::LIST))
                ->addLocales($locales),
        );
    }

    private function buildTaxonomiesView(ViewCollection $viewCollection): void
    {
        if (
            $this->securityChecker->hasPermission($this->buildSecurityContext(), PermissionTypes::EDIT) === false
            || $this->securityChecker->hasPermission($this->buildSecurityContext(PermissionTypes::CONTEXT_TAXONOMIES), PermissionTypes::EDIT) === false
        ) {
            return;
        }
        $viewCollection->add(
            $this->viewBuilderFactory
                ->createFormViewBuilder($this->buildViewName(ViewTypes::TAXONOMIES), '/taxonomies')
                ->setResourceKey(SnippetDocument::RESOURCE_KEY)
                ->setFormKey('snippet_taxonomies')
                ->setTabTitle('sulu_snippet.taxonomies')
                ->addToolbarActions(
                    $this->formToolbarBuilder->build(
                        $this->buildSecurityContext(),
                        $this->buildViewName(ViewTypes::TAXONOMIES),
                    ),
                )
                ->setTitleVisible(true)
                ->setParent($this->buildViewName(ViewTypes::EDIT)),
        );
    }

    private function buildInsightsView(ViewCollection $viewCollection): void
    {
        if (
            $this->hasInsightsSubViewPermissions() === false
            || $this->securityChecker->hasPermission($this->buildSecurityContext(), PermissionTypes::EDIT) === false
            || $this->securityChecker->hasPermission($this->buildSecurityContext(PermissionTypes::CONTEXT_INSIGHTS), PermissionTypes::EDIT) === false
        ) {
            return;
        }

        $viewCollection->add(
            $this->viewBuilderFactory
                ->createResourceTabViewBuilder($this->buildViewName(ViewTypes::INSIGHTS), '/insights')
                ->setResourceKey(SnippetDocument::RESOURCE_KEY)
                ->setTabOrder(6144)
                ->setTabTitle('sulu_admin.insights')
                ->setTitleProperty('')
                ->setParent($this->buildViewName(ViewTypes::EDIT)),
        );

        if ($this->activityViewBuilderFactory->hasActivityListPermission()) {
            $viewCollection->add(
                $this->activityViewBuilderFactory
                    ->createActivityListViewBuilder(
                        $this->buildViewName(ViewTypes::INSIGHTS) . '.activity',
                        '/activities',
                        SnippetDocument::RESOURCE_KEY,
                    )
                    ->setParent($this->buildViewName(ViewTypes::INSIGHTS)),
            );
        }

        if ($this->referenceViewBuilderFactory->hasReferenceListPermission()) {
            $viewCollection->add(
                $this->referenceViewBuilderFactory
                    ->createReferenceListViewBuilder(
                        $this->buildViewName(ViewTypes::INSIGHTS) . '.reference',
                        '/references',
                        SnippetDocument::RESOURCE_KEY,
                    )
                    ->setParent($this->buildViewName(ViewTypes::INSIGHTS)),
            );
        }
    }

    private function buildName(): string
    {
        return ucwords(implode(' ', explode('-', $this->snippetType))) . ' Administration';
    }

    private function buildSecurityContext(string $context = PermissionTypes::CONTEXT_SNIPPETS): string
    {
        return 'sulu_snippet_manager_' . $context . '_' . $this->snippetType . '_security_context';
    }

    private function buildViewName(string $type): string
    {
        return 'sulu_snippet_manager_' . $this->snippetType . '.' . $type;
    }

    private function hasInsightsSubViewPermissions(): bool
    {
        return $this->activityViewBuilderFactory->hasActivityListPermission()
            || $this->referenceViewBuilderFactory->hasReferenceListPermission();
    }

    public function getSecurityContexts(): array
    {
        return [
            'Sulu' => [
                'Snippet Manager' => [
                    $this->buildSecurityContext(PermissionTypes::CONTEXT_SNIPPETS) => [
                        PermissionTypes::VIEW,
                        PermissionTypes::ADD,
                        PermissionTypes::EDIT,
                        PermissionTypes::DELETE,
                    ],
                    $this->buildSecurityContext(PermissionTypes::CONTEXT_TAXONOMIES) => [
                        PermissionTypes::EDIT,
                    ],
                    $this->buildSecurityContext(PermissionTypes::CONTEXT_INSIGHTS) => [
                        PermissionTypes::EDIT,
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
