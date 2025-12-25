import { Injectable } from '@nestjs/common';
import { InjectRepository } from '@nestjs/typeorm';
import { Repository } from 'typeorm';
import { SystemProfit, ProfitSource } from './system-profit.entity';

@Injectable()
export class FinancialService {
  constructor(
    @InjectRepository(SystemProfit)
    private systemProfitsRepository: Repository<SystemProfit>,
  ) {}

  async addProfit(amount: number, source: ProfitSource, description: string, relatedEntityId?: string) {
    if (amount <= 0) return;
    
    const profit = this.systemProfitsRepository.create({
      amount,
      source,
      description,
      relatedEntityId,
    });
    return this.systemProfitsRepository.save(profit);
  }

  findAll() {
    return this.systemProfitsRepository.find({ order: { createdAt: 'DESC' } });
  }

  async getTotalProfit() {
    const { total } = await this.systemProfitsRepository
      .createQueryBuilder('profit')
      .select('SUM(profit.amount)', 'total')
      .getRawOne();
    return total || 0;
  }
}
