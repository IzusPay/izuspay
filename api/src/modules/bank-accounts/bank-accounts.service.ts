import { Injectable, NotFoundException } from '@nestjs/common';
import { InjectRepository } from '@nestjs/typeorm';
import { Repository } from 'typeorm';
import { BankAccount } from './bank-account.entity';
import { CreateBankAccountDto } from './dto/create-bank-account.dto';

@Injectable()
export class BankAccountsService {
  constructor(
    @InjectRepository(BankAccount)
    private bankAccountsRepository: Repository<BankAccount>,
  ) {}

  async create(companyId: string, createBankAccountDto: CreateBankAccountDto) {
    const bankAccount = this.bankAccountsRepository.create({
      ...createBankAccountDto,
      companyId,
    });
    return this.bankAccountsRepository.save(bankAccount);
  }

  async findOne(id: string) {
    return this.bankAccountsRepository.findOne({ where: { id } });
  }

  findAllByCompany(companyId: string) {
    return this.bankAccountsRepository.find({ where: { companyId } });
  }

  async update(id: string, updateBankAccountDto: any) {
    const bankAccount = await this.findOne(id);
    if (!bankAccount) throw new NotFoundException('Bank account not found');
    
    const updated = this.bankAccountsRepository.merge(bankAccount, updateBankAccountDto);
    return this.bankAccountsRepository.save(updated);
  }

  async remove(id: string) {
    return this.bankAccountsRepository.delete(id);
  }
}
