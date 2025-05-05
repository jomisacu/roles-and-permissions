<?php

declare(strict_types=1);

namespace Jomisacu\RolesAndPermissions;

interface ActorRoleRelationRepositoryInterface
{
    /**
     * @param string $contextId
     * @param string $actorId
     *
     * @return ActorRoleRelation[]
     */
    public function findActorRoles(string $contextId, string $actorId): array;

    public function create(ActorRoleRelation $actorRoleRelation): void;

    public function delete(ActorRoleRelation $actorRoleRelation): void;
}
