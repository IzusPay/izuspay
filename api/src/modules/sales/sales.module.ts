import { Module, forwardRef } from '@nestjs/common';
import { TypeOrmModule } from '@nestjs/typeorm';
import { SalesService } from './sales.service';
import { SalesController } from './sales.controller';
import { Sale } from './sale.entity';
import { ProductsModule } from '../products/products.module';
import { CompaniesModule } from '../companies/companies.module';
import { PaymentsModule } from '../payments/payments.module';
import { CustomersModule } from '../customers/customers.module';
import { WebhooksModule } from '../webhooks/webhooks.module';
import { AuditLogsModule } from '../audit-logs/audit-logs.module';

@Module({
  imports: [
    TypeOrmModule.forFeature([Sale]),
    ProductsModule,
    CompaniesModule,
    PaymentsModule,
    CustomersModule,
    forwardRef(() => WebhooksModule),
    AuditLogsModule,
  ],
  controllers: [SalesController],
  providers: [SalesService],
  exports: [SalesService],
})
export class SalesModule {}
