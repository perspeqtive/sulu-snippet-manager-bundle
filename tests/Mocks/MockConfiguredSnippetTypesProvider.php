<?php

declare(strict_types=1);

namespace PERSPEQTIVE\SuluSnippetManagerBundle\Tests\Mocks;

use PERSPEQTIVE\SuluSnippetManagerBundle\EventListener\ConfiguredSnippetTypesProviderInterface;

class MockConfiguredSnippetTypesProvider implements ConfiguredSnippetTypesProviderInterface
{
    public function __construct(public array $result = [])
    {
    }

    public function getConfiguredSnippetTypes(): array
    {
        return $this->result;
    }
}
