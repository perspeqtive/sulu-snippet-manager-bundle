<?php

declare(strict_types=1);

namespace PERSPEQTIVE\SuluSnippetManagerBundle\ToolbarActions;

use PERSPEQTIVE\SuluSnippetManagerBundle\Admin\FormToolbarBuilderInterface;
use Sulu\Bundle\AdminBundle\Admin\View\DropdownToolbarAction;
use Sulu\Bundle\AdminBundle\Admin\View\ToolbarAction;
use Sulu\Component\Security\Authorization\PermissionTypes;
use Sulu\Component\Security\Authorization\SecurityCheckerInterface;
use function str_ends_with;

readonly class FormToolbarBuilder implements FormToolbarBuilderInterface
{
    public function __construct(private SecurityCheckerInterface $securityChecker)
    {
    }

    /**
     * @return ToolbarAction[]
     */
    public function build(string $securityContext, string $view): array
    {
        $formToolbarActions = [];
        $formToolbarActions = $this->buildEditAction($securityContext, $formToolbarActions);

        return $this->buildDeleteAction($view, $securityContext, $formToolbarActions);
    }

    /**
     * @param ToolbarAction[] $formToolbarActions
     *
     * @return ToolbarAction[]
     */
    private function buildEditAction(string $securityContext, array $formToolbarActions): array
    {
        if ($this->securityChecker->hasPermission($securityContext, PermissionTypes::EDIT)) {
            $formToolbarActions[] = new ToolbarAction('sulu_admin.save');
            $editDropdownToolbarActions = [new ToolbarAction('sulu_admin.copy', [
                'visible_condition' => '!!id',
            ])];
            $formToolbarActions[] = new DropdownToolbarAction(
                'sulu_admin.edit',
                'su-pen',
                $editDropdownToolbarActions,
            );
        }

        return $formToolbarActions;
    }

    /**
     * @param ToolbarAction[] $formToolbarActions
     *
     * @return ToolbarAction[]
     */
    private function buildDeleteAction(string $view, string $securityContext, array $formToolbarActions): array
    {
        if ($this->isEditView($view)
            && $this->securityChecker->hasPermission($securityContext, PermissionTypes::DELETE)) {
            $formToolbarActions[] = new ToolbarAction('sulu_admin.delete');
        }

        return $formToolbarActions;
    }

    private function isEditView(string $view): bool
    {
        return str_ends_with($view, '.edit');
    }
}