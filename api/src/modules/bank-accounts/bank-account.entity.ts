import { Entity, Column, PrimaryGeneratedColumn, CreateDateColumn, UpdateDateColumn, ManyToOne, JoinColumn } from 'typeorm';
import { Company } from '../companies/company.entity';
import { PixKeyType } from '../withdrawals/withdrawal.entity';

@Entity('bank_accounts')
export class BankAccount {
  @PrimaryGeneratedColumn('uuid')
  id: string;

  @Column()
  bankName: string;

  @Column()
  agency: string;

  @Column()
  accountNumber: string;

  @Column()
  accountDigit: string;

  @Column({ nullable: true })
  pixKey: string;

  @Column({ type: 'enum', enum: PixKeyType, nullable: true })
  pixKeyType: PixKeyType;

  @Column()
  companyId: string;

  @ManyToOne(() => Company, (company) => company.bankAccounts)
  @JoinColumn({ name: 'companyId' })
  company: Company;

  @CreateDateColumn()
  createdAt: Date;

  @UpdateDateColumn()
  updatedAt: Date;
}
