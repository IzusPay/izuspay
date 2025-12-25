import { IsString, IsNotEmpty, IsEmail, IsOptional, IsUUID } from 'class-validator';
import { ApiProperty } from '@nestjs/swagger';

export class CreateSaleDto {
  @ApiProperty({ description: 'Product ID (Payment Link)' })
  @IsNotEmpty()
  @IsUUID()
  productId: string;

  @ApiProperty()
  @IsNotEmpty()
  @IsString()
  payerName: string;

  @ApiProperty()
  @IsNotEmpty()
  @IsEmail()
  payerEmail: string;

  @ApiProperty()
  @IsNotEmpty()
  @IsString()
  payerDocument: string;

  @ApiProperty({ required: false })
  @IsOptional()
  @IsString()
  payerPhone?: string;
}
