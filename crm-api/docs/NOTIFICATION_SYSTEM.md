# Sistema de Notificações - CRM

## Visão Geral

O Sistema de Notificações é uma solução multi-canal que permite enviar notificações personalizadas para usuários através de Email, WhatsApp, Push, SMS e In-App. O sistema suporta:

- **5 canais de notificação**: Email, WhatsApp, Push (FCM), SMS e In-App
- **Preferências por usuário**: Controle granular de canais e horários
- **Templates personalizados**: Templates reutilizáveis com substituição de variáveis
- **Agendamento**: Envio imediato ou programado
- **Retry automático**: Tentativas automáticas para notificações falhas
- **Prioridades**: low, normal, high, urgent
- **Notificações polimórficas**: Vincular a leads, tasks, proposals, etc

---

## Estrutura de Dados

### Tabelas

#### `notifications`
```sql
- id (PK)
- company_id (FK)
- user_id (FK)
- notifiable_type (morphs)
- notifiable_id (morphs)
- type (enum: 50 tipos)
- channel (email|whatsapp|push|sms|in_app)
- priority (low|normal|high|urgent)
- status (pending|sent|delivered|failed|read)
- title (string 255)
- body (text)
- data (json) - Dados adicionais
- scheduled_at (timestamp) - Agendamento
- sent_at, delivered_at, read_at, failed_at
- error_message (text)
- retry_count (int)
- timestamps
```

#### `notification_preferences`
```sql
- id (PK)
- user_id (FK)
- notification_type (enum)
- email_enabled (boolean)
- whatsapp_enabled (boolean)
- push_enabled (boolean)
- sms_enabled (boolean)
- in_app_enabled (boolean)
- schedule_config (json) - Horários de envio
- filters (json) - Filtros customizados
- timestamps
```

#### `notification_templates`
```sql
- id (PK)
- company_id (FK)
- name (string 255)
- slug (string unique)
- type (enum)
- channel (email|whatsapp|push|sms|in_app)
- subject (string 255) - Para email
- body (text) - Template com {{variables}}
- default_data (json)
- is_active (boolean)
- timestamps
```

---

## Quick Start

### 1. Criar Notificação Simples

```php
use App\Services\Notifications\NotificationService;

$notificationService = app(NotificationService::class);

// Criar e enviar imediatamente
$notification = $notificationService->create([
    'user_id' => 1,
    'notifiable_type' => 'App\Models\Lead',
    'notifiable_id' => 123,
    'type' => 'lead_created',
    'channels' => ['email', 'whatsapp'],
    'priority' => 'high',
    'title' => 'Novo lead atribuído',
    'body' => 'Você tem um novo lead: João Silva',
]);
```

### 2. Agendar Notificação

```php
$notification = $notificationService->create([
    'user_id' => 1,
    'type' => 'task_reminder',
    'channels' => ['push', 'in_app'],
    'title' => 'Lembrete de tarefa',
    'body' => 'Tarefa vence em 1 hora',
    'scheduled_at' => now()->addHours(1),
]);
```

### 3. Criar a partir de Template

```php
$notification = $notificationService->createFromTemplate(
    templateId: 5,
    userId: 1,
    channels: ['email'],
    data: [
        'user_name' => 'João Silva',
        'lead_name' => 'Cliente ABC',
        'lead_phone' => '(11) 98765-4321',
    ],
    scheduledAt: now()->addMinutes(30)
);
```

### 4. Envio em Massa

```php
$notificationService->sendBulk(
    users: [1, 2, 3],
    type: 'system_announcement',
    channels: ['email', 'in_app'],
    data: [
        'title' => 'Manutenção Programada',
        'body' => 'Sistema em manutenção dia 15/02 das 02:00 às 04:00',
        'priority' => 'urgent',
    ]
);
```

---

## API Endpoints

### Notificações

#### `GET /api/notifications`
Lista notificações do usuário autenticado.

**Query Parameters:**
- `status`: pending, sent, delivered, failed, read
- `channel`: email, whatsapp, push, sms, in_app
- `type`: lead_created, task_assigned, etc
- `priority`: low, normal, high, urgent
- `unread`: true/false
- `per_page`: 15 (padrão)

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "type": "lead_created",
      "channel": "email",
      "priority": "high",
      "status": "delivered",
      "title": "Novo lead atribuído",
      "body": "Você tem um novo lead: João Silva",
      "data": {
        "lead_id": 123,
        "lead_name": "João Silva"
      },
      "notifiable_type": "App\\Models\\Lead",
      "notifiable_id": 123,
      "is_read": false,
      "sent_at": "2026-01-29T10:30:00Z",
      "created_at": "2026-01-29T10:29:45Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 42
  }
}
```

#### `GET /api/notifications/{id}`
Mostra uma notificação específica e marca como lida automaticamente.

**Response:**
```json
{
  "id": 1,
  "type": "lead_created",
  "channel": "email",
  "priority": "high",
  "status": "delivered",
  "title": "Novo lead atribuído",
  "body": "Você tem um novo lead: João Silva",
  "data": { "lead_id": 123 },
  "is_read": true,
  "read_at": "2026-01-29T10:45:00Z",
  "created_at": "2026-01-29T10:29:45Z"
}
```

#### `POST /api/notifications`
Cria uma nova notificação (uso administrativo).

**Request Body:**
```json
{
  "user_id": 1,
  "notifiable_type": "App\\Models\\Lead",
  "notifiable_id": 123,
  "type": "lead_created",
  "channels": ["email", "whatsapp"],
  "priority": "high",
  "title": "Novo lead atribuído",
  "body": "Você tem um novo lead: {{lead_name}}",
  "data": {
    "lead_name": "João Silva",
    "lead_phone": "(11) 98765-4321"
  },
  "scheduled_at": "2026-01-29T15:00:00Z"
}
```

**Response:**
```json
{
  "message": "Notificação criada com sucesso",
  "notification": {
    "id": 1,
    "status": "pending",
    "scheduled_at": "2026-01-29T15:00:00Z"
  }
}
```

#### `POST /api/notifications/{id}/mark-as-read`
Marca uma notificação como lida.

**Response:**
```json
{
  "message": "Notificação marcada como lida",
  "notification": {
    "id": 1,
    "is_read": true,
    "read_at": "2026-01-29T11:00:00Z"
  }
}
```

#### `POST /api/notifications/mark-all-as-read`
Marca todas as notificações do usuário como lidas.

**Response:**
```json
{
  "message": "Todas as notificações foram marcadas como lidas",
  "count": 15
}
```

#### `GET /api/notifications/statistics`
Retorna estatísticas das notificações do usuário.

**Response:**
```json
{
  "total": 150,
  "unread": 12,
  "by_channel": {
    "email": 80,
    "whatsapp": 35,
    "push": 20,
    "in_app": 15
  },
  "by_priority": {
    "low": 50,
    "normal": 70,
    "high": 25,
    "urgent": 5
  },
  "recent_unread": [
    {
      "id": 150,
      "type": "task_reminder",
      "title": "Tarefa vence em 1 hora",
      "created_at": "2026-01-29T10:30:00Z"
    }
  ]
}
```

#### `POST /api/notifications/test`
Envia uma notificação de teste.

**Request Body:**
```json
{
  "channel": "email",
  "message": "Teste de notificação do sistema"
}
```

**Response:**
```json
{
  "message": "Notificação de teste enviada com sucesso",
  "channel": "email"
}
```

#### `DELETE /api/notifications/{id}`
Deleta uma notificação.

**Response:**
```json
{
  "message": "Notificação deletada com sucesso"
}
```

---

### Preferências de Notificação

#### `GET /api/notifications/preferences`
Lista todas as preferências do usuário.

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "notification_type": "lead_created",
      "email_enabled": true,
      "whatsapp_enabled": true,
      "push_enabled": false,
      "sms_enabled": false,
      "in_app_enabled": true,
      "schedule_config": {
        "working_hours": {
          "start": "09:00",
          "end": "18:00"
        },
        "timezone": "America/Sao_Paulo",
        "days_of_week": [1, 2, 3, 4, 5]
      }
    }
  ]
}
```

#### `GET /api/notifications/preferences/type/{type}`
Obtém preferência por tipo (cria com defaults se não existir).

**Response:**
```json
{
  "id": 1,
  "notification_type": "lead_created",
  "email_enabled": true,
  "whatsapp_enabled": true,
  "push_enabled": false,
  "sms_enabled": false,
  "in_app_enabled": true,
  "enabled_channels": ["email", "whatsapp", "in_app"]
}
```

#### `POST /api/notifications/preferences`
Cria ou atualiza preferências.

**Request Body:**
```json
{
  "notification_type": "lead_created",
  "email_enabled": true,
  "whatsapp_enabled": true,
  "push_enabled": false,
  "schedule_config": {
    "working_hours": {
      "start": "08:00",
      "end": "20:00"
    },
    "timezone": "America/Sao_Paulo",
    "days_of_week": [1, 2, 3, 4, 5, 6]
  }
}
```

**Response:**
```json
{
  "message": "Preferências salvas com sucesso",
  "preference": { "id": 1, ... }
}
```

#### `POST /api/notifications/preferences/{id}/enable/{channel}`
Ativa um canal específico.

**Response:**
```json
{
  "message": "Canal email ativado com sucesso",
  "preference": {
    "id": 1,
    "email_enabled": true
  }
}
```

#### `POST /api/notifications/preferences/{id}/disable/{channel}`
Desativa um canal específico.

**Response:**
```json
{
  "message": "Canal whatsapp desativado com sucesso",
  "preference": {
    "id": 1,
    "whatsapp_enabled": false
  }
}
```

#### `POST /api/notifications/preferences/reset-default`
Restaura preferências padrão.

**Response:**
```json
{
  "message": "Preferências restauradas para os padrões",
  "count": 5
}
```

---

### Templates de Notificação

#### `GET /api/notifications/templates`
Lista templates (filtrado por empresa do usuário).

**Query Parameters:**
- `type`: lead_created, task_assigned, etc
- `channel`: email, whatsapp, push, sms, in_app
- `is_active`: true/false
- `per_page`: 15 (padrão)

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Novo Lead - Email",
      "slug": "novo-lead-email",
      "type": "lead_created",
      "channel": "email",
      "subject": "Novo lead atribuído: {{lead_name}}",
      "body": "Olá {{user_name}},\n\nVocê tem um novo lead: {{lead_name}}\nTelefone: {{lead_phone}}\n\nAcesse o sistema para mais detalhes.",
      "is_active": true,
      "created_at": "2026-01-29T10:00:00Z"
    }
  ]
}
```

#### `GET /api/notifications/templates/{id}`
Mostra um template específico.

**Response:**
```json
{
  "id": 1,
  "name": "Novo Lead - Email",
  "slug": "novo-lead-email",
  "type": "lead_created",
  "channel": "email",
  "subject": "Novo lead atribuído: {{lead_name}}",
  "body": "Olá {{user_name}}...",
  "default_data": {
    "user_name": "Usuário"
  },
  "is_active": true
}
```

#### `POST /api/notifications/templates`
Cria um novo template.

**Request Body:**
```json
{
  "name": "Tarefa Vencendo - WhatsApp",
  "type": "task_reminder",
  "channel": "whatsapp",
  "body": "🔔 *Lembrete de Tarefa*\n\nOlá {{user_name}},\n\nSua tarefa *{{task_title}}* vence em {{hours_remaining}} horas.\n\nAcesse: {{task_link}}",
  "default_data": {
    "hours_remaining": "1"
  },
  "is_active": true
}
```

**Response:**
```json
{
  "message": "Template criado com sucesso",
  "template": {
    "id": 5,
    "slug": "tarefa-vencendo-whatsapp"
  }
}
```

#### `PUT /api/notifications/templates/{id}`
Atualiza um template existente.

**Request Body:**
```json
{
  "name": "Tarefa Vencendo - WhatsApp (Atualizado)",
  "body": "🔔 *Lembrete de Tarefa*\n\nOlá {{user_name}}...",
  "is_active": false
}
```

**Response:**
```json
{
  "message": "Template atualizado com sucesso",
  "template": { "id": 5, ... }
}
```

#### `POST /api/notifications/templates/{id}/preview`
Visualiza template renderizado com dados.

**Request Body:**
```json
{
  "data": {
    "user_name": "João Silva",
    "lead_name": "Cliente ABC",
    "lead_phone": "(11) 98765-4321"
  }
}
```

**Response:**
```json
{
  "subject": "Novo lead atribuído: Cliente ABC",
  "body": "Olá João Silva,\n\nVocê tem um novo lead: Cliente ABC\nTelefone: (11) 98765-4321\n\nAcesse o sistema para mais detalhes.",
  "required_variables": ["user_name", "lead_name", "lead_phone"],
  "missing_variables": [],
  "has_all_required": true
}
```

#### `GET /api/notifications/templates/{id}/variables`
Lista variáveis do template.

**Response:**
```json
{
  "required": ["user_name", "lead_name", "lead_phone"],
  "available": ["user_name", "user_email", "lead_name", "lead_phone", "lead_email", "company_name"],
  "template": {
    "subject": "Novo lead atribuído: {{lead_name}}",
    "body": "Olá {{user_name}}..."
  }
}
```

#### `DELETE /api/notifications/templates/{id}`
Deleta um template.

**Response:**
```json
{
  "message": "Template deletado com sucesso"
}
```

---

## Canais de Notificação

### Email

**Configuração:**
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=seu-email@gmail.com
MAIL_PASSWORD=sua-senha
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@seucrm.com
MAIL_FROM_NAME="CRM Makin"
```

**Características:**
- HTML formatado com CSS inline
- Indicadores visuais de prioridade
- Subject personalizável
- Suporte a templates

### WhatsApp

**Requisitos:**
- WhatsApp Business API configurada (FASE 4)
- Empresa com número WhatsApp ativo
- `companies.whatsapp_phone`, `whatsapp_api_url`, `whatsapp_api_token`

**Características:**
- Formatação Markdown (*negrito*)
- Sem subject (apenas body)
- Integração com WhatsAppService

**Exemplo:**
```php
// Notificação WhatsApp
$notification = $notificationService->create([
    'user_id' => 1,
    'type' => 'lead_created',
    'channels' => ['whatsapp'],
    'title' => 'Novo Lead',
    'body' => '*Novo Lead Atribuído*\n\nNome: João Silva\nTelefone: (11) 98765-4321',
]);
```

### Push Notifications (FCM)

**Configuração (Futuro):**
```env
FCM_SERVER_KEY=sua-chave-fcm
FCM_PROJECT_ID=seu-projeto-id
```

**Características:**
- Notificações push para mobile/web
- Requer tokens FCM dos dispositivos
- Stub implementado para futura integração

### SMS

**Configuração (Futuro):**
```env
SMS_PROVIDER=twilio
SMS_ACCOUNT_SID=seu-account-sid
SMS_AUTH_TOKEN=seu-auth-token
SMS_FROM_NUMBER=+5511999999999
```

**Características:**
- Mensagens de texto SMS
- Limite de 160 caracteres
- Stub implementado para futura integração

### In-App

**Características:**
- Notificações apenas no banco de dados
- Exibidas no frontend da aplicação
- Sem envio externo
- Ideal para avisos internos

---

## Sistema de Agendamento

### Configurar Scheduler

**app/Console/Kernel.php:**
```php
protected function schedule(Schedule $schedule): void
{
    // Processa notificações agendadas e retry de falhas
    $schedule->job(new ProcessScheduledNotificationsJob())
        ->everyMinute()
        ->withoutOverlapping();
    
    // Limpa notificações antigas (30 dias)
    $schedule->call(function () {
        app(NotificationService::class)->deleteOld(30);
    })->daily();
}
```

**Cron (Servidor):**
```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

### Agendamento Manual

```php
// Agendar para daqui 2 horas
$notification = $notificationService->create([
    'user_id' => 1,
    'type' => 'task_reminder',
    'channels' => ['push', 'in_app'],
    'title' => 'Lembrete de Tarefa',
    'body' => 'Tarefa vence em breve',
    'scheduled_at' => now()->addHours(2),
]);

// Processar notificações agendadas manualmente
$notificationService->processScheduled();
```

### Retry Automático

```php
// Retry de notificações falhas (máximo 3 tentativas)
$notificationService->retryFailed($maxRetries = 3);
```

**Job de Retry:**
- Tenta reenviar notificações com `status=failed`
- Verifica `retry_count < maxRetries`
- Incrementa `retry_count` a cada tentativa
- Para após 3 tentativas

---

## Preferências de Usuário

### Horário de Trabalho

```php
// Configurar horário de recebimento
$preference = NotificationPreference::updateOrCreate(
    [
        'user_id' => 1,
        'notification_type' => 'lead_created',
    ],
    [
        'schedule_config' => [
            'working_hours' => [
                'start' => '09:00',
                'end' => '18:00',
            ],
            'timezone' => 'America/Sao_Paulo',
            'days_of_week' => [1, 2, 3, 4, 5], // Segunda a Sexta
        ],
    ]
);

// Verificar se deve enviar agora
if ($preference->shouldSendNow()) {
    // Enviar notificação
}
```

**Comportamento:**
- Notificações fora do horário são agendadas para o próximo horário útil
- Respeita timezone do usuário
- Considera dias da semana (ex: não enviar aos finais de semana)

### Canais Habilitados

```php
// Habilitar apenas Email e In-App
$preference->update([
    'email_enabled' => true,
    'whatsapp_enabled' => false,
    'push_enabled' => false,
    'sms_enabled' => false,
    'in_app_enabled' => true,
]);

// Obter canais habilitados
$channels = $preference->getEnabledChannels();
// ['email', 'in_app']
```

---

## Sistema de Templates

### Variáveis Disponíveis

**Variáveis de Usuário:**
- `{{user_name}}` - Nome do usuário
- `{{user_email}}` - Email do usuário
- `{{user_phone}}` - Telefone do usuário

**Variáveis de Lead:**
- `{{lead_name}}` - Nome do lead
- `{{lead_email}}` - Email do lead
- `{{lead_phone}}` - Telefone do lead
- `{{lead_company}}` - Empresa do lead

**Variáveis de Tarefa:**
- `{{task_title}}` - Título da tarefa
- `{{task_due_date}}` - Data de vencimento
- `{{task_priority}}` - Prioridade
- `{{task_link}}` - Link para a tarefa

**Variáveis de Proposta:**
- `{{proposal_number}}` - Número da proposta
- `{{proposal_total}}` - Valor total
- `{{proposal_status}}` - Status
- `{{proposal_link}}` - Link para a proposta

**Variáveis de Sistema:**
- `{{company_name}}` - Nome da empresa
- `{{system_url}}` - URL do sistema
- `{{current_date}}` - Data atual
- `{{current_time}}` - Hora atual

### Criar Template

```php
$template = NotificationTemplate::create([
    'company_id' => 1,
    'name' => 'Novo Lead - Email',
    'type' => 'lead_created',
    'channel' => 'email',
    'subject' => 'Novo lead atribuído: {{lead_name}}',
    'body' => <<<EOT
Olá {{user_name}},

Você tem um novo lead atribuído:

Nome: {{lead_name}}
Email: {{lead_email}}
Telefone: {{lead_phone}}
Empresa: {{lead_company}}

Acesse o sistema para mais detalhes:
{{system_url}}/leads/{{lead_id}}

Atenciosamente,
{{company_name}}
EOT,
    'default_data' => [
        'system_url' => 'https://crm.seusite.com',
    ],
    'is_active' => true,
]);
```

### Renderizar Template

```php
$template = NotificationTemplate::find(1);

// Renderizar com dados
$data = [
    'user_name' => 'João Silva',
    'lead_name' => 'Cliente ABC',
    'lead_email' => 'cliente@abc.com',
    'lead_phone' => '(11) 98765-4321',
    'lead_company' => 'ABC Ltda',
    'lead_id' => 123,
    'company_name' => 'CRM Makin',
];

$subject = $template->renderSubject($data);
$body = $template->renderBody($data);

// Verificar variáveis obrigatórias
$required = $template->getRequiredVariables();
// ['user_name', 'lead_name', 'lead_email', ...]

$hasAll = $template->hasAllRequiredVariables($data);
// true
```

### Substituição com Dot Notation

```php
// Template com variáveis aninhadas
$template = NotificationTemplate::create([
    'body' => 'Olá {{user.name}}, lead {{lead.contact.name}} de {{lead.company.name}}',
]);

// Dados aninhados
$data = [
    'user' => [
        'name' => 'João Silva',
        'email' => 'joao@example.com',
    ],
    'lead' => [
        'contact' => ['name' => 'Cliente ABC'],
        'company' => ['name' => 'ABC Ltda'],
    ],
];

$body = $template->renderBody($data);
// "Olá João Silva, lead Cliente ABC de ABC Ltda"
```

---

## Tipos de Notificação

### Leads
- `lead_created` - Novo lead criado
- `lead_assigned` - Lead atribuído
- `lead_status_changed` - Status alterado
- `lead_converted` - Lead convertido
- `lead_lost` - Lead perdido

### Tarefas
- `task_assigned` - Tarefa atribuída
- `task_completed` - Tarefa concluída
- `task_overdue` - Tarefa atrasada
- `task_reminder` - Lembrete de tarefa
- `task_comment` - Comentário em tarefa

### Propostas
- `proposal_created` - Nova proposta
- `proposal_sent` - Proposta enviada
- `proposal_accepted` - Proposta aceita
- `proposal_rejected` - Proposta rejeitada
- `proposal_expired` - Proposta expirada

### Atividades
- `activity_created` - Nova atividade
- `activity_reminder` - Lembrete de atividade
- `activity_completed` - Atividade concluída

### Sistema
- `system_announcement` - Anúncio do sistema
- `system_maintenance` - Manutenção programada
- `user_mention` - Menção em comentário
- `report_ready` - Relatório pronto

---

## Exemplos Práticos

### 1. Notificar sobre Novo Lead

```php
use App\Services\Notifications\NotificationService;
use App\Models\Lead;
use App\Models\User;

$lead = Lead::find(123);
$user = User::find(1);
$notificationService = app(NotificationService::class);

$notificationService->create([
    'user_id' => $user->id,
    'notifiable_type' => Lead::class,
    'notifiable_id' => $lead->id,
    'type' => 'lead_created',
    'channels' => ['email', 'in_app'],
    'priority' => 'high',
    'title' => 'Novo lead atribuído',
    'body' => "Você tem um novo lead: {$lead->name}",
    'data' => [
        'lead_id' => $lead->id,
        'lead_name' => $lead->name,
        'lead_phone' => $lead->phone,
    ],
]);
```

### 2. Lembrete de Tarefa (Agendado)

```php
use App\Services\Notifications\NotificationService;
use App\Models\Task;

$task = Task::find(456);
$notificationService = app(NotificationService::class);

// Agendar lembrete para 1 hora antes do vencimento
$notificationService->create([
    'user_id' => $task->assigned_to_user_id,
    'notifiable_type' => Task::class,
    'notifiable_id' => $task->id,
    'type' => 'task_reminder',
    'channels' => ['push', 'in_app'],
    'priority' => 'normal',
    'title' => 'Lembrete de tarefa',
    'body' => "Tarefa '{$task->title}' vence em 1 hora",
    'scheduled_at' => $task->due_date->subHour(),
]);
```

### 3. Notificação em Massa

```php
use App\Services\Notifications\NotificationService;
use App\Models\User;

$notificationService = app(NotificationService::class);
$userIds = User::where('is_active', true)->pluck('id');

$notificationService->sendBulk(
    users: $userIds,
    type: 'system_announcement',
    channels: ['email', 'in_app'],
    data: [
        'title' => 'Nova funcionalidade disponível',
        'body' => 'O módulo de relatórios foi lançado! Acesse e confira.',
        'priority' => 'normal',
        'data' => [
            'feature' => 'reports',
            'link' => 'https://crm.example.com/reports',
        ],
    ]
);
```

### 4. Notificação com Template

```php
use App\Services\Notifications\NotificationService;
use App\Models\Proposal;

$proposal = Proposal::find(789);
$notificationService = app(NotificationService::class);

$notificationService->createFromTemplate(
    templateId: 5, // Template "Proposta Aceita"
    userId: $proposal->user_id,
    channels: ['email', 'whatsapp'],
    data: [
        'user_name' => $proposal->user->name,
        'proposal_number' => $proposal->number,
        'proposal_total' => number_format($proposal->total, 2, ',', '.'),
        'client_name' => $proposal->lead->name,
        'proposal_link' => route('proposals.show', $proposal),
    ]
);
```

### 5. Verificar Notificações Não Lidas

```php
use App\Models\Notification;

$unreadCount = Notification::query()
    ->where('user_id', auth()->id())
    ->unread()
    ->count();

$unreadNotifications = Notification::query()
    ->where('user_id', auth()->id())
    ->unread()
    ->orderBy('created_at', 'desc')
    ->limit(10)
    ->get();
```

### 6. Marcar como Lida

```php
use App\Services\Notifications\NotificationService;

$notificationService = app(NotificationService::class);

// Marcar uma específica
$notificationService->markAsRead([123]);

// Marcar múltiplas
$notificationService->markAsRead([123, 124, 125]);

// Marcar todas do usuário
auth()->user()->notifications()->unread()->update([
    'status' => 'read',
    'read_at' => now(),
]);
```

### 7. Configurar Preferências

```php
use App\Models\NotificationPreference;

// Desativar WhatsApp para notificações de lead
NotificationPreference::updateOrCreate(
    [
        'user_id' => auth()->id(),
        'notification_type' => 'lead_created',
    ],
    [
        'whatsapp_enabled' => false,
        'email_enabled' => true,
        'in_app_enabled' => true,
    ]
);

// Configurar horário de trabalho
NotificationPreference::updateOrCreate(
    [
        'user_id' => auth()->id(),
        'notification_type' => 'task_reminder',
    ],
    [
        'schedule_config' => [
            'working_hours' => [
                'start' => '08:00',
                'end' => '20:00',
            ],
            'timezone' => 'America/Sao_Paulo',
            'days_of_week' => [1, 2, 3, 4, 5, 6], // Seg-Sáb
        ],
    ]
);
```

---

## Jobs e Filas

### SendNotificationJob

Processa o envio de uma notificação de forma assíncrona.

**Uso:**
```php
use App\Jobs\SendNotificationJob;
use App\Models\Notification;

$notification = Notification::find(1);
SendNotificationJob::dispatch($notification);
```

**Configuração:**
- 3 tentativas com 10s de backoff exponencial
- Verifica se já foi enviada antes de processar
- Marca como falha automaticamente após 3 tentativas
- Log detalhado de erros

### ProcessScheduledNotificationsJob

Processa notificações agendadas e retry de falhas.

**Uso no Scheduler:**
```php
$schedule->job(new ProcessScheduledNotificationsJob())
    ->everyMinute()
    ->withoutOverlapping();
```

**Ou manualmente:**
```php
use App\Jobs\ProcessScheduledNotificationsJob;

ProcessScheduledNotificationsJob::dispatch();
```

### Configurar Filas

**config/queue.php:**
```php
'connections' => [
    'redis' => [
        'driver' => 'redis',
        'connection' => 'default',
        'queue' => env('REDIS_QUEUE', 'default'),
        'retry_after' => 90,
        'block_for' => null,
    ],
],
```

**Iniciar Worker:**
```bash
php artisan queue:work redis --queue=default --tries=3
```

---

## Testes

### Testar Canal Específico

```bash
curl -X POST http://localhost:8000/api/notifications/test \
  -H "Authorization: Bearer seu-token" \
  -H "Content-Type: application/json" \
  -d '{
    "channel": "email",
    "message": "Teste de notificação por email"
  }'
```

### Testar Template

```bash
curl -X POST http://localhost:8000/api/notifications/templates/1/preview \
  -H "Authorization: Bearer seu-token" \
  -H "Content-Type: application/json" \
  -d '{
    "data": {
      "user_name": "João Silva",
      "lead_name": "Cliente ABC",
      "lead_phone": "(11) 98765-4321"
    }
  }'
```

### Testar Agendamento

```php
use App\Services\Notifications\NotificationService;

$notificationService = app(NotificationService::class);

// Criar notificação agendada para daqui 5 minutos
$notification = $notificationService->create([
    'user_id' => 1,
    'type' => 'test',
    'channels' => ['email'],
    'title' => 'Teste de agendamento',
    'body' => 'Esta é uma notificação agendada',
    'scheduled_at' => now()->addMinutes(5),
]);

// Aguardar 5 minutos e processar
sleep(300);
$notificationService->processScheduled();
```

---

## Best Practices

### 1. Preferências de Usuário
- Sempre verificar preferências antes de enviar
- Respeitar horários de trabalho configurados
- Permitir que usuários desabilitem canais específicos

### 2. Templates
- Usar templates para mensagens recorrentes
- Incluir variáveis default para evitar erros
- Validar variáveis antes de renderizar
- Manter templates atualizados e ativos

### 3. Agendamento
- Usar scheduler para processar notificações agendadas
- Configurar retry automático para falhas
- Limpar notificações antigas periodicamente

### 4. Performance
- Usar filas (Redis) para envios assíncronos
- Processar notificações em massa com `sendBulk()`
- Evitar N+1 queries ao buscar notificações

### 5. Segurança
- Validar permissões antes de criar/visualizar notificações
- Filtrar notificações por empresa (`company_id`)
- Não expor dados sensíveis em notificações públicas

### 6. Monitoramento
- Acompanhar taxa de falhas por canal
- Verificar logs de erros regularmente
- Monitorar fila de notificações pendentes

---

## Troubleshooting

### Notificações não estão sendo enviadas

**Verificar:**
1. Job worker está rodando? `php artisan queue:work`
2. Notificação está agendada? Verificar `scheduled_at`
3. Usuário tem preferências habilitadas? Checar `notification_preferences`
4. Notificação já foi enviada? Status não deve ser `sent`

**Solução:**
```bash
# Ver jobs na fila
php artisan queue:monitor

# Processar manualmente
php artisan queue:work --once

# Ver notificações pendentes
php artisan tinker
>>> Notification::pending()->count()
```

### Email não está sendo recebido

**Verificar:**
1. Configuração SMTP em `.env`
2. Logs do Laravel: `storage/logs/laravel.log`
3. Verificar spam/lixo eletrônico
4. Email do usuário está correto?

**Solução:**
```bash
# Testar email manualmente
php artisan tinker
>>> Mail::raw('Teste', fn($msg) => $msg->to('seu-email@example.com')->subject('Teste'))
```

### WhatsApp não está funcionando

**Verificar:**
1. WhatsApp Business API configurada (FASE 4)
2. Empresa tem `whatsapp_phone`, `whatsapp_api_url`, `whatsapp_api_token`
3. Número do destinatário no formato internacional

**Solução:**
```bash
# Testar WhatsApp manualmente
php artisan tinker
>>> app(WhatsAppService::class)->sendMessage('+5511987654321', 'Teste', 1)
```

### Notificações falhando constantemente

**Verificar:**
1. Erro específico em `notifications.error_message`
2. `retry_count` está abaixo de 3?
3. Canal está funcionando?

**Solução:**
```bash
# Ver notificações falhas
php artisan tinker
>>> Notification::failed()->latest()->take(10)->get(['id', 'type', 'channel', 'error_message'])

# Retry manual
>>> app(NotificationService::class)->retryFailed()
```

### Templates não renderizando variáveis

**Verificar:**
1. Variáveis no formato `{{variable}}`
2. Dados contém todas as variáveis obrigatórias
3. Usar `hasAllRequiredVariables()` antes de renderizar

**Solução:**
```php
$template = NotificationTemplate::find(1);
$required = $template->getRequiredVariables();
// Verificar se $data contém todas as variáveis em $required
```

---

## Códigos de Erro

### Notificação
- `NOTIFICATION_NOT_FOUND` - Notificação não encontrada
- `NOTIFICATION_ALREADY_SENT` - Notificação já foi enviada
- `NOTIFICATION_SEND_FAILED` - Falha ao enviar notificação
- `INVALID_CHANNEL` - Canal inválido
- `INVALID_PRIORITY` - Prioridade inválida
- `MAX_RETRIES_EXCEEDED` - Máximo de tentativas excedido

### Preferência
- `PREFERENCE_NOT_FOUND` - Preferência não encontrada
- `INVALID_NOTIFICATION_TYPE` - Tipo de notificação inválido
- `INVALID_SCHEDULE_CONFIG` - Configuração de horário inválida

### Template
- `TEMPLATE_NOT_FOUND` - Template não encontrado
- `TEMPLATE_MISSING_VARIABLES` - Variáveis obrigatórias faltando
- `TEMPLATE_RENDER_ERROR` - Erro ao renderizar template
- `DUPLICATE_TEMPLATE_SLUG` - Slug do template já existe

---

## Próximos Passos

### Integrações Futuras
1. **Push Notifications (FCM)**: Implementar integração completa com Firebase Cloud Messaging
2. **SMS (Twilio/Nexmo)**: Implementar envio de SMS
3. **Slack**: Adicionar canal Slack para notificações em equipe
4. **Telegram**: Adicionar canal Telegram

### Melhorias
1. **Notificações em Tempo Real**: WebSockets para notificações instantâneas
2. **Analytics**: Dashboard de estatísticas de notificações
3. **A/B Testing**: Testar diferentes templates e horários
4. **Inteligência Artificial**: Otimizar horários de envio baseado em padrões

---

## Referências

- **Migrations**: `database/migrations/2026_01_29_*_create_notifications_*.php`
- **Models**: `app/Models/Notification.php`, `NotificationPreference.php`, `NotificationTemplate.php`
- **Services**: `app/Services/Notifications/*`
- **Controllers**: `app/Http/Controllers/Notification*.php`
- **Jobs**: `app/Jobs/SendNotificationJob.php`, `ProcessScheduledNotificationsJob.php`
- **Routes**: `routes/api.php` (linha ~210-251)

---

**Documentação criada em:** 29/01/2026  
**Versão:** 1.0.0  
**FASE 6:** Sistema de Notificações Completo
