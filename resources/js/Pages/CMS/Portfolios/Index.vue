<template>
  <MainLayout>
    <div class="page-container">
      <!-- Header -->
      <div class="page-header">
        <div class="page-header__content">
          <h1 class="page-header__title">PORTFÓLIOS</h1>
          <p class="page-header__subtitle">Mostre seus projetos e trabalhos realizados</p>
        </div>
        <div class="page-header__actions">
          <button class="btn" @click="createPortfolio">
            <i class="fas fa-plus"></i>
            Novo Projeto
          </button>
        </div>
      </div>

      <!-- Stats -->
      <div class="grid grid--4">
        <div class="stat-card">
          <div class="stat-card__label">Total de Projetos</div>
          <div class="stat-card__value">{{ stats.total }}</div>
        </div>
        <div class="stat-card">
          <div class="stat-card__label">Publicados</div>
          <div class="stat-card__value">{{ stats.published }}</div>
        </div>
        <div class="stat-card">
          <div class="stat-card__label">Em Destaque</div>
          <div class="stat-card__value">{{ stats.featured }}</div>
        </div>
        <div class="stat-card">
          <div class="stat-card__label">Visualizações</div>
          <div class="stat-card__value">{{ stats.total_views }}</div>
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

      <!-- Portfolio Grid -->
      <div v-if="filteredPortfolios.length > 0" class="portfolio-grid">
        <div v-for="portfolio in filteredPortfolios" :key="portfolio.id" class="portfolio-card">
          <div class="portfolio-card__image" :style="`background-image: url(${portfolio.thumbnail})`">
            <div class="portfolio-card__overlay">
              <div class="overlay-actions">
                <button class="overlay-btn" @click="editPortfolio(portfolio.id)">
                  <i class="fas fa-edit"></i>
                </button>
                <button class="overlay-btn" @click="viewPortfolio(portfolio.slug)">
                  <i class="fas fa-eye"></i>
                </button>
              </div>
            </div>
            <span v-if="portfolio.is_featured" class="featured-star">
              <i class="fas fa-star"></i>
            </span>
          </div>

          <div class="portfolio-card__content">
            <div class="portfolio-card__header">
              <h3 class="portfolio-card__title">{{ portfolio.title }}</h3>
              <span :class="['badge', `badge--${getStatusColor(portfolio.status)}`]">
                {{ portfolio.status.toUpperCase() }}
              </span>
            </div>

            <p class="portfolio-card__description">{{ truncateText(portfolio.description, 100) }}</p>

            <div class="portfolio-card__tech">
              <span v-for="tech in portfolio.technologies" :key="tech" class="tech-tag">
                {{ tech }}
              </span>
            </div>

            <div class="portfolio-card__meta">
              <div class="meta-item">
                <i class="fas fa-calendar"></i>
                {{ formatDate(portfolio.completed_at) }}
              </div>
              <div class="meta-item">
                <i class="fas fa-eye"></i>
                {{ portfolio.views_count }} views
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="empty-state">
        <i class="fas fa-briefcase empty-state__icon"></i>
        <h3 class="empty-state__title">Nenhum projeto no portfólio</h3>
        <p class="empty-state__text">Adicione projetos para mostrar seu trabalho</p>
        <button class="btn" @click="createPortfolio">
          <i class="fas fa-plus"></i>
          Adicionar Primeiro Projeto
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
  portfolios: Array,
  stats: Object,
});

const activeFilter = ref('all');

const filters = computed(() => [
  { label: 'Todos', value: 'all', count: props.portfolios?.length || 0 },
  { label: 'Publicados', value: 'published', count: props.portfolios?.filter(p => p.status === 'published').length || 0 },
  { label: 'Rascunhos', value: 'draft', count: props.portfolios?.filter(p => p.status === 'draft').length || 0 },
  { label: 'Destaque', value: 'featured', count: props.portfolios?.filter(p => p.is_featured).length || 0 },
]);

const filteredPortfolios = computed(() => {
  if (!props.portfolios) return [];
  if (activeFilter.value === 'all') return props.portfolios;
  if (activeFilter.value === 'featured') return props.portfolios.filter(p => p.is_featured);
  return props.portfolios.filter(p => p.status === activeFilter.value);
});

const getStatusColor = (status) => {
  const colors = {
    published: 'success',
    draft: 'warning',
    archived: 'neutral'
  };
  return colors[status] || 'neutral';
};

const truncateText = (text, length) => {
  if (!text) return '';
  return text.length > length ? text.substring(0, length) + '...' : text;
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('pt-BR');
};

const createPortfolio = () => {
  router.visit('/cms/portfolios/create');
};

const editPortfolio = (id) => {
  router.visit(`/cms/portfolios/${id}/edit`);
};

const viewPortfolio = (slug) => {
  window.open(`/portfolio/${slug}`, '_blank');
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

.portfolio-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 24px;
}

.portfolio-card {
  border: 2px solid var(--border-color);
  background: var(--bg-primary);
  display: flex;
  flex-direction: column;
  transition: all 0.2s ease;

  &:hover {
    border-color: #FF6B35;

    .portfolio-card__overlay {
      opacity: 1;
    }
  }

  &__image {
    height: 280px;
    background-size: cover;
    background-position: center;
    position: relative;
    border-bottom: 2px solid var(--border-color);

    .featured-star {
      position: absolute;
      top: 16px;
      left: 16px;
      width: 48px;
      height: 48px;
      background: #FF6B35;
      color: var(--bg-primary);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 20px;
    }
  }

  &__overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.85);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.2s ease;

    .overlay-actions {
      display: flex;
      gap: 16px;
    }

    .overlay-btn {
      width: 64px;
      height: 64px;
      border: 2px solid #FF6B35;
      background: transparent;
      color: #FF6B35;
      font-size: 24px;
      cursor: pointer;
      transition: all 0.2s ease;

      &:hover {
        background: #FF6B35;
        color: var(--bg-primary);
      }
    }
  }

  &__content {
    padding: 24px;
    flex: 1;
    display: flex;
    flex-direction: column;
  }

  &__header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 12px;
  }

  &__title {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 20px;
    font-weight: 700;
    margin: 0;
    color: var(--text-primary);
    flex: 1;
  }

  &__description {
    font-size: 14px;
    line-height: 1.6;
    color: var(--text-secondary);
    margin: 0 0 16px;
    flex: 1;
  }

  &__tech {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    margin-bottom: 16px;

    .tech-tag {
      padding: 4px 10px;
      border: 2px solid var(--border-color);
      font-family: 'JetBrains Mono', monospace;
      font-size: 10px;
      font-weight: 700;
      text-transform: uppercase;
      color: var(--text-secondary);
    }
  }

  &__meta {
    display: flex;
    gap: 16px;
    padding-top: 16px;
    border-top: 2px solid var(--border-color);

    .meta-item {
      display: flex;
      align-items: center;
      gap: 6px;
      font-size: 12px;
      color: var(--text-secondary);

      i {
        color: #FF6B35;
      }
    }
  }
}

@media (max-width: 768px) {
  .portfolio-grid {
    grid-template-columns: 1fr;
  }
}
</style>
