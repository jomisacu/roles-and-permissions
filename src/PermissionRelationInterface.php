<?php

declare(strict_types=1);

namespace Jomisacu\RolesAndPermissions;

interface PermissionRelationInterface
{
    public function getContextId(): string;

    public function getPermissionId(): string;

    public function getResource(): string;

    public function isNegated(): bool;
}
