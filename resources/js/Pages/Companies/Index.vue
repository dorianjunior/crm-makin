<template>
  <MainLayout>
    <div class="companies-page">
      <Breadcrumbs :items="breadcrumbs" />

      <div class="page-header">
        <div>
          <h1>Empresas</h1>
          <p class="subtitle">Gerencie as empresas multi-tenant do sistema</p>
        </div>
        <Button variant="primary" @click="showCreateModal = true">
          <i class="fa fa-plus"></i>
          Nova Empresa
        </Button>
      </div>

      <!-- Stats -->
      <div class="stats-row">
        <StatCard
          label="Total de Empresas"
          :value="stats.total"
          icon="fa fa-building"
          color="primary"
        />
        <StatCard
          label="Ativas"
          :value="stats.active"
          icon="fa fa-check-circle"
          color="success"
        />
        <StatCard
          label="Suspensas"
          :value="stats.suspended"
          icon="fa fa-pause-circle"
          color="warning"
        />
        <StatCard
          label="Inativas"
          :value="stats.inactive"
          icon="fa fa-times-circle"
          color="error"
        />
      </div>

      <!-- Filters -->
      <div class="filters-card">
        <div class="filters-grid">
          <div class="filter-item">
            <label>Buscar</label>
            <Input
              v-model="filters.search"
              placeholder="Nome ou domínio..."
              icon="fa fa-search"
            />
          </div>

          <div class="filter-item">
            <label>Plano</label>
            <select v-model="filters.plan" class="form-select">
              <option value="">Todos</option>
              <option value="free">Free</option>
              <option value="basic">Basic</option>
              <option value="premium">Premium</option>
              <option value="enterprise">Enterprise</option>
            </select>
          </div>

          <div class="filter-item">
            <label>Status</label>
            <select v-model="filters.status" class="form-select">
              <option value="">Todos</option>
              <option value="active">Ativa</option>
              <option value="inactive">Inativa</option>
              <option value="suspended">Suspensa</option>
            </select>
          </div>

          <div class="filter-actions">
            <Button variant="secondary" @click="applyFilters">
              <i class="fa fa-filter"></i>
              Filtrar
            </Button>
            <Button variant="secondary" @click="clearFilters">
              <i class="fa fa-times"></i>
              Limpar
            </Button>
          </div>
        </div>
      </div>

      <!-- Companies Table -->
      <div class="table-card">
        <table class="data-table">
          <thead>
            <tr>
              <th>Empresa</th>
              <th>Domínio</th>
              <th>Plano</th>
              <th>Status</th>
              <th>Usuários</th>
              <th>Leads</th>
              <th>Criada em</th>
              <th>Ações</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="company in companies.data" :key="company.id">
              <td>
                <div class="company-info">
                  <div class="company-avatar">
                    <i class="fa fa-building"></i>
                  </div>
                  <div>
                    <strong>{{ company.name }}</strong>
                  </div>
                </div>
              </td>
              <td>
                <a :href="`https://${company.domain}`" target="_blank" class="domain-link">
                  {{ company.domain }}
                  <i class="fa fa-external-link-alt"></i>
                </a>
              </td>
              <td>
                <span :class="['plan-badge', `plan-${company.plan}`]">
                  {{ getPlanLabel(company.plan) }}
                </span>
              </td>
              <td>
                <span :class="['status-badge', company.status]">
                  {{ getStatusLabel(company.status) }}
                </span>
              </td>
              <td>{{ company.users_count }}</td>
              <td>{{ company.leads_count }}</td>
              <td>{{ formatDate(company.created_at) }}</td>
              <td>
                <div class="action-buttons">
                  <button @click="viewCompany(company)" class="action-btn" title="Visualizar">
                    <i class="fa fa-eye"></i>
                  </button>
                  <button @click="editCompany(company)" class="action-btn" title="Editar">
                    <i class="fa fa-edit"></i>
                  </button>
                  <button
                    @click="toggleCompanyStatus(company)"
                    class="action-btn"
                    :title="company.status === 'active' ? 'Suspender' : 'Ativar'"
                  >
                    <i :class="company.status === 'active' ? 'fa fa-pause' : 'fa fa-play'"></i>
                  </button>
                  <button @click="deleteCompany(company)" class="action-btn danger" title="Excluir">
                    <i class="fa fa-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Pagination -->
        <div class="pagination">
          <button
            @click="goToPage(companies.current_page - 1)"
            :disabled="!companies.prev_page_url"
            class="pagination-btn"
          >
            <i class="fa fa-chevron-left"></i>
          </button>

          <span class="pagination-info">
            Página {{ companies.current_page }} de {{ companies.last_page }}
          </span>

          <button
            @click="goToPage(companies.current_page + 1)"
            :disabled="!companies.next_page_url"
            class="pagination-btn"
          >
            <i class="fa fa-chevron-right"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <Modal v-if="showCreateModal || showEditModal" @close="closeModal" size="large">
      <template #header>
        <h2>{{ showEditModal ? 'Editar Empresa' : 'Nova Empresa' }}</h2>
      </template>

      <template #body>
        <div class="modal-form">
          <div class="form-row">
            <div class="form-group">
              <label>Nome da Empresa *</label>
              <Input v-model="form.name" placeholder="Acme Inc." />
            </div>

            <div class="form-group">
              <label>Domínio *</label>
              <Input v-model="form.domain" placeholder="acme.com" />
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Plano *</label>
              <select v-model="form.plan" class="form-select">
                <option value="free">Free</option>
                <option value="basic">Basic</option>
                <option value="premium">Premium</option>
                <option value="enterprise">Enterprise</option>
              </select>
            </div>

            <div class="form-group">
              <label>Status *</label>
              <select v-model="form.status" class="form-select">
                <option value="active">Ativa</option>
                <option value="inactive">Inativa</option>
                <option value="suspended">Suspensa</option>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label>Email Principal</label>
            <Input v-model="form.email" type="email" placeholder="contato@acme.com" />
          </div>

          <div class="form-group">
            <label>Telefone</label>
            <Input v-model="form.phone" placeholder="(00) 0000-0000" />
          </div>

          <div class="form-group">
            <label>Notas</label>
            <textarea v-model="form.notes" rows="3" placeholder="Observações sobre a empresa..."></textarea>
          </div>

          <div v-if="showEditModal" class="info-section">
            <h4>Informações Adicionais</h4>
            <div class="info-grid">
              <div class="info-item">
                <span class="label">Usuários:</span>
                <span class="value">{{ currentCompany?.users_count }}</span>
              </div>
              <div class="info-item">
                <span class="label">Leads:</span>
                <span class="value">{{ currentCompany?.leads_count }}</span>
              </div>
              <div class="info-item">
                <span class="label">Sites:</span>
                <span class="value">{{ currentCompany?.sites_count }}</span>
              </div>
              <div class="info-item">
                <span class="label">Criada em:</span>
                <span class="value">{{ formatDate(currentCompany?.created_at) }}</span>
              </div>
            </div>
          </div>
        </div>
      </template>

      <template #footer>
        <Button variant="secondary" @click="closeModal">
          Cancelar
        </Button>
        <Button variant="primary" @click="saveCompany" :disabled="saving">
          <i class="fa fa-save"></i>
          Salvar
        </Button>
      </template>
    </Modal>

    <!-- View Modal -->
    <Modal v-if="showViewModal" @close="showViewModal = false" size="large">
      <template #header>
        <h2>Detalhes da Empresa</h2>
      </template>

      <template #body>
        <div class="company-details">
          <div class="detail-section">
            <h3>Informações Básicas</h3>
            <div class="detail-grid">
              <div class="detail-item">
                <span class="label">Nome:</span>
                <span class="value">{{ currentCompany?.name }}</span>
              </div>
              <div class="detail-item">
                <span class="label">Domínio:</span>
                <span class="value">
                  <a :href="`https://${currentCompany?.domain}`" target="_blank">
                    {{ currentCompany?.domain }}
                  </a>
                </span>
              </div>
              <div class="detail-item">
                <span class="label">Email:</span>
                <span class="value">{{ currentCompany?.email || 'N/A' }}</span>
              </div>
              <div class="detail-item">
                <span class="label">Telefone:</span>
                <span class="value">{{ currentCompany?.phone || 'N/A' }}</span>
              </div>
              <div class="detail-item">
                <span class="label">Plano:</span>
                <span :class="['plan-badge', `plan-${currentCompany?.plan}`]">
                  {{ getPlanLabel(currentCompany?.plan) }}
                </span>
              </div>
              <div class="detail-item">
                <span class="label">Status:</span>
                <span :class="['status-badge', currentCompany?.status]">
                  {{ getStatusLabel(currentCompany?.status) }}
                </span>
              </div>
            </div>
          </div>

          <div class="detail-section">
            <h3>Estatísticas</h3>
            <div class="stats-grid">
              <div class="stat-item">
                <i class="fa fa-users"></i>
                <span class="stat-value">{{ currentCompany?.users_count }}</span>
                <span class="stat-label">Usuários</span>
              </div>
              <div class="stat-item">
                <i class="fa fa-user-friends"></i>
                <span class="stat-value">{{ currentCompany?.leads_count }}</span>
                <span class="stat-label">Leads</span>
              </div>
              <div class="stat-item">
                <i class="fa fa-globe"></i>
                <span class="stat-value">{{ currentCompany?.sites_count }}</span>
                <span class="stat-label">Sites</span>
              </div>
              <div class="stat-item">
                <i class="fa fa-file-invoice-dollar"></i>
                <span class="stat-value">{{ currentCompany?.proposals_count }}</span>
                <span class="stat-label">Propostas</span>
              </div>
            </div>
          </div>

          <div v-if="currentCompany?.notes" class="detail-section">
            <h3>Notas</h3>
            <p>{{ currentCompany.notes }}</p>
          </div>
        </div>
      </template>

      <template #footer>
        <Button variant="secondary" @click="showViewModal = false">
          Fechar
        </Button>
      </template>
    </Modal>
  </MainLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import Button from '@/Components/Button.vue';
import Input from '@/Components/Input.vue';
import StatCard from '@/Components/StatCard.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
  companies: Object,
  stats: Object
});

const showCreateModal = ref(false);
const showEditModal = ref(false);
const showViewModal = ref(false);
const saving = ref(false);
const currentCompany = ref(null);

const filters = ref({
  search: '',
  plan: '',
  status: ''
});

const form = ref({
  name: '',
  domain: '',
  plan: 'basic',
  status: 'active',
  email: '',
  phone: '',
  notes: ''
});

const breadcrumbs = [
  { label: 'Empresas' }
];

const getPlanLabel = (plan) => {
  const labels = {
    free: 'Free',
    basic: 'Basic',
    premium: 'Premium',
    enterprise: 'Enterprise'
  };
  return labels[plan] || plan;
};

const getStatusLabel = (status) => {
  const labels = {
    active: 'Ativa',
    inactive: 'Inativa',
    suspended: 'Suspensa'
  };
  return labels[status] || status;
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('pt-BR');
};

const applyFilters = () => {
  router.get('/companies', filters.value, {
    preserveState: true,
    preserveScroll: true
  });
};

const clearFilters = () => {
  filters.value = {
    search: '',
    plan: '',
    status: ''
  };
  applyFilters();
};

const viewCompany = (company) => {
  currentCompany.value = company;
  showViewModal.value = true;
};

const editCompany = (company) => {
  currentCompany.value = company;
  form.value = { ...company };
  showEditModal.value = true;
};

const toggleCompanyStatus = (company) => {
  const newStatus = company.status === 'active' ? 'suspended' : 'active';

  router.put(`/companies/${company.id}`, {
    status: newStatus
  }, {
    preserveScroll: true
  });
};

const deleteCompany = (company) => {
  if (confirm(`Tem certeza que deseja excluir a empresa "${company.name}"? Esta ação não pode ser desfeita.`)) {
    router.delete(`/companies/${company.id}`, {
      preserveScroll: true
    });
  }
};

const saveCompany = () => {
  saving.value = true;

  const url = showEditModal.value
    ? `/companies/${currentCompany.value.id}`
    : '/companies';

  const method = showEditModal.value ? 'put' : 'post';

  router[method](url, form.value, {
    preserveScroll: true,
    onSuccess: () => {
      closeModal();
    },
    onFinish: () => {
      saving.value = false;
    }
  });
};

const closeModal = () => {
  showCreateModal.value = false;
  showEditModal.value = false;
  currentCompany.value = null;
  form.value = {
    name: '',
    domain: '',
    plan: 'basic',
    status: 'active',
    email: '',
    phone: '',
    notes: ''
  };
};

const goToPage = (page) => {
  router.get('/companies', {
    ...filters.value,
    page
  }, {
    preserveState: true,
    preserveScroll: true
  });
};
</script>

<style scoped lang="scss">
.companies-page {
  padding: 2rem;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 2rem;

  h1 {
    margin: 0 0 0.5rem 0;
    color: var(--text-primary);
  }

  .subtitle {
    margin: 0;
    color: var(--text-secondary);
  }
}

.stats-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.filters-card {
  background: var(--bg-primary);
  border: 1px solid var(--border-color);
  border-radius: 8px;
  padding: 1.5rem;
  margin-bottom: 2rem;

  .filters-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    align-items: end;

    .filter-item {
      label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: var(--text-primary);
      }

      .form-select {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid var(--border-color);
        border-radius: 4px;
        background: var(--bg-primary);
        color: var(--text-primary);

        &:focus {
          outline: none;
          border-color: var(--color-primary);
        }
      }
    }

    .filter-actions {
      display: flex;
      gap: 0.5rem;
    }
  }
}

.table-card {
  background: var(--bg-primary);
  border: 1px solid var(--border-color);
  border-radius: 8px;
  overflow: hidden;

  .data-table {
    width: 100%;
    border-collapse: collapse;

    thead {
      background: var(--bg-secondary);

      th {
        padding: 1rem;
        text-align: left;
        font-weight: 600;
        color: var(--text-primary);
        border-bottom: 1px solid var(--border-color);
      }
    }

    tbody {
      tr {
        border-bottom: 1px solid var(--border-color);
        transition: background 0.2s;

        &:hover {
          background: var(--bg-secondary);
        }

        &:last-child {
          border-bottom: none;
        }

        td {
          padding: 1rem;
          color: var(--text-primary);
        }
      }
    }
  }

  .pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    border-top: 1px solid var(--border-color);

    .pagination-btn {
      padding: 0.5rem 1rem;
      border: 1px solid var(--border-color);
      background: var(--bg-primary);
      color: var(--text-primary);
      border-radius: 4px;
      cursor: pointer;

      &:disabled {
        opacity: 0.5;
        cursor: not-allowed;
      }

      &:not(:disabled):hover {
        background: var(--bg-secondary);
      }
    }

    .pagination-info {
      color: var(--text-secondary);
    }
  }
}

.company-info {
  display: flex;
  align-items: center;
  gap: 1rem;

  .company-avatar {
    width: 40px;
    height: 40px;
    background: var(--color-primary);
    color: white;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
  }
}

.domain-link {
  color: var(--color-primary);
  text-decoration: none;
  display: flex;
  align-items: center;
  gap: 0.5rem;

  &:hover {
    text-decoration: underline;
  }

  i {
    font-size: 0.8rem;
  }
}

.plan-badge {
  padding: 0.35rem 0.75rem;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 500;

  &.plan-free {
    background: rgba(107, 114, 128, 0.1);
    color: #6b7280;
  }

  &.plan-basic {
    background: rgba(59, 130, 246, 0.1);
    color: var(--color-info);
  }

  &.plan-premium {
    background: rgba(139, 92, 246, 0.1);
    color: #8b5cf6;
  }

  &.plan-enterprise {
    background: rgba(16, 185, 129, 0.1);
    color: var(--color-success);
  }
}

.status-badge {
  padding: 0.35rem 0.75rem;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 500;

  &.active {
    background: rgba(16, 185, 129, 0.1);
    color: var(--color-success);
  }

  &.inactive {
    background: rgba(107, 114, 128, 0.1);
    color: #6b7280;
  }

  &.suspended {
    background: rgba(245, 158, 11, 0.1);
    color: var(--color-warning);
  }
}

.action-buttons {
  display: flex;
  gap: 0.5rem;

  .action-btn {
    width: 32px;
    height: 32px;
    border: 1px solid var(--border-color);
    background: var(--bg-primary);
    color: var(--text-primary);
    border-radius: 4px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;

    &:hover {
      background: var(--bg-secondary);
      border-color: var(--color-primary);
      color: var(--color-primary);
    }

    &.danger:hover {
      border-color: var(--color-error);
      color: var(--color-error);
    }
  }
}

// Modal Forms
.modal-form {
  .form-row {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
    margin-bottom: 1rem;
  }

  .form-group {
    margin-bottom: 1rem;

    label {
      display: block;
      margin-bottom: 0.5rem;
      font-weight: 500;
      color: var(--text-primary);
    }

    .form-select {
      width: 100%;
      padding: 0.75rem;
      border: 1px solid var(--border-color);
      border-radius: 4px;
      background: var(--bg-primary);
      color: var(--text-primary);

      &:focus {
        outline: none;
        border-color: var(--color-primary);
      }
    }

    textarea {
      width: 100%;
      padding: 0.75rem;
      border: 1px solid var(--border-color);
      border-radius: 4px;
      font-family: inherit;
      background: var(--bg-primary);
      color: var(--text-primary);
      resize: vertical;

      &:focus {
        outline: none;
        border-color: var(--color-primary);
      }
    }
  }

  .info-section {
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid var(--border-color);

    h4 {
      margin: 0 0 1rem 0;
      color: var(--text-primary);
    }

    .info-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 1rem;

      .info-item {
        display: flex;
        justify-content: space-between;
        padding: 0.75rem;
        background: var(--bg-secondary);
        border-radius: 4px;

        .label {
          color: var(--text-secondary);
        }

        .value {
          color: var(--text-primary);
          font-weight: 500;
        }
      }
    }
  }
}

// Company Details
.company-details {
  .detail-section {
    margin-bottom: 2rem;

    &:last-child {
      margin-bottom: 0;
    }

    h3 {
      margin: 0 0 1rem 0;
      color: var(--text-primary);
      font-size: 1.1rem;
      padding-bottom: 0.75rem;
      border-bottom: 1px solid var(--border-color);
    }

    p {
      margin: 0;
      color: var(--text-secondary);
      line-height: 1.6;
    }
  }

  .detail-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;

    .detail-item {
      display: flex;
      justify-content: space-between;
      padding: 0.75rem;
      background: var(--bg-secondary);
      border-radius: 4px;

      .label {
        color: var(--text-secondary);
      }

      .value {
        color: var(--text-primary);
        font-weight: 500;

        a {
          color: var(--color-primary);
          text-decoration: none;

          &:hover {
            text-decoration: underline;
          }
        }
      }
    }
  }

  .stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;

    .stat-item {
      text-align: center;
      padding: 1.5rem;
      background: var(--bg-secondary);
      border-radius: 8px;

      i {
        font-size: 2rem;
        color: var(--color-primary);
        margin-bottom: 0.5rem;
      }

      .stat-value {
        display: block;
        font-size: 1.5rem;
        font-weight: bold;
        color: var(--text-primary);
        margin-bottom: 0.25rem;
      }

      .stat-label {
        display: block;
        font-size: 0.85rem;
        color: var(--text-secondary);
      }
    }
  }
}

@media (max-width: 768px) {
  .companies-page {
    padding: 1rem;
  }

  .table-card {
    overflow-x: auto;
  }

  .modal-form .form-row,
  .company-details .detail-grid,
  .company-details .stats-grid {
    grid-template-columns: 1fr;
  }
}
</style>
