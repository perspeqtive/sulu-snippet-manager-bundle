<?php

declare(strict_types=1);

namespace PERSPEQTIVE\SuluSnippetManagerBundle\Tests\Mocks;

use Sulu\Component\Security\Authorization\SecurityCheckerInterface;

class MockSecurityChecker implements SecurityCheckerInterface
{

    public string $subjectName = '';

    public function __construct(public bool $hasPermission = true) {}

    /**
     * @inheritDoc
     */
    public function checkPermission($subject, $permission)
    {
        // TODO: Implement checkPermission() method.
    }

    /**
     * @inheritDoc
     */
    public function hasPermission($subject, $permission): bool
    {
        $this->subjectName = $subject;
        return $this->hasPermission;
    }
}