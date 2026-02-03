<template>
  <MainLayout>
    <div class="roles-page">
      <Breadcrumbs :items="breadcrumbs" />

      <div class="page-header">
        <div>
          <h1>Funções e Permissões</h1>
          <p class="subtitle">Gerencie as funções e permissões do sistema</p>
        </div>
        <Button variant="primary" @click="showRoleModal = true">
          <i class="fa fa-plus"></i>
          Nova Função
        </Button>
      </div>

      <!-- Stats -->
      <div class="stats-grid">
        <StatCard
          title="Total de Funções"
          :value="stats.total_roles"
          icon="shield-alt"
          color="blue"
        />
        <StatCard
          title="Permissões Disponíveis"
          :value="stats.total_permissions"
          icon="lock"
          color="green"
        />
        <StatCard
          title="Usuários com Acesso"
          :value="stats.total_users"
          icon="users"
          color="purple"
        />
        <StatCard
          title="Funções Personalizadas"
          :value="stats.custom_roles"
          icon="cog"
          color="orange"
        />
      </div>

      <!-- Roles Grid -->
      <div class="roles-grid">
        <div v-for="role in roles" :key="role.id" class="role-card">
          <div class="role-header">
            <div class="role-icon" :style="{ background: role.color }">
              <i :class="role.icon || 'fa fa-shield-alt'"></i>
            </div>
            <div class="role-info">
              <h3>{{ role.name }}</h3>
              <p>{{ role.description }}</p>
            </div>
            <div class="role-actions">
              <button @click="editRole(role)" class="action-btn" title="Editar">
                <i class="fa fa-edit"></i>
              </button>
              <button
                v-if="!role.is_system"
                @click="deleteRole(role)"
                class="action-btn danger"
                title="Excluir"
              >
                <i class="fa fa-trash"></i>
              </button>
            </div>
          </div>

          <div class="role-stats">
            <div class="stat-item">
              <i class="fa fa-users"></i>
              <span>{{ role.users_count }} usuários</span>
            </div>
            <div class="stat-item">
              <i class="fa fa-lock"></i>
              <span>{{ role.permissions_count }} permissões</span>
            </div>
          </div>

          <div class="role-footer">
            <Button variant="link" size="small" @click="viewPermissions(role)">
              <i class="fa fa-eye"></i>
              Ver Permissões
            </Button>
            <span v-if="role.is_system" class="system-badge">
              <i class="fa fa-shield-check"></i>
              Sistema
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Role Modal (Create/Edit) -->
    <Modal
      v-if="showRoleModal"
      @close="closeRoleModal"
      :title="editingRole ? 'Editar Função' : 'Nova Função'"
      size="large"
    >
      <div class="role-form">
        <div class="form-row">
          <div class="form-group">
            <label>Nome da Função *</label>
            <Input v-model="roleForm.name" placeholder="Ex: Gerente de Vendas" />
            <span v-if="modalErrors.name" class="error-message">{{ modalErrors.name }}</span>
          </div>

          <div class="form-group">
            <label>Slug *</label>
            <Input
              v-model="roleForm.slug"
              placeholder="gerente-vendas"
              :disabled="editingRole?.is_system"
            />
            <span class="help-text">Identificador único (não pode ser alterado)</span>
            <span v-if="modalErrors.slug" class="error-message">{{ modalErrors.slug }}</span>
          </div>
        </div>

        <div class="form-group">
          <label>Descrição</label>
          <textarea
            v-model="roleForm.description"
            rows="3"
            placeholder="Descreva as responsabilidades desta função"
          ></textarea>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Cor</label>
            <div class="color-picker">
              <input type="color" v-model="roleForm.color" />
              <Input v-model="roleForm.color" placeholder="#3498db" />
            </div>
          </div>

          <div class="form-group">
            <label>Ícone (FontAwesome)</label>
            <Input v-model="roleForm.icon" placeholder="fa fa-user-tie" />
            <span class="help-text">Exemplo: fa fa-user-tie, fa fa-crown</span>
          </div>
        </div>

        <div class="form-group">
          <label>Permissões *</label>
          <div class="permissions-section">
            <div class="permissions-filter">
              <Input
                v-model="permissionSearch"
                placeholder="Buscar permissão..."
              />
            </div>

            <div class="permissions-groups">
              <div
                v-for="(groupPermissions, group) in groupedPermissions"
                :key="group"
                class="permission-group"
              >
                <div class="group-header">
                  <label class="group-checkbox">
                    <input
                      type="checkbox"
                      @change="toggleGroup(group, $event)"
                      :checked="isGroupSelected(group)"
                      :indeterminate.prop="isGroupIndeterminate(group)"
                    >
                    <strong>{{ group }}</strong>
                  </label>
                  <span class="group-count">
                    {{ getSelectedInGroup(group) }}/{{ groupPermissions.length }}
                  </span>
                </div>

                <div class="permissions-list">
                  <label
                    v-for="permission in groupPermissions"
                    :key="permission.id"
                    class="permission-item"
                  >
                    <input
                      type="checkbox"
                      :value="permission.id"
                      v-model="roleForm.permissions"
                    >
                    <div class="permission-info">
                      <span class="permission-name">{{ permission.name }}</span>
                      <span class="permission-description">{{ permission.description }}</span>
                    </div>
                  </label>
                </div>
              </div>
            </div>
          </div>
          <span v-if="modalErrors.permissions" class="error-message">{{ modalErrors.permissions }}</span>
        </div>

        <div class="form-group">
          <label>
            <input
              type="checkbox"
              v-model="roleForm.is_active"
              :disabled="editingRole?.is_system"
            >
            Função ativa
          </label>
        </div>
      </div>

      <template #footer>
        <Button variant="secondary" @click="closeRoleModal">Cancelar</Button>
        <Button variant="primary" @click="saveRole" :disabled="savingRole">
          {{ editingRole ? 'Atualizar' : 'Criar Função' }}
        </Button>
      </template>
    </Modal>

    <!-- Permissions View Modal -->
    <Modal
      v-if="showPermissionsModal"
      @close="showPermissionsModal = false"
      :title="`Permissões: ${selectedRole?.name}`"
      size="large"
    >
      <div class="permissions-view">
        <div class="role-info-header">
          <div class="role-icon-large" :style="{ background: selectedRole.color }">
            <i :class="selectedRole.icon || 'fa fa-shield-alt'"></i>
          </div>
          <div>
            <h2>{{ selectedRole.name }}</h2>
            <p>{{ selectedRole.description }}</p>
            <div class="role-stats">
              <span><i class="fa fa-users"></i> {{ selectedRole.users_count }} usuários</span>
              <span><i class="fa fa-lock"></i> {{ selectedRole.permissions_count }} permissões</span>
            </div>
          </div>
        </div>

        <div class="permissions-display">
          <div
            v-for="(groupPermissions, group) in selectedRolePermissions"
            :key="group"
            class="permission-group-display"
          >
            <h3>{{ group }}</h3>
            <div class="permissions-grid">
              <div
                v-for="permission in groupPermissions"
                :key="permission.id"
                class="permission-badge"
              >
                <i class="fa fa-check-circle"></i>
                <div>
                  <strong>{{ permission.name }}</strong>
                  <span>{{ permission.description }}</span>
                </div>
              </div>
            </div>
          </div>

          <div v-if="!selectedRole.permissions?.length" class="empty-state">
            <i class="fa fa-lock-open"></i>
            <p>Nenhuma permissão atribuída a esta função</p>
          </div>
        </div>
      </div>

      <template #footer>
        <Button variant="secondary" @click="showPermissionsModal = false">Fechar</Button>
        <Button variant="primary" @click="editRole(selectedRole)">
          <i class="fa fa-edit"></i>
          Editar Função
        </Button>
      </template>
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
  roles: Array,
  permissions: Array,
  stats: Object,
  errors: Object
});

const showRoleModal = ref(false);
const showPermissionsModal = ref(false);
const editingRole = ref(null);
const selectedRole = ref(null);
const savingRole = ref(false);
const modalErrors = ref({});
const permissionSearch = ref('');

const roleForm = ref({
  name: '',
  slug: '',
  description: '',
  color: '#3498db',
  icon: 'fa fa-shield-alt',
  permissions: [],
  is_active: true
});

const breadcrumbs = [
  { label: 'Admin', url: '/admin' },
  { label: 'Funções' }
];

const groupedPermissions = computed(() => {
  const filtered = props.permissions.filter(p =>
    !permissionSearch.value ||
    p.name.toLowerCase().includes(permissionSearch.value.toLowerCase()) ||
    p.description.toLowerCase().includes(permissionSearch.value.toLowerCase())
  );

  return filtered.reduce((groups, permission) => {
    const group = permission.group || 'Outros';
    if (!groups[group]) {
      groups[group] = [];
    }
    groups[group].push(permission);
    return groups;
  }, {});
});

const selectedRolePermissions = computed(() => {
  if (!selectedRole.value?.permissions) return {};

  return selectedRole.value.permissions.reduce((groups, permission) => {
    const group = permission.group || 'Outros';
    if (!groups[group]) {
      groups[group] = [];
    }
    groups[group].push(permission);
    return groups;
  }, {});
});

const isGroupSelected = (group) => {
  const groupPerms = groupedPermissions.value[group];
  return groupPerms.every(p => roleForm.value.permissions.includes(p.id));
};

const isGroupIndeterminate = (group) => {
  const groupPerms = groupedPermissions.value[group];
  const selected = groupPerms.filter(p => roleForm.value.permissions.includes(p.id));
  return selected.length > 0 && selected.length < groupPerms.length;
};

const getSelectedInGroup = (group) => {
  const groupPerms = groupedPermissions.value[group];
  return groupPerms.filter(p => roleForm.value.permissions.includes(p.id)).length;
};

const toggleGroup = (group, event) => {
  const groupPerms = groupedPermissions.value[group];
  const permissionIds = groupPerms.map(p => p.id);

  if (event.target.checked) {
    // Add all group permissions
    permissionIds.forEach(id => {
      if (!roleForm.value.permissions.includes(id)) {
        roleForm.value.permissions.push(id);
      }
    });
  } else {
    // Remove all group permissions
    roleForm.value.permissions = roleForm.value.permissions.filter(
      id => !permissionIds.includes(id)
    );
  }
};

const editRole = (role) => {
  showPermissionsModal.value = false;
  editingRole.value = role;
  roleForm.value = {
    name: role.name,
    slug: role.slug,
    description: role.description || '',
    color: role.color || '#3498db',
    icon: role.icon || 'fa fa-shield-alt',
    permissions: role.permissions?.map(p => p.id) || [],
    is_active: role.is_active ?? true
  };
  showRoleModal.value = true;
};

const viewPermissions = (role) => {
  selectedRole.value = role;
  showPermissionsModal.value = true;
};

const closeRoleModal = () => {
  showRoleModal.value = false;
  editingRole.value = null;
  modalErrors.value = {};
  permissionSearch.value = '';
  roleForm.value = {
    name: '',
    slug: '',
    description: '',
    color: '#3498db',
    icon: 'fa fa-shield-alt',
    permissions: [],
    is_active: true
  };
};

const saveRole = () => {
  savingRole.value = true;
  const url = editingRole.value ? `/admin/roles/${editingRole.value.id}` : '/admin/roles';
  const method = editingRole.value ? 'put' : 'post';

  router[method](url, roleForm.value, {
    preserveState: true,
    preserveScroll: true,
    onSuccess: () => {
      closeRoleModal();
    },
    onError: (errors) => {
      modalErrors.value = errors;
    },
    onFinish: () => {
      savingRole.value = false;
    }
  });
};

const deleteRole = (role) => {
  if (confirm(`Tem certeza que deseja excluir a função "${role.name}"?\n\nOs usuários com esta função perderão suas permissões.`)) {
    router.delete(`/admin/roles/${role.id}`, {
      preserveState: true,
      preserveScroll: true
    });
  }
};
</script>

<style scoped lang="scss">
.roles-page {
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

// Role Cards
.roles-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 1.5rem;
}

.role-card {
  background: var(--surface-card);
  border: 1px solid var(--border-color);
  border-radius: 8px;
  padding: 1.5rem;
  transition: all 0.2s;

  &:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
  }

  .role-header {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    margin-bottom: 1rem;

    .role-icon {
      width: 50px;
      height: 50px;
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-size: 1.5rem;
      flex-shrink: 0;
    }

    .role-info {
      flex: 1;

      h3 {
        margin: 0 0 0.5rem 0;
        color: var(--text-primary);
      }

      p {
        margin: 0;
        font-size: 0.9rem;
        color: var(--text-secondary);
        line-height: 1.5;
      }
    }

    .role-actions {
      display: flex;
      gap: 0.5rem;

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
  }

  .role-stats {
    display: flex;
    gap: 2rem;
    padding: 1rem 0;
    border-top: 1px solid var(--border-color);
    border-bottom: 1px solid var(--border-color);
    margin-bottom: 1rem;

    .stat-item {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      font-size: 0.9rem;
      color: var(--text-secondary);

      i {
        color: var(--primary-color);
      }
    }
  }

  .role-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;

    .system-badge {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.25rem 0.75rem;
      background: var(--surface-ground);
      border: 1px solid var(--border-color);
      border-radius: 12px;
      font-size: 0.85rem;
      color: var(--text-secondary);

      i {
        color: var(--primary-color);
      }
    }
  }
}

// Form Styles
.role-form {
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
    margin-bottom: 1rem;

    label {
      font-weight: 500;
      color: var(--text-primary);

      input[type="checkbox"] {
        margin-right: 0.5rem;
      }
    }

    textarea {
      padding: 0.75rem;
      border: 1px solid var(--border-color);
      border-radius: 4px;
      font-family: inherit;
      font-size: 0.95rem;
      resize: vertical;
      background: var(--surface-card);
      color: var(--text-primary);

      &:focus {
        outline: none;
        border-color: var(--primary-color);
      }
    }

    .help-text {
      font-size: 0.85rem;
      color: var(--text-secondary);
    }

    .error-message {
      font-size: 0.85rem;
      color: #e74c3c;
    }
  }

  .color-picker {
    display: flex;
    gap: 1rem;
    align-items: center;

    input[type="color"] {
      width: 60px;
      height: 45px;
      border: 1px solid var(--border-color);
      border-radius: 4px;
      cursor: pointer;
    }
  }
}

// Permissions Section
.permissions-section {
  border: 1px solid var(--border-color);
  border-radius: 4px;
  overflow: hidden;

  .permissions-filter {
    padding: 1rem;
    border-bottom: 1px solid var(--border-color);
    background: var(--surface-ground);
  }

  .permissions-groups {
    max-height: 500px;
    overflow-y: auto;
  }

  .permission-group {
    border-bottom: 1px solid var(--border-color);

    &:last-child {
      border-bottom: none;
    }

    .group-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 1rem;
      background: var(--surface-ground);
      cursor: pointer;

      .group-checkbox {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        cursor: pointer;

        input {
          cursor: pointer;
        }

        strong {
          color: var(--text-primary);
          font-size: 1rem;
        }
      }

      .group-count {
        font-size: 0.9rem;
        color: var(--text-secondary);
        font-weight: 500;
      }
    }

    .permissions-list {
      display: flex;
      flex-direction: column;

      .permission-item {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        padding: 1rem 1rem 1rem 2rem;
        cursor: pointer;
        transition: background 0.2s;

        &:hover {
          background: var(--surface-hover);
        }

        input {
          margin-top: 0.25rem;
          cursor: pointer;
        }

        .permission-info {
          flex: 1;
          display: flex;
          flex-direction: column;
          gap: 0.25rem;

          .permission-name {
            font-weight: 500;
            color: var(--text-primary);
          }

          .permission-description {
            font-size: 0.85rem;
            color: var(--text-secondary);
            line-height: 1.4;
          }
        }
      }
    }
  }
}

// Permissions View
.permissions-view {
  .role-info-header {
    display: flex;
    align-items: center;
    gap: 2rem;
    padding-bottom: 2rem;
    border-bottom: 1px solid var(--border-color);
    margin-bottom: 2rem;

    .role-icon-large {
      width: 80px;
      height: 80px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-size: 2.5rem;
      flex-shrink: 0;
    }

    h2 {
      margin: 0 0 0.5rem 0;
      color: var(--text-primary);
    }

    p {
      margin: 0 0 1rem 0;
      color: var(--text-secondary);
      line-height: 1.5;
    }

    .role-stats {
      display: flex;
      gap: 2rem;

      span {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.9rem;
        color: var(--text-secondary);

        i {
          color: var(--primary-color);
        }
      }
    }
  }

  .permissions-display {
    .permission-group-display {
      margin-bottom: 2rem;

      &:last-child {
        margin-bottom: 0;
      }

      h3 {
        margin: 0 0 1rem 0;
        font-size: 1.1rem;
        color: var(--text-primary);
      }

      .permissions-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1rem;

        .permission-badge {
          display: flex;
          align-items: flex-start;
          gap: 1rem;
          padding: 1rem;
          background: var(--surface-ground);
          border: 1px solid var(--border-color);
          border-radius: 6px;

          i {
            color: #27ae60;
            margin-top: 0.25rem;
          }

          div {
            flex: 1;

            strong {
              display: block;
              margin-bottom: 0.25rem;
              color: var(--text-primary);
            }

            span {
              display: block;
              font-size: 0.85rem;
              color: var(--text-secondary);
              line-height: 1.4;
            }
          }
        }
      }
    }
  }

  .empty-state {
    text-align: center;
    padding: 3rem;
    color: var(--text-secondary);

    i {
      font-size: 3rem;
      opacity: 0.5;
      margin-bottom: 1rem;
    }

    p {
      margin: 0;
    }
  }
}

@media (max-width: 768px) {
  .roles-grid {
    grid-template-columns: 1fr;
  }

  .role-form .form-row {
    grid-template-columns: 1fr;
  }

  .permissions-view .permissions-display .permissions-grid {
    grid-template-columns: 1fr;
  }
}
</style>
