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
use Sulu\Component\Localization\Provider\LocalizationProviderInterface;
use Sulu\Component\Security\Authorization\SecurityCheckerInterface;
use Sulu\Snippet\Domain\Model\Snippet;
use Sulu\Snippet\Domain\Model\SnippetDimensionContent;
use Sulu\Snippet\Domain\Model\SnippetInterface;

use function explode;
use function implode;
use function in_array;
use function is_subclass_of;
use function ucwords;

class ConfiguredSnippetAdmin extends Admin
{
    /**
     * @param array<string, array{instanceOf: class-string}> $settingsForms
     * @param array<string, array{instanceOf: class-string}> $excerptForms
     */
    public function __construct(
        private readonly ViewBuilderFactoryInterface $viewBuilderFactory,
        private readonly SecurityCheckerInterface $securityChecker,
        private readonly LocalizationProviderInterface $localizationProvider,
        private readonly FormToolbarBuilderInterface $formToolbarBuilder,
        private readonly ListToolbarBuilderInterface $listToolbarBuilder,
        private readonly ActivityViewBuilderFactoryInterface $activityViewBuilderFactory,
        private readonly ReferenceViewBuilderFactoryInterface $referenceViewBuilderFactory,
        private readonly array $excerptForms,
        private readonly array $settingsForms,
        private readonly string $snippetType,
        private readonly string $navigationTitle,
        private readonly string $listViewKey,
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
        $this->buildExcerptView($viewCollection);
        $this->buildSettingsView($viewCollection);
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
                ->setResourceKey(SnippetInterface::RESOURCE_KEY)
                ->setListKey($this->listViewKey)
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
                // Sulu >= 3.0.8 filters the snippet list by the "templateKeys" query
                // parameter; older versions use "types". Send both so the list is
                // filtered on every supported Sulu 3.0 version. AccessControlManager
                // still reads "types" for permission checks.
                ->addRequestParameters([
                    'types' => $this->snippetType,
                    'templateKeys' => $this->snippetType,
                ]),
        );
    }

    private function buildFormViewTemplates(ViewCollection $viewCollection): void
    {
        if ($this->securityChecker->hasPermission($this->buildSecurityContext(), PermissionTypes::EDIT) === false) {
            return;
        }
        $viewCollection->add(
            $this->viewBuilderFactory->createFormViewBuilder($this->buildViewName(ViewTypes::ADD) . '.content', '/content')
                ->setResourceKey(SnippetInterface::RESOURCE_KEY)
                ->setFormKey('snippet')
                ->setTabTitle('sulu_admin.content')
                ->setEditView($this->buildViewName(ViewTypes::EDIT))
                ->addToolbarActions(
                    $this->formToolbarBuilder->build(
                        $this->buildSecurityContext(),
                        $this->buildViewName(ViewTypes::ADD),
                    ),
                )
                ->addMetadataRequestParameters(['overwriteDefaultType' => $this->snippetType])
                ->setParent($this->buildViewName(ViewTypes::ADD)),
        );

        $viewCollection->add(
            $this->viewBuilderFactory
                ->createFormViewBuilder($this->buildViewName(ViewTypes::EDIT) . '.content', '/content')
                ->setResourceKey(SnippetInterface::RESOURCE_KEY)
                ->setFormKey('snippet')
                ->setTabTitle('sulu_admin.content')
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
                ->createResourceTabViewBuilder($this->buildViewName(ViewTypes::ADD), '/' . $this->snippetType . '-snippets/:locale/add')
                ->setResourceKey(SnippetInterface::RESOURCE_KEY)
                ->addLocales($locales)
                ->setBackView($this->buildViewName(ViewTypes::LIST)),
        );
        $viewCollection->add(
            $this->viewBuilderFactory
                ->createResourceTabViewBuilder($this->buildViewName(ViewTypes::EDIT), '/' . $this->snippetType . '-snippets/:locale/:id')
                ->setResourceKey(SnippetInterface::RESOURCE_KEY)
                ->addRouterAttributesToBackView(['locale'])
                ->setBackView($this->buildViewName(ViewTypes::LIST))
                ->addLocales($locales)
                ->setTitleProperty('title'),
        );
    }

    private function buildExcerptView(ViewCollection $viewCollection): void
    {
        if (
            $this->securityChecker->hasPermission($this->buildSecurityContext(), PermissionTypes::EDIT) === false
            || $this->securityChecker->hasPermission($this->buildSecurityContext(PermissionTypes::CONTEXT_EXCERPT), PermissionTypes::EDIT) === false
        ) {
            return;
        }

        $forms = [];
        foreach ($this->excerptForms as $key => $tag) {
            if (is_subclass_of(SnippetDimensionContent::class, $tag['instanceOf']) || SnippetDimensionContent::class === $tag['instanceOf']) {
                $forms[] = $key;
            }
        }

        $viewCollection->add(
            $this->viewBuilderFactory
                ->createFormViewBuilder($this->buildViewName(ViewTypes::EXCERPT), '/excerpt')
                ->addMetadataRequestParameters(['forms' => $forms])
                ->setResourceKey(SnippetInterface::RESOURCE_KEY)
                ->setFormKey('content_excerpt')
                ->setTabTitle('sulu_content.taxonomies')
                ->addToolbarActions(
                    $this->formToolbarBuilder->build(
                        $this->buildSecurityContext(),
                        $this->buildViewName(ViewTypes::EXCERPT),
                    ),
                )
                ->setTabOrder(40)
                ->setTitleVisible(true)
                ->setParent($this->buildViewName(ViewTypes::EDIT)),
        );
    }

    private function buildSettingsView(ViewCollection $viewCollection): void
    {
        if (
            $this->securityChecker->hasPermission($this->buildSecurityContext(), PermissionTypes::EDIT) === false
            || $this->securityChecker->hasPermission($this->buildSecurityContext(PermissionTypes::CONTEXT_SETTINGS), PermissionTypes::EDIT) === false
        ) {
            return;
        }

        $forms = [];
        foreach ($this->settingsForms as $key => $tag) {
            if (is_subclass_of(SnippetDimensionContent::class, $tag['instanceOf']) || SnippetDimensionContent::class === $tag['instanceOf']) {
                $forms[] = $key;
            }
        }

        $viewCollection->add(
            $this->viewBuilderFactory
                ->createFormViewBuilder($this->buildViewName(ViewTypes::SETTINGS), '/settings')
                ->addMetadataRequestParameters(['forms' => $forms])
                ->setResourceKey(SnippetInterface::RESOURCE_KEY)
                ->setFormKey('content_settings')
                ->setTabTitle('sulu_content.settings')
                ->setTitleVisible(true)
                ->addToolbarActions(
                    $this->formToolbarBuilder->build(
                        $this->buildSecurityContext(),
                        $this->buildViewName(ViewTypes::SETTINGS),
                    ),
                )
                ->setTabOrder(50)
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
                ->setResourceKey(SnippetInterface::RESOURCE_KEY)
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
                        SnippetInterface::RESOURCE_KEY,
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
                        SnippetInterface::RESOURCE_KEY,
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
        return 'snippet_manager.' . $this->snippetType . ($context !== PermissionTypes::CONTEXT_SNIPPETS ? '_' . $context : '');
    }

    private function buildViewName(string $type): string
    {
        $suffix = $type;
        if (in_array($type, [ViewTypes::EDIT, ViewTypes::ADD], true)) {
            $suffix .= '_tabs';
        }

        return 'sulu_snippet_manager_' . $this->snippetType . '.' . $suffix;
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
                    $this->buildSecurityContext(PermissionTypes::CONTEXT_EXCERPT) => [
                        PermissionTypes::EDIT,
                    ],
                    $this->buildSecurityContext(PermissionTypes::CONTEXT_SETTINGS) => [
                        PermissionTypes::EDIT,
                    ],
                    $this->buildSecurityContext(PermissionTypes::CONTEXT_INSIGHTS) => [
                        PermissionTypes::EDIT,
                    ],
                    $this->buildSecurityContext(PermissionTypes::CONTEXT_DEFAULT_SNIPPETS) => [
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
