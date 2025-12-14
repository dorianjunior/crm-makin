# Autenticação com Sanctum - CRM API

## Configuração

A autenticação foi implementada usando Laravel Sanctum com os seguintes recursos:

- Login/Registro de usuários
- Tokens de API
- Middleware de verificação de usuário ativo
- Proteção de rotas

## Endpoints de Autenticação

### 1. Registro de Usuário
```http
POST /api/register
Content-Type: application/json

{
  "company_id": 1,
  "role_id": 1,
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Resposta:**
```json
{
  "user": {
    "id": 1,
    "company_id": 1,
    "role_id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "active": true,
    "company": {...},
    "role": {...}
  },
  "access_token": "1|xxxxxxxxxxxxxxxxxxxx",
  "token_type": "Bearer"
}
```

### 2. Login
```http
POST /api/login
Content-Type: application/json

{
  "email": "john@example.com",
  "password": "password123"
}
```

**Resposta:**
```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "company": {...},
    "role": {...}
  },
  "access_token": "2|xxxxxxxxxxxxxxxxxxxx",
  "token_type": "Bearer"
}
```

### 3. Logout
```http
POST /api/logout
Authorization: Bearer {token}
```

**Resposta:**
```json
{
  "message": "Logged out successfully"
}
```

### 4. Obter Usuário Autenticado
```http
GET /api/user
Authorization: Bearer {token}
```

**Resposta:**
```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "company": {...},
    "role": {...}
  }
}
```

## Como Usar

### Em requisições HTTP
Adicione o token no header:
```
Authorization: Bearer {seu_token_aqui}
```

### Testando com cURL
```bash
# Login
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"john@example.com","password":"password123"}'

# Requisição autenticada
curl http://localhost:8000/api/user \
  -H "Authorization: Bearer SEU_TOKEN_AQUI"
```

## Middlewares

- `auth:sanctum` - Valida o token de autenticação
- `active` - Verifica se o usuário está ativo

## Recursos de Segurança

- ✅ Senhas hasheadas automaticamente
- ✅ Validação de credenciais
- ✅ Revogação de tokens anteriores no login
- ✅ Verificação de conta ativa
- ✅ Relacionamentos carregados (company, role)
