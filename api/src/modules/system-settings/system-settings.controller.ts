import { Controller, Get, Post, Body, UseGuards } from '@nestjs/common';
import { SystemSettingsService } from './system-settings.service';
import { ApiTags, ApiBearerAuth, ApiOperation } from '@nestjs/swagger';
import { CombinedAuthGuard } from '../auth/guards/combined-auth.guard';
import { Roles } from '../../common/decorators/roles.decorator';
import { Role } from '../../common/enums/role.enum';

@ApiTags('System Settings')
@Controller('system-settings')
@UseGuards(CombinedAuthGuard)
@ApiBearerAuth()
export class SystemSettingsController {
  constructor(private readonly settingsService: SystemSettingsService) {}

  @Get()
  @Roles(Role.Admin)
  @ApiOperation({ summary: 'List all system settings' })
  findAll() {
    return this.settingsService.getAll();
  }

  @Post()
  @Roles(Role.Admin)
  @ApiOperation({ summary: 'Update or create a system setting' })
  update(@Body() body: { key: string; value: string; description?: string }) {
    return this.settingsService.set(body.key, body.value, body.description);
  }
}
