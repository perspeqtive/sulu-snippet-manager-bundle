<?php

declare(strict_types=1);

namespace PERSPEQTIVE\SuluSnippetManagerBundle\Tests\Mocks\Sulu;

use Sulu\Component\Security\Authorization\SecurityCheckerInterface;

class MockSecurityChecker implements SecurityCheckerInterface
{
    public string $subjectName = '';

    public function __construct(public bool $hasPermission = true)
    {
    }

    public function checkPermission($subject, $permission)
    {
    }

    public function hasPermission($subject, $permission): bool
    {
        $this->subjectName = $subject;

        return $this->hasPermission;
    }
}
