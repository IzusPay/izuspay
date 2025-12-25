import { Module } from '@nestjs/common';
import { TypeOrmModule } from '@nestjs/typeorm';
import { LegacyUser } from './entities/legacy-user.entity';

@Module({
  imports: [
    TypeOrmModule.forFeature([LegacyUser], 'legacy'),
  ],
  exports: [TypeOrmModule],
})
export class LegacyModule {}
