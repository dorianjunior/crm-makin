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
                        <Input v-model="filters.search" placeholder="Buscar por nome, email ou telefone..."
                            icon="fa-search" @input="debouncedSearch" />

                        <Select v-model="filters.status" label="Status" :options="statusOptions"
                            placeholder="Todos os status" @update:modelValue="loadLeads" />

                        <Select v-model="filters.source_id" label="Fonte" :options="sourceOptions"
                            placeholder="Todas as fontes" @update:modelValue="loadLeads" />

                        <Select v-model="filters.assigned_to" label="Responsável" :options="userOptions"
                            placeholder="Todos os responsáveis" @update:modelValue="loadLeads" />

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

                <Table :columns="columns" :data="leads.data" :loading="loading" empty-text="Nenhum lead encontrado"
                    hoverable>
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
                            <button class="action-btn" @click="viewLead(row)" title="Ver">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="action-btn" @click="editLead(row)" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="action-btn action-btn--danger" @click="deleteLead(row)" title="Excluir">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </template>
                </Table>

                <Pagination :from="leads.from" :to="leads.to" :total="leads.total" :current-page="leads.current_page"
                    :last-page="leads.last_page" @page-change="changePage" />
            </div>
        </div>
    </MainLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Button from '@/Components/Button.vue';
import Input from '@/Components/Input.vue';
import Select from '@/Components/Select.vue';
import Badge from '@/Components/Badge.vue';
import Card from '@/Components/Card.vue';
import Table from '@/Components/Table.vue';
import StatCard from '@/Components/StatCard.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import Pagination from '@/Components/Pagination.vue';
import { useAlert } from '@/composables/useAlert';

const alert = useAlert();

const props = defineProps({
    leads: {
        type: Object,
        default: () => ({
            data: [],
            from: 0,
            to: 0,
            total: 0,
            current_page: 1,
            last_page: 1
        })
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
        default: () => ({
            total: 0,
            new_this_month: 0,
            qualified: 0,
            conversion_rate: 0
        })
    },
});

const breadcrumbs = [
    { name: 'Dashboard', href: '/dashboard' },
    { name: 'Leads' }
];

const loading = ref(false);
const filters = ref({
    search: '',
    status: '',
    source_id: '',
    assigned_to: '',
});

const sortField = ref('created_at');
const sortDirection = ref('desc');

// Table columns
const columns = [
    { field: 'name', label: 'Nome', align: 'left' },
    { field: 'email', label: 'Email', align: 'left' },
    { field: 'phone', label: 'Telefone', align: 'left' },
    { field: 'status', label: 'Status', align: 'center', width: '120px' },
    { field: 'source', label: 'Fonte', align: 'center', width: '120px' },
    { field: 'assigned', label: 'Responsável', align: 'left', width: '150px' },
    { field: 'created', label: 'Criado em', align: 'center', width: '120px' },
    { field: 'actions', label: 'Ações', align: 'center', width: '140px' },
];

// Select options
const statusOptions = computed(() => [
    { value: '', label: 'Todos os status' },
    { value: 'new', label: 'Novo' },
    { value: 'contacted', label: 'Contatado' },
    { value: 'qualified', label: 'Qualificado' },
    { value: 'negotiation', label: 'Negociação' },
    { value: 'won', label: 'Ganho' },
    { value: 'lost', label: 'Perdido' },
]);

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
        loadLeads();
    }, 500);
};

// Load leads with filters
const loadLeads = () => {
    loading.value = true;
    router.get('/leads', {
        ...filters.value,
        sort: sortField.value,
        direction: sortDirection.value,
    }, {
        preserveState: true,
        preserveScroll: true,
        onFinish: () => loading.value = false,
    });
};

// Clear filters
const clearFilters = () => {
    filters.value = {
        search: '',
        status: '',
        source_id: '',
        assigned_to: '',
    };
    loadLeads();
};

// Delete lead
const deleteLead = async (lead) => {
    const confirmed = await alert.confirmDelete('lead', lead.name);

    if (confirmed) {
        router.delete(`/leads/${lead.id}`, {
            onSuccess: () => {
                alert.success('Lead deletado com sucesso!');
            },
            onError: () => {
                alert.error('Erro ao deletar lead. Tente novamente.');
            },
        });
    }
};

// Get status variant
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

const changePage = (page) => {
    if (page === '...') return;
    router.get(`/leads?page=${page}`, filters.value, {
        preserveState: true,
    });
};

const createLead = () => {
    router.visit('/leads/create');
};

const viewLead = (lead) => {
    router.visit(`/leads/${lead.id}`);
};

const editLead = (lead) => {
    router.visit(`/leads/${lead.id}/edit`);
};

const exportLeads = () => {
    window.location.href = '/leads/export?' + new URLSearchParams(filters.value);
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

onMounted(() => {
    // Inicialização se necessário
});
</script>

<style scoped lang="scss">
.page-container {
    padding: 32px;
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
