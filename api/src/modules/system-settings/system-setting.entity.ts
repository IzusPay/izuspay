import { Entity, Column, PrimaryColumn, UpdateDateColumn } from 'typeorm';

@Entity('system_settings')
export class SystemSetting {
  @PrimaryColumn()
  key: string;

  @Column({ type: 'text', nullable: true })
  value: string;

  @Column({ nullable: true })
  description: string;

  @UpdateDateColumn()
  updatedAt: Date;
}
