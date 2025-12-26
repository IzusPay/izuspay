import { SetMetadata } from '@nestjs/common';

export const PERMISSIONS_KEY = 'permissions';

export type PermissionAction = 'create' | 'read' | 'update' | 'delete' | 'detail';

export const RequirePermission = (module: string, action: PermissionAction) => 
  SetMetadata(PERMISSIONS_KEY, { module, action });
