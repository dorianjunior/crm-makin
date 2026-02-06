<template>
  <MainLayout title="Conversas com IA">
    <div class="page-container">
      <!-- Header -->
      <div class="page-header">
        <div class="page-header__content">
          <h1 class="page-header__title">CONVERSAS COM IA</h1>
          <p class="page-header__subtitle">Gerencie conversas automatizadas e qualificação de leads</p>
        </div>
        <div class="page-header__actions">
          <button class="btn btn--secondary" @click="router.visit('/ai/settings')">
            <i class="fas fa-cog"></i>
            Configurações
          </button>
          <button class="btn" @click="createConversation">
            <i class="fas fa-plus"></i>
            Nova Conversa
          </button>
        </div>
      </div>

      <!-- Stats -->
      <div class="grid grid--4">
        <div class="stat-card">
          <div class="stat-card__label">Total de Conversas</div>
          <div class="stat-card__value">{{ stats.total }}</div>
          <div class="stat-card__footer">
            <i class="fas fa-robot"></i>
            {{ stats.automated }} automáticas
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-card__label">Leads Qualificados</div>
          <div class="stat-card__value">{{ stats.qualified }}</div>
          <div class="stat-card__footer">
            <i class="fas fa-arrow-up"></i>
            +{{ stats.qualification_rate }}%
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-card__label">Taxa de Conversão</div>
          <div class="stat-card__value">{{ stats.conversion_rate }}%</div>
          <div class="stat-card__footer">
            <i class="fas fa-chart-line"></i>
            Média mensal
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-card__label">Tempo Médio</div>
          <div class="stat-card__value">{{ stats.avg_time }}m</div>
          <div class="stat-card__footer">
            <i class="fas fa-clock"></i>
            Por conversa
          </div>
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

      <!-- Conversations List -->
      <div v-if="filteredConversations.length > 0" class="conversations-list">
        <div v-for="conversation in filteredConversations" :key="conversation.id" class="conversation">
          <div class="conversation__header">
            <div class="conversation__lead">
              <div class="lead-avatar">
                <i class="fas fa-user"></i>
              </div>
              <div>
                <h3 class="lead-name">{{ conversation.lead_name }}</h3>
                <p class="lead-email">{{ conversation.lead_email }}</p>
              </div>
            </div>
            <div class="conversation__status">
              <span :class="['badge', `badge--${getStatusColor(conversation.status)}`]">
                {{ conversation.status.toUpperCase() }}
              </span>
            </div>
          </div>

          <div class="conversation__body">
            <div class="conversation__info">
              <div class="info-item">
                <i class="fas fa-comments"></i>
                <span>{{ conversation.messages_count }} mensagens</span>
              </div>
              <div class="info-item">
                <i class="fas fa-robot"></i>
                <span>{{ conversation.ai_responses }} da IA</span>
              </div>
              <div class="info-item">
                <i class="fas fa-clock"></i>
                <span>{{ formatDate(conversation.last_message_at) }}</span>
              </div>
              <div class="info-item">
                <i class="fas fa-star"></i>
                <span>Score: {{ conversation.qualification_score }}/100</span>
              </div>
            </div>

            <div class="conversation__preview">
              <p class="preview-label">ÚLTIMA MENSAGEM:</p>
              <p class="preview-text">{{ truncateText(conversation.last_message, 120) }}</p>
            </div>
          </div>

          <div class="conversation__actions">
            <button class="btn btn--sm" @click="viewConversation(conversation.id)">
              <i class="fas fa-eye"></i>
              Ver Conversa
            </button>
            <button class="btn btn--sm btn--secondary" @click="convertToLead(conversation.id)">
              <i class="fas fa-user-plus"></i>
              Converter em Lead
            </button>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="empty-state">
        <i class="fas fa-robot empty-state__icon"></i>
        <h3 class="empty-state__title">Nenhuma conversa encontrada</h3>
        <p class="empty-state__text">Configure a IA e comece a qualificar leads automaticamente</p>
        <button class="btn" @click="router.visit('/ai/settings')">
          <i class="fas fa-cog"></i>
          Configurar IA
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
  conversations: Array,
  stats: Object,
});

const activeFilter = ref('all');

const filters = computed(() => [
  { label: 'Todas', value: 'all', count: props.conversations?.length || 0 },
  { label: 'Em Andamento', value: 'active', count: props.conversations?.filter(c => c.status === 'active').length || 0 },
  { label: 'Qualificadas', value: 'qualified', count: props.conversations?.filter(c => c.status === 'qualified').length || 0 },
  { label: 'Finalizadas', value: 'completed', count: props.conversations?.filter(c => c.status === 'completed').length || 0 },
]);

const filteredConversations = computed(() => {
  if (!props.conversations) return [];
  if (activeFilter.value === 'all') return props.conversations;
  return props.conversations.filter(c => c.status === activeFilter.value);
});

const getStatusColor = (status) => {
  const colors = {
    active: 'info',
    qualified: 'success',
    completed: 'neutral',
    failed: 'danger'
  };
  return colors[status] || 'neutral';
};

const truncateText = (text, length) => {
  if (!text) return '';
  return text.length > length ? text.substring(0, length) + '...' : text;
};

const formatDate = (date) => {
  return new Date(date).toLocaleString('pt-BR');
};

const createConversation = () => {
  router.visit('/ai/conversations/create');
};

const viewConversation = (id) => {
  router.visit(`/ai/conversations/${id}`);
};

const convertToLead = (id) => {
  router.post(`/ai/conversations/${id}/convert-to-lead`);
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

.conversations-list {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.conversation {
  border: 2px solid var(--border-color);
  background: var(--bg-primary);
  transition: all 0.2s ease;

  &:hover {
    border-color: #FF6B35;
    transform: translateX(2px);
  }

  &__header {
    padding: 24px;
    border-bottom: 2px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  &__lead {
    display: flex;
    align-items: center;
    gap: 16px;

    .lead-avatar {
      width: 56px;
      height: 56px;
      border: 2px solid var(--border-color);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 24px;
      color: #FF6B35;
    }

    .lead-name {
      font-family: 'Space Grotesk', sans-serif;
      font-size: 20px;
      font-weight: 700;
      margin: 0 0 4px;
      color: var(--text-primary);
    }

    .lead-email {
      font-family: 'JetBrains Mono', monospace;
      font-size: 13px;
      color: var(--text-secondary);
      margin: 0;
    }
  }

  &__body {
    padding: 24px;
  }

  &__info {
    display: flex;
    gap: 24px;
    flex-wrap: wrap;
    margin-bottom: 24px;

    .info-item {
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

  &__preview {
    .preview-label {
      font-family: 'Space Grotesk', sans-serif;
      font-size: 11px;
      font-weight: 700;
      letter-spacing: 0.1em;
      color: var(--text-secondary);
      margin: 0 0 8px;
    }

    .preview-text {
      font-size: 14px;
      line-height: 1.6;
      color: var(--text-primary);
      margin: 0;
    }
  }

  &__actions {
    padding: 16px 24px;
    border-top: 2px solid var(--border-color);
    display: flex;
    gap: 12px;
  }
}

@media (max-width: 768px) {
  .conversation__header {
    flex-direction: column;
    align-items: flex-start;
    gap: 16px;
  }

  .conversation__actions {
    flex-direction: column;

    .btn {
      width: 100%;
    }
  }
}
</style>
