export interface RolesAndPermissionsScope {
  contextId: string;
  actorId: string;
}

export interface RolesAndPermissionsPermissionRelationBase {
  contextId: string;
  permissionId: string;
  resource: string;
  negated: boolean;
}

export interface RolesAndPermissionsActorRoleRelation {
  contextId: string;
  actorId: string;
  roleId: string;
}

export interface RolesAndPermissionsActorPermissionRelation extends RolesAndPermissionsPermissionRelationBase {
  actorId: string;
}

export interface RolesAndPermissionsRolePermissionRelation extends RolesAndPermissionsPermissionRelationBase {
  roleId: string;
}

export interface RolesAndPermissionsSnapshot {
  actorRoleRelations: RolesAndPermissionsActorRoleRelation[];
  actorPermissionRelations: RolesAndPermissionsActorPermissionRelation[];
  rolePermissionRelations: RolesAndPermissionsRolePermissionRelation[];
}

export interface RolesAndPermissionsCheckScope {
  contextId?: string;
  actorId?: string;
}

export interface RolesAndPermissionsPermissionCheck extends RolesAndPermissionsCheckScope {
  permissionId: string;
  resource: string;
}

export interface RolesAndPermissionsPermissionListCheck extends RolesAndPermissionsCheckScope {
  permissionIds: string[];
  resource: string;
}
