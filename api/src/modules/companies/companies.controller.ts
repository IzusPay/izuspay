import { Controller, Get, Post, Body, Patch, Param, Delete, UseGuards, Request } from '@nestjs/common';
import { CompaniesService } from './companies.service';
import { CreateCompanyDto } from './dto/create-company.dto';
import { UpdateCompanyDto } from './dto/update-company.dto';
import { RegisterCompanyDto } from './dto/register-company.dto';
import { UpdateWebhookSettingsDto } from './dto/update-webhook-settings.dto';
import { ApiTags, ApiOperation, ApiResponse, ApiBearerAuth } from '@nestjs/swagger';
import { AuthGuard } from '@nestjs/passport';
import { RolesGuard } from '../../common/guards/roles.guard';
import { Roles } from '../../common/decorators/roles.decorator';
import { Role } from '../../common/enums/role.enum';
import { Public } from '../../common/decorators/public.decorator';
import { AuditLogsService } from '../audit-logs/audit-logs.service';

@ApiTags('Companies')
@Controller('companies')
export class CompaniesController {
  constructor(
    private readonly companiesService: CompaniesService,
    private readonly auditLogsService: AuditLogsService
  ) {}

  @Post('register')
  @Public() // Public endpoint for registration
  @ApiOperation({ summary: 'Register new company (Seller) + Admin User + Address' })
  @ApiResponse({ status: 201, description: 'Company registered successfully.' })
  register(@Body() registerDto: RegisterCompanyDto) {
    return this.companiesService.register(registerDto);
  }

  @Post()
  @ApiBearerAuth()
  @UseGuards(AuthGuard('jwt'), RolesGuard)
  @Roles(Role.Admin)
  @ApiOperation({ summary: 'Create company manually (Admin)' })
  create(@Body() createCompanyDto: CreateCompanyDto) {
    return this.companiesService.create(createCompanyDto);
  }

  @Get()
  @ApiBearerAuth()
  @UseGuards(AuthGuard('jwt'), RolesGuard)
  @Roles(Role.Admin)
  @ApiOperation({ summary: 'List all companies (Admin)' })
  findAll() {
    return this.companiesService.findAll();
  }

  @Get(':id')
  @ApiBearerAuth()
  @UseGuards(AuthGuard('jwt'), RolesGuard)
  @Roles(Role.Admin, Role.Client)
  @ApiOperation({ summary: 'Get company by ID' })
  findOne(@Param('id') id: string) {
    return this.companiesService.findOne(id);
  }

  @Patch(':id')
  @ApiBearerAuth()
  @UseGuards(AuthGuard('jwt'), RolesGuard)
  @Roles(Role.Admin, Role.Client)
  @ApiOperation({ summary: 'Update company' })
  update(@Param('id') id: string, @Body() updateCompanyDto: UpdateCompanyDto) {
    return this.companiesService.update(id, updateCompanyDto);
  }

  @Patch(':id/webhook-settings')
  @ApiBearerAuth()
  @UseGuards(AuthGuard('jwt'), RolesGuard)
  @Roles(Role.Admin)
  @ApiOperation({ summary: 'Update company webhook skip settings (Admin)' })
  async updateWebhookSettings(@Request() req: any, @Param('id') id: string, @Body() body: UpdateWebhookSettingsDto) {
    const result = await this.companiesService.updateWebhookSettings(id, body.interval);
    await this.auditLogsService.log(
        'update_webhook_settings',
        `Updated webhook skip interval to ${body.interval} for company ${id}`,
        req.ip,
        req.user?.userId,
        id // Target company ID
    );
    return result;
  }

  @Delete(':id')
  @ApiBearerAuth()
  @UseGuards(AuthGuard('jwt'), RolesGuard)
  @Roles(Role.Admin)
  @ApiOperation({ summary: 'Remove company (Admin)' })
  remove(@Param('id') id: string) {
    return this.companiesService.remove(id);
  }
}
