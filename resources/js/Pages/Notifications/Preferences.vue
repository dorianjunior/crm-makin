<template>
  <MainLayout title="Preferências">
    <div class="page-container">
      <!-- Header -->
      <div class="page-header">
        <div class="page-header__content">
          <h1 class="page-header__title">PREFERÊNCIAS</h1>
          <p class="page-header__subtitle">Configure como e quando receber notificações</p>
        </div>
        <div class="page-header__actions">
          <button class="btn btn--secondary" @click="reset">
            <i class="fas fa-undo"></i>
            Resetar
          </button>
          <button class="btn" @click="save" :disabled="saving">
            <i class="fas fa-save"></i>
            Salvar Preferências
          </button>
        </div>
      </div>

      <!-- Settings Sections -->
      <div class="settings-grid">
        <!-- Email Notifications -->
        <div class="card">
          <div class="card__header">
            <div class="section-icon">
              <i class="fas fa-envelope"></i>
            </div>
            <h3 class="card__title">Notificações por E-mail</h3>
          </div>
          <div class="card__body">
            <div class="setting-group">
              <label class="checkbox">
                <input type="checkbox" v-model="settings.email_new_lead" />
                <span>Novo lead criado</span>
              </label>
              <label class="checkbox">
                <input type="checkbox" v-model="settings.email_lead_converted" />
                <span>Lead convertido</span>
              </label>
              <label class="checkbox">
                <input type="checkbox" v-model="settings.email_task_assigned" />
                <span>Tarefa atribuída a mim</span>
              </label>
              <label class="checkbox">
                <input type="checkbox" v-model="settings.email_task_due" />
                <span>Tarefa próxima do vencimento</span>
              </label>
              <label class="checkbox">
                <input type="checkbox" v-model="settings.email_message_received" />
                <span>Mensagem recebida</span>
              </label>
            </div>
          </div>
        </div>

        <!-- Push Notifications -->
        <div class="card">
          <div class="card__header">
            <div class="section-icon">
              <i class="fas fa-bell"></i>
            </div>
            <h3 class="card__title">Notificações Push</h3>
          </div>
          <div class="card__body">
            <div class="setting-group">
              <label class="checkbox">
                <input type="checkbox" v-model="settings.push_new_lead" />
                <span>Novo lead criado</span>
              </label>
              <label class="checkbox">
                <input type="checkbox" v-model="settings.push_task_assigned" />
                <span>Tarefa atribuída</span>
              </label>
              <label class="checkbox">
                <input type="checkbox" v-model="settings.push_message_received" />
                <span>Nova mensagem</span>
              </label>
              <label class="checkbox">
                <input type="checkbox" v-model="settings.push_proposal_updated" />
                <span>Proposta atualizada</span>
              </label>
            </div>
          </div>
        </div>

        <!-- Frequency Settings -->
        <div class="card">
          <div class="card__header">
            <div class="section-icon">
              <i class="fas fa-clock"></i>
            </div>
            <h3 class="card__title">Frequência</h3>
          </div>
          <div class="card__body">
            <div class="form-group">
              <label>Resumo Diário</label>
              <select v-model="settings.daily_digest_time" class="select">
                <option value="">Desativado</option>
                <option value="08:00">08:00 - Manhã</option>
                <option value="12:00">12:00 - Meio-dia</option>
                <option value="18:00">18:00 - Fim do dia</option>
              </select>
            </div>

            <div class="form-group">
              <label>Resumo Semanal</label>
              <select v-model="settings.weekly_digest_day" class="select">
                <option value="">Desativado</option>
                <option value="1">Segunda-feira</option>
                <option value="5">Sexta-feira</option>
                <option value="0">Domingo</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Quiet Hours -->
        <div class="card">
          <div class="card__header">
            <div class="section-icon">
              <i class="fas fa-moon"></i>
            </div>
            <h3 class="card__title">Horário Silencioso</h3>
          </div>
          <div class="card__body">
            <p class="helper-text">Não receber notificações durante estas horas</p>

            <div class="form-group">
              <label class="checkbox">
                <input type="checkbox" v-model="settings.quiet_hours_enabled" />
                <span>Ativar horário silencioso</span>
              </label>
            </div>

            <div v-if="settings.quiet_hours_enabled" class="time-range">
              <div class="form-group">
                <label>Início</label>
                <input type="time" v-model="settings.quiet_hours_start" class="input" />
              </div>
              <div class="form-group">
                <label>Fim</label>
                <input type="time" v-model="settings.quiet_hours_end" class="input" />
              </div>
            </div>
          </div>
        </div>

        <!-- Channels -->
        <div class="card">
          <div class="card__header">
            <div class="section-icon">
              <i class="fas fa-hashtag"></i>
            </div>
            <h3 class="card__title">Canais</h3>
          </div>
          <div class="card__body">
            <div class="setting-group">
              <label class="checkbox">
                <input type="checkbox" v-model="settings.notify_whatsapp" />
                <span><i class="fab fa-whatsapp"></i> WhatsApp</span>
              </label>
              <label class="checkbox">
                <input type="checkbox" v-model="settings.notify_instagram" />
                <span><i class="fab fa-instagram"></i> Instagram</span>
              </label>
              <label class="checkbox">
                <input type="checkbox" v-model="settings.notify_cms" />
                <span><i class="fas fa-file-alt"></i> CMS Updates</span>
              </label>
            </div>
          </div>
        </div>

        <!-- System Notifications -->
        <div class="card">
          <div class="card__header">
            <div class="section-icon">
              <i class="fas fa-cog"></i>
            </div>
            <h3 class="card__title">Sistema</h3>
          </div>
          <div class="card__body">
            <div class="setting-group">
              <label class="checkbox">
                <input type="checkbox" v-model="settings.notify_system_updates" />
                <span>Atualizações do sistema</span>
              </label>
              <label class="checkbox">
                <input type="checkbox" v-model="settings.notify_maintenance" />
                <span>Avisos de manutenção</span>
              </label>
              <label class="checkbox">
                <input type="checkbox" v-model="settings.notify_security" />
                <span>Alertas de segurança</span>
              </label>
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

const props = defineProps({
  preferences: Object,
});

const saving = ref(false);

const settings = ref({
  // Email
  email_new_lead: props.preferences?.email_new_lead ?? true,
  email_lead_converted: props.preferences?.email_lead_converted ?? true,
  email_task_assigned: props.preferences?.email_task_assigned ?? true,
  email_task_due: props.preferences?.email_task_due ?? true,
  email_message_received: props.preferences?.email_message_received ?? false,

  // Push
  push_new_lead: props.preferences?.push_new_lead ?? true,
  push_task_assigned: props.preferences?.push_task_assigned ?? true,
  push_message_received: props.preferences?.push_message_received ?? true,
  push_proposal_updated: props.preferences?.push_proposal_updated ?? false,

  // Frequency
  daily_digest_time: props.preferences?.daily_digest_time ?? '08:00',
  weekly_digest_day: props.preferences?.weekly_digest_day ?? '1',

  // Quiet Hours
  quiet_hours_enabled: props.preferences?.quiet_hours_enabled ?? false,
  quiet_hours_start: props.preferences?.quiet_hours_start ?? '22:00',
  quiet_hours_end: props.preferences?.quiet_hours_end ?? '08:00',

  // Channels
  notify_whatsapp: props.preferences?.notify_whatsapp ?? true,
  notify_instagram: props.preferences?.notify_instagram ?? true,
  notify_cms: props.preferences?.notify_cms ?? false,

  // System
  notify_system_updates: props.preferences?.notify_system_updates ?? true,
  notify_maintenance: props.preferences?.notify_maintenance ?? true,
  notify_security: props.preferences?.notify_security ?? true,
});

const save = () => {
  saving.value = true;
  router.post('/notifications/preferences', settings.value, {
    onFinish: () => saving.value = false,
  });
};

const reset = () => {
  if (confirm('Resetar todas as preferências para o padrão?')) {
    router.post('/notifications/preferences/reset', {}, {
      onSuccess: () => {
        location.reload();
      }
    });
  }
};
</script>

<style scoped lang="scss">
.page-container {
  padding: 32px;
}

.settings-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
  gap: 24px;
  margin-top: 32px;
}

.section-icon {
  width: 48px;
  height: 48px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #FF6B35;
  color: var(--bg-primary);
  font-size: 24px;
  margin-bottom: 16px;
}

.setting-group {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.checkbox {
  display: flex;
  align-items: center;
  gap: 12px;
  cursor: pointer;
  user-select: none;
  font-size: 14px;
  color: var(--text-primary);

  input[type="checkbox"] {
    width: 20px;
    height: 20px;
    border: 2px solid var(--border-color);
    cursor: pointer;

    &:checked {
      accent-color: #FF6B35;
    }
  }

  span {
    display: flex;
    align-items: center;
    gap: 8px;
  }
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;

  label {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--text-secondary);
  }
}

.helper-text {
  font-size: 13px;
  color: var(--text-secondary);
  margin: 0 0 16px;
}

.time-range {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
  margin-top: 16px;
}

@media (max-width: 768px) {
  .settings-grid {
    grid-template-columns: 1fr;
  }

  .time-range {
    grid-template-columns: 1fr;
  }
}
</style>
