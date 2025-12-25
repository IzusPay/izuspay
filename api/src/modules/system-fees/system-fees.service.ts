import { Injectable, NotFoundException, OnModuleInit } from '@nestjs/common';
import { InjectRepository } from '@nestjs/typeorm';
import { Repository } from 'typeorm';
import { SystemFee, FeeType } from './system-fee.entity';

@Injectable()
export class SystemFeesService implements OnModuleInit {
  constructor(
    @InjectRepository(SystemFee)
    private systemFeesRepository: Repository<SystemFee>,
  ) {}

  async onModuleInit() {
    // Seed default fees if not exists
    const count = await this.systemFeesRepository.count();
    if (count === 0) {
      await this.systemFeesRepository.save([
        { type: FeeType.WITHDRAWAL, fixedAmount: 5.00, percentage: 0 },
        { type: FeeType.TRANSACTION, fixedAmount: 1.00, percentage: 3.99 },
      ]);
    }
  }

  findAll() {
    return this.systemFeesRepository.find();
  }

  async update(id: string, updateSystemFeeDto: any) {
    const fee = await this.systemFeesRepository.findOne({ where: { id } });
    if (!fee) throw new NotFoundException('Fee config not found');
    
    this.systemFeesRepository.merge(fee, updateSystemFeeDto);
    return this.systemFeesRepository.save(fee);
  }

  async remove(id: string) {
    return this.systemFeesRepository.delete(id);
  }

  async getFee(type: FeeType) {
    return this.systemFeesRepository.findOne({ where: { type } });
  }
}
