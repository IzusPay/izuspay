import { Module } from '@nestjs/common';
import { TypeOrmModule } from '@nestjs/typeorm';
import { GatewaysService } from './gateways.service';
import { GatewaysController } from './gateways.controller';
import { Gateway } from './gateway.entity';
import { GatewayParam } from './gateway-param.entity';
import { GatewayType } from './gateway-type.entity';

@Module({
  imports: [TypeOrmModule.forFeature([Gateway, GatewayParam, GatewayType])],
  controllers: [GatewaysController],
  providers: [GatewaysService],
  exports: [GatewaysService],
})
export class GatewaysModule {}
