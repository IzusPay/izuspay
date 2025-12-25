import { Injectable } from '@nestjs/common';
import { InjectRepository } from '@nestjs/typeorm';
import { Repository } from 'typeorm';
import { AuditLog } from './audit-log.entity';

@Injectable()
export class AuditLogsService {
  constructor(
    @InjectRepository(AuditLog)
    private auditLogsRepository: Repository<AuditLog>,
  ) {}

  async log(action: string, details: string, ipAddress: string, userId?: string, companyId?: string) {
    const log = this.auditLogsRepository.create({
      action,
      details,
      ipAddress,
      userId,
      companyId,
    });
    return this.auditLogsRepository.save(log);
  }

  findAllByCompany(companyId: string) {
    return this.auditLogsRepository.find({
      where: { companyId },
      order: { createdAt: 'DESC' },
      take: 100, // Limit to last 100 logs
    });
  }
}
