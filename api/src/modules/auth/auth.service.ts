import { Injectable, UnauthorizedException, BadRequestException } from '@nestjs/common';
import { JwtService } from '@nestjs/jwt';
import { UsersService } from '../users/users.service';
import * as bcrypt from 'bcrypt';
import { authenticator } from 'otplib';
import { toDataURL } from 'qrcode';
import { JwtPayload } from './interfaces/jwt-payload.interface';

@Injectable()
export class AuthService {
  constructor(
    private usersService: UsersService,
    private jwtService: JwtService,
  ) {}

  async signIn(email: string, pass: string): Promise<any> {
    const user = await this.usersService.findByEmail(email);
    if (!user) {
      throw new UnauthorizedException();
    }
    const isMatch = await bcrypt.compare(pass, user.password);
    if (!isMatch) {
      throw new UnauthorizedException();
    }

    // Check if 2FA is enabled
    if (user.isTwoFactorEnabled) {
      return {
        isTwoFactorEnabled: true,
        temp_token: this.jwtService.sign({ sub: user.id, isTwoFactorTemp: true }, { expiresIn: '5m' }), // Temporary token for 2FA verification
        user: { id: user.id, email: user.email }
      };
    }

    return this.login(user);
  }

  async login(user: any) {
    const fullUser = await this.usersService.findByIdWithPermissions(user.id);
    
    const permissions: Record<string, any> = {};
    if (fullUser && fullUser.accessRole && fullUser.accessRole.permissions) {
      fullUser.accessRole.permissions.forEach((curr) => {
        if (curr.module) {
          permissions[curr.module.key] = {
            canCreate: curr.canCreate,
            canRead: curr.canRead,
            canUpdate: curr.canUpdate,
            canDelete: curr.canDelete,
            canDetail: curr.canDetail,
          };
        }
      });
    }

    const payload: JwtPayload = { username: user.email, sub: user.id, companyId: user.companyId, role: user.role };
    return {
      access_token: this.jwtService.sign(payload),
      user: {
        id: user.id,
        email: user.email,
        name: user.name,
        role: user.role,
        companyId: user.companyId,
        isTwoFactorEnabled: user.isTwoFactorEnabled,
        permissions: permissions
      }
    };
  }

  async getProfile(userId: string) {
    const user = await this.usersService.findByIdWithPermissions(userId);
    if (!user) {
      throw new UnauthorizedException('User not found');
    }

    const permissions: Record<string, any> = {};
    if (user.accessRole && user.accessRole.permissions) {
      user.accessRole.permissions.forEach((curr) => {
        if (curr.module) {
          permissions[curr.module.key] = {
            canCreate: curr.canCreate,
            canRead: curr.canRead,
            canUpdate: curr.canUpdate,
            canDelete: curr.canDelete,
            canDetail: curr.canDetail,
          };
        }
      });
    }

    return {
      id: user.id,
      email: user.email,
      name: user.name,
      role: user.role,
      companyId: user.companyId,
      isTwoFactorEnabled: user.isTwoFactorEnabled,
      permissions: permissions
    };
  }

  async generateTwoFactorSecret(user: any) {
    const secret = authenticator.generateSecret();
    const otpauthUrl = authenticator.keyuri(user.email, 'IzusPay', secret);

    await this.usersService.setTwoFactorSecret(user.id, secret);

    return {
      secret,
      qrCode: await toDataURL(otpauthUrl),
    };
  }

  async verifyTwoFactor(token: string, code: string) {
    // Decode temp token to get user ID
    let userId;
    try {
      const decoded = this.jwtService.verify(token);
      if (!decoded.isTwoFactorTemp) throw new Error();
      userId = decoded.sub;
    } catch {
      throw new UnauthorizedException('Invalid or expired 2FA session');
    }

    const user = await this.usersService.findByIdWithSecret(userId);
    if (!user || !user.twoFactorSecret) {
      throw new UnauthorizedException('2FA not setup for this user');
    }

    const isValid = authenticator.verify({
      token: code,
      secret: user.twoFactorSecret,
    });

    if (!isValid) {
      throw new UnauthorizedException('Invalid 2FA code');
    }

    return this.login(user);
  }

  async enableTwoFactor(userId: string, code: string) {
    const user = await this.usersService.findByIdWithSecret(userId);
    if (!user || !user.twoFactorSecret) {
      throw new BadRequestException('2FA secret not generated');
    }

    const isValid = authenticator.verify({
      token: code,
      secret: user.twoFactorSecret,
    });

    if (!isValid) {
      throw new BadRequestException('Invalid authentication code');
    }

    await this.usersService.enableTwoFactor(userId);
    return { success: true };
  }
}
