import { Injectable, NotFoundException, BadRequestException } from '@nestjs/common';
import { InjectRepository } from '@nestjs/typeorm';
import { Repository } from 'typeorm';
import { CompanyDocument, DocumentType, DocumentStatus } from './company-document.entity';
import { CompaniesService } from '../companies/companies.service';

@Injectable()
export class DocumentsService {
  constructor(
    @InjectRepository(CompanyDocument)
    private documentsRepository: Repository<CompanyDocument>,
    private companiesService: CompaniesService,
  ) {}

  async upload(companyId: string, type: DocumentType, file: Express.Multer.File) {
    // In a real app, upload to S3/Cloudinary and get URL.
    // Here we simulate by saving file.path or filename
    const url = file.path || file.filename;

    const document = this.documentsRepository.create({
      companyId,
      type,
      url,
      status: DocumentStatus.PENDING,
    });
    return this.documentsRepository.save(document);
  }

  findAllByCompany(companyId: string) {
    return this.documentsRepository.find({ where: { companyId } });
  }

  async findOne(id: string) {
    return this.documentsRepository.findOne({ where: { id } });
  }

  async approve(id: string) {
    const document = await this.findOne(id);
    if (!document) throw new NotFoundException('Document not found');

    document.status = DocumentStatus.APPROVED;
    const savedDoc = await this.documentsRepository.save(document);

    await this.checkAllApproved(document.companyId);

    return savedDoc;
  }

  async reject(id: string, rejectionReason: string) {
    const document = await this.findOne(id);
    if (!document) throw new NotFoundException('Document not found');

    document.status = DocumentStatus.REJECTED;
    document.rejectionReason = rejectionReason;
    return this.documentsRepository.save(document);
  }

  private async checkAllApproved(companyId: string) {
    const requiredTypes = [
      DocumentType.ID_CARD_FRONT,
      DocumentType.ID_CARD_BACK,
      DocumentType.SOCIAL_CONTRACT,
      DocumentType.SELFIE_WITH_ID,
    ];
    const docs = await this.documentsRepository.find({ where: { companyId } });

    const approvedTypes = docs
      .filter((d) => d.status === DocumentStatus.APPROVED)
      .map((d) => d.type);

    const allApproved = requiredTypes.every((t) => approvedTypes.includes(t));

    if (allApproved) {
      // Activate company
      await this.companiesService.activate(companyId);
    }
  }
}
