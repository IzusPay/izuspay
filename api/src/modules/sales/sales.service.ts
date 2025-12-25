import { Injectable, NotFoundException, BadRequestException, ForbiddenException } from '@nestjs/common';
import { InjectRepository } from '@nestjs/typeorm';
import { Repository, DataSource } from 'typeorm';
import { Sale, SaleStatus } from './sale.entity';
import { CreateSaleDto } from './dto/create-sale.dto';
import { CreateSaleApiDto } from './dto/create-sale-api.dto';
import { ProductsService } from '../products/products.service';
import { CompaniesService } from '../companies/companies.service';
import { CompanyStatus } from '../companies/company.entity';
import { CustomersService } from '../customers/customers.service';
import { PaymentStrategyFactory } from '../payments/services/payment-strategy.factory';
import { WebhooksService } from '../webhooks/webhooks.service';

@Injectable()
export class SalesService {
  constructor(
    @InjectRepository(Sale)
    private salesRepository: Repository<Sale>,
    private productsService: ProductsService,
    private companiesService: CompaniesService,
    private customersService: CustomersService,
    private paymentStrategyFactory: PaymentStrategyFactory,
    private webhooksService: WebhooksService,
    private dataSource: DataSource,
  ) {}

  async createFromApi(companyId: string, createSaleApiDto: CreateSaleApiDto) {
    const { amount, customer: customerDto } = createSaleApiDto;

    // 1. Fetch Company & Verify Status
    const company = await this.companiesService.findOne(companyId);
    if (!company) {
        throw new NotFoundException('Company not found');
    }
    
    if (company.status !== CompanyStatus.ACTIVE) {
        throw new ForbiddenException('Company is not active. Please complete document verification.');
    }

    // 2. Find or Create Customer
    let customer = await this.customersService.findByEmailAndCompany(customerDto.email, company.id);
    if (!customer) {
      customer = await this.customersService.create(company.id, {
        name: customerDto.name,
        email: customerDto.email,
        document: customerDto.document,
        phone: customerDto.phone,
      });
    }

    // 3. Calculate Fees
    const feePercentage = Number(company.transactionFeePercentage) || 0;
    const feeFixed = Number(company.transactionFeeFixed) || 0;

    const grossAmount = Number(amount);
    const totalFee = (grossAmount * (feePercentage / 100)) + feeFixed;
    const netAmount = grossAmount - totalFee;

    // 4. Select Payment Gateway
    const { provider, gateway } = await this.paymentStrategyFactory.getActiveProvider();

    // 5. Create Initial Sale Record (No Product)
    const sale = this.salesRepository.create({
      companyId: company.id,
      productId: undefined,
      customerId: customer.id,
      status: SaleStatus.PENDING,
      amount: grossAmount,
      fee: totalFee,
      netAmount: netAmount,
      paymentMethod: 'PIX',
      payerName: customerDto.name,
      payerEmail: customerDto.email,
      payerDocument: customerDto.document,
      payerPhone: customerDto.phone,
      gatewayId: gateway.id,
    });

    const savedSale = await this.salesRepository.save(sale);

    try {
      // 6. Call Gateway
      const transactionResult = await provider.createTransaction(savedSale, gateway);

      // 7. Update Sale with Gateway Response
      savedSale.transactionId = transactionResult.transactionId;
      savedSale.pixCode = transactionResult.qrCode;
      savedSale.pixQrCode = transactionResult.qrCodeUrl;
      
      return this.salesRepository.save(savedSale);

    } catch (error) {
      // If gateway fails, mark sale as failed
      savedSale.status = SaleStatus.FAILED;
      await this.salesRepository.save(savedSale);
      throw new BadRequestException('Payment gateway error: ' + error.message);
    }
  }

  async create(createSaleDto: CreateSaleDto) {
    const { productId, payerName, payerEmail, payerDocument, payerPhone } = createSaleDto;

    // 1. Fetch Product
    const product = await this.productsService.findOne(productId);
    if (!product.active) {
      throw new BadRequestException('This product is not active');
    }

    // 2. Fetch Company to get Fees
    const company = await this.companiesService.findOne(product.companyId);

    // 2.5 Find or Create Customer
    let customer = await this.customersService.findByEmailAndCompany(payerEmail, company.id);
    if (!customer) {
      customer = await this.customersService.create(company.id, {
        name: payerName,
        email: payerEmail,
        document: payerDocument,
        phone: payerPhone,
      });
    }

    // 3. Calculate Fees
    // Default fees if not set on company (fallback)
    const feePercentage = Number(company.transactionFeePercentage) || 0;
    const feeFixed = Number(company.transactionFeeFixed) || 0;

    const grossAmount = Number(product.amount);
    const totalFee = (grossAmount * (feePercentage / 100)) + feeFixed;
    const netAmount = grossAmount - totalFee;

    // 4. Select Payment Gateway
    const { provider, gateway } = await this.paymentStrategyFactory.getActiveProvider();

    // 5. Create Initial Sale Record
    const sale = this.salesRepository.create({
      companyId: company.id,
      productId: product.id,
      customerId: customer.id,
      status: SaleStatus.PENDING,
      amount: grossAmount,
      fee: totalFee,
      netAmount: netAmount,
      paymentMethod: 'PIX',
      payerName,
      payerEmail,
      payerDocument,
      payerPhone,
      gatewayId: gateway.id,
    });

    const savedSale = await this.salesRepository.save(sale);

    // Attach product to savedSale for provider access
    savedSale.product = product;

    try {
      // 6. Call Gateway
      const transactionResult = await provider.createTransaction(savedSale, gateway);

      // 7. Update Sale with Gateway Response
      savedSale.transactionId = transactionResult.transactionId;
      savedSale.pixCode = transactionResult.qrCode;
      savedSale.pixQrCode = transactionResult.qrCodeUrl;
      
      return this.salesRepository.save(savedSale);

    } catch (error) {
      // If gateway fails, mark sale as failed
      savedSale.status = SaleStatus.FAILED;
      await this.salesRepository.save(savedSale);
      throw new BadRequestException('Payment gateway error: ' + error.message);
    }
  }

  async findOne(id: string) {
    const sale = await this.salesRepository.findOne({
      where: { id },
      relations: ['company', 'product', 'customer'], // Added relations for better context
    });
    if (!sale) {
      throw new NotFoundException('Sale not found');
    }
    return sale;
  }

  async updateTransactionId(id: string, transactionId: string) {
    await this.salesRepository.update({ id }, { transactionId });
  }

  async forceConfirm(id: string) {
    const sale = await this.findOne(id);
    
    if (sale.status === SaleStatus.PAID) {
      // Already paid, but maybe we want to re-trigger webhook?
      // User asked: "se eu quiser reenviar um webhook que está com status de pagamento pendente ele consiga confirmar a compra"
      // But also "ou se eu quiser reenviar um webhook que está com status de pagamento pendente ele consiga confirmar a compra como se tivessemos recebido confirmacao de compra"
      // If it's already paid, we just resend webhook.
      await this.webhooksService.notifySalePaid(sale, true);
      return sale;
    }

    sale.status = SaleStatus.PAID;
    const savedSale = await this.salesRepository.save(sale);
    
    // Trigger Webhooks (Force sending, ignore skip rules)
    await this.webhooksService.notifySalePaid(savedSale, true);

    return savedSale;
  }

  findAllByCompany(companyId: string) {
    return this.salesRepository.find({
      where: { companyId },
      order: { createdAt: 'DESC' },
      relations: ['product'],
    });
  }
}
