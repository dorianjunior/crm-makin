<template>
  <MainLayout title="Sites CMS">
    <div class="page-container">
      <!-- Header -->
      <div class="page-header">
        <div class="page-header__content">
          <h1 class="page-header__title">SITES CMS</h1>
          <p class="page-header__subtitle">Gerencie seus sites e conteúdos</p>
        </div>
        <div class="page-header__actions">
          <button class="btn" @click="createSite">
            <i class="fas fa-plus"></i>
            Novo Site
          </button>
        </div>
      </div>

      <!-- Stats -->
      <div class="grid grid--4">
        <div class="stat-card">
          <div class="stat-card__label">Total de Sites</div>
          <div class="stat-card__value">{{ stats.total }}</div>
        </div>
        <div class="stat-card">
          <div class="stat-card__label">Sites Ativos</div>
          <div class="stat-card__value">{{ stats.active }}</div>
        </div>
        <div class="stat-card">
          <div class="stat-card__label">Total de Páginas</div>
          <div class="stat-card__value">{{ stats.total_pages }}</div>
        </div>
        <div class="stat-card">
          <div class="stat-card__label">Conteúdos Publicados</div>
          <div class="stat-card__value">{{ stats.published }}</div>
        </div>
      </div>

      <!-- Sites Grid -->
      <div v-if="sites && sites.length > 0" class="sites-grid">
        <div v-for="site in sites" :key="site.id" class="site-card">
          <div class="site-card__header">
            <div class="site-card__info">
              <h3 class="site-card__name">{{ site.name }}</h3>
              <a :href="site.domain" target="_blank" class="site-card__domain">
                <i class="fas fa-external-link-alt"></i>
                {{ site.domain }}
              </a>
            </div>
            <span :class="['badge', `badge--${site.is_active ? 'success' : 'danger'}`]">
              {{ site.is_active ? 'ATIVO' : 'INATIVO' }}
            </span>
          </div>

          <div class="site-card__body">
            <p v-if="site.description" class="site-card__description">
              {{ site.description }}
            </p>

            <div class="site-card__meta">
              <div class="meta-item">
                <i class="fas fa-file-alt"></i>
                {{ site.pages_count }} páginas
              </div>
              <div class="meta-item">
                <i class="fas fa-blog"></i>
                {{ site.posts_count }} posts
              </div>
              <div class="meta-item">
                <i class="fas fa-briefcase"></i>
                {{ site.portfolios_count }} portfólios
              </div>
            </div>

            <div class="site-card__tech">
              <span class="tech-badge">{{ site.theme }}</span>
              <span v-if="site.custom_domain" class="tech-badge">Custom Domain</span>
              <span v-if="site.ssl_enabled" class="tech-badge">SSL</span>
            </div>
          </div>

          <div class="site-card__actions">
            <button class="btn btn--sm" @click="manageSite(site.id)">
              <i class="fas fa-cog"></i>
              Gerenciar
            </button>
            <button class="btn btn--sm btn--secondary" @click="editSite(site.id)">
              <i class="fas fa-edit"></i>
              Editar
            </button>
            <button class="btn btn--sm btn--secondary" @click="viewSite(site.domain)">
              <i class="fas fa-eye"></i>
              Visualizar
            </button>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="empty-state">
        <i class="fas fa-globe empty-state__icon"></i>
        <h3 class="empty-state__title">Nenhum site criado</h3>
        <p class="empty-state__text">Crie seu primeiro site para começar a gerenciar conteúdo</p>
        <button class="btn" @click="createSite">
          <i class="fas fa-plus"></i>
          Criar Primeiro Site
        </button>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';

const props = defineProps({
  sites: Array,
  stats: Object,
});

const createSite = () => {
  router.visit('/cms/sites/create');
};

const manageSite = (id) => {
  router.visit(`/cms/sites/${id}/manage`);
};

const editSite = (id) => {
  router.visit(`/cms/sites/${id}/edit`);
};

const viewSite = (domain) => {
  window.open(domain, '_blank');
};
</script>

<style scoped lang="scss">
.page-container {
  padding: 32px;
}

.sites-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
  gap: 24px;
  margin-top: 32px;
}

.site-card {
  border: 2px solid var(--border-color);
  background: var(--bg-primary);
  display: flex;
  flex-direction: column;
  transition: all 0.2s ease;

  &:hover {
    border-color: #FF6B35;
    transform: translateY(-2px);
  }

  &__header {
    padding: 24px;
    border-bottom: 2px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 16px;
  }

  &__info {
    flex: 1;
    min-width: 0;
  }

  &__name {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 24px;
    font-weight: 700;
    margin: 0 0 8px;
    color: var(--text-primary);
  }

  &__domain {
    font-family: 'JetBrains Mono', monospace;
    font-size: 13px;
    color: #FF6B35;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: transform 0.2s ease;

    &:hover {
      transform: translateX(4px);
    }
  }

  &__body {
    padding: 24px;
    flex: 1;
  }

  &__description {
    font-size: 14px;
    line-height: 1.6;
    color: var(--text-secondary);
    margin: 0 0 20px;
  }

  &__meta {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
    margin-bottom: 20px;

    .meta-item {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 13px;
      color: var(--text-secondary);

      i {
        color: #FF6B35;
      }
    }
  }

  &__tech {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;

    .tech-badge {
      padding: 4px 12px;
      border: 2px solid var(--border-color);
      font-family: 'JetBrains Mono', monospace;
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      color: var(--text-secondary);
    }
  }

  &__actions {
    padding: 16px 24px;
    border-top: 2px solid var(--border-color);
    display: flex;
    gap: 8px;
  }
}

@media (max-width: 768px) {
  .sites-grid {
    grid-template-columns: 1fr;
  }

  .site-card__actions {
    flex-direction: column;

    .btn {
      width: 100%;
    }
  }
}
</style>
