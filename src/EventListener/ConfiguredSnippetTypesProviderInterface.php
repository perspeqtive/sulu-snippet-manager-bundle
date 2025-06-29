<?php

declare(strict_types=1);

namespace PERSPEQTIVE\SuluSnippetManagerBundle\EventListener;

interface ConfiguredSnippetTypesProviderInterface
{
    /**
     * @return string[]
     */
    public function getConfiguredSnippetTypes(): array;
}
