<template>
  <MainLayout title="Leads">
    <template #breadcrumbs>
      <Breadcrumbs :items="breadcrumbs" />
    </template>

    <div class="leads-page">
      <!-- Header -->
      <div class="leads-page__header">
        <div class="leads-page__header-content">
          <h1 class="leads-page__title">Leads</h1>
          <p class="leads-page__subtitle">Gerencie seus leads e oportunidades</p>
        </div>
        <Button
          variant="accent"
          icon="fa-plus"
          @click="createLead"
        >
          Novo Lead
        </Button>
      </div>

      <!-- Stats Cards -->
      <div class="leads-page__stats">
        <StatCard
          title="Total de Leads"
          :value="stats.total"
          icon="fa-users"
        />
        <StatCard
          title="Novos (este mês)"
          :value="stats.new_this_month"
          icon="fa-user-plus"
        />
        <StatCard
          title="Qualificados"
          :value="stats.qualified"
          icon="fa-star"
        />
        <StatCard
          title="Taxa de Conversão"
          :value="stats.conversion_rate + '%'"
          icon="fa-chart-line"
        />
      </div>

      <!-- Filters -->
      <Card padding="md">
        <div class="leads-page__filters">
          <Input
            v-model="filters.search"
            placeholder="Buscar por nome, email ou telefone..."
            icon="fa-search"
            @input="debouncedSearch"
          />

          <Select
            v-model="filters.status"
            label="Status"
            :options="statusOptions"
            placeholder="Todos os status"
            @update:modelValue="loadLeads"
          />

          <Select
            v-model="filters.source_id"
            label="Fonte"
            :options="sourceOptions"
            placeholder="Todas as fontes"
            @update:modelValue="loadLeads"
          />

          <Select
            v-model="filters.assigned_to"
            label="Responsável"
            :options="userOptions"
            placeholder="Todos os responsáveis"
            @update:modelValue="loadLeads"
          />

          <Button
            variant="ghost"
            icon="fa-times"
            @click="clearFilters"
          >
            Limpar
          </Button>
        </div>
      </Card>

      <!-- Table -->
      <Card padding="none">
        <template #header>
          <div class="leads-page__table-header">
            <h2 class="leads-page__table-title">Lista de Leads</h2>
            <Button
              variant="ghost"
              size="sm"
              icon="fa-download"
              @click="exportLeads"
            >
              Exportar
            </Button>
          </div>
        </template>

        <Table
          :columns="columns"
          :data="leads.data"
          :loading="loading"
          empty-text="Nenhum lead encontrado"
          hoverable
        >
          <template #cell-name="{ row }">
            <div class="leads-page__cell-name">
              <strong>{{ row.name }}</strong>
              <span v-if="row.company" class="leads-page__cell-company">{{ row.company }}</span>
            </div>
          </template>

          <template #cell-status="{ row }">
            <Badge :variant="getStatusVariant(row.status)">
              {{ statusLabel(row.status) }}
            </Badge>
          </template>

          <template #cell-source="{ row }">
            <Badge variant="default">
              {{ row.source?.name || '-' }}
            </Badge>
          </template>

          <template #cell-assigned="{ row }">
            <div v-if="row.assigned_user" class="leads-page__cell-user">
              <i class="fas fa-user-circle"></i>
              {{ row.assigned_user.name }}
            </div>
            <span v-else class="leads-page__cell-empty">-</span>
          </template>

          <template #cell-created="{ row }">
            {{ formatDate(row.created_at) }}
          </template>

          <template #cell-actions="{ row }">
            <div class="leads-page__actions">
              <Button
                variant="ghost"
                size="sm"
                icon="fa-eye"
                @click="viewLead(row)"
              />
              <Button
                variant="ghost"
                size="sm"
                icon="fa-edit"
                @click="editLead(row)"
              />
              <Button
                variant="ghost"
                size="sm"
                icon="fa-trash"
                @click="deleteLead(row)"
              />
            </div>
          </template>
        </Table>

        <template #footer>
          <div class="leads-page__pagination">
            <div class="leads-page__pagination-info">
              Mostrando {{ leads.from }} a {{ leads.to }} de {{ leads.total }} registros
            </div>
            <div class="leads-page__pagination-buttons">
              <Button
                v-for="page in paginationPages"
                :key="page"
                :variant="page === leads.current_page ? 'accent' : 'ghost'"
                size="sm"
                :disabled="page === '...'"
                @click="changePage(page)"
              >
                {{ page }}
              </Button>
            </div>
          </div>
        </template>
      </Card>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Button from '@/Components/Button.vue';
import Input from '@/Components/Input.vue';
import StatCard from '@/Components/StatCard.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
  leads: Object,
  sources: Array,
  users: Array,
  stats: Object,
});

const breadcrumbs = [
  { label: 'Dashboard', to: '/dashboard' },
  { label: 'Leads', active: true }
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
  { field: 'phone', label: 'Telefone', align: 'left', field: (row) => row.phone || '-' },
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

const paginationPages = computed(() => {
  const pages = [];
  const current = props.leads.current_page;
  const last = props.leads.last_page;

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
};
</script>

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
import { useAlert } from '@/composables/useAlert';

const alert = useAlert();

const props = defineProps({
  leads: Object,
  sources: Array,
  users: Array,
  stats: Object,
});

const breadcrumbs = [
  { name: 'Dashboard', href: '/dashboard' },
  { name: 'Leads'
    ...filters.value,
    sort: sortField.value,
    direction: sortDirection.value,
  }, {
    preserveState: true,
    preserveScroll: true,
    onFinish: () => loading.value = false,
  });
};

const sort = (field) => {
  if (sortField.value === field) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortField.value = field;
    sortDirection.value = 'asc';
  }async (lead) => {
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

const getStatusVariant = (status) => {
  const variants = {
    new: 'info',
    contacted: 'primary',
    qualified: 'success',
    negotiation: 'warning',
    won: 'success',
    lost: 'danger',
  };
  return variants[status] || 'default'adLeads();
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

const deleteLead = (lead) => {
  leadToDelete.value = lead;
  showDeleteModal.value = true;
};

const confirmDelete = () => {
  router.delete(`/leads/${leadToDelete.value.id}`, {
    onSuccess: () => {
      showDeleteModal.value = false;
      leadToDelete.value = null;
    },
  });
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

<style scoped>
.leads-page {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

/* Header */
.leads-page__header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 24px;
}

.leads-page__header-content {
  flex: 1;
}

.leads-page__title {
  font-family: 'Space Grotesk', sans-serif;
  font-size: 32px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  color: var(--text-primary);
  margin: 0 0 8px;
}

.leads-page__subtitle {
  font-family: 'Inter', sans-serif;
  font-size: 14px;
  color: var(--text-secondary);
  margin: 0;
}

/* Stats */
.leads-page__stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
}

/* Filters */
.leads-page__filters {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr 1fr auto;
  gap: 16px;
  align-items: end;
}

@media (max-width: 1200px) {
  .leads-page__filters {
    grid-template-columns: 1fr 1fr;
  }
}

@media (max-width: 768px) {
  .leads-page__filters {
    grid-template-columns: 1fr;
  }
}

/* Table Header */
.leads-page__table-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
}

.leads-page__table-title {
  font-family: 'Space Grotesk', sans-serif;
  font-size: 18px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: var(--text-primary);
  margin: 0;
}

/* Table Cells */
.leads-page__cell-name {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.leads-page__cell-company {
  font-size: 12px;
  color: var(--text-secondary);
}

.leads-page__cell-user {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  color: var(--text-primary);
}

.leads-page__cell-user i {
  color: var(--text-secondary);
}

.leads-page__cell-empty {
  color: var(--text-muted);
}

/* Actions */
.leads-page__actions {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 4px;
}

/* Pagination */
.leads-page__pagination {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  padding: 20px 24px;
}

.leads-page__pagination-info {
  font-family: 'Inter', sans-serif;
  font-size: 13px;
  color: var(--text-secondary);
}

.leads-page__pagination-buttons {
  display: flex;
  align-items: center;
  gap: 4px;
}

@media (max-width: 768px) {
  .leads-page__header {
    flex-direction: column;
    align-items: stretch;
  }

  .leads-page__stats {
    grid-template-columns: 1fr;
  }

  .leads-page__pagination {
    flex-direction: column;
    align-items: flex-start;
  }

  .leads-page__pagination-buttons {
    width: 100%;
    flex-wrap: wrap;
  }
}
</style>
