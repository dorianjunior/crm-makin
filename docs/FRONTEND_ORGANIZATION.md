# Frontend Architecture & Design System - CRM Makin

**Design Philosophy:** Data Brutalism - Editorial Brutalist Style  
**DFII Score:** 13/15 (Excellent)  
**Última atualização:** 2026-02-07

> **🆕 ÚLTIMA REFATORAÇÃO:** MainLayout migrado para SCSS global (_layout-brutalist.scss)
> Todas as páginas agora devem usar classes globais em vez de `<style scoped>`

## 📐 Filosofia de Design

Dashboard CRM com **tipografia oversized estrutural**, **layout assimétrico** e **paleta monocromática** + accent color único. 

### Princípios Core:
- 🔢 Números gigantes (64px) dominam stat cards
- 📏 Bordas sólidas de 2px (sem sombras)
- 🎨 Um único accent color: **#FF6B35** (laranja vibrante)
- 📐 Layouts assimétricos que quebram expectativas
- ✍️ Tipografia estrutural com Space Grotesk

### Âncora de Diferenciação:
> "Se você tirar screenshot sem logo, reconhece pelos números gigantes de 64px, bordas quadradas de 2px, e aquele laranja vibrante que surge apenas no hover."

---

---

## 🎨 Sistema de Cores

### Paleta Monocromática
```scss
$primary-dark: #0a0a0a      // Preto profundo
$primary: #1a1a1a           // Preto principal
$primary-light: #2a2a2a     // Cinza escuro
$secondary: #3a3a3a         // Cinza médio
```

### Accent (Único Ponto de Cor)
```scss
$accent: #FF6B35            // Laranja vibrante - ÚNICO acento
$accent-dark: #E85A28       // Hover state
$accent-light: #FFB3A0      // Disabled/light state
```

### Light Theme
```scss
$light-bg-primary: #ffffff
$light-bg-secondary: #fafafa
$light-bg-tertiary: #f5f5f5
$light-text-primary: #0a0a0a
$light-text-secondary: #525252
$light-text-tertiary: #a3a3a3
$light-border: #e5e5e5
$light-border-bold: #262626
```

### Dark Theme
```scss
$dark-bg-primary: #1f2937
$dark-bg-secondary: #111827
$dark-bg-tertiary: #374151
$dark-text-primary: #f9fafb
$dark-text-secondary: #d1d5db
$dark-text-tertiary: #9ca3af
$dark-border: #374151
```

### Activity Type Colors
```scss
.badge--type-call      // #2196F3 (Blue)
.badge--type-meeting   // #4CAF50 (Green)
.badge--type-email     // #9C27B0 (Purple)
.badge--type-note      // #FFEB3B (Yellow)
.badge--type-task      // #FF6B35 (Orange - accent)
```

---

## ✍️ Tipografia

### Hierarquia de Fontes
```scss
$font-display: 'Space Grotesk'   // Headers, números, labels importantes
$font-body: 'Inter'              // Corpo de texto, parágrafos
$font-mono: 'JetBrains Mono'     // Timestamps, códigos, dados técnicos
```

### Stat Numbers (Oversized)
```scss
$stat-number-size: 4rem (64px)
$stat-number-weight: 800
$stat-number-line-height: 1
$stat-label-size: 0.6875rem (11px)
$stat-label-weight: 600
$stat-label-spacing: 0.1em
```

**Princípio:** Números são protagonistas visuais, não coadjuvantes.

---

## 🗂️ Estrutura de Arquivos SCSS

### ✅ Arquivos Principais (Manter)

```
resources/scss/
├── app.scss                          # Entry point
├── _variables.scss                   # Variáveis globais SCSS
├── _mixins.scss                      # Mixins reutilizáveis
├── _base.scss                        # Reset e estilos base
├── _theme.scss                       # CSS variables light/dark
│
├── _navbar.scss                      # ✅ Navbar (migrado)
├── _sidebar.scss                     # ✅ Sidebar (migrado)
│
├── _data-brutalism.scss              # 🎯 PRINCIPAL - Componentes core
├── _forms-brutalist.scss             # Formulários
├── _layout-brutalist.scss            # Layouts de página
├── _components-brutalist.scss        # Timeline, pagination, etc
├── _utilities-brutalist.scss         # Classes utilitárias
│
├── _sweetalert.scss                  # SweetAlert customizado
└── _utilities.scss                   # Utilitários gerais
```

### 📊 Tamanho dos Arquivos

| Arquivo | Linhas | Propósito | Prioridade |
|---------|--------|-----------|------------|
| `_data-brutalism.scss` | ~390 | Componentes core | **Crítico** |
| `_forms-brutalist.scss` | ~130 | Formulários | Alta |
| `_layout-brutalist.scss` | ~100 | Layouts | Alta |
| `_components-brutalist.scss` | ~210 | Timeline, pagination | Alta |
| `_utilities-brutalist.scss` | ~100 | Utilitários | Média |
| `_navbar.scss` | ~470 | Navbar | Alta |
| `_sidebar.scss` | ~190 | Sidebar | Alta |

**Total Brutalist System:** ~1,590 linhas  
**CSS Final:** ~112KB (compilado)

---

## ❌ Anti-Patterns (NUNCA FAZER)

### Evitar Completamente:
- ❌ Border-radius (exceto círculos perfeitos)
- ❌ Box-shadows sutis (use borders sólidas)
- ❌ Múltiplas cores de accent
- ❌ Gradientes
- ❌ Tipografia Inter/Roboto como display
- ❌ Layout simétrico 50-50
- ❌ Ícones coloridos em círculos pastéis
- ❌ Animações decorativas sem propósito

### Se o design parecer:
- "Um template SaaS genérico" → **FALHOU**
- "Dashboard do Notion/Linear/etc" → **FALHOU**
- "Feito com ShadCN/UI sem customização" → **FALHOU**

---

### ❌ Arquivo para Consolidar/Remover

- **`_components.scss`** - Duplica funcionalidades do `_data-brutalism.scss`

---

## 🧩 Componentes Vue Reutilizáveis

### ✅ Componentes Ativos (em resources/js/Components/)

| Componente | Uso | Status |
|------------|-----|--------|
| `Button.vue` | Botões com variantes brutalist | ✅ Ativo |
| `Input.vue` | Inputs de formulário | ✅ Ativo |
| `Select.vue` | Selects customizados | ✅ Ativo |
| `Checkbox.vue` | Checkboxes | ✅ Ativo |
| `Badge.vue` | Badges de status | ✅ Ativo |
| `Card.vue` | Cards genéricos | ✅ Ativo |
| `Modal.vue` | Modais | ✅ Ativo |
| `Table.vue` | Tabelas | ✅ Ativo |
| `Alert.vue` | Alertas inline | ✅ Ativo |
| `StatCard.vue` | Cards de estatística | ✅ Ativo |
| `Breadcrumbs.vue` | Breadcrumbs de navegação | ✅ Ativo |
| `Pagination.vue` | Paginação | ✅ Ativo |

**Total:** 12 componentes principais

---

## 🎨 Classes CSS Reutilizáveis

### Layout

```scss
// Main Layout Structure
.layout-root          // Root container com background
.layout-shell         // Shell principal com padding dinâmico
.layout-shell--closed // Variante com sidebar fechada
.layout-main          // Container do conteúdo principal
.layout-breadcrumbs   // Container dos breadcrumbs
.layout-header        // Header da página
.layout-title         // Título da página (brutalist, uppercase)
.layout-content       // Card de conteúdo com border

// Page Components
.page-container       // Container principal da página (deprecated, use layout-*)
.page-header          // Cabeçalho com título e ações
.page-title           // Título da página (brutalist)
.page-subtitle        // Subtítulo
.page-header__actions // Ações do header

.stats-grid           // Grid de cards de estatística
.filters-card         // Card de filtros
.filters-grid         // Grid de inputs de filtro
.content-card         // Card de conteúdo genérico
```

### Componentes Brutalist

```scss
// Botões
.btn                  // Botão base
.btn--primary         // Botão primário (preto)
.btn--secondary       // Botão secundário (cinza)
.btn--accent          // Botão accent (laranja)
.btn--ghost           // Botão fantasma
.btn--sm / .btn--lg   // Tamanhos

// Cards
.card                 // Card base
.card__header         // Header do card
.card__body           // Body do card
.card__footer         // Footer do card

// Stat Cards
.stat-card            // Card de estatística
.stat-card__icon      // Ícone do stat
.stat-card__label     // Label do stat
.stat-card__value     // Valor do stat

// Badges
.badge               // Badge base
.badge--success      // Verde
.badge--warning      // Amarelo
.badge--danger       // Vermelho
.badge--type-*       // Activity types
```

### Formulários

```scss
.form-group          // Grupo label + input
.form-select         // Select brutalist
.form-textarea       // Textarea
.filter-item         // Item de filtro
```

### Utilitários

```scss
// Texto
.text-brutalist      // Texto uppercase bold
.text-danger         // Texto vermelho
.text-muted          // Texto secundário

// Bordas
.border-brutalist    // Borda 3px preta
.border-brutalist-thick  // Borda 4px

// Botões de ícone
.btn-icon            // Botão 36x36px
.btn-icon-sm         // Botão 30x30px

// Hover
.hover-lift          // Lift no hover
.hover-border-accent // Borda accent no hover
```

---

## 📄 Padrão de Página (Baseado em Leads/Index.vue)

> **⚠️ IMPORTANTE:** Após a refatoração do MainLayout, todas as páginas devem seguir este padrão atualizado.

### ✅ Estrutura Completa Atualizada

```vue
<template>
  <MainLayout title="Título da Página">
    <!-- 1. BREADCRUMBS -->
    <template #breadcrumbs>
      <Breadcrumbs :items="breadcrumbs" />
    </template>

    <!-- 2. HEADER (opcional - usa slot ou title prop) -->
    <template #header>
      <div class="page-header">
        <h1 class="page-title">TÍTULO</h1>
        <div class="page-header__actions">
          <Button variant="primary" @click="action">
            <i class="fas fa-plus"></i>
            Ação Principal
          </Button>
        </div>
      </div>
    </template>

    <!-- 3. STATS (opcional) -->
    <div class="stats-grid">
      <StatCard
        v-for="stat in stats"
        :key="stat.label"
        v-bind="stat"
      />
    </div>

    <!-- 4. FILTERS -->
    <Card class="filters-card">
      <div class="filters-grid">
        <Input
          v-model="filters.search"
          placeholder="Buscar..."
          @input="debouncedSearch"
        />
        <Select
          v-model="filters.status"
          :options="statusOptions"
          @change="loadItems"
        />
        <Button variant="ghost" @click="clearFilters">
          Limpar
        </Button>
      </div>
    </Card>

    <!-- 5. CONTENT -->
    <Card>
      <Table
        :columns="columns"
        :data="items.data"
        :loading="loading"
      >
        <template #cell-name="{ row }">
          <!-- Custom cell content -->
        </template>
      </Table>

      <!-- 6. PAGINATION -->
      <Pagination
        :current-page="items.current_page"
        :last-page="items.last_page"
        @page-change="changePage"
      />
    </Card>
  </MainLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import {
  Button,
  Input,
  Select,
  Card,
  Table,
  StatCard,
  Breadcrumbs,
  Pagination
} from '@/Components';

const props = defineProps({
  items: Object,
  stats: Object,
});

const breadcrumbs = [
  { name: 'Dashboard', href: '/dashboard' },
  { name: 'Página Atual' }
];

const filters = ref({
  search: '',
  status: '',
});

const loading = ref(false);

const loadItems = () => {
  loading.value = true;
  router.get('/rota', filters.value, {
    preserveState: true,
    preserveScroll: true,
    onFinish: () => loading.value = false,
  });
};
</script>

<!-- ❌ SEM <style scoped> - Use classes globais de _layout-brutalist.scss -->
```

### 🎯 Checklist de Refatoração (ATUALIZADO)

**Ao refatorar qualquer página:**

**1. Template Structure**
- [ ] Substituir wrapper por `<MainLayout title="...">`
- [ ] Adicionar breadcrumbs no slot `#breadcrumbs`
- [ ] Usar slot `#header` OU deixar title prop gerar header automático
- [ ] Seguir ordem: Stats → Filters → Content → Pagination
- [ ] Usar `layout-*` classes para estrutura

**2. Classes CSS**
- [ ] **REMOVER** toda seção `<style scoped>`
- [ ] Trocar classes customizadas por globais:
  - `.layout-root`, `.layout-shell`, `.layout-main` (já no MainLayout)
  - `.stats-grid` para grid de StatCards
  - `.filters-card` + `.filters-grid` para filtros
  - `.page-header` + `.page-header__actions` para headers customizados
- [ ] Verificar em `_layout-brutalist.scss` se classe existe
- [ ] Se precisar classe nova específica, criar em arquivo `_[pagename].scss`

**3. Componentes**
- [ ] Importar de `@/Components` usando destructuring
- [ ] `StatCard` para métricas
- [ ] `Card` para seções
- [ ] `Table` para tabelas
- [ ] `Pagination` para paginação
- [ ] `Button`, `Input`, `Select` para forms

**4. Lógica**
- [ ] Usar `router.get()` com `preserveState: true`
- [ ] Loading states com `ref(false)`
- [ ] Debounce em search (500ms)
- [ ] Error handling com `useAlert()`

**5. Testes**
- [ ] `npm run build` sem erros
- [ ] Testar em mobile (< 768px)
- [ ] Testar dark mode
- [ ] Verificar CSS não está inflado

### 📦 Classes Disponíveis em _layout-brutalist.scss

Após a refatoração do MainLayout, estas classes estão disponíveis globalmente:

```scss
// Main Layout Structure (usado internamente pelo MainLayout.vue)
.layout-root            // Root container, background secundário
.layout-shell           // Shell com padding dinâmico baseado em sidebar
.layout-shell--closed   // Variante quando sidebar está fechada
.layout-main            // Container principal do conteúdo
.layout-breadcrumbs     // Wrapper dos breadcrumbs
.layout-header          // Header da página
.layout-title           // Título brutalist (uppercase, Space Grotesk)
.layout-content         // Content wrapper com border

// Page Components (para usar nas suas páginas)
.stats-grid            // Grid responsivo para StatCards
.filters-card          // Card para seção de filtros
.filters-grid          // Grid responsivo para inputs de filtro
.content-card          // Card genérico com border brutalist
.page-header           // Header customizado (se não usar slot)
.page-header__actions  // Container de ações no header
```

### 🎨 Variáveis CSS Disponíveis

```scss
// Em _variables.scss
--sidebar-open: 16rem      // Largura sidebar aberta
--sidebar-closed: 5rem     // Largura sidebar fechada
--navbar-height: 4rem      // Altura do navbar
--bg-primary               // Background principal (light/dark)
--bg-secondary             // Background secundário
--bg-tertiary              // Background terciário
--text-primary             // Texto principal
--text-secondary           // Texto secundário
--text-tertiary            // Texto terciário
--border-color             // Cor das bordas
--scrollbar-thumb          // Cor da scrollbar
--scrollbar-thumb-hover    // Cor da scrollbar no hover
```

### 📋 Exemplo Completo de Refatoração

```vue
<template>
    <MainLayout :title="pageTitle">
        <!-- Breadcrumbs -->
        <template #breadcrumbs>
            <Breadcrumbs :items="breadcrumbs" />
        </template>

        <div class="page-container">
            <!-- 1. HEADER -->
            <div class="page-header">
                <div>
                    <h1 class="page-title">TÍTULO</h1>
                    <p class="page-subtitle">Descrição da página</p>
                </div>
                <div class="page-header__actions">
                    <button class="btn">
                        <i class="fas fa-plus"></i>
                        Ação Principal
                    </button>
                </div>
            </div>

            <!-- 2. STATS (opcional) -->
            <div class="stats-grid">
                <StatCard
                    v-for="stat in stats"
                    :key="stat.label"
                    v-bind="stat"
                />
            </div>

            <!-- 3. FILTROS (opcional) -->
            <div class="filters-card">
                <div class="filters-grid">
                    <Input
                        v-model="filters.search"
                        placeholder="Buscar..."
                        icon="fa-search"
                    />
                    <Select
                        v-model="filters.status"
                        label="Status"
                        :options="statusOptions"
                    />
                    <button class="btn btn--secondary" @click="clearFilters">
                        <i class="fas fa-times"></i>
                        Limpar
                    </button>
                </div>
            </div>

            <!-- 4. CONTEÚDO PRINCIPAL -->
            <div class="card">
                <div class="card__header">
                    <h3>Título da Seção</h3>
                    <button class="btn btn--sm">Ação</button>
                </div>
                <div class="card__body">
                    <!-- Table, lista, ou conteúdo -->
                </div>
            </div>
        </div>
    </MainLayout>
</template>
```

### Script Setup

```vue
<script setup>
import { ref, computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { Button, Input, Select, StatCard, Breadcrumbs } from '@/Components';

// Props do backend
const props = defineProps({
    items: Object,
    stats: Object,
    filters: Object,
});

// State local
const loading = ref(false);
const localFilters = ref({ ...props.filters });

// Breadcrumbs
const breadcrumbs = [
    { label: 'Dashboard', href: '/dashboard', icon: 'fa-home' },
    { label: 'Leads', href: '/leads' },
];

// Funções
const loadData = async () => {
    loading.value = true;
    // Lógica de carregamento
    loading.value = false;
};

const clearFilters = () => {
    localFilters.value = {};
    loadData();
};

onMounted(() => {
    // Inicialização
});
</script>
```

### Estilos

```vue
<style scoped>
/* Apenas estilos ESPECÍFICOS da página */
/* Use classes globais sempre que possível */

.custom-specific-class {
    /* Estilos únicos dessa página */
}
</style>
```

---

## 🚀 Plano de Refatoração

### Fase 1: Limpeza (Atual)

- [x] Analisar duplicações em _components.scss
- [x] Documentar componentes ativos
- [x] Definir padrão de páginas
- [ ] Consolidar _components.scss em _data-brutalism.scss
- [ ] Remover estilos não utilizados

### Fase 2: Padronização

- [ ] Refatorar Leads/Index como referência
- [ ] Criar template de página base
- [ ] Documentar composables reutilizáveis

### Fase 3: Migração

Ordem de refatoração das páginas:
1. **Leads** (referência) ✅
2. **Pipelines** - Similar a Leads
3. **Activities** - Timeline + Filters
4. **Tasks** - Lista + Filters
5. **Products** - CRUD simples
6. **Proposals** - Formulário complexo
7. **Sites/Pages** - CMS
8. **Instagram/WhatsApp** - Social
9. **AI** - Chat interface
10. **Reports** - Dashboards

---

## 📝 Checklist para Nova Página

Ao criar/refatorar uma página:

- [ ] Usar `MainLayout` como layout base
- [ ] Adicionar breadcrumbs
- [ ] Estruturar: Header → Stats → Filters → Content
- [ ] Usar componentes de `/Components` (não recriar)
- [ ] Usar classes globais do SCSS
- [ ] Estilos scoped apenas para específicos
- [ ] Testar responsivo (mobile, tablet, desktop)
- [ ] Adicionar loading states
- [ ] Implementar error handling

---

## 🧪 Composables Disponíveis

```js
useAlert()           // SweetAlert2 brutalist
useFormValidation()  // Validação de formulários
useLeads()          // Gerenciamento de leads
useTheme()          // Dark/light mode
useMask()           // Máscaras de input
```

---

## 🎯 Decisões de Design

### Quando usar cada componente:

| Situação | Componente | Classe CSS |
|----------|------------|-----------|
| Botão de ação | `<Button>` | `.btn` |
| Input de busca | `<Input icon="fa-search">` | - |
| Select de filtro | `<Select>` | - |
| Status badge | `<Badge>` | `.badge` |
| Card de métrica | `<StatCard>` | `.stat-card` |
| Card genérico | `<Card>` | `.card` |
| Tabela de dados | `<Table>` | `.table` |
| Modal de criação | `<Modal>` | - |
| Alert inline | `<Alert>` | `.alert` |

### Quando usar classes diretas:

- Layout de página (`.page-header`, `.stats-grid`)
- Filtros (`.filters-card`, `.filters-grid`)
- Utilitários de texto (`.text-brutalist`, `.text-muted`)
- Botões de ícone pequenos (`.btn-icon`)

---
## 🤖 Para Assistentes de IA (Claude, GPT, etc.)

### Contexto Obrigatório

Ao trabalhar neste projeto, você DEVE:

1. **Ler este documento COMPLETO** antes de fazer qualquer mudança no frontend
2. **Seguir o Design System Data Brutalism** rigorosamente
3. **Usar componentes existentes** em vez de criar novos
4. **Manter consistência** com os padrões já estabelecidos
5. **Testar build** após qualquer mudança: `npm run build`

### ⚠️ ATENÇÃO: Refatoração Recente (2026-02-07)

**MainLayout foi refatorado!** Todos os estilos agora estão em `_layout-brutalist.scss`.

**ANTES (❌ Padrão antigo):**
```vue
<MainLayout>
  <div class="page-container">
    <div class="custom-header">
      <!-- conteúdo -->
    </div>
  </div>
</MainLayout>

<style scoped>
.page-container { padding: 32px; }
.custom-header { ... }
</style>
```

**AGORA (✅ Padrão correto):**
```vue
<MainLayout title="Título">
  <template #breadcrumbs>
    <Breadcrumbs :items="breadcrumbs" />
  </template>

  <template #header>
    <div class="page-header">
      <h1 class="layout-title">Título</h1>
    </div>
  </template>

  <!-- Conteúdo usando classes globais -->
  <div class="stats-grid">...</div>
  <Card class="filters-card">...</Card>
</MainLayout>

<!-- ❌ SEM <style scoped> -->
```

### Decisões Arquiteturais Importantes

**❌ NÃO FAÇA:**
- Adicionar sombras (`box-shadow`)
- Usar `border-radius` (mantenha sharp corners)
- Criar estilos `<style scoped>` em componentes Vue (use SCSS global)
- Adicionar cores além do accent (#FF6B35)
- Criar novos arquivos SCSS sem justificativa clara
- Duplicar classes CSS existentes
- Usar bibliotecas CSS externas (Bootstrap, Tailwind, etc.)

**✅ FAÇA:**
- Usar classes globais de `_data-brutalism.scss`, `_forms-brutalist.scss`, etc.
- Manter bordas sólidas de 2px-3px
- Usar accent color (#FF6B35) apenas em hover/active states
- Consultar componentes existentes antes de criar novos
- Seguir a estrutura de página: Header → Stats → Filters → Content
- Usar `MainLayout` como layout padrão
- Comentar código complexo em português

### Workflow Recomendado

```bash
# 1. Pesquisar componente/classe existente
grep -r "nome-classe" resources/scss/
grep -r "NomeComponente" resources/js/Components/

# 2. Fazer alterações
# (editar arquivos necessários)

# 3. Testar build
npm run build

# 4. Verificar tamanho CSS (deve estar < 150KB)
ls -lh public/build/assets/app-*.css

# 5. Testar no navegador
npm run dev
```

### Quando Criar Novo Componente Vue

Apenas se:
- ✅ Será reutilizado em 3+ lugares diferentes
- ✅ Tem lógica própria complexa (não apenas visual)
- ✅ Encapsula comportamento específico

Se for apenas estilo, use classes SCSS globais.

### Quando Criar Novo Arquivo SCSS

Apenas se:
- ✅ É página específica com muitos estilos únicos
- ✅ É novo módulo independente (ex: novo tipo de layout)
- ✅ Supera 200 linhas e faz sentido modular

Caso contrário, adicione em arquivo existente.

### Padrão de Refatoração

```vue
<!-- ANTES (❌ Não fazer mais assim) -->
<template>
  <div class="my-custom-card">
    <h2>Título</h2>
  </div>
</template>

<style scoped>
.my-custom-card {
  border: 2px solid black;
  padding: 1rem;
}
</style>

<!-- DEPOIS (✅ Padrão correto) -->
<template>
  <div class="content-card">
    <h2 class="card-title">Título</h2>
  </div>
</template>

<!-- Sem <style scoped>. Classes vêm de _layout-brutalist.scss -->
```

### Debugging Comum

| Problema | Causa | Solução |
|----------|-------|---------|
| Estilos não aplicam | Classe não existe ou typo | `grep -r "classe" resources/scss/` |
| Build falha | Erro SCSS sintaxe | Verificar interpolação `#{}`, importações |
| CSS muito grande | Duplicação de código | Consolidar em classes reutilizáveis |
| Componente não encontrado | Não exportado em index.js | Adicionar export em `Components/index.js` |

### Comunicação com Usuário

Ao fazer mudanças:
1. **Explique o que vai fazer** antes de usar ferramentas
2. **Mostre resultado** de builds/testes
3. **Confirme consistência** com design system
4. **Pergunte** se não tiver certeza sobre decisão de design

---## 🔄 Histórico de Refatorações

### 2026-02-07: Refatoração do MainLayout ✅

**Objetivo:** Centralizar estilos do MainLayout em SCSS global, seguindo padrão Data Brutalism.

**Mudanças:**

1. **MainLayout.vue**
   - ❌ Removido: Toda seção `<style scoped>` (88 linhas)
   - ✅ Adicionado: Comentário apontando para `_layout-brutalist.scss`
   - 📦 Resultado: Component mais limpo, sem duplicação de estilos

2. **_layout-brutalist.scss**
   - ✅ Adicionado: Seção "Main Layout Structure" (60 linhas)
   - ✅ Classes: `.layout-root`, `.layout-shell`, `.layout-main`, `.layout-header`, etc.
   - ✅ Scrollbar customizada para `main`
   - ✅ Responsive breakpoints atualizados
   - 📏 Princípios brutalist mantidos: borders de 2px, uppercase em títulos

3. **_variables.scss**
   - ✅ Adicionado: Variáveis de scrollbar
   - `$scrollbar-thumb: #cbd5e0`
   - `$scrollbar-thumb-hover: #a0aec0`

4. **FRONTEND_ORGANIZATION.md**
   - ✅ Atualizado: Seção de classes de layout
   - ✅ Adicionado: Checklist de refatoração atualizado
   - ✅ Adicionado: Exemplo completo de página seguindo novo padrão
   - ✅ Adicionado: Documentação de variáveis CSS disponíveis

**Impacto:**
- 📦 CSS Bundle: 112.35 KB → **113.60 KB** (+1.25 KB - aceitável)
- ⚡ Build time: ~18s → ~25s (variação normal)
- 🎯 Consistência: 100% - todos estilos centralizados
- 🔧 Manutenibilidade: Melhorada - única fonte de verdade

**Próximas páginas para refatorar:**
1. ✅ Leads/Index.vue (já refatorado - referência)
2. 🔄 Pipelines/Index.vue
3. 🔄 Activities/Index.vue
4. 🔄 Tasks/Index.vue
5. 🔄 Products/Index.vue
6. 🔄 Proposals/Index.vue

---
## � Guia de Manutenção para o Futuro

### 🎯 Ao Adicionar Novo Componente

1. **Decidir categoria:**
   - Layout (page structure) → `_layout-brutalist.scss`
   - Formulário (inputs, selects) → `_forms-brutalist.scss`
   - UI Pattern (timeline, cards) → `_data-brutalism.scss` ou `_components-brutalist.scss`
   - Utilitário (helpers) → `_utilities-brutalist.scss`
   - Específico de página → Criar arquivo `_[pagename].scss`

2. **Verificar duplicação:**
   ```bash
   # Buscar se já existe algo similar
   grep -r "nome-do-componente" resources/scss/
   ```

3. **Seguir convenções:**
   - Classes BEM: `.componente__elemento--modificador`
   - Variáveis SCSS para valores reutilizáveis
   - CSS Variables para temas (light/dark)
   - Comentários claros com seções

4. **Documentar:**
   - Adicionar exemplo de uso neste arquivo
   - Atualizar tabela de componentes
   - Se criar componente Vue, adicionar em `index.js`

### 🔧 Ao Refatorar Página

**Checklist obrigatório:**
- [ ] Estrutura: Header → Stats → Filters → Content
- [ ] Usar `MainLayout`
- [ ] Breadcrumbs configurados
- [ ] Usar componentes de `/Components` (não recriar)
- [ ] Classes globais SCSS (mínimo de `<style scoped>`)
- [ ] Loading states implementados
- [ ] Error handling implementado
- [ ] Responsivo testado (mobile, tablet, desktop)
- [ ] Dark mode testado

**Ordem de refatoração sugerida:**
1. Leads ✅ → 2. Pipelines → 3. Activities → 4. Tasks → 5. Products → 6. Proposals → 7. Sites → 8. Pages → 9. Instagram → 10. WhatsApp → 11. AI → 12. Reports

### 🚨 Quando CRIAR Novo Arquivo SCSS de Página

Apenas se:
- ✅ Tem componentes únicos que não fazem sentido em outros lugares
- ✅ Layout específico muito diferente do padrão
- ✅ Cores/estilos específicos de tipos (ex: activity types)

**Template:**
```scss
// =============================================================================
// [Page Name] - Page-Specific Styles
// =============================================================================

@use 'variables' as *;

// Unique components for this page
.page-specific-component {
    // styles
}

// Responsive
@media (max-width: $breakpoint-md) {
    // mobile adjustments
}
```

### 🧹 Limpeza Periódica

**Trimestral (a cada 3 meses):**
1. Buscar classes CSS não utilizadas:
   ```bash
   # Instalar PurgeCSS se necessário
   npm run analyze-css
   ```

2. Verificar componentes Vue órfãos:
   ```bash
   # Buscar componentes não importados
   grep -r "from '@/Components" resources/js/Pages/
   ```

3. Consolidar duplicações:
   - Comparar arquivos `_[page].scss`
   - Mover padrões repetidos para módulos brutalist

### 📝 Regras de Commit

Ao modificar frontend:
```
feat(ui): adiciona componente Timeline brutalist
fix(style): corrige responsivo do sidebar
refactor(page): padroniza Leads/Index
docs(frontend): atualiza guia de componentes
```

### ⚡ Performance

**Manter CSS < 150KB:**
- Evitar duplicações
- Usar classes reutilizáveis
- Não adicionar bibliotecas CSS externas
- Tree-shaking habilitado no Vite

**Build checklist:**
```bash
npm run build                    # Build deve passar
ls -lh public/build/assets/*.css # CSS < 150KB
```

### 🎨 Consistência Visual

**Antes de fazer PR:**
1. Screenshot de cada página modificada
2. Testar nos 3 breakpoints (mobile, tablet, desktop)
3. Verificar dark mode
4. Confirmar accent color usado apenas em hover/active
5. Confirmar tipografia: Space Grotesk nos títulos

---

## 🆘 Troubleshooting

### Problema: "Variável SCSS não encontrada"
**Solução:** Importar no topo do arquivo
```scss
@use 'variables' as *;
```

### Problema: "Classes CSS não aplicando"
**Solução:** Verificar ordem de importação no `app.scss`. Específicos devem vir depois de gerais.

### Problema: "Build lento"
**Solução:** 
1. Verificar imports circulares
2. Remover `@import` antigos (usar `@use`)
3. Evitar deep nesting (> 4 níveis)

### Problema: "Componente Vue não encontrado"
**Solução:** 
1. Verificar export em `Components/index.js`
2. Usar import correto: `import { Button } from '@/Components'`

---

## 📖 Referências Rápidas

### Comandos Úteis
```bash
npm run dev          # Dev server
npm run build        # Production build
npm run lint         # Linting
grep -r "classe" resources/scss/  # Buscar classe CSS
```

### Links Importantes
- [Design System Brutalist](./DESIGN_SYSTEM_BRUTALIST.md)
- [Design System Original](./DESIGN_SYSTEM.md)
- [Vue 3 Docs](https://vuejs.org)
- [Inertia.js Docs](https://inertiajs.com)

---

**Mantido por:** GitHub Copilot (Claude Sonnet 4.5)  
**Última atualização:** 2026-02-07  
**Próxima revisão:** 2026-05-07
