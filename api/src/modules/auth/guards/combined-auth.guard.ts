import { Injectable, ExecutionContext, UnauthorizedException } from '@nestjs/common';
import { AuthGuard } from '@nestjs/passport';
import { ApiKeysService } from '../../api-keys/api-keys.service';

@Injectable()
export class CombinedAuthGuard extends AuthGuard('jwt') {
  constructor(private apiKeysService: ApiKeysService) {
    super();
  }

  async canActivate(context: ExecutionContext): Promise<boolean> {
    const request = context.switchToHttp().getRequest();
    const apiKey = request.headers['x-api-key'];

    // 1. If API Key is present, validate it
    if (apiKey) {
      const validKey = await this.apiKeysService.validateKey(apiKey);
      if (validKey) {
        request.user = {
          companyId: validKey.companyId,
          isApiKey: true,
          role: 'company_admin',
        };
        return true;
      }
      throw new UnauthorizedException('Invalid API Key');
    }

    // 2. Fallback to JWT (Standard AuthGuard behavior)
    try {
      return (await super.canActivate(context)) as boolean;
    } catch (err) {
      throw err;
    }
  }
}
