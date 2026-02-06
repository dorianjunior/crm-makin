<template>
  <MainLayout title="Configurações do Sistema">
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

