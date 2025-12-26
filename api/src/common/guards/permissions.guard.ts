import { Injectable, CanActivate, ExecutionContext } from '@nestjs/common';
import { Reflector } from '@nestjs/core';
import { PERMISSIONS_KEY, PermissionAction } from '../decorators/require-permission.decorator';
import { UsersService } from '../../modules/users/users.service';

@Injectable()
export class PermissionsGuard implements CanActivate {
  constructor(
    private reflector: Reflector,
    private usersService: UsersService,
  ) {}

  async canActivate(context: ExecutionContext): Promise<boolean> {
    const requiredPermission = this.reflector.getAllAndOverride<{ module: string; action: PermissionAction }>(
      PERMISSIONS_KEY,
      [context.getHandler(), context.getClass()],
    );

    if (!requiredPermission) {
      return true;
    }

    const request = context.switchToHttp().getRequest();
    const user = request.user;

    if (!user || !user.id) {
      return false;
    }

    // 1. Check for Super Admin bypass (Legacy or ACL Role)
    if (user.role === 'admin') {
        return true;
    }

    // 2. Load User with ACL Permissions
    const userWithPermissions = await this.usersService.findByIdWithPermissions(user.id);
    
    if (!userWithPermissions || !userWithPermissions.accessRole) {
        return false; // No ACL role assigned
    }

    // 3. Find Permission for the requested module
    const permission = userWithPermissions.accessRole.permissions.find(
        (p) => p.module.key === requiredPermission.module
    );

    if (!permission) {
        return false; // No permission record for this module
    }

    // 4. Check specific action
    switch (requiredPermission.action) {
        case 'create': return permission.canCreate;
        case 'read': return permission.canRead;
        case 'update': return permission.canUpdate;
        case 'delete': return permission.canDelete;
        case 'detail': return permission.canDetail;
        default: return false;
    }
  }
}
