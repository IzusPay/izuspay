# Checklist de Módulos do Frontend

Status do desenvolvimento dos módulos do frontend para integração com a nova API NestJS.

## Autenticação e Perfil
- [x] Login (JWT)
- [x] Perfil do Usuário
- [x] Logout
- [x] Proteção de Rotas (AuthGuard)

## Módulos Principais

### Dashboard
- [x] Layout Principal (Sidebar, Header)
- [x] Cards de Resumo
- [x] Gráficos (Placeholder/Inicial)

### Vendas (Sales)
- [x] Listagem de Vendas
- [x] Detalhes da Venda
- [x] Criação de Venda (Checkout/Link)
- [x] Status e Filtros
- [x] Integração com PIX

### Financeiro (Financial)
- [x] Dashboard Financeiro
- [x] Saldo Disponível/Bloqueado
- [x] Histórico de Transações
- [x] Extrato

### Saques (Withdrawals)
- [x] Solicitação de Saque
- [x] Listagem de Saques
- [x] Status de Saque
- [x] Seleção de Conta Bancária/PIX

### Contas Bancárias (Bank Accounts)
- [x] Listagem de Contas
- [x] Cadastro de Nova Conta
- [x] Edição de Conta
- [x] Exclusão de Conta
- [x] Validação de Chave PIX

### Gateways de Pagamento
- [x] Listagem de Gateways
- [x] Configuração de Gateway
- [x] Ativação/Desativação
- [x] Edição de Credenciais

### Empresas (Companies)
- [x] Listagem de Empresas
- [x] Cadastro de Empresa
- [x] Edição de Empresa
- [x] Exclusão de Empresa

### Clientes (Customers)
- [x] Listagem de Clientes
- [x] Cadastro de Cliente
- [x] Edição de Cliente
- [x] Exclusão de Cliente
- [x] Histórico de Compras (via Vendas)

### Usuários e Permissões (ACL)
- [x] Listagem de Usuários
- [x] Cadastro de Usuário
- [x] Gerenciamento de Cargos (Roles)
- [x] Matriz de Permissões (Modules/Permissions)
- [x] Associação Usuário-Cargo

### Configurações do Sistema
- [x] Configurações Gerais (Nome, Email)
- [x] Identidade Visual (Logo, Favicon)
- [x] Banners da Home
- [x] Upload de Arquivos

## Infraestrutura
- [x] Configuração do Next.js
- [x] Configuração do Tailwind CSS
- [x] Cliente API (Axios/Fetch Wrapper) com Interceptors
- [x] Gerenciamento de Estado (Context API)
- [x] Componentes UI (Shadcn/UI)
