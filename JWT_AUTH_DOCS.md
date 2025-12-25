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

### 5. Gerenciamento de Chaves de API (ApiKeys)
Gerencie as chaves de API para integrações externas.

#### Listar Chaves
- **Método:** `GET`
- **Rota:** `/api/api-keys`
- **Headers:** `Authorization: Bearer <seu_token>`
- **Resposta (200 OK):**
  ```json
  [
    {
      "id": 1,
      "name": "Minha Integração",
      "environment": "production",
      "active": true,
      "created_at": "2023-10-27T10:00:00.000000Z",
      "token_preview": "sk_...a1b2"
    }
  ]
  ```

#### Criar Nova Chave
- **Método:** `POST`
- **Rota:** `/api/api-keys`
- **Body (JSON):**
  ```json
  {
    "name": "Minha Integração",
    "environment": "production" 
  }
  ```
  *(Nota: `environment` pode ser `production` ou `sandbox`)*

- **Resposta (201 Created):**
  ```json
  {
    "message": "Chave de API criada com sucesso.",
    "api_key": {
      "id": 2,
      "name": "Minha Integração",
      "token": "sk_1234567890abcdef...", // Exibido apenas uma vez!
      "environment": "production",
      "active": true
    }
  }
  ```

#### Excluir Chave
- **Método:** `DELETE`
- **Rota:** `/api/api-keys/{id}`
- **Headers:** `Authorization: Bearer <seu_token>`
- **Resposta (200 OK):**
  ```json
  {
    "message": "Chave removida com sucesso."
  }
  ```

## Uso nas Rotas Protegidas
O token JWT gerado permite acesso a todas as rotas da API protegidas (ex: `/api/transactions`, `/api/withdrawals`, etc.).
