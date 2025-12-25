import { DataSource } from 'typeorm';
import { User } from '../modules/users/user.entity';
import { Role } from '../common/enums/role.enum';
import * as bcrypt from 'bcrypt';
import * as dotenv from 'dotenv';
import { Company } from '../modules/companies/company.entity';

dotenv.config();

const dataSource = new DataSource({
  type: 'postgres',
  host: process.env.DB_HOST,
  port: parseInt(process.env.DB_PORT || '5432', 10),
  username: process.env.DB_USERNAME,
  password: process.env.DB_PASSWORD,
  database: process.env.DB_DATABASE,
  entities: [__dirname + '/../modules/**/*.entity.ts'], // Load all entities to avoid missing relation metadata
  synchronize: false,
});

async function run() {
  try {
    await dataSource.initialize();
    console.log('Data Source initialized');

    const userRepository = dataSource.getRepository(User);
    const adminEmail = 'admin@gmail.com';
    const existingAdmin = await userRepository.findOne({ where: { email: adminEmail } });

    if (existingAdmin) {
      console.log('Admin user already exists');
      return;
    }

    const hashedPassword = await bcrypt.hash('123456', 10);
    const adminUser = userRepository.create({
      name: 'Admin',
      email: adminEmail,
      password: hashedPassword,
      role: Role.Admin,
      status: 'active',
      companyId: null,
    });

    await userRepository.save(adminUser);
    console.log('Admin user created successfully');
  } catch (error) {
    console.error('Error seeding admin user:', error);
  } finally {
    await dataSource.destroy();
  }
}

run();
