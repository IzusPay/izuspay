import { Entity, Column, PrimaryGeneratedColumn, CreateDateColumn, UpdateDateColumn, ManyToOne, JoinColumn } from 'typeorm';
import { Role } from '../../common/enums/role.enum';
import { Company } from '../companies/company.entity';
import { RoleEntity } from '../access-control/entities/role.entity';

@Entity('users')
export class User {
  @PrimaryGeneratedColumn('uuid')
  id: string;

  @Column()
  name: string;

  @Column({ unique: true })
  email: string;

  @Column({ select: false })
  password: string;

  // Keep for simple system-wide checks (Admin vs Client)
  @Column({
    type: 'enum',
    enum: Role,
    default: Role.Client,
  })
  role: Role;

  // New ACL Role Relation
  @Column({ nullable: true })
  accessRoleId: string;

  @ManyToOne(() => RoleEntity, (role) => role.users)
  @JoinColumn({ name: 'accessRoleId' })
  accessRole: RoleEntity;

  // 2FA Fields
  @Column({ nullable: true, select: false })
  twoFactorSecret: string;

  @Column({ default: false })
  isTwoFactorEnabled: boolean;

  @Column({ nullable: true })
  companyId: string | null;

  @ManyToOne(() => Company, (company) => company.users)
  @JoinColumn({ name: 'companyId' })
  company: Company;

  @Column({
    type: 'enum',
    enum: ['active', 'inactive', 'suspended'],
    default: 'active',
  })
  status: 'active' | 'inactive' | 'suspended';

  @CreateDateColumn()
  createdAt: Date;

  @UpdateDateColumn()
  updatedAt: Date;
}
