import { Module, Global } from '@nestjs/common';
import { TypeOrmModule } from '@nestjs/typeorm';
import { GatewaysModule } from '../gateways/gateways.module';
import { WebhooksModule } from '../webhooks/webhooks.module';
import { Sale } from '../sales/sale.entity';
import { PaymentStrategyFactory } from './services/payment-strategy.factory';
import { CodiguzProvider } from './providers/codiguz.provider';
import { WitetecProvider } from './providers/witetec.provider';
import { GatewayWebhooksController } from './gateway-webhooks.controller';

@Global()
@Module({
  imports: [
    GatewaysModule,
    WebhooksModule,
    TypeOrmModule.forFeature([Sale]),
  ],
  controllers: [GatewayWebhooksController],
  providers: [
    PaymentStrategyFactory,
    CodiguzProvider,
    WitetecProvider,
  ],
  exports: [PaymentStrategyFactory],
})
export class PaymentsModule {}
