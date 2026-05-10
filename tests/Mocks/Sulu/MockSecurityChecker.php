<?php

declare(strict_types=1);

namespace PERSPEQTIVE\SuluSnippetManagerBundle\Tests\Mocks\Sulu;

use Sulu\Component\Security\Authorization\SecurityCheckerInterface;
use Sulu\Component\Security\Authorization\SecurityCondition;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class MockSecurityChecker implements SecurityCheckerInterface
{
    public string|SecurityCondition $subjectName = '';

    public function __construct(
        public array $hasPermission = ['*' => true],
    ) {
    }

    public function checkPermission($subject, $permission): bool
    {
        if ($this->hasPermission($subject, $permission) === true) {
            return true;
        }
        throw new AccessDeniedException();
    }

    public function hasPermission($subject, $permission): bool
    {
        $this->subjectName = $subject;

        $subjectString = $subject instanceof SecurityCondition ? $subject->getSecurityContext() : $subject;

        return (isset($this->hasPermission[$subjectString][$permission]) && $this->hasPermission[$subjectString][$permission] === true)
            || (isset($this->hasPermission[$subjectString]['*']) && $this->hasPermission[$subjectString]['*'] === true)
            || (isset($this->hasPermission['*']) && $this->hasPermission['*'] === true);
    }
}
