<?php

declare(strict_types=1);

namespace Jomisacu\RolesAndPermissions;

interface ActorPermissionRelationRepositoryInterface
{
    /**
     * @param string $contextId
     * @param string $actorId
     *
     * @return array<ActorPermissionRelation>
     */
    public function findByContextAndActor(string $contextId, string $actorId): array;

    public function create(ActorPermissionRelation $actorPermissionRelation): void;

    public function delete(ActorPermissionRelation $actorPermissionRelation): void;
}
