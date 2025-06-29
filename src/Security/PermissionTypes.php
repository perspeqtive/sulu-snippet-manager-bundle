<?php

declare(strict_types=1);

namespace PERSPEQTIVE\SuluSnippetManagerBundle\Security;

use Sulu\Component\Security\Authorization\PermissionTypes as BasePermissionTypes;

final readonly class PermissionTypes
{
    public const VIEW = BasePermissionTypes::VIEW;

    public const ADD = BasePermissionTypes::ADD;

    public const EDIT = BasePermissionTypes::EDIT;

    public const DELETE = BasePermissionTypes::DELETE;

    public const ARCHIVE = BasePermissionTypes::ARCHIVE;

    public const LIVE = BasePermissionTypes::LIVE;

    public const SECURITY = BasePermissionTypes::SECURITY;

    public const CONTEXT_SNIPPETS = '';

    public const CONTEXT_TAXONOMIES = 'taxonomies';

    public const CONTEXT_INSIGHTS = 'insights';

    public const CONTEXT_DEFAULT_SNIPPETS = 'default_snippets';
}
