<?php

declare(strict_types=1);

namespace PERSPEQTIVE\SuluSnippetManagerBundle\Tests\Mocks;

use PERSPEQTIVE\SuluSnippetManagerBundle\Admin\FormToolbarBuilderInterface;

class MockFormToolbarBuilder implements FormToolbarBuilderInterface
{
    public function __construct(public array $toolbars = ['save', 'delete'])
    {
    }

    public function build(string $securityContext, string $view): array
    {
        return $this->toolbars;
    }
}
