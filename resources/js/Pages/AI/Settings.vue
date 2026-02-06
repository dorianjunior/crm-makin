<template>
  <MainLayout title="Configurações de IA">
    <div class="ai-settings">
      <!-- Header -->
      <div class="page-header">
        <div>
          <h1 class="page-title">
            <i class="fa fa-robot"></i>
            Configurações de IA
          </h1>
          <Breadcrumbs :items="breadcrumbs" />
        </div>
        <div class="header-actions">
          <Button
            label="Testar Integração"
            icon="fa fa-vial"
            @click="testConnection"
            :loading="testing"
            outlined
          />
          <Button
            label="Salvar Configurações"
            icon="fa fa-save"
            @click="saveSettings"
            :loading="saving"
          />
        </div>
      </div>

      <Alert
        v-if="alert.show"
        :type="alert.type"
        :message="alert.message"
        @close="alert.show = false"
      />

      <div class="settings-container">
        <!-- Sidebar Menu -->
        <div class="settings-sidebar">
          <nav class="settings-nav">
            <button
              :class="['nav-item', { active: activeTab === 'provider' }]"
              @click="activeTab = 'provider'"
            >
              <i class="fa fa-plug"></i>
              <span>Provedor IA</span>
            </button>
            <button
              :class="['nav-item', { active: activeTab === 'automation' }]"
              @click="activeTab = 'automation'"
            >
              <i class="fa fa-robot"></i>
              <span>Automação</span>
            </button>
            <button
              :class="['nav-item', { active: activeTab === 'templates' }]"
              @click="activeTab = 'templates'"
            >
              <i class="fa fa-file-alt"></i>
              <span>Templates de Prompt</span>
            </button>
            <button
              :class="['nav-item', { active: activeTab === 'context' }]"
              @click="activeTab = 'context'"
            >
              <i class="fa fa-database"></i>
              <span>Contexto Empresarial</span>
            </button>
            <button
              :class="['nav-item', { active: activeTab === 'limits' }]"
              @click="activeTab = 'limits'"
            >
              <i class="fa fa-chart-line"></i>
              <span>Limites & Uso</span>
            </button>
          </nav>
        </div>

        <!-- Main Content -->
        <div class="settings-content">
          <!-- Provider Settings -->
          <div v-if="activeTab === 'provider'" class="settings-section">
            <div class="section-header">
              <h2>Configuração do Provedor</h2>
              <p>Configure sua integração com Google Gemini AI</p>
            </div>

            <div class="form-grid">
              <div class="form-group full-width">
                <label class="required">Provedor de IA</label>
                <div class="provider-options">
                  <div
                    :class="['provider-card', { selected: form.provider === 'gemini' }]"
                    @click="form.provider = 'gemini'"
                  >
                    <div class="provider-icon">
                      <i class="fab fa-google"></i>
                    </div>
                    <h3>Google Gemini</h3>
                    <p>IA avançada do Google</p>
                    <span v-if="form.provider === 'gemini'" class="check-badge">
                      <i class="fa fa-check"></i>
                    </span>
                  </div>
                  <div class="provider-card disabled">
                    <div class="provider-icon">
                      <i class="fab fa-openai"></i>
                    </div>
                    <h3>OpenAI GPT</h3>
                    <p>Em breve</p>
                    <span class="coming-soon">Em Breve</span>
                  </div>
                  <div class="provider-card disabled">
                    <div class="provider-icon">
                      <i class="fab fa-microsoft"></i>
                    </div>
                    <h3>Azure OpenAI</h3>
                    <p>Em breve</p>
                    <span class="coming-soon">Em Breve</span>
                  </div>
                </div>
              </div>

              <div class="form-group full-width">
                <label class="required">API Key do Gemini</label>
                <div class="input-with-action">
                  <Input
                    v-model="form.api_key"
                    :type="showApiKey ? 'text' : 'password'"
                    placeholder="AIza..."
                  />
                  <button class="btn-toggle" @click="showApiKey = !showApiKey">
                    <i :class="showApiKey ? 'fa fa-eye-slash' : 'fa fa-eye'"></i>
                  </button>
                </div>
                <small class="help-text">
                  <i class="fa fa-info-circle"></i>
                  Obtenha sua API Key em <a href="https://makersuite.google.com/app/apikey" target="_blank">Google AI Studio</a>
                </small>
              </div>

              <div class="form-group">
                <label>Modelo</label>
                <select v-model="form.model" class="form-select">
                  <option value="gemini-1.5-pro">Gemini 1.5 Pro (Recomendado)</option>
                  <option value="gemini-1.5-flash">Gemini 1.5 Flash (Rápido)</option>
                  <option value="gemini-pro">Gemini Pro</option>
                </select>
                <small class="help-text">
                  Gemini 1.5 Pro oferece melhor qualidade, Flash é mais rápido
                </small>
              </div>

              <div class="form-group">
                <label>Temperatura</label>
                <div class="slider-container">
                  <input
                    v-model.number="form.temperature"
                    type="range"
                    min="0"
                    max="1"
                    step="0.1"
                    class="slider"
                  />
                  <span class="slider-value">{{ form.temperature }}</span>
                </div>
                <small class="help-text">
                  0 = mais previsível, 1 = mais criativo
                </small>
              </div>

              <div class="form-group">
                <label>Max Tokens</label>
                <Input
                  v-model.number="form.max_tokens"
                  type="number"
                  placeholder="2048"
                />
              </div>

              <div class="form-group">
                <label>Top P</label>
                <div class="slider-container">
                  <input
                    v-model.number="form.top_p"
                    type="range"
                    min="0"
                    max="1"
                    step="0.05"
                    class="slider"
                  />
                  <span class="slider-value">{{ form.top_p }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Automation Settings -->
          <div v-if="activeTab === 'automation'" class="settings-section">
            <div class="section-header">
              <h2>Configurações de Automação</h2>
              <p>Configure como a IA deve responder automaticamente</p>
            </div>

            <div class="automation-cards">
              <div class="automation-card">
                <div class="card-header">
                  <div class="card-icon whatsapp">
                    <i class="fab fa-whatsapp"></i>
                  </div>
                  <div>
                    <h3>WhatsApp</h3>
                    <p>Respostas automáticas via WhatsApp Business</p>
                  </div>
                  <label class="switch">
                    <input type="checkbox" v-model="form.whatsapp_enabled" />
                    <span class="slider-switch"></span>
                  </label>
                </div>

                <div v-if="form.whatsapp_enabled" class="card-content">
                  <div class="form-group">
                    <label>Horário de Funcionamento</label>
                    <div class="time-inputs">
                      <Input v-model="form.whatsapp_start_time" type="time" size="small" />
                      <span>até</span>
                      <Input v-model="form.whatsapp_end_time" type="time" size="small" />
                    </div>
                  </div>

                  <div class="form-group">
                    <label class="checkbox-label">
                      <input type="checkbox" v-model="form.whatsapp_only_first_message" />
                      <span>Responder apenas primeira mensagem</span>
                    </label>
                  </div>

                  <div class="form-group">
                    <label>Tempo de Espera (segundos)</label>
                    <Input
                      v-model.number="form.whatsapp_delay"
                      type="number"
                      size="small"
                      placeholder="5"
                    />
                    <small class="help-text">Aguardar antes de responder automaticamente</small>
                  </div>
                </div>
              </div>

              <div class="automation-card">
                <div class="card-header">
                  <div class="card-icon instagram">
                    <i class="fab fa-instagram"></i>
                  </div>
                  <div>
                    <h3>Instagram</h3>
                    <p>Respostas automáticas no Direct</p>
                  </div>
                  <label class="switch">
                    <input type="checkbox" v-model="form.instagram_enabled" />
                    <span class="slider-switch"></span>
                  </label>
                </div>

                <div v-if="form.instagram_enabled" class="card-content">
                  <div class="form-group">
                    <label class="checkbox-label">
                      <input type="checkbox" v-model="form.instagram_reply_stories" />
                      <span>Responder respostas a stories</span>
                    </label>
                  </div>

                  <div class="form-group">
                    <label class="checkbox-label">
                      <input type="checkbox" v-model="form.instagram_reply_mentions" />
                      <span>Responder menções em stories</span>
                    </label>
                  </div>

                  <div class="form-group">
                    <label>Tempo de Espera (segundos)</label>
                    <Input
                      v-model.number="form.instagram_delay"
                      type="number"
                      size="small"
                      placeholder="3"
                    />
                  </div>
                </div>
              </div>

              <div class="automation-card">
                <div class="card-header">
                  <div class="card-icon leads">
                    <i class="fa fa-user-plus"></i>
                  </div>
                  <div>
                    <h3>Conversão de Leads</h3>
                    <p>Criar leads automaticamente</p>
                  </div>
                  <label class="switch">
                    <input type="checkbox" v-model="form.auto_create_leads" />
                    <span class="slider-switch"></span>
                  </label>
                </div>

                <div v-if="form.auto_create_leads" class="card-content">
                  <div class="form-group">
                    <label>Pipeline Padrão</label>
                    <select v-model="form.default_pipeline_id" class="form-select">
                      <option :value="null">Selecione...</option>
                      <option v-for="pipeline in pipelines" :key="pipeline.id" :value="pipeline.id">
                        {{ pipeline.name }}
                      </option>
                    </select>
                  </div>

                  <div class="form-group">
                    <label>Estágio Inicial</label>
                    <select v-model="form.default_stage_id" class="form-select">
                      <option :value="null">Selecione...</option>
                      <option v-for="stage in stages" :key="stage.id" :value="stage.id">
                        {{ stage.name }}
                      </option>
                    </select>
                  </div>

                  <div class="form-group">
                    <label class="checkbox-label">
                      <input type="checkbox" v-model="form.require_qualification" />
                      <span>Requerer qualificação mínima</span>
                    </label>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Prompt Templates -->
          <div v-if="activeTab === 'templates'" class="settings-section">
            <div class="section-header">
              <h2>Templates de Prompt</h2>
              <p>Configure prompts personalizados para diferentes situações</p>
            </div>

            <div class="templates-list">
              <div v-for="template in templates" :key="template.id" class="template-card">
                <div class="template-header">
                  <div>
                    <h3>{{ template.name }}</h3>
                    <span class="template-category">{{ template.category }}</span>
                  </div>
                  <div class="template-actions">
                    <button @click="editTemplate(template)" class="btn-icon">
                      <i class="fa fa-edit"></i>
                    </button>
                    <button @click="deleteTemplate(template)" class="btn-icon">
                      <i class="fa fa-trash"></i>
                    </button>
                  </div>
                </div>
                <p class="template-description">{{ template.description }}</p>
                <div class="template-preview">
                  <code>{{ template.prompt.substring(0, 150) }}...</code>
                </div>
              </div>

              <button @click="showTemplateModal = true" class="add-template-btn">
                <i class="fa fa-plus"></i>
                <span>Novo Template</span>
              </button>
            </div>
          </div>

          <!-- Business Context -->
          <div v-if="activeTab === 'context'" class="settings-section">
            <div class="section-header">
              <h2>Contexto Empresarial</h2>
              <p>Informações sobre sua empresa que a IA deve conhecer</p>
            </div>

            <div class="form-grid">
              <div class="form-group full-width">
                <label>Sobre a Empresa</label>
                <textarea
                  v-model="form.company_context"
                  class="form-textarea"
                  rows="4"
                  placeholder="Descreva sua empresa, produtos, serviços..."
                ></textarea>
              </div>

              <div class="form-group full-width">
                <label>Tom de Voz</label>
                <select v-model="form.tone_of_voice" class="form-select">
                  <option value="professional">Profissional</option>
                  <option value="friendly">Amigável</option>
                  <option value="casual">Casual</option>
                  <option value="formal">Formal</option>
                </select>
              </div>

              <div class="form-group full-width">
                <label>Perguntas Frequentes</label>
                <div class="faq-list">
                  <div v-for="(faq, index) in form.faqs" :key="index" class="faq-item">
                    <Input
                      v-model="faq.question"
                      placeholder="Pergunta"
                      size="small"
                    />
                    <textarea
                      v-model="faq.answer"
                      class="faq-answer"
                      placeholder="Resposta..."
                      rows="2"
                    ></textarea>
                    <button @click="removeFaq(index)" class="btn-remove">
                      <i class="fa fa-times"></i>
                    </button>
                  </div>
                  <Button
                    label="Adicionar FAQ"
                    icon="fa fa-plus"
                    @click="addFaq"
                    outlined
                    size="small"
                  />
                </div>
              </div>

              <div class="form-group full-width">
                <label>Produtos/Serviços</label>
                <textarea
                  v-model="form.products_context"
                  class="form-textarea"
                  rows="4"
                  placeholder="Liste seus produtos e serviços principais..."
                ></textarea>
              </div>
            </div>
          </div>

          <!-- Limits & Usage -->
          <div v-if="activeTab === 'limits'" class="settings-section">
            <div class="section-header">
              <h2>Limites e Uso</h2>
              <p>Monitore o uso da API e configure limites</p>
            </div>

            <div class="usage-stats">
              <div class="stat-card">
                <div class="stat-icon">
                  <i class="fa fa-comments"></i>
                </div>
                <div class="stat-content">
                  <h3>{{ usage.total_requests }}</h3>
                  <p>Requisições no Mês</p>
                  <div class="stat-progress">
                    <div
                      class="progress-bar"
                      :style="{ width: (usage.total_requests / usage.monthly_limit * 100) + '%' }"
                    ></div>
                  </div>
                  <small>{{ usage.monthly_limit }} limite mensal</small>
                </div>
              </div>

              <div class="stat-card">
                <div class="stat-icon">
                  <i class="fa fa-coins"></i>
                </div>
                <div class="stat-content">
                  <h3>{{ usage.total_tokens }}</h3>
                  <p>Tokens Utilizados</p>
                  <small>~${{ usage.estimated_cost.toFixed(2) }} estimado</small>
                </div>
              </div>

              <div class="stat-card">
                <div class="stat-icon">
                  <i class="fa fa-clock"></i>
                </div>
                <div class="stat-content">
                  <h3>{{ usage.avg_response_time }}ms</h3>
                  <p>Tempo Médio de Resposta</p>
                </div>
              </div>

              <div class="stat-card">
                <div class="stat-icon">
                  <i class="fa fa-check-circle"></i>
                </div>
                <div class="stat-content">
                  <h3>{{ usage.success_rate }}%</h3>
                  <p>Taxa de Sucesso</p>
                </div>
              </div>
            </div>

            <div class="form-grid">
              <div class="form-group">
                <label>Limite Diário de Requisições</label>
                <Input
                  v-model.number="form.daily_limit"
                  type="number"
                  placeholder="1000"
                />
              </div>

              <div class="form-group">
                <label>Limite por Conversação/Hora</label>
                <Input
                  v-model.number="form.conversation_limit"
                  type="number"
                  placeholder="20"
                />
              </div>

              <div class="form-group full-width">
                <label class="checkbox-label">
                  <input type="checkbox" v-model="form.notify_on_limit" />
                  <span>Notificar ao atingir 80% do limite</span>
                </label>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Template Modal -->
    <Modal
      v-model:visible="showTemplateModal"
      title="Template de Prompt"
      size="large"
      @confirm="saveTemplate"
    >
      <div class="modal-form">
        <div class="form-group">
          <label class="required">Nome do Template</label>
          <Input v-model="templateForm.name" placeholder="Ex: Atendimento Inicial" />
        </div>

        <div class="form-group">
          <label>Categoria</label>
          <select v-model="templateForm.category" class="form-select">
            <option value="atendimento">Atendimento</option>
            <option value="vendas">Vendas</option>
            <option value="suporte">Suporte</option>
            <option value="qualificacao">Qualificação</option>
          </select>
        </div>

        <div class="form-group">
          <label>Descrição</label>
          <textarea
            v-model="templateForm.description"
            class="form-textarea"
            rows="2"
            placeholder="Descreva quando usar este template..."
          ></textarea>
        </div>

        <div class="form-group">
          <label class="required">Prompt</label>
          <textarea
            v-model="templateForm.prompt"
            class="form-textarea"
            rows="8"
            placeholder="Digite o prompt que a IA deve seguir..."
          ></textarea>
          <small class="help-text">
            Use {nome_cliente}, {empresa}, {produto} como variáveis
          </small>
        </div>
      </div>
    </Modal>
  </MainLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Button from '@/Components/Button.vue';
import Input from '@/Components/Input.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import Modal from '@/Components/Modal.vue';
import Alert from '@/Components/Alert.vue';

const props = defineProps({
  settings: Object,
  pipelines: Array,
  templates: Array,
  usage: Object,
});

const breadcrumbs = [
  { label: 'Dashboard', to: '/dashboard' },
  { label: 'IA', to: '/ai' },
  { label: 'Configurações', active: true }
];

const activeTab = ref('provider');
const showApiKey = ref(false);
const testing = ref(false);
const saving = ref(false);
const showTemplateModal = ref(false);

const alert = ref({
  show: false,
  type: 'success',
  message: ''
});

const form = useForm({
  provider: props.settings?.provider || 'gemini',
  api_key: props.settings?.api_key || '',
  model: props.settings?.model || 'gemini-1.5-pro',
  temperature: props.settings?.temperature || 0.7,
  max_tokens: props.settings?.max_tokens || 2048,
  top_p: props.settings?.top_p || 0.95,
  whatsapp_enabled: props.settings?.whatsapp_enabled ?? true,
  whatsapp_start_time: props.settings?.whatsapp_start_time || '08:00',
  whatsapp_end_time: props.settings?.whatsapp_end_time || '18:00',
  whatsapp_only_first_message: props.settings?.whatsapp_only_first_message ?? false,
  whatsapp_delay: props.settings?.whatsapp_delay || 5,
  instagram_enabled: props.settings?.instagram_enabled ?? true,
  instagram_reply_stories: props.settings?.instagram_reply_stories ?? true,
  instagram_reply_mentions: props.settings?.instagram_reply_mentions ?? true,
  instagram_delay: props.settings?.instagram_delay || 3,
  auto_create_leads: props.settings?.auto_create_leads ?? true,
  default_pipeline_id: props.settings?.default_pipeline_id || null,
  default_stage_id: props.settings?.default_stage_id || null,
  require_qualification: props.settings?.require_qualification ?? false,
  company_context: props.settings?.company_context || '',
  tone_of_voice: props.settings?.tone_of_voice || 'professional',
  faqs: props.settings?.faqs || [],
  products_context: props.settings?.products_context || '',
  daily_limit: props.settings?.daily_limit || 1000,
  conversation_limit: props.settings?.conversation_limit || 20,
  notify_on_limit: props.settings?.notify_on_limit ?? true,
});

const templateForm = ref({
  name: '',
  category: 'atendimento',
  description: '',
  prompt: '',
});

const stages = computed(() => {
  if (!form.default_pipeline_id) return [];
  const pipeline = props.pipelines.find(p => p.id === form.default_pipeline_id);
  return pipeline?.stages || [];
});

const testConnection = async () => {
  testing.value = true;

  try {
    const response = await fetch('/api/ai/test-connection', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        provider: form.provider,
        api_key: form.api_key,
        model: form.model,
      }),
    });

    const data = await response.json();

    if (data.success) {
      alert.value = {
        show: true,
        type: 'success',
        message: 'Conexão testada com sucesso! IA respondeu: ' + data.response
      };
    } else {
      alert.value = {
        show: true,
        type: 'error',
        message: 'Erro ao testar conexão: ' + data.error
      };
    }
  } catch (error) {
    alert.value = {
      show: true,
      type: 'error',
      message: 'Erro ao testar conexão: ' + error.message
    };
  } finally {
    testing.value = false;
  }
};

const saveSettings = () => {
  saving.value = true;

  form.post('/ai/settings', {
    onSuccess: () => {
      alert.value = {
        show: true,
        type: 'success',
        message: 'Configurações salvas com sucesso!'
      };
    },
    onError: () => {
      alert.value = {
        show: true,
        type: 'error',
        message: 'Erro ao salvar configurações'
      };
    },
    onFinish: () => {
      saving.value = false;
    },
  });
};

const addFaq = () => {
  form.faqs.push({ question: '', answer: '' });
};

const removeFaq = (index) => {
  form.faqs.splice(index, 1);
};

const editTemplate = (template) => {
  templateForm.value = { ...template };
  showTemplateModal.value = true;
};

const deleteTemplate = (template) => {
  if (confirm('Deseja realmente excluir este template?')) {
    router.delete(`/ai/templates/${template.id}`);
  }
};

const saveTemplate = () => {
  router.post('/ai/templates', templateForm.value, {
    onSuccess: () => {
      showTemplateModal.value = false;
      templateForm.value = { name: '', category: 'atendimento', description: '', prompt: '' };
    },
  });
};
</script>

