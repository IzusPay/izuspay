import { Controller, Get, Post, Body, Patch, Param, Delete, UseGuards } from '@nestjs/common';
import { UsersService } from './users.service';
import { CreateUserDto } from './dto/create-user.dto';
import { UpdateUserDto } from './dto/update-user.dto';
import { ApiBearerAuth, ApiTags, ApiOperation } from '@nestjs/swagger';
import { AuthGuard } from '@nestjs/passport';
import { PermissionsGuard } from '../../common/guards/permissions.guard';
import { RequirePermission } from '../../common/decorators/require-permission.decorator';

@ApiTags('Users')
@ApiBearerAuth()
@UseGuards(AuthGuard('jwt'), PermissionsGuard)
@Controller('users')
export class UsersController {
  constructor(private readonly usersService: UsersService) {}

  @Post()
  @RequirePermission('users', 'create')
  @ApiOperation({ summary: 'Create a new user' })
  create(@Body() createUserDto: CreateUserDto) {
    return this.usersService.create(createUserDto);
  }

  @Get()
  @RequirePermission('users', 'read')
  @ApiOperation({ summary: 'List all users' })
  findAll() {
    // Note: UsersService.findAll is not implemented yet in the service I read earlier,
    // only findOne, findByEmail, etc. I need to add it or use repository directly in service.
    // But for now let's assume I'll add it to service.
    return this.usersService.findAll();
  }

  @Get(':id')
  @RequirePermission('users', 'detail')
  @ApiOperation({ summary: 'Get user details' })
  findOne(@Param('id') id: string) {
    return this.usersService.findOne(id);
  }

  @Patch(':id')
  @RequirePermission('users', 'update')
  @ApiOperation({ summary: 'Update user' })
  update(@Param('id') id: string, @Body() updateUserDto: UpdateUserDto) {
    return this.usersService.update(id, updateUserDto);
  }

  @Delete(':id')
  @RequirePermission('users', 'delete')
  @ApiOperation({ summary: 'Delete user' })
  remove(@Param('id') id: string) {
    return this.usersService.remove(id);
  }
}
