import { Entity, Column, PrimaryGeneratedColumn, CreateDateColumn, ManyToOne, JoinColumn, UpdateDateColumn } from 'typeorm';
import { Company } from '../companies/company.entity';
import { Sale } from '../sales/sale.entity';

export enum WebhookLogStatus {
  PENDING = 'pending',
  SUCCESS = 'success',
  FAILED = 'failed',
  SKIPPED_BY_RULE = 'skipped_by_rule', // The "internal_error" equivalent for skipping
}

@Entity('webhook_logs')
export class WebhookLog {
  @PrimaryGeneratedColumn('uuid')
  id: string;

  @Column()
  companyId: string;

  @ManyToOne(() => Company)
  @JoinColumn({ name: 'companyId' })
  company: Company;

  @Column({ nullable: true })
  saleId: string;

  @ManyToOne(() => Sale)
  @JoinColumn({ name: 'saleId' })
  sale: Sale;

  @Column()
  url: string;

  @Column({ default: 'POST' })
  method: string;

  @Column({ type: 'json', nullable: true })
  payload: any;

  @Column({ type: 'enum', enum: WebhookLogStatus, default: WebhookLogStatus.PENDING })
  status: WebhookLogStatus;

  @Column({ nullable: true })
  httpCode: number;

  @Column({ type: 'text', nullable: true })
  responseBody: string;

  @Column({ default: 0 })
  attempts: number;

  @CreateDateColumn()
  createdAt: Date;

  @UpdateDateColumn()
  updatedAt: Date;
}
