# Pipeline Kanban - TODO List Completo

## 📋 Visão Geral do Projeto
Transformar o módulo de Pipelines em um sistema Kanban completo onde leads podem ser arrastados entre estágios, com registro automático de responsabilidade e histórico completo de movimentações.

---

## 🎯 Funcionalidades Principais a Implementar

### 1. Vista Kanban Board 🎨

#### 1.1 - Criar página Pipelines/Kanban.vue
- [ ] **Layout Principal**
  - [ ] Header com seletor de pipeline
  - [ ] Contadores totais por estágio
  - [ ] Filtros (responsável, data, origem)
  - [ ] Botão "Novo Lead"
  - [ ] Botão alternar entre vista kanban/lista

- [ ] **Board de Colunas**
  - [ ] Renderizar colunas (stages) horizontalmente
  - [ ] Cada coluna mostra nome, cor, probabilidade
  - [ ] Contador de leads e limite por coluna
  - [ ] Scroll horizontal suave se muitas colunas

- [ ] **Card de Lead**
  - [ ] Nome do lead
  - [ ] Empresa (se tiver)
  - [ ] Avatar/inicial do responsável
  - [ ] Tags/labels
  - [ ] Valor do negócio (futuro)
  - [ ] Tempo no estágio atual
  - [ ] Ícones de ações rápidas (editar, ver detalhes)

#### 1.2 - Implementar Drag & Drop
- [ ] **Setup vuedraggable**
  - [ ] Instalar/verificar dependência
  - [ ] Configurar draggable nas colunas
  - [ ] Handles personalizados nos cards

- [ ] **Comportamento de Drag**
  - [ ] Visual feedback ao arrastar
  - [ ] Preview do card sendo movido
  - [ ] Highlight da coluna alvo
  - [ ] Animações suaves

- [ ] **Validações ao Soltar**
  - [ ] Verificar se pode mover para o estágio
  - [ ] Confirmar se necessário (ex: retroceder)
  - [ ] Bloquear movimentos inválidos

---

### 2. Backend - Sistema de Movimentação 🔧

#### 2.1 - Migration: Tabela lead_pipeline
- [ ] **Adicionar campos à tabela existente**
  ```php
  - moved_by (user_id) - quem moveu
  - moved_at (timestamp) - quando moveu
  - entered_at (timestamp) - quando entrou no estágio
  ```

#### 2.2 - Migration: Tabela lead_stage_history
- [ ] **Criar nova tabela de histórico**
  ```php
  - id
  - lead_id
  - pipeline_id
  - from_stage_id (nullable)
  - to_stage_id
  - moved_by (user_id)
  - moved_at (timestamp)
  - duration_in_previous_stage (seconds)
  - notes (text, nullable)
  ```

#### 2.3 - Model: LeadStageHistory
- [ ] **Criar model**
  - [ ] Relacionamentos (lead, pipeline, stages, user)
  - [ ] Casts para datas
  - [ ] Accessor para duração formatada
  - [ ] Scope para filtros comuns

#### 2.4 - Service: LeadMovementService
- [ ] **Criar serviço dedicado**
  ```php
  moveLead($leadId, $toStageId, $userId, $notes = null)
  - Validar se lead existe
  - Validar se estágio existe e pertence ao pipeline
  - Buscar estágio anterior
  - Calcular tempo no estágio anterior
  - Criar registro de histórico
  - Atualizar lead_pipeline
  - Atribuir lead ao usuário se não tiver responsável
  - Disparar eventos
  - Retornar resultado
  ```

- [ ] **Métodos auxiliares**
  ```php
  canMoveLead($lead, $toStage) - validações
  getLeadHistory($leadId) - histórico completo
  getStageMetrics($stageId) - métricas do estágio
  bulkMove($leadIds, $toStageId) - mover múltiplos
  ```

#### 2.5 - Controller: LeadMovementController
- [ ] **Criar controller web**
  - [ ] `move()` - mover um lead
  - [ ] `bulkMove()` - mover múltiplos leads
  - [ ] `history()` - ver histórico de um lead
  - [ ] `revert()` - desfazer última movimentação

#### 2.6 - Rotas Web
```php
// No routes/web.php
Route::post('leads/{lead}/move', [LeadMovementController::class, 'move']);
Route::post('leads/bulk-move', [LeadMovementController::class, 'bulkMove']);
Route::get('leads/{lead}/history', [LeadMovementController::class, 'history']);
Route::post('leads/{lead}/revert', [LeadMovementController::class, 'revert']);
```

---

### 3. Integração Lead com Pipeline 🔗

#### 3.1 - Model: Lead
- [ ] **Adicionar relacionamentos**
  ```php
  currentStage() - estágio atual (últımo)
  pipelineHistory() - histórico de movimentações
  timeInCurrentStage() - tempo no estágio atual
  ```

- [ ] **Adicionar scopes**
  ```php
  scopeInStage($query, $stageId)
  scopeInPipeline($query, $pipelineId)
  scopeWithoutStage($query)
  ```

#### 3.2 - Atualizar LeadController
- [ ] **Método store()**
  - [ ] Ao criar lead, adicionar ao primeiro estágio do pipeline padrão
  - [ ] Registrar no histórico

- [ ] **Método update()**
  - [ ] Permitir mudança de pipeline
  - [ ] Registrar movimentação se trocar pipeline

#### 3.3 - Seed de Teste
- [ ] **LeadSeeder**
  - [ ] Distribuir leads existentes entre estágios
  - [ ] Criar histórico retroativo
  - [ ] Variar responsáveis

---

### 4. Vista Kanban - Frontend 🎨

#### 4.1 - Componente KanbanBoard.vue
```vue
Estrutura:
- Props: pipeline, stages, leads
- Drag & drop configurado
- Emits: onMove, onCardClick, onAddLead
```

- [ ] **Funcionalidades**
  - [ ] Renderizar colunas dinamicamente
  - [ ] Mostrar leads por estágio
  - [ ] Drag & drop funcional
  - [ ] Loading states
  - [ ] Empty states por coluna

#### 4.2 - Componente LeadCard.vue
```vue
Estrutura:
- Props: lead, compact
- Mostrar informações essenciais
- Ações rápidas (ver, editar, mover manual)
```

- [ ] **Funcionalidades**
  - [ ] Avatar do responsável
  - [ ] Badge de tempo no estágio
  - [ ] Indicador visual de prioridade
  - [ ] Tooltip com mais info
  - [ ] Modal de detalhes rápidos

#### 4.3 - Componente KanbanColumn.vue
```vue
Estrutura:
- Props: stage, leads, dragging
- Header com nome e contador
- Drop zone configurado
```

- [ ] **Funcionalidades**
  - [ ] Scroll interno se muitos cards
  - [ ] Indicador de limite de WIP
  - [ ] Botão adicionar lead direto no estágio
  - [ ] Filtro por texto dentro da coluna

#### 4.4 - Página Pipelines/Board.vue
- [ ] **Criar página completa**
  - [ ] Integrar KanbanBoard
  - [ ] Controles de filtro
  - [ ] Seletor de pipeline
  - [ ] Botões de ação (adicionar, exportar)
  - [ ] Breadcrumbs

- [ ] **Estado e lógica**
  - [ ] Gerenciar drag & drop
  - [ ] Chamadas API ao mover
  - [ ] Otimistic updates
  - [ ] Error handling
  - [ ] Confirmation modals

---

### 5. Modal de Movimentação Manual 📝

#### 5.1 - Componente MoveLeadModal.vue
- [ ] **Criar modal**
  - [ ] Seletor de estágio destino
  - [ ] Campo de notas (opcional)
  - [ ] Mostrar estágio atual
  - [ ] Preview do movimento

- [ ] **Funcionalidades**
  - [ ] Validar destino diferente de origem
  - [ ] Sugerir próximo estágio lógico
  - [ ] Avisos se retroceder
  - [ ] Confirmação

---

### 6. Histórico de Movimentações 📜

#### 6.1 - Componente LeadHistory.vue
- [ ] **Criar componente**
  - [ ] Timeline vertical
  - [ ] Cada item mostra: data, de→para, quem moveu, tempo decorrido
  - [ ] Expandir para ver notas
  - [ ] Filtros de período

#### 6.2 - Integrar na página Lead/Show.vue
- [ ] **Adicionar tab/seção**
  - [ ] "Histórico no Pipeline"
  - [ ] Mostrar LeadHistory component
  - [ ] Gráfico de tempo por estágio
  - [ ] Métricas: tempo total, estágio mais demorado

---

### 7. Regras de negócio e Validações ✅

#### 7.1 - Permissões
- [ ] **Criar policies**
  ```php
  canMoveLead($user, $lead, $stage)
  canRevertMovement($user, $lead)
  canViewHistory($user, $lead)
  ```

#### 7.2 - Validações
- [ ] **Regras de movimentação**
  - [ ] Não permitir mover para mesmo estágio
  - [ ] Confirmar se retroceder (opcional)
  - [ ] Verificar se estágio pertence ao pipeline
  - [ ] Validar se lead pertence à mesma empresa

#### 7.3 - Business Rules
- [ ] **Auto-atribuição**
  - [ ] Ao mover, se lead não tem responsável, atribuir quem moveu
  - [ ] Notificar responsável anterior da mudança
  - [ ] Permitir reassign no movimento

- [ ] **Limites**
  - [ ] WIP limit por estágio (opcional)
  - [ ] Bloquear se exceder limite

---

### 8. Notificações e Eventos 🔔

#### 8.1 - Events
- [ ] **Criar eventos**
  ```php
  LeadMovedEvent($lead, $from, $to, $movedBy)
  LeadAssignedEvent($lead, $assignedTo, $assignedBy)
  ```

#### 8.2 - Listeners
- [ ] **Criar listeners**
  - [ ] Notificar responsável da mudança
  - [ ] Notificar gestores de mudanças importantes
  - [ ] Registrar em activity log
  - [ ] Atualizar métricas em cache

#### 8.3 - Notificações em tempo real
- [ ] **Broadcasting (opcional)**
  - [ ] Websocket para atualizar board em tempo real
  - [ ] Mostrar quem está movendo o que
  - [ ] Sincronizar múltiplos usuários

---

### 9. Métricas e Relatórios 📊

#### 9.1 - Dashboard de Pipeline
- [ ] **Criar página Pipelines/Analytics.vue**
  - [ ] Funil de conversão
  - [ ] Tempo médio por estágio
  - [ ] Taxa de conversão por estágio
  - [ ] Leads por responsável
  - [ ] Previsão de fechamento

#### 9.2 - Service: PipelineMetricsService
- [ ] **Criar serviço**
  ```php
  getConversionRate($pipelineId)
  getAverageTimePerStage($pipelineId)
  getLeadsVelocity($pipelineId)
  getBottlenecks($pipelineId)
  getForecast($pipelineId)
  ```

#### 9.3 - Exportação
- [ ] **Relatórios**
  - [ ] Exportar board atual (PDF/Excel)
  - [ ] Exportar histórico de movimentações
  - [ ] Relatório de performance por usuário

---

### 10. Recursos Adicionais ⚡

#### 10.1 - Ações em massa
- [ ] **Checkbox selection**
  - [ ] Selecionar múltiplos cards
  - [ ] Mover todos selecionados
  - [ ] Atribuir responsável em massa
  - [ ] Adicionar tags em massa

#### 10.2 - Filtros avançados
- [ ] **Filtros no Kanban**
  - [ ] Por responsável
  - [ ] Por data de criação
  - [ ] Por fonte (origin)
  - [ ] Por tempo no estágio
  - [ ] Por tags

#### 10.3 - Pesquisa
- [ ] **Busca global no board**
  - [ ] Buscar por nome
  - [ ] Buscar por empresa
  - [ ] Buscar por email/telefone
  - [ ] Highlight resultados

#### 10.4 - Configurações do Pipeline
- [ ] **Página de configuração**
  - [ ] Definir WIP limits
  - [ ] Configurar automações
  - [ ] Regras de movimentação
  - [ ] Campos obrigatórios por estágio

---

### 11. Automações (Fase Futura) 🤖

#### 11.1 - Triggers
- [ ] **Automações baseadas em tempo**
  - [ ] Mover automaticamente após X dias
  - [ ] Notificar se parado muito tempo
  - [ ] Marcar como perdido após inatividade

#### 11.2 - Regras condicionais
- [ ] **Actions baseadas em critérios**
  - [ ] Se movido para "Proposta", criar task
  - [ ] Se movido para "Ganho", criar contrato
  - [ ] Se retroceder, exigir motivo

---

### 12. Testes 🧪

#### 12.1 - Testes Unitários
- [ ] **LeadMovementService**
  - [ ] Testar movimentação normal
  - [ ] Testar validações
  - [ ] Testar cálculo de tempo
  - [ ] Testar auto-atribuição

#### 12.2 - Testes de Feature
- [ ] **API Endpoints**
  - [ ] Testar movimento via API
  - [ ] Testar bulk move
  - [ ] Testar histórico
  - [ ] Testar revert

#### 12.3 - Testes E2E
- [ ] **Cypress/Playwright**
  - [ ] Testar drag & drop no board
  - [ ] Testar filtros
  - [ ] Testar criação de lead pelo board
  - [ ] Testar atualização em tempo real

---

### 13. Documentação 📚

#### 13.1 - Documentação técnica
- [ ] **Atualizar docs**
  - [ ] Arquitetura do módulo
  - [ ] Fluxo de dados
  - [ ] Diagramas de sequência
  - [ ] API Reference

#### 13.2 - Manual do usuário
- [ ] **Criar guia**
  - [ ] Como usar o Kanban
  - [ ] Como mover leads
  - [ ] Como interpretar métricas
  - [ ] FAQ

---

## 🗂️ Estrutura de Arquivos a Criar

```
app/
├── Events/
│   ├── LeadMovedEvent.php
│   └── LeadAssignedEvent.php
├── Listeners/
│   ├── NotifyLeadMovement.php
│   └── UpdatePipelineCache.php
├── Services/
│   ├── LeadMovementService.php
│   └── PipelineMetricsService.php
├── Http/
│   └── Controllers/
│       └── Web/
│           └── LeadMovementController.php
└── Models/
    └── CRM/
        └── LeadStageHistory.php

database/
├── migrations/
│   ├── YYYY_MM_DD_add_movement_fields_to_lead_pipeline_table.php
│   └── YYYY_MM_DD_create_lead_stage_history_table.php
└── seeders/
    └── LeadPipelineDistributionSeeder.php

resources/
└── js/
    ├── Pages/
    │   └── Pipelines/
    │       ├── Board.vue (nova)
    │       └── Analytics.vue (nova)
    └── Components/
        ├── Kanban/
        │   ├── KanbanBoard.vue
        │   ├── KanbanColumn.vue
        │   └── LeadCard.vue
        ├── Lead/
        │   ├── LeadHistory.vue
        │   └── MoveLeadModal.vue
        └── Pipeline/
            └── PipelineSelector.vue

routes/
└── web.php (adicionar rotas)

docs/
├── PIPELINES_KANBAN.md
└── PIPELINES_API.md
```

---

## 📅 Cronograma Sugerido

### Sprint 1 (1 semana) - Backend Básico
- ✅ Migrations
- ✅ Models
- ✅ LeadMovementService
- ✅ LeadMovementController
- ✅ Rotas

### Sprint 2 (1 semana) - Frontend Kanban
- Vue components (Board, Column, Card)
- Drag & drop básico
- Integração com API
- Loading states

### Sprint 3 (4 dias) - Histórico e Detalhes
- LeadHistory component
- Modal de movimento manual
- Integração na página do Lead
- Testes de componentes

### Sprint 4 (4 dias) - Melhorias e UX
- Filtros e busca
- Ações em massa
- Notificações
- Validações avançadas

### Sprint 5 (3 dias) - Métricas
- Dashboard de analytics
- Relatórios
- Exportação
- Gráficos

### Sprint 6 (2 dias) - Polimento
- Testes E2E
- Documentação
- Refinamentos de UX
- Performance

---

## 🎯 Prioridades

### 🔴 Alta (Funcionalidade Core)
1. Vista Kanban básica
2. Drag & drop de leads
3. Registro de movimentações
4. Auto-atribuição de responsável
5. Histórico de movimentações

### 🟡 Média (Melhorias Importantes)
6. Filtros e busca
7. Modal de movimento manual
8. Notificações
9. Métricas básicas
10. Ações em massa

### 🟢 Baixa (Nice to Have)
11. Dashboard de analytics avançado
12. Automações
13. Broadcasting em tempo real
14. WIP limits
15. Relatórios avançados

---

## 💡 Considerações Técnicas

### Performance
- Implementar paginação/lazy loading para muitos leads
- Cache de métricas calculadas
- Otimistic updates no frontend
- Debounce nas buscas

### Segurança
- Validar permissões em cada movimentação
- CSRF protection
- Rate limiting nas ações de massa
- Audit log de todas movimentações

### Escalabilidade
- Queue para processamento de ações em massa
- Cache distribuído para métricas
- Arquitetura pronta para microserviços futuros

---

## ✅ Checklist de Conclusão

Ao finalizar cada item:
- [ ] Código implementado e testado
- [ ] Testes unitários passando
- [ ] Documentação atualizada
- [ ] Code review aprovado
- [ ] Deploy em staging
- [ ] Testes de aceitação do usuário
- [ ] Deploy em produção

---

**Última atualização:** 06/02/2026  
**Status:** 📝 Planejamento Completo  
**Próximo passo:** Iniciar Sprint 1 - Backend Básico
