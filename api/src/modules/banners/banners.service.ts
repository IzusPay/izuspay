import { Injectable } from '@nestjs/common';
import { InjectRepository } from '@nestjs/typeorm';
import { Repository } from 'typeorm';
import { Banner, BannerType } from './banner.entity';
import { CreateBannerDto } from './dto/create-banner.dto';

@Injectable()
export class BannersService {
  constructor(
    @InjectRepository(Banner)
    private bannersRepository: Repository<Banner>,
  ) {}

  async create(companyId: string, type: BannerType, file: Express.Multer.File) {
    // Deactivate previous favicon/logo if applicable
    if (type === BannerType.FAVICON || type === BannerType.LOGO) {
      await this.bannersRepository.update(
        { companyId, type, isActive: true },
        { isActive: false }
      );
    }

    const banner = this.bannersRepository.create({
      companyId,
      type,
      url: file.path, // or filename
      isActive: true,
    });
    return this.bannersRepository.save(banner);
  }

  findAllByCompany(companyId: string) {
    return this.bannersRepository.find({
      where: { companyId, isActive: true },
      order: { createdAt: 'DESC' },
    });
  }

  async remove(id: string) {
    return this.bannersRepository.delete(id);
  }
}
