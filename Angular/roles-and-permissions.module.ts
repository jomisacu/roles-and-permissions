import { NgModule } from '@angular/core';

import {
  CanAllDirective,
  CanAnyDirective,
  CanDirective,
  CantAllDirective,
  CantAnyDirective,
  CantDirective,
} from './permission-visibility.directives';

export const ROLES_AND_PERMISSIONS_DIRECTIVES = [
  CanDirective,
  CanAnyDirective,
  CanAllDirective,
  CantDirective,
  CantAnyDirective,
  CantAllDirective,
];

@NgModule({
  declarations: ROLES_AND_PERMISSIONS_DIRECTIVES,
  exports: ROLES_AND_PERMISSIONS_DIRECTIVES,
})
export class RolesAndPermissionsAngularModule {}
