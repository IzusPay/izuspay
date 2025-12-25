import { IsNumber, IsEnum, IsOptional, IsString, ValidateIf, Min } from 'class-validator';
import { WithdrawalMethod, PixKeyType } from '../withdrawal.entity';
import { ApiProperty } from '@nestjs/swagger';

export class RequestWithdrawalDto {
  @ApiProperty()
  @IsNumber()
  @Min(1)
  amount: number;

  @ApiProperty({ enum: WithdrawalMethod })
  @IsEnum(WithdrawalMethod)
  method: WithdrawalMethod;

  @ApiProperty({ required: false })
  @IsOptional()
  @IsString()
  bankAccountId?: string;

  @ApiProperty({ enum: PixKeyType, required: false })
  @IsOptional()
  @IsEnum(PixKeyType)
  pixKeyType?: PixKeyType;

  @ApiProperty({ required: false })
  @IsOptional()
  @IsString()
  pixKey?: string;
}
