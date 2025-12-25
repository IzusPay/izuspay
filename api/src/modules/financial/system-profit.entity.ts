import { Entity, Column, PrimaryGeneratedColumn, CreateDateColumn } from 'typeorm';

export enum ProfitSource {
  WITHDRAWAL_FEE = 'withdrawal_fee',
  TRANSACTION_FEE = 'transaction_fee',
}

@Entity('system_profits')
export class SystemProfit {
  @PrimaryGeneratedColumn('uuid')
  id: string;

  @Column({ type: 'decimal', precision: 10, scale: 2 })
  amount: number;

  @Column({ type: 'enum', enum: ProfitSource })
  source: ProfitSource;

  @Column({ nullable: true })
  description: string;

  @Column({ nullable: true })
  relatedEntityId: string;

  @CreateDateColumn()
  createdAt: Date;
}
