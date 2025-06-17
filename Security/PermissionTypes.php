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

    public const TAXONOMIES = 'taxonomies';

    public const INSIGHTS = 'insights';
}
