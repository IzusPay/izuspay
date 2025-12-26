import { IsEmail, IsNotEmpty, IsString, MinLength, IsOptional, IsEnum } from 'class-validator';
import { ApiProperty } from '@nestjs/swagger';
import { Role } from '../../../common/enums/role.enum';

export class CreateUserDto {
  @ApiProperty({ example: 'John Doe' })
  @IsNotEmpty()
  @IsString()
  name: string;

  @ApiProperty({ example: 'john@example.com' })
  @IsNotEmpty()
  @IsEmail()
  email: string;

  @ApiProperty({ example: '123456' })
  @IsNotEmpty()
  @IsString()
  @MinLength(6)
  password: string;

  @ApiProperty({ enum: Role, default: Role.Client })
  @IsEnum(Role)
  @IsOptional()
  role?: Role;

  @ApiProperty({ example: 'uuid-company-id', required: false })
  @IsOptional()
  @IsString()
  companyId?: string;
  
  @ApiProperty({ example: 'uuid-role-id', required: false, description: 'ACL Role ID' })
  @IsOptional()
  @IsString()
  accessRoleId?: string;
}
