import { IsString, IsNotEmpty, IsEmail, IsNumber, Min, ValidateNested, IsObject } from 'class-validator';
import { Type } from 'class-transformer';
import { ApiProperty } from '@nestjs/swagger';

export class CustomerDto {
  @ApiProperty()
  @IsNotEmpty()
  @IsString()
  name: string;

  @ApiProperty()
  @IsNotEmpty()
  @IsEmail()
  email: string;

  @ApiProperty()
  @IsNotEmpty()
  @IsString()
  phone: string;

  @ApiProperty()
  @IsNotEmpty()
  @IsString()
  document: string;
}

export class CreateSaleApiDto {
  @ApiProperty()
  @IsNotEmpty()
  @IsNumber()
  @Min(0.01)
  amount: number;

  @ApiProperty()
  @IsObject()
  @ValidateNested()
  @Type(() => CustomerDto)
  customer: CustomerDto;
}
