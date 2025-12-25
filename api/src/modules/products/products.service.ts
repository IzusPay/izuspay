import { Injectable, NotFoundException } from '@nestjs/common';
import { InjectRepository } from '@nestjs/typeorm';
import { Repository } from 'typeorm';
import { Product } from './product.entity';
import { CreateProductDto } from './dto/create-product.dto';
import { UpdateProductDto } from './dto/update-product.dto';

@Injectable()
export class ProductsService {
  constructor(
    @InjectRepository(Product)
    private productsRepository: Repository<Product>,
  ) {}

  create(companyId: string, createProductDto: CreateProductDto) {
    const product = this.productsRepository.create({
      paymentMethods: createProductDto.paymentMethods || ['PIX'], // Ensure value is set
      ...createProductDto,
      companyId,
    });
    return this.productsRepository.save(product);
  }

  findAllByCompany(companyId: string) {
    return this.productsRepository.find({
      where: { companyId },
      order: { createdAt: 'DESC' },
    });
  }

  async findOne(id: string) {
    const product = await this.productsRepository.findOne({ where: { id } });
    if (!product) throw new NotFoundException('Product not found');
    return product;
  }

  async update(id: string, updateProductDto: UpdateProductDto) {
    const product = await this.findOne(id);
    this.productsRepository.merge(product, updateProductDto);
    return this.productsRepository.save(product);
  }

  async remove(id: string) {
    return this.productsRepository.delete(id);
  }
}
