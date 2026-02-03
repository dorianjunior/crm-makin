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

