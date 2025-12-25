import { NestFactory } from '@nestjs/core';
import { ValidationPipe } from '@nestjs/common';
import { SwaggerModule, DocumentBuilder } from '@nestjs/swagger';
import { AppModule } from './app.module';

async function bootstrap() {
  const app = await NestFactory.create(AppModule);

  // Ativar validação global (para os DTOs funcionarem com class-validator)
  app.useGlobalPipes(new ValidationPipe({
    whitelist: true, // Remove propriedades não mapeadas no DTO
    forbidNonWhitelisted: true, // Erro se enviar propriedade extra
    transform: true, // Converte tipos automaticamente (ex: string -> number)
  }));

  // Configuração do Swagger
  const config = new DocumentBuilder()
    .setTitle('IzusPay API')
    .setDescription('Documentação da API do IzusPay (Migração Node.js)')
    .setVersion('1.0')
    .addBearerAuth() // Adiciona suporte a token JWT no Swagger
    .build();
  
  const document = SwaggerModule.createDocument(app, config);
  SwaggerModule.setup('api', app, document); // Swagger disponível em /api

  await app.listen(process.env.PORT ?? 3000);
  console.log(`Application is running on: ${await app.getUrl()}`);
  console.log(`Swagger documentation is running on: ${await app.getUrl()}/api`);
}
bootstrap();
