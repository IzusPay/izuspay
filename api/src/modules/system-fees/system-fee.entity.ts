import { Entity, Column, PrimaryGeneratedColumn, CreateDateColumn, UpdateDateColumn } from 'typeorm';

export enum FeeType {
  WITHDRAWAL = 'withdrawal',
  TRANSACTION = 'transaction',
}

@Entity('system_fees')
export class SystemFee {
  @PrimaryGeneratedColumn('uuid')
  id: string;

  @Column({ type: 'enum', enum: FeeType, unique: true })
  type: FeeType;

  @Column({ type: 'decimal', precision: 10, scale: 2, default: 0 })
  fixedAmount: number;

  @Column({ type: 'decimal', precision: 5, scale: 2, default: 0 })
  percentage: number;

  @CreateDateColumn()
  createdAt: Date;

  @UpdateDateColumn()
  updatedAt: Date;
}
