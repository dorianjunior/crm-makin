# API REST - Endpoints por Módulo

## 🔐 Autenticação

**Base URL:** `/api/auth`

| Método | Endpoint | Descrição | Autenticação |
|--------|----------|-----------|--------------|
| POST | `/register` | Registrar novo usuário | Não |
| POST | `/login` | Login de usuário | Não |
| POST | `/logout` | Logout de usuário | Sim |
| GET | `/user` | Obter usuário autenticado | Sim |

---

## 🏢 Companies (Empresas)

**Base URL:** `/api/companies`

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/` | Listar empresas (paginado) |
| POST | `/` | Criar nova empresa |
| GET | `/{id}` | Obter empresa específica |
| PUT/PATCH | `/{id}` | Atualizar empresa |
| DELETE | `/{id}` | Deletar empresa |

**Parâmetros de consulta:**
- Nenhum específico

---

## 👥 Roles & Permissions (Funções e Permissões)

### Roles
**Base URL:** `/api/roles`

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/` | Listar funções |
| POST | `/` | Criar nova função |
| GET | `/{id}` | Obter função específica |
| PUT/PATCH | `/{id}` | Atualizar função |
| DELETE | `/{id}` | Deletar função |

### Permissions
**Base URL:** `/api/permissions`

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/` | Listar permissões |
| POST | `/` | Criar nova permissão |
| GET | `/{id}` | Obter permissão específica |
| PUT/PATCH | `/{id}` | Atualizar permissão |
| DELETE | `/{id}` | Deletar permissão |

---

## 👤 Users (Usuários)

**Base URL:** `/api/users`

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/` | Listar usuários (paginado) |
| POST | `/` | Criar novo usuário |
| GET | `/{id}` | Obter usuário específico |
| PUT/PATCH | `/{id}` | Atualizar usuário |
| DELETE | `/{id}` | Deletar usuário |

**Parâmetros de consulta:**
- `company_id` - Filtrar por empresa

---

## 📊 Lead Management (Gestão de Leads)

### Lead Sources
**Base URL:** `/api/lead-sources`

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/` | Listar fontes de leads |
| POST | `/` | Criar nova fonte |
| GET | `/{id}` | Obter fonte específica |
| PUT/PATCH | `/{id}` | Atualizar fonte |
| DELETE | `/{id}` | Deletar fonte |

**Parâmetros de consulta:**
- `company_id` - Filtrar por empresa

### Leads
**Base URL:** `/api/leads`

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/` | Listar leads (paginado) |
| POST | `/` | Criar novo lead |
| GET | `/{id}` | Obter lead específico |
| PUT/PATCH | `/{id}` | Atualizar lead |
| DELETE | `/{id}` | Deletar lead |

**Parâmetros de consulta:**
- `company_id` - Filtrar por empresa
- `status` - Filtrar por status
- `assigned_to` - Filtrar por usuário responsável

**Status disponíveis:** `new`, `contacted`, `qualified`, `proposal`, `negotiation`, `won`, `lost`

### Activities
**Base URL:** `/api/activities`

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/` | Listar atividades (paginado) |
| POST | `/` | Criar nova atividade |
| GET | `/{id}` | Obter atividade específica |
| PUT/PATCH | `/{id}` | Atualizar atividade |
| DELETE | `/{id}` | Deletar atividade |

**Parâmetros de consulta:**
- `lead_id` - Filtrar por lead
- `user_id` - Filtrar por usuário

**Tipos disponíveis:** `call`, `email`, `meeting`, `note`, `task`, `whatsapp`

### Tasks
**Base URL:** `/api/tasks`

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/` | Listar tarefas (paginado) |
| POST | `/` | Criar nova tarefa |
| GET | `/{id}` | Obter tarefa específica |
| PUT/PATCH | `/{id}` | Atualizar tarefa |
| DELETE | `/{id}` | Deletar tarefa |

**Parâmetros de consulta:**
- `company_id` - Filtrar por empresa
- `user_id` - Filtrar por usuário
- `status` - Filtrar por status

**Status disponíveis:** `pending`, `in_progress`, `completed`, `cancelled`

---

## 🔄 Pipeline Management (Gestão de Funil)

### Pipelines
**Base URL:** `/api/pipelines`

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/` | Listar pipelines |
| POST | `/` | Criar novo pipeline |
| GET | `/{id}` | Obter pipeline específico |
| PUT/PATCH | `/{id}` | Atualizar pipeline |
| DELETE | `/{id}` | Deletar pipeline |

**Parâmetros de consulta:**
- `company_id` - Filtrar por empresa

### Pipeline Stages
**Base URL:** `/api/pipeline-stages`

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/` | Listar estágios |
| POST | `/` | Criar novo estágio |
| GET | `/{id}` | Obter estágio específico |
| PUT/PATCH | `/{id}` | Atualizar estágio |
| DELETE | `/{id}` | Deletar estágio |
| POST | `/{id}/leads` | Anexar lead ao estágio |
| DELETE | `/{id}/leads/{leadId}` | Remover lead do estágio |

**Parâmetros de consulta:**
- `pipeline_id` - Filtrar por pipeline

---

## 💰 Products & Proposals (Produtos e Propostas)

### Products
**Base URL:** `/api/products`

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/` | Listar produtos (paginado) |
| POST | `/` | Criar novo produto |
| GET | `/{id}` | Obter produto específico |
| PUT/PATCH | `/{id}` | Atualizar produto |
| DELETE | `/{id}` | Deletar produto |

**Parâmetros de consulta:**
- `company_id` - Filtrar por empresa
- `active` - Filtrar por status ativo

### Proposals
**Base URL:** `/api/proposals`

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/` | Listar propostas (paginado) |
| POST | `/` | Criar nova proposta |
| GET | `/{id}` | Obter proposta específica |
| PUT/PATCH | `/{id}` | Atualizar proposta |
| DELETE | `/{id}` | Deletar proposta |

**Parâmetros de consulta:**
- `lead_id` - Filtrar por lead
- `status` - Filtrar por status

**Status disponíveis:** `draft`, `sent`, `accepted`, `rejected`

---

## 💬 Communication (Comunicação)

### Emails
**Base URL:** `/api/emails`

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/` | Listar emails (paginado) |
| POST | `/` | Criar novo email |
| GET | `/{id}` | Obter email específico |
| PUT/PATCH | `/{id}` | Atualizar email |
| DELETE | `/{id}` | Deletar email |

**Parâmetros de consulta:**
- `lead_id` - Filtrar por lead

### WhatsApp Messages
**Base URL:** `/api/whatsapp-messages`

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/` | Listar mensagens (paginado) |
| POST | `/` | Criar nova mensagem |
| GET | `/{id}` | Obter mensagem específica |
| PUT/PATCH | `/{id}` | Atualizar mensagem |
| DELETE | `/{id}` | Deletar mensagem |

**Parâmetros de consulta:**
- `lead_id` - Filtrar por lead
- `status` - Filtrar por status

**Status disponíveis:** `queued`, `sent`, `delivered`, `failed`

### Message Templates
**Base URL:** `/api/message-templates`

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/` | Listar templates |
| POST | `/` | Criar novo template |
| GET | `/{id}` | Obter template específico |
| PUT/PATCH | `/{id}` | Atualizar template |
| DELETE | `/{id}` | Deletar template |

**Parâmetros de consulta:**
- `company_id` - Filtrar por empresa
- `type` - Filtrar por tipo

**Tipos disponíveis:** `email`, `whatsapp`, `sms`

---

## 📁 Files (Arquivos)

**Base URL:** `/api/files`

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/` | Listar arquivos (paginado) |
| POST | `/` | Upload de arquivo |
| GET | `/{id}` | Obter informações do arquivo |
| GET | `/{id}/download` | Download do arquivo |
| DELETE | `/{id}` | Deletar arquivo |

**Parâmetros de consulta:**
- `company_id` - Filtrar por empresa
- `lead_id` - Filtrar por lead

**Limite de upload:** 10MB

---

## 📝 System Logs (Logs do Sistema)

**Base URL:** `/api/system-logs`

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/` | Listar logs (paginado, 50 por página) |
| GET | `/{id}` | Obter log específico |

**Parâmetros de consulta:**
- `user_id` - Filtrar por usuário
- `action` - Filtrar por ação
- `entity` - Filtrar por entidade

---

## 📌 Convenções da API

### Autenticação
Todas as rotas protegidas requerem:
```
Authorization: Bearer {token}
```

### Respostas de Sucesso
- **200 OK** - Operação bem-sucedida
- **201 Created** - Recurso criado com sucesso
- **204 No Content** - Operação bem-sucedida sem conteúdo de retorno

### Respostas de Erro
- **400 Bad Request** - Requisição inválida
- **401 Unauthorized** - Não autenticado
- **403 Forbidden** - Sem permissão
- **404 Not Found** - Recurso não encontrado
- **422 Unprocessable Entity** - Erro de validação
- **500 Internal Server Error** - Erro no servidor

### Paginação
Recursos paginados retornam:
```json
{
  "data": [...],
  "links": {...},
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 100
  }
}
```

### Eager Loading
Use relacionamentos nos endpoints específicos para obter dados relacionados automaticamente.
