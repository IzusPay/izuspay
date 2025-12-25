import { Controller, Get, Body, Param, UseGuards, Patch, Delete } from '@nestjs/common';
import { SystemFeesService } from './system-fees.service';
import { FeeType } from './system-fee.entity';
import { JwtAuthGuard } from '../auth/guards/jwt-auth.guard';
import { ApiTags, ApiBearerAuth } from '@nestjs/swagger';

@ApiTags('system-fees')
@ApiBearerAuth()
@UseGuards(JwtAuthGuard)
@Controller('system-fees')
export class SystemFeesController {
  constructor(private readonly systemFeesService: SystemFeesService) {}

  @Get()
  findAll() {
    return this.systemFeesService.findAll();
  }

  @Patch(':id')
  update(@Param('id') id: string, @Body() body: any) {
    return this.systemFeesService.update(id, body);
  }

  @Delete(':id')
  remove(@Param('id') id: string) {
    return this.systemFeesService.remove(id);
  }
}
