import { Controller, Get, Post, Body, Patch, Param, Delete, UseGuards, Request } from '@nestjs/common';
import { ProductsService } from './products.service';
import { CreateProductDto } from './dto/create-product.dto';
import { UpdateProductDto } from './dto/update-product.dto';
import { ApiTags, ApiBearerAuth, ApiOperation } from '@nestjs/swagger';
import { CombinedAuthGuard } from '../auth/guards/combined-auth.guard';
import { Public } from '../../common/decorators/public.decorator';

@ApiTags('Products (Payment Links)')
@Controller('products')
export class ProductsController {
  constructor(private readonly productsService: ProductsService) {}

  @Post()
  @UseGuards(CombinedAuthGuard)
  @ApiBearerAuth()
  @ApiOperation({ summary: 'Create a new payment link/product' })
  create(@Request() req: any, @Body() createProductDto: CreateProductDto) {
    return this.productsService.create(req.user.companyId, createProductDto);
  }

  @Get()
  @UseGuards(CombinedAuthGuard)
  @ApiBearerAuth()
  @ApiOperation({ summary: 'List all products for the authenticated company' })
  findAll(@Request() req: any) {
    return this.productsService.findAllByCompany(req.user.companyId);
  }

  @Public()
  @Get(':id')
  @ApiOperation({ summary: 'Get product details (Public for Checkout)' })
  findOne(@Param('id') id: string) {
    return this.productsService.findOne(id);
  }

  @Patch(':id')
  @UseGuards(CombinedAuthGuard)
  @ApiBearerAuth()
  @ApiOperation({ summary: 'Update product' })
  update(@Param('id') id: string, @Body() updateProductDto: UpdateProductDto) {
    return this.productsService.update(id, updateProductDto);
  }

  @Delete(':id')
  @UseGuards(CombinedAuthGuard)
  @ApiBearerAuth()
  @ApiOperation({ summary: 'Delete product' })
  remove(@Param('id') id: string) {
    return this.productsService.remove(id);
  }
}
