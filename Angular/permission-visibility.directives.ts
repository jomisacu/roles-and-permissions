import { Directive, Input, OnDestroy, TemplateRef, ViewContainerRef } from '@angular/core';
import { Subscription } from 'rxjs';

import { RolesAndPermissionsCheckScope } from './roles-and-permissions.models';
import { RolesAndPermissionsService } from './roles-and-permissions.service';

@Directive()
abstract class PermissionVisibilityDirective implements OnDestroy {
  protected contextId: string | null = null;
  protected actorId: string | null = null;

  private hasView = false;
  private readonly subscription: Subscription;

  constructor(
    protected readonly templateRef: TemplateRef<unknown>,
    protected readonly viewContainer: ViewContainerRef,
    protected readonly permissions: RolesAndPermissionsService,
  ) {
    this.subscription = this.permissions.changes$.subscribe(() => this.render());
  }

  ngOnDestroy(): void {
    this.subscription.unsubscribe();
  }

  protected setContextId(value: string | null | undefined): void {
    this.contextId = value == null ? null : value;
    this.render();
  }

  protected setActorId(value: string | null | undefined): void {
    this.actorId = value == null ? null : value;
    this.render();
  }

  protected permissionScope(): RolesAndPermissionsCheckScope {
    const scope: RolesAndPermissionsCheckScope = {};

    if (this.contextId !== null) {
      scope.contextId = this.contextId;
    }

    if (this.actorId !== null) {
      scope.actorId = this.actorId;
    }

    return scope;
  }

  protected render(): void {
    const shouldDisplay = this.shouldDisplay();

    if (shouldDisplay && !this.hasView) {
      this.viewContainer.createEmbeddedView(this.templateRef);
      this.hasView = true;
      return;
    }

    if (!shouldDisplay && this.hasView) {
      this.viewContainer.clear();
      this.hasView = false;
    }
  }

  protected abstract shouldDisplay(): boolean;
}

@Directive()
abstract class SinglePermissionVisibilityDirective extends PermissionVisibilityDirective {
  protected permissionId: string | null = null;
  protected resource: string | null = null;

  protected setPermissionId(value: string | null | undefined): void {
    this.permissionId = value == null ? null : value;
    this.render();
  }

  protected setResource(value: string | null | undefined): void {
    this.resource = value == null ? null : value;
    this.render();
  }
}

@Directive()
abstract class MultiplePermissionVisibilityDirective extends PermissionVisibilityDirective {
  protected permissionIds: string[] = [];
  protected resource: string | null = null;

  protected setPermissionIds(value: string[] | null | undefined): void {
    this.permissionIds = Array.isArray(value) ? value.slice() : [];
    this.render();
  }

  protected setResource(value: string | null | undefined): void {
    this.resource = value == null ? null : value;
    this.render();
  }
}

@Directive({ selector: '[can]' })
export class CanDirective extends SinglePermissionVisibilityDirective {
  @Input()
  set can(value: string | null | undefined) {
    this.setPermissionId(value);
  }

  @Input()
  set canResource(value: string | null | undefined) {
    this.setResource(value);
  }

  @Input()
  set canContextId(value: string | null | undefined) {
    this.setContextId(value);
  }

  @Input()
  set canActorId(value: string | null | undefined) {
    this.setActorId(value);
  }

  protected shouldDisplay(): boolean {
    if (this.permissionId === null || this.resource === null) {
      return false;
    }

    return this.permissions.can(this.permissionId, this.resource, this.permissionScope());
  }
}

@Directive({ selector: '[cant]' })
export class CantDirective extends SinglePermissionVisibilityDirective {
  @Input()
  set cant(value: string | null | undefined) {
    this.setPermissionId(value);
  }

  @Input()
  set cantResource(value: string | null | undefined) {
    this.setResource(value);
  }

  @Input()
  set cantContextId(value: string | null | undefined) {
    this.setContextId(value);
  }

  @Input()
  set cantActorId(value: string | null | undefined) {
    this.setActorId(value);
  }

  protected shouldDisplay(): boolean {
    if (this.permissionId === null || this.resource === null) {
      return false;
    }

    return this.permissions.cant(this.permissionId, this.resource, this.permissionScope());
  }
}

@Directive({ selector: '[canAny]' })
export class CanAnyDirective extends MultiplePermissionVisibilityDirective {
  @Input()
  set canAny(value: string[] | null | undefined) {
    this.setPermissionIds(value);
  }

  @Input()
  set canAnyResource(value: string | null | undefined) {
    this.setResource(value);
  }

  @Input()
  set canAnyContextId(value: string | null | undefined) {
    this.setContextId(value);
  }

  @Input()
  set canAnyActorId(value: string | null | undefined) {
    this.setActorId(value);
  }

  protected shouldDisplay(): boolean {
    if (this.resource === null) {
      return false;
    }

    return this.permissions.canAny(this.permissionIds, this.resource, this.permissionScope());
  }
}

@Directive({ selector: '[cantAny]' })
export class CantAnyDirective extends MultiplePermissionVisibilityDirective {
  @Input()
  set cantAny(value: string[] | null | undefined) {
    this.setPermissionIds(value);
  }

  @Input()
  set cantAnyResource(value: string | null | undefined) {
    this.setResource(value);
  }

  @Input()
  set cantAnyContextId(value: string | null | undefined) {
    this.setContextId(value);
  }

  @Input()
  set cantAnyActorId(value: string | null | undefined) {
    this.setActorId(value);
  }

  protected shouldDisplay(): boolean {
    if (this.permissionIds.length === 0 || this.resource === null) {
      return false;
    }

    return this.permissions.cantAny(this.permissionIds, this.resource, this.permissionScope());
  }
}

@Directive({ selector: '[canAll]' })
export class CanAllDirective extends MultiplePermissionVisibilityDirective {
  @Input()
  set canAll(value: string[] | null | undefined) {
    this.setPermissionIds(value);
  }

  @Input()
  set canAllResource(value: string | null | undefined) {
    this.setResource(value);
  }

  @Input()
  set canAllContextId(value: string | null | undefined) {
    this.setContextId(value);
  }

  @Input()
  set canAllActorId(value: string | null | undefined) {
    this.setActorId(value);
  }

  protected shouldDisplay(): boolean {
    if (this.resource === null) {
      return false;
    }

    return this.permissions.canAll(this.permissionIds, this.resource, this.permissionScope());
  }
}

@Directive({ selector: '[cantAll]' })
export class CantAllDirective extends MultiplePermissionVisibilityDirective {
  @Input()
  set cantAll(value: string[] | null | undefined) {
    this.setPermissionIds(value);
  }

  @Input()
  set cantAllResource(value: string | null | undefined) {
    this.setResource(value);
  }

  @Input()
  set cantAllContextId(value: string | null | undefined) {
    this.setContextId(value);
  }

  @Input()
  set cantAllActorId(value: string | null | undefined) {
    this.setActorId(value);
  }

  protected shouldDisplay(): boolean {
    if (this.resource === null) {
      return false;
    }

    return this.permissions.cantAll(this.permissionIds, this.resource, this.permissionScope());
  }
}
