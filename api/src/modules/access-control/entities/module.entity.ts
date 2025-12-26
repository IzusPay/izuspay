import { Entity, Column, PrimaryGeneratedColumn, OneToMany, CreateDateColumn, UpdateDateColumn } from 'typeorm';
import { Permission } from './permission.entity';

@Entity('modules')
export class ModuleEntity {
  @PrimaryGeneratedColumn('uuid')
  id: string;

  @Column()
  name: string; // Display name e.g. "Sales"

  @Column({ unique: true })
  key: string; // Unique key e.g. "sales"

  @OneToMany(() => Permission, (permission) => permission.module)
  permissions: Permission[];

  @CreateDateColumn()
  createdAt: Date;

  @UpdateDateColumn()
  updatedAt: Date;
}
