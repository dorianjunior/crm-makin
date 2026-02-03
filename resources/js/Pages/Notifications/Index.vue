<template>
  <MainLayout>
    <div class="page-container">
      <!-- Header -->
      <div class="page-header">
        <div class="page-header__content">
          <h1 class="page-header__title">NOTIFICAÇÕES</h1>
          <p class="page-header__subtitle">Mantenha-se atualizado com as últimas atividades</p>
        </div>
        <div class="page-header__actions">
          <button class="btn btn--secondary" @click="markAllAsRead" :disabled="unreadCount === 0">
            <i class="fas fa-check-double"></i>
            Marcar Todas como Lidas
          </button>
          <button class="btn btn--secondary" @click="$inertia.visit('/notifications/preferences')">
            <i class="fas fa-cog"></i>
            Configurações
          </button>
        </div>
      </div>

      <!-- Stats -->
      <div class="grid grid--3">
        <div class="stat-card">
          <div class="stat-card__icon">
            <i class="fas fa-bell"></i>
          </div>
          <p class="stat-card__label">Não Lidas</p>
          <h2 class="stat-card__value">{{ unreadCount }}</h2>
        </div>

        <div class="stat-card">
          <div class="stat-card__icon">
            <i class="fas fa-calendar-day"></i>
          </div>
          <p class="stat-card__label">Hoje</p>
          <h2 class="stat-card__value">{{ todayCount }}</h2>
        </div>

        <div class="stat-card">
          <div class="stat-card__icon">
            <i class="fas fa-list"></i>
          </div>
          <p class="stat-card__label">Total</p>
          <h2 class="stat-card__value">{{ totalCount }}</h2>
        </div>
      </div>

      <!-- Filters -->
      <div class="filters">
        <button :class="['filter-btn', { active: filter === 'all' }]" @click="filter = 'all'">
          Todas
        </button>
        <button :class="['filter-btn', { active: filter === 'unread' }]" @click="filter = 'unread'">
          Não Lidas
          <span v-if="unreadCount > 0" class="badge badge--accent">{{ unreadCount }}</span>
        </button>
        <button :class="['filter-btn', { active: filter === 'leads' }]" @click="filter = 'leads'">
          <i class="fas fa-user"></i>
          Leads
        </button>
        <button :class="['filter-btn', { active: filter === 'activities' }]" @click="filter = 'activities'">
          <i class="fas fa-calendar"></i>
          Atividades
        </button>
        <button :class="['filter-btn', { active: filter === 'messages' }]" @click="filter = 'messages'">
          <i class="fas fa-comments"></i>
          Mensagens
        </button>
      </div>

      <!-- Notifications List -->
      <div class="notifications-container">
        <div v-for="(group, date) in groupedNotifications" :key="date" class="notification-group">
          <div class="group-header">
            <h3>{{ formatDateHeader(date) }}</h3>
          </div>

          <div class="notification-list">
            <div
              v-for="notification in group"
              :key="notification.id"
              :class="['notification-item', { 'notification-item--unread': !notification.read_at }]"
              @click="handleNotificationClick(notification)"
            >
              <div :class="['notification-icon', `notification-icon--${notification.type}`]">
                <i :class="getNotificationIcon(notification.type)"></i>
              </div>

              <div class="notification-content">
                <div class="notification-header">
                  <h4>{{ notification.title }}</h4>
                  <span class="notification-time">{{ formatTime(notification.created_at) }}</span>
                </div>
                <p class="notification-message">{{ notification.message }}</p>

                <div v-if="notification.data" class="notification-tags">
                  <span v-if="notification.data.lead_name" class="badge">
                    <i class="fas fa-user"></i>
                    {{ notification.data.lead_name }}
                  </span>
                  <span v-if="notification.data.amount" class="badge badge--accent">
                    <i class="fas fa-dollar-sign"></i>
                    {{ formatCurrency(notification.data.amount) }}
                  </span>
                </div>
              </div>

              <div class="notification-actions">
                <button
                  v-if="!notification.read_at"
                  @click.stop="markAsRead(notification)"
                  class="btn btn--icon-only btn--small"
                  title="Marcar como lida"
                >
                  <i class="fas fa-check"></i>
                </button>
                <button
                  @click.stop="deleteNotification(notification)"
                  class="btn btn--icon-only btn--small"
                  title="Excluir"
                >
                  <i class="fas fa-trash"></i>
                </button>
              </div>
            </div>
          </div>
        </div>

        <div v-if="Object.keys(groupedNotifications).length === 0" class="empty-state">
          <div class="empty-state__icon">
            <i class="fas fa-bell-slash"></i>
          </div>
          <h3 class="empty-state__title">Nenhuma Notificação</h3>
          <p class="empty-state__description">Você está em dia! Não há notificações para exibir.</p>
        </div>

        <div v-if="hasMore" class="load-more">
          <button class="btn btn--secondary" @click="loadMore" :disabled="loading">
            <i class="fas fa-arrow-down"></i>
            Carregar Mais
          </button>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';

const props = defineProps({
  notifications: Array,
  unreadCount: Number,
  todayCount: Number,
  totalCount: Number,
  hasMore: Boolean,
});

const filter = ref('all');
const loading = ref(false);

const groupedNotifications = computed(() => {
  let filtered = props.notifications;

  if (filter.value === 'unread') {
    filtered = filtered.filter(n => !n.read_at);
  } else if (filter.value !== 'all') {
    filtered = filtered.filter(n => n.type === filter.value);
  }

  return filtered.reduce((groups, notification) => {
    const date = new Date(notification.created_at).toLocaleDateString();
    if (!groups[date]) groups[date] = [];
    groups[date].push(notification);
    return groups;
  }, {});
});

const getNotificationIcon = (type) => {
  const icons = {
    leads: 'fas fa-user',
    activities: 'fas fa-calendar',
    messages: 'fas fa-comments',
    system: 'fas fa-cog',
  };
  return icons[type] || 'fas fa-bell';
};

const formatDateHeader = (date) => {
  const today = new Date().toLocaleDateString();
  const yesterday = new Date(Date.now() - 86400000).toLocaleDateString();

  if (date === today) return 'HOJE';
  if (date === yesterday) return 'ONTEM';
  return new Date(date).toLocaleDateString('pt-BR', { day: '2-digit', month: 'long' }).toUpperCase();
};

const formatTime = (datetime) => {
  return new Date(datetime).toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
};

const formatCurrency = (value) => {
  return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value);
};

const markAllAsRead = () => {
  router.post('/notifications/mark-all-read');
};

const markAsRead = (notification) => {
  router.post(`/notifications/${notification.id}/read`);
};

const deleteNotification = (notification) => {
  if (confirm('Deseja excluir esta notificação?')) {
    router.delete(`/notifications/${notification.id}`);
  }
};

const handleNotificationClick = (notification) => {
  if (notification.url) {
    router.visit(notification.url);
  }
};

const loadMore = () => {
  loading.value = true;
  router.get('/notifications', { page: props.notifications.length / 20 + 1 }, {
    preserveState: true,
    onFinish: () => loading.value = false,
  });
};
</script>

<style scoped lang="scss">
.page-container {
  padding: 32px;
}

.filters {
  display: flex;
  gap: 12px;
  margin: 32px 0;
  flex-wrap: wrap;
}

.filter-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 12px 24px;
  font-family: 'Space Grotesk', sans-serif;
  font-size: 12px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: var(--text-primary);
  background: var(--bg-primary);
  border: 2px solid var(--border-color);
  cursor: pointer;
  transition: all 200ms ease;

  &:hover {
    border-color: var(--border-bold, #262626);
  }

  &.active {
    background: #FF6B35;
    border-color: #FF6B35;
    color: var(--bg-primary);
  }
}

.group-header {
  margin: 32px 0 16px;
  padding-bottom: 8px;
  border-bottom: 2px solid var(--border-color);

  h3 {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 14px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--text-tertiary);
    margin: 0;
  }
}

.notification-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.notification-item {
  display: flex;
  gap: 16px;
  padding: 20px;
  background: var(--bg-primary);
  border: 2px solid var(--border-color);
  cursor: pointer;
  transition: all 200ms ease;

  &:hover {
    border-color: var(--border-bold, #262626);
    transform: translateX(4px);
  }

  &--unread {
    border-left: 4px solid #FF6B35;
  }
}

.notification-icon {
  width: 48px;
  height: 48px;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--bg-secondary);
  border: 2px solid var(--border-color);
  font-size: 20px;
  color: var(--text-secondary);

  &--leads { background: #e0edff; color: #2563eb; }
  &--activities { background: #dcfce7; color: #16a34a; }
  &--messages { background: #fef3c7; color: #d97706; }
  &--system { background: #f3f4f6; color: #6b7280; }
}

.notification-content {
  flex: 1;
  min-width: 0;
}

.notification-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 8px;

  h4 {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 16px;
    font-weight: 700;
    color: var(--text-primary);
    margin: 0;
  }
}

.notification-time {
  font-family: 'JetBrains Mono', monospace;
  font-size: 12px;
  color: var(--text-tertiary);
  white-space: nowrap;
}

.notification-message {
  font-size: 14px;
  line-height: 1.6;
  color: var(--text-secondary);
  margin: 0 0 12px;
}

.notification-tags {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.notification-actions {
  display: flex;
  gap: 8px;
}

.load-more {
  margin-top: 32px;
  text-align: center;
}
</style>
