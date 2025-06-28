<?php

declare(strict_types=1);

namespace PERSPEQTIVE\SuluSnippetManagerBundle\Tests\Mocks\Sulu;

use Sulu\Bundle\SnippetBundle\Snippet\DefaultSnippetManagerInterface;

class MockDefaultSnippetManager implements DefaultSnippetManagerInterface
{
    public function __construct(public array $typeForArea = [])
    {
    }

    public function save($webspaceKey, $type, $uuid, $locale)
    {
    }

    public function remove($webspaceKey, $type)
    {
    }

    public function load($webspaceKey, $type, $locale)
    {
    }

    public function loadIdentifier($webspaceKey, $type)
    {
    }

    public function isDefault($uuid)
    {
    }

    public function loadType($uuid)
    {
    }

    public function loadWebspaces($uuid)
    {
    }

    public function getTypeForArea(string $area): ?string
    {
        return $this->typeForArea[$area] ?? null;
    }
}
