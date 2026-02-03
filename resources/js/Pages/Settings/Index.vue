<template>
  <MainLayout>
    <div class="settings-page">
      <Breadcrumbs :items="breadcrumbs" />

      <div class="page-header">
        <div>
          <h1>Configurações do Sistema</h1>
          <p class="subtitle">Gerencie as configurações gerais da plataforma</p>
        </div>
      </div>

      <div class="settings-layout">
        <!-- Sidebar Menu -->
        <div class="settings-sidebar">
          <div class="settings-menu">
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

        <!-- Content -->
        <div class="settings-content">
          <!-- General Settings -->
          <div v-if="activeTab === 'general'" class="settings-section">
            <div class="section-header">
              <h2>Configurações Gerais</h2>
              <p>Informações básicas da empresa e sistema</p>
            </div>

            <form @submit.prevent="saveGeneralSettings" class="settings-form">
              <div class="form-group">
                <label>Nome da Empresa *</label>
                <Input v-model="generalForm.company_name" />
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label>Email Principal *</label>
                  <Input v-model="generalForm.company_email" type="email" />
                </div>

                <div class="form-group">
                  <label>Telefone</label>
                  <Input v-model="generalForm.company_phone" placeholder="(00) 0000-0000" />
                </div>
              </div>

              <div class="form-group">
                <label>Endereço</label>
                <Input v-model="generalForm.address" />
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label>Cidade</label>
                  <Input v-model="generalForm.city" />
                </div>

                <div class="form-group">
                  <label>Estado</label>
                  <select v-model="generalForm.state" class="form-select">
                    <option value="">Selecione</option>
                    <option v-for="state in brazilianStates" :key="state.value" :value="state.value">
                      {{ state.label }}
                    </option>
                  </select>
                </div>

                <div class="form-group">
                  <label>CEP</label>
                  <Input v-model="generalForm.zip_code" placeholder="00000-000" />
                </div>
              </div>

              <div class="form-group">
                <label>CNPJ</label>
                <Input v-model="generalForm.cnpj" placeholder="00.000.000/0000-00" />
              </div>

              <div class="form-group">
                <label>Descrição da Empresa</label>
                <textarea v-model="generalForm.description" rows="4"></textarea>
              </div>

              <div class="form-group">
                <label>Fuso Horário</label>
                <select v-model="generalForm.timezone" class="form-select">
                  <option value="America/Sao_Paulo">Brasília (GMT-3)</option>
                  <option value="America/Manaus">Manaus (GMT-4)</option>
                  <option value="America/Rio_Branco">Rio Branco (GMT-5)</option>
                </select>
              </div>

              <div class="form-group">
                <label>Idioma Padrão</label>
                <select v-model="generalForm.default_language" class="form-select">
                  <option value="pt-BR">Português (Brasil)</option>
                  <option value="en">English</option>
                  <option value="es">Español</option>
                </select>
              </div>

              <div class="form-actions">
                <Button variant="primary" type="submit" :disabled="saving">
                  <i class="fa fa-save"></i>
                  Salvar Alterações
                </Button>
              </div>
            </form>
          </div>

          <!-- Email Settings -->
          <div v-if="activeTab === 'email'" class="settings-section">
            <div class="section-header">
              <h2>Configurações de Email</h2>
              <p>Configure o envio de emails do sistema</p>
            </div>

            <form @submit.prevent="saveEmailSettings" class="settings-form">
              <div class="form-group">
                <label>Driver de Email</label>
                <select v-model="emailForm.mail_driver" class="form-select">
                  <option value="smtp">SMTP</option>
                  <option value="sendmail">Sendmail</option>
                  <option value="mailgun">Mailgun</option>
                  <option value="ses">Amazon SES</option>
                </select>
              </div>

              <div v-if="emailForm.mail_driver === 'smtp'">
                <div class="form-row">
                  <div class="form-group">
                    <label>Host SMTP *</label>
                    <Input v-model="emailForm.mail_host" placeholder="smtp.gmail.com" />
                  </div>

                  <div class="form-group">
                    <label>Porta *</label>
                    <Input v-model="emailForm.mail_port" type="number" placeholder="587" />
                  </div>
                </div>

                <div class="form-row">
                  <div class="form-group">
                    <label>Usuário *</label>
                    <Input v-model="emailForm.mail_username" />
                  </div>

                  <div class="form-group">
                    <label>Senha *</label>
                    <Input v-model="emailForm.mail_password" type="password" />
                  </div>
                </div>

                <div class="form-group">
                  <label>Criptografia</label>
                  <select v-model="emailForm.mail_encryption" class="form-select">
                    <option value="tls">TLS</option>
                    <option value="ssl">SSL</option>
                    <option value="">Nenhuma</option>
                  </select>
                </div>
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label>Email de Envio *</label>
                  <Input v-model="emailForm.mail_from_address" type="email" />
                </div>

                <div class="form-group">
                  <label>Nome do Remetente *</label>
                  <Input v-model="emailForm.mail_from_name" />
                </div>
              </div>

              <div class="form-actions">
                <Button variant="secondary" @click="testEmailConnection">
                  <i class="fa fa-flask"></i>
                  Testar Conexão
                </Button>
                <Button variant="primary" type="submit" :disabled="saving">
                  <i class="fa fa-save"></i>
                  Salvar Alterações
                </Button>
              </div>
            </form>
          </div>

          <!-- System Settings -->
          <div v-if="activeTab === 'system'" class="settings-section">
            <div class="section-header">
              <h2>Configurações do Sistema</h2>
              <p>Parâmetros técnicos e de funcionamento</p>
            </div>

            <form @submit.prevent="saveSystemSettings" class="settings-form">
              <div class="form-group">
                <label>
                  <input type="checkbox" v-model="systemForm.maintenance_mode">
                  Modo de Manutenção
                </label>
                <span class="help-text">Desabilita temporariamente o acesso ao sistema</span>
              </div>

              <div class="form-group">
                <label>
                  <input type="checkbox" v-model="systemForm.debug_mode">
                  Modo Debug
                </label>
                <span class="help-text">Exibe erros detalhados (apenas em desenvolvimento)</span>
              </div>

              <div class="form-group">
                <label>
                  <input type="checkbox" v-model="systemForm.allow_registration">
                  Permitir Auto-Registro
                </label>
                <span class="help-text">Permite que novos usuários se cadastrem</span>
              </div>

              <div class="form-group">
                <label>Duração da Sessão (minutos)</label>
                <Input v-model="systemForm.session_lifetime" type="number" />
                <span class="help-text">Tempo até o logout automático por inatividade</span>
              </div>

              <div class="form-group">
                <label>Limite de Upload (MB)</label>
                <Input v-model="systemForm.max_upload_size" type="number" />
              </div>

              <div class="form-group">
                <label>Itens por Página</label>
                <Input v-model="systemForm.items_per_page" type="number" />
              </div>

              <div class="form-group">
                <label>Retenção de Logs (dias)</label>
                <Input v-model="systemForm.log_retention_days" type="number" />
                <span class="help-text">Logs mais antigos serão automaticamente removidos</span>
              </div>

              <div class="form-actions">
                <Button variant="danger" @click="clearCache">
                  <i class="fa fa-trash"></i>
                  Limpar Cache
                </Button>
                <Button variant="primary" type="submit" :disabled="saving">
                  <i class="fa fa-save"></i>
                  Salvar Alterações
                </Button>
              </div>
            </form>
          </div>

          <!-- Appearance Settings -->
          <div v-if="activeTab === 'appearance'" class="settings-section">
            <div class="section-header">
              <h2>Aparência</h2>
              <p>Personalize a identidade visual do sistema</p>
            </div>

            <form @submit.prevent="saveAppearanceSettings" class="settings-form">
              <div class="form-group">
                <label>Logo da Empresa</label>
                <div class="logo-upload">
                  <div v-if="appearanceForm.logo" class="logo-preview">
                    <img :src="appearanceForm.logo" alt="Logo">
                    <button type="button" @click="appearanceForm.logo = null" class="remove-btn">
                      <i class="fa fa-times"></i>
                    </button>
                  </div>
                  <div v-else class="logo-placeholder">
                    <i class="fa fa-image"></i>
                    <p>Nenhuma logo definida</p>
                  </div>
                  <input
                    ref="logoInput"
                    type="file"
                    accept="image/*"
                    @change="handleLogoUpload"
                    style="display: none"
                  >
                  <Button variant="secondary" size="small" @click="$refs.logoInput.click()">
                    <i class="fa fa-upload"></i>
                    {{ appearanceForm.logo ? 'Alterar Logo' : 'Enviar Logo' }}
                  </Button>
                </div>
              </div>

              <div class="form-group">
                <label>Favicon</label>
                <div class="favicon-upload">
                  <div v-if="appearanceForm.favicon" class="favicon-preview">
                    <img :src="appearanceForm.favicon" alt="Favicon">
                  </div>
                  <Input
                    ref="faviconInput"
                    type="file"
                    accept="image/*"
                    @change="handleFaviconUpload"
                  />
                </div>
              </div>

              <div class="form-group">
                <label>Cor Primária</label>
                <div class="color-input">
                  <input type="color" v-model="appearanceForm.primary_color">
                  <Input v-model="appearanceForm.primary_color" />
                </div>
              </div>

              <div class="form-group">
                <label>Cor Secundária</label>
                <div class="color-input">
                  <input type="color" v-model="appearanceForm.secondary_color">
                  <Input v-model="appearanceForm.secondary_color" />
                </div>
              </div>

              <div class="form-group">
                <label>Cor de Destaque</label>
                <div class="color-input">
                  <input type="color" v-model="appearanceForm.accent_color">
                  <Input v-model="appearanceForm.accent_color" />
                </div>
              </div>

              <div class="form-group">
                <label>Tema Padrão</label>
                <div class="theme-selector">
                  <label class="theme-option">
                    <input type="radio" v-model="appearanceForm.default_theme" value="light">
                    <div class="theme-card">
                      <i class="fa fa-sun"></i>
                      <span>Claro</span>
                    </div>
                  </label>
                  <label class="theme-option">
                    <input type="radio" v-model="appearanceForm.default_theme" value="dark">
                    <div class="theme-card">
                      <i class="fa fa-moon"></i>
                      <span>Escuro</span>
                    </div>
                  </label>
                </div>
              </div>

              <div class="form-actions">
                <Button variant="primary" type="submit" :disabled="saving">
                  <i class="fa fa-save"></i>
                  Salvar Alterações
                </Button>
              </div>
            </form>
          </div>

          <!-- Backup Settings -->
          <div v-if="activeTab === 'backup'" class="settings-section">
            <div class="section-header">
              <h2>Backup e Recuperação</h2>
              <p>Gerencie backups automáticos e manuais</p>
            </div>

            <div class="backup-section">
              <div class="backup-actions-card">
                <div class="action-item">
                  <div>
                    <h4>Criar Backup Manual</h4>
                    <p>Gera um backup completo do sistema e banco de dados</p>
                  </div>
                  <Button variant="primary" @click="createBackup">
                    <i class="fa fa-database"></i>
                    Criar Backup
                  </Button>
                </div>

                <div class="action-item">
                  <div>
                    <h4>Backup Automático</h4>
                    <p>Configure backups agendados</p>
                  </div>
                  <div class="backup-schedule">
                    <select v-model="backupForm.frequency" class="form-select">
                      <option value="disabled">Desativado</option>
                      <option value="daily">Diário</option>
                      <option value="weekly">Semanal</option>
                      <option value="monthly">Mensal</option>
                    </select>
                    <Button variant="secondary" @click="saveBackupSettings">
                      Salvar
                    </Button>
                  </div>
                </div>

                <div class="action-item">
                  <div>
                    <h4>Retenção de Backups</h4>
                    <p>Número de backups a manter</p>
                  </div>
                  <Input
                    v-model="backupForm.retention_count"
                    type="number"
                    min="1"
                    max="30"
                  />
                </div>
              </div>

              <div class="backups-list">
                <h4>Backups Disponíveis</h4>
                <div v-for="backup in backups" :key="backup.id" class="backup-item">
                  <div class="backup-info">
                    <i class="fa fa-file-archive"></i>
                    <div>
                      <strong>{{ backup.filename }}</strong>
                      <span>{{ formatFileSize(backup.size) }} • {{ formatDate(backup.created_at) }}</span>
                    </div>
                  </div>
                  <div class="backup-actions">
                    <Button variant="link" size="small" @click="downloadBackup(backup)">
                      <i class="fa fa-download"></i>
                    </Button>
                    <Button variant="danger" size="small" @click="deleteBackup(backup)">
                      <i class="fa fa-trash"></i>
                    </Button>
                  </div>
                </div>

                <div v-if="!backups.length" class="empty-backups">
                  <i class="fa fa-database"></i>
                  <p>Nenhum backup disponível</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import Button from '@/Components/Button.vue';
import Input from '@/Components/Input.vue';

const props = defineProps({
  settings: Object,
  backups: Array
});

const activeTab = ref('general');
const saving = ref(false);
const logoInput = ref(null);
const faviconInput = ref(null);

const tabs = [
  { id: 'general', label: 'Geral', icon: 'fa fa-cog' },
  { id: 'email', label: 'Email', icon: 'fa fa-envelope' },
  { id: 'system', label: 'Sistema', icon: 'fa fa-server' },
  { id: 'appearance', label: 'Aparência', icon: 'fa fa-palette' },
  { id: 'backup', label: 'Backup', icon: 'fa fa-database' }
];

const brazilianStates = [
  { value: 'AC', label: 'Acre' },
  { value: 'AL', label: 'Alagoas' },
  { value: 'AP', label: 'Amapá' },
  { value: 'AM', label: 'Amazonas' },
  { value: 'BA', label: 'Bahia' },
  { value: 'CE', label: 'Ceará' },
  { value: 'DF', label: 'Distrito Federal' },
  { value: 'ES', label: 'Espírito Santo' },
  { value: 'GO', label: 'Goiás' },
  { value: 'MA', label: 'Maranhão' },
  { value: 'MT', label: 'Mato Grosso' },
  { value: 'MS', label: 'Mato Grosso do Sul' },
  { value: 'MG', label: 'Minas Gerais' },
  { value: 'PA', label: 'Pará' },
  { value: 'PB', label: 'Paraíba' },
  { value: 'PR', label: 'Paraná' },
  { value: 'PE', label: 'Pernambuco' },
  { value: 'PI', label: 'Piauí' },
  { value: 'RJ', label: 'Rio de Janeiro' },
  { value: 'RN', label: 'Rio Grande do Norte' },
  { value: 'RS', label: 'Rio Grande do Sul' },
  { value: 'RO', label: 'Rondônia' },
  { value: 'RR', label: 'Roraima' },
  { value: 'SC', label: 'Santa Catarina' },
  { value: 'SP', label: 'São Paulo' },
  { value: 'SE', label: 'Sergipe' },
  { value: 'TO', label: 'Tocantins' }
];

const generalForm = ref({
  company_name: props.settings?.company_name || '',
  company_email: props.settings?.company_email || '',
  company_phone: props.settings?.company_phone || '',
  address: props.settings?.address || '',
  city: props.settings?.city || '',
  state: props.settings?.state || '',
  zip_code: props.settings?.zip_code || '',
  cnpj: props.settings?.cnpj || '',
  description: props.settings?.description || '',
  timezone: props.settings?.timezone || 'America/Sao_Paulo',
  default_language: props.settings?.default_language || 'pt-BR'
});

const emailForm = ref({
  mail_driver: props.settings?.mail_driver || 'smtp',
  mail_host: props.settings?.mail_host || '',
  mail_port: props.settings?.mail_port || 587,
  mail_username: props.settings?.mail_username || '',
  mail_password: '',
  mail_encryption: props.settings?.mail_encryption || 'tls',
  mail_from_address: props.settings?.mail_from_address || '',
  mail_from_name: props.settings?.mail_from_name || ''
});

const systemForm = ref({
  maintenance_mode: props.settings?.maintenance_mode || false,
  debug_mode: props.settings?.debug_mode || false,
  allow_registration: props.settings?.allow_registration || false,
  session_lifetime: props.settings?.session_lifetime || 120,
  max_upload_size: props.settings?.max_upload_size || 10,
  items_per_page: props.settings?.items_per_page || 15,
  log_retention_days: props.settings?.log_retention_days || 30
});

const appearanceForm = ref({
  logo: props.settings?.logo || null,
  favicon: props.settings?.favicon || null,
  primary_color: props.settings?.primary_color || '#1160b7',
  secondary_color: props.settings?.secondary_color || '#dfe2e8',
  accent_color: props.settings?.accent_color || '#d24726',
  default_theme: props.settings?.default_theme || 'light'
});

const backupForm = ref({
  frequency: props.settings?.backup_frequency || 'disabled',
  retention_count: props.settings?.backup_retention_count || 7
});

const breadcrumbs = [
  { label: 'Configurações' }
];

const saveGeneralSettings = () => {
  saving.value = true;
  router.post('/settings/general', generalForm.value, {
    preserveScroll: true,
    onFinish: () => {
      saving.value = false;
    }
  });
};

const saveEmailSettings = () => {
  saving.value = true;
  router.post('/settings/email', emailForm.value, {
    preserveScroll: true,
    onFinish: () => {
      saving.value = false;
    }
  });
};

const testEmailConnection = () => {
  router.post('/settings/email/test', emailForm.value, {
    preserveScroll: true
  });
};

const saveSystemSettings = () => {
  saving.value = true;
  router.post('/settings/system', systemForm.value, {
    preserveScroll: true,
    onFinish: () => {
      saving.value = false;
    }
  });
};

const clearCache = () => {
  if (confirm('Limpar todo o cache do sistema?')) {
    router.post('/settings/cache/clear', {}, {
      preserveScroll: true
    });
  }
};

const saveAppearanceSettings = () => {
  saving.value = true;
  router.post('/settings/appearance', appearanceForm.value, {
    preserveScroll: true,
    onFinish: () => {
      saving.value = false;
    }
  });
};

const handleLogoUpload = (e) => {
  const file = e.target.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = (event) => {
      appearanceForm.value.logo = event.target.result;
    };
    reader.readAsDataURL(file);
  }
};

const handleFaviconUpload = (e) => {
  const file = e.target.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = (event) => {
      appearanceForm.value.favicon = event.target.result;
    };
    reader.readAsDataURL(file);
  }
};

const saveBackupSettings = () => {
  router.post('/settings/backup', backupForm.value, {
    preserveScroll: true
  });
};

const createBackup = () => {
  if (confirm('Criar um novo backup? Isso pode levar alguns minutos.')) {
    router.post('/settings/backup/create', {}, {
      preserveScroll: true
    });
  }
};

const downloadBackup = (backup) => {
  window.open(`/settings/backup/${backup.id}/download`, '_blank');
};

const deleteBackup = (backup) => {
  if (confirm(`Excluir o backup "${backup.filename}"?`)) {
    router.delete(`/settings/backup/${backup.id}`, {
      preserveScroll: true
    });
  }
};

const formatDate = (date) => {
  return new Date(date).toLocaleString('pt-BR');
};

const formatFileSize = (bytes) => {
  if (bytes < 1024) return bytes + ' B';
  if (bytes < 1048576) return (bytes / 1024).toFixed(2) + ' KB';
  if (bytes < 1073741824) return (bytes / 1048576).toFixed(2) + ' MB';
  return (bytes / 1073741824).toFixed(2) + ' GB';
};
</script>

<style scoped lang="scss">
.settings-page {
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

.settings-layout {
  display: grid;
  grid-template-columns: 280px 1fr;
  gap: 2rem;
}

.settings-sidebar {
  .settings-menu {
    background: var(--bg-primary);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    overflow: hidden;
    position: sticky;
    top: 2rem;

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
      font-size: 0.95rem;

      &:last-child {
        border-bottom: none;
      }

      &:hover {
        background: var(--bg-secondary);
      }

      &.active {
        background: var(--color-primary);
        color: white;

        i {
          color: white;
        }
      }

      i {
        width: 20px;
        color: var(--color-primary);
        transition: color 0.2s;
      }
    }
  }
}

.settings-content {
  .settings-section {
    background: var(--bg-primary);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 2rem;

    .section-header {
      margin-bottom: 2rem;
      padding-bottom: 2rem;
      border-bottom: 1px solid var(--border-color);

      h2 {
        margin: 0 0 0.5rem 0;
        color: var(--text-primary);
      }

      p {
        margin: 0;
        color: var(--text-secondary);
      }
    }
  }
}

.settings-form {
  .form-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 1.5rem;
  }

  .form-group {
    margin-bottom: 1.5rem;

    label {
      display: block;
      margin-bottom: 0.5rem;
      font-weight: 500;
      color: var(--text-primary);

      input[type="checkbox"] {
        margin-right: 0.5rem;
      }
    }

    textarea {
      width: 100%;
      padding: 0.75rem;
      border: 1px solid var(--border-color);
      border-radius: 4px;
      font-family: inherit;
      font-size: 0.95rem;
      background: var(--bg-primary);
      color: var(--text-primary);
      resize: vertical;

      &:focus {
        outline: none;
        border-color: var(--color-primary);
      }
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

    .help-text {
      display: block;
      margin-top: 0.25rem;
      font-size: 0.85rem;
      color: var(--text-secondary);
    }
  }

  .form-actions {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid var(--border-color);
  }
}

// Logo Upload
.logo-upload,
.favicon-upload {
  display: flex;
  flex-direction: column;
  gap: 1rem;

  .logo-preview {
    position: relative;
    padding: 2rem;
    border: 1px solid var(--border-color);
    border-radius: 4px;
    text-align: center;

    img {
      max-width: 200px;
      max-height: 100px;
      object-fit: contain;
    }

    .remove-btn {
      position: absolute;
      top: 0.5rem;
      right: 0.5rem;
      width: 32px;
      height: 32px;
      border: none;
      background: var(--color-error);
      color: white;
      border-radius: 50%;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;

      &:hover {
        opacity: 0.8;
      }
    }
  }

  .logo-placeholder {
    padding: 3rem;
    border: 2px dashed var(--border-color);
    border-radius: 4px;
    text-align: center;
    color: var(--text-secondary);

    i {
      font-size: 3rem;
      opacity: 0.3;
      margin-bottom: 0.5rem;
    }

    p {
      margin: 0;
    }
  }

  .favicon-preview {
    width: 64px;
    height: 64px;
    padding: 1rem;
    border: 1px solid var(--border-color);
    border-radius: 4px;

    img {
      width: 100%;
      height: 100%;
      object-fit: contain;
    }
  }
}

.color-input {
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

.theme-selector {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;

  .theme-option {
    input {
      display: none;

      &:checked + .theme-card {
        border-color: var(--color-primary);
        background: var(--bg-secondary);
      }
    }

    .theme-card {
      padding: 2rem 1rem;
      border: 2px solid var(--border-color);
      border-radius: 8px;
      text-align: center;
      cursor: pointer;
      transition: all 0.2s;

      &:hover {
        border-color: var(--color-primary);
      }

      i {
        font-size: 2rem;
        margin-bottom: 0.5rem;
        display: block;
        color: var(--text-primary);
      }

      span {
        font-size: 0.9rem;
        color: var(--text-primary);
      }
    }
  }
}

// Backup Section
.backup-section {
  .backup-actions-card {
    margin-bottom: 2rem;

    .action-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 1.5rem;
      border: 1px solid var(--border-color);
      border-radius: 4px;
      margin-bottom: 1rem;

      h4 {
        margin: 0 0 0.5rem 0;
        color: var(--text-primary);
      }

      p {
        margin: 0;
        color: var(--text-secondary);
        font-size: 0.9rem;
      }

      .backup-schedule {
        display: flex;
        gap: 1rem;
        align-items: center;

        .form-select {
          min-width: 150px;
        }
      }
    }
  }

  .backups-list {
    h4 {
      margin: 0 0 1rem 0;
      color: var(--text-primary);
    }

    .backup-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 1rem;
      border: 1px solid var(--border-color);
      border-radius: 4px;
      margin-bottom: 0.5rem;

      .backup-info {
        display: flex;
        align-items: center;
        gap: 1rem;

        i {
          font-size: 1.5rem;
          color: var(--color-primary);
        }

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

      .backup-actions {
        display: flex;
        gap: 0.5rem;
      }
    }

    .empty-backups {
      text-align: center;
      padding: 3rem;
      color: var(--text-secondary);

      i {
        font-size: 3rem;
        opacity: 0.3;
        margin-bottom: 0.5rem;
      }

      p {
        margin: 0;
      }
    }
  }
}

@media (max-width: 1024px) {
  .settings-layout {
    grid-template-columns: 1fr;
  }

  .settings-sidebar .settings-menu {
    position: static;
    display: flex;
    overflow-x: auto;

    .menu-item {
      flex: 0 0 auto;
      border-bottom: none;
      border-right: 1px solid var(--border-color);

      &:last-child {
        border-right: none;
      }
    }
  }
}
</style>
