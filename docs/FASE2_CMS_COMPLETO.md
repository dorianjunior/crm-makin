# FASE 2: CMS Completo - Documentação

## 📋 Resumo

A FASE 2 adicionou 6 novos tipos de conteúdo ao CMS, expandindo significativamente as capacidades do sistema.

## ✅ Tipos de Conteúdo Implementados

### 1. Portfolio (Portfólio de Projetos)
**Propósito**: Showcase de projetos realizados

**Campos**:
- `title` - Título do projeto
- `slug` - URL amigável (auto-gerado)
- `description` - Descrição completa
- `client_name` - Nome do cliente
- `project_url` - URL do projeto online
- `images` - Array de URLs de imagens
- `technologies` - Array de tecnologias utilizadas
- `completion_date` - Data de conclusão
- `status` - ContentStatus (draft/pending/published)
- `order` - Ordem de exibição

**Endpoints**:
```
GET    /api/cms/portfolios                      - Listar portfólios (filtros: site_id, status)
POST   /api/cms/portfolios                      - Criar portfólio
GET    /api/cms/portfolios/{id}                 - Ver portfólio
PUT    /api/cms/portfolios/{id}                 - Atualizar portfólio
DELETE /api/cms/portfolios/{id}                 - Deletar portfólio
POST   /api/cms/portfolios/{id}/publish         - Publicar
POST   /api/cms/portfolios/{id}/unpublish       - Despublicar
POST   /api/cms/portfolios/{id}/request-approval - Solicitar aprovação
```

---

### 2. FAQ (Perguntas Frequentes)
**Propósito**: Base de conhecimento de perguntas e respostas

**Campos**:
- `category` - Categoria da FAQ (ex: "Pagamento", "Suporte")
- `question` - Pergunta
- `answer` - Resposta completa
- `status` - ContentStatus
- `order` - Ordem dentro da categoria

**Endpoints**:
```
GET    /api/cms/faqs                            - Listar FAQs (filtros: site_id, category, status)
POST   /api/cms/faqs                            - Criar FAQ
GET    /api/cms/faqs/{id}                       - Ver FAQ
PUT    /api/cms/faqs/{id}                       - Atualizar FAQ
DELETE /api/cms/faqs/{id}                       - Deletar FAQ
POST   /api/cms/faqs/{id}/publish               - Publicar
POST   /api/cms/faqs/{id}/unpublish             - Despublicar
POST   /api/cms/faqs/{id}/request-approval      - Solicitar aprovação
```

**Filtros Especiais**:
- `category` - Filtrar por categoria específica

---

### 3. Testimonial (Depoimentos)
**Propósito**: Depoimentos de clientes e avaliações

**Campos**:
- `author_name` - Nome do autor
- `author_position` - Cargo do autor
- `author_company` - Empresa do autor
- `author_avatar` - URL da foto do autor
- `content` - Texto do depoimento
- `rating` - Avaliação (1-5 estrelas)
- `status` - ContentStatus
- `order` - Ordem de exibição

**Endpoints**:
```
GET    /api/cms/testimonials                    - Listar depoimentos (filtros: site_id, status, min_rating)
POST   /api/cms/testimonials                    - Criar depoimento
GET    /api/cms/testimonials/{id}               - Ver depoimento
PUT    /api/cms/testimonials/{id}               - Atualizar depoimento
DELETE /api/cms/testimonials/{id}               - Deletar depoimento
POST   /api/cms/testimonials/{id}/publish       - Publicar
POST   /api/cms/testimonials/{id}/unpublish     - Despublicar
POST   /api/cms/testimonials/{id}/request-approval - Solicitar aprovação
```

**Filtros Especiais**:
- `min_rating` - Mostrar apenas depoimentos com rating >= valor especificado

---

### 4. Team Member (Membros da Equipe)
**Propósito**: Perfis dos membros da equipe/staff

**Campos**:
- `name` - Nome completo
- `position` - Cargo
- `department` - Departamento
- `bio` - Biografia
- `photo` - URL da foto
- `email` - Email de contato
- `phone` - Telefone
- `social_links` - Array de links de redes sociais
- `status` - ContentStatus
- `order` - Ordem de exibição

**Endpoints**:
```
GET    /api/cms/team-members                    - Listar membros (filtros: site_id, department, status)
POST   /api/cms/team-members                    - Criar membro
GET    /api/cms/team-members/{id}               - Ver membro
PUT    /api/cms/team-members/{id}               - Atualizar membro
DELETE /api/cms/team-members/{id}               - Deletar membro
POST   /api/cms/team-members/{id}/publish       - Publicar
POST   /api/cms/team-members/{id}/unpublish     - Despublicar
POST   /api/cms/team-members/{id}/request-approval - Solicitar aprovação
```

**Filtros Especiais**:
- `department` - Filtrar por departamento específico

---

### 5. Form (Formulários Dinâmicos)
**Propósito**: Criação de formulários customizados

**Campos**:
- `name` - Nome do formulário
- `slug` - Identificador único (auto-gerado)
- `description` - Descrição
- `fields` - Array JSON de campos do formulário
  - `name` - Nome do campo
  - `type` - Tipo (text, email, textarea, select, checkbox, radio, file)
  - `label` - Label de exibição
  - `required` - Obrigatório?
  - `options` - Opções (para select/radio/checkbox)
- `settings` - Array JSON de configurações adicionais
- `submit_button_text` - Texto do botão de envio
- `success_message` - Mensagem de sucesso
- `notification_email` - Email para notificações
- `active` - Booleano (ativo/inativo)

**Endpoints**:
```
GET    /api/cms/forms                           - Listar formulários (filtros: site_id, active)
POST   /api/cms/forms                           - Criar formulário
GET    /api/cms/forms/{id}                      - Ver formulário
PUT    /api/cms/forms/{id}                      - Atualizar formulário
DELETE /api/cms/forms/{id}                      - Deletar formulário
POST   /api/cms/forms/{id}/activate             - Ativar formulário
POST   /api/cms/forms/{id}/deactivate           - Desativar formulário
```

**⚠️ Nota**: Formulários usam sistema de ativação (active/inactive) em vez de publicação (draft/published).

**Exemplo de Campo**:
```json
{
  "name": "full_name",
  "type": "text",
  "label": "Nome Completo",
  "required": true
}
```

---

### 6. Banner (Banners Promocionais)
**Propósito**: Banners promocionais com agendamento

**Campos**:
- `title` - Título interno
- `location` - Localização do banner (ex: "homepage-hero", "sidebar")
- `image` - URL da imagem
- `link_url` - URL de destino ao clicar
- `new_window` - Abrir em nova janela?
- `alt_text` - Texto alternativo da imagem
- `start_date` - Data de início de exibição
- `end_date` - Data de término de exibição
- `status` - ContentStatus
- `order` - Ordem de exibição

**Endpoints**:
```
GET    /api/cms/banners                         - Listar banners (filtros: site_id, location, status, active_only)
POST   /api/cms/banners                         - Criar banner
GET    /api/cms/banners/{id}                    - Ver banner
PUT    /api/cms/banners/{id}                    - Atualizar banner
DELETE /api/cms/banners/{id}                    - Deletar banner
POST   /api/cms/banners/{id}/publish            - Publicar
POST   /api/cms/banners/{id}/unpublish          - Despublicar
POST   /api/cms/banners/{id}/request-approval   - Solicitar aprovação
```

**Filtros Especiais**:
- `location` - Filtrar por localização do banner
- `active_only=true` - Mostrar apenas banners atualmente ativos (dentro do período start_date/end_date)

**Recurso Extra**: Método `isActive()` verifica se banner está publicado E dentro do período de exibição.

---

## 🗄️ Estrutura de Banco de Dados

Todas as tabelas seguem o padrão CMS com:
- `site_id` - FK para sites
- `created_by` - FK para users
- `published_at` - Timestamp de publicação
- `created_at` / `updated_at` - Timestamps padrão
- `deleted_at` - SoftDeletes

Índices criados para otimização:
- `(site_id, status)` - Queries por site e status
- `order` - Ordenação
- Índices específicos por tipo (ex: `(site_id, location, status)` em banners)

---

## 📦 Arquivos Criados

### Migrations (6 arquivos)
- `2026_01_28_210859_create_portfolios_table.php`
- `2026_01_28_210905_create_faqs_table.php`
- `2026_01_28_210909_create_testimonials_table.php`
- `2026_01_28_210912_create_team_members_table.php`
- `2026_01_28_210915_create_forms_table.php`
- `2026_01_28_210920_create_banners_table.php`

### Models (6 arquivos)
- `app/Models/CMS/Portfolio.php`
- `app/Models/CMS/Faq.php`
- `app/Models/CMS/Testimonial.php`
- `app/Models/CMS/TeamMember.php`
- `app/Models/CMS/Form.php`
- `app/Models/CMS/Banner.php`

### Controllers (6 arquivos)
- `app/Http/Controllers/API/CMS/PortfolioController.php`
- `app/Http/Controllers/API/CMS/FaqController.php`
- `app/Http/Controllers/API/CMS/TestimonialController.php`
- `app/Http/Controllers/API/CMS/TeamMemberController.php`
- `app/Http/Controllers/API/CMS/FormController.php`
- `app/Http/Controllers/API/CMS/BannerController.php`

### Form Requests (12 arquivos - Store/Update pairs)
- `app/Http/Requests/CMS/StorePortfolioRequest.php` + `UpdatePortfolioRequest.php`
- `app/Http/Requests/CMS/StoreFaqRequest.php` + `UpdateFaqRequest.php`
- `app/Http/Requests/CMS/StoreTestimonialRequest.php` + `UpdateTestimonialRequest.php`
- `app/Http/Requests/CMS/StoreTeamMemberRequest.php` + `UpdateTeamMemberRequest.php`
- `app/Http/Requests/CMS/StoreFormRequest.php` + `UpdateFormRequest.php`
- `app/Http/Requests/CMS/StoreBannerRequest.php` + `UpdateBannerRequest.php`

### API Resources (6 arquivos)
- `app/Http/Resources/CMS/PortfolioResource.php`
- `app/Http/Resources/CMS/FaqResource.php`
- `app/Http/Resources/CMS/TestimonialResource.php`
- `app/Http/Resources/CMS/TeamMemberResource.php`
- `app/Http/Resources/CMS/FormResource.php`
- `app/Http/Resources/CMS/BannerResource.php`

**Total**: 36 arquivos criados

---

## 🔗 Rotas

**Total de Rotas CMS**: 76 rotas
- **FASE 1**: 29 rotas (sites, pages, posts, menus)
- **FASE 2**: 47 rotas (portfolios, faqs, testimonials, team-members, forms, banners)

Para listar todas as rotas:
```bash
php artisan route:list --path=cms
```

---

## 🔐 Autenticação

Todas as rotas CMS requerem:
- Middleware `auth:sanctum` - Token de autenticação
- Middleware `active` - Conta de usuário ativa

---

## 🎯 Features por Tipo

### Com Sistema de Publicação (5 tipos)
- Portfolio, FAQ, Testimonial, TeamMember, Banner
- Enum: `ContentStatus` (draft, pending, published)
- Versionamento via `ContentVersion`
- Aprovação via `ContentApproval`
- Métodos: `publish()`, `unpublish()`, `requestApproval()`

### Com Sistema de Ativação (1 tipo)
- Form
- Boolean: `active` (true/false)
- Sem versionamento ou aprovação
- Métodos: `activate()`, `deactivate()`

---

## 📊 Relacionamentos

Todos os tipos têm:
- `belongsTo('site')` - Site proprietário
- `belongsTo('creator')` - Usuário criador

Tipos com publicação também têm:
- `morphMany('versions')` - Histórico de versões
- `morphMany('approvals')` - Histórico de aprovações

---

## 🔍 Scopes Disponíveis

### Scopes Comuns (todos com ContentStatus)
- `published()` - Apenas publicados
- `draft()` - Apenas rascunhos
- `pending()` - Aguardando aprovação
- `forSite($siteId)` - Por site específico

### Scopes Específicos
- **Faq**: `byCategory($category)` - Filtrar por categoria
- **Testimonial**: `highRated($minRating = 4)` - Rating mínimo
- **TeamMember**: `byDepartment($department)` - Por departamento
- **Form**: `active()` - Apenas formulários ativos
- **Banner**: `active()` - Banners dentro do período, `byLocation($location)` - Por localização

---

## 🧪 Próximos Passos (Pendente)

### 3. Sistema de Aprovação Completo
- [ ] Events (ContentPublished, ApprovalRequested, etc)
- [ ] Notifications para managers
- [ ] Testes de workflow

### 4. Preview e Versionamento Avançado
- [ ] JWT preview tokens
- [ ] Endpoint público de preview
- [ ] Rollback de versões
- [ ] Comparação de versões (diff)

### 5. SDK JavaScript
- [ ] Pacote NPM `cms-client-sdk`
- [ ] Métodos para todos os content types
- [ ] Autenticação via API key
- [ ] Integração com Vue.js

---

## 📝 Exemplo de Uso

### Criar Portfolio
```bash
POST /api/cms/portfolios
Authorization: Bearer {token}

{
  "site_id": 1,
  "title": "Sistema CRM Avançado",
  "description": "Desenvolvimento completo de CRM para gestão comercial",
  "client_name": "Empresa XYZ Ltda",
  "project_url": "https://crm.exemplo.com",
  "images": [
    "/storage/portfolio/crm-dashboard.jpg",
    "/storage/portfolio/crm-leads.jpg"
  ],
  "technologies": ["Laravel", "Vue.js", "MariaDB", "Redis"],
  "completion_date": "2025-01-15",
  "status": "draft",
  "order": 10
}
```

### Listar FAQs por Categoria
```bash
GET /api/cms/faqs?category=Pagamento&status=published
Authorization: Bearer {token}
```

### Ativar Formulário
```bash
POST /api/cms/forms/5/activate
Authorization: Bearer {token}
```

### Banners Ativos por Localização
```bash
GET /api/cms/banners?location=homepage-hero&active_only=true
Authorization: Bearer {token}
```

---

**Status**: ✅ FASE 2 - Controllers e Resources Completos (36 arquivos, 76 rotas)
