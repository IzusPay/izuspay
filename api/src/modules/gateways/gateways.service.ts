import { Injectable } from '@nestjs/common';
import { InjectRepository } from '@nestjs/typeorm';
import { Repository } from 'typeorm';
import { Gateway } from './gateway.entity';
import { GatewayParam } from './gateway-param.entity';
import { GatewayType } from './gateway-type.entity';
import { CreateGatewayDto } from './dto/create-gateway.dto';

@Injectable()
export class GatewaysService {
  constructor(
    @InjectRepository(Gateway)
    private gatewaysRepository: Repository<Gateway>,
    @InjectRepository(GatewayType)
    private gatewayTypesRepository: Repository<GatewayType>,
  ) {}

  async createType(name: string, slug: string) {
    const type = this.gatewayTypesRepository.create({ name, slug });
    return this.gatewayTypesRepository.save(type);
  }

  findAllTypes() {
    return this.gatewayTypesRepository.find();
  }

  async create(createGatewayDto: CreateGatewayDto) {
    const { params, ...gatewayData } = createGatewayDto;
    
    // Convert typeId to string if present, or handle mismatch
    const gatewayDataWithType = {
      ...gatewayData,
      typeId: gatewayData.typeId ? String(gatewayData.typeId) : undefined,
    };

    const gateway = this.gatewaysRepository.create(gatewayDataWithType);
    
    if (params && params.length > 0) {
      gateway.params = params.map(p => {
        const param = new GatewayParam();
        param.label = p.label;
        param.value = p.value;
        return param;
      });
    }

    return this.gatewaysRepository.save(gateway);
  }

  findAll() {
    return this.gatewaysRepository.find({
      relations: ['params', 'type'],
      order: {
        priority: 'ASC', // Lower number = Higher priority (1 is top)
        createdAt: 'ASC',
      },
    });
  }

  findOne(id: string) {
    return this.gatewaysRepository.findOne({ where: { id }, relations: ['params', 'type'] });
  }

  async findHighestPriorityActiveGateway() {
    return this.gatewaysRepository.findOne({
      where: { isActive: true },
      relations: ['params', 'type'],
      order: {
        priority: 'ASC', // Lower number = Higher priority (1 is top)
        createdAt: 'ASC',
      },
    });
  }

  async update(id: string, updateGatewayDto: CreateGatewayDto) {
    const gateway = await this.findOne(id);
    if (!gateway) return null;

    Object.assign(gateway, updateGatewayDto);
    return this.gatewaysRepository.save(gateway);
  }

  remove(id: string) {
    return this.gatewaysRepository.delete(id);
  }
}
