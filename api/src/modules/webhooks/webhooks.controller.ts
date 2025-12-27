import { Controller, Get, Post, Body, Param, Delete, UseGuards, Request, Put } from '@nestjs/common';
import { WebhooksService } from './webhooks.service';
import { CreateWebhookDto } from './dto/create-webhook.dto';
import { UpdateWebhookDto } from './dto/update-webhook.dto';
import { ApiTags, ApiOperation, ApiBearerAuth } from '@nestjs/swagger';
import { CombinedAuthGuard } from '../auth/guards/combined-auth.guard';
import { Roles } from '../../common/decorators/roles.decorator';
import { Role } from '../../common/enums/role.enum';
import { AuditLogsService } from '../audit-logs/audit-logs.service';

@ApiTags('Webhooks')
@Controller('webhooks')
@UseGuards(CombinedAuthGuard)
@ApiBearerAuth()
export class WebhooksController {
  constructor(
    private readonly webhooksService: WebhooksService,
    private readonly auditLogsService: AuditLogsService
  ) {}

  @Post()
  @ApiOperation({ summary: 'Register a new webhook' })
  create(@Request() req: any, @Body() createWebhookDto: CreateWebhookDto) {
    return this.webhooksService.create(req.user.companyId, createWebhookDto);
  }

  @Get()
  @ApiOperation({ summary: 'List all webhooks' })
  findAll(@Request() req: any) {
    return this.webhooksService.findAllByCompany(req.user.companyId);
  }

  @Put(':id')
  @ApiOperation({ summary: 'Update a webhook' })
  update(@Request() req: any, @Param('id') id: string, @Body() updateWebhookDto: UpdateWebhookDto) {
    return this.webhooksService.update(id, req.user.companyId, updateWebhookDto);
  }

  @Delete(':id')
  @ApiOperation({ summary: 'Delete a webhook' })
  remove(@Request() req: any, @Param('id') id: string) {
    return this.webhooksService.remove(id, req.user.companyId);
  }

  @Get('logs')
  @Roles(Role.Admin)
  @ApiOperation({ summary: 'List all webhook logs (Admin)' })
  findAllLogs() {
    return this.webhooksService.findAllLogs();
  }

  @Post('logs/:id/resend')
  @Roles(Role.Admin)
  @ApiOperation({ summary: 'Resend a webhook log (Admin)' })
  async resendLog(@Request() req: any, @Param('id') id: string) {
    const result = await this.webhooksService.resendLog(id);
    await this.auditLogsService.log(
        'resend_webhook',
        `Resent webhook log ${id}`,
        req.ip,
        req.user?.userId,
        result.companyId
    );
    return result;
  }
}
