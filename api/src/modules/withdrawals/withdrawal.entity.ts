import { Entity, Column, PrimaryGeneratedColumn, CreateDateColumn, UpdateDateColumn, ManyToOne, JoinColumn } from 'typeorm';
import { Company } from '../companies/company.entity';
import { BankAccount } from '../bank-accounts/bank-account.entity';

export enum WithdrawalMethod {
  BANK_ACCOUNT = 'bank_account',
  PIX = 'pix',
}

export enum WithdrawalStatus {
  PENDING = 'pending',
  PROCESSING = 'processing',
  COMPLETED = 'completed',
  REJECTED = 'rejected',
  FAILED = 'failed',
}

export enum PixKeyType {
  CPF = 'cpf',
  CNPJ = 'cnpj',
  EMAIL = 'email',
  PHONE = 'phone',
  RANDOM = 'random',
}

@Entity('withdrawals')
export class Withdrawal {
  @PrimaryGeneratedColumn('uuid')
  id: string;

  @Column({ type: 'decimal', precision: 10, scale: 2 })
  amount: number;

  @Column({ type: 'decimal', precision: 10, scale: 2, default: 0 })
  fee: number;

  @Column({ type: 'enum', enum: WithdrawalStatus, default: WithdrawalStatus.PENDING })
  status: WithdrawalStatus;

  @Column({ type: 'enum', enum: WithdrawalMethod })
  method: WithdrawalMethod;

  @Column({ nullable: true })
  rejectionReason: string;

  @Column({ nullable: true })
  companyId: string;

  @ManyToOne(() => Company)
  @JoinColumn({ name: 'companyId' })
  company: Company;

  @Column({ nullable: true })
  bankAccountId: string;

  @ManyToOne(() => BankAccount, { nullable: true })
  @JoinColumn({ name: 'bankAccountId' })
  bankAccount: BankAccount;

  // PIX details if not using saved bank account
  @Column({ nullable: true })
  pixKey: string;

  @Column({ nullable: true, type: 'enum', enum: PixKeyType })
  pixKeyType: PixKeyType;

  @CreateDateColumn()
  createdAt: Date;

  @UpdateDateColumn()
  updatedAt: Date;
}
