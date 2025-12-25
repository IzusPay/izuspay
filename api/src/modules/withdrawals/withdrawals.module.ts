import { Module } from '@nestjs/common';
import { TypeOrmModule } from '@nestjs/typeorm';
import { WithdrawalsService } from './withdrawals.service';
import { WithdrawalsController } from './withdrawals.controller';
import { Withdrawal } from './withdrawal.entity';
import { Company } from '../companies/company.entity';
import { BankAccount } from '../bank-accounts/bank-account.entity';
import { FinancialModule } from '../financial/financial.module';

@Module({
  imports: [
    TypeOrmModule.forFeature([Withdrawal, Company, BankAccount]),
    FinancialModule,
  ],
  controllers: [WithdrawalsController],
  providers: [WithdrawalsService],
})
export class WithdrawalsModule {}
