import { IsString, IsNotEmpty, IsOptional, IsNumber, IsArray, ValidateNested, IsBoolean } from 'class-validator';
import { Type } from 'class-transformer';
import { ApiProperty } from '@nestjs/swagger';

class GatewayParamDto {
  @ApiProperty()
  @IsNotEmpty()
  @IsString()
  label: string;

  @ApiProperty()
  @IsNotEmpty()
  @IsString()
  value: string;
}

export class CreateGatewayDto {
  @ApiProperty()
  @IsNotEmpty()
  @IsString()
  name: string;

  @ApiProperty()
  @IsOptional()
  @IsString()
  image: string;

  @ApiProperty()
  @IsNotEmpty()
  @IsString()
  apiUrl: string;

  @ApiProperty({ type: [GatewayParamDto] })
  @IsArray()
  @ValidateNested({ each: true })
  @Type(() => GatewayParamDto)
  params: GatewayParamDto[];

  @ApiProperty()
  @IsNumber()
  transactionFeePercentage: number;

  @ApiProperty()
  @IsNumber()
  transactionFeeFixed: number;

  @ApiProperty({ required: false, default: 0 })
  @IsOptional()
  @IsNumber()
  costFeePercentage?: number;

  @ApiProperty({ required: false, default: 0 })
  @IsOptional()
  @IsNumber()
  costFeeFixed?: number;

  @ApiProperty()
  @IsOptional()
  @IsString()
  typeId?: string;

  @ApiProperty({ required: false, default: 0 })
  @IsOptional()
  @IsNumber()
  priority?: number;

  @ApiProperty({ required: false, default: true })
  @IsOptional()
  @IsBoolean()
  isActive?: boolean;
}
