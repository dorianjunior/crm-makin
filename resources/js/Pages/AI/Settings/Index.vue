<template>
  <MainLayout>
    <div class="page-container">
      <!-- Header -->
      <div class="page-header">
        <div class="page-header__content">
          <h1 class="page-header__title">CONFIGURAÇÕES DE IA</h1>
          <p class="page-header__subtitle">Configure provedores e automações de inteligência artificial</p>
        </div>
        <div class="page-header__actions">
          <button class="btn btn--secondary" @click="testConnection" :disabled="testing">
            <i class="fas fa-vial"></i>
            Testar Conexão
          </button>
          <button class="btn" @click="saveSettings" :disabled="saving">
            <i class="fas fa-save"></i>
            Salvar Configurações
          </button>
        </div>
      </div>

      <!-- Provider Selection -->
      <div class="section-header">
        <h2 class="section-header__title">PROVEDOR DE IA</h2>
        <p class="section-header__subtitle">Escolha qual provedor usar para as funcionalidades de IA</p>
      </div>

      <div class="provider-grid">
        <div
          v-for="provider in providers"
          :key="provider.value"
          class="provider-card"
          :class="{ 'provider-card--active': settings.provider === provider.value }"
          @click="settings.provider = provider.value"
        >
          <div class="provider-card__icon">
            <i :class="provider.icon"></i>
          </div>
          <h3 class="provider-card__name">{{ provider.name }}</h3>
          <p class="provider-card__description">{{ provider.description }}</p>
          <div v-if="settings.provider === provider.value" class="provider-card__active-badge">
            <i class="fas fa-check"></i>
            SELECIONADO
          </div>
        </div>
      </div>

      <!-- API Configuration -->
      <div class="section-header">
        <h2 class="section-header__title">CONFIGURAÇÃO DE API</h2>
        <p class="section-header__subtitle">Credenciais e endpoints do provedor selecionado</p>
      </div>

      <div class="card">
        <div class="card__body">
          <div class="form-group">
            <label>API Key</label>
            <input
              v-model="settings.api_key"
              type="password"
              class="input"
              placeholder="sk-..."
            />
          </div>

          <div class="form-group">
            <label>Modelo</label>
            <select v-model="settings.model" class="select">
              <option value="gpt-4">GPT-4 (Recomendado)</option>
              <option value="gpt-3.5-turbo">GPT-3.5 Turbo</option>
              <option value="claude-3-opus">Claude 3 Opus</option>
              <option value="claude-3-sonnet">Claude 3 Sonnet</option>
            </select>
          </div>

          <div class="form-group">
            <label>Temperatura (0-1)</label>
            <input
              v-model.number="settings.temperature"
              type="number"
              min="0"
              max="1"
              step="0.1"
              class="input"
            />
            <p class="helper-text">Controla a criatividade das respostas. Valores menores = mais consistente.</p>
          </div>

          <div class="form-group">
            <label>Max Tokens</label>
            <input
              v-model.number="settings.max_tokens"
              type="number"
              class="input"
            />
          </div>
        </div>
      </div>

      <!-- Automation Settings -->
      <div class="section-header">
        <h2 class="section-header__title">AUTOMAÇÕES</h2>
        <p class="section-header__subtitle">Configure quando e como a IA deve agir automaticamente</p>
      </div>

      <div class="settings-grid">
        <div class="card">
          <div class="card__header">
            <div class="section-icon">
              <i class="fas fa-user-plus"></i>
            </div>
            <h3 class="card__title">Qualificação de Leads</h3>
          </div>
          <div class="card__body">
            <label class="checkbox">
              <input type="checkbox" v-model="settings.auto_qualify_leads" />
              <span>Qualificar leads automaticamente</span>
            </label>
            <label class="checkbox">
              <input type="checkbox" v-model="settings.auto_send_messages" />
              <span>Enviar mensagens de boas-vindas</span>
            </label>
            <label class="checkbox">
              <input type="checkbox" v-model="settings.auto_schedule_followup" />
              <span>Agendar follow-ups automaticamente</span>
            </label>
          </div>
        </div>

        <div class="card">
          <div class="card__header">
            <div class="section-icon">
              <i class="fas fa-comments"></i>
            </div>
            <h3 class="card__title">Respostas Automáticas</h3>
          </div>
          <div class="card__body">
            <label class="checkbox">
              <input type="checkbox" v-model="settings.auto_respond_whatsapp" />
              <span>WhatsApp</span>
            </label>
            <label class="checkbox">
              <input type="checkbox" v-model="settings.auto_respond_instagram" />
              <span>Instagram Direct</span>
            </label>
            <label class="checkbox">
              <input type="checkbox" v-model="settings.auto_respond_email" />
              <span>E-mail</span>
            </label>

            <div class="form-group" style="margin-top: 16px;">
              <label>Tempo de resposta (segundos)</label>
              <input
                v-model.number="settings.response_delay"
                type="number"
                min="0"
                class="input"
              />
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card__header">
            <div class="section-icon">
              <i class="fas fa-file-alt"></i>
            </div>
            <h3 class="card__title">Geração de Conteúdo</h3>
          </div>
          <div class="card__body">
            <label class="checkbox">
              <input type="checkbox" v-model="settings.auto_generate_posts" />
              <span>Gerar posts para CMS</span>
            </label>
            <label class="checkbox">
              <input type="checkbox" v-model="settings.auto_generate_descriptions" />
              <span>Criar descrições de produtos</span>
            </label>
            <label class="checkbox">
              <input type="checkbox" v-model="settings.auto_translate" />
              <span>Tradução automática</span>
            </label>
          </div>
        </div>

        <div class="card">
          <div class="card__header">
            <div class="section-icon">
              <i class="fas fa-chart-line"></i>
            </div>
            <h3 class="card__title">Análise e Insights</h3>
          </div>
          <div class="card__body">
            <label class="checkbox">
              <input type="checkbox" v-model="settings.auto_analyze_sentiment" />
              <span>Análise de sentimento</span>
            </label>
            <label class="checkbox">
              <input type="checkbox" v-model="settings.auto_extract_keywords" />
              <span>Extração de palavras-chave</span>
            </label>
            <label class="checkbox">
              <input type="checkbox" v-model="settings.auto_generate_reports" />
              <span>Relatórios automáticos</span>
            </label>
          </div>
        </div>
      </div>

      <!-- Prompt Templates -->
      <div class="section-header">
        <h2 class="section-header__title">TEMPLATES DE PROMPT</h2>
        <p class="section-header__subtitle">Personalize as instruções enviadas para a IA</p>
      </div>

      <div class="card">
        <div class="card__body">
          <div class="form-group">
            <label>Prompt de Qualificação</label>
            <textarea
              v-model="settings.qualification_prompt"
              class="textarea"
              rows="4"
              placeholder="Você é um assistente de vendas..."
            ></textarea>
          </div>

          <div class="form-group">
            <label>Prompt de Resposta Automática</label>
            <textarea
              v-model="settings.response_prompt"
              class="textarea"
              rows="4"
              placeholder="Responda de forma amigável e profissional..."
            ></textarea>
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
  currentSettings: Object,
});

const testing = ref(false);
const saving = ref(false);

const providers = [
  {
    name: 'OpenAI',
    value: 'openai',
    icon: 'fas fa-brain',
    description: 'GPT-4 e GPT-3.5 - Melhor para conversação e análise'
  },
  {
    name: 'Anthropic',
    value: 'anthropic',
    icon: 'fas fa-robot',
    description: 'Claude 3 - Ótimo para tarefas complexas'
  },
  {
    name: 'Google AI',
    value: 'google',
    icon: 'fab fa-google',
    description: 'Gemini - Rápido e eficiente'
  },
];

const settings = ref({
  provider: props.currentSettings?.provider || 'openai',
  api_key: props.currentSettings?.api_key || '',
  model: props.currentSettings?.model || 'gpt-4',
  temperature: props.currentSettings?.temperature || 0.7,
  max_tokens: props.currentSettings?.max_tokens || 2000,

  auto_qualify_leads: props.currentSettings?.auto_qualify_leads ?? true,
  auto_send_messages: props.currentSettings?.auto_send_messages ?? true,
  auto_schedule_followup: props.currentSettings?.auto_schedule_followup ?? false,

  auto_respond_whatsapp: props.currentSettings?.auto_respond_whatsapp ?? true,
  auto_respond_instagram: props.currentSettings?.auto_respond_instagram ?? false,
  auto_respond_email: props.currentSettings?.auto_respond_email ?? false,
  response_delay: props.currentSettings?.response_delay || 3,

  auto_generate_posts: props.currentSettings?.auto_generate_posts ?? false,
  auto_generate_descriptions: props.currentSettings?.auto_generate_descriptions ?? false,
  auto_translate: props.currentSettings?.auto_translate ?? false,

  auto_analyze_sentiment: props.currentSettings?.auto_analyze_sentiment ?? true,
  auto_extract_keywords: props.currentSettings?.auto_extract_keywords ?? true,
  auto_generate_reports: props.currentSettings?.auto_generate_reports ?? false,

  qualification_prompt: props.currentSettings?.qualification_prompt || '',
  response_prompt: props.currentSettings?.response_prompt || '',
});

const testConnection = () => {
  testing.value = true;
  router.post('/ai/settings/test', settings.value, {
    onFinish: () => testing.value = false,
  });
};

const saveSettings = () => {
  saving.value = true;
  router.post('/ai/settings', settings.value, {
    onFinish: () => saving.value = false,
  });
};
</script>

<style scoped lang="scss">
.page-container {
  padding: 32px;
}

.provider-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 24px;
  margin-bottom: 48px;
}

.provider-card {
  border: 2px solid var(--border-color);
  background: var(--bg-primary);
  padding: 32px;
  cursor: pointer;
  transition: all 0.2s ease;
  position: relative;

  &:hover {
    border-color: #FF6B35;
    transform: translateY(-2px);
  }

  &--active {
    border-color: #FF6B35;
    background: rgba(255, 107, 53, 0.05);
  }

  &__icon {
    width: 64px;
    height: 64px;
    border: 2px solid var(--border-color);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 32px;
    color: #FF6B35;
    margin-bottom: 16px;
  }

  &__name {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 24px;
    font-weight: 700;
    margin: 0 0 8px;
    color: var(--text-primary);
  }

  &__description {
    font-size: 14px;
    line-height: 1.6;
    color: var(--text-secondary);
    margin: 0;
  }

  &__active-badge {
    position: absolute;
    top: 16px;
    right: 16px;
    background: #FF6B35;
    color: var(--bg-primary);
    padding: 4px 12px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.1em;
  }
}

.settings-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
  gap: 24px;
  margin-bottom: 48px;
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

.checkbox {
  display: flex;
  align-items: center;
  gap: 12px;
  cursor: pointer;
  user-select: none;
  font-size: 14px;
  color: var(--text-primary);
  margin-bottom: 12px;

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
  margin-bottom: 24px;

  &:last-child {
    margin-bottom: 0;
  }

  label {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--text-secondary);
    display: block;
    margin-bottom: 8px;
  }
}

.helper-text {
  font-size: 12px;
  color: var(--text-secondary);
  margin-top: 6px;
}

@media (max-width: 768px) {
  .provider-grid,
  .settings-grid {
    grid-template-columns: 1fr;
  }
}
</style>
