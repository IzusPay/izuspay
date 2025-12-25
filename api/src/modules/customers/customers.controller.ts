import { Controller, Get, Param, UseGuards, Request } from '@nestjs/common';
import { CustomersService } from './customers.service';
import { ApiTags, ApiOperation, ApiBearerAuth } from '@nestjs/swagger';
import { JwtAuthGuard } from '../auth/guards/jwt-auth.guard';

@ApiTags('Customers')
@Controller('customers')
@UseGuards(JwtAuthGuard)
@ApiBearerAuth()
export class CustomersController {
  constructor(private readonly customersService: CustomersService) {}

  @Get()
  @ApiOperation({ summary: 'List all customers for the authenticated company' })
  findAll(@Request() req: any) {
    return this.customersService.findAllByCompany(req.user.companyId);
  }

  @Get(':id')
  @ApiOperation({ summary: 'Get customer details' })
  findOne(@Param('id') id: string) {
    return this.customersService.findOne(id);
  }
}
