# Documentação da API - Control Toplist

## Estrutura do Sistema

O sistema agora está configurado para funcionar como:

### 1. Painel Administrativo (Filament)
- **URL**: `http://localhost/admin`
- **Autenticação**: Login tradicional via web
- **Recursos**: CRUD completo para usuários
- **API Automática**: Plugin ApiService gera endpoints RESTful automaticamente

### 2. API RESTful
- **Base URL**: `http://localhost/api`
- **Autenticação**: Laravel Sanctum (Bearer Token)

## Endpoints da API

### Status da API
```
GET /api/status
```
Retorna o status da API (público, sem autenticação)

### Autenticação

#### Login
```
POST /api/auth/login
Content-Type: application/json

{
    "email": "user@example.com",
    "password": "password"
}
```

**Resposta:**
```json
{
    "message": "Login realizado com sucesso",
    "user": {...},
    "token": "token_aqui",
    "token_type": "Bearer"
}
```

#### Logout
```
POST /api/auth/logout
Authorization: Bearer {token}
```

#### Informações do usuário
```
GET /api/auth/me
Authorization: Bearer {token}
```

#### Revogar todos os tokens
```
POST /api/auth/revoke-all
Authorization: Bearer {token}
```

### Rotas Protegidas
```
GET /api/user
Authorization: Bearer {token}
```

### Rotas Personalizadas
- **Protegidas**: `/api/v1/*` (requer autenticação)
- **Públicas**: `/api/v1/public/*` (sem autenticação)

## Como usar

### 1. Para o Painel Admin:
1. Acesse `http://localhost/admin`
2. Faça login com suas credenciais
3. Gerencie usuários e outros recursos

### 2. Para a API:
1. Faça login via POST `/api/auth/login`
2. Use o token retornado no header Authorization
3. Acesse os endpoints protegidos

## Configurações

### CORS
- Configurado para aceitar requisições de qualquer origem
- Headers e métodos liberados para desenvolvimento

### Sanctum
- Tokens sem expiração automática
- Middleware configurado para API

## Próximos Passos

1. Criar modelos e resources específicos do seu projeto
2. Adicionar endpoints personalizados em `/api/v1/`
3. Configurar permissions e roles se necessário
4. Ajustar configurações de CORS para produção