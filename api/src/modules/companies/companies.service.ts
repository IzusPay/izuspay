import { Injectable, NotFoundException } from '@nestjs/common';
import { InjectRepository } from '@nestjs/typeorm';
import { Repository, DataSource } from 'typeorm';
import * as bcrypt from 'bcrypt';
import { Company, CompanyStatus } from './company.entity';
import { CreateCompanyDto } from './dto/create-company.dto';
import { UpdateCompanyDto } from './dto/update-company.dto';
import { RegisterCompanyDto } from './dto/register-company.dto';
import { Role } from '../../common/enums/role.enum';
import { User } from '../users/user.entity';
import { Address } from '../addresses/address.entity';

import { SystemFeesService } from '../system-fees/system-fees.service';
import { FeeType } from '../system-fees/system-fee.entity';

@Injectable()
export class CompaniesService {
  constructor(
    @InjectRepository(Company)
    private companiesRepository: Repository<Company>,
    private dataSource: DataSource,
    private systemFeesService: SystemFeesService,
  ) {}

  async register(registerDto: RegisterCompanyDto) {
    const queryRunner = this.dataSource.createQueryRunner();
    await queryRunner.connect();
    await queryRunner.startTransaction();

    try {
      // Fetch default fees
      const defaultWithdrawalFee = await this.systemFeesService.getFee(FeeType.WITHDRAWAL);
      const defaultTransactionFee = await this.systemFeesService.getFee(FeeType.TRANSACTION);

      // 1. Create Address
      const address = queryRunner.manager.create(Address, {
        street: registerDto.street,
        number: registerDto.number,
        complement: registerDto.complement,
        neighborhood: registerDto.neighborhood,
        city: registerDto.city,
        state: registerDto.state,
        zipCode: registerDto.zipCode,
      });
      
      // 2. Create Company
      const company = this.companiesRepository.create({
        name: registerDto.name,
        slug: registerDto.slug,
        type: registerDto.type,
        document: registerDto.document,
        phone: registerDto.phone,
        status: CompanyStatus.PENDING_DOCUMENTS,
        balance: 0,
        address: address, // Attach address entity
        withdrawalFee: defaultWithdrawalFee?.fixedAmount || 0,
        transactionFeePercentage: defaultTransactionFee?.percentage || 0,
        transactionFeeFixed: defaultTransactionFee?.fixedAmount || 0,
      });
      
      const savedCompany = await queryRunner.manager.save(Company, company);

      // 3. Create Admin User for Company
      const hashedPassword = await bcrypt.hash(registerDto.password, 10);
      
      const userEntity = queryRunner.manager.create(User, {
         name: registerDto.name, // Or a specific contact name if added to DTO
         email: registerDto.email,
         password: hashedPassword,
         role: Role.Client,
         companyId: savedCompany.id,
         status: 'active',
      });
      await queryRunner.manager.save(User, userEntity);

      await queryRunner.commitTransaction();
      return savedCompany;
    } catch (err) {
      await queryRunner.rollbackTransaction();
      throw err;
    } finally {
      await queryRunner.release();
    }
  }

  create(createCompanyDto: CreateCompanyDto) {
    // This simple create might need address handling too if used directly
    const company = this.companiesRepository.create(createCompanyDto as any);
    return this.companiesRepository.save(company);
  }

  findAll() {
    return this.companiesRepository.find({ relations: ['address'] });
  }

  async activate(id: string) {
    const company = await this.findOne(id);
    if (!company) throw new NotFoundException('Company not found');
    
    company.status = CompanyStatus.ACTIVE;
    return this.companiesRepository.save(company);
  }

  async incrementWebhookCount(id: string) {
    await this.companiesRepository.increment({ id }, 'webhookSentCount', 1);
  }

  async resetWebhookCount(id: string) {
    await this.companiesRepository.update({ id }, { webhookSentCount: 0 });
  }

  async updateWebhookSettings(id: string, interval: number | null) {
    // TypeORM update expects compatible types. If interval is null, we can pass null if the column allows it.
    // However, TypeORM QueryDeepPartialEntity strictness might require explicit casting or handling.
    await this.companiesRepository.update({ id }, { webhookSkipInterval: interval } as any);
  }

  async findOne(id: string) {
    const company = await this.companiesRepository.findOne({
      where: { id },
      relations: ['address', 'documents'],
    });
    if (!company) {
      throw new NotFoundException(`Company with ID ${id} not found`);
    }
    return company;
  }

  async update(id: string, updateCompanyDto: UpdateCompanyDto) {
    const company = await this.findOne(id);
    this.companiesRepository.merge(company, updateCompanyDto as any);
    return this.companiesRepository.save(company);
  }

  remove(id: string) {
    return this.companiesRepository.delete(id);
  }
}
