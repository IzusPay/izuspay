import { IsString, IsNotEmpty, IsEnum, IsOptional, IsBoolean } from 'class-validator';
import { ApiProperty } from '@nestjs/swagger';
import { BannerType } from '../banner.entity';

export class CreateBannerDto {
  @ApiProperty()
  @IsNotEmpty()
  @IsString()
  imageUrl: string;

  @ApiProperty({ enum: BannerType })
  @IsNotEmpty()
  @IsEnum(BannerType)
  type: BannerType;

  @ApiProperty()
  @IsOptional()
  @IsBoolean()
  active?: boolean;
}
