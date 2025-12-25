import { Injectable, BadRequestException, NotFoundException } from '@nestjs/common';
import { InjectRepository } from '@nestjs/typeorm';
import { Repository, DataSource } from 'typeorm';
import { Withdrawal, WithdrawalStatus, WithdrawalMethod } from './withdrawal.entity';
import { RequestWithdrawalDto } from './dto/request-withdrawal.dto';
import { Company } from '../companies/company.entity';
import { BankAccount } from '../bank-accounts/bank-account.entity';
import { FinancialService } from '../financial/financial.service';
import { ProfitSource } from '../financial/system-profit.entity';

@Injectable()
export class WithdrawalsService {
  constructor(
    @InjectRepository(Withdrawal)
    private withdrawalsRepository: Repository<Withdrawal>,
    private dataSource: DataSource,
    private financialService: FinancialService,
  ) {}

  async request(companyId: string, dto: RequestWithdrawalDto) {
    let pixKey = dto.pixKey;
    let pixKeyType = dto.pixKeyType;
    let bankAccountId = dto.bankAccountId;

    // If bankAccountId is provided, fetch the PIX key from the account
    if (bankAccountId) {
      const bankAccount = await this.dataSource.manager.findOne(BankAccount, {
        where: { id: bankAccountId, companyId },
      });

      if (!bankAccount) {
        throw new BadRequestException('Bank account not found');
      }

      if (bankAccount.pixKey && bankAccount.pixKeyType) {
        pixKey = bankAccount.pixKey;
        pixKeyType = bankAccount.pixKeyType as any;
      } else {
        if (!pixKey && !dto.pixKey) {
             throw new BadRequestException('Selected bank account does not have a PIX key registered, and no PIX key was provided.');
        }
      }
    }

    // Final validation: Must have PIX key info
    if (!pixKey || !pixKeyType) {
       throw new BadRequestException('PIX Key and Type are required (either provide them directly or select a bank account with PIX registered)');
    }


    const queryRunner = this.dataSource.createQueryRunner();
    await queryRunner.connect();
    await queryRunner.startTransaction();

    try {
      // Lock company row for update to prevent race conditions on balance
      const company = await queryRunner.manager.findOne(Company, {
        where: { id: companyId },
        lock: { mode: 'pessimistic_write' },
      });

      if (!company) {
        throw new BadRequestException('Company not found');
      }

      const withdrawalFee = Number(company.withdrawalFee) || 0;
      const totalDeduction = dto.amount + withdrawalFee;

      if (Number(company.balance) < totalDeduction) {
        throw new BadRequestException(`Insufficient balance. Amount + Fee (${withdrawalFee}) exceeds balance.`);
      }

      // Deduct balance
      company.balance = Number(company.balance) - totalDeduction;
      await queryRunner.manager.save(Company, company);

      // Create Withdrawal Record
      // Always save as PIX method since "o saque vai ser só pra chave pix"
      const withdrawal = queryRunner.manager.create(Withdrawal, {
        companyId,
        amount: dto.amount,
        fee: withdrawalFee,
        method: WithdrawalMethod.PIX, 
        status: WithdrawalStatus.PENDING,
        bankAccountId: bankAccountId, // Link account if selected
        pixKeyType: pixKeyType,
        pixKey: pixKey,
      });

      const savedWithdrawal = await queryRunner.manager.save(Withdrawal, withdrawal);

      await queryRunner.commitTransaction();
      return savedWithdrawal;
    } catch (err) {
      await queryRunner.rollbackTransaction();
      throw err;
    } finally {
      await queryRunner.release();
    }
  }

  async approve(id: string) {
    const withdrawal = await this.withdrawalsRepository.findOne({ where: { id } });
    if (!withdrawal) throw new NotFoundException('Withdrawal not found');
    if (withdrawal.status !== WithdrawalStatus.PENDING) throw new BadRequestException('Withdrawal not pending');

    withdrawal.status = WithdrawalStatus.COMPLETED; // Or PROCESSING if we had an async gateway
    await this.withdrawalsRepository.save(withdrawal);

    // Register profit
    if (withdrawal.fee > 0) {
      await this.financialService.addProfit(
        withdrawal.fee,
        ProfitSource.WITHDRAWAL_FEE,
        `Withdrawal Fee #${withdrawal.id}`,
        withdrawal.id
      );
    }
    
    return withdrawal;
  }

  async reject(id: string, reason: string) {
    const queryRunner = this.dataSource.createQueryRunner();
    await queryRunner.connect();
    await queryRunner.startTransaction();

    try {
      const withdrawal = await queryRunner.manager.findOne(Withdrawal, {
        where: { id },
        relations: ['company'], // We need company to refund
        lock: { mode: 'pessimistic_write' }, // Lock withdrawal? Actually lock company is more important for balance
      });

      if (!withdrawal) throw new NotFoundException('Withdrawal not found');
      if (withdrawal.status !== WithdrawalStatus.PENDING) throw new BadRequestException('Withdrawal not pending');

      // Refund balance
      // We need to lock company
      const company = await queryRunner.manager.findOne(Company, {
        where: { id: withdrawal.companyId },
        lock: { mode: 'pessimistic_write' },
      });

      if (company) {
        const totalRefund = Number(withdrawal.amount) + Number(withdrawal.fee);
        company.balance = Number(company.balance) + totalRefund;
        await queryRunner.manager.save(Company, company);
      }

      withdrawal.status = WithdrawalStatus.REJECTED;
      withdrawal.rejectionReason = reason;
      const savedWithdrawal = await queryRunner.manager.save(Withdrawal, withdrawal);

      await queryRunner.commitTransaction();
      return savedWithdrawal;
    } catch (err) {
      await queryRunner.rollbackTransaction();
      throw err;
    } finally {
      await queryRunner.release();
    }
  }

  findAll() {
    return this.withdrawalsRepository.find({
      relations: ['company'],
      order: { createdAt: 'DESC' }
    });
  }

  findAllByCompany(companyId: string) {
    return this.withdrawalsRepository.find({
      where: { companyId },
      order: { createdAt: 'DESC' },
      relations: ['bankAccount'],
    });
  }
}
