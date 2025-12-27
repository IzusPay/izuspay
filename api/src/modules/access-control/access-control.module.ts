import { Module } from '@nestjs/common';
import { TypeOrmModule } from '@nestjs/typeorm';
import { RoleEntity } from './entities/role.entity';
import { ModuleEntity } from './entities/module.entity';
import { Permission } from './entities/permission.entity';
import { User } from '../users/user.entity';
import { AccessControlService } from './access-control.service';
import { AccessControlController } from './access-control.controller';

@Module({
  imports: [TypeOrmModule.forFeature([RoleEntity, ModuleEntity, Permission, User])],
  controllers: [AccessControlController],
  providers: [AccessControlService],
  exports: [AccessControlService],
})
export class AccessControlModule {}
