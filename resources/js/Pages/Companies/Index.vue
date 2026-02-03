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

