import { Injectable, NotFoundException, Logger, ForbiddenException, Inject, forwardRef } from '@nestjs/common';
import { InjectRepository } from '@nestjs/typeorm';
import { Repository } from 'typeorm';
import { HttpService } from '@nestjs/axios';
import { firstValueFrom } from 'rxjs';
import { Webhook } from './webhook.entity';
import { WebhookLog, WebhookLogStatus } from './webhook-log.entity';
import { CreateWebhookDto } from './dto/create-webhook.dto';
import { CompaniesService } from '../companies/companies.service';
import { CompanyStatus } from '../companies/company.entity';
import { SystemSettingsService } from '../system-settings/system-settings.service';
import { Sale } from '../sales/sale.entity';
import { WebhooksQueueService } from './queue/webhooks-queue.service';

@Injectable()
export class WebhooksService {
  private readonly logger = new Logger(WebhooksService.name);

  constructor(
    @InjectRepository(Webhook)
    private webhooksRepository: Repository<Webhook>,
    @InjectRepository(WebhookLog)
    private webhookLogsRepository: Repository<WebhookLog>,
    private companiesService: CompaniesService,
    private systemSettingsService: SystemSettingsService,
    private httpService: HttpService,
    @Inject(forwardRef(() => WebhooksQueueService))
    private webhooksQueueService: WebhooksQueueService,
  ) {}

  async create(companyId: string, createWebhookDto: CreateWebhookDto) {
    const company = await this.companiesService.findOne(companyId);
    if (!company) throw new NotFoundException('Company not found');
    
    if (company.status !== CompanyStatus.ACTIVE) {
        throw new ForbiddenException('Company is not active. Documents must be approved first.');
    }

    const webhook = this.webhooksRepository.create({
      ...createWebhookDto,
      companyId,
    });
    return this.webhooksRepository.save(webhook);
  }

  findAllByCompany(companyId: string) {
    return this.webhooksRepository.find({
      where: { companyId },
      order: { createdAt: 'DESC' },
    });
  }

  async remove(id: string, companyId: string) {
    const result = await this.webhooksRepository.delete({ id, companyId });
    if (result.affected === 0) {
      throw new NotFoundException('Webhook not found');
    }
  }

  // --- New Logic for Sale Confirmation ---

  async notifySalePaid(sale: Sale, ignoreSkipRule: boolean = false) {
    const event = 'sale.paid';
    
    // 1. Find subscribers
    const webhooks = await this.webhooksRepository.find({
      where: { companyId: sale.companyId, active: true },
    });

    const subscribers = webhooks.filter(w => w.events.includes(event));
    if (subscribers.length === 0) return;

    // 2. Determine Skip Logic
    const company = await this.companiesService.findOne(sale.companyId);
    const globalSetting = await this.systemSettingsService.get('webhook_skip_interval');
    
    // Use company setting if present, otherwise global. Default to null (no skipping).
    const skipInterval = company.webhookSkipInterval ?? (globalSetting ? parseInt(globalSetting, 10) : null);
    
    let shouldSkip = false;
    
    if (!ignoreSkipRule && skipInterval !== null && skipInterval > 0) {
       // Logic: "Every N sales, the next one (N+1) is not sent"
       // Example N=3. Count=0,1,2 (Send). Count=3 (Skip). Reset.
       if (company.webhookSentCount >= skipInterval) {
         shouldSkip = true;
         await this.companiesService.resetWebhookCount(company.id);
       } else {
         await this.companiesService.incrementWebhookCount(company.id);
       }
    }

    // 3. Dispatch
    for (const webhook of subscribers) {
      await this.createLogAndSend(webhook.url, event, sale, shouldSkip);
    }
  }

  async dispatch(companyId: string, event: string, data: any) {
    const webhooks = await this.webhooksRepository.find({
      where: { companyId, active: true },
    });

    const subscribers = webhooks.filter(w => w.events.includes(event));
    if (subscribers.length === 0) return;

    for (const webhook of subscribers) {
      // For generic dispatch, we don't skip.
      // We assume data is the payload.
      // We try to extract saleId if possible.
      const saleId = data?.id; 

      const log = this.webhookLogsRepository.create({
          companyId,
          saleId,
          url: webhook.url,
          method: 'POST',
          payload: { event, data },
          status: WebhookLogStatus.PENDING,
      });
      
      const savedLog = await this.webhookLogsRepository.save(log);
      
      // Use queue instead of direct execution
      await this.queueWebhookRequest(savedLog);
    }
  }

  private async createLogAndSend(url: string, event: string, sale: Sale, skipped: boolean) {
    // Create Log
    const log = this.webhookLogsRepository.create({
      companyId: sale.companyId,
      saleId: sale.id,
      url,
      method: 'POST',
      payload: { event, data: sale }, // Simplified payload
      status: skipped ? WebhookLogStatus.SKIPPED_BY_RULE : WebhookLogStatus.PENDING,
    });
    
    const savedLog = await this.webhookLogsRepository.save(log);

    if (skipped) {
      this.logger.log(`Webhook skipped by rule for sale ${sale.id} to ${url}`);
      return;
    }

    // Send Request via Queue
    await this.queueWebhookRequest(savedLog);
  }

  // Method to optionally queue execution instead of direct call
  // For now, this is a helper method. To fully switch, we would replace executeWebhookRequest usage.
  async queueWebhookRequest(log: WebhookLog) {
      // Feature flag to enable/disable queue. Default to false if not set.
      const useQueue = process.env.USE_QUEUE === 'true';

      if (!useQueue) {
          await this.executeWebhookRequest(log);
          return;
      }

      try {
          await this.webhooksQueueService.queueWebhook(log.id);
          this.logger.log(`Queued webhook log ${log.id}`);
      } catch (error) {
          this.logger.error(`Failed to queue webhook log ${log.id}, falling back to sync`, error);
          // Fallback to sync execution if queue is down
          await this.executeWebhookRequest(log);
      }
  }

  async executeWebhookRequest(log: WebhookLog) {
    try {
      log.attempts++;
      log.status = WebhookLogStatus.PENDING; // In case it's a retry
      await this.webhookLogsRepository.save(log);

      const response = await firstValueFrom(
        this.httpService.post(log.url, log.payload, {
          timeout: 5000,
          validateStatus: () => true, // Capture all status codes
        })
      );

      log.httpCode = response.status;
      log.responseBody = JSON.stringify(response.data).substring(0, 5000); // Truncate if too long

      if (response.status >= 200 && response.status < 300) {
        log.status = WebhookLogStatus.SUCCESS;
      } else {
        log.status = WebhookLogStatus.FAILED;
      }

    } catch (error) {
      log.status = WebhookLogStatus.FAILED;
      log.responseBody = error.message;
    } finally {
      await this.webhookLogsRepository.save(log);
    }
  }

  // --- Admin Methods ---

  async findAllLogs() {
    return this.webhookLogsRepository.find({
      order: { createdAt: 'DESC' },
      relations: ['company', 'sale'],
      take: 100, // Limit to last 100 for now
    });
  }

  async resendLog(id: string) {
    const log = await this.webhookLogsRepository.findOne({ where: { id } });
    if (!log) throw new NotFoundException('Webhook log not found');

    // Reset to pending and execute
    // If it was skipped, we are "forcing" it now, so we respect that intention.
    await this.executeWebhookRequest(log);
    return log;
  }
}
