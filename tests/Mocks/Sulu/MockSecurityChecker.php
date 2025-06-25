<?php

declare(strict_types=1);

namespace PERSPEQTIVE\SuluSnippetManagerBundle\Tests\Mocks\Sulu;

use Sulu\Component\Security\Authorization\SecurityCheckerInterface;

class MockSecurityChecker implements SecurityCheckerInterface
{
    public string $subjectName = '';

    public function __construct(public array $hasPermission = ['*' => true])
    {
    }

    public function checkPermission($subject, $permission)
    {
    }

    public function hasPermission($subject, $permission): bool
    {
        $this->subjectName = $subject;

        return (isset($this->hasPermission[$subject][$permission]) && $this->hasPermission[$subject][$permission] === true)
            || (isset($this->hasPermission[$subject]['*']) && $this->hasPermission[$subject]['*'] === true)
            || (isset($this->hasPermission['*']) && $this->hasPermission['*'] === true);
    }
}
