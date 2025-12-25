import { Injectable, Logger, BadRequestException } from '@nestjs/common';
import { PaymentProvider, PaymentTransactionResult, WebhookResult } from '../interfaces/payment-provider.interface';
import { Sale } from '../../sales/sale.entity';
import { Gateway } from '../../gateways/gateway.entity';

@Injectable()
export class WitetecProvider implements PaymentProvider {
  private readonly logger = new Logger(WitetecProvider.name);

  async createTransaction(sale: Sale, gateway: Gateway): Promise<PaymentTransactionResult> {
    this.logger.log(`Creating transaction for sale ${sale.id} using Witetec`);
    
    const credentials = this.getCredentials(gateway);
    const apiUrl = (credentials['WITETEC_API_URL'] || gateway.apiUrl || '').replace(/\/$/, '');
    const apiKey = credentials['WITETEC_API_KEY'];
    const accessToken = credentials['WITETEC_ACCESS_TOKEN'];

    if (!apiUrl || !apiKey) {
      throw new BadRequestException('Missing Witetec credentials (API_URL or API_KEY)');
    }

    const headers: any = {
      'Content-Type': 'application/json',
      'x-api-key': apiKey,
    };

    const bearer = accessToken || apiKey;
    if (bearer) {
      headers['Authorization'] = `Bearer ${bearer}`;
    }

    const amount = Math.round(Number(sale.amount) * 100);
    const document = sale.payerDocument.replace(/\D/g, '');
    const documentType = document.length === 11 ? 'CPF' : 'CNPJ';
    
    const productTitle = sale.product?.name || `Produto #${sale.productId || 'N/A'}`;
    const productPrice = amount;

    const payload = {
      amount,
      method: 'PIX',
      customer: {
        name: sale.payerName,
        email: sale.payerEmail,
        phone: sale.payerPhone?.replace(/\D/g, '') || '',
        documentType,
        document,
      },
      items: [
        {
          title: productTitle,
          amount: productPrice,
          quantity: 1,
          tangible: false,
          externalRef: sale.productId || sale.id,
        }
      ],
      postbackUrl: `${process.env.API_BASE_URL || 'http://localhost:3000'}/webhooks/gateways/witetec`,
    };

    try {
      const response = await fetch(`${apiUrl}/transactions`, {
        method: 'POST',
        headers,
        body: JSON.stringify(payload),
      });

      if (!response.ok) {
        const errorText = await response.text();
        this.logger.error(`Witetec Error: ${response.status} - ${errorText}`);
        throw new Error(`Witetec API Error: ${errorText}`);
      }

      const data: any = await response.json();
      const transactionData = data.data || data;

      const transactionId = transactionData.id || transactionData.data?.id;
      let pixCode = null;

      if (transactionData.pix && transactionData.pix.copyPaste) {
        pixCode = transactionData.pix.copyPaste;
      }

      const qrCodeUrl = pixCode 
        ? `https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=${encodeURIComponent(pixCode)}`
        : '';

      return {
        transactionId,
        qrCode: pixCode,
        qrCodeUrl,
        rawResponse: transactionData,
      };

    } catch (error) {
      this.logger.error(`Witetec Transaction Failed: ${error.message}`);
      throw error;
    }
  }

  async processWebhook(payload: any): Promise<WebhookResult> {
    this.logger.log('Processing Witetec Webhook', payload);
    
    const data = payload.data || payload;

    return {
      transactionId: data.id || data.transactionId,
      status: data.status === 'PAID' ? 'PAID' : (data.status === 'FAILED' ? 'FAILED' : 'PENDING'),
      metadata: payload,
    };
  }

  private getCredentials(gateway: Gateway): Record<string, string> {
    const creds: Record<string, string> = {};
    if (gateway.params) {
      gateway.params.forEach(p => {
        creds[p.label] = p.value;
      });
    }
    return creds;
  }
}
