import { Entity, Column, PrimaryGeneratedColumn, ManyToOne, JoinColumn, CreateDateColumn, UpdateDateColumn } from 'typeorm';
import { RoleEntity } from './role.entity';
import { ModuleEntity } from './module.entity';

@Entity('permissions')
export class Permission {
  @PrimaryGeneratedColumn('uuid')
  id: string;

  @Column()
  roleId: string;

  @ManyToOne(() => RoleEntity, (role) => role.permissions, { onDelete: 'CASCADE' })
  @JoinColumn({ name: 'roleId' })
  role: RoleEntity;

  @Column()
  moduleId: string;

  @ManyToOne(() => ModuleEntity, (module) => module.permissions, { onDelete: 'CASCADE' })
  @JoinColumn({ name: 'moduleId' })
  module: ModuleEntity;

  @Column({ default: false })
  canCreate: boolean;

  @Column({ default: false })
  canRead: boolean;

  @Column({ default: false })
  canUpdate: boolean;

  @Column({ default: false })
  canDelete: boolean;

  @Column({ default: false })
  canDetail: boolean;

  @CreateDateColumn()
  createdAt: Date;

  @UpdateDateColumn()
  updatedAt: Date;
}
