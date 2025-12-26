import { Injectable, CanActivate, ExecutionContext } from '@nestjs/common';
import { Reflector } from '@nestjs/core';
import { PERMISSIONS_KEY, PermissionAction } from '../decorators/permissions.decorator';
import { UsersService } from '../../modules/users/users.service';

@Injectable()
export class PermissionsGuard implements CanActivate {
  constructor(private reflector: Reflector, private usersService: UsersService) {}

  async canActivate(context: ExecutionContext): Promise<boolean> {
    const requiredPermission = this.reflector.getAllAndOverride<{ moduleKey: string; action: PermissionAction }>(
      PERMISSIONS_KEY,
      [context.getHandler(), context.getClass()],
    );

    if (!requiredPermission) {
      return true; // No permission required
    }

    const { user } = context.switchToHttp().getRequest();
    if (!user || !user.sub) {
      return false;
    }

    // Fetch full user with Role and Permissions
    // Note: This is expensive. In production, we should cache permissions in Redis or JWT.
    const fullUser = await this.usersService.findByIdWithPermissions(user.sub);

    if (!fullUser || !fullUser.accessRole) {
        // If user has no custom role, fallback to old Admin check?
        // For now, let's assume ACL is strict if decorator is present.
        // However, if we want to allow "Super Admins" to bypass everything:
        if (user.role === 'admin') return true; 
        return false;
    }

    const permission = fullUser.accessRole.permissions.find(
      (p) => p.module.key === requiredPermission.moduleKey,
    );

    if (!permission) {
      return false;
    }

    switch (requiredPermission.action) {
      case 'create':
        return permission.canCreate;
      case 'read':
        return permission.canRead;
      case 'update':
        return permission.canUpdate;
      case 'delete':
        return permission.canDelete;
      case 'detail':
        return permission.canDetail;
      default:
        return false;
    }
  }
}
