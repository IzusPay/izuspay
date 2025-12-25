import { Entity, Column, PrimaryGeneratedColumn, CreateDateColumn, UpdateDateColumn, ManyToOne, JoinColumn } from 'typeorm';
import { Company } from '../companies/company.entity';
import { Product } from '../products/product.entity';
import { Customer } from '../customers/customer.entity';

export enum SaleStatus {
  PENDING = 'pending',
  PAID = 'paid',
  FAILED = 'failed',
  REFUNDED = 'refunded',
  EXPIRED = 'expired',
}

@Entity('sales')
export class Sale {
  @PrimaryGeneratedColumn('uuid')
  id: string;

  @Column()
  companyId: string;

  @ManyToOne(() => Company)
  @JoinColumn({ name: 'companyId' })
  company: Company;

  @Column({ nullable: true })
  productId: string;

  @ManyToOne(() => Product)
  @JoinColumn({ name: 'productId' })
  product: Product;

  @Column({ nullable: true })
  customerId: string; // Payer Customer ID

  @ManyToOne(() => Customer)
  @JoinColumn({ name: 'customerId' })
  customer: Customer;

  @Column({ type: 'enum', enum: SaleStatus, default: SaleStatus.PENDING })
  status: SaleStatus;

  // Financials
  @Column({ type: 'decimal', precision: 10, scale: 2 })
  amount: number; // Gross Amount

  @Column({ type: 'decimal', precision: 10, scale: 2, default: 0 })
  fee: number; // Total Fee

  @Column({ type: 'decimal', precision: 10, scale: 2 })
  netAmount: number; // Amount to be credited to company

  @Column({ default: 'PIX' })
  paymentMethod: string;

  // Payer Info (Snapshot)
  @Column()
  payerName: string;

  @Column()
  payerEmail: string;

  @Column()
  payerDocument: string;

  @Column({ nullable: true })
  payerPhone: string;

  // Gateway Info
  @Column({ nullable: true })
  gatewayId: string;

  @Column({ nullable: true })
  transactionId: string; // Gateway Transaction ID

  @Column({ type: 'text', nullable: true })
  pixCode: string; // Copy and Paste

  @Column({ type: 'text', nullable: true })
  pixQrCode: string; // Base64 or URL

  @CreateDateColumn()
  createdAt: Date;

  @UpdateDateColumn()
  updatedAt: Date;
}
