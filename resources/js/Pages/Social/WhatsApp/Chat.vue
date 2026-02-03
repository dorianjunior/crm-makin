<template>
  <MainLayout>
    <div class="whatsapp-chat">
      <!-- Header -->
      <div class="page-header">
        <div>
          <h1 class="page-title">
            <i class="fab fa-whatsapp"></i>
            WhatsApp Business
          </h1>
          <Breadcrumbs :items="breadcrumbs" />
        </div>
        <div class="header-actions">
          <Button
            label="Nova Conversa"
            icon="fa fa-plus"
            @click="showNewChatModal = true"
            severity="success"
          />
          <Button
            label="Configurações"
            icon="fa fa-cog"
            @click="router.visit('/social/whatsapp/settings')"
            outlined
          />
        </div>
      </div>

      <div class="chat-container">
        <!-- Sidebar - Lista de Conversas -->
        <div class="chat-sidebar">
          <div class="sidebar-header">
            <h2>Conversas</h2>
            <span class="badge">{{ conversations.length }}</span>
          </div>

          <div class="sidebar-search">
            <Input
              v-model="searchQuery"
              placeholder="Buscar conversas..."
              icon="fa fa-search"
              size="small"
            />
          </div>

          <div class="sidebar-filters">
            <button
              :class="['filter-btn', { active: filter === 'all' }]"
              @click="filter = 'all'"
            >
              Todas
            </button>
            <button
              :class="['filter-btn', { active: filter === 'unread' }]"
              @click="filter = 'unread'"
            >
              Não Lidas
              <span v-if="unreadCount > 0" class="badge-count">{{ unreadCount }}</span>
            </button>
            <button
              :class="['filter-btn', { active: filter === 'active' }]"
              @click="filter = 'active'"
            >
              Ativas
            </button>
          </div>

          <div class="conversations-list">
            <div
              v-for="conversation in filteredConversations"
              :key="conversation.id"
              :class="['conversation-item', { active: activeConversation?.id === conversation.id, unread: conversation.unread_count > 0 }]"
              @click="selectConversation(conversation)"
            >
              <div class="conversation-avatar">
                <img v-if="conversation.contact_avatar" :src="conversation.contact_avatar" :alt="conversation.contact_name" />
                <div v-else class="avatar-placeholder">
                  {{ getInitials(conversation.contact_name) }}
                </div>
                <span v-if="conversation.is_online" class="status-indicator online"></span>
              </div>

              <div class="conversation-content">
                <div class="conversation-header">
                  <h3 class="conversation-name">{{ conversation.contact_name }}</h3>
                  <span class="conversation-time">{{ formatTime(conversation.last_message_at) }}</span>
                </div>

                <div class="conversation-preview">
                  <div class="preview-text">
                    <i v-if="conversation.last_message_from === 'bot'" class="fa fa-robot"></i>
                    <i v-else-if="conversation.last_message_from === 'user'" class="fa fa-user"></i>
                    <span>{{ conversation.last_message_preview }}</span>
                  </div>
                  <span v-if="conversation.unread_count > 0" class="unread-badge">
                    {{ conversation.unread_count }}
                  </span>
                </div>

                <div v-if="conversation.lead" class="conversation-lead">
                  <i class="fa fa-user-tag"></i>
                  <span>{{ conversation.lead.name }}</span>
                </div>
              </div>
            </div>

            <div v-if="filteredConversations.length === 0" class="empty-conversations">
              <i class="fab fa-whatsapp"></i>
              <p>Nenhuma conversa encontrada</p>
            </div>
          </div>
        </div>

        <!-- Main Chat Area -->
        <div class="chat-main">
          <div v-if="!activeConversation" class="chat-empty">
            <i class="fab fa-whatsapp"></i>
            <h3>Selecione uma conversa</h3>
            <p>Escolha uma conversa da lista ou inicie uma nova</p>
          </div>

          <template v-else>
            <!-- Chat Header -->
            <div class="chat-header">
              <div class="chat-contact">
                <div class="contact-avatar">
                  <img v-if="activeConversation.contact_avatar" :src="activeConversation.contact_avatar" :alt="activeConversation.contact_name" />
                  <div v-else class="avatar-placeholder">
                    {{ getInitials(activeConversation.contact_name) }}
                  </div>
                  <span v-if="activeConversation.is_online" class="status-indicator online"></span>
                </div>
                <div class="contact-info">
                  <h3>{{ activeConversation.contact_name }}</h3>
                  <p>{{ activeConversation.contact_phone }}</p>
                </div>
              </div>

              <div class="chat-actions">
                <button v-if="activeConversation.lead" class="btn-icon" title="Ver Lead" @click="viewLead(activeConversation.lead)">
                  <i class="fa fa-user-tag"></i>
                </button>
                <button v-else class="btn-icon" title="Converter em Lead" @click="convertToLead">
                  <i class="fa fa-user-plus"></i>
                </button>
                <button class="btn-icon" title="Informações" @click="showInfoSidebar = !showInfoSidebar">
                  <i class="fa fa-info-circle"></i>
                </button>
                <button class="btn-icon" title="Mais opções">
                  <i class="fa fa-ellipsis-v"></i>
                </button>
              </div>
            </div>

            <!-- Messages Area -->
            <div class="messages-container" ref="messagesContainer">
              <div v-for="(group, date) in groupedMessages" :key="date" class="message-group">
                <div class="message-date-divider">
                  <span>{{ formatDateDivider(date) }}</span>
                </div>

                <div v-for="message in group" :key="message.id" :class="['message', message.direction]">
                  <div class="message-content">
                    <div v-if="message.type === 'text'" class="message-text">
                      {{ message.content }}
                    </div>
                    <div v-else-if="message.type === 'image'" class="message-media">
                      <img :src="message.media_url" :alt="message.caption" @click="openMediaModal(message)" />
                      <p v-if="message.caption" class="media-caption">{{ message.caption }}</p>
                    </div>
                    <div v-else-if="message.type === 'audio'" class="message-audio">
                      <audio controls :src="message.media_url"></audio>
                    </div>
                    <div v-else-if="message.type === 'document'" class="message-document">
                      <i class="fa fa-file"></i>
                      <a :href="message.media_url" target="_blank">{{ message.filename }}</a>
                    </div>

                    <div class="message-meta">
                      <span class="message-time">{{ formatMessageTime(message.created_at) }}</span>
                      <i v-if="message.direction === 'outbound'" :class="getMessageStatusIcon(message.status)"></i>
                    </div>
                  </div>

                  <div v-if="message.from_ai" class="ai-badge">
                    <i class="fa fa-robot"></i>
                    IA
                  </div>
                </div>
              </div>

              <div v-if="isTyping" class="typing-indicator">
                <div class="typing-dots">
                  <span></span>
                  <span></span>
                  <span></span>
                </div>
                <span class="typing-text">Digitando...</span>
              </div>
            </div>

            <!-- Input Area -->
            <div class="chat-input-area">
              <div v-if="aiSuggestion" class="ai-suggestion">
                <div class="suggestion-header">
                  <i class="fa fa-robot"></i>
                  <span>Sugestão da IA:</span>
                  <button @click="dismissAISuggestion" class="btn-close-sm">
                    <i class="fa fa-times"></i>
                  </button>
                </div>
                <p class="suggestion-text">{{ aiSuggestion }}</p>
                <Button
                  label="Usar Sugestão"
                  icon="fa fa-check"
                  @click="useAISuggestion"
                  size="small"
                />
              </div>

              <div class="input-row">
                <button class="btn-icon" @click="showEmojiPicker = !showEmojiPicker">
                  <i class="fa fa-smile"></i>
                </button>
                <button class="btn-icon" @click="showAttachMenu = !showAttachMenu">
                  <i class="fa fa-paperclip"></i>
                </button>

                <textarea
                  v-model="messageText"
                  @keydown.enter.exact.prevent="sendMessage"
                  placeholder="Digite uma mensagem..."
                  class="message-input"
                  rows="1"
                  ref="messageInput"
                ></textarea>

                <button
                  class="btn-send"
                  @click="sendMessage"
                  :disabled="!messageText.trim()"
                >
                  <i class="fa fa-paper-plane"></i>
                </button>
              </div>

              <!-- Attach Menu -->
              <div v-if="showAttachMenu" class="attach-menu">
                <button @click="attachImage" class="attach-option">
                  <i class="fa fa-image"></i>
                  <span>Imagem</span>
                </button>
                <button @click="attachDocument" class="attach-option">
                  <i class="fa fa-file"></i>
                  <span>Documento</span>
                </button>
                <button @click="attachAudio" class="attach-option">
                  <i class="fa fa-microphone"></i>
                  <span>Áudio</span>
                </button>
              </div>
            </div>
          </template>
        </div>

        <!-- Info Sidebar -->
        <div v-if="showInfoSidebar && activeConversation" class="info-sidebar">
          <div class="sidebar-header">
            <h3>Informações</h3>
            <button @click="showInfoSidebar = false" class="btn-close">
              <i class="fa fa-times"></i>
            </button>
          </div>

          <div class="info-content">
            <div class="info-section">
              <h4>Contato</h4>
              <div class="info-item">
                <span class="label">Nome:</span>
                <span class="value">{{ activeConversation.contact_name }}</span>
              </div>
              <div class="info-item">
                <span class="label">Telefone:</span>
                <span class="value">{{ activeConversation.contact_phone }}</span>
              </div>
            </div>

            <div v-if="activeConversation.lead" class="info-section">
              <h4>Lead Associado</h4>
              <div class="lead-card">
                <p><strong>{{ activeConversation.lead.name }}</strong></p>
                <p>{{ activeConversation.lead.email }}</p>
                <Button
                  label="Ver Lead"
                  icon="fa fa-external-link-alt"
                  @click="viewLead(activeConversation.lead)"
                  size="small"
                  outlined
                />
              </div>
            </div>

            <div class="info-section">
              <h4>Configurações IA</h4>
              <div class="toggle-option">
                <span>Respostas Automáticas</span>
                <label class="switch">
                  <input type="checkbox" v-model="aiAutoReply" @change="toggleAIAutoReply" />
                  <span class="slider"></span>
                </label>
              </div>
              <div class="toggle-option">
                <span>Sugestões IA</span>
                <label class="switch">
                  <input type="checkbox" v-model="aiSuggestions" @change="toggleAISuggestions" />
                  <span class="slider"></span>
                </label>
              </div>
            </div>

            <div class="info-section">
              <h4>Mídia Compartilhada</h4>
              <div class="media-grid">
                <div v-for="media in sharedMedia" :key="media.id" class="media-thumb">
                  <img :src="media.thumbnail" :alt="media.name" @click="openMediaModal(media)" />
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Nova Conversa -->
    <Modal
      v-model:visible="showNewChatModal"
      title="Nova Conversa"
      @confirm="startNewChat"
    >
      <div class="modal-form">
        <div class="form-group">
          <label class="required">Número de Telefone</label>
          <Input
            v-model="newChatPhone"
            placeholder="+55 11 99999-9999"
            icon="fa fa-phone"
          />
        </div>
        <div class="form-group">
          <label>Mensagem Inicial</label>
          <textarea
            v-model="newChatMessage"
            class="form-textarea"
            placeholder="Digite a primeira mensagem..."
            rows="4"
          ></textarea>
        </div>
      </div>
    </Modal>
  </MainLayout>
</template>

<script setup>
import { ref, computed, onMounted, nextTick, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Button from '@/Components/Button.vue';
import Input from '@/Components/Input.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
  conversations: Array,
  initialConversation: Object,
});

const breadcrumbs = [
  { label: 'Dashboard', to: '/dashboard' },
  { label: 'Social', to: '/social' },
  { label: 'WhatsApp', active: true }
];

const searchQuery = ref('');
const filter = ref('all');
const activeConversation = ref(props.initialConversation || null);
const messages = ref([]);
const messageText = ref('');
const isTyping = ref(false);
const showInfoSidebar = ref(false);
const showEmojiPicker = ref(false);
const showAttachMenu = ref(false);
const messagesContainer = ref(null);
const messageInput = ref(null);
const aiSuggestion = ref(null);
const aiAutoReply = ref(true);
const aiSuggestions = ref(true);
const sharedMedia = ref([]);
const showNewChatModal = ref(false);
const newChatPhone = ref('');
const newChatMessage = ref('');

const unreadCount = computed(() => {
  return props.conversations.filter(c => c.unread_count > 0).length;
});

const filteredConversations = computed(() => {
  let filtered = props.conversations;

  if (searchQuery.value) {
    filtered = filtered.filter(c =>
      c.contact_name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      c.contact_phone.includes(searchQuery.value)
    );
  }

  if (filter.value === 'unread') {
    filtered = filtered.filter(c => c.unread_count > 0);
  } else if (filter.value === 'active') {
    filtered = filtered.filter(c => c.is_active);
  }

  return filtered;
});

const groupedMessages = computed(() => {
  const groups = {};

  messages.value.forEach(message => {
    const date = new Date(message.created_at).toLocaleDateString('pt-BR');

    if (!groups[date]) {
      groups[date] = [];
    }

    groups[date].push(message);
  });

  return groups;
});

watch(activeConversation, async (newConv) => {
  if (newConv) {
    await loadMessages(newConv.id);
    scrollToBottom();
  }
});

const selectConversation = async (conversation) => {
  activeConversation.value = conversation;
  showInfoSidebar.value = false;

  // Mark as read
  router.post(`/social/whatsapp/conversations/${conversation.id}/mark-read`, {}, {
    preserveState: true,
  });
};

const loadMessages = async (conversationId) => {
  try {
    const response = await fetch(`/api/social/whatsapp/conversations/${conversationId}/messages`);
    const data = await response.json();
    messages.value = data.messages;

    // Simulate AI suggestion after loading
    if (aiSuggestions.value && Math.random() > 0.5) {
      setTimeout(() => {
        aiSuggestion.value = "Olá! Vejo que você está interessado em nossos serviços. Gostaria de agendar uma demonstração?";
      }, 2000);
    }
  } catch (error) {
    console.error('Error loading messages:', error);
  }
};

const sendMessage = async () => {
  if (!messageText.value.trim() || !activeConversation.value) return;

  const message = {
    conversation_id: activeConversation.value.id,
    content: messageText.value,
    type: 'text',
    direction: 'outbound',
  };

  router.post('/social/whatsapp/messages', message, {
    preserveState: true,
    onSuccess: () => {
      messageText.value = '';
      aiSuggestion.value = null;
    },
  });
};

const useAISuggestion = () => {
  messageText.value = aiSuggestion.value;
  aiSuggestion.value = null;
  messageInput.value?.focus();
};

const dismissAISuggestion = () => {
  aiSuggestion.value = null;
};

const convertToLead = () => {
  router.post(`/social/whatsapp/conversations/${activeConversation.value.id}/convert-to-lead`);
};

const viewLead = (lead) => {
  router.visit(`/leads/${lead.id}`);
};

const toggleAIAutoReply = () => {
  router.patch(`/social/whatsapp/conversations/${activeConversation.value.id}/settings`, {
    ai_auto_reply: aiAutoReply.value
  });
};

const toggleAISuggestions = () => {
  // Save to user preferences
};

const startNewChat = () => {
  router.post('/social/whatsapp/conversations', {
    phone: newChatPhone.value,
    initial_message: newChatMessage.value,
  }, {
    onSuccess: () => {
      showNewChatModal.value = false;
      newChatPhone.value = '';
      newChatMessage.value = '';
    },
  });
};

const attachImage = () => {
  // Implement image upload
  showAttachMenu.value = false;
};

const attachDocument = () => {
  // Implement document upload
  showAttachMenu.value = false;
};

const attachAudio = () => {
  // Implement audio upload
  showAttachMenu.value = false;
};

const openMediaModal = (media) => {
  // Implement media viewer
};

const scrollToBottom = () => {
  nextTick(() => {
    if (messagesContainer.value) {
      messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
    }
  });
};

const getInitials = (name) => {
  return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
};

const formatTime = (datetime) => {
  const now = new Date();
  const then = new Date(datetime);
  const diffHours = (now - then) / (1000 * 60 * 60);

  if (diffHours < 24) {
    return then.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
  }
  return then.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit' });
};

const formatMessageTime = (datetime) => {
  return new Date(datetime).toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
};

const formatDateDivider = (date) => {
  const today = new Date().toLocaleDateString('pt-BR');
  const yesterday = new Date(Date.now() - 86400000).toLocaleDateString('pt-BR');

  if (date === today) return 'Hoje';
  if (date === yesterday) return 'Ontem';
  return date;
};

const getMessageStatusIcon = (status) => {
  const icons = {
    sent: 'fa fa-check',
    delivered: 'fa fa-check-double',
    read: 'fa fa-check-double text-blue',
    failed: 'fa fa-exclamation-circle text-red',
  };
  return icons[status] || 'fa fa-clock';
};

onMounted(() => {
  if (activeConversation.value) {
    loadMessages(activeConversation.value.id);
  }

  // Setup real-time updates (websockets/polling)
  const interval = setInterval(() => {
    if (activeConversation.value) {
      // Poll for new messages
    }
  }, 5000);

  return () => clearInterval(interval);
});
</script>

