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

