import { Entity, Column, PrimaryGeneratedColumn, CreateDateColumn, ManyToOne, JoinColumn } from 'typeorm';
import { Company } from '../companies/company.entity';

@Entity('api_keys')
export class ApiKey {
  @PrimaryGeneratedColumn('uuid')
  id: string;

  @Column()
  name: string; // Friendly name

  @Column({ unique: true })
  key: string; // Hashed key (SHA256) for lookups

  @Column({ nullable: true })
  encryptedKey: string; // Encrypted key (AES) for retrieval

  @Column({ nullable: true })
  iv: string; // Initialization Vector for AES

  @Column()
  prefix: string; // Display prefix

  @Column({ default: true })
  active: boolean;

  @Column({ nullable: true })
  lastUsedAt: Date;

  @Column()
  companyId: string;

  @ManyToOne(() => Company)
  @JoinColumn({ name: 'companyId' })
  company: Company;

  @CreateDateColumn()
  createdAt: Date;
}
