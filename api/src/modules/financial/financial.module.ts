import { Module, Global } from '@nestjs/common';
import { TypeOrmModule } from '@nestjs/typeorm';
import { FinancialService } from './financial.service';
import { FinancialController } from './financial.controller';
import { SystemProfit } from './system-profit.entity';

@Global()
@Module({
  imports: [TypeOrmModule.forFeature([SystemProfit])],
  controllers: [FinancialController],
  providers: [FinancialService],
  exports: [FinancialService],
})
export class FinancialModule {}
