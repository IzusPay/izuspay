import { IsNotEmpty, IsUrl, IsArray, IsOptional, IsBoolean } from 'class-validator';
import { ApiProperty } from '@nestjs/swagger';

export class CreateWebhookDto {
  @ApiProperty({ description: 'The URL to receive webhooks', example: 'https://mysite.com/webhook' })
  @IsNotEmpty()
  @IsUrl()
  url: string;

  @ApiProperty({ description: 'Events to subscribe to', example: ['sale.created', 'sale.paid'] })
  @IsOptional()
  @IsArray()
  events?: string[];

  @ApiProperty({ description: 'Description of the webhook', example: 'Main production webhook' })
  @IsOptional()
  description?: string;
}
