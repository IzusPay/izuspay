import { Entity, Column, PrimaryGeneratedColumn, OneToMany, CreateDateColumn, UpdateDateColumn, ManyToOne, JoinColumn } from 'typeorm';
import { GatewayParam } from './gateway-param.entity';
import { GatewayType } from './gateway-type.entity';

@Entity('gateways')
export class Gateway {
  @PrimaryGeneratedColumn('uuid')
  id: string;

  @Column()
  name: string;

  @Column({ nullable: true })
  image: string;

  @Column()
  apiUrl: string;

  @Column({ nullable: true })
  typeId: string;

  @ManyToOne(() => GatewayType, (type) => type.gateways)
  @JoinColumn({ name: 'typeId' })
  type: GatewayType;

  @OneToMany(() => GatewayParam, (param) => param.gateway, { cascade: true })
  params: GatewayParam[];

  @Column({ type: 'decimal', precision: 5, scale: 2, default: 0 })
  transactionFeePercentage: number;

  @Column({ type: 'decimal', precision: 10, scale: 2, default: 0 })
  transactionFeeFixed: number;

  @Column({ default: 0 })
  priority: number;

  @Column({ default: true })
  isActive: boolean;

  @CreateDateColumn()
  createdAt: Date;

  @UpdateDateColumn()
  updatedAt: Date;
}
