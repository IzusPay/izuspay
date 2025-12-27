import { Controller, Get, Post, Body, UseGuards, Param, Put } from '@nestjs/common';
import { ApiTags, ApiOperation, ApiBearerAuth } from '@nestjs/swagger';
import { AccessControlService } from './access-control.service';
import { AuthGuard } from '@nestjs/passport';
import { Roles } from '../../common/decorators/roles.decorator';
import { Role } from '../../common/enums/role.enum';
import { RolesGuard } from '../../common/guards/roles.guard';

@ApiTags('Access Control (ACL)')
@ApiBearerAuth()
@UseGuards(AuthGuard('jwt'), RolesGuard)
@Controller('access-control')
export class AccessControlController {
  constructor(private readonly aclService: AccessControlService) {}

  @Get('roles')
  @Roles(Role.Admin)
  @ApiOperation({ summary: 'List all ACL Roles' })
  findAllRoles() {
    return this.aclService.findAllRoles();
  }

  @Post('roles')
  @Roles(Role.Admin)
  @ApiOperation({ summary: 'Create a new ACL Role' })
  createRole(@Body() body: { name: string; description: string }) {
    return this.aclService.createRole(body.name, body.description);
  }

  @Get('modules')
  @ApiOperation({ summary: 'List all System Modules' })
  findAllModules() {
    return this.aclService.findAllModules();
  }

  @Put('roles/:id/permissions')
  @Roles(Role.Admin)
  @ApiOperation({ summary: 'Update Permissions for a Role' })
  updatePermissions(
    @Param('id') roleId: string,
    @Body() body: { permissions: { moduleId: string; actions: any }[] },
  ) {
    return this.aclService.updatePermissions(roleId, body.permissions);
  }

  @Put('modules/:id')
  @Roles(Role.Admin)
  @ApiOperation({ summary: 'Update a Module' })
  updateModule(
    @Param('id') id: string,
    @Body() body: { icon?: string; route?: string },
  ) {
    return this.aclService.updateModule(id, body);
  }
}
