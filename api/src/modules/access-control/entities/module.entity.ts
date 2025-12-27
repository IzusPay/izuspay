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

  @Column({ nullable: true })
  icon: string;

  @Column({ nullable: true })
  route: string;

  @OneToMany(() => Permission, (permission) => permission.module)
  permissions: Permission[];

  @CreateDateColumn()
  createdAt: Date;

  @UpdateDateColumn()
  updatedAt: Date;
}
