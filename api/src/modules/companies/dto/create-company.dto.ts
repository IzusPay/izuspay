import { IsEmail, IsNotEmpty, IsOptional, IsEnum, IsString, Length } from 'class-validator';
import { ApiProperty } from '@nestjs/swagger';

export class CreateCompanyDto {
  @ApiProperty({ description: 'Company name' })
  @IsNotEmpty()
  @IsString()
  name: string;

  @ApiProperty({ description: 'Unique slug for URL', required: false })
  @IsOptional()
  @IsString()
  slug?: string;

  @ApiProperty({ description: 'Person type', enum: ['individual', 'company'] })
  @IsNotEmpty()
  @IsEnum(['individual', 'company'])
  type: 'individual' | 'company';

  @ApiProperty({ description: 'Document (CPF/CNPJ)' })
  @IsNotEmpty()
  @IsString()
  document: string;

  @ApiProperty({ description: 'Contact email (for User creation)' })
  @IsNotEmpty()
  @IsEmail()
  email: string;

  @ApiProperty({ description: 'Contact phone' })
  @IsNotEmpty()
  @IsString()
  phone: string;

  // Address Fields
  @ApiProperty({ description: 'Street' })
  @IsNotEmpty()
  @IsString()
  street: string;

  @ApiProperty({ description: 'Address number' })
  @IsNotEmpty()
  @IsString()
  number: string;

  @ApiProperty({ description: 'Address complement', required: false })
  @IsOptional()
  @IsString()
  complement?: string;

  @ApiProperty({ description: 'Neighborhood' })
  @IsNotEmpty()
  @IsString()
  neighborhood: string;

  @ApiProperty({ description: 'City' })
  @IsNotEmpty()
  @IsString()
  city: string;

  @ApiProperty({ description: 'State (UF)', minLength: 2, maxLength: 2 })
  @IsNotEmpty()
  @IsString()
  @Length(2, 2)
  state: string;

  @ApiProperty({ description: 'Zip Code', minLength: 8, maxLength: 9 })
  @IsNotEmpty()
  @IsString()
  @Length(8, 9)
  zipCode: string;
}
