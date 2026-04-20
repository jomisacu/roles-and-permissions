import { Injectable } from '@angular/core';
import { Observable, Subject } from 'rxjs';

import {
  RolesAndPermissionsActorPermissionRelation,
  RolesAndPermissionsActorRoleRelation,
  RolesAndPermissionsCheckScope,
  RolesAndPermissionsPermissionCheck,
  RolesAndPermissionsPermissionListCheck,
  RolesAndPermissionsPermissionRelationBase,
  RolesAndPermissionsRolePermissionRelation,
  RolesAndPermissionsScope,
  RolesAndPermissionsSnapshot,
} from './roles-and-permissions.models';
import { RolesAndPermissionsResourceMatcher } from './resource-matcher';

@Injectable({ providedIn: 'root' })
export class RolesAndPermissionsService {
  private readonly changesSubject = new Subject<void>();
  private readonly resourceMatcher = new RolesAndPermissionsResourceMatcher();

  readonly changes$: Observable<void> = this.changesSubject.asObservable();

  private snapshot: RolesAndPermissionsSnapshot = this.emptySnapshot();
  private scope: RolesAndPermissionsScope | null = null;

  setSnapshot(snapshot: RolesAndPermissionsSnapshot): void {
    this.snapshot = {
      actorRoleRelations: snapshot.actorRoleRelations.map((relation) => ({ ...relation })),
      actorPermissionRelations: snapshot.actorPermissionRelations.map((relation) => ({ ...relation })),
      rolePermissionRelations: snapshot.rolePermissionRelations.map((relation) => ({ ...relation })),
    };

    this.emitChanges();
  }

  clearSnapshot(): void {
    this.snapshot = this.emptySnapshot();
    this.emitChanges();
  }

  setScope(scope: RolesAndPermissionsScope): void {
    this.scope = { ...scope };
    this.emitChanges();
  }

  clearScope(): void {
    this.scope = null;
    this.emitChanges();
  }

  clear(): void {
    this.snapshot = this.emptySnapshot();
    this.scope = null;
    this.emitChanges();
  }

  can(permissionId: string, resource: string, scope?: RolesAndPermissionsCheckScope): boolean;
  can(request: RolesAndPermissionsPermissionCheck): boolean;
  can(
    permissionIdOrRequest: string | RolesAndPermissionsPermissionCheck,
    resource?: string,
    scope: RolesAndPermissionsCheckScope = {},
  ): boolean {
    const request = this.normalizePermissionCheck(permissionIdOrRequest, resource, scope);
    const resolvedScope = this.resolveScope(request);

    if (resolvedScope === null || request.resource === '') {
      return false;
    }

    return this.permissionAllowed(
      this.findPermissionRelations(resolvedScope.contextId, resolvedScope.actorId),
      request.permissionId,
      request.resource,
    );
  }

  cant(permissionId: string, resource: string, scope?: RolesAndPermissionsCheckScope): boolean;
  cant(request: RolesAndPermissionsPermissionCheck): boolean;
  cant(
    permissionIdOrRequest: string | RolesAndPermissionsPermissionCheck,
    resource?: string,
    scope: RolesAndPermissionsCheckScope = {},
  ): boolean {
    const request = this.normalizePermissionCheck(permissionIdOrRequest, resource, scope);
    const resolvedScope = this.resolveScope(request);

    if (resolvedScope === null || request.resource === '') {
      return false;
    }

    return !this.permissionAllowed(
      this.findPermissionRelations(resolvedScope.contextId, resolvedScope.actorId),
      request.permissionId,
      request.resource,
    );
  }

  canAny(permissionIds: string[], resource: string, scope?: RolesAndPermissionsCheckScope): boolean;
  canAny(request: RolesAndPermissionsPermissionListCheck): boolean;
  canAny(
    permissionIdsOrRequest: string[] | RolesAndPermissionsPermissionListCheck,
    resource?: string,
    scope: RolesAndPermissionsCheckScope = {},
  ): boolean {
    const request = this.normalizePermissionListCheck(permissionIdsOrRequest, resource, scope);
    const resolvedScope = this.resolveScope(request);

    if (resolvedScope === null || request.resource === '' || request.permissionIds.length === 0) {
      return false;
    }

    const permissionRelations = this.findPermissionRelations(resolvedScope.contextId, resolvedScope.actorId);

    return request.permissionIds.some((permissionId) => this.permissionAllowed(permissionRelations, permissionId, request.resource));
  }

  cantAny(permissionIds: string[], resource: string, scope?: RolesAndPermissionsCheckScope): boolean;
  cantAny(request: RolesAndPermissionsPermissionListCheck): boolean;
  cantAny(
    permissionIdsOrRequest: string[] | RolesAndPermissionsPermissionListCheck,
    resource?: string,
    scope: RolesAndPermissionsCheckScope = {},
  ): boolean {
    const request = this.normalizePermissionListCheck(permissionIdsOrRequest, resource, scope);
    const resolvedScope = this.resolveScope(request);

    if (resolvedScope === null || request.resource === '') {
      return false;
    }

    if (request.permissionIds.length === 0) {
      return true;
    }

    const permissionRelations = this.findPermissionRelations(resolvedScope.contextId, resolvedScope.actorId);

    return !request.permissionIds.some((permissionId) => this.permissionAllowed(permissionRelations, permissionId, request.resource));
  }

  canAll(permissionIds: string[], resource: string, scope?: RolesAndPermissionsCheckScope): boolean;
  canAll(request: RolesAndPermissionsPermissionListCheck): boolean;
  canAll(
    permissionIdsOrRequest: string[] | RolesAndPermissionsPermissionListCheck,
    resource?: string,
    scope: RolesAndPermissionsCheckScope = {},
  ): boolean {
    const request = this.normalizePermissionListCheck(permissionIdsOrRequest, resource, scope);
    const resolvedScope = this.resolveScope(request);

    if (request.permissionIds.length === 0) {
      return true;
    }

    if (resolvedScope === null || request.resource === '') {
      return false;
    }

    const permissionRelations = this.findPermissionRelations(resolvedScope.contextId, resolvedScope.actorId);

    return request.permissionIds.every((permissionId) => this.permissionAllowed(permissionRelations, permissionId, request.resource));
  }

  cantAll(permissionIds: string[], resource: string, scope?: RolesAndPermissionsCheckScope): boolean;
  cantAll(request: RolesAndPermissionsPermissionListCheck): boolean;
  cantAll(
    permissionIdsOrRequest: string[] | RolesAndPermissionsPermissionListCheck,
    resource?: string,
    scope: RolesAndPermissionsCheckScope = {},
  ): boolean {
    const request = this.normalizePermissionListCheck(permissionIdsOrRequest, resource, scope);
    const resolvedScope = this.resolveScope(request);

    if (request.permissionIds.length === 0) {
      return false;
    }

    if (resolvedScope === null || request.resource === '') {
      return false;
    }

    const permissionRelations = this.findPermissionRelations(resolvedScope.contextId, resolvedScope.actorId);

    return !request.permissionIds.every((permissionId) => this.permissionAllowed(permissionRelations, permissionId, request.resource));
  }

  private normalizePermissionCheck(
    permissionIdOrRequest: string | RolesAndPermissionsPermissionCheck,
    resource?: string,
    scope: RolesAndPermissionsCheckScope = {},
  ): RolesAndPermissionsPermissionCheck {
    if (typeof permissionIdOrRequest === 'string') {
      return {
        actorId: scope.actorId,
        contextId: scope.contextId,
        permissionId: permissionIdOrRequest,
        resource: resource || '',
      };
    }

    return {
      actorId: permissionIdOrRequest.actorId,
      contextId: permissionIdOrRequest.contextId,
      permissionId: permissionIdOrRequest.permissionId,
      resource: permissionIdOrRequest.resource,
    };
  }

  private normalizePermissionListCheck(
    permissionIdsOrRequest: string[] | RolesAndPermissionsPermissionListCheck,
    resource?: string,
    scope: RolesAndPermissionsCheckScope = {},
  ): RolesAndPermissionsPermissionListCheck {
    if (Array.isArray(permissionIdsOrRequest)) {
      return {
        actorId: scope.actorId,
        contextId: scope.contextId,
        permissionIds: permissionIdsOrRequest.slice(),
        resource: resource || '',
      };
    }

    return {
      actorId: permissionIdsOrRequest.actorId,
      contextId: permissionIdsOrRequest.contextId,
      permissionIds: permissionIdsOrRequest.permissionIds.slice(),
      resource: permissionIdsOrRequest.resource,
    };
  }

  private resolveScope(scope: RolesAndPermissionsCheckScope): RolesAndPermissionsScope | null {
    const contextId = scope.contextId || (this.scope ? this.scope.contextId : undefined);
    const actorId = scope.actorId || (this.scope ? this.scope.actorId : undefined);

    if (!contextId || !actorId) {
      return null;
    }

    return { contextId, actorId };
  }

  private findPermissionRelations(contextId: string, actorId: string): RolesAndPermissionsPermissionRelationBase[] {
    const actorRoles = this.snapshot.actorRoleRelations.filter(
      (relation: RolesAndPermissionsActorRoleRelation) => relation.contextId === contextId && relation.actorId === actorId,
    );
    const assignedRoleIds = actorRoles.map((relation) => relation.roleId);

    const rolePermissions: RolesAndPermissionsPermissionRelationBase[] = this.snapshot.rolePermissionRelations.filter(
      (relation: RolesAndPermissionsRolePermissionRelation) =>
        relation.contextId === contextId && assignedRoleIds.indexOf(relation.roleId) !== -1,
    );

    const actorPermissions: RolesAndPermissionsPermissionRelationBase[] = this.snapshot.actorPermissionRelations.filter(
      (relation: RolesAndPermissionsActorPermissionRelation) => relation.contextId === contextId && relation.actorId === actorId,
    );

    return rolePermissions.concat(actorPermissions);
  }

  private permissionAllowed(
    permissionRelations: RolesAndPermissionsPermissionRelationBase[],
    permissionId: string,
    resource: string,
  ): boolean {
    if (this.permissionNegated(permissionRelations, permissionId, resource)) {
      return false;
    }

    return this.permissionGranted(permissionRelations, permissionId, resource);
  }

  private permissionNegated(
    permissionRelations: RolesAndPermissionsPermissionRelationBase[],
    permissionId: string,
    resource: string,
  ): boolean {
    return permissionRelations.some(
      (permissionRelation) =>
        permissionRelation.permissionId === permissionId
        && this.resourceMatcher.match(permissionRelation.resource, resource)
        && permissionRelation.negated,
    );
  }

  private permissionGranted(
    permissionRelations: RolesAndPermissionsPermissionRelationBase[],
    permissionId: string,
    resource: string,
  ): boolean {
    return permissionRelations.some(
      (permissionRelation) =>
        permissionRelation.permissionId === permissionId
        && this.resourceMatcher.match(permissionRelation.resource, resource)
        && !permissionRelation.negated,
    );
  }

  private emitChanges(): void {
    this.changesSubject.next();
  }

  private emptySnapshot(): RolesAndPermissionsSnapshot {
    return {
      actorRoleRelations: [],
      actorPermissionRelations: [],
      rolePermissionRelations: [],
    };
  }
}
