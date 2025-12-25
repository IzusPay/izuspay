import { Controller, Get, Post, Body, UseGuards, Request, Patch, Param } from '@nestjs/common';
import { WithdrawalsService } from './withdrawals.service';
import { RequestWithdrawalDto } from './dto/request-withdrawal.dto';
import { CombinedAuthGuard } from '../auth/guards/combined-auth.guard';
import { ApiTags, ApiBearerAuth } from '@nestjs/swagger';

@ApiTags('withdrawals')
@ApiBearerAuth()
@UseGuards(CombinedAuthGuard)
@Controller('withdrawals')
export class WithdrawalsController {
  constructor(private readonly withdrawalsService: WithdrawalsService) {}

  @Post()
  request(@Request() req: any, @Body() requestWithdrawalDto: RequestWithdrawalDto) {
    return this.withdrawalsService.request(req.user.companyId, requestWithdrawalDto);
  }

  @Get('my-withdrawals')
  findAllMy(@Request() req: any) {
    return this.withdrawalsService.findAllByCompany(req.user.companyId);
  }

  @Get('admin')
  findAllAdmin() {
    return this.withdrawalsService.findAll();
  }

  @Patch(':id/approve')
  approve(@Param('id') id: string) {
    return this.withdrawalsService.approve(id);
  }

  @Patch(':id/reject')
  reject(@Param('id') id: string, @Body('reason') reason: string) {
    return this.withdrawalsService.reject(id, reason);
  }
}
