import { Injectable, Logger, BadRequestException } from '@nestjs/common';
import { PaymentProvider, PaymentTransactionResult, WebhookResult } from '../interfaces/payment-provider.interface';
import { Sale } from '../../sales/sale.entity';
import { Gateway } from '../../gateways/gateway.entity';

@Injectable()
export class CodiguzProvider implements PaymentProvider {
  private readonly logger = new Logger(CodiguzProvider.name);

  async createTransaction(sale: Sale, gateway: Gateway): Promise<PaymentTransactionResult> {
    this.logger.log(`Creating transaction for sale ${sale.id} using Codiguz (Gateway ID: ${gateway.id})`);

    const credentials = this.getCredentials(gateway);
    // Mandatory API URL from Gateway config or params
    const baseUrl = (credentials['CODIGUZ_API_URL'] || gateway.apiUrl || '').replace(/\/$/, '');
    
    if (!baseUrl) {
      throw new BadRequestException('Missing Codiguz API URL (gateway.apiUrl or CODIGUZ_API_URL param is required)');
    }
    
    // Auth
    // User maps labels: CODIGUZ_USERNAME / CODIGUZ_PASSWORD / CODIGUZ_SECRET_KEY / CODIGUZ_COMPANY_ID
    const username = credentials['CODIGUZ_USERNAME'] || credentials['CODIGUZ_SECRET_KEY'];
    const password = credentials['CODIGUZ_PASSWORD'] || credentials['CODIGUZ_COMPANY_ID'];
    
    if (!username || !password) {
      throw new BadRequestException('Missing Codiguz credentials (CODIGUZ_USERNAME/CODIGUZ_SECRET_KEY or CODIGUZ_PASSWORD/CODIGUZ_COMPANY_ID)');
    }

    const basicAuth = Buffer.from(`${username}:${password}`).toString('base64');
    const headers: any = {
      'Authorization': `Basic ${basicAuth}`,
      'Content-Type': 'application/json',
    };
    
    if (credentials['CODIGUZ_API_KEY']) {
      headers['x-api-key'] = credentials['CODIGUZ_API_KEY'];
    }

    // Payload
    const amount = Math.round(Number(sale.amount) * 100); // Convert to cents
    const document = sale.payerDocument.replace(/\D/g, '');
    const documentType = document.length === 11 ? 'CPF' : 'CNPJ';

    // Ensure product title is available or fallback
    const productTitle = sale.product?.name || `Produto #${sale.productId || 'N/A'}`;
    const productPrice = Math.round(Number(sale.amount) * 100);

    const payload = {
      amount,
      paymentMethod: 'PIX',
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
          unitPrice: productPrice,
          quantity: 1,
          tangible: false,
          externalRef: sale.productId || sale.id,
        }
      ],
      postbackUrl: credentials['CODIGUZ_POSTBACK_URL'] || `${process.env.API_BASE_URL || 'http://localhost:3000'}/webhooks/gateways/codiguz`,
    };

    try {
      // Codiguz White Labels usually follow this structure
      const response = await fetch(`${baseUrl}/functions/v1/transactions`, {
        method: 'POST',
        headers,
        body: JSON.stringify(payload),
      });

      if (!response.ok) {
        const errorText = await response.text();
        this.logger.error(`Codiguz Error: ${response.status} - ${errorText}`);
        throw new Error(`Codiguz API Error: ${errorText}`);
      }

      const data: any = await response.json();
      const transactionData = data.data || data;

      const transactionId = transactionData.id;
      let pixCode = null;
      
      // Extract PIX code (logic from PHP)
      if (transactionData.pix && typeof transactionData.pix === 'object') {
        const p = transactionData.pix;
        pixCode = p.copyPaste || p.code || p.copy_paste || p.qrCode || p.qrcode || p.emv;
      }
      if (!pixCode && transactionData.pix_qr_code) {
        pixCode = transactionData.pix_qr_code;
      }

      // Generate QR Code URL (Google Charts fallback if not provided)
      const qrCodeUrl = `https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=${encodeURIComponent(pixCode)}`;

      return {
        transactionId,
        qrCode: pixCode,
        qrCodeUrl,
        rawResponse: transactionData,
      };

    } catch (error) {
      this.logger.error(`Codiguz Transaction Failed: ${error.message}`);
      throw error;
    }
  }

  async processWebhook(payload: any): Promise<WebhookResult> {
    this.logger.log('Processing Codiguz Webhook', payload);
    
    // Payload usually has { id: '...', status: 'PAID' } or { data: { ... } }
    const data = payload.data || payload;
    
    return {
      transactionId: data.id,
      status: data.status === 'PAID' ? 'PAID' : (data.status === 'FAILED' ? 'FAILED' : 'PENDING'),
      metadata: payload,
    };
  }

  private getCredentials(gateway: Gateway): Record<string, string> {
    const creds: Record<string, string> = {};
    if (gateway.params) {
      gateway.params.forEach(p => {
        // We use p.label as the key because user inputs "CODIGUZ_SECRET_KEY" as label
        creds[p.label] = p.value;
      });
    }
    return creds;
  }
}
