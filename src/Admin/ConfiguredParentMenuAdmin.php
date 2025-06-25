<?php

declare(strict_types=1);

namespace PERSPEQTIVE\SuluSnippetManagerBundle\Admin;

use Sulu\Bundle\AdminBundle\Admin\Admin;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItem;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItemCollection;

class ConfiguredParentMenuAdmin extends Admin
{
    public function __construct(
        private readonly string $navigationTitle,
        private readonly int $position = 40,
        private readonly string $icon = 'su-snippet',
    ) {
    }

    public function configureNavigationItems(NavigationItemCollection $navigationItemCollection): void
    {
        $configurationItem = new NavigationItem($this->navigationTitle);
        $configurationItem->setPosition($this->position);
        $configurationItem->setIcon($this->icon);
        $navigationItemCollection->add($configurationItem);
    }

    public static function getPriority(): int
    {
        return 100;
    }
}