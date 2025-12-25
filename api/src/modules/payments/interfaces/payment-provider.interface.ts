import { Sale } from '../../sales/sale.entity';
import { Gateway } from '../../gateways/gateway.entity';

export interface PaymentTransactionResult {
  transactionId: string;
  qrCode: string;
  qrCodeUrl: string;
  rawResponse: any;
}

export interface WebhookResult {
  transactionId: string;
  status: 'PAID' | 'FAILED' | 'PENDING';
  metadata?: any;
}

export interface PaymentProvider {
  createTransaction(sale: Sale, gateway: Gateway): Promise<PaymentTransactionResult>;
  processWebhook(payload: any): Promise<WebhookResult>;
}
