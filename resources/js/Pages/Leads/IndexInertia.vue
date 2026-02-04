<template>
    <MainLayout title="Leads">
        <template #breadcrumbs>
            <Breadcrumbs :items="breadcrumbs" />
        </template>

        <div class="page-container">
            <!-- Header -->
            <div class="page-header">
                <div class="page-header__content">
                    <h1 class="page-header__title">LEADS</h1>
                    <p class="page-header__subtitle">Gerencie seus leads e oportunidades de negócio</p>
                </div>
                <div class="page-header__actions">
                    <button class="btn btn--sm btn--ghost" @click="refresh" title="Atualizar">
                        <i class="fas fa-sync-alt" :class="{ 'fa-spin': loading }"></i>
                    </button>
                    <button class="btn" @click="createLead">
                        <i class="fas fa-plus"></i>
                        Novo Lead
                    </button>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid--4">
                <div class="stat-card">
                    <div class="stat-card__icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-card__label">Total de Leads</div>
                    <div class="stat-card__value">{{ stats.total }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card__icon">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <div class="stat-card__label">Novos (este mês)</div>
                    <div class="stat-card__value">{{ stats.new_this_month }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card__icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-card__label">Qualificados</div>
                    <div class="stat-card__value">{{ stats.qualified }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card__icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-card__label">Taxa de Conversão</div>
                    <div class="stat-card__value">{{ stats.conversion_rate }}%</div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card" style="margin-top: 32px;">
                <div class="card__body">
                    <div class="filters-grid">
                        <Input
                            v-model="localFilters.search"
                            placeholder="Buscar por nome, email, telefone ou empresa..."
                            icon="fa-search"
                            @input="debouncedSearch"
                        />

                        <Select
                            v-model="localFilters.status"
                            label="Status"
                            :options="statusOptions"
                            placeholder="Todos os status"
                            @update:modelValue="applyFilters"
                        />

                        <Select
                            v-model="localFilters.source_id"
                            label="Fonte"
                            :options="sourceOptions"
                            placeholder="Todas as fontes"
                            @update:modelValue="applyFilters"
                        />

                        <Select
                            v-model="localFilters.assigned_to"
                            label="Responsável"
                            :options="userOptions"
                            placeholder="Todos os responsáveis"
                            @update:modelValue="applyFilters"
                        />

                        <button class="btn btn--secondary" @click="clearFilters">
                            <i class="fas fa-times"></i>
                            Limpar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="card" style="margin-top: 32px;">
                <div class="table__header">
                    <h3 class="section-header__title">LISTA DE LEADS</h3>
                    <button class="btn btn--sm btn--secondary" @click="exportLeads">
                        <i class="fas fa-download"></i>
                        Exportar
                    </button>
                </div>

                <Table
                    :columns="columns"
                    :data="leads.data"
                    :loading="loading"
                    empty-text="Nenhum lead encontrado"
                    hoverable
                >
                    <template #cell-name="{ row }">
                        <div class="cell-name">
                            <strong>{{ row.name }}</strong>
                            <span v-if="row.company" class="cell-company">{{ row.company }}</span>
                        </div>
                    </template>

                    <template #cell-status="{ row }">
                        <span :class="['badge', `badge--${getStatusVariant(row.status)}`]">
                            {{ statusLabel(row.status) }}
                        </span>
                    </template>

                    <template #cell-source="{ row }">
                        <span class="badge badge--neutral">
                            {{ row.source?.name || '-' }}
                        </span>
                    </template>

                    <template #cell-assigned="{ row }">
                        <div v-if="row.assigned_user" class="cell-user">
                            <i class="fas fa-user-circle"></i>
                            {{ row.assigned_user.name }}
                        </div>
                        <span v-else class="cell-empty">-</span>
                    </template>

                    <template #cell-created="{ row }">
                        {{ formatDate(row.created_at) }}
                    </template>

                    <template #cell-actions="{ row }">
                        <div class="action-buttons">
                            <button class="action-btn" @click="editLead(row)" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="action-btn action-btn--danger" @click="deleteLead(row)" title="Excluir">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </template>
                </Table>

                <Pagination
                    :from="leads.from"
                    :to="leads.to"
                    :total="leads.total"
                    :current-page="leads.current_page"
                    :last-page="leads.last_page"
                    @page-change="changePage"
                />
            </div>
        </div>
    </MainLayout>
</template>

<script setup>
import { ref, computed, reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Input from '@/Components/Input.vue';
import Select from '@/Components/Select.vue';
import Table from '@/Components/Table.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import Pagination from '@/Components/Pagination.vue';
import { useAlert } from '@/composables/useAlert';

const alert = useAlert();

// Props vem do Inertia automaticamente ✨
const props = defineProps({
    leads: {
        type: Object,
        required: true
    },
    sources: {
        type: Array,
        default: () => []
    },
    users: {
        type: Array,
        default: () => []
    },
    stats: {
        type: Object,
        required: true
    },
    filters: {
        type: Object,
        default: () => ({})
    }
});

const breadcrumbs = [
    { name: 'Dashboard', href: '/dashboard' },
    { name: 'Leads' }
];

const loading = ref(false);

// Filtros locais (reativos)
const localFilters = reactive({
    search: props.filters.search || '',
    status: props.filters.status || '',
    source_id: props.filters.source_id || '',
    assigned_to: props.filters.assigned_to || '',
});

// Table columns
const columns = [
    { field: 'name', label: 'Nome', align: 'left' },
    { field: 'email', label: 'Email', align: 'left' },
    { field: 'phone', label: 'Telefone', align: 'left' },
    { field: 'status', label: 'Status', align: 'center', width: '120px' },
    { field: 'source', label: 'Fonte', align: 'center', width: '120px' },
    { field: 'assigned', label: 'Responsável', align: 'left', width: '150px' },
    { field: 'created', label: 'Criado em', align: 'center', width: '120px' },
    { field: 'actions', label: 'Ações', align: 'center', width: '100px' },
];

// Select options
const statusOptions = [
    { value: '', label: 'Todos os status' },
    { value: 'new', label: 'Novo' },
    { value: 'contacted', label: 'Contatado' },
    { value: 'qualified', label: 'Qualificado' },
    { value: 'negotiation', label: 'Negociação' },
    { value: 'won', label: 'Ganho' },
    { value: 'lost', label: 'Perdido' },
];

const sourceOptions = computed(() => [
    { value: '', label: 'Todas as fontes' },
    ...props.sources.map(source => ({ value: source.id, label: source.name }))
]);

const userOptions = computed(() => [
    { value: '', label: 'Todos os responsáveis' },
    ...props.users.map(user => ({ value: user.id, label: user.name }))
]);

// Debounced search
let searchTimeout;
const debouncedSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 500);
};

// Apply filters using Inertia
const applyFilters = () => {
    router.get('/leads', localFilters, {
        preserveState: true,
        preserveScroll: true,
        only: ['leads', 'stats'],
        onStart: () => loading.value = true,
        onFinish: () => loading.value = false,
    });
};

// Clear filters
const clearFilters = () => {
    localFilters.search = '';
    localFilters.status = '';
    localFilters.source_id = '';
    localFilters.assigned_to = '';
    applyFilters();
};

// Refresh data
const refresh = () => {
    router.reload({ only: ['leads', 'stats'] });
};

// Delete lead
const deleteLead = async (lead) => {
    const confirmed = await alert.confirmDelete('lead', lead.name);

    if (confirmed) {
        router.delete(`/leads/${lead.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                alert.success('Lead deletado com sucesso!');
            },
            onError: () => {
                alert.error('Erro ao deletar lead. Tente novamente.');
            },
        });
    }
};

// Navigation
const changePage = (page) => {
    if (page === '...') return;
    router.get(`/leads?page=${page}`, localFilters, {
        preserveState: true,
        preserveScroll: true,
    });
};

const createLead = () => {
    router.visit('/leads/create');
};

const editLead = (lead) => {
    router.visit(`/leads/${lead.id}/edit`);
};

const exportLeads = () => {
    window.location.href = '/leads/export?' + new URLSearchParams(localFilters);
};

// Helpers
const getStatusVariant = (status) => {
    const variants = {
        new: 'info',
        contacted: 'primary',
        qualified: 'success',
        negotiation: 'warning',
        won: 'success',
        lost: 'danger',
    };
    return variants[status] || 'default';
};

const statusLabel = (status) => {
    const labels = {
        new: 'Novo',
        contacted: 'Contatado',
        qualified: 'Qualificado',
        negotiation: 'Negociação',
        won: 'Ganho',
        lost: 'Perdido',
    };
    return labels[status] || status;
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
    });
};
</script>

<style scoped lang="scss">
.page-container {
    padding: 32px;
}

.page-header__actions {
    display: flex;
    align-items: center;
    gap: 12px;
}

.btn--ghost {
    background: transparent;
    border-color: transparent;

    &:hover {
        background: var(--bg-secondary);
    }
}

.fa-spin {
    animation: fa-spin 1s infinite linear;
}

@keyframes fa-spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.filters-grid {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr 1fr auto;
    gap: 16px;
    align-items: end;
}

@media (max-width: 1200px) {
    .filters-grid {
        grid-template-columns: 1fr 1fr;
    }
}

@media (max-width: 768px) {
    .filters-grid {
        grid-template-columns: 1fr;
    }
}

.table__header {
    padding: 24px;
    border-bottom: 2px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.cell-name {
    display: flex;
    flex-direction: column;
    gap: 4px;

    strong {
        color: var(--text-primary);
    }
}

.cell-company {
    font-size: 12px;
    color: var(--text-secondary);
}

.cell-user {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: var(--text-primary);

    i {
        color: #FF6B35;
    }
}

.cell-empty {
    color: var(--text-muted);
}

.action-buttons {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.action-btn {
    width: 32px;
    height: 32px;
    border: 2px solid var(--border-color);
    background: var(--bg-primary);
    color: var(--text-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;

    &:hover {
        border-color: #FF6B35;
        color: #FF6B35;
    }

    &--danger:hover {
        border-color: #dc3545;
        color: #dc3545;
    }
}
</style>
