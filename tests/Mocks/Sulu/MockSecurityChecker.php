<?php

declare(strict_types=1);

namespace PERSPEQTIVE\SuluSnippetManagerBundle\Tests\Mocks\Sulu;

use Sulu\Component\Security\Authorization\SecurityCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class MockSecurityChecker implements SecurityCheckerInterface
{
    public string $subjectName = '';

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

        return (isset($this->hasPermission[$subject][$permission]) && $this->hasPermission[$subject][$permission] === true)
            || (isset($this->hasPermission[$subject]['*']) && $this->hasPermission[$subject]['*'] === true)
            || (isset($this->hasPermission['*']) && $this->hasPermission['*'] === true);
    }
}
