import { Injectable, OnModuleInit, Logger } from '@nestjs/common';
import { InjectRepository } from '@nestjs/typeorm';
import { Repository } from 'typeorm';
import { RoleEntity } from './entities/role.entity';
import { ModuleEntity } from './entities/module.entity';
import { Permission } from './entities/permission.entity';

@Injectable()
export class AccessControlService implements OnModuleInit {
  private readonly logger = new Logger(AccessControlService.name);

  constructor(
    @InjectRepository(RoleEntity)
    private roleRepository: Repository<RoleEntity>,
    @InjectRepository(ModuleEntity)
    private moduleRepository: Repository<ModuleEntity>,
    @InjectRepository(Permission)
    private permissionRepository: Repository<Permission>,
  ) {}

  async onModuleInit() {
    await this.seed();
  }

  async seed() {
    this.logger.log('Checking ACL Seeds...');

    // 1. Seed Modules
    const modules = [
      { key: 'users', name: 'Users Management' },
      { key: 'companies', name: 'Companies Management' },
      { key: 'products', name: 'Products Management' },
      { key: 'sales', name: 'Sales Management' },
      { key: 'financial', name: 'Financial Management' },
      { key: 'gateways', name: 'Gateways Configuration' },
      { key: 'system_settings', name: 'System Settings' },
      { key: 'access_control', name: 'Access Control (ACL)' },
      { key: 'webhooks', name: 'Webhooks Logs' },
      { key: 'withdrawals', name: 'Withdrawals' },
    ];

    for (const mod of modules) {
      const exists = await this.moduleRepository.findOne({ where: { key: mod.key } });
      if (!exists) {
        await this.moduleRepository.save(mod);
        this.logger.log(`Module seeded: ${mod.name}`);
      }
    }

    // 2. Seed Default Roles
    const roles = [
      { name: 'Super Admin', description: 'Full access to everything' },
      { name: 'Support Agent', description: 'Read-only access to sales and users' },
      { name: 'Finance Manager', description: 'Manage withdrawals and financial data' },
    ];

    for (const roleData of roles) {
      let role = await this.roleRepository.findOne({ where: { name: roleData.name } });
      if (!role) {
        role = await this.roleRepository.save(roleData);
        this.logger.log(`Role seeded: ${role.name}`);

        // 3. Assign Default Permissions
        const allModules = await this.moduleRepository.find();
        
        for (const mod of allModules) {
          const permission = new Permission();
          permission.role = role;
          permission.module = mod;

          if (role.name === 'Super Admin') {
            // Full access
            permission.canCreate = true;
            permission.canRead = true;
            permission.canUpdate = true;
            permission.canDelete = true;
            permission.canDetail = true;
          } else if (role.name === 'Support Agent') {
             // Read only for specific modules
             if (['users', 'sales', 'companies', 'webhooks'].includes(mod.key)) {
                permission.canRead = true;
                permission.canDetail = true;
             }
          } else if (role.name === 'Finance Manager') {
             // Financial access
             if (['financial', 'withdrawals', 'sales'].includes(mod.key)) {
                permission.canRead = true;
                permission.canDetail = true;
                permission.canUpdate = true;
             }
          }

          await this.permissionRepository.save(permission);
        }
        this.logger.log(`Permissions assigned for role: ${role.name}`);
      }
    }
  }

  // Helper methods for Controller
  async findAllRoles() {
    return this.roleRepository.find({ relations: ['permissions', 'permissions.module'] });
  }

  async createRole(name: string, description: string) {
    return this.roleRepository.save({ name, description });
  }

  async findAllModules() {
    return this.moduleRepository.find();
  }

  async updatePermissions(roleId: string, permissions: { moduleId: string; actions: any }[]) {
     // Implementation for updating permissions via API
     // This would iterate over permissions array and update the Permission entity
     for (const p of permissions) {
        let perm = await this.permissionRepository.findOne({ 
            where: { role: { id: roleId }, module: { id: p.moduleId } } 
        });
        
        if (!perm) {
            const newPerm = this.permissionRepository.create({ 
                roleId, 
                moduleId: p.moduleId,
                ...p.actions 
            });
            await this.permissionRepository.save(newPerm);
        } else {
            Object.assign(perm, p.actions);
            await this.permissionRepository.save(perm);
        }
     }
     return this.roleRepository.findOne({ where: { id: roleId }, relations: ['permissions'] });
  }
}
