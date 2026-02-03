<template>
  <MainLayout>
    <div class="page-container">
      <!-- Header -->
      <div class="page-header">
        <div class="page-header__content">
          <h1 class="page-header__title">TEMPLATES DE MENSAGEM</h1>
          <p class="page-header__subtitle">Crie e gerencie templates reutilizáveis para WhatsApp e Instagram</p>
        </div>
        <div class="page-header__actions">
          <button class="btn" @click="createTemplate">
            <i class="fas fa-plus"></i>
            Novo Template
          </button>
        </div>
      </div>

      <!-- Stats -->
      <div class="grid grid--4">
        <div class="stat-card">
          <div class="stat-card__label">Total de Templates</div>
          <div class="stat-card__value">{{ stats.total }}</div>
        </div>
        <div class="stat-card">
          <div class="stat-card__label">WhatsApp</div>
          <div class="stat-card__value">{{ stats.whatsapp }}</div>
        </div>
        <div class="stat-card">
          <div class="stat-card__label">Instagram</div>
          <div class="stat-card__value">{{ stats.instagram }}</div>
        </div>
        <div class="stat-card">
          <div class="stat-card__label">Mais Usado</div>
          <div class="stat-card__value">{{ stats.most_used }}</div>
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

      <!-- Templates Grid -->
      <div v-if="filteredTemplates.length > 0" class="templates-grid">
        <div v-for="template in filteredTemplates" :key="template.id" class="template-card">
          <div class="template-card__header">
            <div class="template-card__channel">
              <i :class="getChannelIcon(template.channel)"></i>
              {{ template.channel.toUpperCase() }}
            </div>
            <div class="template-card__badge">
              <span v-if="template.is_approved" class="badge badge--success">
                <i class="fas fa-check"></i> Aprovado
              </span>
              <span v-else class="badge badge--warning">
                <i class="fas fa-clock"></i> Pendente
              </span>
            </div>
          </div>

          <div class="template-card__body">
            <h3 class="template-card__title">{{ template.name }}</h3>
            <p class="template-card__category">{{ template.category }}</p>
            <div class="template-card__preview">
              {{ truncateText(template.content, 150) }}
            </div>

            <div class="template-card__meta">
              <div class="meta-item">
                <i class="fas fa-paper-plane"></i>
                {{ template.uses_count }} envios
              </div>
              <div class="meta-item">
                <i class="fas fa-calendar"></i>
                {{ formatDate(template.updated_at) }}
              </div>
            </div>
          </div>

          <div class="template-card__actions">
            <button class="btn btn--sm" @click="editTemplate(template.id)">
              <i class="fas fa-edit"></i>
              Editar
            </button>
            <button class="btn btn--sm btn--secondary" @click="duplicateTemplate(template.id)">
              <i class="fas fa-copy"></i>
              Duplicar
            </button>
            <button class="btn btn--sm btn--danger" @click="deleteTemplate(template.id)">
              <i class="fas fa-trash"></i>
            </button>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="empty-state">
        <i class="fas fa-comment-dots empty-state__icon"></i>
        <h3 class="empty-state__title">Nenhum template encontrado</h3>
        <p class="empty-state__text">Crie seu primeiro template para começar a enviar mensagens rapidamente</p>
        <button class="btn" @click="createTemplate">
          <i class="fas fa-plus"></i>
          Criar Primeiro Template
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
  templates: Array,
  stats: Object,
});

const activeFilter = ref('all');

const filters = computed(() => [
  { label: 'Todos', value: 'all', count: props.templates?.length || 0 },
  { label: 'WhatsApp', value: 'whatsapp', count: props.templates?.filter(t => t.channel === 'whatsapp').length || 0 },
  { label: 'Instagram', value: 'instagram', count: props.templates?.filter(t => t.channel === 'instagram').length || 0 },
  { label: 'Aprovados', value: 'approved', count: props.templates?.filter(t => t.is_approved).length || 0 },
]);

const filteredTemplates = computed(() => {
  if (!props.templates) return [];

  if (activeFilter.value === 'all') return props.templates;
  if (activeFilter.value === 'approved') return props.templates.filter(t => t.is_approved);
  return props.templates.filter(t => t.channel === activeFilter.value);
});

const getChannelIcon = (channel) => {
  return channel === 'whatsapp' ? 'fab fa-whatsapp' : 'fab fa-instagram';
};

const truncateText = (text, length) => {
  if (!text) return '';
  return text.length > length ? text.substring(0, length) + '...' : text;
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('pt-BR');
};

const createTemplate = () => {
  router.visit('/social/message-templates/create');
};

const editTemplate = (id) => {
  router.visit(`/social/message-templates/${id}/edit`);
};

const duplicateTemplate = (id) => {
  router.post(`/social/message-templates/${id}/duplicate`);
};

const deleteTemplate = (id) => {
  if (confirm('Tem certeza que deseja excluir este template?')) {
    router.delete(`/social/message-templates/${id}`);
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

.templates-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
  gap: 24px;
}

.template-card {
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
    padding: 16px;
    border-bottom: 2px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  &__channel {
    font-family: 'JetBrains Mono', monospace;
    font-size: 12px;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 8px;

    i {
      font-size: 16px;
    }
  }

  &__body {
    padding: 24px;
    flex: 1;
  }

  &__title {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 20px;
    font-weight: 700;
    margin: 0 0 8px;
    color: var(--text-primary);
  }

  &__category {
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--text-secondary);
    margin: 0 0 16px;
  }

  &__preview {
    font-size: 14px;
    line-height: 1.6;
    color: var(--text-secondary);
    margin-bottom: 16px;
    min-height: 80px;
  }

  &__meta {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
    font-size: 12px;
    color: var(--text-secondary);

    .meta-item {
      display: flex;
      align-items: center;
      gap: 6px;
    }
  }

  &__actions {
    padding: 16px;
    border-top: 2px solid var(--border-color);
    display: flex;
    gap: 8px;
  }
}

@media (max-width: 768px) {
  .templates-grid {
    grid-template-columns: 1fr;
  }

  .template-card__actions {
    flex-direction: column;

    .btn {
      width: 100%;
    }
  }
}
</style>
