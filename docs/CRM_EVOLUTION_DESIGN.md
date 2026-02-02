# 🚀 CRM Makin Evolution - Design Completo

> **Projeto:** CRM Multi-Site com Headless CMS, Integrações Sociais e IA  
> **Versão:** 2.0  
> **Data:** Janeiro 2026  
> **Arquitetura:** MVC + Service Layer (SOLID, Clean Code)

---

## 📋 Índice

1. [Visão Geral](#-visão-geral)
2. [Decisões de Arquitetura](#-decisões-de-arquitetura)
3. [Modelagem de Banco de Dados](#-modelagem-de-banco-de-dados)
4. [Estrutura do Projeto](#-estrutura-do-projeto)
5. [Services e Controllers](#-services-e-controllers)
6. [Jobs e Queues](#-jobs-e-queues)
7. [Integrações](#-integrações)
8. [Sistema de IA](#-sistema-de-ia)
9. [Frontend](#-frontend)
10. [Roadmap de Implementação](#-roadmap-de-implementação)
11. [Exemplos de Código](#-exemplos-de-código)
12. [Testes](#-testes)
13. [Decision Log](#-decision-log)
14. [Referências](#-referências)

---

## 🎯 Visão Geral

### O que será construído

**CRM Makin Evolution** é uma evolução do CRM atual que adiciona:

1. **Headless CMS completo** - Gerenciar conteúdo de múltiplos sites via API REST
   - 10 tipos de conteúdo: Pages, Posts, Portfolio, FAQ, Testimonials, Team, Forms, Banners, Menu, SEO
   - Versionamento simples (última publicada + draft)
   - Preview mode com tokens seguros
   - Workflow de aprovação (draft → pending → published)

2. **Integrações Sociais**
   - **Instagram:** Read-only (posts e DMs via Meta Graph API)
   - **WhatsApp:** Business API oficial (enviar e receber mensagens)
   - Vinculação automática com leads do CRM

3. **IA Generativa (Google Gemini)**
   - Respostas automáticas contextuais
   - Configurável por empresa
   - Histórico de conversas e aprendizado
   - Templates de prompts reutilizáveis

4. **Painel Administrativo Moderno**
   - Vue 3 + Inertia.js + PrimeVue
   - Interface responsiva e profissional
   - Real-time updates

5. **Refatoração Completa**
   - Aplicação de SOLID principles
   - Clean Code patterns
   - Testes (60-75% cobertura)
   - Análise estática (Larastan)

### Por que existe

- Centralizar gestão de múltiplos sites de pequenas empresas
- Unificar leads + conteúdo + comunicação social + IA em um único sistema
- Criar projeto de portfólio profissional demonstrando capacidade técnica avançada

### Stack Tecnológico

| Camada | Tecnologia | Versão |
|--------|------------|--------|
| Backend | Laravel | 12 |
| Frontend | Vue.js | 3 |
| UI Framework | Inertia.js + PrimeVue | Latest |
| Database | MySQL | 8.4 |
| Cache/Queue | Redis | Latest |
| IA | Google Gemini | API v1 |
| Social | Meta Graph API | v18+ |
| Tests | PHPUnit/Pest | 11 |
| Static Analysis | Larastan | Level 5+ |
| Code Style | Laravel Pint | Latest |

### Requisitos Não-Funcionais

- **Performance:** < 500ms resposta API, operações pesadas em queues
- **Escala:** 5-20 empresas inicial, arquitetar para 100+
- **Segurança:** Multi-tenancy isolado, Sanctum auth, rate limiting, tokens criptografados
- **Disponibilidade:** Queues com retry, logs estruturados, monitoring
- **Manutenibilidade:** SOLID, Clean Code, testes automatizados, documentação

---

## 🏗️ Decisões de Arquitetura

### Abordagem Escolhida: MVC Melhorado com Service Layer

**Por que essa escolha:**
- ✅ Familiaridade com o padrão Laravel tradicional
- ✅ Produtividade sem over-engineering
- ✅ Fácil onboarding de novos desenvolvedores
- ✅ Aplicação de SOLID onde faz sentido
- ✅ Testabilidade através de Services injetáveis

**Alternativas consideradas:**
- Domain-Driven Design Pragmático (mais complexo, maior curva)
- Microservices Modulares (over-engineering para o escopo)

### Princípios Aplicados

#### 1. Single Responsibility Principle (SRP)
- Controllers apenas orquestram (magros)
- Services executam lógica de negócio
- Models apenas representam dados e relationships
- Jobs executam tarefas assíncronas específicas

#### 2. Open/Closed Principle (OCP)
- Interfaces para services externos (AIProviderInterface, MessageServiceInterface)
- Factory Pattern para trocar implementações sem modificar código

#### 3. Liskov Substitution Principle (LSP)
- Implementações podem ser trocadas via DI sem quebrar sistema
- Ex: GeminiService → OpenAIService usando mesma interface

#### 4. Interface Segregation Principle (ISP)
- Interfaces específicas e pequenas
- Não forçar implementações desnecessárias

#### 5. Dependency Inversion Principle (DIP)
- Controllers dependem de interfaces, não implementações concretas
- Injeção de dependências via constructor

---

## 🗄️ Modelagem de Banco de Dados

### Diagrama ER - CMS Module

```
┌─────────────┐         ┌─────────────┐
│  companies  │────────<│    sites    │
└─────────────┘         └─────────────┘
                              │
                              │ 1:N
                              │
          ┌───────────────────┼───────────────────┐
          │                   │                   │
          ▼                   ▼                   ▼
    ┌─────────┐         ┌─────────┐        ┌──────────┐
    │  pages  │         │  posts  │        │portfolios│
    └─────────┘         └─────────┘        └──────────┘
          │                   │
          │                   │
          ▼                   ▼
    ┌─────────────────────────────┐
    │    content_versions         │  (polymorphic)
    └─────────────────────────────┘
```

### Tabelas CMS

#### sites
```sql
CREATE TABLE sites (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    company_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    domain VARCHAR(255) NOT NULL,
    api_key VARCHAR(64) NOT NULL,              -- Token para autenticação
    config JSON,                               -- { theme, seo_defaults, analytics_id }
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    UNIQUE KEY unique_domain (domain),
    UNIQUE KEY unique_api_key (api_key),
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    INDEX idx_company_active (company_id, is_active),
    INDEX idx_api_key (api_key)
);
```

#### pages (Páginas Estáticas)
```sql
CREATE TABLE pages (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    site_id BIGINT UNSIGNED NOT NULL,
    slug VARCHAR(255) NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT,
    meta_title VARCHAR(60),
    meta_description VARCHAR(160),
    meta_keywords VARCHAR(255),
    status ENUM('draft', 'pending', 'published') DEFAULT 'draft',
    published_version_id BIGINT UNSIGNED NULL,
    published_at TIMESTAMP NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    updated_by BIGINT UNSIGNED,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    UNIQUE KEY unique_site_slug (site_id, slug),
    FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (updated_by) REFERENCES users(id),
    FOREIGN KEY (published_version_id) REFERENCES content_versions(id) ON DELETE SET NULL,
    
    INDEX idx_site_status (site_id, status),
    INDEX idx_site_published (site_id, published_at),
    INDEX idx_created_by (created_by),
    FULLTEXT INDEX idx_search (title, content)
);
```

#### post_categories
```sql
CREATE TABLE post_categories (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    site_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    UNIQUE KEY unique_site_category (site_id, slug),
    FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE CASCADE,
    INDEX idx_site (site_id)
);
```

#### posts (Blog/Notícias)
```sql
CREATE TABLE posts (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    site_id BIGINT UNSIGNED NOT NULL,
    category_id BIGINT UNSIGNED,
    slug VARCHAR(255) NOT NULL,
    title VARCHAR(255) NOT NULL,
    excerpt TEXT,
    content TEXT,
    featured_image VARCHAR(500),
    author_id BIGINT UNSIGNED NOT NULL,
    tags JSON,                                 -- Array de tags
    published_at TIMESTAMP NULL,
    status ENUM('draft', 'pending', 'published') DEFAULT 'draft',
    published_version_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    UNIQUE KEY unique_site_slug (site_id, slug),
    FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES post_categories(id) ON DELETE SET NULL,
    FOREIGN KEY (author_id) REFERENCES users(id),
    FOREIGN KEY (published_version_id) REFERENCES content_versions(id) ON DELETE SET NULL,
    
    INDEX idx_site_status_published (site_id, status, published_at DESC),
    INDEX idx_category (category_id),
    INDEX idx_author (author_id),
    FULLTEXT INDEX idx_search (title, excerpt, content)
);
```

#### portfolios, faqs, testimonials, team_members, forms, banners
```sql
-- Estrutura similar aos posts, adaptada para cada tipo
-- Ver migrations específicas no roadmap
```

#### menus
```sql
CREATE TABLE menus (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    site_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,                -- header, footer, sidebar
    config JSON,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    UNIQUE KEY unique_site_menu (site_id, name),
    FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE CASCADE,
    INDEX idx_site (site_id)
);
```

#### menu_items (Hierárquico)
```sql
CREATE TABLE menu_items (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    menu_id BIGINT UNSIGNED NOT NULL,
    parent_id BIGINT UNSIGNED NULL,            -- Self-reference
    label VARCHAR(100) NOT NULL,
    url VARCHAR(500),
    icon VARCHAR(50),
    order INT DEFAULT 0,
    target ENUM('_self', '_blank') DEFAULT '_self',
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (menu_id) REFERENCES menus(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES menu_items(id) ON DELETE CASCADE,
    INDEX idx_menu_order (menu_id, order),
    INDEX idx_parent (parent_id)
);
```

#### content_versions (Versionamento Polymorphic)
```sql
CREATE TABLE content_versions (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    versionable_type VARCHAR(255) NOT NULL,    -- App\Models\CMS\Page
    versionable_id BIGINT UNSIGNED NOT NULL,
    data JSON NOT NULL,                        -- Snapshot completo
    created_by BIGINT UNSIGNED NOT NULL,
    notes TEXT,
    created_at TIMESTAMP,
    
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_versionable (versionable_type, versionable_id, created_at DESC),
    INDEX idx_created_by (created_by)
);
```

#### content_approvals (Workflow)
```sql
CREATE TABLE content_approvals (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    approvable_type VARCHAR(255) NOT NULL,
    approvable_id BIGINT UNSIGNED NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    requested_by BIGINT UNSIGNED NOT NULL,
    reviewed_by BIGINT UNSIGNED NULL,
    notes TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (requested_by) REFERENCES users(id),
    FOREIGN KEY (reviewed_by) REFERENCES users(id),
    INDEX idx_approvable (approvable_type, approvable_id),
    INDEX idx_status (status)
);
```

### Tabelas Social Media

#### instagram_accounts
```sql
CREATE TABLE instagram_accounts (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    company_id BIGINT UNSIGNED NOT NULL,
    instagram_user_id VARCHAR(100) NOT NULL,
    username VARCHAR(100) NOT NULL,
    access_token TEXT NOT NULL,                -- Criptografado
    token_expires_at TIMESTAMP,
    profile_picture VARCHAR(500),
    is_active BOOLEAN DEFAULT true,
    last_sync_at TIMESTAMP NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    UNIQUE KEY unique_instagram_user (instagram_user_id),
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    INDEX idx_company_active (company_id, is_active)
);
```

#### instagram_messages
```sql
CREATE TABLE instagram_messages (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    instagram_account_id BIGINT UNSIGNED NOT NULL,
    message_id VARCHAR(100) NOT NULL,
    conversation_id VARCHAR(100),
    sender_id VARCHAR(100),
    sender_name VARCHAR(255),
    message_text TEXT,
    message_type ENUM('text', 'image', 'video', 'story_reply', 'post_comment'),
    media_url VARCHAR(500),
    is_from_customer BOOLEAN DEFAULT true,
    lead_id BIGINT UNSIGNED NULL,
    received_at TIMESTAMP,
    created_at TIMESTAMP,
    
    UNIQUE KEY unique_message (instagram_account_id, message_id),
    FOREIGN KEY (instagram_account_id) REFERENCES instagram_accounts(id) ON DELETE CASCADE,
    FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE SET NULL,
    INDEX idx_account_conversation (instagram_account_id, conversation_id, received_at DESC),
    INDEX idx_lead (lead_id),
    INDEX idx_received (received_at DESC)
);
```

#### whatsapp_accounts
```sql
CREATE TABLE whatsapp_accounts (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    company_id BIGINT UNSIGNED NOT NULL,
    phone_number_id VARCHAR(100) NOT NULL,     -- WhatsApp Business Phone ID
    business_account_id VARCHAR(100) NOT NULL,
    phone_number VARCHAR(20) NOT NULL,
    display_name VARCHAR(255),
    access_token TEXT NOT NULL,                -- Criptografado
    webhook_verify_token VARCHAR(100),
    is_active BOOLEAN DEFAULT true,
    last_sync_at TIMESTAMP NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    UNIQUE KEY unique_phone_number_id (phone_number_id),
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    INDEX idx_company_active (company_id, is_active)
);
```

#### whatsapp_conversations
```sql
CREATE TABLE whatsapp_conversations (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    whatsapp_account_id BIGINT UNSIGNED NOT NULL,
    contact_phone VARCHAR(20) NOT NULL,
    contact_name VARCHAR(255),
    lead_id BIGINT UNSIGNED NULL,
    last_message_at TIMESTAMP,
    is_archived BOOLEAN DEFAULT false,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    UNIQUE KEY unique_account_contact (whatsapp_account_id, contact_phone),
    FOREIGN KEY (whatsapp_account_id) REFERENCES whatsapp_accounts(id) ON DELETE CASCADE,
    FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE SET NULL,
    INDEX idx_account_active (whatsapp_account_id, is_archived, last_message_at DESC),
    INDEX idx_lead (lead_id)
);
```

#### whatsapp_messages
```sql
CREATE TABLE whatsapp_messages (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    conversation_id BIGINT UNSIGNED NOT NULL,
    message_id VARCHAR(100),
    direction ENUM('inbound', 'outbound') NOT NULL,
    message_type ENUM('text', 'image', 'document', 'audio', 'video', 'template'),
    content TEXT,
    media_url VARCHAR(500),
    status ENUM('sent', 'delivered', 'read', 'failed') DEFAULT 'sent',
    sent_by BIGINT UNSIGNED NULL,
    ai_generated BOOLEAN DEFAULT false,
    delivered_at TIMESTAMP NULL,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP,
    
    UNIQUE KEY unique_message_id (message_id),
    FOREIGN KEY (conversation_id) REFERENCES whatsapp_conversations(id) ON DELETE CASCADE,
    FOREIGN KEY (sent_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_conversation_created (conversation_id, created_at DESC),
    INDEX idx_status (status),
    INDEX idx_ai_generated (ai_generated),
    FULLTEXT INDEX idx_search_content (content)
);
```

### Tabelas IA

#### ai_settings
```sql
CREATE TABLE ai_settings (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    company_id BIGINT UNSIGNED NOT NULL,
    provider ENUM('gemini', 'openai', 'claude') DEFAULT 'gemini',
    api_key TEXT,                              -- Criptografado (opcional, pode usar global)
    model VARCHAR(50) DEFAULT 'gemini-pro',
    temperature DECIMAL(3,2) DEFAULT 0.7,
    max_tokens INT DEFAULT 1000,
    auto_reply_enabled BOOLEAN DEFAULT false,
    auto_reply_delay INT DEFAULT 0,            -- Segundos
    business_context TEXT,                     -- Contexto do negócio
    tone ENUM('formal', 'casual', 'friendly', 'professional') DEFAULT 'professional',
    language VARCHAR(10) DEFAULT 'pt-BR',
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    UNIQUE KEY unique_company (company_id),
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
);
```

#### ai_prompt_templates
```sql
CREATE TABLE ai_prompt_templates (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    company_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    type ENUM('system', 'greeting', 'faq', 'sales', 'support', 'custom'),
    prompt_text TEXT NOT NULL,
    variables JSON,                            -- { "customer_name", "product_name" }
    is_active BOOLEAN DEFAULT true,
    usage_count INT DEFAULT 0,
    created_by BIGINT UNSIGNED,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_company_type (company_id, type, is_active)
);
```

#### ai_conversations
```sql
CREATE TABLE ai_conversations (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    company_id BIGINT UNSIGNED NOT NULL,
    lead_id BIGINT UNSIGNED NULL,
    context_type ENUM('whatsapp', 'instagram', 'email', 'chat', 'manual'),
    context_id BIGINT UNSIGNED,                -- ID da conversa origem
    status ENUM('active', 'completed', 'failed') DEFAULT 'active',
    total_tokens_used INT DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE SET NULL,
    INDEX idx_company_status (company_id, status),
    INDEX idx_lead (lead_id),
    INDEX idx_context (context_type, context_id)
);
```

#### ai_messages
```sql
CREATE TABLE ai_messages (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    conversation_id BIGINT UNSIGNED NOT NULL,
    role ENUM('system', 'user', 'assistant') NOT NULL,
    content TEXT NOT NULL,
    tokens_used INT DEFAULT 0,
    prompt_template_id BIGINT UNSIGNED NULL,
    metadata JSON,                             -- { model, temperature, latency_ms }
    created_at TIMESTAMP,
    
    FOREIGN KEY (conversation_id) REFERENCES ai_conversations(id) ON DELETE CASCADE,
    FOREIGN KEY (prompt_template_id) REFERENCES ai_prompt_templates(id) ON DELETE SET NULL,
    INDEX idx_conversation_role (conversation_id, created_at),
    INDEX idx_created (created_at DESC)
);
```

#### ai_feedback
```sql
CREATE TABLE ai_feedback (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    ai_message_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    rating TINYINT,                            -- 1-5 estrelas
    feedback_type ENUM('helpful', 'not_helpful', 'inappropriate', 'inaccurate'),
    notes TEXT,
    created_at TIMESTAMP,
    
    FOREIGN KEY (ai_message_id) REFERENCES ai_messages(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_message (ai_message_id),
    INDEX idx_rating (rating, created_at)
);
```

### Índices Estratégicos - Resumo

| Tabela | Índices Críticos | Razão |
|--------|------------------|-------|
| sites | (company_id, is_active), api_key | Multi-tenancy + auth rápida |
| pages/posts | (site_id, slug), (site_id, status), FULLTEXT(title, content) | URLs únicas, filtros, busca |
| content_versions | (versionable_type, versionable_id, created_at DESC) | Histórico de versões |
| instagram_messages | (account_id, conversation_id, received_at DESC) | Conversas agrupadas |
| whatsapp_messages | (conversation_id, created_at DESC), FULLTEXT(content) | Chat + busca |
| ai_conversations | (company_id, status), (lead_id) | Listar conversas ativas |

---

## 📁 Estrutura do Projeto

```
crm-api/
├── app/
│   ├── Models/
│   │   ├── CRM/                    # Grupo CRM (existentes refatorados)
│   │   │   ├── Lead.php
│   │   │   ├── Pipeline.php
│   │   │   ├── PipelineStage.php
│   │   │   ├── Activity.php
│   │   │   ├── Task.php
│   │   │   ├── Product.php
│   │   │   ├── Proposal.php
│   │   │   └── ProposalItem.php
│   │   ├── CMS/                    # Grupo CMS (novos)
│   │   │   ├── Site.php
│   │   │   ├── Page.php
│   │   │   ├── Post.php
│   │   │   ├── PostCategory.php
│   │   │   ├── Portfolio.php
│   │   │   ├── Faq.php
│   │   │   ├── Testimonial.php
│   │   │   ├── TeamMember.php
│   │   │   ├── Form.php
│   │   │   ├── Banner.php
│   │   │   ├── Menu.php
│   │   │   ├── MenuItem.php
│   │   │   ├── ContentVersion.php
│   │   │   └── ContentApproval.php
│   │   ├── Social/                 # Grupo Integrações Sociais
│   │   │   ├── InstagramAccount.php
│   │   │   ├── InstagramMessage.php
│   │   │   ├── WhatsAppAccount.php
│   │   │   ├── WhatsAppConversation.php
│   │   │   └── WhatsAppMessage.php
│   │   └── AI/                     # Grupo IA
│   │       ├── AISettings.php
│   │       ├── AIPromptTemplate.php
│   │       ├── AIConversation.php
│   │       ├── AIMessage.php
│   │       └── AIFeedback.php
│   │
│   ├── Services/                   # Lógica de Negócio
│   │   ├── CRM/
│   │   │   ├── LeadService.php
│   │   │   ├── PipelineService.php
│   │   │   └── ActivityService.php
│   │   ├── CMS/
│   │   │   ├── SiteService.php
│   │   │   ├── ContentService.php
│   │   │   ├── VersioningService.php
│   │   │   └── PublishingService.php
│   │   ├── Social/
│   │   │   ├── InstagramService.php
│   │   │   ├── WhatsAppService.php
│   │   │   └── SocialMediaFactory.php
│   │   └── AI/
│   │       ├── GeminiService.php
│   │       ├── ResponseGeneratorService.php
│   │       └── ConversationService.php
│   │
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── API/
│   │   │       ├── CRM/
│   │   │       │   ├── LeadController.php
│   │   │       │   ├── PipelineController.php
│   │   │       │   └── ActivityController.php
│   │   │       ├── CMS/
│   │   │       │   ├── SiteController.php
│   │   │       │   ├── PageController.php
│   │   │       │   ├── PostController.php
│   │   │       │   ├── MenuController.php
│   │   │       │   └── ContentPreviewController.php
│   │   │       ├── Social/
│   │   │       │   ├── InstagramController.php
│   │   │       │   ├── WhatsAppController.php
│   │   │       │   ├── InstagramWebhookController.php
│   │   │       │   └── WhatsAppWebhookController.php
│   │   │       └── AI/
│   │   │           ├── AISettingsController.php
│   │   │           ├── AIConversationController.php
│   │   │           └── AIResponseController.php
│   │   ├── Requests/               # Form Requests por contexto
│   │   │   ├── CRM/
│   │   │   ├── CMS/
│   │   │   ├── Social/
│   │   │   └── AI/
│   │   ├── Resources/              # API Resources
│   │   │   ├── CRM/
│   │   │   ├── CMS/
│   │   │   ├── Social/
│   │   │   └── AI/
│   │   └── Middleware/
│   │
│   ├── Jobs/                       # Queue Jobs
│   │   ├── CMS/
│   │   │   ├── PublishContentJob.php
│   │   │   ├── GenerateSitemapJob.php
│   │   │   └── OptimizeImagesJob.php
│   │   ├── Social/
│   │   │   ├── SyncInstagramMessagesJob.php
│   │   │   ├── SyncInstagramPostsJob.php
│   │   │   ├── SendWhatsAppMessageJob.php
│   │   │   └── ProcessIncomingMessageJob.php
│   │   ├── AI/
│   │   │   ├── GenerateAIResponseJob.php
│   │   │   ├── AnalyzeConversationSentimentJob.php
│   │   │   └── TrainCustomPromptsJob.php
│   │   └── Notifications/
│   │       ├── SendEmailNotificationJob.php
│   │       └── SendWebhookNotificationJob.php
│   │
│   ├── Enums/                      # PHP 8.2 Enums
│   │   ├── ContentStatus.php
│   │   ├── ContentType.php
│   │   ├── MessageStatus.php
│   │   └── AIProvider.php
│   │
│   └── Contracts/                  # Interfaces
│       ├── MessageServiceInterface.php
│       └── AIProviderInterface.php
│
├── resources/
│   └── js/
│       ├── app.js
│       ├── Pages/
│       │   ├── Dashboard.vue
│       │   ├── CRM/
│       │   ├── CMS/
│       │   ├── Social/
│       │   └── AI/
│       ├── Components/
│       │   ├── Layout/
│       │   └── Shared/
│       └── Composables/
│
├── routes/
│   ├── api.php                     # API routes
│   ├── web.php                     # Inertia routes
│   └── console.php
│
├── tests/
│   ├── Feature/
│   │   ├── CRM/
│   │   ├── CMS/
│   │   ├── Social/
│   │   └── AI/
│   └── Unit/
│       ├── Services/
│       └── Models/
│
└── docs/
    ├── API_ENDPOINTS.md
    ├── AUTHENTICATION.md
    ├── CMS.md
    ├── INTEGRATIONS.md
    ├── AI.md
    └── CRM_EVOLUTION_DESIGN.md  # Este arquivo
```

---

## 🔧 Services e Controllers

### Arquitetura de Services (SRP)

Cada Service tem **uma responsabilidade específica** e bem definida:

#### ContentService (CMS)
**Responsabilidade:** CRUD de conteúdo (pages, posts, etc.)

```php
namespace App\Services\CMS;

class ContentService
{
    public function __construct(
        private VersioningService $versioningService
    ) {}

    public function listPages(int $siteId, array $filters = []): LengthAwarePaginator
    public function createPage(int $siteId, array $data): Page
    public function updatePage(int $id, array $data): Page
    public function deletePage(int $id): bool
    public function generatePreviewData(string $type, int $id): array
    private function ensureUniqueSlug(int $siteId, string $slug, string $type, ?int $excludeId = null): string
}
```

#### PublishingService (CMS)
**Responsabilidade:** Workflow de aprovação e publicação

```php
namespace App\Services\CMS;

class PublishingService
{
    public function publishContent(string $type, int $id): mixed
    public function requestApproval(mixed $model): mixed
    public function approveContent(int $approvalId): mixed
    public function rejectContent(int $approvalId, string $reason): mixed
    private function needsApproval(mixed $model): bool
    private function publish(mixed $model): mixed
}
```

#### VersioningService (CMS)
**Responsabilidade:** Versionamento de conteúdo

```php
namespace App\Services\CMS;

class VersioningService
{
    public function createVersion(string $type, int $id, array $data, ?string $notes = null): ContentVersion
    public function getVersionHistory(string $type, int $id): Collection
    public function rollback(string $type, int $id, int $versionId): mixed
    public function compareVersions(int $versionId1, int $versionId2): array
}
```

#### ResponseGeneratorService (IA)
**Responsabilidade:** Gerar respostas com IA

```php
namespace App\Services\AI;

class ResponseGeneratorService
{
    public function __construct(
        private AIProviderInterface $aiProvider,
        private ConversationService $conversationService
    ) {}

    public function generateResponse(
        int $companyId,
        string $userMessage,
        string $contextType,
        ?int $leadId = null
    ): string
    
    private function buildContextualPrompt(AISettings $settings, string $message, AIConversation $conversation): string
}
```

### Controllers Magros (Orquestração)

**Princípio:** Controllers apenas delegam para Services

```php
namespace App\Http\Controllers\API\CMS;

use App\Http\Controllers\Controller;
use App\Http\Requests\CMS\StorePageRequest;
use App\Http\Resources\CMS\PageResource;
use App\Services\CMS\ContentService;
use App\Services\CMS\PublishingService;

class PageController extends Controller
{
    public function __construct(
        private ContentService $contentService,
        private PublishingService $publishingService
    ) {}

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Page::class);
        
        $pages = $this->contentService->listPages(
            siteId: request('site_id'),
            filters: request()->only(['status', 'search'])
        );

        return PageResource::collection($pages)->response();
    }

    public function store(StorePageRequest $request): JsonResponse
    {
        $page = $this->contentService->createPage(
            siteId: $request->site_id,
            data: $request->validated()
        );

        return PageResource::make($page)
            ->response()
            ->setStatusCode(201);
    }

    public function publish(int $id): JsonResponse
    {
        $this->authorize('publish', Page::class);
        
        $page = $this->publishingService->publishContent('page', $id);

        return response()->json([
            'message' => 'Page published successfully',
            'data' => PageResource::make($page)
        ]);
    }
}
```

**Benefícios:**
- ✅ Controllers fáceis de entender
- ✅ Lógica testável (services isolados)
- ✅ Reutilização (services usados em múltiplos lugares)
- ✅ SRP respeitado

---

## ⚡ Jobs e Queues

### Configuração de Queues

**config/queue.php:**
```php
'connections' => [
    'redis' => [
        'driver' => 'redis',
        'connection' => 'default',
        'queue' => env('REDIS_QUEUE', 'default'),
        'retry_after' => 90,
    ],
],

// Múltiplas filas por prioridade
'queues' => [
    'high' => 'high',        // Webhooks, mensagens urgentes
    'default' => 'default',  // Operações normais
    'low' => 'low',          // Sincronizações, relatórios
]
```

### Estratégia de Retry

| Job | Tries | Timeout | Backoff | Fila |
|-----|-------|---------|---------|------|
| ProcessIncomingMessageJob | 3 | 60s | 10s | high |
| GenerateAIResponseJob | 2 | 120s | - | default |
| SendWhatsAppMessageJob | 3 | 30s | [10,30,60]s | high |
| SyncInstagramMessagesJob | 1 | 300s | - | low |

### Fluxo de Mensagem Completo

```
┌─────────────────────────────────────────────────────────────┐
│ 1. Webhook recebe mensagem WhatsApp/Instagram              │
└────────────────┬────────────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────────────┐
│ 2. ProcessIncomingMessageJob (queue: high)                 │
│    - Salva mensagem no banco                                │
│    - Busca ou cria lead                                     │
│    - Registra atividade no CRM                              │
│    - Verifica se auto_reply está ativo                      │
└────────────────┬────────────────────────────────────────────┘
                 │
                 │ Se auto_reply = true
                 ▼
┌─────────────────────────────────────────────────────────────┐
│ 3. GenerateAIResponseJob (queue: default, delay: Xs)       │
│    - Busca configurações IA da empresa                      │
│    - Busca histórico da conversa                            │
│    - Gera resposta com Gemini                               │
│    - Salva no histórico de IA                               │
└────────────────┬────────────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────────────┐
│ 4. SendWhatsAppMessageJob (queue: high)                    │
│    - Envia via WhatsApp Business API                        │
│    - Salva mensagem enviada (ai_generated = true)           │
│    - Atualiza timestamp da conversa                         │
│    - Registra atividade no lead                             │
└─────────────────────────────────────────────────────────────┘
```

### Schedule (Tarefas Periódicas)

**app/Console/Kernel.php:**
```php
protected function schedule(Schedule $schedule): void
{
    // Sincronizar Instagram a cada 5 minutos
    $schedule->call(function() {
        $accounts = InstagramAccount::where('is_active', true)->get();
        foreach ($accounts as $account) {
            SyncInstagramMessagesJob::dispatch($account->id);
        }
    })->everyFiveMinutes()->name('sync-instagram');

    // Limpar versões antigas (manter últimas 10)
    $schedule->command('cms:cleanup-versions')->daily()->at('02:00');

    // Gerar sitemap dos sites
    $schedule->command('cms:generate-sitemaps')->daily()->at('03:00');

    // Relatórios de uso de IA
    $schedule->command('ai:usage-report')->weekly()->mondays()->at('09:00');
}
```

### Rodar Workers

```bash
# Development (single worker)
php artisan queue:work redis --queue=high,default,low --tries=3 --timeout=90

# Production (supervisor)
# /etc/supervisor/conf.d/crm-worker.conf
[program:crm-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/crm-api/artisan queue:work redis --queue=high,default,low --tries=3 --timeout=90
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=3
redirect_stderr=true
stdout_logfile=/var/www/crm-api/storage/logs/worker.log
```

---

## 🔌 Integrações

### Instagram (Meta Graph API)

#### Setup Manual
1. Criar app em https://developers.facebook.com
2. Adicionar produto "Instagram Basic Display"
3. Configurar OAuth redirect: `https://seu-crm.com/api/social/instagram/callback`
4. Obter App ID e App Secret
5. Configurar Webhooks para receber mensagens

#### Endpoints

```php
// routes/api.php
Route::prefix('social/instagram')->middleware('auth:sanctum')->group(function() {
    // Conectar conta
    Route::get('connect', [InstagramController::class, 'redirectToInstagram']);
    Route::get('callback', [InstagramController::class, 'handleCallback']);
    
    // Listar mensagens
    Route::get('accounts', [InstagramController::class, 'accounts']);
    Route::get('accounts/{id}/messages', [InstagramController::class, 'messages']);
    
    // Sincronizar manualmente
    Route::post('accounts/{id}/sync', [InstagramController::class, 'sync']);
});

// Webhook (sem auth)
Route::post('webhooks/instagram', [InstagramWebhookController::class, 'handle']);
Route::get('webhooks/instagram', [InstagramWebhookController::class, 'verify']);
```

#### Exemplo de Chamada API

```php
// InstagramService.php
public function fetchRecentMessages(InstagramAccount $account): array
{
    $response = Http::get("https://graph.instagram.com/v18.0/{$account->instagram_user_id}/conversations", [
        'access_token' => Crypt::decryptString($account->access_token),
        'fields' => 'id,messages{id,from,message,created_time}',
        'limit' => 50
    ]);

    if ($response->failed()) {
        Log::error('Instagram API error', [
            'account_id' => $account->id,
            'error' => $response->json()
        ]);
        throw new \Exception('Failed to fetch Instagram messages');
    }

    return $response->json('data', []);
}
```

### WhatsApp Business API (Meta)

#### Setup Manual
1. Criar WhatsApp Business Account em https://business.facebook.com
2. Adicionar número de telefone
3. Obter Phone Number ID e Business Account ID
4. Gerar Access Token permanente
5. Configurar Webhook: `https://seu-crm.com/api/webhooks/whatsapp`
6. Definir Verify Token (string aleatória)

#### .env
```env
WHATSAPP_BUSINESS_ACCOUNT_ID=your_business_account_id
WHATSAPP_PHONE_NUMBER_ID=your_phone_number_id
WHATSAPP_ACCESS_TOKEN=your_permanent_token
WHATSAPP_WEBHOOK_VERIFY_TOKEN=your_secret_verify_token
```

#### Endpoints

```php
// routes/api.php
Route::prefix('social/whatsapp')->middleware('auth:sanctum')->group(function() {
    // Gerenciar contas
    Route::apiResource('accounts', WhatsAppAccountController::class);
    
    // Conversas
    Route::get('conversations', [WhatsAppController::class, 'conversations']);
    Route::get('conversations/{id}', [WhatsAppController::class, 'conversation']);
    Route::get('conversations/{id}/messages', [WhatsAppController::class, 'messages']);
    
    // Enviar mensagem
    Route::post('conversations/{id}/send', [WhatsAppController::class, 'sendMessage']);
});

// Webhooks
Route::post('webhooks/whatsapp', [WhatsAppWebhookController::class, 'handle']);
Route::get('webhooks/whatsapp', [WhatsAppWebhookController::class, 'verify']);
```

#### Webhook Verification

```php
// WhatsAppWebhookController.php
public function verify(Request $request)
{
    $mode = $request->query('hub.mode');
    $token = $request->query('hub.verify_token');
    $challenge = $request->query('hub.challenge');
    
    if ($mode === 'subscribe' && $token === config('services.whatsapp.verify_token')) {
        return response($challenge, 200);
    }
    
    return response('Forbidden', 403);
}

public function handle(Request $request)
{
    // Validar signature
    $signature = $request->header('X-Hub-Signature-256');
    $payload = $request->getContent();
    
    if (!$this->validateSignature($payload, $signature)) {
        return response('Invalid signature', 403);
    }

    // Processar webhook
    $data = $request->json()->all();
    
    foreach ($data['entry'] ?? [] as $entry) {
        foreach ($entry['changes'] ?? [] as $change) {
            if ($change['field'] === 'messages') {
                ProcessIncomingMessageJob::dispatch(
                    platform: 'whatsapp',
                    accountId: $this->getAccountIdFromPhoneNumber($change['value']['metadata']['phone_number_id']),
                    messageData: $change['value']['messages'][0]
                );
            }
        }
    }

    return response()->json(['status' => 'ok']);
}
```

#### Enviar Mensagem

```php
// WhatsAppService.php
public function sendMessage(int $accountId, string $recipientPhone, string $message): string
{
    $account = WhatsAppAccount::findOrFail($accountId);
    
    $response = Http::withToken(Crypt::decryptString($account->access_token))
        ->post("https://graph.facebook.com/v18.0/{$account->phone_number_id}/messages", [
            'messaging_product' => 'whatsapp',
            'to' => $recipientPhone,
            'type' => 'text',
            'text' => ['body' => $message]
        ]);

    if ($response->failed()) {
        throw new \Exception('Failed to send WhatsApp message: ' . $response->body());
    }

    return $response->json('messages.0.id');
}
```

---

## 🤖 Sistema de IA

### Google Gemini Integration

#### Setup
```bash
# Obter API Key em https://ai.google.dev
# .env
GEMINI_API_KEY=your_gemini_api_key
GEMINI_MODEL=gemini-1.5-flash
```

#### AIProviderInterface (Strategy Pattern)

```php
namespace App\Contracts;

interface AIProviderInterface
{
    /**
     * Gerar resposta única
     */
    public function generate(string $prompt, array $context = [], array $options = []): string;
    
    /**
     * Chat com histórico de mensagens
     */
    public function chat(array $messages, array $options = []): string;
    
    /**
     * Contar tokens de um texto
     */
    public function countTokens(string $text): int;
}
```

#### GeminiService Implementation

```php
namespace App\Services\AI;

use App\Contracts\AIProviderInterface;
use Illuminate\Support\Facades\Http;

class GeminiService implements AIProviderInterface
{
    public function generate(string $prompt, array $context = [], array $options = []): string
    {
        $model = $options['model'] ?? config('services.gemini.model', 'gemini-1.5-flash');
        $apiKey = config('services.gemini.api_key');
        
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}", [
            'contents' => [
                ['parts' => [['text' => $this->buildPrompt($prompt, $context)]]]
            ],
            'generationConfig' => [
                'temperature' => $options['temperature'] ?? 0.7,
                'maxOutputTokens' => $options['max_tokens'] ?? 1000,
            ]
        ]);

        if ($response->failed()) {
            throw new \Exception('Gemini API error: ' . $response->body());
        }

        return $response->json('candidates.0.content.parts.0.text');
    }

    public function chat(array $messages, array $options = []): string
    {
        $model = $options['model'] ?? config('services.gemini.model');
        $apiKey = config('services.gemini.api_key');
        
        // Converter formato de mensagens para Gemini
        $contents = collect($messages)->map(function($msg) {
            return [
                'role' => $msg['role'] === 'assistant' ? 'model' : 'user',
                'parts' => [['text' => $msg['content']]]
            ];
        })->toArray();

        $response = Http::post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}", [
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => $options['temperature'] ?? 0.7,
                'maxOutputTokens' => $options['max_tokens'] ?? 1000,
            ]
        ]);

        return $response->json('candidates.0.content.parts.0.text');
    }

    public function countTokens(string $text): int
    {
        // Estimativa: ~4 caracteres = 1 token
        return (int) ceil(strlen($text) / 4);
    }

    private function buildPrompt(string $prompt, array $context): string
    {
        if (empty($context)) {
            return $prompt;
        }

        $contextText = "Contexto:\n";
        foreach ($context as $key => $value) {
            $contextText .= "- {$key}: {$value}\n";
        }

        return $contextText . "\n" . $prompt;
    }
}
```

#### ResponseGeneratorService (Lógica de Negócio)

```php
namespace App\Services\AI;

use App\Contracts\AIProviderInterface;
use App\Models\AI\{AISettings, AIConversation, AIMessage};

class ResponseGeneratorService
{
    public function __construct(
        private AIProviderInterface $aiProvider,
        private ConversationService $conversationService
    ) {}

    public function generateResponse(
        int $companyId,
        string $userMessage,
        string $contextType,
        ?int $leadId = null
    ): string {
        // 1. Buscar configurações da empresa
        $settings = AISettings::where('company_id', $companyId)->firstOrFail();
        
        // 2. Buscar ou criar conversa
        $conversation = $this->conversationService->getOrCreate(
            companyId: $companyId,
            contextType: $contextType,
            leadId: $leadId
        );
        
        // 3. Buscar histórico recente (últimas 5 mensagens)
        $history = $this->conversationService->getRecentMessages($conversation->id, 5);
        
        // 4. Construir prompt contextual
        $messages = $this->buildMessages($settings, $userMessage, $history);
        
        // 5. Gerar resposta
        $startTime = microtime(true);
        
        $response = $this->aiProvider->chat($messages, [
            'temperature' => $settings->temperature,
            'max_tokens' => $settings->max_tokens,
            'model' => $settings->model
        ]);
        
        $latencyMs = (int) ((microtime(true) - $startTime) * 1000);
        $tokensUsed = $this->aiProvider->countTokens($userMessage . $response);
        
        // 6. Salvar no histórico
        $this->conversationService->saveMessage($conversation->id, 'user', $userMessage);
        $this->conversationService->saveMessage(
            conversationId: $conversation->id,
            role: 'assistant',
            content: $response,
            tokensUsed: $tokensUsed,
            metadata: [
                'model' => $settings->model,
                'temperature' => $settings->temperature,
                'latency_ms' => $latencyMs
            ]
        );
        
        // 7. Atualizar contador de tokens
        $conversation->increment('total_tokens_used', $tokensUsed);
        
        return $response;
    }

    private function buildMessages(AISettings $settings, string $userMessage, $history): array
    {
        $messages = [];
        
        // System prompt
        $systemPrompt = $this->buildSystemPrompt($settings);
        $messages[] = [
            'role' => 'system',
            'content' => $systemPrompt
        ];
        
        // Histórico
        foreach ($history as $msg) {
            $messages[] = [
                'role' => $msg->role,
                'content' => $msg->content
            ];
        }
        
        // Mensagem atual
        $messages[] = [
            'role' => 'user',
            'content' => $userMessage
        ];
        
        return $messages;
    }

    private function buildSystemPrompt(AISettings $settings): string
    {
        $company = $settings->company;
        
        $prompt = "Você é um assistente virtual da empresa {$company->name}.\n\n";
        
        if ($settings->business_context) {
            $prompt .= "Contexto do negócio:\n{$settings->business_context}\n\n";
        }
        
        $prompt .= "Tom de voz: {$settings->tone}\n";
        $prompt .= "Idioma: {$settings->language}\n\n";
        
        $prompt .= "Instruções:\n";
        $prompt .= "- Seja prestativo e responda de forma clara\n";
        $prompt .= "- Se não souber algo, seja honesto\n";
        $prompt .= "- Mantenha respostas concisas (máximo 3 parágrafos)\n";
        $prompt .= "- Use o contexto da conversa anterior\n";
        
        return $prompt;
    }
}
```

#### AIFactory (Trocar Provider Facilmente)

```php
namespace App\Services\AI;

use App\Contracts\AIProviderInterface;

class AIFactory
{
    public static function make(?string $provider = null): AIProviderInterface
    {
        $provider = $provider ?? config('services.ai.default_provider', 'gemini');
        
        return match($provider) {
            'gemini' => app(GeminiService::class),
            'openai' => app(OpenAIService::class),  // Implementar no futuro
            'claude' => app(ClaudeService::class),  // Implementar no futuro
            default => throw new \InvalidArgumentException("AI provider '{$provider}' not supported")
        };
    }
}
```

### Prompt Engineering - Exemplos

#### Prompt de Saudação
```
Você está recebendo uma mensagem inicial de um cliente.
Responda de forma amigável, se apresente brevemente e pergunte como pode ajudar.
Não seja prolixo.
```

#### Prompt de Qualificação de Lead
```
Você está conversando com um potencial cliente.
Seu objetivo é entender:
- O que ele precisa
- Qual o prazo desejado
- Qual o orçamento aproximado

Faça perguntas de forma natural, uma de cada vez.
Não seja invasivo.
```

#### Prompt de FAQ
```
O cliente fez uma pergunta. Consulte a base de conhecimento abaixo:

{faq_knowledge_base}

Se a resposta estiver na base, forneça-a de forma clara.
Se não estiver, seja honesto e ofereça transferir para um humano.
```

---

## 🎨 Frontend (Vue 3 + Inertia + PrimeVue)

### Setup Básico

**resources/js/app.js:**
```javascript
import './bootstrap';
import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import PrimeVue from 'primevue/config';
import ToastService from 'primevue/toastservice';
import ConfirmationService from 'primevue/confirmationservice';

// PrimeVue CSS
import 'primevue/resources/themes/lara-light-blue/theme.css';
import 'primevue/resources/primevue.min.css';
import 'primeicons/primeicons.css';

createInertiaApp({
    resolve: name => {
        const pages = import.meta.glob('./Pages/**/*.vue', { eager: true });
        return pages[`./Pages/${name}.vue`];
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(PrimeVue, { ripple: true })
            .use(ToastService)
            .use(ConfirmationService)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
```

### Layout Base

**resources/js/Components/Layout/AppLayout.vue:**
```vue
<template>
    <div class="layout-wrapper">
        <Sidebar :visible="sidebarVisible" @update:visible="sidebarVisible = $event" />
        
        <div class="layout-main-container">
            <Navbar @toggle-sidebar="sidebarVisible = !sidebarVisible" />
            
            <div class="layout-main">
                <Breadcrumb :home="home" :model="breadcrumbs" class="mb-4" />
                
                <Toast />
                <ConfirmDialog />
                
                <slot />
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Sidebar from './Sidebar.vue';
import Navbar from './Navbar.vue';
import Breadcrumb from 'primevue/breadcrumb';
import Toast from 'primevue/toast';
import ConfirmDialog from 'primevue/confirmdialog';

const sidebarVisible = ref(true);

const page = usePage();
const home = { icon: 'pi pi-home', to: '/dashboard' };
const breadcrumbs = computed(() => page.props.breadcrumbs || []);
</script>
```

### Exemplo de Página - Lista de Pages (CMS)

**resources/js/Pages/CMS/Pages/Index.vue:**
```vue
<template>
    <AppLayout>
        <div class="card">
            <div class="flex justify-content-between align-items-center mb-4">
                <h1>Páginas</h1>
                <Button 
                    label="Nova Página" 
                    icon="pi pi-plus" 
                    @click="create"
                />
            </div>

            <DataTable 
                :value="pages.data" 
                :loading="loading"
                paginator 
                :rows="15"
                :totalRecords="pages.total"
                :lazy="true"
                @page="onPage"
                filterDisplay="row"
                v-model:filters="filters"
                @filter="onFilter"
            >
                <Column field="title" header="Título" sortable>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText 
                            v-model="filterModel.value" 
                            @input="filterCallback()" 
                            placeholder="Buscar por título"
                        />
                    </template>
                </Column>
                
                <Column field="slug" header="Slug" />
                
                <Column field="status" header="Status">
                    <template #body="{ data }">
                        <Tag 
                            :value="data.status" 
                            :severity="getStatusSeverity(data.status)"
                        />
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <Dropdown 
                            v-model="filterModel.value" 
                            @change="filterCallback()"
                            :options="statusOptions" 
                            placeholder="Todos"
                            showClear
                        />
                    </template>
                </Column>
                
                <Column field="published_at" header="Publicado em" />
                
                <Column header="Ações">
                    <template #body="{ data }">
                        <Button 
                            icon="pi pi-pencil" 
                            class="p-button-rounded p-button-text"
                            @click="edit(data.id)"
                        />
                        <Button 
                            icon="pi pi-eye" 
                            class="p-button-rounded p-button-text"
                            @click="preview(data.id)"
                        />
                        <Button 
                            v-if="data.status === 'draft'"
                            icon="pi pi-send" 
                            class="p-button-rounded p-button-text p-button-success"
                            @click="publish(data.id)"
                        />
                        <Button 
                            icon="pi pi-trash" 
                            class="p-button-rounded p-button-text p-button-danger"
                            @click="confirmDelete(data.id)"
                        />
                    </template>
                </Column>
            </DataTable>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import { useConfirm } from 'primevue/useconfirm';
import { useToast } from 'primevue/usetoast';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Dropdown from 'primevue/dropdown';
import Tag from 'primevue/tag';

const props = defineProps({
    pages: Object,
    filters: Object
});

const loading = ref(false);
const confirm = useConfirm();
const toast = useToast();

const statusOptions = ['draft', 'pending', 'published'];

const filters = reactive({
    title: { value: props.filters?.search || null },
    status: { value: props.filters?.status || null }
});

function getStatusSeverity(status) {
    return {
        draft: 'warning',
        pending: 'info',
        published: 'success'
    }[status];
}

function create() {
    router.visit('/cms/pages/create');
}

function edit(id) {
    router.visit(`/cms/pages/${id}/edit`);
}

function preview(id) {
    window.open(`/api/cms/pages/${id}/preview`, '_blank');
}

function publish(id) {
    router.post(`/cms/pages/${id}/publish`, {}, {
        onSuccess: () => {
            toast.add({
                severity: 'success',
                summary: 'Sucesso',
                detail: 'Página publicada com sucesso',
                life: 3000
            });
        }
    });
}

function confirmDelete(id) {
    confirm.require({
        message: 'Tem certeza que deseja excluir esta página?',
        header: 'Confirmar exclusão',
        icon: 'pi pi-exclamation-triangle',
        accept: () => {
            router.delete(`/cms/pages/${id}`, {
                onSuccess: () => {
                    toast.add({
                        severity: 'success',
                        summary: 'Sucesso',
                        detail: 'Página excluída com sucesso',
                        life: 3000
                    });
                }
            });
        }
    });
}

function onPage(event) {
    router.get('/cms/pages', {
        page: event.page + 1,
        ...filters
    }, {
        preserveState: true,
        preserveScroll: true
    });
}

function onFilter() {
    router.get('/cms/pages', filters, {
        preserveState: true,
        preserveScroll: true
    });
}
</script>
```

### Componentes Reutilizáveis

#### ChatInterface.vue (WhatsApp/Instagram)
```vue
<template>
    <div class="chat-container">
        <div class="chat-messages" ref="messagesContainer">
            <div 
                v-for="message in messages" 
                :key="message.id"
                :class="['message', message.direction]"
            >
                <div class="message-content">
                    <p>{{ message.content }}</p>
                    <span class="message-time">{{ formatTime(message.created_at) }}</span>
                    <i v-if="message.ai_generated" class="pi pi-bolt" title="Gerado por IA" />
                </div>
            </div>
        </div>
        
        <div class="chat-input">
            <InputText 
                v-model="newMessage" 
                placeholder="Digite sua mensagem..."
                @keyup.enter="sendMessage"
            />
            <Button 
                icon="pi pi-send" 
                @click="sendMessage"
                :disabled="!newMessage.trim()"
            />
            <Button 
                icon="pi pi-bolt" 
                class="p-button-outlined"
                @click="generateAIResponse"
                title="Gerar resposta com IA"
            />
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue';
import { router } from '@inertiajs/vue3';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';

const props = defineProps({
    conversationId: Number,
    messages: Array
});

const newMessage = ref('');
const messagesContainer = ref(null);

function sendMessage() {
    if (!newMessage.value.trim()) return;
    
    router.post(`/social/whatsapp/conversations/${props.conversationId}/send`, {
        message: newMessage.value
    }, {
        preserveScroll: true,
        onSuccess: () => {
            newMessage.value = '';
            scrollToBottom();
        }
    });
}

function generateAIResponse() {
    router.post(`/ai/generate-response`, {
        conversation_id: props.conversationId
    }, {
        preserveScroll: true,
        onSuccess: () => {
            scrollToBottom();
        }
    });
}

function formatTime(datetime) {
    return new Date(datetime).toLocaleTimeString('pt-BR', {
        hour: '2-digit',
        minute: '2-digit'
    });
}

function scrollToBottom() {
    nextTick(() => {
        messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
    });
}

onMounted(() => {
    scrollToBottom();
});
</script>

<style scoped>
.chat-container {
    display: flex;
    flex-direction: column;
    height: 600px;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
}

.chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 1rem;
}

.message {
    display: flex;
    margin-bottom: 1rem;
}

.message.inbound {
    justify-content: flex-start;
}

.message.outbound {
    justify-content: flex-end;
}

.message-content {
    max-width: 70%;
    padding: 0.75rem 1rem;
    border-radius: 12px;
    background: #f0f0f0;
}

.message.outbound .message-content {
    background: #007bff;
    color: white;
}

.message-time {
    font-size: 0.75rem;
    opacity: 0.7;
    margin-left: 0.5rem;
}

.chat-input {
    display: flex;
    gap: 0.5rem;
    padding: 1rem;
    border-top: 1px solid #e0e0e0;
}
</style>
```

---

## 🗓️ Roadmap de Implementação

### FASE 0: Preparação e Refatoração Base (Semana 1)


#### ✅ Checklist Completo

- [x] **Ferramentas de Qualidade**
    - [x] Instalar Pest: `composer require --dev pestphp/pest`
    - [x] Instalar Larastan: `composer require --dev larastan/larastan`
    - [x] Instalar Pint: `composer require --dev laravel/pint`
    - [x] Configurar PHPStan (nível 5+)  
    - [x] Git hooks para Pint  <!-- ✅ Implementado: pre-commit hook -->

- [x] **Estrutura de Pastas**
    - [x] Criar `app/Services/{CRM,CMS,Social,AI}`
    - [x] Criar `app/Models/{CRM,CMS,Social,AI}`
    - [x] Criar `app/Jobs/{CRM,CMS,Social,AI,Notifications}`
    - [x] Criar `app/Enums`, `app/Contracts`
    - [x] Criar `app/Http/Controllers/API/{CRM,CMS,Social,AI}`
    - [x] Criar `app/Http/Resources/{CRM,CMS,Social,AI}`
    - [x] Criar `app/Http/Requests/{CRM,CMS,Social,AI}`

- [ ] **Migrar Models Existentes** <!-- TODO: FALTA MIGRAR MODELS CRM/SOCIAL PARA SUBPASTAS -->
    - [ ] Mover para `app/Models/CRM/`
    - [ ] Atualizar namespaces
    - [ ] Atualizar imports em controllers/services
    - [ ] Rodar testes: `php artisan test`

- [x] **Criar Enums Básicos**
    - [x] ContentStatus.php
    - [x] ContentType.php
    - [x] MessageStatus.php
    - [x] AIProvider.php

**Tempo estimado:** 3-5 horas

---

### FASE 1: CMS Headless - Base (Semana 2-3)


#### ✅ Checklist Completo

- [x] **Migrations CMS Base**
    - [x] create_sites_table
    - [x] create_pages_table
    - [x] create_posts_table
    - [x] create_post_categories_table
    - [x] create_content_versions_table
    - [x] create_menus_table
    - [x] create_menu_items_table
    - [x] create_content_approvals_table
    - [x] Executar: `php artisan migrate`

- [x] **Models e Relationships**
    - [x] Site, Page, Post, PostCategory
    - [x] ContentVersion, Menu, MenuItem
    - [x] Relationships definidos
    - [x] Scopes (published, draft, etc.)
    - [x] Casts (JSON, dates, enums)
    - [x] Soft deletes habilitados

- [x] **Services CMS**
    - [x] SiteService (CRUD de sites, generate API key)
    - [x] ContentService (CRUD páginas/posts, unique slug)
    - [x] VersioningService (create, getHistory, rollback)
    - [x] PublishingService (publish, workflow aprovação)

- [x] **Controllers e Routes**
    - [x] SiteController (API REST)
    - [x] PageController (API REST + preview)
    - [x] PostController (API REST)
    - [x] MenuController (API REST)
    - [x] Form Requests (validação)
    - [x] API Resources (transformação)
    - [x] Policies (autorização) <!-- ✅ 4 Policies: Site, Page, Post, ContentApproval -->
    - [x] Routes em api.php

- [x] **Testes** <!-- ✅ Implementados: 45 testes totais -->
    - [x] Feature: SiteTest (11), PageTest (11), ContentApprovalTest (7)
    - [x] Unit: ContentServiceTest (9), PublishingServiceTest (7)
    - [x] Cobertura ~60%+
    - [x] `php artisan test` passando (pendente: factories)

**Tempo estimado:** 10-15 horas

---

### FASE 2: CMS Completo (Semana 4-5)


#### ✅ Checklist Completo

- [x] **Tipos de Conteúdo Restantes**
    - [x] Migration + Model: Portfolio
    - [x] Migration + Model: Faq
    - [x] Migration + Model: Testimonial
    - [x] Migration + Model: TeamMember
    - [x] Migration + Model: Form
    - [x] Migration + Model: Banner
    - [x] Controllers e Resources para cada tipo
    - [x] Routes configuradas

- [x] **Sistema de Aprovação**
    - [x] ContentApproval model
    - [x] PublishingService: requestApproval, approve, reject
    - [x] Events: ContentPublished, ApprovalRequested, ApprovalProcessed, ContentCreated, ContentUpdated <!-- ✅ 5 events -->
    - [x] Notifications para managers <!-- ✅ 4 listeners integrados -->
    - [x] Endpoints de aprovação
    - [x] Testes de workflow <!-- ✅ ContentApprovalTest com 7 testes -->

- [x] **Preview e Versionamento Avançado**
    - [x] Preview token generation (JWT)
    - [x] Endpoint público de preview
    - [x] Rollback de versões
    - [x] Comparação de versões (diff)
    - [x] Interface de versionamento

- [ ] **SDK JavaScript** <!-- TODO: FALTA SDK JAVASCRIPT -->
    - [ ] Criar pacote cms-client-sdk
    - [ ] Métodos: getPages, getPage, getPosts, etc.
    - [ ] Autenticação via API key
    - [ ] README com exemplos
    - [ ] Testar integração com site Vue.js

**Tempo estimado:** 10-15 horas

---

### FASE 3: Integração Instagram - Base (Semana 6) ✅ COMPLETA

> **Status**: ✅ Implementada - Commit `9d1ce7b`  
> **Data**: 28/01/2026

#### ✅ Checklist Completo

- [x] **Setup Meta Developer**
    - [x] Documentação de setup criada
    - [x] Configurar Instagram Basic Display
    - [x] Configurar OAuth redirect
    - [x] Obter App ID e Secret
    - [x] Configurar webhooks
    - [x] Adicionar credenciais no .env

- [x] **Models e Migrations**
    - [x] create_instagram_accounts_table
    - [x] create_instagram_messages_table
    - [x] InstagramAccount model
    - [x] InstagramMessage model
    - [x] Relationships com Company e Lead

- [x] **InstagramService**
    - [x] Implementar MessageServiceInterface
    - [x] connectAccount (OAuth)
    - [x] fetchRecentMessages
    - [x] fetchPosts
    - [x] saveMessage
    - [x] Criptografia de tokens

- [x] **Controllers e Webhooks**
    - [x] InstagramController (connect, messages)
    - [x] InstagramWebhookController (handle, verify)
    - [x] Validação de signature
    - [x] Routes configuradas

- [x] **Jobs**
    - [x] SyncInstagramMessagesJob
    - [x] ProcessIncomingMessageJob
    - [x] Schedule a cada 5 minutos
    - [x] Vincular mensagens a leads

- [x] **Testes**
    - [x] Feature: OAuth flow, listar mensagens
    - [x] Unit: InstagramService (mock API)
    - [x] Webhook validation

- [x] **Documentação**
    - [x] INSTAGRAM_INTEGRATION.md completo
    - [x] FASE3_INSTAGRAM_COMPLETE.md resumo

**Tempo estimado:** 8-12 horas  
**Tempo real:** ~10 horas  
**Arquivos:** 18 novos, 3 modificados (~1.981 linhas)

---

### FASE 3.5: Instagram - Funcionalidades Avançadas (Futuro)

> **Status**: ⏳ Pendente - Melhorias após implementação base  
> **Prioridade**: Média

#### 📋 Checklist de Funcionalidades Avançadas (TODO)

- [ ] **Envio de Mensagens**
  - [ ] Implementar sendMessage no InstagramService
  - [ ] Suporte para texto, imagens, vídeos
  - [ ] Suporte para mensagens template
  - [ ] Controle de rate limiting
  - [ ] Fila de envio com retry

- [ ] **Interações com Posts**
  - [ ] Responder comentários automaticamente
  - [ ] Curtir comentários via API
  - [ ] Ocultar comentários spam
  - [ ] Monitorar menções (@empresa)
  - [ ] Alertas de comentários negativos

- [ ] **Monitoramento e Analytics**
  - [ ] Hashtags tracker (#produto, #campanha)
  - [ ] Engagement metrics (likes, shares, saves)
  - [ ] Audience insights (demographics)
  - [ ] Best time to post analysis
  - [ ] Competitor monitoring

- [ ] **Agendamento de Conteúdo**
  - [ ] Schedule posts
  - [ ] Queue de publicação
  - [ ] Preview antes de publicar
  - [ ] Auto-publish com aprovação
  - [ ] Calendário editorial

- [ ] **Stories e Reels**
  - [ ] Fetch stories views
  - [ ] Reels engagement tracking
  - [ ] Story mentions processor
  - [ ] Interactive stickers (polls, questions)
  - [ ] Story replies integration

- [ ] **Respostas Automáticas**
  - [ ] Templates de respostas rápidas
  - [ ] Auto-reply keywords
  - [ ] Away messages
  - [ ] FAQ bot integration
  - [ ] Integração com IA (FASE 5)

- [ ] **Dashboard e Relatórios**
  - [ ] Multi-account dashboard
  - [ ] Unified inbox (Instagram + WhatsApp)
  - [ ] Response time metrics
  - [ ] Lead conversion tracking
  - [ ] Export relatórios PDF/Excel

**Tempo estimado:** 20-30 horas  
**Complexidade:** Alta (depende de aprovações Meta)

---

### FASE 4: Integração WhatsApp Business API (Semana 7) ✅ COMPLETA

> **Status**: ✅ Implementada  
> **Data**: Janeiro 2026

#### ✅ Checklist Completo

- [x] **Setup WhatsApp Business**
  - [x] Criar WhatsApp Business Account
  - [x] Adicionar número
  - [x] Obter Phone Number ID
  - [x] Gerar Access Token permanente
  - [x] Configurar webhook
  - [x] Definir Verify Token
  - [x] Credenciais no .env

- [x] **Models e Migrations**
  - [x] create_whatsapp_accounts_table
  - [x] create_whatsapp_conversations_table
  - [x] create_whatsapp_messages_table
  - [x] Models com relationships

- [x] **WhatsAppService**
  - [x] Implementar MessageServiceInterface
  - [x] sendMessage (texto, mídia)
  - [x] receiveMessage
  - [x] updateMessageStatus
  - [x] getConversations
  - [x] Criptografia de tokens

- [x] **Controllers e Webhooks**
  - [x] WhatsAppController (conversations, send)
  - [x] WhatsAppWebhookController (handle, verify)
  - [x] Validação de signature HMAC
  - [x] Routes

- [x] **Jobs**
  - [x] SendWhatsAppMessageJob
  - [x] ProcessIncomingMessageJob
  - [x] Retry strategy configurada
  - [x] Vincular a leads
  - [x] Registrar atividades

- [x] **Testes**
  - [x] Webhook verification
  - [x] Envio e recebimento
  - [x] Status updates
  - [x] Integração com CRM

**Tempo estimado:** 10-15 horas  
**Tempo real:** ~12 horas

---

### FASE 5: IA com Gemini (Semana 8-9) ✅ COMPLETA

> **Status**: ✅ Implementada  
> **Data**: 28/01/2026

#### ✅ Checklist Completo

- [x] **Setup Gemini**
    - [x] Criar conta Google AI Studio
    - [x] Obter API key
    - [x] Testar chamada manual
    - [x] Configurar .env

- [x] **Models e Migrations**
    - [x] create_ai_settings_table
    - [x] create_ai_prompt_templates_table
    - [x] create_ai_conversations_table
    - [x] create_ai_messages_table
    - [x] create_ai_feedback_table
    - [x] Models completos

- [x] **Services IA**
    - [x] AIProviderInterface
    - [x] GeminiService (generate, chat, countTokens)
    - [x] AIFactory (Strategy Pattern)
    - [x] ResponseGeneratorService
    - [x] ConversationService
    - [x] Testes com mock

- [x] **Controllers**
    - [x] AISettingsController (CRUD configurações)
    - [x] AIConversationController (histórico)
    - [x] AIPromptTemplateController (templates)
    - [x] API Resources
    - [x] Form Requests

- [x] **Integração com Social**
    - [x] GenerateAIResponseJob
    - [x] Integrar com ProcessIncomingMessage
    - [x] Delay configurável
    - [x] Flag ai_generated
    - [x] Testar fluxo completo

- [x] **Prompt Templates**
    - [x] Seeder com templates padrão
    - [x] Atendimento, FAQ, vendas, suporte
    - [x] Variáveis dinâmicas
    - [x] Interface de edição

- [x] **Documentação**
    - [x] AI_INTEGRATION.md completo
    - [x] Exemplos de uso
    - [x] Guia de configuração

**Tempo estimado:** 12-18 horas  
**Tempo real:** ~15 horas  
**Arquivos:** 15 novos (~3.200 linhas)

---

### FASE 6: Sistema de Notificações (Semana 10) ✅ COMPLETA

> **Status**: ✅ Implementada  
> **Data**: 29/01/2026

#### ✅ Checklist Completo

- [x] **Models e Migrations**
  - [x] create_notifications_table
  - [x] create_notification_preferences_table
  - [x] create_notification_templates_table
  - [x] Models com relacionamentos

- [x] **NotificationService**
  - [x] send() com validação de preferências
  - [x] create() e createFromTemplate()
  - [x] sendBulk() para notificações em massa
  - [x] retryFailed() com retry configurável
  - [x] processScheduled() para agendamentos
  - [x] markAsRead() operação em lote

- [x] **Canais de Notificação (5)**
  - [x] EmailNotificationChannel (HTML formatado)
  - [x] WhatsAppNotificationChannel (integração Business API)
  - [x] PushNotificationChannel (stub FCM)
  - [x] SmsNotificationChannel (stub)
  - [x] InAppNotificationChannel (database)

- [x] **Controllers**
  - [x] NotificationController (CRUD, statistics)
  - [x] NotificationPreferenceController (preferências)
  - [x] NotificationTemplateController (templates)
  - [x] 24 endpoints de API

- [x] **Jobs**
  - [x] SendNotificationJob (async)
  - [x] ProcessScheduledNotificationsJob

- [x] **Features**
  - [x] 50 tipos de notificação
  - [x] Preferências por usuário
  - [x] Schedule (horário de trabalho, timezone)
  - [x] Template com substituição de variáveis
  - [x] 4 níveis de prioridade
  - [x] Retry automático (3 tentativas)

- [x] **Documentação**
  - [x] NOTIFICATION_SYSTEM.md completo
  - [x] Exemplos de uso
  - [x] API reference

**Tempo estimado:** 10-12 horas  
**Tempo real:** ~10 horas  
**Arquivos:** 18 novos (~3.000 linhas)

---

### FASE 7: Relatórios e Dashboards (Semana 11) ✅ COMPLETA

> **Status**: ✅ Implementada  
> **Data**: 29/01/2026

#### ✅ Checklist Completo

- [x] **Models e Migrations**
  - [x] create_reports_table
  - [x] create_report_schedules_table
  - [x] create_report_exports_table
  - [x] Models com relacionamentos completos

- [x] **ReportService**
  - [x] execute() com filtros dinâmicos
  - [x] 8 tipos de relatórios
  - [x] Agrupamento com agregações
  - [x] Seleção de colunas
  - [x] Geração de gráficos
  - [x] Sumários automáticos

- [x] **DashboardService**
  - [x] getMainDashboard() completo
  - [x] Métricas de leads
  - [x] Métricas de vendas
  - [x] Métricas de atividades
  - [x] Métricas de tarefas
  - [x] Taxa de conversão
  - [x] Top performers
  - [x] Funil de vendas
  - [x] Métricas em tempo real

- [x] **ExportService**
  - [x] Export para PDF (stub)
  - [x] Export para Excel/CSV
  - [x] Gestão de arquivos
  - [x] Auto-expiração
  - [x] Download tracking

- [x] **Controllers**
  - [x] ReportController (CRUD, execute, export)
  - [x] DashboardController (9 endpoints)
  - [x] ReportScheduleController (agendamentos)
  - [x] 33 endpoints de API

- [x] **Jobs**
  - [x] GenerateScheduledReportJob
  - [x] CleanOldExportsJob

- [x] **Features**
  - [x] 8 tipos de relatórios
  - [x] Filtros dinâmicos por tipo
  - [x] Colunas personalizáveis
  - [x] Agrupamento e agregações
  - [x] Ordenação customizável
  - [x] Gráficos (bar, line, pie)
  - [x] Exportação (PDF, Excel, CSV)
  - [x] Favoritos e públicos/privados
  - [x] Duplicação de relatórios
  - [x] Agendamento (5 frequências)
  - [x] Email automático com anexo

**Tempo estimado:** 12-15 horas  
**Tempo real:** ~12 horas  
**Arquivos:** 16 novos (~2.600 linhas)

---

### FASE 8: Frontend Vue + Inertia (Semana 12-13)

#### ✅ Checklist Completo

- [ ] **Setup Frontend**
  - [ ] Instalar Inertia Laravel
  - [ ] Instalar @inertiajs/vue3
  - [ ] Instalar PrimeVue + PrimeIcons
  - [ ] Configurar Vite
  - [ ] Hot reload funcionando

- [ ] **Layout Base**
  - [ ] AppLayout.vue (sidebar, navbar, breadcrumbs)
  - [ ] Sidebar.vue (menu navegação)
  - [ ] Navbar.vue (user dropdown)
  - [ ] PrimeVue theme aplicado
  - [ ] Responsivo

- [ ] **Páginas CRM**
  - [ ] Dashboard.vue (refatorar)
  - [ ] Leads/Index.vue (DataTable)
  - [ ] Leads/Form.vue
  - [ ] Pipelines/Index.vue
  - [ ] Activities/Index.vue
  - [ ] Tasks/Index.vue

- [ ] **Páginas CMS**
  - [ ] Sites/Index.vue + Form.vue
  - [ ] Pages/Index.vue + Form.vue + Preview.vue
  - [ ] Posts/Index.vue + Form.vue
  - [ ] Portfolios/Index.vue + Form.vue
  - [ ] Menus/Index.vue + Form.vue
  - [ ] Editor WYSIWYG (TinyMCE/Tiptap)
  - [ ] Upload de imagens
  - [ ] Preview iframe

- [ ] **Páginas Social**
  - [ ] Instagram/Index.vue (contas)
  - [ ] Instagram/Messages.vue
  - [ ] WhatsApp/Index.vue (conversas)
  - [ ] WhatsApp/Chat.vue (interface chat)
  - [ ] ChatInterface.vue (componente reutilizável)
  - [ ] Real-time updates (polling)

- [ ] **Páginas IA**
  - [ ] AI/Settings.vue (configurações)
  - [ ] AI/Conversations.vue (histórico)
  - [ ] AI/PromptTemplates/Index.vue + Form.vue
  - [ ] AI/Analytics.vue (dashboard uso)

- [ ] **Páginas Notificações**
  - [ ] Notifications/Index.vue
  - [ ] Notifications/Preferences.vue
  - [ ] Notifications/Templates.vue

- [ ] **Páginas Relatórios**
  - [ ] Reports/Index.vue
  - [ ] Reports/Builder.vue (construtor visual)
  - [ ] Reports/View.vue (visualização)
  - [ ] Dashboards/Main.vue

**Tempo estimado:** 20-25 horas

---

### FASE 9: Testes e Qualidade (Semana 14)

#### ✅ Checklist Completo

- [ ] **Testes Completos**
  - [ ] Feature tests: CMS workflow completo
  - [ ] Feature tests: WhatsApp integration
  - [ ] Feature tests: IA generation
  - [ ] Feature tests: Notificações
  - [ ] Feature tests: Relatórios
  - [ ] Unit tests: Services críticos
  - [ ] Mocks de APIs externas
  - [ ] `php artisan test --coverage`
  - [ ] Cobertura 65%+

- [ ] **Análise Estática**
  - [ ] `./vendor/bin/phpstan analyse`
  - [ ] Corrigir type hints
  - [ ] Corrigir propriedades
  - [ ] Larastan nível 5+ passando

- [ ] **Code Style**
  - [ ] `./vendor/bin/pint`
  - [ ] PSR-12 compliance
  - [ ] Configurar pre-commit hook

- [ ] **Documentação API**
  - [ ] Instalar l5-swagger
  - [ ] Annotations nos controllers principais
  - [ ] Schemas definidos
  - [ ] /api/documentation acessível
  - [ ] Testar endpoints

- [ ] **Documentação Geral**
  - [ ] Atualizar README.md
  - [ ] Criar CHANGELOG.md
  - [ ] docs/CMS.md
  - [ ] docs/INTEGRATIONS.md
  - [ ] Atualizar docs/AI_INTEGRATION.md
  - [ ] Atualizar docs/NOTIFICATION_SYSTEM.md
  - [ ] Completar docs/REPORTS_DASHBOARDS.md
  - [ ] Guias de uso

**Tempo estimado:** 10-15 horas

---

### FASE 10: Deploy e Produção (Pós-desenvolvimento)

#### ✅ Checklist Completo

- [ ] **Preparar para Deploy**
  - [ ] Otimizações: config:cache, route:cache, view:cache
  - [ ] npm run build
  - [ ] .env.example atualizado
  - [ ] Dependências de produção

- [ ] **Setup Servidor**
  - [ ] Provisionar VPS
  - [ ] Nginx + PHP 8.2-FPM
  - [ ] MySQL 8.4
  - [ ] Redis
  - [ ] SSL (Let's Encrypt)
  - [ ] Firewall configurado

- [ ] **Queue Workers**
  - [ ] Supervisor configurado
  - [ ] 3 workers (high, default, low)
  - [ ] Auto-restart habilitado

- [ ] **Cron Jobs**
  - [ ] Scheduler configurado
  - [ ] `* * * * * php artisan schedule:run`

- [ ] **Monitoring**
  - [ ] Logs estruturados
  - [ ] Error tracking (Sentry, opcional)
  - [ ] Uptime monitoring

- [ ] **CI/CD (Opcional)**
  - [ ] GitHub Actions
  - [ ] Testes automáticos
  - [ ] Deploy automático (staging)

**Tempo estimado:** 6-10 horas

---

## 📚 Exemplos de Código

### Migration Completa - Pages

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->string('slug');
            $table->string('title');
            $table->text('content')->nullable();
            $table->string('meta_title', 60)->nullable();
            $table->string('meta_description', 160)->nullable();
            $table->string('meta_keywords')->nullable();
            $table->enum('status', ['draft', 'pending', 'published'])->default('draft');
            $table->foreignId('published_version_id')->nullable()->constrained('content_versions')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->unique(['site_id', 'slug']);
            $table->index(['site_id', 'status']);
            $table->index(['site_id', 'published_at']);
            $table->index('created_by');
            
            // Full-text search
            $table->fullText(['title', 'content']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
```

### Model Completo - Page

```php
<?php

namespace App\Models\CMS;

use App\Enums\ContentStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Page extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'site_id',
        'slug',
        'title',
        'content',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'status',
        'published_version_id',
        'published_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'status' => ContentStatus::class,
        'published_at' => 'datetime',
    ];

    // Relationships
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function publishedVersion(): BelongsTo
    {
        return $this->belongsTo(ContentVersion::class, 'published_version_id');
    }

    public function versions(): MorphMany
    {
        return $this->morphMany(ContentVersion::class, 'versionable');
    }

    public function approvals(): MorphMany
    {
        return $this->morphMany(ContentApproval::class, 'approvable');
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', ContentStatus::PUBLISHED);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', ContentStatus::DRAFT);
    }

    public function scopePending($query)
    {
        return $query->where('status', ContentStatus::PENDING);
    }

    public function scopeForSite($query, int $siteId)
    {
        return $query->where('site_id', $siteId);
    }

    // Accessors
    public function getIsPublishedAttribute(): bool
    {
        return $this->status === ContentStatus::PUBLISHED;
    }

    public function getPreviewUrlAttribute(): string
    {
        return route('api.cms.preview', [
            'type' => 'page',
            'id' => $this->id,
            'token' => $this->generatePreviewToken()
        ]);
    }

    // Methods
    public function generatePreviewToken(): string
    {
        return encrypt([
            'page_id' => $this->id,
            'expires_at' => now()->addHours(24)
        ]);
    }
}
```

### Form Request - StorePageRequest

```php
<?php

namespace App\Http\Requests\CMS;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\CMS\Page::class);
    }

    public function rules(): array
    {
        return [
            'site_id' => ['required', 'exists:sites,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9-]+$/',
                Rule::unique('pages')->where('site_id', $this->site_id)
            ],
            'content' => ['nullable', 'string'],
            'meta_title' => ['nullable', 'string', 'max:60'],
            'meta_description' => ['nullable', 'string', 'max:160'],
            'meta_keywords' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:draft,pending,published'],
        ];
    }

    public function messages(): array
    {
        return [
            'slug.regex' => 'O slug deve conter apenas letras minúsculas, números e hífens',
            'slug.unique' => 'Já existe uma página com este slug neste site',
            'meta_title.max' => 'O título SEO deve ter no máximo 60 caracteres',
            'meta_description.max' => 'A descrição SEO deve ter no máximo 160 caracteres',
        ];
    }
}
```

### API Resource - PageResource

```php
<?php

namespace App\Http\Resources\CMS;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'site_id' => $this->site_id,
            'slug' => $this->slug,
            'title' => $this->title,
            'content' => $this->content,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'meta_keywords' => $this->meta_keywords,
            'status' => $this->status->value,
            'is_published' => $this->is_published,
            'published_at' => $this->published_at?->toIso8601String(),
            'preview_url' => $this->preview_url,
            
            // Relationships
            'site' => new SiteResource($this->whenLoaded('site')),
            'creator' => [
                'id' => $this->creator->id,
                'name' => $this->creator->name,
            ],
            'updater' => $this->updater ? [
                'id' => $this->updater->id,
                'name' => $this->updater->name,
            ] : null,
            
            // Versions
            'versions_count' => $this->whenCounted('versions'),
            'latest_version' => new ContentVersionResource($this->whenLoaded('versions', function() {
                return $this->versions->first();
            })),
            
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
```

### Policy - PagePolicy

```php
<?php

namespace App\Policies\CMS;

use App\Models\CMS\Page;
use App\Models\User;

class PagePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('cms.pages.view');
    }

    public function view(User $user, Page $page): bool
    {
        return $user->company_id === $page->site->company_id
            && $user->hasPermissionTo('cms.pages.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('cms.pages.create');
    }

    public function update(User $user, Page $page): bool
    {
        return $user->company_id === $page->site->company_id
            && $user->hasPermissionTo('cms.pages.update');
    }

    public function delete(User $user, Page $page): bool
    {
        return $user->company_id === $page->site->company_id
            && $user->hasPermissionTo('cms.pages.delete');
    }

    public function publish(User $user): bool
    {
        return $user->hasRole(['admin', 'manager']);
    }

    public function approve(User $user): bool
    {
        return $user->hasRole(['admin', 'manager']);
    }
}
```

### Test - PageTest

```php
<?php

namespace Tests\Feature\CMS;

use App\Models\CMS\{Site, Page};
use App\Models\{Company, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Site $site;

    protected function setUp(): void
    {
        parent::setUp();
        
        $company = Company::factory()->create();
        $this->user = User::factory()->create(['company_id' => $company->id]);
        $this->site = Site::factory()->create(['company_id' => $company->id]);
        
        $this->actingAs($this->user);
    }

    public function test_can_list_pages()
    {
        Page::factory()->count(5)->create(['site_id' => $this->site->id]);

        $response = $this->getJson("/api/cms/pages?site_id={$this->site->id}");

        $response->assertOk()
            ->assertJsonCount(5, 'data');
    }

    public function test_can_create_page()
    {
        $data = [
            'site_id' => $this->site->id,
            'title' => 'Test Page',
            'content' => 'Test content',
            'status' => 'draft'
        ];

        $response = $this->postJson('/api/cms/pages', $data);

        $response->assertCreated()
            ->assertJsonPath('data.title', 'Test Page')
            ->assertJsonPath('data.slug', 'test-page');

        $this->assertDatabaseHas('pages', [
            'site_id' => $this->site->id,
            'title' => 'Test Page',
            'slug' => 'test-page',
        ]);
    }

    public function test_slug_must_be_unique_per_site()
    {
        Page::factory()->create([
            'site_id' => $this->site->id,
            'slug' => 'test-page'
        ]);

        $data = [
            'site_id' => $this->site->id,
            'title' => 'Another Page',
            'slug' => 'test-page',
            'status' => 'draft'
        ];

        $response = $this->postJson('/api/cms/pages', $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('slug');
    }

    public function test_can_publish_page()
    {
        $page = Page::factory()->create([
            'site_id' => $this->site->id,
            'status' => 'draft'
        ]);

        $response = $this->postJson("/api/cms/pages/{$page->id}/publish");

        $response->assertOk();

        $page->refresh();
        $this->assertEquals('published', $page->status->value);
        $this->assertNotNull($page->published_at);
        $this->assertNotNull($page->published_version_id);
    }

    public function test_creates_version_on_update()
    {
        $page = Page::factory()->create(['site_id' => $this->site->id]);

        $this->assertEquals(1, $page->versions()->count());

        $this->putJson("/api/cms/pages/{$page->id}", [
            'title' => 'Updated Title',
            'content' => 'Updated content'
        ]);

        $this->assertEquals(2, $page->fresh()->versions()->count());
    }
}
```

---

## 🧪 Testes

### Estratégia de Testes

#### Feature Tests (~40% cobertura)
**O que testar:**
- CRUD completo de cada recurso
- Validações de input
- Autorização (policies)
- Workflows (aprovação, publicação)
- Integrações (webhooks)

#### Unit Tests (~30% cobertura)
**O que testar:**
- Lógica de Services
- Métodos de Models
- Helpers e Utilities

#### Mocks
- APIs externas (Instagram, WhatsApp, Gemini)
- Jobs pesados
- External services

### Comandos Úteis

```bash
# Rodar todos os testes
php artisan test

# Com cobertura
php artisan test --coverage

# Com cobertura HTML
php artisan test --coverage-html coverage

# Apenas um grupo
php artisan test --group=cms

# Apenas um arquivo
php artisan test tests/Feature/CMS/PageTest.php

# Filtrar por nome
php artisan test --filter=test_can_create_page
```

---

## 📊 Decision Log

### Resumo de Decisões Arquiteturais

| # | Área | Decisão | Alternativas Consideradas | Razão |
|---|------|---------|---------------------------|-------|
| 1 | Arquitetura Base | MVC + Service Layer | DDD Pragmático, Microservices | Familiaridade, produtividade, sem over-engineering |
| 2 | CMS Content Types | Tabelas separadas por tipo | Tabela única com type discriminator | Campos específicos, queries simples, índices otimizados |
| 3 | Versionamento | Polymorphic (content_versions) | Tabelas separadas, sem versionamento | Reutilização, simplicidade, suporta todos tipos |
| 4 | Categorias Posts | Tabela normalizada | JSON field | Filtros/agrupamentos eficientes, índices |
| 5 | Mensagens Sociais | Tabelas separadas (instagram_messages, whatsapp_messages) | Tabela única social_messages | Campos específicos por plataforma, queries otimizadas |
| 6 | Vinculação Leads | FK opcional + auto-criação | Sempre criar, nunca criar | Flexibilidade, facilita conversão |
| 7 | Provider IA | Interface + Factory Pattern | Hardcoded Gemini | Não fica preso, fácil trocar OpenAI/Claude |
| 8 | Histórico IA | Salvar todas interações | Não salvar, só resumos | Análise, melhoria contínua, auditoria, custos |
| 9 | Auto-reply IA | Opcional + delay configurável | Sempre sugerir, sempre enviar | Segurança, controle, evita respostas inapropriadas |
| 10 | Controllers | Magros + Services | Fat controllers, Repository pattern | SRP, testabilidade, reuso |
| 11 | Services | Específicos por contexto | God services, micro-services | Evita God Classes, responsabilidade clara |
| 12 | Queues | Redis multi-fila (high, default, low) | Database queue, SQS | Rápido, priorização, suporte nativo Laravel |
| 13 | Retry Strategy | Auto retry com backoff incremental | Retry infinito, falhar imediatamente | Resiliência a falhas temporárias, não sobrecarrega |
| 14 | Jobs | Específicos por operação | Job genérico | SRP, controle fino (timeout, retry), logs claros |
| 15 | Frontend | Vue 3 + Inertia + PrimeVue | Nuxt separado, Vue puro | SSR híbrido, componentes prontos, produtividade |

---

## 📚 Referências

### Documentações Oficiais

- **Laravel 12:** https://laravel.com/docs/12.x
- **Vue 3:** https://vuejs.org/guide/introduction.html
- **Inertia.js:** https://inertiajs.com/
- **PrimeVue:** https://primevue.org/
- **Meta Graph API:** https://developers.facebook.com/docs/graph-api
- **WhatsApp Business API:** https://developers.facebook.com/docs/whatsapp
- **Google Gemini:** https://ai.google.dev/docs

### Padrões e Princípios

- **SOLID Principles:** https://en.wikipedia.org/wiki/SOLID
- **Clean Code (Robert C. Martin):** Livro referência
- **Domain-Driven Design Basics:** https://martinfowler.com/bliki/DomainDrivenDesign.html
- **API REST Best Practices:** https://restfulapi.net/

### Ferramentas

- **PHPStan/Larastan:** https://github.com/larastan/larastan
- **Laravel Pint:** https://laravel.com/docs/12.x/pint
- **Pest PHP:** https://pestphp.com/
- **L5 Swagger:** https://github.com/DarkaOnLine/L5-Swagger

---

## 🎯 Próximos Passos Imediatos

### Para Começar AGORA:

1. **Revisar este documento completo** ✅
   - Certifique-se que entendeu todas as decisões
   - Anote dúvidas para esclarecer

2. **Setup do Ambiente** (30 min)
   ```bash
   cd d:\github\crm-makin\crm-api
   composer install
   composer require --dev pestphp/pest larastan/larastan laravel/pint
   php artisan pest:install
   ```

3. **Criar Branch de Desenvolvimento** (5 min)
   ```bash
   git checkout -b feature/cms-headless
   ```

4. **Iniciar FASE 0** (3-5 horas)
   - Seguir checklist da Fase 0
   - Commits pequenos e frequentes
   - Testar após cada alteração

5. **Daily Progress** (recomendado)
   - Definir meta diária (ex: completar X tarefas do checklist)
   - Commit no final do dia
   - Atualizar checklist

### Quando Precisar de Ajuda:

- **Dúvidas arquiteturais:** Revisar seções de Design deste doc
- **Problemas de implementação:** Ver exemplos de código
- **Bugs/Erros:** Verificar logs, rodar testes
- **Decisões novas:** Documentar no Decision Log

---

## 📞 Suporte

Este documento foi criado como guia completo para o desenvolvimento do CRM Makin Evolution.

**Mantenha este documento atualizado** conforme o projeto evolui!

---

**Versão:** 1.0  
**Última atualização:** Janeiro 2026  
**Status:** 🟢 Pronto para Implementação

---

## ✅ Final Checklist

Antes de começar a implementação, confirme:

- [ ] Li e entendi todo este documento
- [ ] Entendo as decisões de arquitetura
- [ ] Sei como está organizado o banco de dados
- [ ] Compreendo a estrutura de Services/Controllers
- [ ] Entendo o fluxo de Jobs e Queues
- [ ] Tenho ambiente de desenvolvimento configurado
- [ ] Tenho acesso às APIs necessárias (Meta, Gemini)
- [ ] Sei onde buscar ajuda quando necessário
- [ ] Estou pronto para começar! 🚀

---

**Boa sorte com o desenvolvimento! Este será um projeto incrível para seu portfólio!** 💪
