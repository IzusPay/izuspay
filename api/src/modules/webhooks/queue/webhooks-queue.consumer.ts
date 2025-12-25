import { Controller, Inject, forwardRef } from '@nestjs/common';
import { EventPattern, Payload, Ctx, RmqContext } from '@nestjs/microservices';
import { WebhooksService } from '../webhooks.service';
import { Injectable } from '@nestjs/common';

@Controller()
export class WebhooksQueueConsumer {
  constructor(
    @Inject(forwardRef(() => WebhooksService))
    private readonly webhooksService: WebhooksService
  ) {}

  @EventPattern('process_webhook')
  async handleWebhook(@Payload() data: { webhookLogId: string }, @Ctx() context: RmqContext) {
    const channel = context.getChannelRef();
    const originalMsg = context.getMessage();

    try {
      console.log(`[Queue] Processing webhook log: ${data.webhookLogId}`);
      await this.webhooksService.resendLog(data.webhookLogId);
      
      // Acknowledge message if successful
      channel.ack(originalMsg);
    } catch (error) {
      console.error(`[Queue] Failed to process webhook log ${data.webhookLogId}`, error);
      // Nack (Negative Acknowledgement) - can requeue or send to Dead Letter Queue
      // false = do not requeue immediately (to avoid infinite loop of instant failures)
      // Ideally, implement a retry strategy or DLQ logic here.
      channel.nack(originalMsg, false, false); 
    }
  }
}
