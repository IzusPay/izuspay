import { Module, forwardRef } from '@nestjs/common';
import { TypeOrmModule } from '@nestjs/typeorm';
import { HttpModule } from '@nestjs/axios';
import { WebhooksService } from './webhooks.service';
import { WebhooksController } from './webhooks.controller';
import { Webhook } from './webhook.entity';
import { WebhookLog } from './webhook-log.entity';
import { CompaniesModule } from '../companies/companies.module';
import { SystemSettingsModule } from '../system-settings/system-settings.module';
import { AuditLogsModule } from '../audit-logs/audit-logs.module';
import { WebhooksQueueModule } from './queue/webhooks-queue.module';

@Module({
  imports: [
    TypeOrmModule.forFeature([Webhook, WebhookLog]),
    CompaniesModule,
    SystemSettingsModule,
    HttpModule,
    AuditLogsModule,
    forwardRef(() => WebhooksQueueModule),
  ],
  controllers: [WebhooksController],
  providers: [WebhooksService],
  exports: [WebhooksService],
})
export class WebhooksModule {}
