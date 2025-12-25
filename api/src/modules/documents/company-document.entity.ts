import { Entity, Column, PrimaryGeneratedColumn, CreateDateColumn, UpdateDateColumn, ManyToOne, JoinColumn } from 'typeorm';
import { Company } from '../companies/company.entity';

export enum DocumentType {
  ID_CARD_FRONT = 'id_card_front',
  ID_CARD_BACK = 'id_card_back',
  SOCIAL_CONTRACT = 'social_contract',
  SELFIE_WITH_ID = 'selfie_with_id',
}

export enum DocumentStatus {
  PENDING = 'pending',
  APPROVED = 'approved',
  REJECTED = 'rejected',
}

@Entity('company_documents')
export class CompanyDocument {
  @PrimaryGeneratedColumn('uuid')
  id: string;

  @Column()
  companyId: string;

  @ManyToOne(() => Company)
  @JoinColumn({ name: 'companyId' })
  company: Company;

  @Column({ type: 'enum', enum: DocumentType })
  type: DocumentType;

  @Column()
  url: string;

  @Column({ type: 'enum', enum: DocumentStatus, default: DocumentStatus.PENDING })
  status: DocumentStatus;

  @Column({ nullable: true, type: 'text' })
  rejectionReason: string | null;

  @CreateDateColumn()
  createdAt: Date;

  @UpdateDateColumn()
  updatedAt: Date;
}
