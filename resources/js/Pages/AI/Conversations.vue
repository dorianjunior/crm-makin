<template>
  <MainLayout>
    <div class="ai-conversations">
      <!-- Header -->
      <div class="page-header">
        <div>
          <h1 class="page-title">
            <i class="fa fa-robot"></i>
            Conversas com IA
          </h1>
          <Breadcrumbs :items="breadcrumbs" />
        </div>
        <div class="header-actions">
          <select v-model="dateRange" class="date-select">
            <option value="today">Hoje</option>
            <option value="week">Esta Semana</option>
            <option value="month">Este Mês</option>
            <option value="all">Todas</option>
          </select>
          <Button
            label="Configurações IA"
            icon="fa fa-cog"
            @click="router.visit('/ai/settings')"
            outlined
          />
        </div>
      </div>

      <!-- Stats -->
      <div class="stats-grid">
        <StatCard
          icon="fa fa-comments"
          label="Total de Conversas"
          :value="stats.total_conversations"
          color="purple"
        >
          <template #footer>
            <span class="stat-detail">
              <i class="fa fa-robot"></i>
              {{ stats.automated_responses }} respostas automáticas
            </span>
          </template>
        </StatCard>

        <StatCard
          icon="fa fa-user-plus"
          label="Leads Qualificados"
          :value="stats.leads_qualified"
          :trend="stats.qualification_trend"
          color="green"
        >
          <template #footer>
            <span class="stat-detail">
              {{ stats.qualification_rate }}% taxa de qualificação
            </span>
          </template>
        </StatCard>

        <StatCard
          icon="fa fa-clock"
          label="Tempo Médio"
          :value="stats.avg_response_time + 's'"
          color="blue"
        >
          <template #footer>
            <span class="stat-detail">
              Tempo de resposta da IA
            </span>
          </template>
        </StatCard>

        <StatCard
          icon="fa fa-thumbs-up"
          label="Satisfação"
          :value="stats.satisfaction_score + '%'"
          :trend="stats.satisfaction_trend"
          color="orange"
        >
          <template #footer>
            <span class="stat-detail">
              Baseado em {{ stats.feedback_count }} avaliações
            </span>
          </template>
        </StatCard>
      </div>

      <!-- Filters and Search -->
      <div class="filters-bar">
        <Input
          v-model="searchQuery"
          placeholder="Buscar conversas..."
          icon="fa fa-search"
        />

        <div class="filter-buttons">
          <button
            :class="['filter-btn', { active: filter === 'all' }]"
            @click="filter = 'all'"
          >
            Todas
          </button>
          <button
            :class="['filter-btn', { active: filter === 'qualified' }]"
            @click="filter = 'qualified'"
          >
            <i class="fa fa-check-circle"></i>
            Qualificadas
          </button>
          <button
            :class="['filter-btn', { active: filter === 'unresolved' }]"
            @click="filter = 'unresolved'"
          >
            <i class="fa fa-question-circle"></i>
            Não Resolvidas
          </button>
          <button
            :class="['filter-btn', { active: filter === 'whatsapp' }]"
            @click="filter = 'whatsapp'"
          >
            <i class="fab fa-whatsapp"></i>
            WhatsApp
          </button>
          <button
            :class="['filter-btn', { active: filter === 'instagram' }]"
            @click="filter = 'instagram'"
          >
            <i class="fab fa-instagram"></i>
            Instagram
          </button>
        </div>
      </div>

      <!-- Conversations List -->
      <div class="conversations-grid">
        <div
          v-for="conversation in filteredConversations"
          :key="conversation.id"
          class="conversation-card"
          @click="showConversationDetails(conversation)"
        >
          <div class="card-header">
            <div class="contact-info">
              <div class="contact-avatar">
                <img v-if="conversation.contact_avatar" :src="conversation.contact_avatar" :alt="conversation.contact_name" />
                <div v-else class="avatar-placeholder">
                  {{ getInitials(conversation.contact_name) }}
                </div>
              </div>
              <div>
                <h3>{{ conversation.contact_name }}</h3>
                <p class="contact-meta">
                  <i :class="getChannelIcon(conversation.channel)"></i>
                  {{ conversation.channel }}
                  · {{ formatDate(conversation.started_at) }}
                </p>
              </div>
            </div>

            <div class="card-badges">
              <span v-if="conversation.lead_created" class="badge success">
                <i class="fa fa-user-plus"></i>
                Lead Criado
              </span>
              <span v-else-if="conversation.is_qualified" class="badge info">
                <i class="fa fa-check"></i>
                Qualificado
              </span>
              <span v-if="!conversation.is_resolved" class="badge warning">
                <i class="fa fa-clock"></i>
                Pendente
              </span>
            </div>
          </div>

          <div class="card-content">
            <div class="conversation-stats">
              <div class="stat">
                <i class="fa fa-comments"></i>
                <span>{{ conversation.messages_count }} mensagens</span>
              </div>
              <div class="stat">
                <i class="fa fa-robot"></i>
                <span>{{ conversation.ai_responses_count }} IA</span>
              </div>
              <div class="stat">
                <i class="fa fa-clock"></i>
                <span>{{ conversation.duration }}</span>
              </div>
            </div>

            <div class="conversation-preview">
              <strong>Último resumo da IA:</strong>
              <p>{{ conversation.ai_summary || 'Sem resumo disponível' }}</p>
            </div>

            <div v-if="conversation.extracted_info" class="extracted-info">
              <strong>Informações Extraídas:</strong>
              <div class="info-tags">
                <span v-if="conversation.extracted_info.name" class="info-tag">
                  <i class="fa fa-user"></i>
                  {{ conversation.extracted_info.name }}
                </span>
                <span v-if="conversation.extracted_info.email" class="info-tag">
                  <i class="fa fa-envelope"></i>
                  {{ conversation.extracted_info.email }}
                </span>
                <span v-if="conversation.extracted_info.phone" class="info-tag">
                  <i class="fa fa-phone"></i>
                  {{ conversation.extracted_info.phone }}
                </span>
                <span v-if="conversation.extracted_info.interest" class="info-tag">
                  <i class="fa fa-tag"></i>
                  {{ conversation.extracted_info.interest }}
                </span>
              </div>
            </div>

            <div v-if="conversation.sentiment" class="sentiment-analysis">
              <strong>Análise de Sentimento:</strong>
              <div class="sentiment-bar">
                <div
                  class="sentiment-fill"
                  :class="conversation.sentiment.type"
                  :style="{ width: conversation.sentiment.score + '%' }"
                ></div>
              </div>
              <span class="sentiment-label">{{ conversation.sentiment.label }}</span>
            </div>
          </div>

          <div class="card-footer">
            <Button
              v-if="!conversation.lead_created && conversation.is_qualified"
              label="Converter em Lead"
              icon="fa fa-user-plus"
              @click.stop="convertToLead(conversation)"
              size="small"
            />
            <Button
              v-if="conversation.lead_id"
              label="Ver Lead"
              icon="fa fa-external-link-alt"
              @click.stop="router.visit(`/leads/${conversation.lead_id}`)"
              size="small"
              outlined
            />
            <Button
              label="Ver Conversa"
              icon="fa fa-eye"
              @click.stop="showConversationDetails(conversation)"
              size="small"
              outlined
            />
          </div>
        </div>

        <div v-if="filteredConversations.length === 0" class="empty-state">
          <i class="fa fa-robot"></i>
          <h3>Nenhuma conversa encontrada</h3>
          <p>As conversas automatizadas pela IA aparecerão aqui</p>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="pagination.total > pagination.per_page" class="pagination">
        <Button
          label="Anterior"
          icon="fa fa-chevron-left"
          @click="changePage(pagination.current_page - 1)"
          :disabled="pagination.current_page === 1"
          outlined
        />
        <span class="page-info">
          Página {{ pagination.current_page }} de {{ pagination.last_page }}
        </span>
        <Button
          label="Próxima"
          icon="fa fa-chevron-right"
          @click="changePage(pagination.current_page + 1)"
          :disabled="pagination.current_page === pagination.last_page"
          outlined
        />
      </div>
    </div>

    <!-- Conversation Details Modal -->
    <Modal
      v-model:visible="showDetailsModal"
      :title="selectedConversation?.contact_name"
      size="large"
      :showFooter="false"
    >
      <div v-if="selectedConversation" class="conversation-details">
        <div class="details-header">
          <div class="contact-card">
            <div class="contact-avatar large">
              <img v-if="selectedConversation.contact_avatar" :src="selectedConversation.contact_avatar" :alt="selectedConversation.contact_name" />
              <div v-else class="avatar-placeholder">
                {{ getInitials(selectedConversation.contact_name) }}
              </div>
            </div>
            <div>
              <h3>{{ selectedConversation.contact_name }}</h3>
              <p>
                <i :class="getChannelIcon(selectedConversation.channel)"></i>
                {{ selectedConversation.channel }}
              </p>
              <p v-if="selectedConversation.contact_phone">
                <i class="fa fa-phone"></i>
                {{ selectedConversation.contact_phone }}
              </p>
            </div>
          </div>

          <div class="details-actions">
            <Button
              v-if="!selectedConversation.lead_created"
              label="Converter em Lead"
              icon="fa fa-user-plus"
              @click="convertToLead(selectedConversation)"
            />
          </div>
        </div>

        <div class="messages-timeline">
          <div v-for="message in selectedConversation.messages" :key="message.id" :class="['timeline-message', message.from]">
            <div class="message-avatar">
              <i v-if="message.from === 'ai'" class="fa fa-robot"></i>
              <i v-else-if="message.from === 'user'" class="fa fa-user"></i>
              <i v-else class="fa fa-user-circle"></i>
            </div>
            <div class="message-bubble">
              <div class="message-header">
                <strong>{{ message.sender_name }}</strong>
                <span class="message-time">{{ formatTime(message.created_at) }}</span>
              </div>
              <p class="message-text">{{ message.content }}</p>
              <div v-if="message.confidence_score" class="confidence-badge">
                Confiança: {{ message.confidence_score }}%
              </div>
            </div>
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
import Button from '@/Components/Button.vue';
import Input from '@/Components/Input.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import Modal from '@/Components/Modal.vue';
import StatCard from '@/Components/StatCard.vue';

const props = defineProps({
  conversations: Array,
  stats: Object,
  pagination: Object,
});

const breadcrumbs = [
  { label: 'Dashboard', to: '/dashboard' },
  { label: 'IA', to: '/ai' },
  { label: 'Conversas', active: true }
];

const searchQuery = ref('');
const filter = ref('all');
const dateRange = ref('month');
const showDetailsModal = ref(false);
const selectedConversation = ref(null);

const filteredConversations = computed(() => {
  let filtered = props.conversations;

  if (searchQuery.value) {
    filtered = filtered.filter(c =>
      c.contact_name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      c.ai_summary?.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
  }

  if (filter.value === 'qualified') {
    filtered = filtered.filter(c => c.is_qualified);
  } else if (filter.value === 'unresolved') {
    filtered = filtered.filter(c => !c.is_resolved);
  } else if (filter.value === 'whatsapp') {
    filtered = filtered.filter(c => c.channel === 'whatsapp');
  } else if (filter.value === 'instagram') {
    filtered = filtered.filter(c => c.channel === 'instagram');
  }

  return filtered;
});

const showConversationDetails = async (conversation) => {
  selectedConversation.value = conversation;

  // Load full conversation details if not already loaded
  if (!conversation.messages) {
    const response = await fetch(`/api/ai/conversations/${conversation.id}`);
    const data = await response.json();
    selectedConversation.value = data;
  }

  showDetailsModal.value = true;
};

const convertToLead = (conversation) => {
  router.post(`/ai/conversations/${conversation.id}/convert-to-lead`, {}, {
    onSuccess: () => {
      showDetailsModal.value = false;
    },
  });
};

const changePage = (page) => {
  router.get('/ai/conversations', {
    page,
    date_range: dateRange.value,
    filter: filter.value,
    search: searchQuery.value,
  });
};

const getInitials = (name) => {
  return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
};

const getChannelIcon = (channel) => {
  const icons = {
    whatsapp: 'fab fa-whatsapp',
    instagram: 'fab fa-instagram',
    email: 'fa fa-envelope',
  };
  return icons[channel] || 'fa fa-comments';
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('pt-BR', { day: '2-digit', month: 'short' });
};

const formatTime = (datetime) => {
  return new Date(datetime).toLocaleString('pt-BR', {
    day: '2-digit',
    month: 'short',
    hour: '2-digit',
    minute: '2-digit'
  });
};
</script>

