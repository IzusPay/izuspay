import { Module } from '@nestjs/common';
import { APP_INTERCEPTOR } from '@nestjs/core';
import { ConfigModule, ConfigService } from '@nestjs/config';
import { TypeOrmModule } from '@nestjs/typeorm';
import { AppController } from './app.controller';
import { AppService } from './app.service';
import { WebhooksModule } from './modules/webhooks/webhooks.module';
import { AuthModule } from './modules/auth/auth.module';
import { UsersModule } from './modules/users/users.module';
import { CompaniesModule } from './modules/companies/companies.module';
import { DocumentsModule } from './modules/documents/documents.module';
import { AddressesModule } from './modules/addresses/addresses.module';
import { BankAccountsModule } from './modules/bank-accounts/bank-accounts.module';
import { WithdrawalsModule } from './modules/withdrawals/withdrawals.module';
import { AuditLogsModule } from './modules/audit-logs/audit-logs.module';
import { AuditInterceptor } from './common/interceptors/audit.interceptor';

import { SystemFeesModule } from './modules/system-fees/system-fees.module';
import { FinancialModule } from './modules/financial/financial.module';
import { GatewaysModule } from './modules/gateways/gateways.module';
import { BannersModule } from './modules/banners/banners.module';
import { ProductsModule } from './modules/products/products.module';
import { SalesModule } from './modules/sales/sales.module';
import { CustomersModule } from './modules/customers/customers.module';
import { ApiKeysModule } from './modules/api-keys/api-keys.module';
import { PaymentsModule } from './modules/payments/payments.module';
import { SystemSettingsModule } from './modules/system-settings/system-settings.module';
import { AccessControlModule } from './modules/access-control/access-control.module';

@Module({
  imports: [
    ConfigModule.forRoot({
      isGlobal: true,
    }),
    TypeOrmModule.forRootAsync({
      name: 'default',
      imports: [ConfigModule],
      useFactory: (configService: ConfigService) => ({
        type: 'postgres',
        host: configService.get<string>('DB_HOST'),
        port: configService.get<number>('DB_PORT'),
        username: configService.get<string>('DB_USERNAME'),
        password: configService.get<string>('DB_PASSWORD'),
        database: configService.get<string>('DB_DATABASE'),
        autoLoadEntities: true,
        synchronize: configService.get<boolean>('DB_SYNCHRONIZE', false), // Recommended: false for production
      }),
      inject: [ConfigService],
    }),
    WebhooksModule,
    AuthModule,
    UsersModule,
    CompaniesModule,
    DocumentsModule,
    AddressesModule,
    BankAccountsModule,
    WithdrawalsModule,
    AuditLogsModule,
    SystemFeesModule,
    FinancialModule,
    GatewaysModule,
    BannersModule,
    ProductsModule,
    SalesModule,
    CustomersModule,
    ApiKeysModule,
    PaymentsModule,
    SystemSettingsModule,
    AccessControlModule,
  ],
  controllers: [AppController],
  providers: [
    AppService,
    {
      provide: APP_INTERCEPTOR,
      useClass: AuditInterceptor,
    },
  ],
})
export class AppModule {}
