import { Injectable, OnModuleInit, Logger } from '@nestjs/common';
import { InjectRepository } from '@nestjs/typeorm';
import { Repository, IsNull } from 'typeorm';
import { RoleEntity } from './entities/role.entity';
import { ModuleEntity } from './entities/module.entity';
import { Permission } from './entities/permission.entity';
import { User } from '../users/user.entity';
import { Role } from '../../common/enums/role.enum';

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
    @InjectRepository(User)
    private userRepository: Repository<User>,
  ) {}

  async onModuleInit() {
    await this.seed();
  }

  async seed() {
    this.logger.log('Checking ACL Seeds...');

    // 1. Seed Modules
    const modules = [
      { key: 'users', name: 'Usuários', icon: 'Users', route: '/users' },
      { key: 'companies', name: 'Empresas', icon: 'Factory', route: '/companies' },
      { key: 'products', name: 'Produtos', icon: 'Package', route: '/products' },
      { key: 'sales', name: 'Vendas', icon: 'ShoppingCart', route: '/sales' },
      { key: 'financial', name: 'Financeiro', icon: 'Building2', route: '/financial' },
      { key: 'gateways', name: 'Gateways', icon: 'CreditCard', route: '/gateways' },
      { key: 'system_settings', name: 'Configurações', icon: 'Settings', route: '/settings' },
      { key: 'access_control', name: 'Controle de Acesso', icon: 'Shield', route: '/acl' },
      { key: 'webhooks', name: 'Webhooks', icon: 'Webhook', route: '/webhooks' },
      { key: 'withdrawals', name: 'Saques', icon: 'Wallet', route: '/withdrawals' },
      { key: 'bank_accounts', name: 'Contas Bancárias', icon: 'Building2', route: '/bank-accounts' },
      { key: 'customers', name: 'Clientes', icon: 'Users2', route: '/customers' },
    ];

    for (const mod of modules) {
      const exists = await this.moduleRepository.findOne({ where: { key: mod.key } });
      if (!exists) {
        await this.moduleRepository.save(mod);
        this.logger.log(`Module seeded: ${mod.name}`);
      } else {
        // Update existing module with new fields (icon/route)
        exists.name = mod.name;
        exists.icon = mod.icon;
        exists.route = mod.route;
        await this.moduleRepository.save(exists);
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
      }

      // 3. Assign Default Permissions (Check for missing ones)
      const allModules = await this.moduleRepository.find();
      
      for (const mod of allModules) {
        let permission = await this.permissionRepository.findOne({
           where: { role: { id: role.id }, module: { id: mod.id } }
        });

        if (!permission) {
          permission = new Permission();
          permission.role = role;
          permission.module = mod;
        }

        // Force update permissions for Super Admin to ensure they are all true
        if (role.name === 'Super Admin') {
            permission.canCreate = true;
            permission.canRead = true;
            permission.canUpdate = true;
            permission.canDelete = true;
            permission.canDetail = true;
            await this.permissionRepository.save(permission);
        } else if (!permission.id) {
             // For other roles, only set initial permissions if creating new
             if (role.name === 'Support Agent') {
                if (['users', 'sales', 'companies', 'webhooks'].includes(mod.key)) {
                    permission.canRead = true;
                    permission.canDetail = true;
                }
             } else if (role.name === 'Finance Manager') {
                if (['financial', 'withdrawals', 'sales', 'bank_accounts'].includes(mod.key)) {
                    permission.canRead = true;
                    permission.canDetail = true;
                    permission.canUpdate = true;
                }
             }
             await this.permissionRepository.save(permission);
        }
      }
    }

    // 4. Migrate Legacy Admin Users (Assign Super Admin Role if missing)
    const superAdminRole = await this.roleRepository.findOne({ where: { name: 'Super Admin' } });
    
    if (superAdminRole) {
      const allAdmins = await this.userRepository.find({
        where: { role: Role.Admin },
        relations: ['accessRole']
      });

      this.logger.log(`Found ${allAdmins.length} admin users to check for ACL migration.`);

      for (const admin of allAdmins) {
        if (!admin.accessRole) {
          admin.accessRole = superAdminRole;
          await this.userRepository.save(admin);
          this.logger.log(`Migrated legacy admin user ${admin.email} to Super Admin ACL role.`);
        } else {
           this.logger.log(`Admin user ${admin.email} already has ACL role: ${admin.accessRole.name}`);
        }
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

  async updateModule(id: string, data: Partial<ModuleEntity>) {
    const module = await this.moduleRepository.findOne({ where: { id } });
    if (!module) throw new Error('Module not found');
    
    Object.assign(module, data);
    return this.moduleRepository.save(module);
  }
}
