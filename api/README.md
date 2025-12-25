# IzusPay Backend (Node.js/NestJS)

Este é o novo serviço de backend em Node.js para a migração gradual do sistema IzusPay.
O objetivo inicial é migrar o processamento de **Webhooks** e filas (RabbitMQ).

## Estrutura
- Framework: [NestJS](https://nestjs.com/)
- Banco de Dados: MySQL (Conectado ao mesmo banco do Laravel via TypeORM)
- Filas: RabbitMQ (via `@nestjs/microservices`)
- Autenticação: JWT (Compatível com Laravel/Bcrypt)

## Configuração
1.  Instale as dependências:
    ```bash
    npm install
    ```
2.  Configure o `.env` (já criado com base no Laravel):
    ```env
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_USERNAME=root
    DB_PASSWORD=
    DB_DATABASE=izus
    PORT=3001
    JWT_SECRET=seu_segredo_jwt_do_laravel
    ```
3.  Inicie o servidor de desenvolvimento:
    ```bash
    npm run start:dev
    ```

## Módulos Migrados
- [x] **Auth**: Login via JWT com verificação de senha Bcrypt (compatível com Laravel).
- [ ] **Webhooks**: Estrutura inicial criada (`src/webhooks`).
- [ ] **Filas**: Dependências instaladas (`amqplib`).

## Endpoints de Autenticação
- `POST /auth/login`: Realiza login (Body: `{ "email": "...", "password": "..." }`)
- `GET /auth/profile`: Retorna dados do usuário logado (Requer Header `Authorization: Bearer <token>`)

## Próximos Passos
1.  Implementar o endpoint de recebimento de Webhooks no `WebhooksController`.
2.  Configurar o Producer do RabbitMQ para enviar eventos para processamento assíncrono.
3.  Criar os Consumers para processar esses eventos.
