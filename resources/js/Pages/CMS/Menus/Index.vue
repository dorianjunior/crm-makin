<template>
  <MainLayout>
    <div class="page-container">
      <!-- Header -->
      <div class="page-header">
        <div class="page-header__content">
          <h1 class="page-header__title">MENUS DE NAVEGAÇÃO</h1>
          <p class="page-header__subtitle">Configure menus e estrutura de navegação do site</p>
        </div>
        <div class="page-header__actions">
          <button class="btn" @click="createMenu">
            <i class="fas fa-plus"></i>
            Novo Menu
          </button>
        </div>
      </div>

      <!-- Stats -->
      <div class="grid grid--3">
        <div class="stat-card">
          <div class="stat-card__label">Total de Menus</div>
          <div class="stat-card__value">{{ stats.total }}</div>
        </div>
        <div class="stat-card">
          <div class="stat-card__label">Menus Ativos</div>
          <div class="stat-card__value">{{ stats.active }}</div>
        </div>
        <div class="stat-card">
          <div class="stat-card__label">Total de Itens</div>
          <div class="stat-card__value">{{ stats.total_items }}</div>
        </div>
      </div>

      <!-- Menus List -->
      <div v-if="menus && menus.length > 0" class="menus-list">
        <div v-for="menu in menus" :key="menu.id" class="menu-card">
          <div class="menu-card__header">
            <div class="menu-card__info">
              <h3 class="menu-card__title">{{ menu.name }}</h3>
              <p class="menu-card__location">
                <i class="fas fa-map-marker-alt"></i>
                {{ menu.location }}
              </p>
            </div>
            <div class="menu-card__status">
              <span :class="['badge', `badge--${menu.is_active ? 'success' : 'neutral'}`]">
                {{ menu.is_active ? 'ATIVO' : 'INATIVO' }}
              </span>
            </div>
          </div>

          <div class="menu-card__body">
            <div class="menu-tree">
              <div v-for="item in menu.items" :key="item.id" class="menu-tree-item">
                <div class="menu-tree-item__content">
                  <i :class="getItemIcon(item.type)"></i>
                  <span class="menu-tree-item__label">{{ item.label }}</span>
                  <span class="menu-tree-item__url">{{ truncateUrl(item.url) }}</span>
                </div>

                <div v-if="item.children && item.children.length" class="menu-tree-children">
                  <div v-for="child in item.children" :key="child.id" class="menu-tree-item menu-tree-item--child">
                    <div class="menu-tree-item__content">
                      <i :class="getItemIcon(child.type)"></i>
                      <span class="menu-tree-item__label">{{ child.label }}</span>
                      <span class="menu-tree-item__url">{{ truncateUrl(child.url) }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="menu-card__footer">
            <div class="menu-card__meta">
              <div class="meta-item">
                <i class="fas fa-list"></i>
                {{ menu.items_count }} itens
              </div>
              <div class="meta-item">
                <i class="fas fa-calendar"></i>
                Atualizado {{ formatDate(menu.updated_at) }}
              </div>
            </div>
            <div class="menu-card__actions">
              <button class="btn btn--sm" @click="editMenu(menu.id)">
                <i class="fas fa-edit"></i>
                Editar
              </button>
              <button class="btn btn--sm btn--secondary" @click="duplicateMenu(menu.id)">
                <i class="fas fa-copy"></i>
                Duplicar
              </button>
              <button class="btn btn--sm btn--danger" @click="deleteMenu(menu.id)">
                <i class="fas fa-trash"></i>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="empty-state">
        <i class="fas fa-bars empty-state__icon"></i>
        <h3 class="empty-state__title">Nenhum menu configurado</h3>
        <p class="empty-state__text">Crie menus de navegação para seu site</p>
        <button class="btn" @click="createMenu">
          <i class="fas fa-plus"></i>
          Criar Primeiro Menu
        </button>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';

const props = defineProps({
  menus: Array,
  stats: Object,
});

const getItemIcon = (type) => {
  const icons = {
    'page': 'fas fa-file-alt',
    'post': 'fas fa-blog',
    'custom': 'fas fa-link',
    'category': 'fas fa-folder',
    'external': 'fas fa-external-link-alt'
  };
  return icons[type] || 'fas fa-link';
};

const truncateUrl = (url) => {
  if (!url) return '';
  return url.length > 40 ? url.substring(0, 40) + '...' : url;
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('pt-BR');
};

const createMenu = () => {
  router.visit('/cms/menus/create');
};

const editMenu = (id) => {
  router.visit(`/cms/menus/${id}/edit`);
};

const duplicateMenu = (id) => {
  router.post(`/cms/menus/${id}/duplicate`);
};

const deleteMenu = (id) => {
  if (confirm('Tem certeza que deseja excluir este menu?')) {
    router.delete(`/cms/menus/${id}`);
  }
};
</script>

<style scoped lang="scss">
.page-container {
  padding: 32px;
}

.menus-list {
  display: flex;
  flex-direction: column;
  gap: 24px;
  margin-top: 32px;
}

.menu-card {
  border: 2px solid var(--border-color);
  background: var(--bg-primary);
  transition: all 0.2s ease;

  &:hover {
    border-color: #FF6B35;
  }

  &__header {
    padding: 24px;
    border-bottom: 2px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
  }

  &__info {
    flex: 1;
  }

  &__title {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 24px;
    font-weight: 700;
    margin: 0 0 8px;
    color: var(--text-primary);
  }

  &__location {
    font-family: 'JetBrains Mono', monospace;
    font-size: 13px;
    color: var(--text-secondary);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 6px;
  }

  &__body {
    padding: 24px;
  }

  &__footer {
    padding: 16px 24px;
    border-top: 2px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
  }

  &__meta {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;

    .meta-item {
      display: flex;
      align-items: center;
      gap: 6px;
      font-size: 13px;
      color: var(--text-secondary);

      i {
        color: #FF6B35;
      }
    }
  }

  &__actions {
    display: flex;
    gap: 8px;
  }
}

.menu-tree {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.menu-tree-item {
  &__content {
    padding: 12px 16px;
    border: 2px solid var(--border-color);
    background: var(--bg-primary);
    display: flex;
    align-items: center;
    gap: 12px;
    transition: all 0.2s ease;

    &:hover {
      border-color: #FF6B35;
      transform: translateX(4px);
    }

    i {
      color: #FF6B35;
      font-size: 14px;
    }
  }

  &__label {
    font-family: 'Space Grotesk', sans-serif;
    font-weight: 700;
    font-size: 14px;
    color: var(--text-primary);
  }

  &__url {
    font-family: 'JetBrains Mono', monospace;
    font-size: 12px;
    color: var(--text-secondary);
    margin-left: auto;
  }

  &--child {
    margin-left: 32px;

    .menu-tree-item__content {
      background: rgba(255, 107, 53, 0.05);
    }
  }
}

.menu-tree-children {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-top: 8px;
}

@media (max-width: 768px) {
  .menu-card__footer {
    flex-direction: column;
    align-items: flex-start;
  }

  .menu-card__actions {
    flex-direction: column;
    width: 100%;

    .btn {
      width: 100%;
    }
  }

  .menu-tree-item--child {
    margin-left: 16px;
  }
}
</style>
