<?php

declare(strict_types=1);

namespace PERSPEQTIVE\SuluSnippetManagerBundle\Admin;

use Sulu\Bundle\AdminBundle\Admin\View\ToolbarAction;

interface ListToolbarBuilderInterface
{
    /**
     * @return ToolbarAction[]
     */
    public function build(string $securityContext): array;
}
