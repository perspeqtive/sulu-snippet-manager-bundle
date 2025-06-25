<?php

declare(strict_types=1);

namespace PERSPEQTIVE\SuluSnippetManagerBundle\Tests\Mocks\Sulu;

use Sulu\Component\Localization\Provider\LocalizationProviderInterface;

class MockLocalizationProvider implements LocalizationProviderInterface
{
    public function __construct(public array $locales = ['de', 'en'])
    {
    }

    public function getAllLocalizations(): array
    {
        return [];
    }

    public function getAllLocales(): array
    {
        return $this->locales;
    }
}
