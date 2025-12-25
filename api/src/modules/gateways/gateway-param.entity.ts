import { Entity, Column, PrimaryGeneratedColumn, ManyToOne, JoinColumn } from 'typeorm';
import { Gateway } from './gateway.entity';

@Entity('gateway_params')
export class GatewayParam {
  @PrimaryGeneratedColumn('uuid')
  id: string;

  @Column()
  gatewayId: string;

  @ManyToOne(() => Gateway, (gateway) => gateway.params, { onDelete: 'CASCADE' })
  @JoinColumn({ name: 'gatewayId' })
  gateway: Gateway;

  @Column()
  label: string;

  @Column()
  value: string;
}
