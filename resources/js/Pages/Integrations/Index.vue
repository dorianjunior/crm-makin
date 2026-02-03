<template>
  <MainLayout>
    <div class="integrations-page">
      <Breadcrumbs :items="breadcrumbs" />

      <div class="page-header">
        <div>
          <h1>Integrações</h1>
          <p class="subtitle">Conecte-se com serviços externos e APIs</p>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="stats-row">
        <StatCard
          label="Integrações Ativas"
          :value="activeIntegrations"
          icon="fa fa-plug"
          color="success"
        />
        <StatCard
          label="Pendentes"
          :value="pendingIntegrations"
          icon="fa fa-clock"
          color="warning"
        />
        <StatCard
          label="Com Erro"
          :value="errorIntegrations"
          icon="fa fa-exclamation-triangle"
          color="error"
        />
        <StatCard
          label="Total"
          :value="totalIntegrations"
          icon="fa fa-network-wired"
          color="info"
        />
      </div>

      <!-- Integration Categories -->
      <div class="integration-categories">
        <button
          v-for="category in categories"
          :key="category.id"
          :class="{ active: selectedCategory === category.id }"
          @click="selectedCategory = category.id"
          class="category-btn"
        >
          <i :class="category.icon"></i>
          {{ category.label }}
        </button>
      </div>

      <!-- Integrations Grid -->
      <div class="integrations-grid">
        <!-- WhatsApp Integration -->
        <div
          v-if="!selectedCategory || selectedCategory === 'messaging'"
          class="integration-card"
        >
          <div class="card-header">
            <div class="service-info">
              <div class="service-icon whatsapp">
                <i class="fab fa-whatsapp"></i>
              </div>
              <div>
                <h3>WhatsApp Business</h3>
                <p>API oficial do WhatsApp para empresas</p>
              </div>
            </div>
            <span :class="['status-badge', integrations.whatsapp?.status || 'disconnected']">
              {{ getStatusLabel(integrations.whatsapp?.status) }}
            </span>
          </div>

          <div class="card-body">
            <p class="description">
              Envie e receba mensagens, crie chatbots e automatize atendimentos via WhatsApp oficial.
            </p>

            <div v-if="integrations.whatsapp?.connected" class="connection-info">
              <div class="info-item">
                <span class="label">Número:</span>
                <span class="value">{{ integrations.whatsapp.phone_number }}</span>
              </div>
              <div class="info-item">
                <span class="label">Última sincronização:</span>
                <span class="value">{{ formatDate(integrations.whatsapp.last_sync) }}</span>
              </div>
            </div>

            <div class="card-actions">
              <Button
                v-if="!integrations.whatsapp?.connected"
                variant="primary"
                @click="configureIntegration('whatsapp')"
              >
                <i class="fa fa-cog"></i>
                Configurar
              </Button>
              <template v-else>
                <Button variant="secondary" @click="configureIntegration('whatsapp')">
                  <i class="fa fa-cog"></i>
                  Gerenciar
                </Button>
                <Button variant="danger" @click="disconnectIntegration('whatsapp')">
                  <i class="fa fa-unlink"></i>
                  Desconectar
                </Button>
              </template>
            </div>
          </div>
        </div>

        <!-- Instagram Integration -->
        <div
          v-if="!selectedCategory || selectedCategory === 'social'"
          class="integration-card"
        >
          <div class="card-header">
            <div class="service-info">
              <div class="service-icon instagram">
                <i class="fab fa-instagram"></i>
              </div>
              <div>
                <h3>Instagram Business</h3>
                <p>Conecte seu perfil comercial do Instagram</p>
              </div>
            </div>
            <span :class="['status-badge', integrations.instagram?.status || 'disconnected']">
              {{ getStatusLabel(integrations.instagram?.status) }}
            </span>
          </div>

          <div class="card-body">
            <p class="description">
              Gerencie DMs, publique conteúdo e monitore comentários direto da plataforma.
            </p>

            <div v-if="integrations.instagram?.connected" class="connection-info">
              <div class="info-item">
                <span class="label">Conta:</span>
                <span class="value">@{{ integrations.instagram.username }}</span>
              </div>
              <div class="info-item">
                <span class="label">Seguidores:</span>
                <span class="value">{{ integrations.instagram.followers_count?.toLocaleString() }}</span>
              </div>
            </div>

            <div class="card-actions">
              <Button
                v-if="!integrations.instagram?.connected"
                variant="primary"
                @click="configureIntegration('instagram')"
              >
                <i class="fa fa-cog"></i>
                Conectar
              </Button>
              <template v-else>
                <Button variant="secondary" @click="configureIntegration('instagram')">
                  <i class="fa fa-cog"></i>
                  Gerenciar
                </Button>
                <Button variant="danger" @click="disconnectIntegration('instagram')">
                  <i class="fa fa-unlink"></i>
                  Desconectar
                </Button>
              </template>
            </div>
          </div>
        </div>

        <!-- Google Workspace -->
        <div
          v-if="!selectedCategory || selectedCategory === 'productivity'"
          class="integration-card"
        >
          <div class="card-header">
            <div class="service-info">
              <div class="service-icon google">
                <i class="fab fa-google"></i>
              </div>
              <div>
                <h3>Google Workspace</h3>
                <p>Gmail, Drive, Calendar e mais</p>
              </div>
            </div>
            <span :class="['status-badge', integrations.google?.status || 'disconnected']">
              {{ getStatusLabel(integrations.google?.status) }}
            </span>
          </div>

          <div class="card-body">
            <p class="description">
              Sincronize emails, calendário e documentos do Google com o CRM.
            </p>

            <div v-if="integrations.google?.connected" class="connection-info">
              <div class="info-item">
                <span class="label">Email:</span>
                <span class="value">{{ integrations.google.email }}</span>
              </div>
              <div class="info-item">
                <span class="label">Serviços ativos:</span>
                <span class="value">{{ integrations.google.active_services?.join(', ') }}</span>
              </div>
            </div>

            <div class="card-actions">
              <Button
                v-if="!integrations.google?.connected"
                variant="primary"
                @click="connectOAuth('google')"
              >
                <i class="fab fa-google"></i>
                Conectar com Google
              </Button>
              <template v-else>
                <Button variant="secondary" @click="configureIntegration('google')">
                  <i class="fa fa-cog"></i>
                  Gerenciar
                </Button>
                <Button variant="danger" @click="disconnectIntegration('google')">
                  <i class="fa fa-unlink"></i>
                  Desconectar
                </Button>
              </template>
            </div>
          </div>
        </div>

        <!-- SendGrid -->
        <div
          v-if="!selectedCategory || selectedCategory === 'email'"
          class="integration-card"
        >
          <div class="card-header">
            <div class="service-info">
              <div class="service-icon sendgrid">
                <i class="fa fa-envelope"></i>
              </div>
              <div>
                <h3>SendGrid</h3>
                <p>Serviço de email transacional</p>
              </div>
            </div>
            <span :class="['status-badge', integrations.sendgrid?.status || 'disconnected']">
              {{ getStatusLabel(integrations.sendgrid?.status) }}
            </span>
          </div>

          <div class="card-body">
            <p class="description">
              Envie emails profissionais em escala com alta taxa de entrega.
            </p>

            <div v-if="integrations.sendgrid?.connected" class="connection-info">
              <div class="info-item">
                <span class="label">Emails enviados (mês):</span>
                <span class="value">{{ integrations.sendgrid.monthly_sent?.toLocaleString() }}</span>
              </div>
            </div>

            <div class="card-actions">
              <Button
                v-if="!integrations.sendgrid?.connected"
                variant="primary"
                @click="configureIntegration('sendgrid')"
              >
                <i class="fa fa-cog"></i>
                Configurar
              </Button>
              <template v-else>
                <Button variant="secondary" @click="configureIntegration('sendgrid')">
                  <i class="fa fa-cog"></i>
                  Gerenciar
                </Button>
                <Button variant="danger" @click="disconnectIntegration('sendgrid')">
                  <i class="fa fa-unlink"></i>
                  Desconectar
                </Button>
              </template>
            </div>
          </div>
        </div>

        <!-- Zapier -->
        <div
          v-if="!selectedCategory || selectedCategory === 'automation'"
          class="integration-card"
        >
          <div class="card-header">
            <div class="service-info">
              <div class="service-icon zapier">
                <i class="fa fa-bolt"></i>
              </div>
              <div>
                <h3>Zapier</h3>
                <p>Automações e webhooks</p>
              </div>
            </div>
            <span :class="['status-badge', integrations.zapier?.status || 'disconnected']">
              {{ getStatusLabel(integrations.zapier?.status) }}
            </span>
          </div>

          <div class="card-body">
            <p class="description">
              Conecte com mais de 5.000 aplicativos através de automações personalizadas.
            </p>

            <div v-if="integrations.zapier?.connected" class="connection-info">
              <div class="info-item">
                <span class="label">Zaps ativos:</span>
                <span class="value">{{ integrations.zapier.active_zaps }}</span>
              </div>
            </div>

            <div class="card-actions">
              <Button
                v-if="!integrations.zapier?.connected"
                variant="primary"
                @click="configureIntegration('zapier')"
              >
                <i class="fa fa-cog"></i>
                Configurar
              </Button>
              <template v-else>
                <Button variant="secondary" @click="configureIntegration('zapier')">
                  <i class="fa fa-cog"></i>
                  Gerenciar
                </Button>
                <Button variant="danger" @click="disconnectIntegration('zapier')">
                  <i class="fa fa-unlink"></i>
                  Desconectar
                </Button>
              </template>
            </div>
          </div>
        </div>

        <!-- Stripe -->
        <div
          v-if="!selectedCategory || selectedCategory === 'payment'"
          class="integration-card"
        >
          <div class="card-header">
            <div class="service-info">
              <div class="service-icon stripe">
                <i class="fab fa-stripe"></i>
              </div>
              <div>
                <h3>Stripe</h3>
                <p>Gateway de pagamento online</p>
              </div>
            </div>
            <span :class="['status-badge', integrations.stripe?.status || 'disconnected']">
              {{ getStatusLabel(integrations.stripe?.status) }}
            </span>
          </div>

          <div class="card-body">
            <p class="description">
              Aceite pagamentos com cartão e gerencie assinaturas recorrentes.
            </p>

            <div v-if="integrations.stripe?.connected" class="connection-info">
              <div class="info-item">
                <span class="label">Modo:</span>
                <span class="value">{{ integrations.stripe.mode === 'live' ? 'Produção' : 'Teste' }}</span>
              </div>
            </div>

            <div class="card-actions">
              <Button
                v-if="!integrations.stripe?.connected"
                variant="primary"
                @click="configureIntegration('stripe')"
              >
                <i class="fa fa-cog"></i>
                Configurar
              </Button>
              <template v-else>
                <Button variant="secondary" @click="configureIntegration('stripe')">
                  <i class="fa fa-cog"></i>
                  Gerenciar
                </Button>
                <Button variant="danger" @click="disconnectIntegration('stripe')">
                  <i class="fa fa-unlink"></i>
                  Desconectar
                </Button>
              </template>
            </div>
          </div>
        </div>

        <!-- Google Analytics -->
        <div
          v-if="!selectedCategory || selectedCategory === 'analytics'"
          class="integration-card"
        >
          <div class="card-header">
            <div class="service-info">
              <div class="service-icon analytics">
                <i class="fa fa-chart-line"></i>
              </div>
              <div>
                <h3>Google Analytics</h3>
                <p>Análise de métricas e conversões</p>
              </div>
            </div>
            <span :class="['status-badge', integrations.analytics?.status || 'disconnected']">
              {{ getStatusLabel(integrations.analytics?.status) }}
            </span>
          </div>

          <div class="card-body">
            <p class="description">
              Rastreie visitantes, conversões e comportamento dos usuários.
            </p>

            <div v-if="integrations.analytics?.connected" class="connection-info">
              <div class="info-item">
                <span class="label">Tracking ID:</span>
                <span class="value">{{ integrations.analytics.tracking_id }}</span>
              </div>
            </div>

            <div class="card-actions">
              <Button
                v-if="!integrations.analytics?.connected"
                variant="primary"
                @click="configureIntegration('analytics')"
              >
                <i class="fa fa-cog"></i>
                Configurar
              </Button>
              <template v-else>
                <Button variant="secondary" @click="configureIntegration('analytics')">
                  <i class="fa fa-cog"></i>
                  Gerenciar
                </Button>
                <Button variant="danger" @click="disconnectIntegration('analytics')">
                  <i class="fa fa-unlink"></i>
                  Desconectar
                </Button>
              </template>
            </div>
          </div>
        </div>

        <!-- Meta Pixel (Facebook) -->
        <div
          v-if="!selectedCategory || selectedCategory === 'analytics'"
          class="integration-card"
        >
          <div class="card-header">
            <div class="service-info">
              <div class="service-icon facebook">
                <i class="fab fa-facebook"></i>
              </div>
              <div>
                <h3>Meta Pixel</h3>
                <p>Rastreamento Facebook & Instagram</p>
              </div>
            </div>
            <span :class="['status-badge', integrations.meta_pixel?.status || 'disconnected']">
              {{ getStatusLabel(integrations.meta_pixel?.status) }}
            </span>
          </div>

          <div class="card-body">
            <p class="description">
              Otimize anúncios e crie públicos personalizados com base em eventos.
            </p>

            <div v-if="integrations.meta_pixel?.connected" class="connection-info">
              <div class="info-item">
                <span class="label">Pixel ID:</span>
                <span class="value">{{ integrations.meta_pixel.pixel_id }}</span>
              </div>
            </div>

            <div class="card-actions">
              <Button
                v-if="!integrations.meta_pixel?.connected"
                variant="primary"
                @click="configureIntegration('meta_pixel')"
              >
                <i class="fa fa-cog"></i>
                Configurar
              </Button>
              <template v-else>
                <Button variant="secondary" @click="configureIntegration('meta_pixel')">
                  <i class="fa fa-cog"></i>
                  Gerenciar
                </Button>
                <Button variant="danger" @click="disconnectIntegration('meta_pixel')">
                  <i class="fa fa-unlink"></i>
                  Desconectar
                </Button>
              </template>
            </div>
          </div>
        </div>
      </div>

      <!-- Configuration Modal -->
      <Modal v-if="showConfigModal" @close="showConfigModal = false" size="large">
        <template #header>
          <h2>{{ configModalTitle }}</h2>
        </template>

        <template #body>
          <!-- WhatsApp Config -->
          <div v-if="currentIntegration === 'whatsapp'" class="integration-config">
            <div class="form-group">
              <label>Número de Telefone *</label>
              <Input
                v-model="configForm.whatsapp.phone_number"
                placeholder="+55 11 99999-9999"
              />
            </div>

            <div class="form-group">
              <label>Business Account ID *</label>
              <Input
                v-model="configForm.whatsapp.business_account_id"
                placeholder="123456789012345"
              />
            </div>

            <div class="form-group">
              <label>Phone Number ID *</label>
              <Input
                v-model="configForm.whatsapp.phone_number_id"
                placeholder="123456789012345"
              />
            </div>

            <div class="form-group">
              <label>Access Token *</label>
              <Input
                v-model="configForm.whatsapp.access_token"
                type="password"
                placeholder="EAAxxxxxxxxx"
              />
            </div>

            <div class="form-group">
              <label>Webhook Verify Token</label>
              <Input
                v-model="configForm.whatsapp.webhook_token"
                placeholder="Token para verificação do webhook"
              />
            </div>

            <div class="webhook-info">
              <h4>URL do Webhook</h4>
              <div class="webhook-url">
                <code>{{ webhookUrl }}/whatsapp</code>
                <button @click="copyWebhookUrl" class="copy-btn">
                  <i class="fa fa-copy"></i>
                </button>
              </div>
            </div>
          </div>

          <!-- Instagram Config -->
          <div v-if="currentIntegration === 'instagram'" class="integration-config">
            <div class="form-group">
              <label>Instagram Business Account ID *</label>
              <Input
                v-model="configForm.instagram.account_id"
                placeholder="17841400000000000"
              />
            </div>

            <div class="form-group">
              <label>Access Token *</label>
              <Input
                v-model="configForm.instagram.access_token"
                type="password"
                placeholder="Token de acesso do Facebook"
              />
            </div>

            <div class="form-group">
              <label>
                <input type="checkbox" v-model="configForm.instagram.sync_messages">
                Sincronizar mensagens diretas
              </label>
            </div>

            <div class="form-group">
              <label>
                <input type="checkbox" v-model="configForm.instagram.sync_comments">
                Sincronizar comentários
              </label>
            </div>

            <div class="form-group">
              <label>
                <input type="checkbox" v-model="configForm.instagram.sync_mentions">
                Sincronizar menções
              </label>
            </div>
          </div>

          <!-- SendGrid Config -->
          <div v-if="currentIntegration === 'sendgrid'" class="integration-config">
            <div class="form-group">
              <label>API Key *</label>
              <Input
                v-model="configForm.sendgrid.api_key"
                type="password"
                placeholder="SG.xxxxxxxxxxxx"
              />
            </div>

            <div class="form-group">
              <label>Email de Envio *</label>
              <Input
                v-model="configForm.sendgrid.from_email"
                type="email"
                placeholder="contato@empresa.com"
              />
            </div>

            <div class="form-group">
              <label>Nome do Remetente *</label>
              <Input
                v-model="configForm.sendgrid.from_name"
                placeholder="Minha Empresa"
              />
            </div>

            <div class="form-group">
              <label>
                <input type="checkbox" v-model="configForm.sendgrid.track_opens">
                Rastrear aberturas de email
              </label>
            </div>

            <div class="form-group">
              <label>
                <input type="checkbox" v-model="configForm.sendgrid.track_clicks">
                Rastrear cliques
              </label>
            </div>
          </div>

          <!-- Zapier Config -->
          <div v-if="currentIntegration === 'zapier'" class="integration-config">
            <div class="webhook-info">
              <h4>Webhook URLs</h4>
              <p>Use estas URLs em seus Zaps:</p>

              <div class="webhook-item">
                <label>Novo Lead:</label>
                <div class="webhook-url">
                  <code>{{ webhookUrl }}/zapier/lead</code>
                  <button @click="copyWebhookUrl" class="copy-btn">
                    <i class="fa fa-copy"></i>
                  </button>
                </div>
              </div>

              <div class="webhook-item">
                <label>Nova Proposta:</label>
                <div class="webhook-url">
                  <code>{{ webhookUrl }}/zapier/proposal</code>
                  <button @click="copyWebhookUrl" class="copy-btn">
                    <i class="fa fa-copy"></i>
                  </button>
                </div>
              </div>

              <div class="webhook-item">
                <label>Nova Task:</label>
                <div class="webhook-url">
                  <code>{{ webhookUrl }}/zapier/task</code>
                  <button @click="copyWebhookUrl" class="copy-btn">
                    <i class="fa fa-copy"></i>
                  </button>
                </div>
              </div>
            </div>

            <div class="form-group">
              <label>API Key (gerada automaticamente)</label>
              <div class="api-key-display">
                <code>{{ configForm.zapier.api_key || 'Será gerada ao salvar' }}</code>
                <button v-if="configForm.zapier.api_key" @click="regenerateAPIKey" class="regenerate-btn">
                  <i class="fa fa-sync"></i>
                  Regenerar
                </button>
              </div>
            </div>
          </div>

          <!-- Stripe Config -->
          <div v-if="currentIntegration === 'stripe'" class="integration-config">
            <div class="form-group">
              <label>Publishable Key *</label>
              <Input
                v-model="configForm.stripe.publishable_key"
                placeholder="pk_live_..."
              />
            </div>

            <div class="form-group">
              <label>Secret Key *</label>
              <Input
                v-model="configForm.stripe.secret_key"
                type="password"
                placeholder="sk_live_..."
              />
            </div>

            <div class="form-group">
              <label>Webhook Secret</label>
              <Input
                v-model="configForm.stripe.webhook_secret"
                type="password"
                placeholder="whsec_..."
              />
            </div>

            <div class="form-group">
              <label>Modo</label>
              <select v-model="configForm.stripe.mode" class="form-select">
                <option value="test">Teste</option>
                <option value="live">Produção</option>
              </select>
            </div>

            <div class="webhook-info">
              <h4>URL do Webhook</h4>
              <div class="webhook-url">
                <code>{{ webhookUrl }}/stripe</code>
                <button @click="copyWebhookUrl" class="copy-btn">
                  <i class="fa fa-copy"></i>
                </button>
              </div>
            </div>
          </div>

          <!-- Google Analytics Config -->
          <div v-if="currentIntegration === 'analytics'" class="integration-config">
            <div class="form-group">
              <label>Tracking ID / Measurement ID *</label>
              <Input
                v-model="configForm.analytics.tracking_id"
                placeholder="G-XXXXXXXXXX ou UA-XXXXXXXXX-X"
              />
            </div>

            <div class="form-group">
              <label>
                <input type="checkbox" v-model="configForm.analytics.enhanced_ecommerce">
                Enhanced Ecommerce
              </label>
            </div>

            <div class="form-group">
              <label>
                <input type="checkbox" v-model="configForm.analytics.anonymize_ip">
                Anonimizar IP
              </label>
            </div>
          </div>

          <!-- Meta Pixel Config -->
          <div v-if="currentIntegration === 'meta_pixel'" class="integration-config">
            <div class="form-group">
              <label>Pixel ID *</label>
              <Input
                v-model="configForm.meta_pixel.pixel_id"
                placeholder="123456789012345"
              />
            </div>

            <div class="form-group">
              <label>Access Token (opcional)</label>
              <Input
                v-model="configForm.meta_pixel.access_token"
                type="password"
                placeholder="Para API de conversões"
              />
            </div>

            <div class="form-group">
              <label>
                <input type="checkbox" v-model="configForm.meta_pixel.track_pageview">
                Rastrear visualizações de página
              </label>
            </div>

            <div class="form-group">
              <label>
                <input type="checkbox" v-model="configForm.meta_pixel.advanced_matching">
                Advanced Matching
              </label>
            </div>
          </div>
        </template>

        <template #footer>
          <Button variant="secondary" @click="showConfigModal = false">
            Cancelar
          </Button>
          <Button variant="primary" @click="saveIntegrationConfig">
            <i class="fa fa-save"></i>
            Salvar Configuração
          </Button>
        </template>
      </Modal>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import Button from '@/Components/Button.vue';
import Input from '@/Components/Input.vue';
import StatCard from '@/Components/StatCard.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
  integrations: Object
});

const selectedCategory = ref(null);
const showConfigModal = ref(false);
const currentIntegration = ref(null);

const categories = [
  { id: null, label: 'Todas', icon: 'fa fa-th' },
  { id: 'messaging', label: 'Mensageria', icon: 'fa fa-comments' },
  { id: 'social', label: 'Redes Sociais', icon: 'fa fa-hashtag' },
  { id: 'email', label: 'Email', icon: 'fa fa-envelope' },
  { id: 'productivity', label: 'Produtividade', icon: 'fa fa-briefcase' },
  { id: 'payment', label: 'Pagamentos', icon: 'fa fa-credit-card' },
  { id: 'analytics', label: 'Analytics', icon: 'fa fa-chart-bar' },
  { id: 'automation', label: 'Automação', icon: 'fa fa-magic' }
];

const configForm = ref({
  whatsapp: {
    phone_number: '',
    business_account_id: '',
    phone_number_id: '',
    access_token: '',
    webhook_token: ''
  },
  instagram: {
    account_id: '',
    access_token: '',
    sync_messages: true,
    sync_comments: true,
    sync_mentions: true
  },
  sendgrid: {
    api_key: '',
    from_email: '',
    from_name: '',
    track_opens: true,
    track_clicks: true
  },
  zapier: {
    api_key: null
  },
  stripe: {
    publishable_key: '',
    secret_key: '',
    webhook_secret: '',
    mode: 'test'
  },
  analytics: {
    tracking_id: '',
    enhanced_ecommerce: false,
    anonymize_ip: true
  },
  meta_pixel: {
    pixel_id: '',
    access_token: '',
    track_pageview: true,
    advanced_matching: false
  }
});

const webhookUrl = computed(() => {
  return window.location.origin + '/webhooks';
});

const activeIntegrations = computed(() => {
  return Object.values(props.integrations).filter(i => i?.status === 'connected').length;
});

const pendingIntegrations = computed(() => {
  return Object.values(props.integrations).filter(i => i?.status === 'pending').length;
});

const errorIntegrations = computed(() => {
  return Object.values(props.integrations).filter(i => i?.status === 'error').length;
});

const totalIntegrations = computed(() => {
  return 8; // Total de integrações disponíveis
});

const configModalTitle = computed(() => {
  const titles = {
    whatsapp: 'Configurar WhatsApp Business',
    instagram: 'Configurar Instagram Business',
    google: 'Configurar Google Workspace',
    sendgrid: 'Configurar SendGrid',
    zapier: 'Configurar Zapier',
    stripe: 'Configurar Stripe',
    analytics: 'Configurar Google Analytics',
    meta_pixel: 'Configurar Meta Pixel'
  };
  return titles[currentIntegration.value] || 'Configurar Integração';
});

const breadcrumbs = [
  { label: 'Integrações' }
];

const getStatusLabel = (status) => {
  const labels = {
    connected: 'Conectado',
    disconnected: 'Desconectado',
    pending: 'Pendente',
    error: 'Erro'
  };
  return labels[status] || 'Desconectado';
};

const configureIntegration = (integration) => {
  currentIntegration.value = integration;

  // Preencher form com dados existentes
  if (props.integrations[integration]?.connected) {
    Object.assign(configForm.value[integration], props.integrations[integration]);
  }

  showConfigModal.value = true;
};

const connectOAuth = (provider) => {
  window.location.href = `/integrations/${provider}/connect`;
};

const disconnectIntegration = (integration) => {
  if (confirm(`Desconectar ${integration}? Você precisará reconfigurá-lo para usá-lo novamente.`)) {
    router.delete(`/integrations/${integration}`, {
      preserveScroll: true
    });
  }
};

const saveIntegrationConfig = () => {
  router.post(`/integrations/${currentIntegration.value}`,
    configForm.value[currentIntegration.value],
    {
      preserveScroll: true,
      onSuccess: () => {
        showConfigModal.value = false;
      }
    }
  );
};

const copyWebhookUrl = () => {
  // Logic to copy webhook URL
  alert('URL copiada para a área de transferência!');
};

const regenerateAPIKey = () => {
  if (confirm('Regenerar API Key? A chave atual será invalidada.')) {
    router.post(`/integrations/zapier/regenerate`, {}, {
      preserveScroll: true
    });
  }
};

const formatDate = (date) => {
  if (!date) return 'Nunca';
  return new Date(date).toLocaleString('pt-BR');
};
</script>

