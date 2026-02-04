# Arquitetura SaaS CRM/CMS Multi-tenant

## 📊 Visão Geral do Sistema

Este é um **SaaS Multi-tenant** onde você oferece uma plataforma de CRM/CMS para seus clientes gerenciarem:
- **Leads** (CRM) - Prospects, oportunidades, vendas
- **Conteúdo** (CMS) - Páginas, posts, portfolio para os sites deles

```
┌─────────────────────────────────────────────────────────────┐
│            SUA PLATAFORMA (Multi-tenant SaaS)               │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  Cliente 1 (Empresa A)         Cliente 2 (Empresa B)       │
│  ┌──────────────────┐          ┌──────────────────┐       │
│  │ CRM              │          │ CRM              │       │
│  │ ├─ Leads         │          │ ├─ Leads         │       │
│  │ ├─ Atividades    │          │ ├─ Atividades    │       │
│  │ └─ Propostas     │          │ └─ Propostas     │       │
│  │                  │          │                  │       │
│  │ CMS              │          │ CMS              │       │
│  │ ├─ Páginas       │          │ ├─ Páginas       │       │
│  │ ├─ Posts         │          │ ├─ Posts         │       │
│  │ └─ Portfolio     │          │ └─ Portfolio     │       │
│  └──────────────────┘          └──────────────────┘       │
│         │                              │                   │
│         ↓                              ↓                   │
│  www.site-cliente-a.com        www.site-cliente-b.com     │
│  (consome API pública)         (consome API pública)      │
└─────────────────────────────────────────────────────────────┘
```

---

## 🎯 Arquitetura Recomendada

### **1. INERTIA.JS - 90% do Sistema** ⭐⭐⭐⭐⭐

**Use Inertia.js para toda a interface administrativa:**

#### **Painel da Plataforma (Você)**
- Gerenciar empresas/clientes
- Relatórios globais
- Configurações da plataforma
- Billing/Assinaturas

#### **Painel do Cliente (Suas empresas)**
- **CRM Completo**
  - ✅ Leads (CRUD)
  - ✅ Atividades
  - ✅ Tarefas
  - ✅ Produtos
  - ✅ Propostas
  - ✅ Pipeline
  
- **CMS Completo**
  - ✅ Páginas (CRUD)
  - ✅ Posts/Blog (CRUD)
  - ✅ Portfolio (CRUD)
  - ✅ Menus
  - ✅ Banners
  - ✅ Equipe/Testimonials

**Por que Inertia?**
```php
// Controller super simples
public function index() {
    return Inertia::render('Leads/Index', [
        'leads' => Lead::where('company_id', auth()->user()->company_id)
            ->paginate(15),
        'stats' => $this->getStats(),
    ]);
}
```

```vue
<!-- Vue component recebe props automaticamente -->
<script setup>
const props = defineProps(['leads', 'stats'])
// Pronto! Sem useEffect, useState, fetch, etc
</script>
```

---

### **2. API REST Pública - 10% do Sistema** 🔌

**Use APENAS para os sites públicos dos clientes consumirem:**

#### **Endpoints Públicos:**
```
GET  /api/public/pages?site_key=abc123
GET  /api/public/pages/{slug}?site_key=abc123
GET  /api/public/posts?site_key=abc123
POST /api/public/leads (formulário de contato → cria lead)
```

#### **Exemplo de Uso no Site do Cliente:**

```html
<!-- No site do cliente (HTML/WordPress/etc) -->
<script>
// 1. Buscar conteúdo
fetch('https://sua-plataforma.com/api/public/pages?site_key=abc123')
  .then(res => res.json())
  .then(pages => {
    // Renderizar páginas no site
  })

// 2. Formulário de contato que cria lead
document.getElementById('contact-form').addEventListener('submit', async (e) => {
  e.preventDefault()
  
  const response = await fetch('https://sua-plataforma.com/api/public/leads', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-Site-Key': 'abc123'
    },
    body: JSON.stringify({
      name: 'João Silva',
      email: 'joao@email.com',
      phone: '11999999999',
      message: 'Gostaria de saber mais...'
    })
  })
  
  // Lead criado! Aparece no CRM do cliente
})
</script>
```

---

### **3. WebSocket - 5% do Sistema (Opcional)** ⚡

**Use APENAS para:**

#### **Notificações em Tempo Real**
```javascript
// Dashboard do cliente
Echo.channel(`company.${companyId}`)
  .listen('lead.created', (lead) => {
    // 🔔 "Novo lead: João Silva"
    showNotification(`Novo lead: ${lead.name}`)
    playSound()
  })
```

#### **Colaboração entre Usuários**
```javascript
// Múltiplos usuários da mesma empresa
Echo.join(`company.${companyId}.editing`)
  .here((users) => {
    // Mostrar quem está online
  })
  .joining((user) => {
    // "Maria entrou"
  })
```

---

## 🔐 Segurança Multi-tenant CRÍTICA

### **Global Scopes Automáticos**

```php
// app/Traits/HasCompanyScope.php
trait HasCompanyScope {
    protected static function bootHasCompanyScope() {
        // SEMPRE filtra por company_id
        static::addGlobalScope('company', function ($builder) {
            if (auth()->check()) {
                $builder->where('company_id', auth()->user()->company_id);
            }
        });
        
        // Adiciona company_id automaticamente ao criar
        static::creating(function ($model) {
            if (!$model->company_id) {
                $model->company_id = auth()->user()->company_id;
            }
        });
    }
}
```

```php
// Aplicar em TODOS os models
class Lead extends Model {
    use HasCompanyScope; // ✅ Segurança automática
}

class Page extends Model {
    use HasCompanyScope; // ✅ Segurança automática
}

// Agora é IMPOSSÍVEL acessar dados de outra empresa!
Lead::all(); // Só retorna leads da empresa do usuário
```

### **Middleware de Validação**

```php
// app/Http/Middleware/EnsureUserCompanyScope.php
class EnsureUserCompanyScope {
    public function handle($request, $next) {
        if (!auth()->user()->company_id) {
            abort(403, 'Usuário sem empresa');
        }
        
        // Adiciona company_id ao request
        $request->merge(['company_id' => auth()->user()->company_id]);
        
        return $next($request);
    }
}
```

---

## 📁 Estrutura de Arquivos Recomendada

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Web/              # Inertia (90%)
│   │   │   ├── LeadController.php       ✅
│   │   │   ├── PageController.php       ✅
│   │   │   ├── PostController.php       ✅
│   │   │   └── DashboardController.php  ✅
│   │   │
│   │   └── API/
│   │       ├── CRM/          # API interna (se precisar)
│   │       └── Public/       # API pública (sites clientes)
│   │           └── ContentController.php ✅
│   │
│   └── Middleware/
│       └── EnsureUserCompanyScope.php   ✅ CRÍTICO
│
├── Models/
│   ├── CRM/
│   │   ├── Lead.php          (use HasCompanyScope)
│   │   ├── Activity.php      (use HasCompanyScope)
│   │   └── Proposal.php      (use HasCompanyScope)
│   │
│   └── CMS/
│       ├── Page.php          (use HasCompanyScope)
│       ├── Post.php          (use HasCompanyScope)
│       └── Site.php          (use HasCompanyScope)
│
├── Traits/
│   └── HasCompanyScope.php   ✅ CRÍTICO
│
└── Events/
    ├── LeadCreated.php       (WebSocket - opcional)
    └── PagePublished.php     (WebSocket - opcional)

resources/
└── js/
    ├── Pages/
    │   ├── Leads/            # Inertia Views
    │   │   ├── Index.vue     ✅ (use IndexInertia.vue)
    │   │   ├── Create.vue    ✅
    │   │   └── Edit.vue      ✅
    │   │
    │   ├── Pages/            # CMS Pages
    │   │   ├── Index.vue
    │   │   ├── Builder.vue   (Page Builder)
    │   │   └── Preview.vue
    │   │
    │   └── Posts/            # Blog Posts
    │       ├── Index.vue
    │       └── Editor.vue
    │
    └── composables/
        ├── useLeadsWebSocket.js    (opcional)
        └── useNotifications.js     (opcional)

routes/
├── web.php           # Rotas Inertia (90% do sistema)
├── api.php           # API interna (se precisar)
└── api-public.php    # API pública (sites clientes) ✅
```

---

## 🚀 Fluxos Principais

### **Fluxo 1: Cliente gerencia conteúdo (Inertia)**

```
1. Cliente faz login no painel
   ↓
2. Acessa "CMS → Páginas"
   ↓
3. Clica "Nova Página"
   ↓
4. Preenche conteúdo (Page Builder)
   ↓
5. Clica "Publicar"
   ↓
6. [Inertia] POST /pages
   ↓
7. [Laravel] Salva com company_id automático
   ↓
8. [Inertia] Retorna para lista (sem reload)
   ↓
9. Página disponível na API pública
```

### **Fluxo 2: Site público consome conteúdo (API)**

```
1. Site do cliente carrega
   ↓
2. JavaScript faz: GET /api/public/pages?site_key=abc123
   ↓
3. [Laravel] Valida API key
   ↓
4. [Laravel] Retorna páginas (company_id = X, status = published)
   ↓
5. Site renderiza conteúdo
```

### **Fluxo 3: Visitante vira Lead (API)**

```
1. Visitante preenche formulário de contato
   ↓
2. JavaScript faz: POST /api/public/leads
   {
     "name": "João",
     "email": "joao@email.com",
     "message": "Quero saber mais"
   }
   ↓
3. [Laravel] Valida API key
   ↓
4. [Laravel] Cria lead (company_id = X, status = new)
   ↓
5. [WebSocket - opcional] Notifica dashboard do cliente
   ↓
6. Cliente vê novo lead no CRM em tempo real 🎉
```

---

## 💰 Custos e Escalabilidade

| Solução | Custo Mensal | Complexidade | Quando Usar |
|---------|-------------|--------------|-------------|
| **Inertia.js** | $0 | Baixa ✅ | SEMPRE (90%) |
| **API REST** | $0 | Média | Sites públicos (10%) |
| **Soketi** | $5-10 | Média | WebSocket self-hosted |
| **Pusher** | $49+ | Baixa | WebSocket gerenciado |

**Recomendação:**
- **Fase 1 (MVP):** Inertia.js apenas (RÁPIDO)
- **Fase 2:** Adicionar API pública para sites
- **Fase 3:** Adicionar WebSocket (se necessário)

---

## 📋 Checklist de Implementação

### ✅ Segurança Multi-tenant
- [ ] Criar `HasCompanyScope` trait
- [ ] Aplicar trait em TODOS os models
- [ ] Criar middleware `EnsureUserCompanyScope`
- [ ] Adicionar middleware em todas as rotas
- [ ] Testar: usuário A não pode ver dados de B

### ✅ Inertia.js (Interface Admin)
- [x] Leads CRUD com Inertia
- [ ] Pages CRUD com Inertia
- [ ] Posts CRUD com Inertia
- [ ] Dashboard com estatísticas
- [ ] Filtros e busca

### ✅ API Pública (Sites Clientes)
- [ ] Criar `ContentController` público
- [ ] Gerar API keys para sites
- [ ] Endpoints: pages, posts, leads
- [ ] Rate limiting
- [ ] CORS configurado
- [ ] Documentação da API

### ✅ WebSocket (Opcional - Fase 3)
- [ ] Instalar Soketi/Pusher
- [ ] Configurar Laravel Echo
- [ ] Events: LeadCreated, PagePublished
- [ ] Notificações em tempo real
- [ ] Indicador de usuários online

---

## 🎯 Resumo da Arquitetura

```
┌─────────────────────────────────────────────────────────────┐
│                   SUA PLATAFORMA SaaS                       │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  ADMIN INTERFACE (Inertia.js) ← 90% do sistema             │
│  ├─ Painel dos Clientes                                    │
│  │  ├─ CRM (Leads, Atividades, Propostas)                 │
│  │  ├─ CMS (Páginas, Posts, Portfolio)                    │
│  │  └─ Dashboards e Relatórios                            │
│  │                                                          │
│  └─ Painel da Plataforma (Você)                            │
│     ├─ Gerenciar Empresas                                  │
│     ├─ Billing/Assinaturas                                 │
│     └─ Relatórios Globais                                  │
│                                                             │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  API PÚBLICA (REST) ← 10% do sistema                       │
│  └─ Para sites dos clientes consumirem:                    │
│     ├─ GET /api/public/pages                               │
│     ├─ GET /api/public/posts                               │
│     └─ POST /api/public/leads (formulários)                │
│                                                             │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  WEBSOCKET (Opcional) ← 5% do sistema                      │
│  └─ Notificações em tempo real                             │
│     ├─ Novos leads                                         │
│     ├─ Usuários online                                     │
│     └─ Chat entre equipe                                   │
│                                                             │
└─────────────────────────────────────────────────────────────┘
          ↓                    ↓                    ↓
    ┌─────────┐          ┌─────────┐          ┌─────────┐
    │ Site    │          │ Site    │          │ Site    │
    │Cliente A│          │Cliente B│          │Cliente C│
    └─────────┘          └─────────┘          └─────────┘
```

---

## 🎓 Conclusão

**Para o seu SaaS CRM/CMS:**

1. **Use Inertia.js** para toda interface administrativa
   - Mais rápido de desenvolver
   - Menos bugs
   - Melhor DX (Developer Experience)
   - Segurança integrada com Laravel

2. **API REST** apenas para sites públicos dos clientes
   - Endpoints simples
   - Validação por API key
   - Rate limiting

3. **WebSocket** só se realmente precisar de tempo real
   - Não é necessário no MVP
   - Adicione depois se houver demanda

**Seus clientes vão:**
- ✅ Gerenciar leads no CRM (Inertia)
- ✅ Criar conteúdo no CMS (Inertia)
- ✅ Seus sites consumem via API REST
- ✅ Formulários dos sites criam leads automaticamente

Simples, seguro e escalável! 🚀
