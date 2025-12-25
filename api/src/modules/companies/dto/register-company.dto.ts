import { IsNotEmpty, IsString, MinLength } from 'class-validator';
import { CreateCompanyDto } from './create-company.dto';
import { ApiProperty } from '@nestjs/swagger';

export class RegisterCompanyDto extends CreateCompanyDto {
  @ApiProperty({
    description: 'Password for panel access (used to create the company admin user)',
    minLength: 6,
    example: 'password123',
  })
  @IsNotEmpty()
  @IsString()
  @MinLength(6)
  password: string;
}
