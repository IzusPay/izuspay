import { IsString, IsNotEmpty, IsOptional, IsNumber, IsBoolean, IsArray, IsUrl, IsHexColor } from 'class-validator';
import { ApiProperty } from '@nestjs/swagger';

export class CreateProductDto {
  @ApiProperty({ description: 'Internal name for the link' })
  @IsNotEmpty()
  @IsString()
  name: string;

  @ApiProperty({ description: 'Public product name displayed at checkout' })
  @IsNotEmpty()
  @IsString()
  productName: string;

  @ApiProperty({ required: false })
  @IsOptional()
  @IsString()
  description?: string;

  @ApiProperty()
  @IsNotEmpty()
  @IsNumber()
  amount: number;

  @ApiProperty({ required: false, default: true })
  @IsOptional()
  @IsBoolean()
  active?: boolean;

  @ApiProperty({ required: false, default: ['PIX'] })
  @IsOptional()
  @IsArray()
  paymentMethods?: string[];

  @ApiProperty({ required: false })
  @IsOptional()
  @IsString()
  imageUrl?: string;

  @ApiProperty({ required: false, description: 'Hex color code' })
  @IsOptional()
  @IsString()
  mainColor?: string;
}
