import { IsNotEmpty, IsOptional, IsString, Length } from 'class-validator';
import { ApiProperty } from '@nestjs/swagger';

export class CompanyAddressDto {
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
