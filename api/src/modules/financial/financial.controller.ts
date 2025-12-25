import { Controller, Get, UseGuards } from '@nestjs/common';
import { FinancialService } from './financial.service';
import { JwtAuthGuard } from '../auth/guards/jwt-auth.guard';
import { ApiTags, ApiBearerAuth } from '@nestjs/swagger';

@ApiTags('financial')
@ApiBearerAuth()
@UseGuards(JwtAuthGuard)
@Controller('financial')
export class FinancialController {
  constructor(private readonly financialService: FinancialService) {}

  @Get('profits')
  findAll() {
    return this.financialService.findAll();
  }

  @Get('profits/total')
  getTotal() {
    return this.financialService.getTotalProfit();
  }
}
