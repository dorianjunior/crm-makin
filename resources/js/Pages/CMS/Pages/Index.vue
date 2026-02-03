<template>
  <MainLayout>
    <div class="page-container">
      <!-- Header -->
      <div class="page-header">
        <div class="page-header__content">
          <h1 class="page-header__title">PÁGINAS CMS</h1>
          <p class="page-header__subtitle">Gerencie páginas estáticas do seu site</p>
        </div>
        <div class="page-header__actions">
          <button class="btn btn--secondary" @click="showFilters = !showFilters">
            <i class="fas fa-filter"></i>
            Filtros
          </button>
          <button class="btn" @click="createPage">
            <i class="fas fa-plus"></i>
            Nova Página
          </button>
        </div>
      </div>

      <!-- Stats -->
      <div class="grid grid--5">
        <div class="stat-card">
          <div class="stat-card__label">Total</div>
          <div class="stat-card__value">{{ stats.total }}</div>
        </div>
        <div class="stat-card">
          <div class="stat-card__label">Publicadas</div>
          <div class="stat-card__value">{{ stats.published }}</div>
        </div>
        <div class="stat-card">
          <div class="stat-card__label">Rascunhos</div>
          <div class="stat-card__value">{{ stats.draft }}</div>
        </div>
        <div class="stat-card">
          <div class="stat-card__label">Agendadas</div>
          <div class="stat-card__value">{{ stats.scheduled }}</div>
        </div>
        <div class="stat-card">
          <div class="stat-card__label">Visualizações</div>
          <div class="stat-card__value">{{ stats.views }}</div>
        </div>
      </div>

      <!-- Filters -->
      <div class="filters">
        <button
          v-for="filter in filters"
          :key="filter.value"
          class="filter-btn"
          :class="{ 'filter-btn--active': activeFilter === filter.value }"
          @click="activeFilter = filter.value"
        >
          {{ filter.label }} ({{ filter.count }})
        </button>
      </div>

      <!-- Pages Table -->
      <div v-if="filteredPages.length > 0" class="table">
        <table>
          <thead>
            <tr>
              <th>TÍTULO</th>
              <th>SLUG</th>
              <th>STATUS</th>
              <th>SITE</th>
              <th>AUTOR</th>
              <th>VISUALIZAÇÕES</th>
              <th>ÚLTIMA ATUALIZAÇÃO</th>
              <th>AÇÕES</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="page in filteredPages" :key="page.id">
              <td>
                <div class="page-title-cell">
                  <i :class="getPageIcon(page.template)"></i>
                  {{ page.title }}
                </div>
              </td>
              <td>
                <span class="code-text">{{ page.slug }}</span>
              </td>
              <td>
                <span :class="['badge', `badge--${getStatusColor(page.status)}`]">
                  {{ page.status.toUpperCase() }}
                </span>
              </td>
              <td>{{ page.site_name }}</td>
              <td>{{ page.author_name }}</td>
              <td>
                <div class="views-cell">
                  <i class="fas fa-eye"></i>
                  {{ page.views_count }}
                </div>
              </td>
              <td>{{ formatDate(page.updated_at) }}</td>
              <td>
                <div class="actions-cell">
                  <button class="action-btn" @click="editPage(page.id)" title="Editar">
                    <i class="fas fa-edit"></i>
                  </button>
                  <button class="action-btn" @click="viewPage(page.slug)" title="Visualizar">
                    <i class="fas fa-eye"></i>
                  </button>
                  <button class="action-btn action-btn--danger" @click="deletePage(page.id)" title="Excluir">
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Empty State -->
      <div v-else class="empty-state">
        <i class="fas fa-file-alt empty-state__icon"></i>
        <h3 class="empty-state__title">Nenhuma página encontrada</h3>
        <p class="empty-state__text">Crie sua primeira página para começar</p>
        <button class="btn" @click="createPage">
          <i class="fas fa-plus"></i>
          Criar Primeira Página
        </button>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';

const props = defineProps({
  pages: Array,
  stats: Object,
});

const activeFilter = ref('all');
const showFilters = ref(false);

const filters = computed(() => [
  { label: 'Todas', value: 'all', count: props.pages?.length || 0 },
  { label: 'Publicadas', value: 'published', count: props.pages?.filter(p => p.status === 'published').length || 0 },
  { label: 'Rascunhos', value: 'draft', count: props.pages?.filter(p => p.status === 'draft').length || 0 },
  { label: 'Agendadas', value: 'scheduled', count: props.pages?.filter(p => p.status === 'scheduled').length || 0 },
]);

const filteredPages = computed(() => {
  if (!props.pages) return [];
  if (activeFilter.value === 'all') return props.pages;
  return props.pages.filter(p => p.status === activeFilter.value);
});

const getPageIcon = (template) => {
  const icons = {
    'home': 'fas fa-home',
    'about': 'fas fa-info-circle',
    'contact': 'fas fa-envelope',
    'services': 'fas fa-briefcase',
    'default': 'fas fa-file-alt'
  };
  return icons[template] || icons.default;
};

const getStatusColor = (status) => {
  const colors = {
    published: 'success',
    draft: 'warning',
    scheduled: 'info',
    archived: 'neutral'
  };
  return colors[status] || 'neutral';
};

const formatDate = (date) => {
  return new Date(date).toLocaleString('pt-BR');
};

const createPage = () => {
  router.visit('/cms/pages/create');
};

const editPage = (id) => {
  router.visit(`/cms/pages/${id}/edit`);
};

const viewPage = (slug) => {
  window.open(`/page/${slug}`, '_blank');
};

const deletePage = (id) => {
  if (confirm('Tem certeza que deseja excluir esta página?')) {
    router.delete(`/cms/pages/${id}`);
  }
};
</script>

<style scoped lang="scss">
.page-container {
  padding: 32px;
}

.filters {
  display: flex;
  gap: 16px;
  margin: 32px 0;
  flex-wrap: wrap;
}

.filter-btn {
  padding: 12px 24px;
  border: 2px solid var(--border-color);
  background: var(--bg-primary);
  color: var(--text-primary);
  font-family: 'Space Grotesk', sans-serif;
  font-size: 14px;
  font-weight: 700;
  text-transform: uppercase;
  cursor: pointer;
  transition: all 0.2s ease;

  &:hover {
    border-color: #FF6B35;
    transform: translateX(2px);
  }

  &--active {
    background: #FF6B35;
    color: var(--bg-primary);
    border-color: #FF6B35;
  }
}

.page-title-cell {
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 700;

  i {
    color: #FF6B35;
  }
}

.code-text {
  font-family: 'JetBrains Mono', monospace;
  font-size: 12px;
  color: var(--text-secondary);
}

.views-cell {
  display: flex;
  align-items: center;
  gap: 6px;

  i {
    color: #FF6B35;
  }
}

.actions-cell {
  display: flex;
  gap: 8px;
}

.action-btn {
  width: 32px;
  height: 32px;
  border: 2px solid var(--border-color);
  background: var(--bg-primary);
  color: var(--text-primary);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;

  &:hover {
    border-color: #FF6B35;
    color: #FF6B35;
  }

  &--danger:hover {
    border-color: #dc3545;
    color: #dc3545;
  }
}

@media (max-width: 1024px) {
  .table {
    overflow-x: auto;
  }
}
</style>
