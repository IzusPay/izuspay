import { Controller, Get, Post, Body, Put, Param, Delete, UseGuards } from '@nestjs/common';
import { GatewaysService } from './gateways.service';
import { CreateGatewayDto } from './dto/create-gateway.dto';
import { CreateGatewayTypeDto } from './dto/create-gateway-type.dto';
import { JwtAuthGuard } from '../auth/guards/jwt-auth.guard';
import { ApiTags, ApiBearerAuth } from '@nestjs/swagger';

@ApiTags('gateways')
@ApiBearerAuth()
@UseGuards(JwtAuthGuard)
@Controller('gateways')
export class GatewaysController {
  constructor(private readonly gatewaysService: GatewaysService) {}

  @Post('types')
  createType(@Body() createGatewayTypeDto: CreateGatewayTypeDto) {
    return this.gatewaysService.createType(createGatewayTypeDto.name, createGatewayTypeDto.slug);
  }

  @Get('types')
  findAllTypes() {
    return this.gatewaysService.findAllTypes();
  }

  @Post()
  create(@Body() createGatewayDto: CreateGatewayDto) {
    return this.gatewaysService.create(createGatewayDto);
  }

  @Get()
  findAll() {
    return this.gatewaysService.findAll();
  }

  @Get(':id')
  findOne(@Param('id') id: string) {
    return this.gatewaysService.findOne(id);
  }

  @Put(':id')
  update(@Param('id') id: string, @Body() updateGatewayDto: CreateGatewayDto) {
    return this.gatewaysService.update(id, updateGatewayDto);
  }

  @Delete(':id')
  remove(@Param('id') id: string) {
    return this.gatewaysService.remove(id);
  }
}
