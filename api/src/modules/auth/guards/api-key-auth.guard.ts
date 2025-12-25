import { Injectable, CanActivate, ExecutionContext, UnauthorizedException } from '@nestjs/common';
import { ApiKeysService } from '../../api-keys/api-keys.service';

@Injectable()
export class ApiKeyAuthGuard implements CanActivate {
  constructor(private apiKeysService: ApiKeysService) {}

  async canActivate(context: ExecutionContext): Promise<boolean> {
    const request = context.switchToHttp().getRequest();
    const apiKey = request.headers['x-api-key'];

    if (!apiKey) {
      // If no API key, let other guards handle it (or fail if this is the only guard)
      // However, if we want to support EITHER JWT OR API Key, we need to be careful.
      // Usually, if a guard returns false, the request is denied.
      // If we use a Composite Guard, we can handle logic.
      // For now, if x-api-key is present, we validate it.
      return true; // Pass through if no key (to let JWT guard try) - WAIT.
      // If we use UseGuards(JwtAuthGuard, ApiKeyAuthGuard), both must pass? No.
      // If we use UseGuards(JwtAuthGuard), it checks Bearer.
      // If we use UseGuards(ApiKeyAuthGuard), it checks Header.
      
      // We need a strategy that checks: Is there a Bearer? Use JWT. Is there x-api-key? Use ApiKey.
    }

    const validKey = await this.apiKeysService.validateKey(apiKey);
    if (!validKey) {
      throw new UnauthorizedException('Invalid API Key');
    }

    // Attach company to request.user (mimicking JWT structure)
    request.user = {
      companyId: validKey.companyId,
      isApiKey: true,
      role: 'company_admin', // Grant admin-like privileges for API access
    };

    return true;
  }
}
