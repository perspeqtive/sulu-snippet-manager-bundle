<?php

declare(strict_types=1);

namespace PERSPEQTIVE\SuluSnippetManagerBundle\Tests\Mocks;

use PERSPEQTIVE\SuluSnippetManagerBundle\Admin\ListToolbarBuilderInterface;

class MockListToolbarBuilder implements ListToolbarBuilderInterface
{
    public function __construct(public array $toolbars = ['save', 'delete'])
    {
    }

    public function build(string $securityContext): array
    {
        return $this->toolbars;
    }
}
