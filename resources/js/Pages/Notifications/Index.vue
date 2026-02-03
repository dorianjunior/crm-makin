<template>
  <MainLayout>
    <div class="notifications-page">
      <!-- Header -->
      <div class="page-header">
        <div>
          <h1 class="page-title">
            <i class="fa fa-bell"></i>
            Notificações
          </h1>
          <Breadcrumbs :items="breadcrumbs" />
        </div>
        <div class="header-actions">
          <Button
            label="Marcar Todas como Lidas"
            icon="fa fa-check-double"
            @click="markAllAsRead"
            :disabled="unreadCount === 0"
            outlined
          />
          <Button
            label="Configurações"
            icon="fa fa-cog"
            @click="showSettings = true"
            outlined
          />
        </div>
      </div>

      <!-- Stats -->
      <div class="notification-stats">
        <div class="stat-item">
          <span class="stat-value">{{ unreadCount }}</span>
          <span class="stat-label">Não Lidas</span>
        </div>
        <div class="stat-item">
          <span class="stat-value">{{ todayCount }}</span>
          <span class="stat-label">Hoje</span>
        </div>
        <div class="stat-item">
          <span class="stat-value">{{ totalCount }}</span>
          <span class="stat-label">Total</span>
        </div>
      </div>

      <!-- Filters -->
      <div class="notification-filters">
        <button
          :class="['filter-btn', { active: filter === 'all' }]"
          @click="filter = 'all'"
        >
          Todas
        </button>
        <button
          :class="['filter-btn', { active: filter === 'unread' }]"
          @click="filter = 'unread'"
        >
          Não Lidas
          <span v-if="unreadCount > 0" class="badge">{{ unreadCount }}</span>
        </button>
        <button
          :class="['filter-btn', { active: filter === 'leads' }]"
          @click="filter = 'leads'"
        >
          <i class="fa fa-user"></i>
          Leads
        </button>
        <button
          :class="['filter-btn', { active: filter === 'activities' }]"
          @click="filter = 'activities'"
        >
          <i class="fa fa-calendar"></i>
          Atividades
        </button>
        <button
          :class="['filter-btn', { active: filter === 'messages' }]"
          @click="filter = 'messages'"
        >
          <i class="fa fa-comments"></i>
          Mensagens
        </button>
        <button
          :class="['filter-btn', { active: filter === 'system' }]"
          @click="filter = 'system'"
        >
          <i class="fa fa-cog"></i>
          Sistema
        </button>
      </div>

      <!-- Notifications List -->
      <div class="notifications-container">
        <div v-for="(group, date) in groupedNotifications" :key="date" class="notification-group">
          <div class="group-header">
            <h3>{{ formatDateHeader(date) }}</h3>
          </div>

          <div class="notifications-list">
            <div
              v-for="notification in group"
              :key="notification.id"
              :class="['notification-item', { unread: !notification.read_at }]"
              @click="handleNotificationClick(notification)"
            >
              <div class="notification-icon" :class="notification.type">
                <i :class="getNotificationIcon(notification.type)"></i>
              </div>

              <div class="notification-content">
                <div class="notification-header">
                  <h4>{{ notification.title }}</h4>
                  <span class="notification-time">{{ formatTime(notification.created_at) }}</span>
                </div>
                <p class="notification-message">{{ notification.message }}</p>

                <div v-if="notification.data" class="notification-data">
                  <span v-if="notification.data.lead_name" class="data-tag">
                    <i class="fa fa-user"></i>
                    {{ notification.data.lead_name }}
                  </span>
                  <span v-if="notification.data.amount" class="data-tag amount">
                    <i class="fa fa-dollar-sign"></i>
                    {{ formatCurrency(notification.data.amount) }}
                  </span>
                  <span v-if="notification.data.user_name" class="data-tag">
                    <i class="fa fa-user-circle"></i>
                    {{ notification.data.user_name }}
                  </span>
                </div>
              </div>

              <div class="notification-actions">
                <button
                  v-if="!notification.read_at"
                  @click.stop="markAsRead(notification)"
                  class="btn-action"
                  title="Marcar como lida"
                >
                  <i class="fa fa-check"></i>
                </button>
                <button
                  @click.stop="deleteNotification(notification)"
                  class="btn-action"
                  title="Excluir"
                >
                  <i class="fa fa-trash"></i>
                </button>
              </div>
            </div>
          </div>
        </div>

        <div v-if="Object.keys(groupedNotifications).length === 0" class="empty-state">
          <i class="fa fa-bell-slash"></i>
          <h3>Nenhuma notificação</h3>
          <p>Você está em dia! Não há notificações para exibir.</p>
        </div>

        <!-- Load More -->
        <div v-if="hasMore" class="load-more">
          <Button
            label="Carregar Mais"
            icon="fa fa-arrow-down"
            @click="loadMore"
            :loading="loading"
            outlined
            block
          />
        </div>
      </div>
    </div>

    <!-- Settings Modal -->
    <Modal
      v-model:visible="showSettings"
      title="Configurações de Notificações"
      @confirm="saveSettings"
    >
      <div class="settings-form">
        <div class="settings-section">
          <h4>Notificações por E-mail</h4>
          <div class="setting-item">
            <label class="checkbox-label">
              <input type="checkbox" v-model="settings.email_new_lead" />
              <span>Novo lead criado</span>
            </label>
          </div>
          <div class="setting-item">
            <label class="checkbox-label">
              <input type="checkbox" v-model="settings.email_lead_converted" />
              <span>Lead convertido</span>
            </label>
          </div>
          <div class="setting-item">
            <label class="checkbox-label">
              <input type="checkbox" v-model="settings.email_task_assigned" />
              <span>Tarefa atribuída a mim</span>
            </label>
          </div>
          <div class="setting-item">
            <label class="checkbox-label">
              <input type="checkbox" v-model="settings.email_task_due" />
              <span>Tarefa próxima do prazo</span>
            </label>
          </div>
          <div class="setting-item">
            <label class="checkbox-label">
              <input type="checkbox" v-model="settings.email_new_message" />
              <span>Nova mensagem (WhatsApp/Instagram)</span>
            </label>
          </div>
        </div>

        <div class="settings-section">
          <h4>Notificações no Sistema</h4>
          <div class="setting-item">
            <label class="checkbox-label">
              <input type="checkbox" v-model="settings.system_new_lead" />
              <span>Novo lead criado</span>
            </label>
          </div>
          <div class="setting-item">
            <label class="checkbox-label">
              <input type="checkbox" v-model="settings.system_activity_reminder" />
              <span>Lembretes de atividades</span>
            </label>
          </div>
          <div class="setting-item">
            <label class="checkbox-label">
              <input type="checkbox" v-model="settings.system_lead_updated" />
              <span>Lead atualizado</span>
            </label>
          </div>
        </div>

        <div class="settings-section">
          <h4>Notificações Push</h4>
          <div class="setting-item">
            <label class="checkbox-label">
              <input type="checkbox" v-model="settings.push_enabled" />
              <span>Ativar notificações push</span>
            </label>
          </div>
          <div v-if="settings.push_enabled" class="push-options">
            <div class="setting-item">
              <label class="checkbox-label">
                <input type="checkbox" v-model="settings.push_urgent_only" />
                <span>Apenas urgentes</span>
              </label>
            </div>
          </div>
        </div>

        <div class="settings-section">
          <h4>Horário de Silêncio</h4>
          <div class="time-range">
            <div class="form-group">
              <label>Das</label>
              <Input v-model="settings.quiet_hours_start" type="time" size="small" />
            </div>
            <div class="form-group">
              <label>Até</label>
              <Input v-model="settings.quiet_hours_end" type="time" size="small" />
            </div>
          </div>
          <small class="help-text">
            Não receber notificações durante este período
          </small>
        </div>
      </div>
    </Modal>
  </MainLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Button from '@/Components/Button.vue';
import Input from '@/Components/Input.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
  notifications: Array,
  hasMore: Boolean,
  userSettings: Object,
});

const breadcrumbs = [
  { label: 'Dashboard', to: '/dashboard' },
  { label: 'Notificações', active: true }
];

const filter = ref('all');
const showSettings = ref(false);
const loading = ref(false);

const settings = ref({
  email_new_lead: props.userSettings?.email_new_lead ?? true,
  email_lead_converted: props.userSettings?.email_lead_converted ?? true,
  email_task_assigned: props.userSettings?.email_task_assigned ?? true,
  email_task_due: props.userSettings?.email_task_due ?? true,
  email_new_message: props.userSettings?.email_new_message ?? true,
  system_new_lead: props.userSettings?.system_new_lead ?? true,
  system_activity_reminder: props.userSettings?.system_activity_reminder ?? true,
  system_lead_updated: props.userSettings?.system_lead_updated ?? true,
  push_enabled: props.userSettings?.push_enabled ?? false,
  push_urgent_only: props.userSettings?.push_urgent_only ?? false,
  quiet_hours_start: props.userSettings?.quiet_hours_start || '22:00',
  quiet_hours_end: props.userSettings?.quiet_hours_end || '08:00',
});

const unreadCount = computed(() => {
  return props.notifications.filter(n => !n.read_at).length;
});

const todayCount = computed(() => {
  const today = new Date().toDateString();
  return props.notifications.filter(n =>
    new Date(n.created_at).toDateString() === today
  ).length;
});

const totalCount = computed(() => props.notifications.length);

const filteredNotifications = computed(() => {
  let filtered = props.notifications;

  if (filter.value === 'unread') {
    filtered = filtered.filter(n => !n.read_at);
  } else if (filter.value !== 'all') {
    filtered = filtered.filter(n => n.type === filter.value);
  }

  return filtered;
});

const groupedNotifications = computed(() => {
  const groups = {};

  filteredNotifications.value.forEach(notification => {
    const date = new Date(notification.created_at).toLocaleDateString('pt-BR');

    if (!groups[date]) {
      groups[date] = [];
    }

    groups[date].push(notification);
  });

  return groups;
});

const markAsRead = (notification) => {
  router.patch(`/notifications/${notification.id}/read`, {}, {
    preserveState: true,
  });
};

const markAllAsRead = () => {
  router.post('/notifications/mark-all-read', {}, {
    preserveState: true,
  });
};

const deleteNotification = (notification) => {
  router.delete(`/notifications/${notification.id}`, {
    preserveState: true,
  });
};

const handleNotificationClick = (notification) => {
  if (!notification.read_at) {
    markAsRead(notification);
  }

  if (notification.action_url) {
    router.visit(notification.action_url);
  }
};

const loadMore = () => {
  loading.value = true;
  router.get('/notifications', {
    page: Math.ceil(props.notifications.length / 20) + 1,
  }, {
    preserveState: true,
    onFinish: () => loading.value = false,
  });
};

const saveSettings = () => {
  router.post('/notifications/settings', settings.value, {
    onSuccess: () => {
      showSettings.value = false;
    },
  });
};

const formatDateHeader = (date) => {
  const today = new Date().toLocaleDateString('pt-BR');
  const yesterday = new Date(Date.now() - 86400000).toLocaleDateString('pt-BR');

  if (date === today) return 'Hoje';
  if (date === yesterday) return 'Ontem';
  return date;
};

const formatTime = (datetime) => {
  const now = new Date();
  const then = new Date(datetime);
  const diffMinutes = Math.floor((now - then) / (1000 * 60));

  if (diffMinutes < 1) return 'Agora';
  if (diffMinutes < 60) return `${diffMinutes}m`;

  const diffHours = Math.floor(diffMinutes / 60);
  if (diffHours < 24) return `${diffHours}h`;

  return then.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
};

const formatCurrency = (value) => {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  }).format(value);
};

const getNotificationIcon = (type) => {
  const icons = {
    leads: 'fa fa-user-plus',
    activities: 'fa fa-calendar-check',
    messages: 'fa fa-comments',
    system: 'fa fa-info-circle',
    task: 'fa fa-tasks',
    deal: 'fa fa-handshake',
    warning: 'fa fa-exclamation-triangle',
  };
  return icons[type] || 'fa fa-bell';
};
</script>

<style scoped lang="scss">
.notifications-page {
  padding: 2rem;
  max-width: 1200px;
  margin: 0 auto;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;

  .page-title {
    font-size: 2rem;
    font-weight: 700;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 0.75rem;

    i {
      color: var(--primary-color);
    }
  }

  .header-actions {
    display: flex;
    gap: 0.75rem;
  }
}

.notification-stats {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.stat-item {
  padding: 1.5rem;
  background: var(--surface-card);
  border-radius: var(--border-radius);
  box-shadow: var(--shadow-sm);
  text-align: center;

  .stat-value {
    display: block;
    font-size: 2rem;
    font-weight: 700;
    color: var(--primary-color);
    margin-bottom: 0.25rem;
  }

  .stat-label {
    font-size: 0.875rem;
    color: var(--text-secondary);
  }
}

.notification-filters {
  display: flex;
  gap: 0.75rem;
  margin-bottom: 1.5rem;
  overflow-x: auto;
  padding-bottom: 0.5rem;
}

.filter-btn {
  padding: 0.625rem 1rem;
  background: var(--surface-card);
  border: 1px solid var(--border-color);
  border-radius: 20px;
  color: var(--text-secondary);
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  white-space: nowrap;
  box-shadow: var(--shadow-sm);

  &:hover {
    background: var(--surface-hover);
  }

  &.active {
    background: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
  }

  .badge {
    padding: 0.125rem 0.5rem;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 10px;
    font-size: 0.75rem;
    font-weight: 600;
  }
}

.notifications-container {
  background: var(--surface-card);
  border-radius: var(--border-radius);
  box-shadow: var(--shadow-sm);
  overflow: hidden;
}

.notification-group {
  border-bottom: 1px solid var(--border-color);

  &:last-child {
    border-bottom: none;
  }

  .group-header {
    padding: 1rem 1.5rem;
    background: var(--surface-ground);
    border-bottom: 1px solid var(--border-color);

    h3 {
      font-size: 0.875rem;
      font-weight: 600;
      color: var(--text-secondary);
      text-transform: uppercase;
    }
  }
}

.notifications-list {
  display: flex;
  flex-direction: column;
}

.notification-item {
  display: flex;
  gap: 1rem;
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid var(--border-color);
  cursor: pointer;
  transition: all 0.2s;
  position: relative;

  &:last-child {
    border-bottom: none;
  }

  &:hover {
    background: var(--surface-hover);

    .notification-actions {
      opacity: 1;
    }
  }

  &.unread {
    background: var(--primary-50);

    &::before {
      content: '';
      position: absolute;
      left: 0;
      top: 0;
      bottom: 0;
      width: 4px;
      background: var(--primary-color);
    }
  }

  .notification-icon {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;

    i {
      font-size: 1.25rem;
      color: white;
    }

    &.leads {
      background: #3B82F6;
    }

    &.activities {
      background: #8B5CF6;
    }

    &.messages {
      background: #10B981;
    }

    &.system {
      background: #6B7280;
    }

    &.task {
      background: #F59E0B;
    }

    &.deal {
      background: #10B981;
    }

    &.warning {
      background: #EF4444;
    }
  }

  .notification-content {
    flex: 1;
    min-width: 0;

    .notification-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 0.25rem;

      h4 {
        font-size: 0.9375rem;
        font-weight: 600;
        color: var(--text-primary);
      }

      .notification-time {
        font-size: 0.75rem;
        color: var(--text-secondary);
        white-space: nowrap;
        margin-left: 1rem;
      }
    }

    .notification-message {
      font-size: 0.875rem;
      color: var(--text-secondary);
      line-height: 1.5;
      margin-bottom: 0.5rem;
    }

    .notification-data {
      display: flex;
      flex-wrap: wrap;
      gap: 0.5rem;
    }

    .data-tag {
      padding: 0.25rem 0.625rem;
      background: var(--surface-ground);
      border-radius: 4px;
      font-size: 0.75rem;
      color: var(--text-primary);
      display: flex;
      align-items: center;
      gap: 0.25rem;

      i {
        font-size: 0.7rem;
        color: var(--text-secondary);
      }

      &.amount {
        background: var(--green-50);
        color: var(--green-700);

        i {
          color: var(--green-600);
        }
      }
    }
  }

  .notification-actions {
    display: flex;
    gap: 0.25rem;
    align-items: flex-start;
    opacity: 0;
    transition: opacity 0.2s;

    .btn-action {
      padding: 0.5rem;
      background: transparent;
      border: none;
      border-radius: var(--border-radius);
      color: var(--text-secondary);
      cursor: pointer;
      transition: all 0.2s;

      &:hover {
        background: var(--surface-ground);
        color: var(--text-primary);
      }
    }
  }
}

.empty-state {
  padding: 4rem 2rem;
  text-align: center;
  color: var(--text-secondary);

  i {
    font-size: 4rem;
    margin-bottom: 1rem;
    display: block;
    opacity: 0.3;
  }

  h3 {
    font-size: 1.5rem;
    color: var(--text-primary);
    margin-bottom: 0.5rem;
  }
}

.load-more {
  padding: 1.5rem;
}

.settings-form {
  .settings-section {
    margin-bottom: 2rem;
    padding-bottom: 2rem;
    border-bottom: 1px solid var(--border-color);

    &:last-child {
      border-bottom: none;
      margin-bottom: 0;
      padding-bottom: 0;
    }

    h4 {
      font-size: 1rem;
      font-weight: 600;
      color: var(--text-primary);
      margin-bottom: 1rem;
    }
  }

  .setting-item {
    margin-bottom: 0.75rem;
  }

  .checkbox-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;

    input[type="checkbox"] {
      width: 18px;
      height: 18px;
      cursor: pointer;
    }

    span {
      font-size: 0.875rem;
      color: var(--text-primary);
    }
  }

  .push-options {
    padding-left: 1.5rem;
    margin-top: 0.5rem;
  }

  .time-range {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;

    .form-group {
      label {
        display: block;
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--text-secondary);
        margin-bottom: 0.5rem;
      }
    }
  }

  .help-text {
    display: block;
    font-size: 0.75rem;
    color: var(--text-secondary);
    margin-top: 0.5rem;
  }
}

@media (max-width: 768px) {
  .notifications-page {
    padding: 1rem;
  }

  .notification-stats {
    grid-template-columns: 1fr;
  }

  .notification-item {
    flex-direction: column;
    gap: 0.75rem;

    .notification-actions {
      opacity: 1;
      justify-content: flex-end;
    }
  }
}
</style>
