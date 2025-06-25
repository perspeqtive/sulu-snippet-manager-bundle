<?php

declare(strict_types=1);

namespace PERSPEQTIVE\SuluSnippetManagerBundle\Tests\Mocks\Sulu;

use Sulu\Component\Security\Authorization\AccessControl\AccessControlManagerInterface;
use Sulu\Component\Security\Authorization\SecurityCondition;

class MockAccessControlManager implements AccessControlManagerInterface
{
    public function __construct(public array $result = ['sulu.global.snippets' => ['view' => true, 'edit' => false]])
    {
    }

    public function setPermissions($type, $identifier, $permissions, bool $inherit = false): array
    {
        return ['setPermissions'];
    }

    public function getPermissions($type, $identifier): array
    {
        return ['getPermissions'];
    }

    public function getUserPermissions(SecurityCondition $securityCondition, $user): array
    {
        return $this->result[$securityCondition->getSecurityContext()] ?? [];
    }

    public function getUserPermissionByArray($locale, $securityContext, $objectPermissionsByRole, $user, $system = null): array
    {
        return ['getUserPermissionByArray'];
    }
}
