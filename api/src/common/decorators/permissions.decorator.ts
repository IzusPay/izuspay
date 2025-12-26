import { SetMetadata } from '@nestjs/common';

export type PermissionAction = 'create' | 'read' | 'update' | 'delete' | 'detail';

export const PERMISSIONS_KEY = 'permissions';
export const RequirePermissions = (moduleKey: string, action: PermissionAction) =>
  SetMetadata(PERMISSIONS_KEY, { moduleKey, action });
