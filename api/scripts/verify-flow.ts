import { Test, TestingModule } from '@nestjs/testing';
import { AppModule } from '../src/app.module';
import { CompaniesService } from '../src/modules/companies/companies.service';
import { DocumentsService } from '../src/modules/documents/documents.service';
import { ApiKeysService } from '../src/modules/api-keys/api-keys.service';
import { SalesService } from '../src/modules/sales/sales.service';
import { GatewaysService } from '../src/modules/gateways/gateways.service';
import { WebhooksService } from '../src/modules/webhooks/webhooks.service';
import { CompanyStatus } from '../src/modules/companies/company.entity';
import { DocumentType, DocumentStatus } from '../src/modules/documents/company-document.entity';
import { GatewayWebhooksController } from '../src/modules/payments/gateway-webhooks.controller';
import { SaleStatus } from '../src/modules/sales/sale.entity';
import { WebhookLogStatus } from '../src/modules/webhooks/webhook-log.entity';

async function bootstrap() {
  const moduleFixture: TestingModule = await Test.createTestingModule({
    imports: [AppModule],
  }).compile();

  const app = moduleFixture.createNestApplication({
    logger: ['log', 'error', 'warn', 'debug', 'verbose'],
  });
  await app.init();

  const companiesService = app.get(CompaniesService);
  const documentsService = app.get(DocumentsService);
  const apiKeysService = app.get(ApiKeysService);
  const salesService = app.get(SalesService);
  const gatewaysService = app.get(GatewaysService);
  const webhooksService = app.get(WebhooksService);
  const webhookController = app.get(GatewayWebhooksController);

  console.log('--- STARTING FLOW VERIFICATION ---');

  // 1. Setup Gateway (Codiguz)
  console.log('1. Setting up Gateway...');
  const types = await gatewaysService.findAllTypes();
  let codiguzType = types.find((t: any) => t.slug === 'codiguz');
  if (!codiguzType) {
    codiguzType = await gatewaysService.createType('Codiguz', 'codiguz');
  }

  // Check if gateway exists
  let gateway = (await gatewaysService.findAll()).find((g: any) => g.typeId === codiguzType.id);
  if (!gateway) {
      gateway = await gatewaysService.create({
          name: 'Codiguz Primary',
          typeId: codiguzType.id,
          priority: 1,
          isActive: true,
          apiUrl: 'https://api.codiguz.com', // Mock URL
          image: 'https://placehold.co/100',
          transactionFeePercentage: 2.0,
          transactionFeeFixed: 0.50,
          params: [
              { label: 'CODIGUZ_USERNAME', value: 'user_test' },
              { label: 'CODIGUZ_PASSWORD', value: 'pass_test' },
              { label: 'CODIGUZ_API_URL', value: 'https://mock.codiguz.com' }
          ]
      });
  }

  // 2. Register Company
  console.log('2. Registering Company...');
  const uniqueSlug = `test-company-${Date.now()}`;
  const company = await companiesService.register({
    name: 'Test Company',
    slug: uniqueSlug,
    type: 'company',
    document: `12345678000199${Math.floor(Math.random() * 1000)}`, // Random document to avoid duplicate
    phone: '11999999999',
    email: `${uniqueSlug}@example.com`,
    password: 'password123',
    street: 'Rua Teste',
    number: '123',
    neighborhood: 'Centro',
    city: 'São Paulo',
    state: 'SP',
    zipCode: '01001000'
  });

  console.log(`Company created with status: ${company.status}`);
  if (company.status !== CompanyStatus.PENDING_DOCUMENTS) {
      console.error('FAIL: Company should be PENDING_DOCUMENTS');
      process.exit(1);
  }

  // 3. Upload Documents
  console.log('3. Uploading Documents...');
  const docTypes = [
    DocumentType.ID_CARD_FRONT,
    DocumentType.ID_CARD_BACK,
    DocumentType.SOCIAL_CONTRACT,
    DocumentType.SELFIE_WITH_ID,
  ];

  for (const type of docTypes) {
      // Mock file
      const file: any = { path: `/tmp/${type}.jpg` };
      await documentsService.upload(company.id, type, file);
  }

  // 4. Approve Documents
  console.log('4. Approving Documents...');
  const docs = await documentsService.findAllByCompany(company.id);
  for (const doc of docs) {
      await documentsService.approve(doc.id);
  }

  // 5. Verify Activation
  const activeCompany = await companiesService.findOne(company.id);
  console.log(`Company status after approval: ${activeCompany.status}`);
  if (activeCompany.status !== CompanyStatus.ACTIVE) {
      console.error('FAIL: Company should be ACTIVE');
      process.exit(1);
  }

  // 6. Generate API Key
  console.log('6. Generating API Key...');
  const apiKey = await apiKeysService.create(company.id, { name: 'Production Key' });
  console.log(`API Key generated: ${apiKey.prefix}`);

  // 6.5 Setup Webhook and Skip Logic
  console.log('6.5 Setting up Webhooks and Skip Rule...');
  await webhooksService.create(company.id, {
      url: 'https://client-site.com/webhook',
      events: ['sale.paid'],
      description: 'Main Webhook'
  });

  // Skip every 1 sale (Send 1, Skip 1, Send 1...)
  // Logic: if count >= interval, skip.
  // Start: count=0. Interval=1.
  // Sale 1: count=0 < 1. Send. count becomes 1.
  // Sale 2: count=1 >= 1. Skip. count becomes 0.
  // Sale 3: count=0 < 1. Send. count becomes 1.
  await companiesService.updateWebhookSettings(company.id, 1);
  console.log('Webhook skip interval set to 1');

  // 7. Create Sales via API (Loop 3 times)
  console.log('7. Creating 3 Sales via API...');
  const sales = [];

  for (let i = 1; i <= 3; i++) {
      try {
        console.log(`Creating Sale ${i}...`);
        const sale = await salesService.createFromApi(company.id, {
            amount: 100 + i,
            customer: {
                name: `Customer ${i}`,
                email: `customer${i}@example.com`,
                phone: '11988887777',
                document: '11122233344'
            }
        });
        sales.push(sale);
        console.log(`Sale ${i} created. ID: ${sale.id}, TransactionID: ${sale.transactionId}`);

        // Hack: Update transactionId to be unique for testing flow, as Mock Gateway might return duplicate IDs
        const uniqueTxId = `tx_test_${Date.now()}_${i}`;
        await salesService.updateTransactionId(sale.id, uniqueTxId);
        sale.transactionId = uniqueTxId;
        
        // Simulate Webhook for Payment
        if (sale.transactionId) {
            console.log(`Simulating webhook for Sale ${i} (TxID: ${sale.transactionId})...`);
            await webhookController.handleWebhook('codiguz', {
                id: sale.transactionId,
                status: 'PAID', 
            });
        } else {
            console.warn(`Sale ${i} has no transactionId. Skipping webhook.`);
        }
      } catch (err) {
        console.error(`Error creating sale ${i}:`, err.message);
      }
  }

  // 8. Verify Webhook Logs
  console.log('8. Verifying Webhook Logs...');
  // We need to fetch logs. Since we don't have direct access to repo in this script easily without injecting,
  // let's use the controller or service if exposed.
  // We added findAllLogs to WebhooksService but it returns all. Let's filter or just check the last 3.
  const logs = await webhooksService.findAllLogs();
  const companyLogs = logs.filter((l: any) => l.companyId === company.id).sort((a: any, b: any) => a.createdAt.getTime() - b.createdAt.getTime());

  console.log(`Found ${companyLogs.length} logs for company.`);

  if (companyLogs.length !== 3) {
      console.warn(`Expected 3 logs, found ${companyLogs.length}.`);
  }

  // Check statuses
  // Log 1: Should be SUCCESS (or FAILED if fetch failed, but not SKIPPED)
  // Log 2: Should be SKIPPED_BY_RULE
  // Log 3: Should be SUCCESS (or FAILED)

  // Note: Logs might not be strictly ordered by ID if async, but createdAt should be close.
  // Let's map by sale ID.
  const logMap = new Map();
  companyLogs.forEach((l: any) => logMap.set(l.saleId, l));

  const s1 = sales[0];
  const s2 = sales[1];
  const s3 = sales[2];

  const l1 = logMap.get(s1.id);
  const l2 = logMap.get(s2.id);
  const l3 = logMap.get(s3.id);

  console.log(`Sale 1 Log Status: ${l1?.status}`);
  console.log(`Sale 2 Log Status: ${l2?.status}`);
  console.log(`Sale 3 Log Status: ${l3?.status}`);

  if (l1?.status === WebhookLogStatus.SKIPPED_BY_RULE) console.error('FAIL: Sale 1 should NOT be skipped');
  if (l2?.status !== WebhookLogStatus.SKIPPED_BY_RULE) console.error('FAIL: Sale 2 SHOULD be skipped');
  if (l3?.status === WebhookLogStatus.SKIPPED_BY_RULE) console.error('FAIL: Sale 3 should NOT be skipped');

  // 9. Test Force Confirm on Skipped Sale
  console.log('9. Testing Force Confirm on Sale 2...');
  await salesService.forceConfirm(s2.id);

  // Check logs again
  const logsAfter = await webhooksService.findAllLogs();
  const l2Logs = logsAfter.filter((l: any) => l.saleId === s2.id);
  
  console.log(`Sale 2 now has ${l2Logs.length} logs.`);
  const forcedLog = l2Logs.find((l: any) => l.status !== WebhookLogStatus.SKIPPED_BY_RULE);
  
  if (forcedLog) {
      console.log(`SUCCESS: Found forced log with status ${forcedLog.status}`);
  } else {
      console.error('FAIL: Did not find forced log for Sale 2');
  }

  console.log('--- FLOW VERIFICATION COMPLETE ---');
  await app.close();
}

// Mock global fetch for Node environment
global.fetch = (() =>
    Promise.resolve({
        ok: true,
        json: () => Promise.resolve({ data: { id: 'tx_mock_123', pix: { copyPaste: 'pix_code_123' } } }),
        text: () => Promise.resolve('ok'),
    })) as any;

bootstrap();
