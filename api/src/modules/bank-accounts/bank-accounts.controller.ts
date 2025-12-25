import { Controller, Get, Post, Body, Patch, Param, Delete, UseGuards, Request } from '@nestjs/common';
import { BankAccountsService } from './bank-accounts.service';
import { CreateBankAccountDto } from './dto/create-bank-account.dto';
import { ApiTags, ApiBearerAuth } from '@nestjs/swagger';
import { JwtAuthGuard } from '../auth/guards/jwt-auth.guard';

@ApiTags('bank-accounts')
@ApiBearerAuth()
@UseGuards(JwtAuthGuard)
@Controller('bank-accounts')
export class BankAccountsController {
  constructor(private readonly bankAccountsService: BankAccountsService) {}

  @Post()
  create(@Request() req: any, @Body() createBankAccountDto: CreateBankAccountDto) {
    return this.bankAccountsService.create(req.user.companyId, createBankAccountDto);
  }

  @Get()
  findAll(@Request() req: any) {
    return this.bankAccountsService.findAllByCompany(req.user.companyId);
  }

  @Get(':id')
  findOne(@Param('id') id: string) {
    return this.bankAccountsService.findOne(id);
  }

  @Patch(':id')
  update(
    @Param('id') id: string,
    @Body() updateBankAccountDto: CreateBankAccountDto,
  ) {
    return this.bankAccountsService.update(id, updateBankAccountDto);
  }

  @Delete(':id')
  remove(@Param('id') id: string) {
    return this.bankAccountsService.remove(id);
  }
}
