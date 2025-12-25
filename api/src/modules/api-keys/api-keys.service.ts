import { Injectable, NotFoundException, ForbiddenException } from '@nestjs/common';
import { InjectRepository } from '@nestjs/typeorm';
import { Repository } from 'typeorm';
import { ApiKey } from './api-key.entity';
import { CreateApiKeyDto } from './dto/create-api-key.dto';
import * as crypto from 'crypto';
import { CompaniesService } from '../companies/companies.service';
import { CompanyStatus } from '../companies/company.entity';

@Injectable()
export class ApiKeysService {
  // TODO: Move to environment variable
  private readonly ENCRYPTION_KEY = crypto.scryptSync('secret_key_passphrase', 'salt', 32);
  private readonly ALGORITHM = 'aes-256-cbc';

  constructor(
    @InjectRepository(ApiKey)
    private apiKeysRepository: Repository<ApiKey>,
    private companiesService: CompaniesService,
  ) {}

  /**
   * Generates a new API Key.
   * Stores both Hash (for validation) and Encrypted (for retrieval).
   */
  async create(companyId: string, createApiKeyDto: CreateApiKeyDto) {
    const company = await this.companiesService.findOne(companyId);
    if (!company) throw new NotFoundException('Company not found');
    
    if (company.status !== CompanyStatus.ACTIVE) {
        throw new ForbiddenException('Company is not active. Documents must be approved first.');
    }

    const rawKey = `sk_live_${crypto.randomBytes(24).toString('hex')}`;
    const hash = this.hashKey(rawKey);
    const prefix = rawKey.substring(0, 15) + '...';
    
    // Encrypt for storage/retrieval
    const { encrypted, iv } = this.encryptKey(rawKey);

    const apiKey = this.apiKeysRepository.create({
      name: createApiKeyDto.name,
      key: hash,
      encryptedKey: encrypted,
      iv: iv,
      prefix: prefix,
      companyId: companyId,
    });

    await this.apiKeysRepository.save(apiKey);

    return {
      ...apiKey,
      plainKey: rawKey,
    };
  }

  async findAllByCompany(companyId: string) {
    return this.apiKeysRepository.find({
      where: { companyId },
      order: { createdAt: 'DESC' },
      select: ['id', 'name', 'prefix', 'active', 'createdAt', 'lastUsedAt'],
    });
  }

  /**
   * Reveals the plain text API Key for a given ID.
   */
  async revealKey(id: string, companyId: string) {
    const apiKey = await this.apiKeysRepository.findOne({
      where: { id, companyId },
    });

    if (!apiKey) {
      throw new NotFoundException('API Key not found');
    }

    if (!apiKey.encryptedKey || !apiKey.iv) {
      throw new NotFoundException('This API Key cannot be revealed (legacy or missing encryption)');
    }

    const plainKey = this.decryptKey(apiKey.encryptedKey, apiKey.iv);
    return { plainKey };
  }

  async remove(id: string, companyId: string) {
    await this.apiKeysRepository.delete({ id, companyId });
  }

  async validateKey(rawKey: string): Promise<ApiKey | null> {
    const hash = this.hashKey(rawKey);
    const apiKey = await this.apiKeysRepository.findOne({
      where: { key: hash, active: true },
      relations: ['company'],
    });

    if (apiKey) {
      // Update last used asynchronously
      this.apiKeysRepository.update(apiKey.id, { lastUsedAt: new Date() });
    }

    return apiKey;
  }

  private hashKey(key: string): string {
    return crypto.createHash('sha256').update(key).digest('hex');
  }

  private encryptKey(text: string) {
    const iv = crypto.randomBytes(16);
    const cipher = crypto.createCipheriv(this.ALGORITHM, this.ENCRYPTION_KEY, iv);
    let encrypted = cipher.update(text);
    encrypted = Buffer.concat([encrypted, cipher.final()]);
    return { encrypted: encrypted.toString('hex'), iv: iv.toString('hex') };
  }

  private decryptKey(encryptedText: string, ivHex: string): string {
    const iv = Buffer.from(ivHex, 'hex');
    const encrypted = Buffer.from(encryptedText, 'hex');
    const decipher = crypto.createDecipheriv(this.ALGORITHM, this.ENCRYPTION_KEY, iv);
    let decrypted = decipher.update(encrypted);
    decrypted = Buffer.concat([decrypted, decipher.final()]);
    return decrypted.toString();
  }
}
