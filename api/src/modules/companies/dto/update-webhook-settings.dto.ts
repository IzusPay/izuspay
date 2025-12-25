import { IsInt, Min, IsOptional } from 'class-validator';
import { ApiProperty } from '@nestjs/swagger';

export class UpdateWebhookSettingsDto {
  @ApiProperty({ description: 'Interval for skipping webhooks (Every Nth sale). Set to 0 or null to disable.', example: 5, nullable: true })
  @IsInt()
  @Min(0)
  @IsOptional()
  interval: number | null;
}
