import { Controller, Get, Post, Body, Param, UseGuards, Request } from '@nestjs/common';
import { SalesService } from './sales.service';
import { CreateSaleDto } from './dto/create-sale.dto';
import { CreateSaleApiDto } from './dto/create-sale-api.dto';
import { ApiTags, ApiOperation, ApiBearerAuth, ApiHeader } from '@nestjs/swagger';
import { CombinedAuthGuard } from '../auth/guards/combined-auth.guard';
import { Public } from '../../common/decorators/public.decorator';
import { Roles } from '../../common/decorators/roles.decorator';
import { Role } from '../../common/enums/role.enum';
import { AuditLogsService } from '../audit-logs/audit-logs.service';

@ApiTags('Sales')
@Controller('sales')
export class SalesController {
  constructor(
    private readonly salesService: SalesService,
    private readonly auditLogsService: AuditLogsService
  ) {}

  @Public()
  @Post()
  @ApiOperation({ summary: 'Create a new sale (Public for Checkout)' })
  create(@Body() createSaleDto: CreateSaleDto) {
    return this.salesService.create(createSaleDto);
  }

  @Post('api')
  @UseGuards(CombinedAuthGuard)
  @ApiBearerAuth()
  @ApiHeader({ name: 'x-api-key', description: 'API Key for authentication' })
  @ApiOperation({ summary: 'Create a new sale via API (Server-to-Server)' })
  createFromApi(@Request() req: any, @Body() createSaleApiDto: CreateSaleApiDto) {
    return this.salesService.createFromApi(req.user.companyId, createSaleApiDto);
  }

  @Get()
  @UseGuards(CombinedAuthGuard)
  @ApiBearerAuth()
  @ApiOperation({ summary: 'List all sales for the company (JWT or API Key)' })
  findAll(@Request() req: any) {
    return this.salesService.findAllByCompany(req.user.companyId);
  }

  @Get(':id')
  @UseGuards(CombinedAuthGuard)
  @ApiBearerAuth()
  @ApiOperation({ summary: 'Get sale details' })
  findOne(@Param('id') id: string) {
    return this.salesService.findOne(id);
  }

  @Post(':id/force-confirm')
  @UseGuards(CombinedAuthGuard)
  @ApiBearerAuth()
  @Roles(Role.Admin)
  @ApiOperation({ summary: 'Force confirm sale (Admin)' })
  async forceConfirm(@Request() req: any, @Param('id') id: string) {
    const result = await this.salesService.forceConfirm(id);
    await this.auditLogsService.log(
        'force_confirm_sale',
        `Forced confirmation for sale ${id}`,
        req.ip,
        req.user?.userId,
        result.companyId // Log against the company owning the sale
    );
    return result;
  }
}
