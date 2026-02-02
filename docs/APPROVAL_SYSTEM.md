# Sistema de Aprovação Completo - CMS

## 📋 Visão Geral

O sistema de aprovação permite que conteúdos passem por um fluxo de revisão antes da publicação, com notificações automáticas e registro de atividades.

## 🔄 Fluxo de Aprovação

```
Draft → Request Approval → Pending → Approved/Rejected → Published/Draft
```

### Estados do Conteúdo
- **Draft** - Rascunho, em edição
- **Pending** - Aguardando aprovação
- **Published** - Publicado e disponível

### Estados da Aprovação
- **pending** - Aguardando revisão
- **approved** - Aprovada
- **rejected** - Rejeitada

## 📡 Events (Eventos)

### 1. ContentPublished
Disparado quando um conteúdo é publicado.

**Payload**:
- `content` - Model do conteúdo
- `contentType` - Tipo do conteúdo (Page, Post, Portfolio, etc.)

**Ações Automáticas**:
- Notificação ao criador do conteúdo
- Log de atividade no sistema

---

### 2. ApprovalRequested
Disparado quando uma solicitação de aprovação é criada.

**Payload**:
- `content` - Model do conteúdo
- `contentType` - Tipo do conteúdo
- `approval` - Model da aprovação criada

**Ações Automáticas**:
- Notificação aos managers com permissão `approve_content`
- Log de atividade no sistema

---

### 3. ApprovalApproved
Disparado quando uma solicitação é aprovada.

**Payload**:
- `content` - Model do conteúdo
- `contentType` - Tipo do conteúdo
- `approval` - Model da aprovação

**Ações Automáticas**:
- Notificação ao solicitante
- Log de atividade no sistema
- Auto-publicação do conteúdo

---

### 4. ApprovalRejected
Disparado quando uma solicitação é rejeitada.

**Payload**:
- `content` - Model do conteúdo
- `contentType` - Tipo do conteúdo
- `approval` - Model da aprovação

**Ações Automáticas**:
- Notificação ao solicitante
- Log de atividade no sistema
- Conteúdo volta para status Draft

## 📧 Notifications (Notificações)

### 1. ApprovalRequestedNotification
Enviada aos managers quando uma solicitação é criada.

**Canais**: Email + Database

**Dados**:
- Tipo de conteúdo
- Título do conteúdo
- Nome do solicitante
- Comentário da solicitação
- Link para revisão

---

### 2. ContentPublishedNotification
Enviada ao criador quando conteúdo é publicado.

**Canais**: Email + Database

**Dados**:
- Tipo de conteúdo
- Título do conteúdo
- Data de publicação
- Link para visualização

---

### 3. ApprovalDecisionNotification
Enviada ao solicitante quando aprovação é decidida.

**Canais**: Email + Database

**Dados**:
- Decisão (aprovado/rejeitado)
- Tipo de conteúdo
- Título do conteúdo
- Nome do revisor
- Data da decisão
- Comentário do revisor
- Link para visualização

## 🎯 API Endpoints

### Listar Solicitações de Aprovação
```http
GET /api/cms/approvals
Authorization: Bearer {token}
```

**Query Parameters**:
- `status` - Filtrar por status (pending, approved, rejected)
- `approvable_type` - Filtrar por tipo de conteúdo
- `page` - Número da página (paginação de 20)

**Response**:
```json
{
  "data": [
    {
      "id": 1,
      "approvable_type": "App\\Models\\CMS\\Portfolio",
      "approvable_id": 5,
      "requested_by": 2,
      "reviewed_by": null,
      "status": "pending",
      "message": "Por favor, revise este novo portfólio",
      "review_comment": null,
      "reviewed_at": null,
      "created_at": "2026-01-28T10:30:00.000000Z",
      "requester": {
        "id": 2,
        "name": "João Silva",
        "email": "joao@example.com"
      },
      "content": {
        "id": 5,
        "title": "Projeto CRM Avançado",
        "status": "pending"
      }
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total": 5
  }
}
```

---

### Ver Solicitação Específica
```http
GET /api/cms/approvals/{id}
Authorization: Bearer {token}
```

---

### Estatísticas de Aprovações
```http
GET /api/cms/approvals/statistics
Authorization: Bearer {token}
```

**Response**:
```json
{
  "pending": 3,
  "approved": 45,
  "rejected": 7,
  "total": 55,
  "by_type": {
    "Portfolio": 10,
    "Page": 20,
    "Post": 15,
    "Testimonial": 10
  }
}
```

---

### Aprovar Solicitação
```http
POST /api/cms/approvals/{id}/approve
Authorization: Bearer {token}
```

**Ações**:
1. Atualiza status da aprovação para "approved"
2. Registra revisor e data de revisão
3. Publica o conteúdo automaticamente
4. Dispara evento `ApprovalApproved`
5. Notifica o solicitante
6. Registra atividade no sistema

**Response**:
```json
{
  "message": "Conteúdo aprovado e publicado com sucesso.",
  "approval": {
    "id": 1,
    "status": "approved",
    "reviewed_by": 1,
    "reviewed_at": "2026-01-28T11:00:00.000000Z"
  }
}
```

---

### Rejeitar Solicitação
```http
POST /api/cms/approvals/{id}/reject
Authorization: Bearer {token}
Content-Type: application/json

{
  "reason": "O conteúdo precisa de mais detalhes e imagens de melhor qualidade."
}
```

**Validação**:
- `reason` - obrigatório, string, máx 1000 caracteres

**Ações**:
1. Atualiza status da aprovação para "rejected"
2. Registra revisor, motivo e data
3. Retorna conteúdo para status Draft
4. Dispara evento `ApprovalRejected`
5. Notifica o solicitante com o motivo
6. Registra atividade no sistema

**Response**:
```json
{
  "message": "Solicitação rejeitada com sucesso.",
  "approval": {
    "id": 1,
    "status": "rejected",
    "reviewed_by": 1,
    "review_comment": "O conteúdo precisa de mais detalhes...",
    "reviewed_at": "2026-01-28T11:00:00.000000Z"
  }
}
```

## 🔐 Permissões

### approve_content
Permissão necessária para aprovar/rejeitar conteúdos.

Managers com esta permissão recebem notificações de novas solicitações.

### Como Configurar
```php
// Criar permissão
$permission = Permission::create([
    'name' => 'approve_content',
    'description' => 'Aprovar e rejeitar conteúdos do CMS'
]);

// Atribuir a uma role
$managerRole = Role::where('name', 'manager')->first();
$managerRole->permissions()->attach($permission->id);
```

## 📊 Log de Atividades

Todas as ações são registradas na tabela `activities`:

### Tipos de Atividade
- `cms_publish` - Conteúdo publicado
- `cms_approval_request` - Solicitação criada
- `cms_approval_approved` - Solicitação aprovada
- `cms_approval_rejected` - Solicitação rejeitada

### Estrutura do Log
```php
[
    'company_id' => 1,
    'user_id' => 2,
    'activity_type' => 'cms_approval_request',
    'subject_type' => 'App\\Models\\CMS\\Portfolio',
    'subject_id' => 5,
    'description' => 'Solicitação de aprovação criada: Portfolio #5',
    'properties' => [
        'content_type' => 'Portfolio',
        'content_id' => 5,
        'approval_id' => 10,
        'title' => 'Projeto CRM',
        'message' => 'Por favor revise'
    ]
]
```

## 🔧 Services Atualizados

### PublishingService

#### publish()
Agora dispara:
- Evento `ContentPublished`
- Notificação `ContentPublishedNotification` ao criador

#### requestApproval()
Agora dispara:
- Evento `ApprovalRequested`
- Notificação `ApprovalRequestedNotification` aos managers

#### approve()
Agora dispara:
- Evento `ApprovalApproved`
- Notificação `ApprovalDecisionNotification` ao solicitante

#### reject()
Agora dispara:
- Evento `ApprovalRejected`
- Notificação `ApprovalDecisionNotification` ao solicitante

## 🎭 Listeners

### LogContentActivity
Subscriber que escuta todos os eventos CMS e registra atividades.

Registrado em `AppServiceProvider::boot()`:
```php
Event::subscribe(LogContentActivity::class);
```

## 📝 Exemplo de Uso Completo

### 1. Criar Conteúdo
```http
POST /api/cms/portfolios
{
  "site_id": 1,
  "title": "Novo Projeto",
  "status": "draft"
}
```

### 2. Solicitar Aprovação
```http
POST /api/cms/portfolios/5/request-approval
{
  "message": "Pronto para revisão!"
}
```

**Resultado**:
- Status do portfolio → `pending`
- Aprovação criada com status `pending`
- Managers recebem email/notificação
- Atividade registrada

### 3. Manager Revisa e Aprova
```http
POST /api/cms/approvals/10/approve
```

**Resultado**:
- Aprovação → status `approved`
- Portfolio → status `published`, `published_at` definido
- Solicitante recebe email/notificação
- Atividade registrada

### 4. Verificar Notificações
```http
GET /api/notifications
```

```json
{
  "data": [
    {
      "id": "uuid",
      "type": "App\\Notifications\\CMS\\ApprovalDecisionNotification",
      "data": {
        "content_type": "Portfolio",
        "approved": true,
        "reviewed_by": 1
      },
      "read_at": null,
      "created_at": "2026-01-28T11:00:00Z"
    }
  ]
}
```

## ⚙️ Configuração de Email

Certifique-se de configurar o `.env` para envio de emails:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@crm.com
MAIL_FROM_NAME="CRM Makin"
```

## 🧪 Testes

### Testar Notificações
```bash
php artisan tinker
```

```php
use App\Models\CMS\Portfolio;
use App\Services\CMS\PublishingService;

$portfolio = Portfolio::find(1);
$service = app(PublishingService::class);

// Solicitar aprovação
$approval = $service->requestApproval($portfolio, auth()->id(), 'Teste');

// Aprovar
$service->approve($approval, auth()->id());
```

### Verificar Fila de Jobs
```bash
php artisan queue:work
```

## 📈 Melhorias Futuras

- [ ] Aprovação multi-nível (múltiplos aprovadores)
- [ ] Aprovação em lote
- [ ] Webhook para aprovações
- [ ] Dashboard de aprovações pendentes
- [ ] Estatísticas por período
- [ ] Exportação de relatórios

---

**Status**: ✅ Sistema de Aprovação Completo Implementado
**Arquivos Criados**: 8 (4 events, 3 notifications, 1 listener, 1 controller)
**Rotas Adicionadas**: 5 endpoints de aprovação
