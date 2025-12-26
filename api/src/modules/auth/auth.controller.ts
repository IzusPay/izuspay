import { Body, Controller, Post, HttpCode, HttpStatus, Get, UseGuards, Request } from '@nestjs/common';
import { ApiTags, ApiOperation, ApiResponse, ApiBearerAuth, ApiBody } from '@nestjs/swagger';
import { AuthService } from './auth.service';
import { AuthGuard } from '@nestjs/passport';
import { RolesGuard } from '../../common/guards/roles.guard';
import { Roles } from '../../common/decorators/roles.decorator';
import { Role } from '../../common/enums/role.enum';
import { LoginDto } from './dto/login.dto';

@ApiTags('Auth')
@Controller('auth')
export class AuthController {
  constructor(private authService: AuthService) {}

  @ApiOperation({ summary: 'Realizar login e obter token JWT' })
  @ApiResponse({ status: 200, description: 'Login realizado com sucesso.' })
  @ApiResponse({ status: 401, description: 'Credenciais inválidas.' })
  @ApiBody({ type: LoginDto })
  @HttpCode(HttpStatus.OK)
  @Post('login')
  signIn(@Body() signInDto: LoginDto) {
    return this.authService.signIn(signInDto.email, signInDto.password);
  }

  @ApiBearerAuth()
  @ApiOperation({ summary: 'Generate 2FA Secret and QR Code' })
  @UseGuards(AuthGuard('jwt'))
  @Get('2fa/generate')
  async generateTwoFactor(@Request() req: any) {
    return this.authService.generateTwoFactorSecret(req.user);
  }

  @ApiBearerAuth()
  @ApiOperation({ summary: 'Enable 2FA with code' })
  @UseGuards(AuthGuard('jwt'))
  @Post('2fa/enable')
  async enableTwoFactor(@Request() req: any, @Body() body: { code: string }) {
    return this.authService.enableTwoFactor(req.user.id, body.code);
  }

  @ApiOperation({ summary: 'Verify 2FA code during login' })
  @HttpCode(HttpStatus.OK)
  @Post('2fa/authenticate')
  async verifyTwoFactor(@Body() body: { token: string; code: string }) {
    return this.authService.verifyTwoFactor(body.token, body.code);
  }

  @ApiBearerAuth()
  @ApiOperation({ summary: 'Obter perfil do usuário logado' })
  @UseGuards(AuthGuard('jwt'))
  @Get('profile')
  getProfile(@Request() req: any) {
    return req.user;
  }

  @ApiBearerAuth()
  @ApiOperation({ summary: 'Rota exclusiva para administradores (Exemplo RBAC)' })
  @UseGuards(AuthGuard('jwt'), RolesGuard)
  @Roles(Role.Admin)
  @Get('admin')
  getAdminOnlyData() {
    return { message: 'Acesso permitido apenas para administradores.' };
  }
}
