import { PartialType } from '@nestjs/swagger';
import { CreateWebhookDto } from './create-webhook.dto';
import { IsBoolean, IsOptional } from 'class-validator';
import { ApiProperty } from '@nestjs/swagger';

export class UpdateWebhookDto extends PartialType(CreateWebhookDto) {
  @ApiProperty({ required: false })
  @IsOptional()
  @IsBoolean()
  active?: boolean;

  // Frontend sends is_active, need to handle that or change frontend.
  // Ideally change frontend to match DTO, but user said "radio n funcionou" implying existing code.
  // I'll add is_active alias here just in case I can map it in controller.
  @ApiProperty({ required: false, name: 'is_active' })
  @IsOptional()
  @IsBoolean()
  is_active?: boolean;
}
