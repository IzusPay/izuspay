import { Entity, Column, PrimaryGeneratedColumn, CreateDateColumn, UpdateDateColumn, OneToOne } from 'typeorm';
import { Company } from '../companies/company.entity';

@Entity('addresses')
export class Address {
  @PrimaryGeneratedColumn('uuid')
  id: string;

  @Column()
  street: string;

  @Column()
  number: string;

  @Column({ type: 'varchar', nullable: true })
  complement: string | null;

  @Column()
  neighborhood: string;

  @Column()
  city: string;

  @Column({ length: 2 })
  state: string;

  @Column()
  zipCode: string;

  @OneToOne(() => Company, (company) => company.address)
  company: Company;

  @CreateDateColumn()
  createdAt: Date;

  @UpdateDateColumn()
  updatedAt: Date;
}
