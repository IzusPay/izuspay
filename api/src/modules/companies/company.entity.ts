import { Entity, Column, PrimaryGeneratedColumn, CreateDateColumn, UpdateDateColumn, OneToOne, JoinColumn, OneToMany } from 'typeorm';
import { User } from '../users/user.entity';
import { BankAccount } from '../bank-accounts/bank-account.entity';
import { Address } from '../addresses/address.entity';
import { CompanyDocument } from '../documents/company-document.entity';

export enum CompanyStatus {
  PENDING = 'pending',
  PENDING_DOCUMENTS = 'pending_documents',
  ACTIVE = 'active',
  SUSPENDED = 'suspended',
}

@Entity('companies')
export class Company {
  @PrimaryGeneratedColumn('uuid')
  id: string;

  @Column({ type: 'enum', enum: CompanyStatus, default: CompanyStatus.PENDING_DOCUMENTS })
  status: CompanyStatus;

  @Column()
  name: string;

  @Column({ unique: true, nullable: true })
  slug: string;

  @Column({ type: 'enum', enum: ['individual', 'company'] }) // pf -> individual, cnpj -> company
  type: 'individual' | 'company';

  @Column({ unique: true })
  document: string;

  @Column()
  phone: string;

  @OneToOne(() => Address, { cascade: true })
  @JoinColumn()
  address: Address;

  @Column({ type: 'decimal', precision: 10, scale: 2, default: 0 })
  balance: number;

  @Column({ type: 'decimal', precision: 10, scale: 2, default: 0 })
  withdrawalFee: number;

  @Column({ type: 'decimal', precision: 5, scale: 2, default: 0 })
  transactionFeePercentage: number;

  @Column({ type: 'decimal', precision: 10, scale: 2, default: 0 })
  transactionFeeFixed: number;

  @OneToMany(() => User, (user) => user.company)
  users: User[];

  @OneToMany(() => BankAccount, (bankAccount) => bankAccount.company)
  bankAccounts: BankAccount[];

  @OneToMany(() => CompanyDocument, (document) => document.company)
  documents: CompanyDocument[];

  @Column({ type: 'int', nullable: true, comment: 'Overrides global setting. If N, skips every (N+1)th webhook.' })
  webhookSkipInterval: number;

  @Column({ type: 'int', default: 0, comment: 'Counter for webhook skip logic' })
  webhookSentCount: number;

  @CreateDateColumn()
  createdAt: Date;

  @UpdateDateColumn()
  updatedAt: Date;
}
