import { Controller, Get, Post, Delete, Body, Param, UseGuards, Request } from '@nestjs/common';
import { ApiKeysService } from './api-keys.service';
import { CreateApiKeyDto } from './dto/create-api-key.dto';
import { ApiTags, ApiOperation, ApiBearerAuth } from '@nestjs/swagger';
import { CombinedAuthGuard } from '../auth/guards/combined-auth.guard';

@ApiTags('API Keys')
@Controller('api-keys')
@UseGuards(CombinedAuthGuard)
@ApiBearerAuth()
export class ApiKeysController {
  constructor(private readonly apiKeysService: ApiKeysService) {}

  @Post()
  @ApiOperation({ summary: 'Create a new API Key' })
  create(@Request() req: any, @Body() createApiKeyDto: CreateApiKeyDto) {
    return this.apiKeysService.create(req.user.companyId, createApiKeyDto);
  }

  @Get()
  @ApiOperation({ summary: 'List all API Keys' })
  findAll(@Request() req: any) {
    return this.apiKeysService.findAllByCompany(req.user.companyId);
  }

  @Get(':id/reveal')
  @ApiOperation({ summary: 'Reveal the plain text API Key' })
  reveal(@Request() req: any, @Param('id') id: string) {
    return this.apiKeysService.revealKey(id, req.user.companyId);
  }

  @Delete(':id')
  @ApiOperation({ summary: 'Revoke an API Key' })
  remove(@Request() req: any, @Param('id') id: string) {
    return this.apiKeysService.remove(id, req.user.companyId);
  }
}
