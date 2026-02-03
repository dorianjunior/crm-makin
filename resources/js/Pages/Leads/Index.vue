<template>
  <MainLayout>
    <div class="leads-index">
      <!-- Header -->
      <div class="page-header">
        <div>
          <h1 class="page-title">Leads</h1>
          <Breadcrumbs :items="breadcrumbs" />
        </div>
        <Button
          label="Novo Lead"
          icon="fa fa-plus"
          @click="createLead"
          severity="success"
        />
      </div>

      <!-- Filters -->
      <div class="filters-card">
        <div class="filters-grid">
          <div class="filter-item">
            <label>Buscar</label>
            <Input
              v-model="filters.search"
              placeholder="Nome, email ou telefone..."
              icon="fa fa-search"
              @input="debouncedSearch"
            />
          </div>

          <div class="filter-item">
            <label>Status</label>
            <select v-model="filters.status" @change="loadLeads" class="form-select">
              <option value="">Todos</option>
              <option value="new">Novo</option>
              <option value="contacted">Contatado</option>
              <option value="qualified">Qualificado</option>
              <option value="negotiation">Negociação</option>
              <option value="won">Ganho</option>
              <option value="lost">Perdido</option>
            </select>
          </div>

          <div class="filter-item">
            <label>Fonte</label>
            <select v-model="filters.source_id" @change="loadLeads" class="form-select">
              <option value="">Todas</option>
              <option v-for="source in sources" :key="source.id" :value="source.id">
                {{ source.name }}
              </option>
            </select>
          </div>

          <div class="filter-item">
            <label>Responsável</label>
            <select v-model="filters.assigned_to" @change="loadLeads" class="form-select">
              <option value="">Todos</option>
              <option v-for="user in users" :key="user.id" :value="user.id">
                {{ user.name }}
              </option>
            </select>
          </div>
        </div>

        <div class="filters-actions">
          <Button
            label="Limpar Filtros"
            @click="clearFilters"
            severity="secondary"
            size="small"
            outlined
          />
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="stats-grid">
        <StatCard
          title="Total de Leads"
          :value="stats.total"
          icon="fa fa-users"
          color="blue"
        />
        <StatCard
          title="Novos (este mês)"
          :value="stats.new_this_month"
          icon="fa fa-user-plus"
          color="green"
        />
        <StatCard
          title="Qualificados"
          :value="stats.qualified"
          icon="fa fa-star"
          color="purple"
        />
        <StatCard
          title="Taxa de Conversão"
          :value="stats.conversion_rate + '%'"
          icon="fa fa-chart-line"
          color="orange"
        />
      </div>

      <!-- Table -->
      <div class="table-card">
        <div class="table-header">
          <h2>Lista de Leads</h2>
          <div class="table-actions">
            <Button
              label="Exportar"
              icon="fa fa-download"
              @click="exportLeads"
              size="small"
              outlined
            />
          </div>
        </div>

        <div v-if="loading" class="table-loading">
          <i class="fa fa-spinner fa-spin"></i> Carregando...
        </div>

        <div v-else-if="leads.data.length === 0" class="table-empty">
          <i class="fa fa-inbox"></i>
          <p>Nenhum lead encontrado</p>
        </div>

        <table v-else class="data-table">
          <thead>
            <tr>
              <th @click="sort('name')">
                Nome
                <i v-if="sortField === 'name'" :class="sortIcon"></i>
              </th>
              <th @click="sort('email')">
                Email
                <i v-if="sortField === 'email'" :class="sortIcon"></i>
              </th>
              <th>Telefone</th>
              <th @click="sort('status')">
                Status
                <i v-if="sortField === 'status'" :class="sortIcon"></i>
              </th>
              <th>Fonte</th>
              <th>Responsável</th>
              <th @click="sort('created_at')">
                Criado em
                <i v-if="sortField === 'created_at'" :class="sortIcon"></i>
              </th>
              <th class="text-center">Ações</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="lead in leads.data" :key="lead.id">
              <td>
                <div class="lead-name">
                  <strong>{{ lead.name }}</strong>
                  <span v-if="lead.company" class="lead-company">{{ lead.company }}</span>
                </div>
              </td>
              <td>{{ lead.email }}</td>
              <td>{{ lead.phone || '-' }}</td>
              <td>
                <span :class="['badge', 'badge-' + lead.status]">
                  {{ statusLabel(lead.status) }}
                </span>
              </td>
              <td>
                <span class="source-badge">
                  {{ lead.source?.name || '-' }}
                </span>
              </td>
              <td>
                <div v-if="lead.assigned_user" class="user-badge">
                  <i class="fa fa-user-circle"></i>
                  {{ lead.assigned_user.name }}
                </div>
                <span v-else class="text-muted">-</span>
              </td>
              <td>{{ formatDate(lead.created_at) }}</td>
              <td class="text-center">
                <div class="action-buttons">
                  <button @click="viewLead(lead)" class="btn-icon" title="Ver">
                    <i class="fa fa-eye"></i>
                  </button>
                  <button @click="editLead(lead)" class="btn-icon" title="Editar">
                    <i class="fa fa-edit"></i>
                  </button>
                  <button @click="deleteLead(lead)" class="btn-icon btn-danger" title="Excluir">
                    <i class="fa fa-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Pagination -->
        <div v-if="leads.data.length > 0" class="pagination">
          <div class="pagination-info">
            Mostrando {{ leads.from }} a {{ leads.to }} de {{ leads.total }} registros
          </div>
          <div class="pagination-buttons">
            <button
              @click="changePage(page)"
              v-for="page in paginationPages"
              :key="page"
              :class="['pagination-btn', { active: page === leads.current_page }]"
              :disabled="page === '...'"
            >
              {{ page }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de confirmação de exclusão -->
    <Modal
      v-model:visible="showDeleteModal"
      title="Confirmar Exclusão"
      @confirm="confirmDelete"
    >
      <p>Tem certeza que deseja excluir o lead <strong>{{ leadToDelete?.name }}</strong>?</p>
      <p class="text-muted">Esta ação não pode ser desfeita.</p>
    </Modal>
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
const showDeleteModal = ref(false);
const leadToDelete = ref(null);

const sortIcon = computed(() => {
  return sortDirection.value === 'asc' ? 'fa fa-sort-up' : 'fa fa-sort-down';
});

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

  return pages;
});

let searchTimeout = null;
const debouncedSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    loadLeads();
  }, 500);
};

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

const sort = (field) => {
  if (sortField.value === field) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortField.value = field;
    sortDirection.value = 'asc';
  }
  loadLeads();
};

const clearFilters = () => {
  filters.value = {
    search: '',
    status: '',
    source_id: '',
    assigned_to: '',
  };
  loadLeads();
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

<style scoped lang="scss">
.leads-index {
  padding: 2rem;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
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
  margin-bottom: 1rem;
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

.filters-actions {
  display: flex;
  justify-content: flex-end;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.table-card {
  background: var(--surface-card);
  border-radius: var(--border-radius);
  box-shadow: var(--shadow-sm);
  overflow: hidden;
}

.table-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-bottom: 1px solid var(--border-color);

  h2 {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--text-primary);
  }
}

.table-loading,
.table-empty {
  padding: 3rem;
  text-align: center;
  color: var(--text-secondary);
  font-size: 1rem;

  i {
    font-size: 2rem;
    margin-bottom: 1rem;
    display: block;
  }
}

.data-table {
  width: 100%;
  border-collapse: collapse;

  th {
    padding: 1rem 1.5rem;
    text-align: left;
    font-weight: 600;
    color: var(--text-secondary);
    background: var(--surface-ground);
    border-bottom: 2px solid var(--border-color);
    cursor: pointer;
    user-select: none;
    transition: background 0.2s;

    &:hover {
      background: var(--surface-hover);
    }

    i {
      margin-left: 0.5rem;
      font-size: 0.875rem;
    }
  }

  td {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid var(--border-color);
    color: var(--text-primary);
  }

  tbody tr {
    transition: background 0.2s;

    &:hover {
      background: var(--surface-hover);
    }
  }
}

.lead-name {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;

  strong {
    color: var(--text-primary);
  }

  .lead-company {
    font-size: 0.8rem;
    color: var(--text-secondary);
  }
}

.badge {
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  font-size: 0.8rem;
  font-weight: 600;
  text-transform: uppercase;

  &.badge-new { background: #e3f2fd; color: #1976d2; }
  &.badge-contacted { background: #f3e5f5; color: #7b1fa2; }
  &.badge-qualified { background: #e8f5e9; color: #388e3c; }
  &.badge-negotiation { background: #fff3e0; color: #f57c00; }
  &.badge-won { background: #c8e6c9; color: #2e7d32; }
  &.badge-lost { background: #ffcdd2; color: #c62828; }
}

.source-badge {
  padding: 0.25rem 0.5rem;
  background: var(--surface-ground);
  border-radius: 4px;
  font-size: 0.8rem;
  color: var(--text-secondary);
}

.user-badge {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;

  i {
    color: var(--primary-color);
  }
}

.action-buttons {
  display: flex;
  gap: 0.5rem;
  justify-content: center;
}

.btn-icon {
  padding: 0.5rem;
  background: transparent;
  border: none;
  border-radius: var(--border-radius);
  color: var(--text-secondary);
  cursor: pointer;
  transition: all 0.2s;

  &:hover {
    background: var(--surface-ground);
    color: var(--primary-color);
  }

  &.btn-danger:hover {
    color: var(--red-500);
  }
}

.pagination {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
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

.text-center {
  text-align: center;
}

.text-muted {
  color: var(--text-secondary);
}
</style>
