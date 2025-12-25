import { Injectable } from '@nestjs/common';
import { InjectRepository } from '@nestjs/typeorm';
import { Repository } from 'typeorm';
import { SystemSetting } from './system-setting.entity';

@Injectable()
export class SystemSettingsService {
  constructor(
    @InjectRepository(SystemSetting)
    private settingsRepository: Repository<SystemSetting>,
  ) {}

  async get(key: string): Promise<string | null> {
    const setting = await this.settingsRepository.findOne({ where: { key } });
    return setting ? setting.value : null;
  }

  async set(key: string, value: string, description?: string): Promise<SystemSetting> {
    const setting = await this.settingsRepository.findOne({ where: { key } });
    if (setting) {
      setting.value = value;
      if (description) setting.description = description;
      return this.settingsRepository.save(setting);
    } else {
      const newSetting = this.settingsRepository.create({ key, value, description });
      return this.settingsRepository.save(newSetting);
    }
  }

  async getAll() {
    return this.settingsRepository.find();
  }
}
