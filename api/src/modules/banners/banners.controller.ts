import { Controller, Get, Post, Body, Patch, Param, Delete, UseGuards, Request, UseInterceptors, UploadedFile } from '@nestjs/common';
import { BannersService } from './banners.service';
import { CreateBannerDto } from './dto/create-banner.dto';
import { UpdateBannerDto } from './dto/update-banner.dto';
import { ApiTags, ApiBearerAuth, ApiOperation, ApiConsumes, ApiBody } from '@nestjs/swagger';
import { JwtAuthGuard } from '../auth/guards/jwt-auth.guard';
import { FileInterceptor } from '@nestjs/platform-express';
import { BannerType } from './banner.entity';

@ApiTags('banners')
@ApiBearerAuth()
@UseGuards(JwtAuthGuard)
@Controller('banners')
export class BannersController {
  constructor(private readonly bannersService: BannersService) {}

  @Post()
  @UseInterceptors(FileInterceptor('file'))
  @ApiConsumes('multipart/form-data')
  @ApiBody({
    schema: {
      type: 'object',
      properties: {
        type: { type: 'string', enum: Object.values(BannerType) },
        file: {
          type: 'string',
          format: 'binary',
        },
      },
    },
  })
  create(
    @Request() req: any,
    @Body('type') type: BannerType,
    @UploadedFile() file: Express.Multer.File,
  ) {
    return this.bannersService.create(req.user.companyId, type, file);
  }

  @Get()
  findAll(@Request() req: any) {
    return this.bannersService.findAllByCompany(req.user.companyId);
  }

  @Delete(':id')
  remove(@Param('id') id: string) {
    return this.bannersService.remove(id);
  }
}
