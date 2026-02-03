<template>
  <MainLayout>
    <div class="users-page">
      <Breadcrumbs :items="breadcrumbs" />

      <div class="page-header">
        <div>
          <h1>Usuários</h1>
          <p class="subtitle">Gerencie os usuários do sistema</p>
        </div>
        <Button variant="primary" @click="showUserModal = true">
          <i class="fa fa-plus"></i>
          Novo Usuário
        </Button>
      </div>

      <!-- Stats -->
      <div class="stats-grid">
        <StatCard
          title="Total de Usuários"
          :value="stats.total"
          icon="users"
          color="blue"
        />
        <StatCard
          title="Ativos"
          :value="stats.active"
          icon="user-check"
          color="green"
        />
        <StatCard
          title="Inativos"
          :value="stats.inactive"
          icon="user-times"
          color="orange"
        />
        <StatCard
          title="Online Agora"
          :value="stats.online"
          icon="circle"
          color="purple"
        />
      </div>

      <!-- Filters & Search -->
      <div class="filters-section card">
        <div class="search-bar">
          <i class="fa fa-search"></i>
          <Input
            v-model="searchQuery"
            placeholder="Buscar por nome, email ou telefone..."
            @input="handleSearch"
          />
        </div>

        <div class="filters">
          <div class="filter-group">
            <label>Status:</label>
            <select v-model="filters.status" @change="applyFilters" class="filter-select">
              <option value="">Todos</option>
              <option value="active">Ativos</option>
              <option value="inactive">Inativos</option>
            </select>
          </div>

          <div class="filter-group">
            <label>Função:</label>
            <select v-model="filters.role" @change="applyFilters" class="filter-select">
              <option value="">Todas</option>
              <option v-for="role in roles" :key="role.id" :value="role.id">
                {{ role.name }}
              </option>
            </select>
          </div>

          <div class="filter-group">
            <label>Empresa:</label>
            <select v-model="filters.company" @change="applyFilters" class="filter-select">
              <option value="">Todas</option>
              <option v-for="company in companies" :key="company.id" :value="company.id">
                {{ company.name }}
              </option>
            </select>
          </div>

          <Button variant="secondary" size="small" @click="clearFilters">
            <i class="fa fa-times"></i>
            Limpar
          </Button>
        </div>
      </div>

      <!-- Users Table -->
      <div class="card">
        <div class="table-header">
          <div class="bulk-actions" v-if="selectedUsers.length > 0">
            <span>{{ selectedUsers.length }} selecionado(s)</span>
            <Button variant="danger" size="small" @click="bulkDelete">
              <i class="fa fa-trash"></i>
              Excluir Selecionados
            </Button>
          </div>

          <div class="table-actions">
            <Button variant="link" size="small" @click="exportUsers">
              <i class="fa fa-download"></i>
              Exportar
            </Button>
          </div>
        </div>

        <div class="table-responsive">
          <table class="users-table">
            <thead>
              <tr>
                <th>
                  <input
                    type="checkbox"
                    @change="toggleSelectAll"
                    :checked="selectedUsers.length === users.data.length"
                  >
                </th>
                <th @click="sortBy('name')" class="sortable">
                  Usuário
                  <i class="fa fa-sort"></i>
                </th>
                <th @click="sortBy('email')" class="sortable">
                  Email
                  <i class="fa fa-sort"></i>
                </th>
                <th>Função</th>
                <th>Empresa</th>
                <th @click="sortBy('created_at')" class="sortable">
                  Data de Cadastro
                  <i class="fa fa-sort"></i>
                </th>
                <th>Status</th>
                <th>Último Acesso</th>
                <th class="text-right">Ações</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="user in users.data" :key="user.id">
                <td>
                  <input
                    type="checkbox"
                    :value="user.id"
                    v-model="selectedUsers"
                  >
                </td>
                <td>
                  <div class="user-info">
                    <div class="user-avatar">
                      <img v-if="user.avatar" :src="user.avatar" :alt="user.name">
                      <div v-else class="avatar-placeholder">
                        {{ getInitials(user.name) }}
                      </div>
                      <span v-if="user.is_online" class="online-indicator"></span>
                    </div>
                    <div>
                      <strong>{{ user.name }}</strong>
                      <span v-if="user.phone" class="user-meta">{{ user.phone }}</span>
                    </div>
                  </div>
                </td>
                <td>{{ user.email }}</td>
                <td>
                  <span class="role-badge" :style="{ background: user.role?.color }">
                    {{ user.role?.name }}
                  </span>
                </td>
                <td>{{ user.company?.name || '-' }}</td>
                <td>{{ formatDate(user.created_at) }}</td>
                <td>
                  <span class="status-badge" :class="user.status">
                    {{ user.status === 'active' ? 'Ativo' : 'Inativo' }}
                  </span>
                </td>
                <td>
                  <span v-if="user.last_login_at">
                    {{ formatRelativeTime(user.last_login_at) }}
                  </span>
                  <span v-else class="text-muted">Nunca</span>
                </td>
                <td class="text-right">
                  <div class="action-buttons">
                    <button
                      @click="viewUser(user)"
                      class="action-btn"
                      title="Ver Detalhes"
                    >
                      <i class="fa fa-eye"></i>
                    </button>
                    <button
                      @click="editUser(user)"
                      class="action-btn"
                      title="Editar"
                    >
                      <i class="fa fa-edit"></i>
                    </button>
                    <button
                      @click="toggleUserStatus(user)"
                      class="action-btn"
                      :title="user.status === 'active' ? 'Desativar' : 'Ativar'"
                    >
                      <i :class="user.status === 'active' ? 'fa fa-ban' : 'fa fa-check'"></i>
                    </button>
                    <button
                      @click="sendPasswordReset(user)"
                      class="action-btn"
                      title="Enviar Reset de Senha"
                    >
                      <i class="fa fa-key"></i>
                    </button>
                    <button
                      @click="deleteUser(user)"
                      class="action-btn danger"
                      title="Excluir"
                    >
                      <i class="fa fa-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="pagination">
          <div class="pagination-info">
            Exibindo {{ users.from }} a {{ users.to }} de {{ users.total }} usuários
          </div>
          <div class="pagination-buttons">
            <button
              v-for="page in paginationPages"
              :key="page"
              @click="changePage(page)"
              :class="{ active: page === users.current_page }"
              :disabled="page === '...'"
            >
              {{ page }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- User Modal (Create/Edit) -->
    <Modal
      v-if="showUserModal"
      @close="closeUserModal"
      :title="editingUser ? 'Editar Usuário' : 'Novo Usuário'"
      size="large"
    >
      <div class="user-form">
        <div class="form-row">
          <div class="form-group">
            <label>Nome Completo *</label>
            <Input v-model="userForm.name" placeholder="Digite o nome completo" />
            <span v-if="modalErrors.name" class="error-message">{{ modalErrors.name }}</span>
          </div>

          <div class="form-group">
            <label>Email *</label>
            <Input v-model="userForm.email" type="email" placeholder="usuario@email.com" />
            <span v-if="modalErrors.email" class="error-message">{{ modalErrors.email }}</span>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Telefone</label>
            <Input v-model="userForm.phone" placeholder="(00) 00000-0000" />
          </div>

          <div class="form-group">
            <label>CPF</label>
            <Input v-model="userForm.cpf" placeholder="000.000.000-00" />
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Função *</label>
            <select v-model="userForm.role_id" class="form-select">
              <option value="">Selecione uma função</option>
              <option v-for="role in roles" :key="role.id" :value="role.id">
                {{ role.name }}
              </option>
            </select>
            <span v-if="modalErrors.role_id" class="error-message">{{ modalErrors.role_id }}</span>
          </div>

          <div class="form-group">
            <label>Empresa</label>
            <select v-model="userForm.company_id" class="form-select">
              <option value="">Selecione uma empresa</option>
              <option v-for="company in companies" :key="company.id" :value="company.id">
                {{ company.name }}
              </option>
            </select>
          </div>
        </div>

        <div v-if="!editingUser" class="form-row">
          <div class="form-group">
            <label>Senha *</label>
            <Input
              v-model="userForm.password"
              type="password"
              placeholder="Mínimo 8 caracteres"
            />
            <span v-if="modalErrors.password" class="error-message">{{ modalErrors.password }}</span>
          </div>

          <div class="form-group">
            <label>Confirmar Senha *</label>
            <Input
              v-model="userForm.password_confirmation"
              type="password"
              placeholder="Repita a senha"
            />
          </div>
        </div>

        <div class="form-group">
          <label>Cargo</label>
          <Input v-model="userForm.position" placeholder="Ex: Gerente de Vendas" />
        </div>

        <div class="form-group">
          <label>Departamento</label>
          <select v-model="userForm.department" class="form-select">
            <option value="">Selecione um departamento</option>
            <option value="sales">Vendas</option>
            <option value="marketing">Marketing</option>
            <option value="support">Suporte</option>
            <option value="development">Desenvolvimento</option>
            <option value="management">Gestão</option>
            <option value="finance">Financeiro</option>
            <option value="hr">Recursos Humanos</option>
          </select>
        </div>

        <div class="form-group">
          <label>Observações</label>
          <textarea
            v-model="userForm.notes"
            rows="3"
            placeholder="Notas internas sobre o usuário"
          ></textarea>
        </div>

        <div class="form-group">
          <label class="checkbox-label">
            <input type="checkbox" v-model="userForm.send_welcome_email">
            Enviar email de boas-vindas com credenciais
          </label>
        </div>

        <div class="form-group">
          <label class="checkbox-label">
            <input type="checkbox" v-model="userForm.is_active">
            Usuário ativo
          </label>
        </div>
      </div>

      <template #footer>
        <Button variant="secondary" @click="closeUserModal">Cancelar</Button>
        <Button variant="primary" @click="saveUser" :disabled="savingUser">
          {{ editingUser ? 'Atualizar' : 'Criar Usuário' }}
        </Button>
      </template>
    </Modal>

    <!-- User Details Modal -->
    <Modal
      v-if="showDetailsModal"
      @close="showDetailsModal = false"
      :title="selectedUser?.name"
      size="large"
    >
      <div class="user-details">
        <div class="details-header">
          <div class="user-avatar-large">
            <img v-if="selectedUser.avatar" :src="selectedUser.avatar" :alt="selectedUser.name">
            <div v-else class="avatar-placeholder">
              {{ getInitials(selectedUser.name) }}
            </div>
          </div>
          <div class="user-info-large">
            <h2>{{ selectedUser.name }}</h2>
            <p class="user-email">{{ selectedUser.email }}</p>
            <div class="user-badges">
              <span class="role-badge" :style="{ background: selectedUser.role?.color }">
                {{ selectedUser.role?.name }}
              </span>
              <span class="status-badge" :class="selectedUser.status">
                {{ selectedUser.status === 'active' ? 'Ativo' : 'Inativo' }}
              </span>
            </div>
          </div>
        </div>

        <div class="details-grid">
          <div class="detail-section">
            <h3>Informações Pessoais</h3>
            <div class="detail-item">
              <strong>Telefone:</strong>
              <span>{{ selectedUser.phone || '-' }}</span>
            </div>
            <div class="detail-item">
              <strong>CPF:</strong>
              <span>{{ selectedUser.cpf || '-' }}</span>
            </div>
            <div class="detail-item">
              <strong>Cargo:</strong>
              <span>{{ selectedUser.position || '-' }}</span>
            </div>
            <div class="detail-item">
              <strong>Departamento:</strong>
              <span>{{ selectedUser.department || '-' }}</span>
            </div>
          </div>

          <div class="detail-section">
            <h3>Informações da Conta</h3>
            <div class="detail-item">
              <strong>Empresa:</strong>
              <span>{{ selectedUser.company?.name || '-' }}</span>
            </div>
            <div class="detail-item">
              <strong>Data de Cadastro:</strong>
              <span>{{ formatDate(selectedUser.created_at) }}</span>
            </div>
            <div class="detail-item">
              <strong>Último Acesso:</strong>
              <span>{{ selectedUser.last_login_at ? formatDate(selectedUser.last_login_at) : 'Nunca' }}</span>
            </div>
            <div class="detail-item">
              <strong>IP do Último Acesso:</strong>
              <span>{{ selectedUser.last_login_ip || '-' }}</span>
            </div>
          </div>

          <div class="detail-section full-width">
            <h3>Permissões</h3>
            <div class="permissions-list">
              <span
                v-for="permission in selectedUser.role?.permissions"
                :key="permission.id"
                class="permission-badge"
              >
                {{ permission.name }}
              </span>
              <span v-if="!selectedUser.role?.permissions?.length" class="text-muted">
                Nenhuma permissão atribuída
              </span>
            </div>
          </div>

          <div class="detail-section full-width" v-if="selectedUser.notes">
            <h3>Observações</h3>
            <p>{{ selectedUser.notes }}</p>
          </div>
        </div>
      </div>
    </Modal>
  </MainLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import Button from '@/Components/Button.vue';
import Input from '@/Components/Input.vue';
import Modal from '@/Components/Modal.vue';
import StatCard from '@/Components/StatCard.vue';

const props = defineProps({
  users: Object,
  roles: Array,
  companies: Array,
  stats: Object,
  filters: Object,
  errors: Object
});

const searchQuery = ref('');
const selectedUsers = ref([]);
const showUserModal = ref(false);
const showDetailsModal = ref(false);
const editingUser = ref(null);
const selectedUser = ref(null);
const savingUser = ref(false);
const modalErrors = ref({});

const filters = ref({
  status: props.filters?.status || '',
  role: props.filters?.role || '',
  company: props.filters?.company || ''
});

const userForm = ref({
  name: '',
  email: '',
  phone: '',
  cpf: '',
  role_id: '',
  company_id: '',
  password: '',
  password_confirmation: '',
  position: '',
  department: '',
  notes: '',
  send_welcome_email: true,
  is_active: true
});

const breadcrumbs = [
  { label: 'Admin', url: '/admin' },
  { label: 'Usuários' }
];

const paginationPages = computed(() => {
  const current = props.users.current_page;
  const last = props.users.last_page;
  const pages = [];

  if (last <= 7) {
    for (let i = 1; i <= last; i++) {
      pages.push(i);
    }
  } else {
    if (current <= 3) {
      for (let i = 1; i <= 5; i++) pages.push(i);
      pages.push('...');
      pages.push(last);
    } else if (current >= last - 2) {
      pages.push(1);
      pages.push('...');
      for (let i = last - 4; i <= last; i++) pages.push(i);
    } else {
      pages.push(1);
      pages.push('...');
      for (let i = current - 1; i <= current + 1; i++) pages.push(i);
      pages.push('...');
      pages.push(last);
    }
  }

  return pages;
});

const handleSearch = () => {
  router.get('/admin/users', { search: searchQuery.value, ...filters.value }, {
    preserveState: true,
    preserveScroll: true
  });
};

const applyFilters = () => {
  router.get('/admin/users', { search: searchQuery.value, ...filters.value }, {
    preserveState: true,
    preserveScroll: true
  });
};

const clearFilters = () => {
  filters.value = { status: '', role: '', company: '' };
  searchQuery.value = '';
  router.get('/admin/users', {}, { preserveState: true });
};

const sortBy = (field) => {
  router.get('/admin/users', {
    search: searchQuery.value,
    ...filters.value,
    sort: field
  }, {
    preserveState: true,
    preserveScroll: true
  });
};

const changePage = (page) => {
  if (page === '...') return;
  router.get('/admin/users', {
    search: searchQuery.value,
    ...filters.value,
    page
  }, {
    preserveState: true,
    preserveScroll: true
  });
};

const toggleSelectAll = (e) => {
  selectedUsers.value = e.target.checked ? props.users.data.map(u => u.id) : [];
};

const editUser = (user) => {
  editingUser.value = user;
  userForm.value = {
    name: user.name,
    email: user.email,
    phone: user.phone || '',
    cpf: user.cpf || '',
    role_id: user.role_id,
    company_id: user.company_id || '',
    position: user.position || '',
    department: user.department || '',
    notes: user.notes || '',
    is_active: user.status === 'active',
    send_welcome_email: false
  };
  showUserModal.value = true;
};

const viewUser = (user) => {
  selectedUser.value = user;
  showDetailsModal.value = true;
};

const closeUserModal = () => {
  showUserModal.value = false;
  editingUser.value = null;
  modalErrors.value = {};
  userForm.value = {
    name: '',
    email: '',
    phone: '',
    cpf: '',
    role_id: '',
    company_id: '',
    password: '',
    password_confirmation: '',
    position: '',
    department: '',
    notes: '',
    send_welcome_email: true,
    is_active: true
  };
};

const saveUser = () => {
  savingUser.value = true;
  const url = editingUser.value ? `/admin/users/${editingUser.value.id}` : '/admin/users';
  const method = editingUser.value ? 'put' : 'post';

  router[method](url, userForm.value, {
    preserveState: true,
    preserveScroll: true,
    onSuccess: () => {
      closeUserModal();
    },
    onError: (errors) => {
      modalErrors.value = errors;
    },
    onFinish: () => {
      savingUser.value = false;
    }
  });
};

const toggleUserStatus = (user) => {
  if (confirm(`Tem certeza que deseja ${user.status === 'active' ? 'desativar' : 'ativar'} este usuário?`)) {
    router.patch(`/admin/users/${user.id}/toggle-status`, {}, {
      preserveState: true,
      preserveScroll: true
    });
  }
};

const sendPasswordReset = (user) => {
  if (confirm(`Enviar email de reset de senha para ${user.email}?`)) {
    router.post(`/admin/users/${user.id}/password-reset`, {}, {
      preserveState: true,
      preserveScroll: true
    });
  }
};

const deleteUser = (user) => {
  if (confirm(`Tem certeza que deseja excluir o usuário ${user.name}? Esta ação não pode ser desfeita.`)) {
    router.delete(`/admin/users/${user.id}`, {
      preserveState: true,
      preserveScroll: true
    });
  }
};

const bulkDelete = () => {
  if (confirm(`Excluir ${selectedUsers.value.length} usuário(s) selecionado(s)? Esta ação não pode ser desfeita.`)) {
    router.post('/admin/users/bulk-delete', {
      user_ids: selectedUsers.value
    }, {
      preserveState: true,
      preserveScroll: true,
      onSuccess: () => {
        selectedUsers.value = [];
      }
    });
  }
};

const exportUsers = () => {
  window.open('/admin/users/export?' + new URLSearchParams({
    search: searchQuery.value,
    ...filters.value
  }), '_blank');
};

const getInitials = (name) => {
  return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('pt-BR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  });
};

const formatRelativeTime = (datetime) => {
  const seconds = Math.floor((new Date() - new Date(datetime)) / 1000);
  if (seconds < 60) return 'Agora';
  if (seconds < 3600) return `${Math.floor(seconds / 60)}m atrás`;
  if (seconds < 86400) return `${Math.floor(seconds / 3600)}h atrás`;
  return `${Math.floor(seconds / 86400)}d atrás`;
};
</script>

<style scoped lang="scss">
.users-page {
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

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.filters-section {
  margin-bottom: 2rem;
  padding: 1.5rem;

  .search-bar {
    position: relative;
    margin-bottom: 1.5rem;

    i {
      position: absolute;
      left: 1rem;
      top: 50%;
      transform: translateY(-50%);
      color: var(--text-secondary);
    }

    input {
      padding-left: 3rem;
    }
  }

  .filters {
    display: flex;
    gap: 1rem;
    align-items: flex-end;
    flex-wrap: wrap;
  }

  .filter-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;

    label {
      font-size: 0.85rem;
      font-weight: 500;
      color: var(--text-secondary);
    }
  }

  .filter-select {
    padding: 0.75rem;
    border: 1px solid var(--border-color);
    border-radius: 4px;
    background: var(--surface-card);
    color: var(--text-primary);
    min-width: 150px;

    &:focus {
      outline: none;
      border-color: var(--primary-color);
    }
  }
}

.card {
  background: var(--surface-card);
  border: 1px solid var(--border-color);
  border-radius: 8px;
  overflow: hidden;
}

.table-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.5rem;
  border-bottom: 1px solid var(--border-color);

  .bulk-actions {
    display: flex;
    align-items: center;
    gap: 1rem;

    span {
      font-weight: 500;
      color: var(--text-primary);
    }
  }

  .table-actions {
    display: flex;
    gap: 0.5rem;
  }
}

.table-responsive {
  overflow-x: auto;
}

.users-table {
  width: 100%;
  border-collapse: collapse;

  thead {
    background: var(--surface-ground);

    th {
      padding: 1rem 1.5rem;
      text-align: left;
      font-weight: 600;
      color: var(--text-primary);
      font-size: 0.9rem;
      white-space: nowrap;

      &.sortable {
        cursor: pointer;
        user-select: none;

        &:hover {
          color: var(--primary-color);
        }

        i {
          margin-left: 0.5rem;
          opacity: 0.5;
        }
      }

      &.text-right {
        text-align: right;
      }
    }
  }

  tbody {
    tr {
      border-bottom: 1px solid var(--border-color);
      transition: background 0.2s;

      &:hover {
        background: var(--surface-hover);
      }

      td {
        padding: 1rem 1.5rem;
        color: var(--text-primary);

        &.text-right {
          text-align: right;
        }
      }
    }
  }
}

.user-info {
  display: flex;
  align-items: center;
  gap: 1rem;

  .user-avatar {
    position: relative;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    overflow: hidden;
    flex-shrink: 0;

    img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .avatar-placeholder {
      width: 100%;
      height: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
      background: var(--primary-color);
      color: white;
      font-weight: 600;
      font-size: 0.9rem;
    }

    .online-indicator {
      position: absolute;
      bottom: 0;
      right: 0;
      width: 12px;
      height: 12px;
      background: #27ae60;
      border: 2px solid var(--surface-card);
      border-radius: 50%;
    }
  }

  strong {
    display: block;
    margin-bottom: 0.25rem;
  }

  .user-meta {
    display: block;
    font-size: 0.85rem;
    color: var(--text-secondary);
  }
}

.role-badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  font-size: 0.85rem;
  font-weight: 500;
  color: white;
}

.status-badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  font-size: 0.85rem;
  font-weight: 500;

  &.active {
    background: #d4edda;
    color: #155724;
  }

  &.inactive {
    background: #f8d7da;
    color: #721c24;
  }
}

.action-buttons {
  display: flex;
  gap: 0.5rem;
  justify-content: flex-end;

  .action-btn {
    padding: 0.5rem 0.75rem;
    border: 1px solid var(--border-color);
    background: var(--surface-card);
    border-radius: 4px;
    cursor: pointer;
    color: var(--text-primary);
    transition: all 0.2s;

    &:hover {
      background: var(--surface-hover);
      border-color: var(--primary-color);
      color: var(--primary-color);
    }

    &.danger:hover {
      border-color: #e74c3c;
      color: #e74c3c;
    }
  }
}

.pagination {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.5rem;
  border-top: 1px solid var(--border-color);

  .pagination-info {
    color: var(--text-secondary);
    font-size: 0.9rem;
  }

  .pagination-buttons {
    display: flex;
    gap: 0.5rem;

    button {
      padding: 0.5rem 1rem;
      border: 1px solid var(--border-color);
      background: var(--surface-card);
      border-radius: 4px;
      cursor: pointer;
      color: var(--text-primary);
      transition: all 0.2s;

      &:hover:not(:disabled) {
        background: var(--surface-hover);
        border-color: var(--primary-color);
      }

      &.active {
        background: var(--primary-color);
        color: white;
        border-color: var(--primary-color);
      }

      &:disabled {
        cursor: not-allowed;
        opacity: 0.5;
      }
    }
  }
}

// Modal Styles
.user-form {
  .form-row {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
    margin-bottom: 1rem;
  }

  .form-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;

    label {
      font-weight: 500;
      color: var(--text-primary);

      &.checkbox-label {
        flex-direction: row;
        align-items: center;

        input {
          margin-right: 0.5rem;
        }
      }
    }

    .form-select,
    textarea {
      padding: 0.75rem;
      border: 1px solid var(--border-color);
      border-radius: 4px;
      font-family: inherit;
      font-size: 0.95rem;
      background: var(--surface-card);
      color: var(--text-primary);

      &:focus {
        outline: none;
        border-color: var(--primary-color);
      }
    }

    textarea {
      resize: vertical;
    }

    .error-message {
      font-size: 0.85rem;
      color: #e74c3c;
    }
  }
}

.user-details {
  .details-header {
    display: flex;
    align-items: center;
    gap: 2rem;
    padding-bottom: 2rem;
    border-bottom: 1px solid var(--border-color);
    margin-bottom: 2rem;

    .user-avatar-large {
      width: 100px;
      height: 100px;
      border-radius: 50%;
      overflow: hidden;
      flex-shrink: 0;

      img {
        width: 100%;
        height: 100%;
        object-fit: cover;
      }

      .avatar-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--primary-color);
        color: white;
        font-weight: 600;
        font-size: 2rem;
      }
    }

    .user-info-large {
      h2 {
        margin: 0 0 0.5rem 0;
        color: var(--text-primary);
      }

      .user-email {
        margin: 0 0 1rem 0;
        color: var(--text-secondary);
      }

      .user-badges {
        display: flex;
        gap: 0.5rem;
      }
    }
  }

  .details-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 2rem;

    .detail-section {
      &.full-width {
        grid-column: 1 / -1;
      }

      h3 {
        margin: 0 0 1rem 0;
        font-size: 1.1rem;
        color: var(--text-primary);
      }

      .detail-item {
        display: flex;
        justify-content: space-between;
        padding: 0.75rem 0;
        border-bottom: 1px solid var(--border-color);

        &:last-child {
          border-bottom: none;
        }

        strong {
          color: var(--text-secondary);
          font-weight: 500;
        }

        span {
          color: var(--text-primary);
        }
      }
    }
  }

  .permissions-list {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;

    .permission-badge {
      padding: 0.5rem 1rem;
      background: var(--surface-ground);
      border: 1px solid var(--border-color);
      border-radius: 4px;
      font-size: 0.85rem;
      color: var(--text-primary);
    }
  }
}

.text-muted {
  color: var(--text-secondary);
}

@media (max-width: 768px) {
  .user-form .form-row {
    grid-template-columns: 1fr;
  }

  .user-details .details-grid {
    grid-template-columns: 1fr;
  }
}
</style>
