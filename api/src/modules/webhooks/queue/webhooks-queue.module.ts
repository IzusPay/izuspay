import { Module, forwardRef } from '@nestjs/common';
import { ClientsModule, Transport } from '@nestjs/microservices';
import { WebhooksQueueService } from './webhooks-queue.service';
import { WebhooksQueueConsumer } from './webhooks-queue.consumer'; // Consumer for processing queue items
import { ConfigModule, ConfigService } from '@nestjs/config';
import { WebhooksModule } from '../webhooks.module';

@Module({
  imports: [
    ConfigModule,
    forwardRef(() => WebhooksModule),
    ClientsModule.registerAsync([
      {
        name: 'WEBHOOK_SERVICE',
        imports: [ConfigModule],
        useFactory: async (configService: ConfigService) => ({
          transport: Transport.RMQ,
          options: {
            urls: [configService.get<string>('RABBITMQ_URI') || 'amqp://localhost:5672'],
            queue: 'webhooks_queue',
            queueOptions: {
              durable: true,
            },
          },
        }),
        inject: [ConfigService],
      },
    ]),
  ],
  providers: [WebhooksQueueService, WebhooksQueueConsumer],
  exports: [WebhooksQueueService],
})
export class WebhooksQueueModule {}
