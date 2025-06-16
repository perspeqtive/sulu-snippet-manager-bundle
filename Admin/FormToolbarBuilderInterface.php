<?php

declare(strict_types=1);

namespace PERSPEQTIVE\SuluSnippetManagerBundle\Admin;

use Sulu\Bundle\AdminBundle\Admin\View\ToolbarAction;

interface FormToolbarBuilderInterface
{
    /**
     * @return ToolbarAction[]
     */
    public function build(string $securityContext, string $view): array;
}