import { Module, Global } from '@nestjs/common';
import { TypeOrmModule } from '@nestjs/typeorm';
import { SystemFeesService } from './system-fees.service';
import { SystemFeesController } from './system-fees.controller';
import { SystemFee } from './system-fee.entity';

@Global()
@Module({
  imports: [TypeOrmModule.forFeature([SystemFee])],
  controllers: [SystemFeesController],
  providers: [SystemFeesService],
  exports: [SystemFeesService],
})
export class SystemFeesModule {}
