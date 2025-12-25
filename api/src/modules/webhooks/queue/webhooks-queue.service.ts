import { Injectable, Inject } from '@nestjs/common';
import { ClientProxy } from '@nestjs/microservices';

@Injectable()
export class WebhooksQueueService {
  constructor(@Inject('WEBHOOK_SERVICE') private client: ClientProxy) {}

  async queueWebhook(webhookLogId: string) {
    // Send a message pattern 'process_webhook' with the log ID
    this.client.emit('process_webhook', { webhookLogId });
  }
}
