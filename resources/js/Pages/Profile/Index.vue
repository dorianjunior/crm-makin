<template>
  <MainLayout>
    <div class="profile-page">
      <Breadcrumbs :items="breadcrumbs" />

      <div class="page-header">
        <h1>Meu Perfil</h1>
        <p class="subtitle">Gerencie suas informações pessoais e preferências</p>
      </div>

      <div class="profile-layout">
        <!-- Sidebar -->
        <div class="profile-sidebar">
          <div class="profile-card">
            <div class="profile-avatar-section">
              <div class="avatar-wrapper">
                <img v-if="user.avatar" :src="user.avatar" :alt="user.name" class="profile-avatar">
                <div v-else class="avatar-placeholder">
                  {{ getInitials(user.name) }}
                </div>
                <button @click="showAvatarModal = true" class="avatar-edit-btn">
                  <i class="fa fa-camera"></i>
                </button>
              </div>
              <h2>{{ user.name }}</h2>
              <p class="user-email">{{ user.email }}</p>
              <span class="role-badge" :style="{ background: user.role?.color }">
                {{ user.role?.name }}
              </span>
            </div>

            <div class="profile-stats">
              <div class="stat-item">
                <strong>{{ user.leads_count || 0 }}</strong>
                <span>Leads</span>
              </div>
              <div class="stat-item">
                <strong>{{ user.activities_count || 0 }}</strong>
                <span>Atividades</span>
              </div>
              <div class="stat-item">
                <strong>{{ user.deals_count || 0 }}</strong>
                <span>Negócios</span>
              </div>
            </div>

            <div class="profile-info">
              <div class="info-item">
                <i class="fa fa-building"></i>
                <span>{{ user.company?.name || 'Sem empresa' }}</span>
              </div>
              <div class="info-item">
                <i class="fa fa-briefcase"></i>
                <span>{{ user.position || 'Sem cargo' }}</span>
              </div>
              <div class="info-item">
                <i class="fa fa-calendar"></i>
                <span>Membro desde {{ formatDate(user.created_at) }}</span>
              </div>
            </div>
          </div>

          <div class="profile-menu">
            <button
              v-for="tab in tabs"
              :key="tab.id"
              @click="activeTab = tab.id"
              :class="{ active: activeTab === tab.id }"
              class="menu-item"
            >
              <i :class="tab.icon"></i>
              {{ tab.label }}
            </button>
          </div>
        </div>

        <!-- Main Content -->
        <div class="profile-content">
          <!-- Personal Info Tab -->
          <div v-if="activeTab === 'personal'" class="card">
            <div class="card-header">
              <h3>Informações Pessoais</h3>
              <Button v-if="!editingPersonal" variant="link" @click="editingPersonal = true">
                <i class="fa fa-edit"></i>
                Editar
              </Button>
            </div>

            <form v-if="editingPersonal" @submit.prevent="savePersonalInfo">
              <div class="form-row">
                <div class="form-group">
                  <label>Nome Completo *</label>
                  <Input v-model="personalForm.name" />
                  <span v-if="errors.name" class="error-message">{{ errors.name }}</span>
                </div>

                <div class="form-group">
                  <label>Email *</label>
                  <Input v-model="personalForm.email" type="email" />
                  <span v-if="errors.email" class="error-message">{{ errors.email }}</span>
                </div>
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label>Telefone</label>
                  <Input v-model="personalForm.phone" placeholder="(00) 00000-0000" />
                </div>

                <div class="form-group">
                  <label>CPF</label>
                  <Input v-model="personalForm.cpf" placeholder="000.000.000-00" />
                </div>
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label>Cargo</label>
                  <Input v-model="personalForm.position" placeholder="Ex: Gerente de Vendas" />
                </div>

                <div class="form-group">
                  <label>Departamento</label>
                  <select v-model="personalForm.department" class="form-select">
                    <option value="">Selecione</option>
                    <option value="sales">Vendas</option>
                    <option value="marketing">Marketing</option>
                    <option value="support">Suporte</option>
                    <option value="development">Desenvolvimento</option>
                    <option value="management">Gestão</option>
                    <option value="finance">Financeiro</option>
                    <option value="hr">Recursos Humanos</option>
                  </select>
                </div>
              </div>

              <div class="form-group">
                <label>Bio</label>
                <textarea
                  v-model="personalForm.bio"
                  rows="4"
                  placeholder="Conte um pouco sobre você..."
                ></textarea>
              </div>

              <div class="form-actions">
                <Button variant="secondary" @click="cancelPersonalEdit">Cancelar</Button>
                <Button variant="primary" type="submit" :disabled="savingPersonal">
                  Salvar Alterações
                </Button>
              </div>
            </form>

            <div v-else class="info-display">
              <div class="info-row">
                <div class="info-col">
                  <label>Nome Completo</label>
                  <p>{{ user.name }}</p>
                </div>
                <div class="info-col">
                  <label>Email</label>
                  <p>{{ user.email }}</p>
                </div>
              </div>

              <div class="info-row">
                <div class="info-col">
                  <label>Telefone</label>
                  <p>{{ user.phone || '-' }}</p>
                </div>
                <div class="info-col">
                  <label>CPF</label>
                  <p>{{ user.cpf || '-' }}</p>
                </div>
              </div>

              <div class="info-row">
                <div class="info-col">
                  <label>Cargo</label>
                  <p>{{ user.position || '-' }}</p>
                </div>
                <div class="info-col">
                  <label>Departamento</label>
                  <p>{{ user.department || '-' }}</p>
                </div>
              </div>

              <div v-if="user.bio" class="info-row">
                <div class="info-col full-width">
                  <label>Bio</label>
                  <p>{{ user.bio }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Security Tab -->
          <div v-if="activeTab === 'security'" class="card">
            <div class="card-header">
              <h3>Segurança</h3>
            </div>

            <form @submit.prevent="changePassword" class="security-form">
              <div class="form-group">
                <label>Senha Atual *</label>
                <Input
                  v-model="securityForm.current_password"
                  type="password"
                  placeholder="Digite sua senha atual"
                />
                <span v-if="errors.current_password" class="error-message">{{ errors.current_password }}</span>
              </div>

              <div class="form-group">
                <label>Nova Senha *</label>
                <Input
                  v-model="securityForm.password"
                  type="password"
                  placeholder="Mínimo 8 caracteres"
                />
                <span v-if="errors.password" class="error-message">{{ errors.password }}</span>
              </div>

              <div class="form-group">
                <label>Confirmar Nova Senha *</label>
                <Input
                  v-model="securityForm.password_confirmation"
                  type="password"
                  placeholder="Repita a nova senha"
                />
              </div>

              <div class="form-actions">
                <Button variant="primary" type="submit" :disabled="savingSecurity">
                  Alterar Senha
                </Button>
              </div>
            </form>

            <div class="security-info">
              <h4>Sessões Ativas</h4>
              <div class="sessions-list">
                <div v-for="session in sessions" :key="session.id" class="session-item">
                  <div class="session-icon">
                    <i :class="getDeviceIcon(session.device)"></i>
                  </div>
                  <div class="session-info">
                    <strong>{{ session.device }}</strong>
                    <span>{{ session.ip }} • {{ formatRelativeTime(session.last_activity) }}</span>
                    <span v-if="session.is_current" class="current-badge">Sessão Atual</span>
                  </div>
                  <Button
                    v-if="!session.is_current"
                    variant="danger"
                    size="small"
                    @click="revokeSession(session)"
                  >
                    Revogar
                  </Button>
                </div>
              </div>
            </div>

            <div class="two-factor-section">
              <h4>Autenticação de Dois Fatores</h4>
              <p class="help-text">
                Adicione uma camada extra de segurança à sua conta
              </p>
              <Button
                :variant="user.two_factor_enabled ? 'danger' : 'primary'"
                @click="toggle2FA"
              >
                <i :class="user.two_factor_enabled ? 'fa fa-times' : 'fa fa-shield-check'"></i>
                {{ user.two_factor_enabled ? 'Desativar 2FA' : 'Ativar 2FA' }}
              </Button>
            </div>
          </div>

          <!-- Notifications Tab -->
          <div v-if="activeTab === 'notifications'" class="card">
            <div class="card-header">
              <h3>Preferências de Notificação</h3>
            </div>

            <form @submit.prevent="saveNotificationSettings">
              <div class="notification-section">
                <h4>Email</h4>
                <div class="notification-items">
                  <label v-for="item in emailNotifications" :key="item.id" class="notification-item">
                    <div>
                      <strong>{{ item.label }}</strong>
                      <span>{{ item.description }}</span>
                    </div>
                    <input
                      type="checkbox"
                      v-model="notificationForm.email[item.id]"
                      class="toggle-switch"
                    >
                  </label>
                </div>
              </div>

              <div class="notification-section">
                <h4>Sistema</h4>
                <div class="notification-items">
                  <label v-for="item in systemNotifications" :key="item.id" class="notification-item">
                    <div>
                      <strong>{{ item.label }}</strong>
                      <span>{{ item.description }}</span>
                    </div>
                    <input
                      type="checkbox"
                      v-model="notificationForm.system[item.id]"
                      class="toggle-switch"
                    >
                  </label>
                </div>
              </div>

              <div class="notification-section">
                <h4>Push</h4>
                <div class="notification-items">
                  <label class="notification-item">
                    <div>
                      <strong>Notificações Push</strong>
                      <span>Receba notificações no navegador</span>
                    </div>
                    <input
                      type="checkbox"
                      v-model="notificationForm.push.enabled"
                      class="toggle-switch"
                    >
                  </label>
                </div>
              </div>

              <div class="form-actions">
                <Button variant="primary" type="submit" :disabled="savingNotifications">
                  Salvar Preferências
                </Button>
              </div>
            </form>
          </div>

          <!-- Preferences Tab -->
          <div v-if="activeTab === 'preferences'" class="card">
            <div class="card-header">
              <h3>Preferências</h3>
            </div>

            <form @submit.prevent="savePreferences">
              <div class="form-group">
                <label>Idioma</label>
                <select v-model="preferencesForm.language" class="form-select">
                  <option value="pt-BR">Português (Brasil)</option>
                  <option value="en">English</option>
                  <option value="es">Español</option>
                </select>
              </div>

              <div class="form-group">
                <label>Fuso Horário</label>
                <select v-model="preferencesForm.timezone" class="form-select">
                  <option value="America/Sao_Paulo">Brasília (GMT-3)</option>
                  <option value="America/New_York">Nova York (GMT-5)</option>
                  <option value="Europe/London">Londres (GMT+0)</option>
                  <option value="Asia/Tokyo">Tóquio (GMT+9)</option>
                </select>
              </div>

              <div class="form-group">
                <label>Formato de Data</label>
                <select v-model="preferencesForm.date_format" class="form-select">
                  <option value="d/m/Y">DD/MM/AAAA</option>
                  <option value="m/d/Y">MM/DD/AAAA</option>
                  <option value="Y-m-d">AAAA-MM-DD</option>
                </select>
              </div>

              <div class="form-group">
                <label>Tema</label>
                <div class="theme-selector">
                  <label class="theme-option">
                    <input type="radio" v-model="preferencesForm.theme" value="light">
                    <div class="theme-preview light">
                      <i class="fa fa-sun"></i>
                      <span>Claro</span>
                    </div>
                  </label>
                  <label class="theme-option">
                    <input type="radio" v-model="preferencesForm.theme" value="dark">
                    <div class="theme-preview dark">
                      <i class="fa fa-moon"></i>
                      <span>Escuro</span>
                    </div>
                  </label>
                  <label class="theme-option">
                    <input type="radio" v-model="preferencesForm.theme" value="auto">
                    <div class="theme-preview auto">
                      <i class="fa fa-circle-half-stroke"></i>
                      <span>Auto</span>
                    </div>
                  </label>
                </div>
              </div>

              <div class="form-actions">
                <Button variant="primary" type="submit" :disabled="savingPreferences">
                  Salvar Preferências
                </Button>
              </div>
            </form>
          </div>

          <!-- API Tab -->
          <div v-if="activeTab === 'api'" class="card">
            <div class="card-header">
              <h3>Chaves de API</h3>
              <Button variant="primary" @click="generateAPIKey">
                <i class="fa fa-plus"></i>
                Gerar Nova Chave
              </Button>
            </div>

            <div class="api-keys-list">
              <div v-for="key in apiKeys" :key="key.id" class="api-key-item">
                <div class="api-key-info">
                  <strong>{{ key.name }}</strong>
                  <code>{{ key.key }}</code>
                  <span class="api-key-meta">
                    Criada em {{ formatDate(key.created_at) }} • Último uso: {{ key.last_used_at ? formatRelativeTime(key.last_used_at) : 'Nunca' }}
                  </span>
                </div>
                <div class="api-key-actions">
                  <Button variant="link" size="small" @click="copyAPIKey(key.key)">
                    <i class="fa fa-copy"></i>
                  </Button>
                  <Button variant="danger" size="small" @click="revokeAPIKey(key)">
                    <i class="fa fa-trash"></i>
                  </Button>
                </div>
              </div>

              <div v-if="!apiKeys.length" class="empty-state">
                <i class="fa fa-key"></i>
                <p>Nenhuma chave de API criada</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Avatar Modal -->
    <Modal v-if="showAvatarModal" @close="showAvatarModal = false" title="Alterar Foto de Perfil">
      <div class="avatar-upload">
        <div class="avatar-preview">
          <img v-if="avatarPreview" :src="avatarPreview" alt="Preview">
          <div v-else class="avatar-placeholder">
            {{ getInitials(user.name) }}
          </div>
        </div>
        <input
          ref="avatarInput"
          type="file"
          accept="image/*"
          @change="handleAvatarChange"
          style="display: none"
        >
        <Button variant="secondary" @click="$refs.avatarInput.click()">
          <i class="fa fa-upload"></i>
          Escolher Imagem
        </Button>
      </div>

      <template #footer>
        <Button variant="secondary" @click="showAvatarModal = false">Cancelar</Button>
        <Button variant="primary" @click="uploadAvatar" :disabled="!avatarPreview">
          Salvar Foto
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
import Modal from '@/Components/Modal.vue';

const props = defineProps({
  user: Object,
  sessions: Array,
  apiKeys: Array,
  errors: Object
});

const activeTab = ref('personal');
const editingPersonal = ref(false);
const showAvatarModal = ref(false);
const avatarPreview = ref(null);
const avatarInput = ref(null);

const savingPersonal = ref(false);
const savingSecurity = ref(false);
const savingNotifications = ref(false);
const savingPreferences = ref(false);

const personalForm = ref({
  name: props.user.name,
  email: props.user.email,
  phone: props.user.phone || '',
  cpf: props.user.cpf || '',
  position: props.user.position || '',
  department: props.user.department || '',
  bio: props.user.bio || ''
});

const securityForm = ref({
  current_password: '',
  password: '',
  password_confirmation: ''
});

const notificationForm = ref({
  email: {
    new_lead: true,
    lead_converted: true,
    task_assigned: true,
    task_due: true,
    new_message: true
  },
  system: {
    new_lead: true,
    activity_reminder: true,
    lead_updated: false
  },
  push: {
    enabled: false
  }
});

const preferencesForm = ref({
  language: props.user.preferences?.language || 'pt-BR',
  timezone: props.user.preferences?.timezone || 'America/Sao_Paulo',
  date_format: props.user.preferences?.date_format || 'd/m/Y',
  theme: props.user.preferences?.theme || 'light'
});

const breadcrumbs = [
  { label: 'Perfil' }
];

const tabs = [
  { id: 'personal', label: 'Informações Pessoais', icon: 'fa fa-user' },
  { id: 'security', label: 'Segurança', icon: 'fa fa-lock' },
  { id: 'notifications', label: 'Notificações', icon: 'fa fa-bell' },
  { id: 'preferences', label: 'Preferências', icon: 'fa fa-cog' },
  { id: 'api', label: 'Chaves de API', icon: 'fa fa-key' }
];

const emailNotifications = [
  { id: 'new_lead', label: 'Novo Lead', description: 'Quando um novo lead é atribuído a você' },
  { id: 'lead_converted', label: 'Lead Convertido', description: 'Quando um lead é convertido em negócio' },
  { id: 'task_assigned', label: 'Tarefa Atribuída', description: 'Quando uma tarefa é atribuída a você' },
  { id: 'task_due', label: 'Tarefa Vencendo', description: 'Lembrete de tarefas próximas do vencimento' },
  { id: 'new_message', label: 'Nova Mensagem', description: 'Quando você recebe uma nova mensagem' }
];

const systemNotifications = [
  { id: 'new_lead', label: 'Novo Lead', description: 'Notificação no sistema para novos leads' },
  { id: 'activity_reminder', label: 'Lembrete de Atividade', description: 'Lembretes de atividades agendadas' },
  { id: 'lead_updated', label: 'Lead Atualizado', description: 'Quando informações de um lead são atualizadas' }
];

const getInitials = (name) => {
  return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('pt-BR');
};

const formatRelativeTime = (datetime) => {
  const seconds = Math.floor((new Date() - new Date(datetime)) / 1000);
  if (seconds < 60) return 'Agora';
  if (seconds < 3600) return `${Math.floor(seconds / 60)}m atrás`;
  if (seconds < 86400) return `${Math.floor(seconds / 3600)}h atrás`;
  return `${Math.floor(seconds / 86400)}d atrás`;
};

const getDeviceIcon = (device) => {
  if (device.includes('Mobile')) return 'fa fa-mobile';
  if (device.includes('Tablet')) return 'fa fa-tablet';
  return 'fa fa-desktop';
};

const cancelPersonalEdit = () => {
  editingPersonal.value = false;
  personalForm.value = {
    name: props.user.name,
    email: props.user.email,
    phone: props.user.phone || '',
    cpf: props.user.cpf || '',
    position: props.user.position || '',
    department: props.user.department || '',
    bio: props.user.bio || ''
  };
};

const savePersonalInfo = () => {
  savingPersonal.value = true;
  router.put('/profile/personal', personalForm.value, {
    preserveScroll: true,
    onSuccess: () => {
      editingPersonal.value = false;
    },
    onFinish: () => {
      savingPersonal.value = false;
    }
  });
};

const changePassword = () => {
  savingSecurity.value = true;
  router.put('/profile/password', securityForm.value, {
    preserveScroll: true,
    onSuccess: () => {
      securityForm.value = {
        current_password: '',
        password: '',
        password_confirmation: ''
      };
    },
    onFinish: () => {
      savingSecurity.value = false;
    }
  });
};

const saveNotificationSettings = () => {
  savingNotifications.value = true;
  router.put('/profile/notifications', notificationForm.value, {
    preserveScroll: true,
    onFinish: () => {
      savingNotifications.value = false;
    }
  });
};

const savePreferences = () => {
  savingPreferences.value = true;
  router.put('/profile/preferences', preferencesForm.value, {
    preserveScroll: true,
    onFinish: () => {
      savingPreferences.value = false;
    }
  });
};

const handleAvatarChange = (e) => {
  const file = e.target.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = (event) => {
      avatarPreview.value = event.target.result;
    };
    reader.readAsDataURL(file);
  }
};

const uploadAvatar = () => {
  router.post('/profile/avatar', { avatar: avatarPreview.value }, {
    preserveScroll: true,
    onSuccess: () => {
      showAvatarModal.value = false;
      avatarPreview.value = null;
    }
  });
};

const revokeSession = (session) => {
  if (confirm('Tem certeza que deseja revogar esta sessão?')) {
    router.delete(`/profile/sessions/${session.id}`, {
      preserveScroll: true
    });
  }
};

const toggle2FA = () => {
  if (props.user.two_factor_enabled) {
    if (confirm('Desativar autenticação de dois fatores?')) {
      router.delete('/profile/two-factor', { preserveScroll: true });
    }
  } else {
    router.post('/profile/two-factor', {}, { preserveScroll: true });
  }
};

const generateAPIKey = () => {
  const name = prompt('Digite um nome para a chave de API:');
  if (name) {
    router.post('/profile/api-keys', { name }, { preserveScroll: true });
  }
};

const copyAPIKey = (key) => {
  navigator.clipboard.writeText(key);
  alert('Chave copiada para a área de transferência!');
};

const revokeAPIKey = (key) => {
  if (confirm(`Revogar a chave "${key.name}"?`)) {
    router.delete(`/profile/api-keys/${key.id}`, { preserveScroll: true });
  }
};
</script>

<style scoped lang="scss">
.profile-page {
  padding: 2rem;
}

.page-header {
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

.profile-layout {
  display: grid;
  grid-template-columns: 320px 1fr;
  gap: 2rem;
}

// Sidebar
.profile-sidebar {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.profile-card {
  background: var(--surface-card);
  border: 1px solid var(--border-color);
  border-radius: 8px;
  padding: 2rem;

  .profile-avatar-section {
    text-align: center;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid var(--border-color);
    margin-bottom: 1.5rem;

    .avatar-wrapper {
      position: relative;
      width: 120px;
      height: 120px;
      margin: 0 auto 1rem;

      .profile-avatar,
      .avatar-placeholder {
        width: 100%;
        height: 100%;
        border-radius: 50%;
      }

      .profile-avatar {
        object-fit: cover;
      }

      .avatar-placeholder {
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--primary-color);
        color: white;
        font-size: 2.5rem;
        font-weight: 600;
      }

      .avatar-edit-btn {
        position: absolute;
        bottom: 0;
        right: 0;
        width: 40px;
        height: 40px;
        border: 3px solid var(--surface-card);
        background: var(--primary-color);
        color: white;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;

        &:hover {
          background: var(--primary-color-dark);
        }
      }
    }

    h2 {
      margin: 0 0 0.5rem 0;
      color: var(--text-primary);
    }

    .user-email {
      margin: 0 0 1rem 0;
      color: var(--text-secondary);
    }

    .role-badge {
      display: inline-block;
      padding: 0.5rem 1rem;
      border-radius: 20px;
      font-size: 0.85rem;
      color: white;
      font-weight: 500;
    }
  }

  .profile-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid var(--border-color);
    margin-bottom: 1.5rem;

    .stat-item {
      text-align: center;

      strong {
        display: block;
        font-size: 1.5rem;
        color: var(--primary-color);
        margin-bottom: 0.25rem;
      }

      span {
        display: block;
        font-size: 0.85rem;
        color: var(--text-secondary);
      }
    }
  }

  .profile-info {
    display: flex;
    flex-direction: column;
    gap: 1rem;

    .info-item {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      font-size: 0.9rem;
      color: var(--text-primary);

      i {
        width: 20px;
        color: var(--text-secondary);
      }
    }
  }
}

.profile-menu {
  background: var(--surface-card);
  border: 1px solid var(--border-color);
  border-radius: 8px;
  overflow: hidden;

  .menu-item {
    width: 100%;
    padding: 1rem 1.5rem;
    border: none;
    background: transparent;
    text-align: left;
    cursor: pointer;
    color: var(--text-primary);
    border-bottom: 1px solid var(--border-color);
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 1rem;

    &:last-child {
      border-bottom: none;
    }

    &:hover {
      background: var(--surface-hover);
    }

    &.active {
      background: var(--primary-color);
      color: white;

      i {
        color: white;
      }
    }

    i {
      width: 20px;
      color: var(--primary-color);
    }
  }
}

// Main Content
.profile-content {
  .card {
    background: var(--surface-card);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 2rem;

    .card-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 2rem;
      padding-bottom: 1rem;
      border-bottom: 1px solid var(--border-color);

      h3 {
        margin: 0;
        color: var(--text-primary);
      }
    }
  }
}

// Forms
.form-row {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1.5rem;
  margin-bottom: 1.5rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  margin-bottom: 1.5rem;

  label {
    font-weight: 500;
    color: var(--text-primary);
  }

  textarea,
  .form-select {
    padding: 0.75rem;
    border: 1px solid var(--border-color);
    border-radius: 4px;
    font-family: inherit;
    font-size: 0.95rem;
    background: var(--surface-card);
    color: var(--text-primary);
    resize: vertical;

    &:focus {
      outline: none;
      border-color: var(--primary-color);
    }
  }

  .error-message {
    font-size: 0.85rem;
    color: #e74c3c;
  }
}

.form-actions {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
  margin-top: 2rem;
  padding-top: 2rem;
  border-top: 1px solid var(--border-color);
}

// Info Display
.info-display {
  .info-row {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 2rem;
    margin-bottom: 2rem;

    .info-col {
      &.full-width {
        grid-column: 1 / -1;
      }

      label {
        display: block;
        margin-bottom: 0.5rem;
        font-size: 0.85rem;
        font-weight: 500;
        color: var(--text-secondary);
        text-transform: uppercase;
      }

      p {
        margin: 0;
        color: var(--text-primary);
      }
    }
  }
}

// Theme Selector
.theme-selector {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1rem;

  .theme-option {
    input {
      display: none;

      &:checked + .theme-preview {
        border-color: var(--primary-color);
        background: var(--surface-hover);
      }
    }

    .theme-preview {
      padding: 2rem 1rem;
      border: 2px solid var(--border-color);
      border-radius: 8px;
      text-align: center;
      cursor: pointer;
      transition: all 0.2s;

      &:hover {
        border-color: var(--primary-color);
      }

      i {
        font-size: 2rem;
        margin-bottom: 0.5rem;
        display: block;
      }

      span {
        font-size: 0.9rem;
        color: var(--text-primary);
      }
    }
  }
}

// Notification Items
.notification-section {
  margin-bottom: 2rem;

  h4 {
    margin: 0 0 1rem 0;
    color: var(--text-primary);
  }
}

.notification-items {
  display: flex;
  flex-direction: column;
  gap: 1rem;

  .notification-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    border: 1px solid var(--border-color);
    border-radius: 4px;
    cursor: pointer;
    transition: background 0.2s;

    &:hover {
      background: var(--surface-hover);
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
      }
    }

    .toggle-switch {
      cursor: pointer;
    }
  }
}

// Sessions & API Keys
.sessions-list,
.api-keys-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.session-item,
.api-key-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  border: 1px solid var(--border-color);
  border-radius: 4px;

  .session-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--surface-ground);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--primary-color);
    flex-shrink: 0;
  }

  .session-info,
  .api-key-info {
    flex: 1;

    strong {
      display: block;
      margin-bottom: 0.25rem;
      color: var(--text-primary);
    }

    span,
    code {
      display: block;
      font-size: 0.85rem;
      color: var(--text-secondary);
      margin-top: 0.25rem;
    }

    code {
      background: var(--surface-ground);
      padding: 0.25rem 0.5rem;
      border-radius: 4px;
      font-family: monospace;
      margin-top: 0.5rem;
    }

    .current-badge {
      display: inline-block;
      padding: 0.25rem 0.5rem;
      background: var(--primary-color);
      color: white;
      border-radius: 12px;
      font-size: 0.75rem;
      margin-top: 0.5rem;
    }
  }

  .api-key-actions {
    display: flex;
    gap: 0.5rem;
  }
}

.security-info,
.two-factor-section {
  margin-top: 2rem;
  padding-top: 2rem;
  border-top: 1px solid var(--border-color);

  h4 {
    margin: 0 0 1rem 0;
    color: var(--text-primary);
  }

  .help-text {
    margin: 0 0 1rem 0;
    color: var(--text-secondary);
  }
}

// Avatar Modal
.avatar-upload {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1.5rem;

  .avatar-preview {
    width: 200px;
    height: 200px;
    border-radius: 50%;
    overflow: hidden;
    border: 3px solid var(--border-color);

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
      font-size: 4rem;
      font-weight: 600;
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

@media (max-width: 1200px) {
  .profile-layout {
    grid-template-columns: 1fr;
  }

  .profile-sidebar {
    order: -1;
  }

  .form-row,
  .info-display .info-row {
    grid-template-columns: 1fr;
  }
}
</style>
