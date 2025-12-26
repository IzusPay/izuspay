import { Injectable } from '@nestjs/common';
import { InjectRepository } from '@nestjs/typeorm';
import { Repository } from 'typeorm';
import { User } from './user.entity';

@Injectable()
export class UsersService {
  constructor(
    @InjectRepository(User)
    private usersRepository: Repository<User>,
  ) {}

  async findAll(): Promise<User[]> {
    return this.usersRepository.find({
      relations: ['company', 'accessRole'],
    });
  }

  async findOne(id: string): Promise<User | null> {
    return this.usersRepository.findOne({ where: { id } });
  }

  async findByEmail(email: string): Promise<User | null> {
    return this.usersRepository.findOne({
      where: { email },
      relations: ['company'],
      select: ['id', 'email', 'name', 'password', 'role', 'companyId', 'status', 'isTwoFactorEnabled', 'createdAt', 'updatedAt'], // Explicitly select password and 2FA status
    });
  }

  async findByIdWithSecret(id: string): Promise<User | null> {
    return this.usersRepository.findOne({
      where: { id },
      relations: ['company'],
      select: ['id', 'email', 'name', 'role', 'companyId', 'twoFactorSecret', 'isTwoFactorEnabled'],
    });
  }

  async findByIdWithPermissions(id: string): Promise<User | null> {
    return this.usersRepository.findOne({
      where: { id },
      relations: ['accessRole', 'accessRole.permissions', 'accessRole.permissions.module'],
    });
  }

  async setTwoFactorSecret(id: string, secret: string) {
    return this.usersRepository.update(id, { twoFactorSecret: secret });
  }

  async enableTwoFactor(id: string) {
    return this.usersRepository.update(id, { isTwoFactorEnabled: true });
  }

  async update(id: string, updateUserDto: any): Promise<User> {
    const user = await this.findOne(id);
    if (!user) {
      throw new Error('User not found');
    }
    this.usersRepository.merge(user, updateUserDto);
    return this.usersRepository.save(user);
  }

  async remove(id: string): Promise<void> {
    await this.usersRepository.delete(id);
  }

  create(userData: Partial<User>): Promise<User> {
    const user = this.usersRepository.create(userData);
    return this.usersRepository.save(user);
  }
}
