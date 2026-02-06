# TODO - Módulo de Atividades (Activities)

## ✅ Implementado (Fase 1 - Index)

### Backend
- [x] Migration para adicionar campos `notes`, `duration`, `company_id`
- [x] Model Activity com scopes (ofType, forLead, forUser, today, thisWeek, thisMonth, search)
- [x] ActivityService com métodos para index, stats, filtros
- [x] StoreActivityRequest com validação completa
- [x] UpdateActivityRequest com validação parcial
- [x] Web ActivityController com index, store, update, destroy
- [x] Rotas web para activities (resource)
- [x] Multi-tenant (company_id) implementado

### Frontend
- [x] Activities/Index.vue com timeline, filtros, stats, paginação

---

## 🔴 Funcionalidades Pendentes

### 1️⃣ CRUD Completo

#### Backend (Alta Prioridade)
- [ ] **Create Page (Activities/Create.vue)**
  - [ ] Rota `GET /activities/create`
  - [ ] Controller method `create()` retornando Inertia view
  - [ ] Passar lista de leads e tipos para o form

- [ ] **Edit Page (Activities/Edit.vue)**
  - [ ] Rota `GET /activities/{id}/edit`
  - [ ] Controller method `edit(Activity $activity)`
  - [ ] Carregar activity com relacionamentos (lead, user)

- [ ] **Show Page (Activities/Show.vue)**
  - [ ] Rota `GET /activities/{id}`
  - [ ] Controller method `show(Activity $activity)`
  - [ ] Exibir detalhes completos da atividade
  - [ ] Linha do tempo de mudanças (audit log)

#### Frontend (Alta Prioridade)
- [ ] **Activities/Create.vue**
  - [ ] Form com campos: lead, type, description, notes, duration
  - [ ] Select de leads com busca
  - [ ] Radio/Select para tipo de atividade
  - [ ] Textarea para description e notes
  - [ ] Input de duration com validação
  - [ ] Botão de submit com loading state

- [ ] **Activities/Edit.vue**
  - [ ] Mesmo form do Create, pré-preenchido
  - [ ] Botão "Cancelar" voltando para index

- [ ] **Activities/Show.vue**
  - [ ] Card com informações da atividade
  - [ ] Badge do tipo com cor
  - [ ] Link para o lead relacionado
  - [ ] Informações de criação/atualização
  - [ ] Botões: Editar, Excluir

- [ ] **Modal de Criação Rápida**
  - [ ] Botão "Nova Atividade" no Index
  - [ ] Modal com form simplificado
  - [ ] Submit via AJAX sem recarregar página

---

### 2️⃣ Filtros Avançados

#### Backend (Média Prioridade)
- [ ] **ActivityService - Novos filtros**
  - [ ] Filtro por data (date_from, date_to)
  - [ ] Filtro por período pré-definido (hoje, ontem, esta semana, mês passado)
  - [ ] Filtro por duração (min_duration, max_duration)
  - [ ] Ordenação customizável (sort_by, sort_direction)

#### Frontend (Média Prioridade)
- [ ] **BoardFilters.vue Component**
  - [ ] Date range picker para filtro de datas
  - [ ] Dropdown de períodos pré-definidos
  - [ ] Range slider para duração
  - [ ] Select de ordenação
  - [ ] Botão "Limpar filtros"

---

### 3️⃣ Integração com Leads

#### Backend (Alta Prioridade)
- [ ] **LeadController - Activities Tab**
  - [ ] Método `getActivities(Lead $lead)` para retornar activities do lead
  - [ ] Método `storeActivity(Lead $lead, Request)` para criar activity direto na página do lead

- [ ] **ActivityService**
  - [ ] `getActivitiesGroupedByType(int $leadId)` - agrupar por tipo
  - [ ] `getActivitySummary(int $leadId)` - resumo de atividades (total, último contato, etc)

#### Frontend (Alta Prioridade)
- [ ] **Leads/Show.vue - Aba de Atividades**
  - [ ] Timeline de atividades do lead
  - [ ] Botão "Nova Atividade" inline
  - [ ] Filtro por tipo de atividade
  - [ ] Estatísticas rápidas (total, última atividade)

---

### 4️⃣ Tipos de Atividade (Enum)

#### Backend (Média Prioridade)
- [ ] **Criar Enum ActivityType**
  - [ ] Valores: CALL, MEETING, EMAIL, NOTE, TASK
  - [ ] Labels: Ligação, Reunião, Email, Nota, Tarefa
  - [ ] Cores: para cada tipo (ex: CALL = blue, MEETING = green)
  - [ ] Ícones: FontAwesome classes

- [ ] **Atualizar Model Activity**
  - [ ] Cast `type` para `ActivityType::class`
  - [ ] Accessor `getTypeLabelAttribute()`
  - [ ] Accessor `getTypeColorAttribute()`
  - [ ] Accessor `getTypeIconAttribute()`

- [ ] **Atualizar FormRequests**
  - [ ] Usar `Rule::enum(ActivityType::class)` para validação

#### Frontend (Média Prioridade)
- [ ] **ActivityTypeSelector.vue Component**
  - [ ] Grid de tipos com ícones e cores
  - [ ] Radio buttons visuais
  - [ ] Usado em Create/Edit forms

---

### 5️⃣ Tarefas (Tasks)

#### Backend (Alta Prioridade)
- [ ] **Adicionar campos na migration**
  - [ ] `due_date` (timestamp) - data de vencimento
  - [ ] `completed_at` (timestamp) - data de conclusão
  - [ ] `priority` (enum: low, medium, high)
  - [ ] `status` (enum: pending, in_progress, completed, cancelled)

- [ ] **ActivityService - Task methods**
  - [ ] `getTasksForUser(int $userId)` - tarefas do usuário
  - [ ] `getOverdueTasks(int $companyId)` - tarefas atrasadas
  - [ ] `getPendingTasks(int $companyId)` - tarefas pendentes
  - [ ] `completeTask(Activity $task)` - marcar como concluída

- [ ] **TaskController (Web)**
  - [ ] `index()` - página de tarefas
  - [ ] `complete(Activity $task)` - marcar como concluída
  - [ ] `reopen(Activity $task)` - reabrir tarefa

#### Frontend (Alta Prioridade)
- [ ] **Tasks/Index.vue**
  - [ ] Lista de tarefas com filtros
  - [ ] Checkbox para marcar como concluída
  - [ ] Badge de prioridade
  - [ ] Badge de status
  - [ ] Indicador de tarefas atrasadas
  - [ ] Ordenação por data de vencimento

- [ ] **TaskCard.vue Component**
  - [ ] Card visual para tarefa
  - [ ] Checkbox de conclusão
  - [ ] Badge de prioridade e status
  - [ ] Data de vencimento
  - [ ] Link para lead relacionado

---

### 6️⃣ Notificações e Lembretes

#### Backend (Baixa Prioridade)
- [ ] **Adicionar campos na migration**
  - [ ] `reminder_at` (timestamp) - data do lembrete
  - [ ] `reminded_at` (timestamp) - quando foi enviado o lembrete

- [ ] **Job SendActivityReminder**
  - [ ] Job para enviar lembretes de atividades
  - [ ] Disparar notificação por email
  - [ ] Disparar notificação in-app
  - [ ] Marcar `reminded_at` após envio

- [ ] **Command CheckActivityReminders**
  - [ ] Rodar a cada 5 minutos (cron)
  - [ ] Buscar activities com `reminder_at` <= now() e `reminded_at` IS NULL
  - [ ] Disparar SendActivityReminder job

- [ ] **Notification ActivityReminderNotification**
  - [ ] Via email e database
  - [ ] Template de email personalizado
  - [ ] Link direto para a atividade

#### Frontend (Baixa Prioridade)
- [ ] **ReminderPicker.vue Component**
  - [ ] Date/time picker para reminder
  - [ ] Opções rápidas (15 min antes, 1 hora antes, 1 dia antes)
  - [ ] Usado em Create/Edit forms

---

### 7️⃣ Anexos (Attachments)

#### Backend (Baixa Prioridade)
- [ ] **Migration create_activity_attachments_table**
  - [ ] Campos: activity_id, filename, path, size, mime_type

- [ ] **Model ActivityAttachment**
  - [ ] Relacionamento com Activity
  - [ ] Accessor para URL do arquivo

- [ ] **ActivityService - Attachment methods**
  - [ ] `uploadAttachment(Activity $activity, UploadedFile $file)`
  - [ ] `deleteAttachment(ActivityAttachment $attachment)`

- [ ] **Controller methods**
  - [ ] `uploadAttachment(Activity $activity, Request)`
  - [ ] `deleteAttachment(ActivityAttachment $attachment)`
  - [ ] `downloadAttachment(ActivityAttachment $attachment)`

#### Frontend (Baixa Prioridade)
- [ ] **FileUploader.vue Component**
  - [ ] Drag & drop de arquivos
  - [ ] Lista de anexos
  - [ ] Ícone por tipo de arquivo
  - [ ] Botão de download
  - [ ] Botão de remover

---

### 8️⃣ Comentários/Notas Adicionais

#### Backend (Baixa Prioridade)
- [ ] **Migration create_activity_comments_table**
  - [ ] Campos: activity_id, user_id, comment, created_at

- [ ] **Model ActivityComment**
  - [ ] Relacionamento com Activity e User

- [ ] **ActivityService**
  - [ ] `addComment(Activity $activity, string $comment, int $userId)`
  - [ ] `deleteComment(ActivityComment $comment)`

#### Frontend (Baixa Prioridade)
- [ ] **CommentSection.vue Component**
  - [ ] Lista de comentários
  - [ ] Form para novo comentário
  - [ ] Avatar do usuário
  - [ ] Timestamp do comentário

---

### 9️⃣ Relatórios e Estatísticas

#### Backend (Média Prioridade)
- [ ] **ActivityReportService**
  - [ ] `getActivitiesCountByType(int $companyId, $dateFrom, $dateTo)`
  - [ ] `getActivitiesCountByUser(int $companyId, $dateFrom, $dateTo)`
  - [ ] `getAverageDurationByType(int $companyId)`
  - [ ] `getMostActiveLeads(int $companyId, int $limit = 10)`
  - [ ] `getMostActiveUsers(int $companyId, int $limit = 10)`

- [ ] **ReportController**
  - [ ] `activities()` - página de relatórios de atividades

#### Frontend (Média Prioridade)
- [ ] **Reports/Activities.vue**
  - [ ] Gráfico de atividades por tipo (chart.js ou apex charts)
  - [ ] Gráfico de atividades por usuário
  - [ ] Tabela de leads mais ativos
  - [ ] Tabela de usuários mais ativos
  - [ ] Filtros de período (data range)
  - [ ] Botão de exportar para PDF/Excel

---

### 🔟 Exportação de Dados

#### Backend (Baixa Prioridade)
- [ ] **ActivityExportService**
  - [ ] `exportToExcel(array $filters)` - usar Laravel Excel
  - [ ] `exportToCsv(array $filters)`
  - [ ] `exportToPdf(array $filters)` - usar DomPDF ou wkhtmltopdf

- [ ] **Controller methods**
  - [ ] `export(Request $request)` - endpoint para exportar

#### Frontend (Baixa Prioridade)
- [ ] **Botão "Exportar" no Index**
  - [ ] Dropdown com opções: Excel, CSV, PDF
  - [ ] Loading state durante exportação
  - [ ] Download automático do arquivo

---

## 📋 Estrutura de Arquivos (Completa)

### Backend
```
app/
├── Enums/
│   └── ActivityType.php (TODO)
├── Http/
│   ├── Controllers/
│   │   ├── Web/
│   │   │   ├── ActivityController.php ✅
│   │   │   ├── TaskController.php (TODO)
│   │   │   └── ReportController.php (TODO)
│   │   └── API/ (já existe)
│   └── Requests/
│       └── CRM/
│           ├── StoreActivityRequest.php ✅
│           └── UpdateActivityRequest.php ✅
├── Models/
│   └── CRM/
│       ├── Activity.php ✅
│       ├── ActivityAttachment.php (TODO)
│       └── ActivityComment.php (TODO)
├── Services/
│   └── CRM/
│       ├── ActivityService.php ✅
│       ├── ActivityReportService.php (TODO)
│       └── ActivityExportService.php (TODO)
├── Jobs/
│   └── SendActivityReminder.php (TODO)
├── Notifications/
│   └── ActivityReminderNotification.php (TODO)
└── Console/
    └── Commands/
        └── CheckActivityReminders.php (TODO)

database/
└── migrations/
    ├── 2025_12_14_000011_create_activities_table.php ✅
    ├── 2025_02_06_000003_add_fields_to_activities_table.php ✅
    ├── xxxx_add_task_fields_to_activities_table.php (TODO)
    ├── xxxx_create_activity_attachments_table.php (TODO)
    └── xxxx_create_activity_comments_table.php (TODO)
```

### Frontend
```
resources/js/
├── Pages/
│   ├── Activities/
│   │   ├── Index.vue ✅
│   │   ├── Create.vue (TODO)
│   │   ├── Edit.vue (TODO)
│   │   └── Show.vue (TODO)
│   ├── Tasks/
│   │   └── Index.vue (TODO)
│   └── Reports/
│       └── Activities.vue (TODO)
└── Components/
    ├── Activities/
    │   ├── ActivityTypeSelector.vue (TODO)
    │   ├── TaskCard.vue (TODO)
    │   ├── CommentSection.vue (TODO)
    │   └── FileUploader.vue (TODO)
    ├── Filters/
    │   └── BoardFilters.vue (TODO)
    └── Forms/
        └── ReminderPicker.vue (TODO)
```

---

## 🎯 Priorização (Sprints)

### Sprint 1 - CRUD Básico (4 dias)
1. Create page (backend + frontend)
2. Edit page (backend + frontend)
3. Show page (backend + frontend)
4. Modal de criação rápida

### Sprint 2 - Integração com Leads (3 dias)
1. Aba de atividades na página do lead
2. Criar atividade direto do lead
3. Resumo de atividades do lead

### Sprint 3 - Tarefas (5 dias)
1. Migration com campos de task
2. TaskController e service methods
3. Tasks/Index.vue com listagem
4. Marcar como concluída/reabrir

### Sprint 4 - Tipos de Atividade (Enum) (2 dias)
1. Criar enum ActivityType
2. Atualizar model e requests
3. ActivityTypeSelector component

### Sprint 5 - Filtros Avançados (2 dias)
1. Filtros de data e duração no backend
2. BoardFilters component

### Sprint 6 - Relatórios (4 dias)
1. ActivityReportService
2. Reports/Activities.vue
3. Gráficos e tabelas

### Sprint 7 - Notificações (3 dias)
1. Job e command para lembretes
2. ReminderPicker component

### Sprint 8 - Anexos (3 dias)
1. Migration e model
2. Upload/download de arquivos
3. FileUploader component

### Sprint 9 - Comentários (2 dias)
1. Migration e model
2. CommentSection component

### Sprint 10 - Exportação (2 dias)
1. ActivityExportService
2. Botão de exportar

---

## 📊 Estimativa de Esforço

| Funcionalidade | Backend | Frontend | Total |
|----------------|---------|----------|-------|
| CRUD Completo | 4h | 6h | 10h |
| Filtros Avançados | 2h | 3h | 5h |
| Integração Leads | 3h | 4h | 7h |
| Enum Tipos | 2h | 2h | 4h |
| Tarefas | 4h | 5h | 9h |
| Notificações | 4h | 2h | 6h |
| Anexos | 3h | 3h | 6h |
| Comentários | 2h | 2h | 4h |
| Relatórios | 4h | 6h | 10h |
| Exportação | 3h | 1h | 4h |
| **TOTAL** | **31h** | **34h** | **65h** |

---

## ✅ Checklist de Qualidade

Antes de marcar uma funcionalidade como completa:

- [ ] Código segue padrões da SKILL (Clean Code PHP/Laravel)
- [ ] Type hints em todos os métodos
- [ ] Validação com FormRequests
- [ ] Multi-tenant implementado (company_id)
- [ ] Testes unitários e feature criados
- [ ] Documentação atualizada
- [ ] Frontend responsivo (mobile, tablet, desktop)
- [ ] Loading states e error handling
- [ ] Traduções (pt-BR)
- [ ] Acessibilidade (ARIA labels, keyboard navigation)

---

**Última atualização:** 06/02/2026  
**Próxima revisão:** Após Sprint 1
