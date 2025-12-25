import { Controller, Post, Param, Body, Logger, NotFoundException } from '@nestjs/common';
import { InjectRepository } from '@nestjs/typeorm';
import { Repository } from 'typeorm';
import { PaymentStrategyFactory } from './services/payment-strategy.factory';
import { ApiTags, ApiOperation } from '@nestjs/swagger';
import { Public } from '../../common/decorators/public.decorator';
import { Sale, SaleStatus } from '../sales/sale.entity';
import { WebhooksService } from '../webhooks/webhooks.service';

@ApiTags('Gateway Webhooks (Inbound)')
@Controller('webhooks/gateways')
export class GatewayWebhooksController {
  private readonly logger = new Logger(GatewayWebhooksController.name);

  constructor(
    private readonly paymentStrategyFactory: PaymentStrategyFactory,
    @InjectRepository(Sale)
    private readonly salesRepository: Repository<Sale>,
    private readonly webhooksService: WebhooksService,
  ) {}

  @Public()
  @Post(':provider')
  @ApiOperation({ summary: 'Receive webhook from Payment Gateway (BrPag, Witetec, etc.)' })
  async handleWebhook(@Param('provider') providerSlug: string, @Body() payload: any) {
    this.logger.log(`Received webhook for provider: ${providerSlug}`);
    
    const provider = this.paymentStrategyFactory.getProviderBySlug(providerSlug);
    
    // 1. Normalize Payload
    const result = await provider.processWebhook(payload);

    if (!result.transactionId) {
      this.logger.warn('Webhook payload missing transactionId');
      return { received: true, ignored: true };
    }

    // 2. Find Sale
    const sale = await this.salesRepository.findOne({
      where: { transactionId: result.transactionId },
    });

    if (!sale) {
      this.logger.warn(`Sale not found for transactionId: ${result.transactionId}`);
      throw new NotFoundException('Sale not found');
    }

    // 3. Update Status
    if (result.status === 'PAID' && sale.status !== SaleStatus.PAID) {
      sale.status = SaleStatus.PAID;
      await this.salesRepository.save(sale);
      
      // 4. Dispatch to Company (using specialized logic)
      await this.webhooksService.notifySalePaid(sale);
      this.logger.log(`Sale ${sale.id} marked as PAID via webhook`);
    } else if (result.status === 'FAILED' && sale.status !== SaleStatus.FAILED) {
      sale.status = SaleStatus.FAILED;
      await this.salesRepository.save(sale);

      await this.webhooksService.dispatch(sale.companyId, 'sale.failed', sale);
      this.logger.log(`Sale ${sale.id} marked as FAILED via webhook`);
    } else {
      this.logger.log(`Sale ${sale.id} status unchanged: ${sale.status}`);
    }

    return { received: true };
  }
}
