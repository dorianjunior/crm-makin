# Sistema de Componentes Brutalist

## Componentes Criados/Refatorados

### ✅ Componentes Base
1. **Button** - Botão brutalist com variantes e tamanhos
2. **Input** - Campo de texto com suporte a textarea, ícones e validação
3. **Select** - Select customizado com busca e multi-seleção
4. **Checkbox** - Checkbox estilizado com estados indeterminate
5. **Card** - Card container com header, body e footer
6. **Badge** - Badge com variantes de cor e ícones
7. **Modal** - Modal responsivo com animações
8. **Table** - Tabela brutalist com loading, empty states e slots
9. **StatCard** - Card de estatísticas (já existente)

### 🎨 Design System
- SCSS modules com BEM naming
- CSS custom properties para temas
- Variantes: primary, secondary, accent, success, warning, danger, info, ghost
- Tamanhos: sm, md, lg
- Bordas de 2px
- Tipografia: Space Grotesk (display), Inter (body), JetBrains Mono (mono)
- Cor accent: #FF6B35

### 🔔 Sistema de Alertas
- **useAlert composable** - SweetAlert2 com tema brutalist
- Funções: success, error, warning, info, confirm, confirmDelete, loading, close, toast
- SCSS customizado em `_sweetalert.scss`
- Integrado com temas dark/light

### 🌓 Sistema de Temas
- **useTheme composable** - Gerenciamento de temas
- Persistência no localStorage
- Detecção de preferência do sistema
- Toggle automático entre light/dark
- Integrado no MainLayout

## Como Usar

### Button
```vue
<Button variant="accent" size="md" icon="fa-plus" @click="handleClick">
  Criar Novo
</Button>

<Button variant="danger" loading :disabled="processing">
  Deletar
</Button>
```

### Input
```vue
<Input
  v-model="form.name"
  label="Nome"
  placeholder="Digite seu nome"
  icon="fa-user"
  :error="form.errors.name"
  required
/>

<Input
  v-model="form.description"
  type="textarea"
  label="Descrição"
  :rows="5"
  :maxlength="500"
/>
```

### Select
```vue
<Select
  v-model="form.status"
  label="Status"
  :options="statusOptions"
  searchable
  :error="form.errors.status"
/>

<Select
  v-model="form.tags"
  label="Tags"
  :options="tagOptions"
  multiple
/>
```

### Checkbox
```vue
<Checkbox v-model="form.active" label="Ativo" />

<Checkbox
  v-model="form.terms"
  label="Aceito os termos"
  description="Li e concordo com os termos de uso"
/>
```

### Card
```vue
<Card title="Título do Card" subtitle="Subtítulo" hoverable>
  <template #actions>
    <Button variant="ghost" size="sm" icon="fa-edit" />
  </template>
  
  Conteúdo do card aqui
  
  <template #footer>
    <Button variant="accent">Confirmar</Button>
  </template>
</Card>
```

### Badge
```vue
<Badge variant="success">Ativo</Badge>
<Badge variant="warning" icon="fa-clock">Pendente</Badge>
<Badge variant="accent" dot>Online</Badge>
<Badge removable @remove="handleRemove">Tag</Badge>
```

### Modal
```vue
<Modal
  :show="showModal"
  title="Confirmar Ação"
  size="md"
  show-footer
  @close="showModal = false"
  @confirm="handleConfirm"
  @cancel="showModal = false"
>
  Conteúdo do modal aqui
</Modal>
```

### Table
```vue
<Table
  :columns="columns"
  :data="items"
  :loading="loading"
  hoverable
  striped
  @row-click="handleRowClick"
>
  <template #cell-actions="{ row }">
    <Button variant="ghost" size="sm" icon="fa-edit" />
    <Button variant="ghost" size="sm" icon="fa-trash" />
  </template>
</Table>
```

### useAlert
```vue
<script setup>
import { useAlert } from '@/composables/useAlert';

const alert = useAlert();

const handleDelete = async () => {
  const confirmed = await alert.confirmDelete('lead');
  
  if (confirmed) {
    try {
      await deleteLead();
      alert.success('Lead deletado com sucesso!');
    } catch (error) {
      alert.error('Erro ao deletar lead');
    }
  }
};

const handleSave = async () => {
  const loading = alert.loading('Salvando...');
  
  try {
    await save();
    loading.close();
    alert.toast('Salvo com sucesso!');
  } catch (error) {
    loading.close();
    alert.error('Erro ao salvar');
  }
};
</script>
```

### useTheme
```vue
<script setup>
import { useTheme } from '@/composables/useTheme';

const { isDark, isLight, toggleTheme, setTheme, THEMES } = useTheme();

// Alternar tema
const handleToggle = () => {
  toggleTheme();
};

// Definir tema específico
const setDarkMode = () => {
  setTheme(THEMES.DARK);
};
</script>

<template>
  <Button @click="toggleTheme">
    <i :class="isDark() ? 'fa-sun' : 'fa-moon'"></i>
    {{ isDark() ? 'Light Mode' : 'Dark Mode' }}
  </Button>
</template>
```

## Estrutura de Arquivos

```
resources/
├── js/
│   ├── Components/
│   │   ├── Alert.vue
│   │   ├── Badge.vue ✨ NOVO
│   │   ├── Breadcrumbs.vue
│   │   ├── Button.vue ♻️ REFATORADO
│   │   ├── Card.vue ✨ NOVO
│   │   ├── Checkbox.vue ✨ NOVO
│   │   ├── Input.vue ♻️ REFATORADO
│   │   ├── Modal.vue ♻️ REFATORADO
│   │   ├── Select.vue ✨ NOVO
│   │   ├── StatCard.vue
│   │   ├── Table.vue ✨ NOVO
│   │   └── index.js ✨ NOVO (export central)
│   ├── composables/
│   │   ├── useAlert.js ✨ NOVO
│   │   └── useTheme.js ✨ NOVO
│   └── Layouts/
│       ├── MainLayout.vue ♻️ REFATORADO
│       ├── Navbar.vue
│       └── Sidebar.vue
└── scss/
    ├── _variables.scss
    ├── _theme.scss
    ├── _components.scss
    ├── _utilities.scss
    ├── _mixins.scss
    ├── _base.scss
    ├── _sweetalert.scss ✨ NOVO
    └── app.scss
```

## Próximos Passos

### Task 4: Refatorar Página Leads
- [ ] Remover classes Tailwind
- [ ] Implementar Table component para listagem
- [ ] Adicionar filtros com Select e Input
- [ ] Integrar useAlert para confirmações de delete
- [ ] Usar Badge para status
- [ ] Modal para criar/editar leads

### Task 5: Refatorar Páginas CMS
- [ ] Sites: Table + Card para visualização
- [ ] Pages: Form com novos inputs
- [ ] Posts: Editor com Modal preview
- [ ] Integrar SweetAlert2 em todas ações

## Paleta de Cores

### Light Mode
- Background: #FFFFFF
- Secondary: #F5F5F5
- Text: #0F0F0F
- Border: #E0E0E0
- Accent: #FF6B35

### Dark Mode
- Background: #0F0F0F
- Secondary: #1A1A1A
- Text: #F5F5F5
- Border: #262626
- Accent: #FF6B35

## Tipografia

- **Display**: Space Grotesk (700)
- **Body**: Inter (400, 500, 600)
- **Mono**: JetBrains Mono (400)

## Variantes

- **primary**: Azul (#2563EB)
- **secondary**: Cinza neutro
- **accent**: Laranja (#FF6B35)
- **success**: Verde (#10B981)
- **warning**: Amarelo (#F59E0B)
- **danger**: Vermelho (#EF4444)
- **info**: Ciano (#06B6D4)
- **ghost**: Transparente com borda
