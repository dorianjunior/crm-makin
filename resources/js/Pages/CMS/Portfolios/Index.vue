<template>
  <MainLayout>
    <div class="portfolios-page">
      <Breadcrumbs :items="breadcrumbs" />

      <div class="page-header">
        <div>
          <h1>Portfólios</h1>
          <p class="subtitle">Gerencie os projetos e trabalhos do seu portfólio</p>
        </div>
        <Button variant="primary" @click="$inertia.visit('/cms/portfolios/create')">
          <i class="fa fa-plus"></i>
          Novo Projeto
        </Button>
      </div>

      <!-- Stats -->
      <div class="stats-grid">
        <StatCard
          title="Total de Projetos"
          :value="stats.total"
          icon="briefcase"
          color="blue"
        />
        <StatCard
          title="Publicados"
          :value="stats.published"
          icon="check-circle"
          color="green"
        />
        <StatCard
          title="Rascunhos"
          :value="stats.draft"
          icon="file"
          color="orange"
        />
        <StatCard
          title="Visualizações"
          :value="stats.views"
          icon="eye"
          color="purple"
        />
      </div>

      <!-- Filters -->
      <div class="filters-section card">
        <div class="search-bar">
          <i class="fa fa-search"></i>
          <Input
            v-model="searchQuery"
            placeholder="Buscar projetos..."
            @input="handleSearch"
          />
        </div>

        <div class="filters">
          <div class="filter-group">
            <label>Status:</label>
            <select v-model="filters.status" @change="applyFilters" class="filter-select">
              <option value="">Todos</option>
              <option value="published">Publicados</option>
              <option value="draft">Rascunhos</option>
              <option value="archived">Arquivados</option>
            </select>
          </div>

          <div class="filter-group">
            <label>Categoria:</label>
            <select v-model="filters.category" @change="applyFilters" class="filter-select">
              <option value="">Todas</option>
              <option v-for="category in categories" :key="category.id" :value="category.id">
                {{ category.name }}
              </option>
            </select>
          </div>

          <div class="filter-group">
            <label>Site:</label>
            <select v-model="filters.site" @change="applyFilters" class="filter-select">
              <option value="">Todos</option>
              <option v-for="site in sites" :key="site.id" :value="site.id">
                {{ site.name }}
              </option>
            </select>
          </div>

          <Button variant="secondary" size="small" @click="clearFilters">
            <i class="fa fa-times"></i>
            Limpar
          </Button>
        </div>
      </div>

      <!-- View Toggle -->
      <div class="view-toggle">
        <button
          @click="viewMode = 'grid'"
          :class="{ active: viewMode === 'grid' }"
          class="toggle-btn"
        >
          <i class="fa fa-th"></i>
          Grade
        </button>
        <button
          @click="viewMode = 'list'"
          :class="{ active: viewMode === 'list' }"
          class="toggle-btn"
        >
          <i class="fa fa-list"></i>
          Lista
        </button>
      </div>

      <!-- Grid View -->
      <div v-if="viewMode === 'grid'" class="portfolios-grid">
        <div v-for="portfolio in portfolios.data" :key="portfolio.id" class="portfolio-card">
          <div class="portfolio-image">
            <img v-if="portfolio.featured_image" :src="portfolio.featured_image" :alt="portfolio.title">
            <div v-else class="image-placeholder">
              <i class="fa fa-image"></i>
            </div>
            <div class="portfolio-overlay">
              <Button variant="primary" size="small" @click="editPortfolio(portfolio)">
                <i class="fa fa-edit"></i>
              </Button>
              <Button variant="secondary" size="small" @click="viewPortfolio(portfolio)">
                <i class="fa fa-eye"></i>
              </Button>
              <Button variant="danger" size="small" @click="deletePortfolio(portfolio)">
                <i class="fa fa-trash"></i>
              </Button>
            </div>
            <span class="status-badge" :class="portfolio.status">
              {{ getStatusLabel(portfolio.status) }}
            </span>
          </div>
          <div class="portfolio-content">
            <h3>{{ portfolio.title }}</h3>
            <p class="portfolio-excerpt">{{ portfolio.excerpt }}</p>
            <div class="portfolio-meta">
              <span class="meta-item">
                <i class="fa fa-folder"></i>
                {{ portfolio.category?.name || 'Sem categoria' }}
              </span>
              <span class="meta-item">
                <i class="fa fa-calendar"></i>
                {{ formatDate(portfolio.created_at) }}
              </span>
              <span class="meta-item">
                <i class="fa fa-eye"></i>
                {{ portfolio.views || 0 }} visualizações
              </span>
            </div>
            <div class="portfolio-tags">
              <span v-for="tag in portfolio.tags" :key="tag.id" class="tag">
                {{ tag.name }}
              </span>
            </div>
          </div>
        </div>

        <div v-if="!portfolios.data.length" class="empty-state">
          <i class="fa fa-briefcase"></i>
          <h3>Nenhum projeto encontrado</h3>
          <p>Comece criando seu primeiro projeto de portfólio</p>
          <Button variant="primary" @click="$inertia.visit('/cms/portfolios/create')">
            <i class="fa fa-plus"></i>
            Criar Primeiro Projeto
          </Button>
        </div>
      </div>

      <!-- List View -->
      <div v-if="viewMode === 'list'" class="card">
        <div class="table-responsive">
          <table class="portfolios-table">
            <thead>
              <tr>
                <th>Projeto</th>
                <th>Categoria</th>
                <th>Cliente</th>
                <th>Status</th>
                <th>Data</th>
                <th>Visualizações</th>
                <th class="text-right">Ações</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="portfolio in portfolios.data" :key="portfolio.id">
                <td>
                  <div class="portfolio-info">
                    <img v-if="portfolio.featured_image" :src="portfolio.featured_image" :alt="portfolio.title" class="thumbnail">
                    <div class="thumbnail-placeholder" v-else>
                      <i class="fa fa-image"></i>
                    </div>
                    <div>
                      <strong>{{ portfolio.title }}</strong>
                      <span class="portfolio-url">{{ portfolio.slug }}</span>
                    </div>
                  </div>
                </td>
                <td>{{ portfolio.category?.name || '-' }}</td>
                <td>{{ portfolio.client || '-' }}</td>
                <td>
                  <span class="status-badge" :class="portfolio.status">
                    {{ getStatusLabel(portfolio.status) }}
                  </span>
                </td>
                <td>{{ formatDate(portfolio.created_at) }}</td>
                <td>{{ portfolio.views || 0 }}</td>
                <td class="text-right">
                  <div class="action-buttons">
                    <button @click="editPortfolio(portfolio)" class="action-btn" title="Editar">
                      <i class="fa fa-edit"></i>
                    </button>
                    <button @click="viewPortfolio(portfolio)" class="action-btn" title="Ver">
                      <i class="fa fa-eye"></i>
                    </button>
                    <button @click="duplicatePortfolio(portfolio)" class="action-btn" title="Duplicar">
                      <i class="fa fa-copy"></i>
                    </button>
                    <button @click="deletePortfolio(portfolio)" class="action-btn danger" title="Excluir">
                      <i class="fa fa-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="pagination">
          <div class="pagination-info">
            Exibindo {{ portfolios.from }} a {{ portfolios.to }} de {{ portfolios.total }} projetos
          </div>
          <div class="pagination-buttons">
            <button
              v-for="page in paginationPages"
              :key="page"
              @click="changePage(page)"
              :class="{ active: page === portfolios.current_page }"
              :disabled="page === '...'"
            >
              {{ page }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import Button from '@/Components/Button.vue';
import Input from '@/Components/Input.vue';
import StatCard from '@/Components/StatCard.vue';

const props = defineProps({
  portfolios: Object,
  categories: Array,
  sites: Array,
  stats: Object,
  filters: Object
});

const searchQuery = ref('');
const viewMode = ref('grid');

const filters = ref({
  status: props.filters?.status || '',
  category: props.filters?.category || '',
  site: props.filters?.site || ''
});

const breadcrumbs = [
  { label: 'CMS', url: '/cms' },
  { label: 'Portfólios' }
];

const paginationPages = computed(() => {
  const current = props.portfolios.current_page;
  const last = props.portfolios.last_page;
  const pages = [];

  if (last <= 7) {
    for (let i = 1; i <= last; i++) pages.push(i);
  } else {
    if (current <= 3) {
      for (let i = 1; i <= 5; i++) pages.push(i);
      pages.push('...');
      pages.push(last);
    } else if (current >= last - 2) {
      pages.push(1);
      pages.push('...');
      for (let i = last - 4; i <= last; i++) pages.push(i);
    } else {
      pages.push(1);
      pages.push('...');
      for (let i = current - 1; i <= current + 1; i++) pages.push(i);
      pages.push('...');
      pages.push(last);
    }
  }

  return pages;
});

const handleSearch = () => {
  router.get('/cms/portfolios', { search: searchQuery.value, ...filters.value }, {
    preserveState: true,
    preserveScroll: true
  });
};

const applyFilters = () => {
  router.get('/cms/portfolios', { search: searchQuery.value, ...filters.value }, {
    preserveState: true,
    preserveScroll: true
  });
};

const clearFilters = () => {
  filters.value = { status: '', category: '', site: '' };
  searchQuery.value = '';
  router.get('/cms/portfolios', {}, { preserveState: true });
};

const changePage = (page) => {
  if (page === '...') return;
  router.get('/cms/portfolios', {
    search: searchQuery.value,
    ...filters.value,
    page
  }, {
    preserveState: true,
    preserveScroll: true
  });
};

const editPortfolio = (portfolio) => {
  router.visit(`/cms/portfolios/${portfolio.id}/edit`);
};

const viewPortfolio = (portfolio) => {
  window.open(`/portfolio/${portfolio.slug}`, '_blank');
};

const duplicatePortfolio = (portfolio) => {
  if (confirm(`Duplicar o projeto "${portfolio.title}"?`)) {
    router.post(`/cms/portfolios/${portfolio.id}/duplicate`, {}, {
      preserveState: true,
      preserveScroll: true
    });
  }
};

const deletePortfolio = (portfolio) => {
  if (confirm(`Tem certeza que deseja excluir o projeto "${portfolio.title}"?`)) {
    router.delete(`/cms/portfolios/${portfolio.id}`, {
      preserveState: true,
      preserveScroll: true
    });
  }
};

const getStatusLabel = (status) => {
  const labels = {
    published: 'Publicado',
    draft: 'Rascunho',
    archived: 'Arquivado'
  };
  return labels[status] || status;
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('pt-BR');
};
</script>

<style scoped lang="scss">
.portfolios-page {
  padding: 2rem;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 2rem;

  h1 {
    margin: 0 0 0.5rem 0;
    color: var(--text-primary);
  }

  .subtitle {
    margin: 0;
    color: var(--text-secondary);
  }
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.filters-section {
  margin-bottom: 2rem;
  padding: 1.5rem;

  .search-bar {
    position: relative;
    margin-bottom: 1.5rem;

    i {
      position: absolute;
      left: 1rem;
      top: 50%;
      transform: translateY(-50%);
      color: var(--text-secondary);
    }

    input {
      padding-left: 3rem;
    }
  }

  .filters {
    display: flex;
    gap: 1rem;
    align-items: flex-end;
    flex-wrap: wrap;
  }

  .filter-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;

    label {
      font-size: 0.85rem;
      font-weight: 500;
      color: var(--text-secondary);
    }
  }

  .filter-select {
    padding: 0.75rem;
    border: 1px solid var(--border-color);
    border-radius: 4px;
    background: var(--bg-primary);
    color: var(--text-primary);
    min-width: 150px;

    &:focus {
      outline: none;
      border-color: var(--color-primary);
    }
  }
}

.view-toggle {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 2rem;

  .toggle-btn {
    padding: 0.75rem 1.5rem;
    border: 1px solid var(--border-color);
    background: var(--bg-primary);
    border-radius: 4px;
    cursor: pointer;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.2s;

    &:hover {
      background: var(--bg-tertiary);
      border-color: var(--color-primary);
    }

    &.active {
      background: var(--color-primary);
      color: white;
      border-color: var(--color-primary);
    }
  }
}

.card {
  background: var(--bg-primary);
  border: 1px solid var(--border-color);
  border-radius: 8px;
  overflow: hidden;
}

// Grid View
.portfolios-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 2rem;
}

.portfolio-card {
  background: var(--bg-primary);
  border: 1px solid var(--border-color);
  border-radius: 8px;
  overflow: hidden;
  transition: all 0.3s;

  &:hover {
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    transform: translateY(-4px);

    .portfolio-overlay {
      opacity: 1;
    }
  }

  .portfolio-image {
    position: relative;
    width: 100%;
    height: 250px;
    overflow: hidden;
    background: var(--bg-tertiary);

    img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.3s;
    }

    .image-placeholder {
      width: 100%;
      height: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--text-tertiary);

      i {
        font-size: 4rem;
        opacity: 0.3;
      }
    }

    .portfolio-overlay {
      position: absolute;
      inset: 0;
      background: rgba(0, 0, 0, 0.7);
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 1rem;
      opacity: 0;
      transition: opacity 0.3s;
    }

    .status-badge {
      position: absolute;
      top: 1rem;
      right: 1rem;
      padding: 0.5rem 1rem;
      border-radius: 20px;
      font-size: 0.85rem;
      font-weight: 500;

      &.published {
        background: var(--color-success);
        color: white;
      }

      &.draft {
        background: var(--color-warning);
        color: white;
      }

      &.archived {
        background: var(--text-tertiary);
        color: white;
      }
    }
  }

  .portfolio-content {
    padding: 1.5rem;

    h3 {
      margin: 0 0 0.75rem 0;
      color: var(--text-primary);
      font-size: 1.25rem;
    }

    .portfolio-excerpt {
      margin: 0 0 1rem 0;
      color: var(--text-secondary);
      font-size: 0.9rem;
      line-height: 1.5;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }

    .portfolio-meta {
      display: flex;
      flex-wrap: wrap;
      gap: 1rem;
      margin-bottom: 1rem;
      padding-bottom: 1rem;
      border-bottom: 1px solid var(--border-color);

      .meta-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.85rem;
        color: var(--text-secondary);

        i {
          color: var(--color-primary);
        }
      }
    }

    .portfolio-tags {
      display: flex;
      flex-wrap: wrap;
      gap: 0.5rem;

      .tag {
        padding: 0.25rem 0.75rem;
        background: var(--bg-tertiary);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        font-size: 0.8rem;
        color: var(--text-secondary);
      }
    }
  }
}

// List View
.table-responsive {
  overflow-x: auto;
}

.portfolios-table {
  width: 100%;
  border-collapse: collapse;

  thead {
    background: var(--bg-secondary);

    th {
      padding: 1rem 1.5rem;
      text-align: left;
      font-weight: 600;
      color: var(--text-primary);
      font-size: 0.9rem;
      white-space: nowrap;

      &.text-right {
        text-align: right;
      }
    }
  }

  tbody {
    tr {
      border-bottom: 1px solid var(--border-color);
      transition: background 0.2s;

      &:hover {
        background: var(--bg-secondary);
      }

      td {
        padding: 1rem 1.5rem;
        color: var(--text-primary);

        &.text-right {
          text-align: right;
        }
      }
    }
  }
}

.portfolio-info {
  display: flex;
  align-items: center;
  gap: 1rem;

  .thumbnail,
  .thumbnail-placeholder {
    width: 60px;
    height: 60px;
    border-radius: 4px;
    flex-shrink: 0;
  }

  .thumbnail {
    object-fit: cover;
  }

  .thumbnail-placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--bg-tertiary);
    color: var(--text-tertiary);

    i {
      font-size: 1.5rem;
      opacity: 0.5;
    }
  }

  strong {
    display: block;
    margin-bottom: 0.25rem;
  }

  .portfolio-url {
    display: block;
    font-size: 0.85rem;
    color: var(--text-secondary);
  }
}

.status-badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  font-size: 0.85rem;
  font-weight: 500;

  &.published {
    background: var(--color-success-light);
    color: var(--color-success);
  }

  &.draft {
    background: var(--color-warning-light);
    color: var(--color-warning);
  }

  &.archived {
    background: var(--bg-tertiary);
    color: var(--text-tertiary);
  }
}

.action-buttons {
  display: flex;
  gap: 0.5rem;
  justify-content: flex-end;

  .action-btn {
    padding: 0.5rem 0.75rem;
    border: 1px solid var(--border-color);
    background: var(--bg-primary);
    border-radius: 4px;
    cursor: pointer;
    color: var(--text-primary);
    transition: all 0.2s;

    &:hover {
      background: var(--bg-tertiary);
      border-color: var(--color-primary);
      color: var(--color-primary);
    }

    &.danger:hover {
      border-color: var(--color-error);
      color: var(--color-error);
    }
  }
}

.pagination {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.5rem;
  border-top: 1px solid var(--border-color);

  .pagination-info {
    color: var(--text-secondary);
    font-size: 0.9rem;
  }

  .pagination-buttons {
    display: flex;
    gap: 0.5rem;

    button {
      padding: 0.5rem 1rem;
      border: 1px solid var(--border-color);
      background: var(--bg-primary);
      border-radius: 4px;
      cursor: pointer;
      color: var(--text-primary);
      transition: all 0.2s;

      &:hover:not(:disabled) {
        background: var(--bg-tertiary);
        border-color: var(--color-primary);
      }

      &.active {
        background: var(--color-primary);
        color: white;
        border-color: var(--color-primary);
      }

      &:disabled {
        cursor: not-allowed;
        opacity: 0.5;
      }
    }
  }
}

.empty-state {
  text-align: center;
  padding: 4rem 2rem;
  color: var(--text-secondary);

  i {
    font-size: 4rem;
    opacity: 0.3;
    margin-bottom: 1rem;
  }

  h3 {
    margin: 0 0 0.5rem 0;
    color: var(--text-primary);
  }

  p {
    margin: 0 0 2rem 0;
  }
}

@media (max-width: 768px) {
  .portfolios-grid {
    grid-template-columns: 1fr;
  }

  .filters {
    flex-direction: column;
    align-items: stretch !important;

    .filter-group {
      width: 100%;
    }
  }
}
</style>
