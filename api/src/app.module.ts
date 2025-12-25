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
import { LegacyModule } from './modules/legacy/legacy.module';
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

@Module({
  imports: [
    ConfigModule.forRoot({
      isGlobal: true,
    }),
    TypeOrmModule.forRootAsync({
      name: 'default',
      imports: [ConfigModule],
      useFactory: (configService: ConfigService) => ({
        type: 'mysql',
        host: configService.get<string>('DB_HOST'),
        port: configService.get<number>('DB_PORT'),
        username: configService.get<string>('DB_USERNAME'),
        password: configService.get<string>('DB_PASSWORD'),
        database: configService.get<string>('DB_DATABASE'),
        entities: [
          __dirname + '/modules/companies/**/*.entity{.ts,.js}',
          __dirname + '/modules/users/**/*.entity{.ts,.js}',
          __dirname + '/modules/addresses/**/*.entity{.ts,.js}',
          __dirname + '/modules/documents/**/*.entity{.ts,.js}',
          __dirname + '/modules/bank-accounts/**/*.entity{.ts,.js}',
          __dirname + '/modules/withdrawals/**/*.entity{.ts,.js}',
          __dirname + '/modules/audit-logs/**/*.entity{.ts,.js}',
          __dirname + '/modules/system-fees/**/*.entity{.ts,.js}',
          __dirname + '/modules/financial/**/*.entity{.ts,.js}',
          __dirname + '/modules/gateways/**/*.entity{.ts,.js}',
          __dirname + '/modules/banners/**/*.entity{.ts,.js}',
          __dirname + '/modules/products/**/*.entity{.ts,.js}',
          __dirname + '/modules/sales/**/*.entity{.ts,.js}',
          __dirname + '/modules/customers/**/*.entity{.ts,.js}',
          __dirname + '/modules/api-keys/**/*.entity{.ts,.js}',
          __dirname + '/modules/webhooks/**/*.entity{.ts,.js}',
          __dirname + '/modules/system-settings/**/*.entity{.ts,.js}',
        ],
        synchronize: true, // Auto-create tables for the new DB
      }),
      inject: [ConfigService],
    }),
    TypeOrmModule.forRootAsync({
      name: 'legacy',
      imports: [ConfigModule],
      useFactory: (configService: ConfigService) => ({
        type: 'mysql',
        host: configService.get<string>('LEGACY_DB_HOST'),
        port: configService.get<number>('LEGACY_DB_PORT'),
        username: configService.get<string>('LEGACY_DB_USERNAME'),
        password: configService.get<string>('LEGACY_DB_PASSWORD'),
        database: configService.get<string>('LEGACY_DB_DATABASE'),
        entities: [__dirname + '/modules/legacy/entities/*.entity{.ts,.js}'],
        synchronize: false, // NEVER sync legacy DB
      }),
      inject: [ConfigService],
    }),
    WebhooksModule,
    AuthModule,
    UsersModule,
    CompaniesModule,
    DocumentsModule,
    AddressesModule,
    LegacyModule,
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
