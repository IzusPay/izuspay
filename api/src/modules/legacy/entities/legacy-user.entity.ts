import { Entity, Column, PrimaryGeneratedColumn, CreateDateColumn, UpdateDateColumn } from 'typeorm';

@Entity('users')
export class LegacyUser {
  @PrimaryGeneratedColumn()
  id: number;

  @Column({ name: 'association_id', nullable: true })
  associationId: number;

  @Column({ type: 'enum', enum: ['admin', 'cliente', 'membro'], default: 'cliente' })
  tipo: 'admin' | 'cliente' | 'membro';

  @Column()
  name: string;

  @Column({ unique: true })
  email: string;

  @Column({ nullable: true })
  telefone: string;

  @Column({ nullable: true })
  documento: string;

  @Column({ default: 'pendente' })
  status: string;

  @Column()
  password: string;

  @Column({ name: 'api_token', nullable: true })
  apiToken: string;

  @Column({ name: 'remember_token', nullable: true })
  rememberToken: string;

  @CreateDateColumn({ name: 'created_at' })
  createdAt: Date;

  @UpdateDateColumn({ name: 'updated_at' })
  updatedAt: Date;
}
