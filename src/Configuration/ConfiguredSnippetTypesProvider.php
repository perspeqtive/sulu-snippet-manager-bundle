<?php

declare(strict_types=1);

namespace PERSPEQTIVE\SuluSnippetManagerBundle\Configuration;

use PERSPEQTIVE\SuluSnippetManagerBundle\EventListener\ConfiguredSnippetTypesProviderInterface;

use function array_merge;
use function count;
use function is_array;

readonly class ConfiguredSnippetTypesProvider implements ConfiguredSnippetTypesProviderInterface
{
    /**
     * @param array<array{
     *      navigation_title: string,
     *      type: string,
     *      order: int,
     *      icon: string,
     *      children?: array<array-key, array{
     *          navigation_title: string,
     *          type: string,
     *          order: int,
     *          icon: string
     *      }>
     * }> $snippetConfig
     */
    public function __construct(private array $snippetConfig)
    {
    }

    /**
     * @return string[]
     */
    public function getConfiguredSnippetTypes(): array
    {
        return $this->getSnippetTypes($this->snippetConfig);
    }

    /**
     * @param array<array{
     *      navigation_title: string,
     *      type: string,
     *      order: int,
     *      icon: string,
     *      children?: array<array-key, array{
     *          navigation_title: string,
     *          type: string,
     *          order: int,
     *          icon: string
     *      }>
     * }> $configuration
     *
     * @return string[]
     */
    private function getSnippetTypes(array $configuration): array
    {
        $types = [];
        foreach ($configuration as $config) {
            /* @phpstan-ignore identical.alwaysTrue */
            if (isset($config['children']) === true && is_array($config['children']) === true && count($config['children']) > 0) {
                $types[] = $this->getSnippetTypes($config['children']);
                continue;
            }
            $types[] = [$config['type']];
        }

        return array_merge(...$types);
    }
}
