import { IsNotEmpty, IsString } from 'class-validator';
import { ApiProperty } from '@nestjs/swagger';

export class CreateApiKeyDto {
  @ApiProperty({ description: 'Friendly name for the API Key', example: 'My Website Integration' })
  @IsNotEmpty()
  @IsString()
  name: string;
}
