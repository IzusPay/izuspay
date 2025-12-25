import { Controller, Post, Get, Param, UploadedFile, UseInterceptors, UseGuards, Body, Request } from '@nestjs/common';
import { FileInterceptor } from '@nestjs/platform-express';
import { DocumentsService } from './documents.service';
import { DocumentType } from './company-document.entity';
import { ApiTags, ApiOperation, ApiConsumes, ApiBody, ApiBearerAuth } from '@nestjs/swagger';
import { AuthGuard } from '@nestjs/passport';
import { RolesGuard } from '../../common/guards/roles.guard';
import { Roles } from '../../common/decorators/roles.decorator';
import { Role } from '../../common/enums/role.enum';
import { diskStorage } from 'multer';
import { extname } from 'path';

@ApiTags('Documents')
@Controller('documents')
@UseGuards(AuthGuard('jwt'), RolesGuard)
export class DocumentsController {
  constructor(private readonly documentsService: DocumentsService) {}

  @Post('upload')
  @ApiConsumes('multipart/form-data')
  @ApiBody({
    schema: {
      type: 'object',
      properties: {
        type: { type: 'string', enum: Object.values(DocumentType) },
        file: {
          type: 'string',
          format: 'binary',
        },
      },
    },
  })
  @UseInterceptors(FileInterceptor('file'))
  upload(
    @Request() req: any,
    @Body('type') type: DocumentType,
    @UploadedFile() file: Express.Multer.File,
  ) {
    return this.documentsService.upload(req.user.companyId, type, file);
  }

  @Get()
  findAll(@Request() req: any) {
    return this.documentsService.findAllByCompany(req.user.companyId);
  }

  @Post(':id/approve')
  @Roles(Role.Admin)
  approve(@Param('id') id: string) {
    return this.documentsService.approve(id);
  }

  @Post(':id/reject')
  @Roles(Role.Admin)
  reject(@Param('id') id: string, @Body('reason') reason: string) {
    return this.documentsService.reject(id, reason);
  }
}
