<template>
  <MainLayout>
    <div class="activities-index">
      <!-- Header -->
      <div class="page-header">
        <div>
          <h1 class="page-title">Atividades</h1>
          <Breadcrumbs :items="breadcrumbs" />
        </div>
      </div>

      <!-- Filters -->
      <div class="filters-card">
        <div class="filters-grid">
          <div class="filter-item">
            <label>Buscar</label>
            <Input
              v-model="filters.search"
              placeholder="Buscar atividades..."
              icon="fa fa-search"
              @input="debouncedSearch"
            />
          </div>

          <div class="filter-item">
            <label>Tipo</label>
            <select v-model="filters.type" @change="loadActivities" class="form-select">
              <option value="">Todos</option>
              <option value="call">Ligação</option>
              <option value="meeting">Reunião</option>
              <option value="email">Email</option>
              <option value="note">Nota</option>
              <option value="task">Tarefa</option>
            </select>
          </div>

          <div class="filter-item">
            <label>Lead</label>
            <select v-model="filters.lead_id" @change="loadActivities" class="form-select">
              <option value="">Todos</option>
              <option v-for="lead in leads" :key="lead.id" :value="lead.id">
                {{ lead.name }}
              </option>
            </select>
          </div>

          <div class="filter-item">
            <label>Usuário</label>
            <select v-model="filters.user_id" @change="loadActivities" class="form-select">
              <option value="">Todos</option>
              <option v-for="user in users" :key="user.id" :value="user.id">
                {{ user.name }}
              </option>
            </select>
          </div>
        </div>
      </div>

      <!-- Stats -->
      <div class="stats-grid">
        <StatCard
          title="Total de Atividades"
          :value="stats.total"
          icon="fa fa-clipboard-list"
          color="blue"
        />
        <StatCard
          title="Hoje"
          :value="stats.today"
          icon="fa fa-calendar-day"
          color="green"
        />
        <StatCard
          title="Esta Semana"
          :value="stats.this_week"
          icon="fa fa-calendar-week"
          color="purple"
        />
        <StatCard
          title="Este Mês"
          :value="stats.this_month"
          icon="fa fa-calendar"
          color="orange"
        />
      </div>

      <!-- Timeline -->
      <div class="timeline-card">
        <h2 class="card-title">
          <i class="fa fa-history"></i> Linha do Tempo
        </h2>

        <div v-if="loading" class="timeline-loading">
          <i class="fa fa-spinner fa-spin"></i> Carregando...
        </div>

        <div v-else-if="activities.data.length === 0" class="timeline-empty">
          <i class="fa fa-inbox"></i>
          <p>Nenhuma atividade encontrada</p>
        </div>

        <div v-else class="timeline">
          <div v-for="(group, date) in groupedActivities" :key="date" class="timeline-group">
            <div class="timeline-date">
              <i class="fa fa-calendar"></i>
              {{ formatDateHeader(date) }}
            </div>

            <div class="timeline-items">
              <div v-for="activity in group" :key="activity.id" class="timeline-item">
                <div class="timeline-marker" :class="'marker-' + activity.type">
                  <i :class="getActivityIcon(activity.type)"></i>
                </div>

                <div class="timeline-content">
                  <div class="activity-header">
                    <div class="activity-info">
                      <span :class="['activity-type', 'type-' + activity.type]">
                        {{ getActivityTypeLabel(activity.type) }}
                      </span>
                      <span class="activity-time">
                        <i class="fa fa-clock"></i>
                        {{ formatTime(activity.created_at) }}
                      </span>
                    </div>

                    <div class="activity-user">
                      <i class="fa fa-user-circle"></i>
                      {{ activity.user?.name }}
                    </div>
                  </div>

                  <div v-if="activity.lead" class="activity-lead">
                    <i class="fa fa-user-tag"></i>
                    <router-link :to="`/leads/${activity.lead.id}`">
                      {{ activity.lead.name }}
                    </router-link>
                  </div>

                  <div class="activity-description">
                    {{ activity.description }}
                  </div>

                  <div v-if="activity.notes" class="activity-notes">
                    <i class="fa fa-sticky-note"></i>
                    {{ activity.notes }}
                  </div>

                  <div v-if="activity.duration" class="activity-duration">
                    <i class="fa fa-hourglass-half"></i>
                    Duração: {{ activity.duration }} minutos
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="activities.data.length > 0" class="pagination">
          <div class="pagination-info">
            Mostrando {{ activities.from }} a {{ activities.to }} de {{ activities.total }} registros
          </div>
          <div class="pagination-buttons">
            <button
              @click="changePage(page)"
              v-for="page in paginationPages"
              :key="page"
              :class="['pagination-btn', { active: page === activities.current_page }]"
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
import Input from '@/Components/Input.vue';
import StatCard from '@/Components/StatCard.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';

const props = defineProps({
  activities: Object,
  leads: Array,
  users: Array,
  stats: Object,
});

const breadcrumbs = [
  { label: 'Dashboard', to: '/dashboard' },
  { label: 'Atividades', active: true }
];

const loading = ref(false);
const filters = ref({
  search: '',
  type: '',
  lead_id: '',
  user_id: '',
});

const groupedActivities = computed(() => {
  const groups = {};

  props.activities.data.forEach(activity => {
    const date = new Date(activity.created_at).toLocaleDateString('pt-BR');

    if (!groups[date]) {
      groups[date] = [];
    }

    groups[date].push(activity);
  });

  return groups;
});

const paginationPages = computed(() => {
  const pages = [];
  const current = props.activities.current_page;
  const last = props.activities.last_page;

  if (last <= 7) {
    for (let i = 1; i <= last; i++) {
      pages.push(i);
    }
  } else {
    if (current <= 3) {
      pages.push(1, 2, 3, 4, '...', last);
    } else if (current >= last - 2) {
      pages.push(1, '...', last - 3, last - 2, last - 1, last);
    } else {
      pages.push(1, '...', current - 1, current, current + 1, '...', last);
    }
  }

  return pages;
});

let searchTimeout = null;
const debouncedSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    loadActivities();
  }, 500);
};

const loadActivities = () => {
  loading.value = true;
  router.get('/activities', filters.value, {
    preserveState: true,
    preserveScroll: true,
    onFinish: () => loading.value = false,
  });
};

const changePage = (page) => {
  if (page === '...') return;
  router.get(`/activities?page=${page}`, filters.value, {
    preserveState: true,
  });
};

const getActivityIcon = (type) => {
  const icons = {
    call: 'fa fa-phone',
    meeting: 'fa fa-users',
    email: 'fa fa-envelope',
    note: 'fa fa-sticky-note',
    task: 'fa fa-tasks',
  };
  return icons[type] || 'fa fa-circle';
};

const getActivityTypeLabel = (type) => {
  const labels = {
    call: 'Ligação',
    meeting: 'Reunião',
    email: 'Email',
    note: 'Nota',
    task: 'Tarefa',
  };
  return labels[type] || type;
};

const formatDateHeader = (date) => {
  const today = new Date().toLocaleDateString('pt-BR');
  const yesterday = new Date(Date.now() - 86400000).toLocaleDateString('pt-BR');

  if (date === today) return 'Hoje';
  if (date === yesterday) return 'Ontem';

  return date;
};

const formatTime = (datetime) => {
  return new Date(datetime).toLocaleTimeString('pt-BR', {
    hour: '2-digit',
    minute: '2-digit',
  });
};
</script>

<style scoped lang="scss">
.activities-index {
  padding: 2rem;
}

.page-header {
  margin-bottom: 2rem;
}

.page-title {
  font-size: 2rem;
  font-weight: 700;
  color: var(--text-primary);
  margin-bottom: 0.5rem;
}

.filters-card {
  background: var(--surface-card);
  border-radius: var(--border-radius);
  padding: 1.5rem;
  margin-bottom: 2rem;
  box-shadow: var(--shadow-sm);
}

.filters-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1rem;
}

.filter-item {
  label {
    display: block;
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--text-secondary);
    margin-bottom: 0.5rem;
  }
}

.form-select {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid var(--border-color);
  border-radius: var(--border-radius);
  background: var(--surface-ground);
  color: var(--text-primary);
  font-size: 0.875rem;
  transition: border-color 0.2s;

  &:focus {
    outline: none;
    border-color: var(--primary-color);
  }
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.timeline-card {
  background: var(--surface-card);
  border-radius: var(--border-radius);
  padding: 2rem;
  box-shadow: var(--shadow-sm);
}

.card-title {
  font-size: 1.5rem;
  font-weight: 600;
  color: var(--text-primary);
  margin-bottom: 2rem;
  display: flex;
  align-items: center;
  gap: 0.75rem;

  i {
    color: var(--primary-color);
  }
}

.timeline-loading,
.timeline-empty {
  padding: 3rem;
  text-align: center;
  color: var(--text-secondary);

  i {
    font-size: 2rem;
    margin-bottom: 1rem;
    display: block;
  }
}

.timeline {
  position: relative;
}

.timeline-group {
  margin-bottom: 3rem;

  &:last-child {
    margin-bottom: 0;
  }
}

.timeline-date {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 1.125rem;
  font-weight: 600;
  color: var(--text-primary);
  margin-bottom: 1.5rem;
  padding-bottom: 0.5rem;
  border-bottom: 2px solid var(--border-color);

  i {
    color: var(--primary-color);
  }
}

.timeline-items {
  position: relative;
  padding-left: 2rem;

  &::before {
    content: '';
    position: absolute;
    left: 8px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: var(--border-color);
  }
}

.timeline-item {
  position: relative;
  margin-bottom: 2rem;

  &:last-child {
    margin-bottom: 0;
  }
}

.timeline-marker {
  position: absolute;
  left: -2rem;
  top: 0.25rem;
  width: 36px;
  height: 36px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--surface-card);
  border: 3px solid var(--border-color);
  z-index: 1;

  i {
    font-size: 0.875rem;
  }

  &.marker-call {
    border-color: #3B82F6;
    color: #3B82F6;
  }

  &.marker-meeting {
    border-color: #8B5CF6;
    color: #8B5CF6;
  }

  &.marker-email {
    border-color: #10B981;
    color: #10B981;
  }

  &.marker-note {
    border-color: #F59E0B;
    color: #F59E0B;
  }

  &.marker-task {
    border-color: #EF4444;
    color: #EF4444;
  }
}

.timeline-content {
  background: var(--surface-ground);
  border-radius: var(--border-radius);
  padding: 1rem;
  border-left: 3px solid var(--border-color);
}

.activity-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.75rem;
}

.activity-info {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.activity-type {
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;

  &.type-call { background: #DBEAFE; color: #1E40AF; }
  &.type-meeting { background: #EDE9FE; color: #6D28D9; }
  &.type-email { background: #D1FAE5; color: #065F46; }
  &.type-note { background: #FEF3C7; color: #92400E; }
  &.type-task { background: #FEE2E2; color: #991B1B; }
}

.activity-time {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  font-size: 0.875rem;
  color: var(--text-secondary);

  i {
    font-size: 0.75rem;
  }
}

.activity-user {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  color: var(--text-secondary);

  i {
    color: var(--primary-color);
  }
}

.activity-lead {
  margin-bottom: 0.75rem;
  font-size: 0.875rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;

  i {
    color: var(--primary-color);
  }

  a {
    color: var(--primary-color);
    text-decoration: none;
    font-weight: 600;

    &:hover {
      text-decoration: underline;
    }
  }
}

.activity-description {
  color: var(--text-primary);
  margin-bottom: 0.5rem;
  line-height: 1.5;
}

.activity-notes {
  display: flex;
  align-items: flex-start;
  gap: 0.5rem;
  padding: 0.75rem;
  background: var(--surface-card);
  border-radius: var(--border-radius);
  font-size: 0.875rem;
  color: var(--text-secondary);
  margin-top: 0.75rem;

  i {
    color: var(--primary-color);
    margin-top: 0.125rem;
  }
}

.activity-duration {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  color: var(--text-secondary);
  margin-top: 0.5rem;

  i {
    color: var(--primary-color);
  }
}

.pagination {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 2rem;
  margin-top: 2rem;
  border-top: 1px solid var(--border-color);
}

.pagination-info {
  color: var(--text-secondary);
  font-size: 0.875rem;
}

.pagination-buttons {
  display: flex;
  gap: 0.5rem;
}

.pagination-btn {
  padding: 0.5rem 0.75rem;
  background: var(--surface-ground);
  border: 1px solid var(--border-color);
  border-radius: var(--border-radius);
  color: var(--text-primary);
  cursor: pointer;
  transition: all 0.2s;

  &:hover:not(:disabled) {
    background: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
  }

  &.active {
    background: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
  }

  &:disabled {
    cursor: default;
    opacity: 0.5;
  }
}
</style>
