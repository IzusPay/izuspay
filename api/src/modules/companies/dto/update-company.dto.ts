import { PartialType } from '@nestjs/mapped-types';
import { CreateCompanyDto } from './create-company.dto';
import { IsOptional, ValidateNested, IsNumber, Min, IsEnum } from 'class-validator';
import { Type } from 'class-transformer';
import { CompanyAddressDto } from './company-address.dto';
import { ApiProperty } from '@nestjs/swagger';
import { CompanyStatus } from '../company.entity';

export class UpdateCompanyDto extends PartialType(CreateCompanyDto) {
  @ApiProperty({ type: CompanyAddressDto, required: false })
  @IsOptional()
  @ValidateNested()
  @Type(() => CompanyAddressDto)
  address?: CompanyAddressDto;

  @ApiProperty({ enum: CompanyStatus, required: false })
  @IsOptional()
  @IsEnum(CompanyStatus)
  status?: CompanyStatus;

  @ApiProperty({ description: 'Withdrawal fee', required: false })
  @IsOptional()
  @IsNumber()
  @Min(0)
  withdrawalFee?: number;

  @ApiProperty({ description: 'Transaction fee percentage', required: false })
  @IsOptional()
  @IsNumber()
  @Min(0)
  transactionFeePercentage?: number;

  @ApiProperty({ description: 'Transaction fee fixed', required: false })
  @IsOptional()
  @IsNumber()
  @Min(0)
  transactionFeeFixed?: number;

  @ApiProperty({ description: 'Webhook skip interval', required: false })
  @IsOptional()
  @IsNumber()
  @Min(0)
  webhookSkipInterval?: number;
}
