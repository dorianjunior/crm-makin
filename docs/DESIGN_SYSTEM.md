# Design System - Data Brutalism

## 🎨 Direção Estética

**Nome:** Data Brutalism  
**DFII Score:** 13/15 (Excellent)

### Filosofia

Dashboard CRM que combina **tipografia oversized estrutural** com **layout assimétrico** e **paleta monocromática** + um único accent color vibrante. Evita completamente o "generic SaaS UI" através de:

- Números gigantes (64px) que dominam visualmente os stat cards
- Grid assimétrico (3-2 split em vez de 2-2)
- Bordas sólidas de 2px em vez de sombras sutis
- Um único ponto de cor: laranja vibrante (#FF6B35)
- Tipografia estrutural com Space Grotesk

---

## 🎯 Âncora de Diferenciação

> **"Se você tirar screenshot sem logo, reconhece pelos números gigantes de 64px, bordas quadradas de 2px, e aquele laranja vibrante que surge apenas no hover."**

---

## 🎨 Paleta de Cores

### Monocromático (Cinzas)

```scss
$primary-dark: #0a0a0a    // Preto profundo
$primary: #1a1a1a          // Preto principal
$primary-light: #2a2a2a    // Cinza escuro
$secondary: #3a3a3a        // Cinza médio
```

### Accent (Único)

```scss
$accent: #FF6B35           // Laranja vibrante - O ÚNICO acento de cor
$accent-dark: #E85A28      // Hover state
$accent-light: #FFB3A0     // Disabled/light state
```

### Light Theme

```scss
$light-bg-primary: #ffffff
$light-bg-secondary: #fafafa
$light-text-primary: #0a0a0a
$light-text-secondary: #525252
$light-text-tertiary: #a3a3a3
$light-border: #e5e5e5
$light-border-bold: #262626
```

---

## ✍️ Tipografia

### Hierarquia

```scss
$font-display: 'Space Grotesk'  // Headers, números, labels importantes
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

## 🧱 Componentes

### 1. Stat Card Brutalist (`.stat-card-brutalist`)

**Características:**
- Borda sólida de 2px
- Números de 64px dominam o card
- Ícone posicionado absolutamente (top-right)
- Hover: linha accent laranja vertical cresce de 0 a 100%
- Hover: número muda para accent color
- Hover: ícone rotaciona 5° e escala 1.1x

**Uso:**
```vue
<StatCard
  title="Leads Ativos"
  :value="152"
  icon="fa-users"
/>
```

---

### 2. Action Block Brutalist (`.action-block-brutalist`)

**Características:**
- Ícone quadrado de 56px com borda 2px
- Texto uppercase em Space Grotesk
- Hover: background muda para accent laranja completo
- Hover: translateX(4px) para criar sensação de "empurrar"
- Sem border-radius (brutalismo)

**Uso:**
```vue
<Link href="/leads/create" class="action-block-brutalist">
  <div class="action-icon">
    <i class="fas fa-plus"></i>
  </div>
  <span class="action-label">Novo Lead</span>
</Link>
```

---

### 3. Activity Item Brutalist (`.activity-item-brutalist`)

**Características:**
- Borda vertical esquerda de 2px
- Ícone circular absoluto intercepta a borda
- Hover: borda muda para accent color
- Timestamps em JetBrains Mono uppercase

---

### 4. Section Header Brutalist (`.section-header-brutalist`)

**Características:**
- Ícone em bloco quadrado accent laranja
- Título em Space Grotesk 800 uppercase
- Tamanho 1.5rem com letter-spacing negativo

---

## 📐 Layout

### Grid Assimétrico

```scss
.grid-brutalist.stats {
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
}
```

**Dashboard Layout:**
- Stats: 4 colunas iguais (mobile: 1 coluna)
- Content area: 3-2 split (Timeline 60% | Metrics 40%)
- Quick Actions: 4 colunas (mobile: 1 coluna)

---

## 🎬 Animações

### Princípios
- **Purposeful, not decorative**
- Cubic-bezier(0.4, 0, 0.2, 1) - ease-out "material"
- 300ms duration padrão
- Stagger entrance: 50ms delay entre cards

### Stat Cards Entrance

```scss
@keyframes slideInUp {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

.grid-brutalist.stats > * {
  animation: slideInUp 0.5s backwards;
  &:nth-child(1) { animation-delay: 0.05s; }
  &:nth-child(2) { animation-delay: 0.1s; }
  &:nth-child(3) { animation-delay: 0.15s; }
  &:nth-child(4) { animation-delay: 0.2s; }
}
```

### Hover States

1. **Stat Card:** Barra accent cresce verticalmente + número muda cor
2. **Action Block:** Background fill accent + translateX
3. **Activity Item:** Borda muda cor + ícone escala 1.1x

---

## ❌ Anti-Patterns (NUNCA FAZER)

### Evitar Completamente:

❌ Border-radius (exceto círculos perfeitos para ícones)  
❌ Box-shadows sutis (use borders sólidas)  
❌ Múltiplas cores de accent  
❌ Gradientes  
❌ Tipografia Inter/Roboto/system-ui como display  
❌ Layout simétrico 50-50  
❌ Ícones coloridos em círculos pastéis  
❌ Animações decorativas sem propósito  

### Se o design parecer:
- "Um template SaaS genérico" → FALHOU
- "Dashboard do Notion/Linear/etc" → FALHOU
- "Feito com ShadCN/UI sem customização" → FALHOU

---

## ✅ Checklist de Implementação

Antes de finalizar um novo componente:

- [ ] Usa Space Grotesk para títulos/labels importantes?
- [ ] Tem bordas sólidas de 2px (não sombras)?
- [ ] Accent color aparece apenas em hover/active?
- [ ] Animação tem propósito claro?
- [ ] Layout é assimétrico ou quebra expectativas?
- [ ] Tipografia é estrutural (não decorativa)?
- [ ] Componente é reconhecível visualmente?

---

## 🔧 Integração com Tailwind

As variáveis CSS estão sincronizadas:

```css
/* tailwind.css */
@theme {
  --color-accent: #FF6B35;
  --font-display: 'Space Grotesk', system-ui, sans-serif;
}
```

Uso em Vue:

```vue
<div class="text-accent font-display">
  <!-- Usa accent color e Space Grotesk -->
</div>
```

---

## 📦 Arquivos do Sistema

- `resources/scss/_variables.scss` - Cores, tipografia, espaçamentos
- `resources/scss/_components.scss` - Componentes brutalist
- `resources/scss/_theme.scss` - CSS variables light/dark
- `resources/css/tailwind.css` - Configuração Tailwind v4
- `resources/scss/app.scss` - Entry point + animações globais

---

## 🚀 Próximos Passos

### Componentes a Criar:
1. **Table Brutalist** - Tabelas com bordas grossas, headers accent
2. **Modal Brutalist** - Full-screen overlay, entrada dramática
3. **Form Brutalist** - Inputs com bordas 2px, labels uppercase
4. **Chart Brutalist** - Gráficos com linhas grossas, accent único

### Páginas a Redesenhar:
1. Leads List
2. CMS Pages
3. Settings
4. Reports

---

## 💡 Inspiração Conceitual

- Swiss Design (grid estrutural, tipografia dominante)
- Brutalist Architecture (honestidade material, funcionalidade exposta)
- Data Dashboards industriais (números primeiro, decoração nunca)
- Revistas editoriais modernas (layouts assimétricos, tipografia bold)

**Não é cópia visual, é absorção de princípios.**

---

**Última atualização:** 2026-02-03  
**Designer-Engineer:** GitHub Copilot (Claude Sonnet 4.5)  
**DFII Score:** 13/15 (Excellent)
