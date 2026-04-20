# Angular integration

These helpers let Angular templates and TypeScript code evaluate the same permission model used by the backend package.

Important: this is only for UX. The backend must remain the source of truth and must still validate every action.

## What is included

- `RolesAndPermissionsService` for `can`, `canAny`, `canAll` and their inverse checks in TypeScript.
- Structural directives for templates:
  - `*can`
  - `*canAny`
  - `*canAll`
  - `*cant`
  - `*cantAny`
  - `*cantAll`
- `RolesAndPermissionsAngularModule` to declare/export the directives.

## Files

Import from `Angular/index.ts` or copy the folder into your Angular app/library.

## Data expected by the service

The frontend service works with the same relation concepts as the PHP package:

```ts
import { RolesAndPermissionsSnapshot } from './index';

const snapshot: RolesAndPermissionsSnapshot = {
  actorRoleRelations: [
    { contextId: 'tenant-1', actorId: 'user-1', roleId: 'editor' },
  ],
  actorPermissionRelations: [
    { contextId: 'tenant-1', actorId: 'user-1', permissionId: 'publish-posts', resource: 'blog::post::*', negated: false },
  ],
  rolePermissionRelations: [
    { contextId: 'tenant-1', roleId: 'editor', permissionId: 'edit-posts', resource: 'blog::post::*', negated: false },
  ],
};
```

## Service setup

```ts
import { Component } from '@angular/core';
import { RolesAndPermissionsService } from './index';

@Component({
  selector: 'app-post-page',
  templateUrl: './post-page.component.html',
})
export class PostPageComponent {
  constructor(private readonly permissions: RolesAndPermissionsService) {}

  ngOnInit(): void {
    this.permissions.setScope({
      contextId: 'tenant-1',
      actorId: 'user-1',
    });

    this.permissions.setSnapshot({
      actorRoleRelations: [
        { contextId: 'tenant-1', actorId: 'user-1', roleId: 'editor' },
      ],
      actorPermissionRelations: [],
      rolePermissionRelations: [
        { contextId: 'tenant-1', roleId: 'editor', permissionId: 'edit-posts', resource: 'blog::post::*', negated: false },
      ],
    });
  }
}
```

You can omit `contextId` and `actorId` in later checks if you already called `setScope()`.

## TypeScript usage

```ts
const canEdit = this.permissions.can('edit-posts', 'blog::post::123');

const canModerate = this.permissions.canAny(
  ['edit-posts', 'publish-posts'],
  'blog::post::123',
);

const canDoEverything = this.permissions.canAll(
  ['edit-posts', 'publish-posts'],
  'blog::post::123',
);

const cantDelete = this.permissions.cant('delete-posts', 'blog::post::123');
```

You can also pass the scope inline instead of using `setScope()`:

```ts
const canEdit = this.permissions.can('edit-posts', 'blog::post::123', {
  contextId: 'tenant-1',
  actorId: 'user-1',
});
```

## Template usage

Import the module in your Angular module:

```ts
import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RolesAndPermissionsAngularModule } from './roles-and-permissions/Angular';

@NgModule({
  imports: [CommonModule, RolesAndPermissionsAngularModule],
})
export class PostsModule {}
```

Then use the directives in the template:

```html
<button *can="'edit-posts'; resource: 'blog::post::123'">
  Edit post
</button>

<button *canAny="['edit-posts', 'publish-posts']; resource: 'blog::post::123'">
  Moderate post
</button>

<button *canAll="['edit-posts', 'publish-posts']; resource: 'blog::post::123'">
  Publish post
</button>

<p *cant="'delete-posts'; resource: 'blog::post::123'">
  You cannot delete this post.
</p>
```

If you need to override the current scope from the template:

```html
<button
  *can="'edit-posts'; resource: 'blog::post::123'; contextId: tenantId; actorId: actorId"
>
  Edit post
</button>
```

## Notes

- The frontend matcher follows the same wildcard behavior as the backend `ResourceMatcher`.
- Negated permissions override granted permissions, just like in PHP.
- The directives re-render automatically when `setSnapshot()`, `clearSnapshot()`, `setScope()`, `clearScope()`, or `clear()` are called.
