import { Injectable, NotFoundException } from '@nestjs/common';
import { InjectRepository } from '@nestjs/typeorm';
import { Repository } from 'typeorm';
import { Customer } from './customer.entity';
import { CreateCustomerDto } from './dto/create-customer.dto';

@Injectable()
export class CustomersService {
  constructor(
    @InjectRepository(Customer)
    private customersRepository: Repository<Customer>,
  ) {}

  async create(companyId: string, createCustomerDto: CreateCustomerDto) {
    const customer = this.customersRepository.create({
      ...createCustomerDto,
      companyId,
    });
    return this.customersRepository.save(customer);
  }

  async findByEmailAndCompany(email: string, companyId: string) {
    return this.customersRepository.findOne({ where: { email, companyId } });
  }

  findAllByCompany(companyId: string) {
    return this.customersRepository.find({
      where: { companyId },
      order: { createdAt: 'DESC' },
    });
  }

  async findOne(id: string) {
    const customer = await this.customersRepository.findOne({ where: { id } });
    if (!customer) throw new NotFoundException('Customer not found');
    return customer;
  }
}
