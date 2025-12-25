import { Injectable, NotFoundException } from '@nestjs/common';
import { GatewaysService } from '../../gateways/gateways.service';
import { PaymentProvider } from '../interfaces/payment-provider.interface';
import { CodiguzProvider } from '../providers/codiguz.provider';
import { WitetecProvider } from '../providers/witetec.provider';

@Injectable()
export class PaymentStrategyFactory {
  constructor(
    private readonly gatewaysService: GatewaysService,
    private readonly codiguzProvider: CodiguzProvider,
    private readonly witetecProvider: WitetecProvider,
  ) {}

  /**
   * Selects the active gateway with the highest priority and returns the corresponding provider.
   */
  async getActiveProvider() {
    const gateway = await this.gatewaysService.findHighestPriorityActiveGateway();

    if (!gateway) {
      throw new NotFoundException('No active payment gateway found');
    }

    const provider = this.getProviderBySlug(gateway.type?.slug);

    return { provider, gateway };
  }

  getProviderBySlug(slug: string): PaymentProvider {
    switch (slug?.toLowerCase()) {
      case 'brpag':
      case 'codiguz':
        return this.codiguzProvider;
      case 'witetec':
        return this.witetecProvider;
      default:
        // Default to Codiguz if unknown or not set (fallback)
        // Or throw error
        return this.codiguzProvider; 
    }
  }
}
