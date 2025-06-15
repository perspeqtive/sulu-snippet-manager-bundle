<?php

declare(strict_types=1);

namespace PERSPEQTIVE\SuluSnippetManagerBundle\Admin;

use Sulu\Bundle\AdminBundle\Admin\Admin;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItem;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItemCollection;
use Sulu\Component\Security\Authorization\PermissionTypes;
use Sulu\Component\Security\Authorization\SecurityCheckerInterface;

class ConfiguredParentMenuAdmin extends Admin
{

    public function __construct(
        private SecurityCheckerInterface $securityChecker,
        private readonly string                      $navigationTitle,
        private readonly int                         $position = 40,
        private readonly string                      $icon = 'su-snippet',
    ) {}

    public function configureNavigationItems(NavigationItemCollection $navigationItemCollection): void
    {
        if ($this->securityChecker->hasPermission($this->buildSecurityContext(), PermissionTypes::VIEW) === false) {
            return;
        }
        $configurationItem = new NavigationItem($this->navigationTitle);
        $configurationItem->setPosition($this->position);
        $configurationItem->setIcon($this->icon);
        $navigationItemCollection->add($configurationItem);

    }

    private function buildSecurityContext(): string
    {
        $title = mb_strtolower($this->navigationTitle);
        $title = preg_replace('~[^a-zA-Z0-9-]~', '', $title);
        return  'sulu_snippet_manager_' . $title . '_security_context';
    }

    public function getSecurityContexts(): array
    {
        return [
            'Sulu' => [
                'Website' => [
                    $this->buildSecurityContext() => [
                        PermissionTypes::VIEW,
                    ],
                ],
            ],
        ];
    }

    public static function getPriority(): int {
        return 100;
    }

}