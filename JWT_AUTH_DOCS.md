# Documentação de Autenticação JWT (IzusPay API)

Esta API utiliza JSON Web Tokens (JWT) para autenticação. O token obtido deve ser enviado no cabeçalho `Authorization` de todas as requisições subsequentes.

## Base URL
Todas as rotas de autenticação estão prefixadas com: `/api/auth`

## Endpoints

### 1. Login
Autentica um usuário e retorna um token de acesso.

- **Método:** `POST`
- **Rota:** `/api/auth/login`
- **Body (JSON):**
  ```json
  {
    "email": "usuario@exemplo.com",
    "password": "sua_senha"
  }
  ```
- **Resposta (200 OK):**
  ```json
  {
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbG...",
    "token_type": "bearer",
    "expires_in": 3600
  }
  ```

### 2. Dados do Usuário (Me)
Retorna os dados do usuário autenticado.

- **Método:** `POST`
- **Rota:** `/api/auth/me`
- **Headers:**
  - `Authorization: Bearer <seu_token>`
- **Resposta (200 OK):**
  ```json
  {
    "id": 1,
    "name": "Nome do Usuário",
    "email": "usuario@exemplo.com",
    ...
  }
  ```

### 3. Atualizar Token (Refresh)
Renova o token atual. Útil quando o token está prestes a expirar.

- **Método:** `POST`
- **Rota:** `/api/auth/refresh`
- **Headers:**
  - `Authorization: Bearer <seu_token>`
- **Resposta (200 OK):**
  ```json
  {
    "access_token": "novo_token_jwt...",
    "token_type": "bearer",
    "expires_in": 3600
  }
  ```

### 4. Logout
Invalida o token atual.

- **Método:** `POST`
- **Rota:** `/api/auth/logout`
- **Headers:**
  - `Authorization: Bearer <seu_token>`
- **Resposta (200 OK):**
  ```json
  {
    "message": "Successfully logged out"
  }
  ```

## Uso nas Rotas Protegidas
O token JWT gerado permite acesso a todas as rotas da API protegidas (ex: `/api/transactions`, `/api/withdrawals`, etc.).