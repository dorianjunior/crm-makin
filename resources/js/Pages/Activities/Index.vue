<template>
    <MainLayout title="Atividades">
        <template #breadcrumbs>
            <Breadcrumbs :items="breadcrumbs" />
        </template>

        <div class="page-container">
            <!-- Header -->
            <div class="page-header">
                <div>
                    <h1 class="page-title">Atividades</h1>
                    <p class="page-subtitle">Acompanhe todas as atividades e interações com leads</p>
                </div>
            </div>

            <!-- Stats -->
            <div class="stats-grid">
                <StatCard title="Total de Atividades" :value="stats.total" icon="fa fa-clipboard-list" color="blue" />
                <StatCard title="Hoje" :value="stats.today" icon="fa fa-calendar-day" color="green" />
                <StatCard title="Esta Semana" :value="stats.this_week" icon="fa fa-calendar-week" color="purple" />
                <StatCard title="Este Mês" :value="stats.this_month" icon="fa fa-calendar" color="orange" />
            </div>

            <!-- Filters -->
            <div class="filters-card">
                <div class="filters-header">
                    <div class="filters-title">
                        <i class="fa fa-filter"></i>
                        <span>FILTROS</span>
                    </div>
                </div>
                <div class="filters-grid">
                    <Input v-model="filters.search" placeholder="Buscar atividades..." icon="fa-search"
                        @input="debouncedSearch" />

                    <Select v-model="filters.type" label="Tipo" :options="typeOptions" placeholder="Todos os tipos"
                        @update:modelValue="loadActivities" />

                    <Select v-model="filters.lead_id" label="Lead" :options="leadOptions" placeholder="Todos os leads"
                        @update:modelValue="loadActivities" />

                    <Select v-model="filters.user_id" label="Usuário" :options="userOptions"
                        placeholder="Todos os usuários" @update:modelValue="loadActivities" />

                    <button class="btn btn--secondary" @click="clearFilters">
                        <i class="fas fa-times"></i>
                        Limpar
                    </button>
                </div>
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
                <Pagination v-if="activities.data.length > 0" :from="activities.from" :to="activities.to"
                    :total="activities.total" :current-page="activities.current_page" :last-page="activities.last_page"
                    @page-change="changePage" />
            </div>
        </div>
    </MainLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { useAlert } from '@/composables/useAlert';
import MainLayout from '@/Layouts/MainLayout.vue';
import Input from '@/Components/Input.vue';
import Select from '@/Components/Select.vue';
import StatCard from '@/Components/StatCard.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    activities: Object,
    leads: Array,
    users: Array,
    stats: Object,
});

const alert = useAlert();

const breadcrumbs = [
    { name: 'Dashboard', href: '/dashboard' },
    { name: 'Atividades' }
];

const loading = ref(false);
const filters = ref({
    search: '',
    type: '',
    lead_id: '',
    user_id: '',
});

const typeOptions = computed(() => [
    { value: '', label: 'Todos os tipos' },
    { value: 'call', label: 'Ligação' },
    { value: 'meeting', label: 'Reunião' },
    { value: 'email', label: 'Email' },
    { value: 'note', label: 'Nota' },
    { value: 'task', label: 'Tarefa' },
]);

const leadOptions = computed(() => [
    { value: '', label: 'Todos os leads' },
    ...props.leads.map(lead => ({ value: lead.id, label: lead.name }))
]);

const userOptions = computed(() => [
    { value: '', label: 'Todos os usuários' },
    ...props.users.map(user => ({ value: user.id, label: user.name }))
]);

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
        onError: () => {
            alert.error('Erro ao carregar', 'Não foi possível carregar as atividades.');
        },
    });
};

const clearFilters = () => {
    filters.value = {
        search: '',
        type: '',
        lead_id: '',
        user_id: '',
    };
    loadActivities();
    alert.toast('Filtros limpos!', 'success');
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
