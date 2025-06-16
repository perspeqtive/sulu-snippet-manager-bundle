<?php

declare(strict_types=1);

namespace PERSPEQTIVE\SuluSnippetManagerBundle\ToolbarActions;

use PERSPEQTIVE\SuluSnippetManagerBundle\Admin\ListToolbarBuilderInterface;
use Sulu\Bundle\AdminBundle\Admin\View\ToolbarAction;
use Sulu\Component\Security\Authorization\PermissionTypes;
use Sulu\Component\Security\Authorization\SecurityCheckerInterface;

readonly class ListToolbarBuilder implements ListToolbarBuilderInterface
{
    public function __construct(private SecurityCheckerInterface $securityChecker)
    {
    }

    /**
     * @return ToolbarAction[]
     */
    public function build(string $securityContext): array
    {
        $listToolbarActions = [];
        if ($this->securityChecker->hasPermission($securityContext, PermissionTypes::ADD)) {
            $listToolbarActions[] = new ToolbarAction('sulu_admin.add');
        }
        if ($this->securityChecker->hasPermission($securityContext, PermissionTypes::DELETE)) {
            $listToolbarActions[] = new ToolbarAction('sulu_admin.delete');
        }
        if ($this->securityChecker->hasPermission($securityContext, PermissionTypes::VIEW)) {
            $listToolbarActions[] = new ToolbarAction('sulu_admin.export');
        }

        return $listToolbarActions;
    }
}
