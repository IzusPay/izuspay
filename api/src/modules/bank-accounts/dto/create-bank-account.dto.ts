import { IsString, IsNotEmpty, IsOptional, IsEnum } from 'class-validator';
import { ApiProperty } from '@nestjs/swagger';
import { PixKeyType } from '../../withdrawals/withdrawal.entity';

export class CreateBankAccountDto {
  @ApiProperty()
  @IsNotEmpty()
  @IsString()
  bankName: string;

  @ApiProperty()
  @IsNotEmpty()
  @IsString()
  agency: string;

  @ApiProperty()
  @IsNotEmpty()
  @IsString()
  accountNumber: string;

  @ApiProperty()
  @IsNotEmpty()
  @IsString()
  accountDigit: string;

  @ApiProperty({ enum: PixKeyType, required: false })
  @IsOptional()
  @IsEnum(PixKeyType)
  pixKeyType?: PixKeyType;

  @ApiProperty()
  @IsOptional()
  @IsString()
  pixKey?: string;
}
