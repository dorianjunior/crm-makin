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

<style scoped lang="scss">
.whatsapp-chat {
  padding: 2rem;
  height: calc(100vh - 4rem);
  display: flex;
  flex-direction: column;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;

  .page-title {
    font-size: 2rem;
    font-weight: 700;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 0.75rem;

    i {
      color: #25D366;
    }
  }

  .header-actions {
    display: flex;
    gap: 0.75rem;
  }
}

.chat-container {
  display: flex;
  flex: 1;
  gap: 0;
  background: var(--surface-card);
  border-radius: var(--border-radius);
  box-shadow: var(--shadow-md);
  overflow: hidden;
  height: calc(100vh - 12rem);
}

// Sidebar
.chat-sidebar {
  width: 380px;
  border-right: 1px solid var(--border-color);
  display: flex;
  flex-direction: column;
  background: var(--surface-ground);
}

.sidebar-header {
  padding: 1.5rem;
  border-bottom: 1px solid var(--border-color);
  display: flex;
  justify-content: space-between;
  align-items: center;

  h2 {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--text-primary);
  }

  .badge {
    padding: 0.25rem 0.75rem;
    background: var(--primary-color);
    color: white;
    border-radius: 12px;
    font-size: 0.875rem;
    font-weight: 600;
  }
}

.sidebar-search {
  padding: 1rem 1.5rem;
  border-bottom: 1px solid var(--border-color);
}

.sidebar-filters {
  display: flex;
  padding: 0.75rem 1.5rem;
  gap: 0.5rem;
  border-bottom: 1px solid var(--border-color);
}

.filter-btn {
  padding: 0.5rem 1rem;
  background: transparent;
  border: 1px solid var(--border-color);
  border-radius: 20px;
  color: var(--text-secondary);
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  gap: 0.5rem;

  &:hover {
    background: var(--surface-hover);
  }

  &.active {
    background: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
  }

  .badge-count {
    padding: 0.125rem 0.5rem;
    background: white;
    color: var(--primary-color);
    border-radius: 10px;
    font-size: 0.75rem;
    font-weight: 600;
  }
}

.conversations-list {
  flex: 1;
  overflow-y: auto;
}

.conversation-item {
  display: flex;
  gap: 1rem;
  padding: 1rem 1.5rem;
  cursor: pointer;
  transition: background 0.2s;
  border-bottom: 1px solid var(--border-color);

  &:hover {
    background: var(--surface-hover);
  }

  &.active {
    background: var(--primary-color);
    color: white;

    .conversation-name,
    .preview-text,
    .conversation-time {
      color: white;
    }
  }

  &.unread {
    background: var(--surface-card);

    .conversation-name {
      font-weight: 700;
    }
  }
}

.conversation-avatar {
  position: relative;
  flex-shrink: 0;

  img,
  .avatar-placeholder {
    width: 50px;
    height: 50px;
    border-radius: 50%;
  }

  .avatar-placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--primary-color);
    color: white;
    font-weight: 600;
  }

  .status-indicator {
    position: absolute;
    bottom: 2px;
    right: 2px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid var(--surface-ground);

    &.online {
      background: #10B981;
    }
  }
}

.conversation-content {
  flex: 1;
  min-width: 0;
}

.conversation-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.25rem;
}

.conversation-name {
  font-weight: 600;
  color: var(--text-primary);
  font-size: 0.95rem;
}

.conversation-time {
  font-size: 0.75rem;
  color: var(--text-secondary);
}

.conversation-preview {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.5rem;

  .preview-text {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    color: var(--text-secondary);
    font-size: 0.875rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;

    i {
      font-size: 0.75rem;
      flex-shrink: 0;
    }
  }

  .unread-badge {
    padding: 0.125rem 0.5rem;
    background: #25D366;
    color: white;
    border-radius: 10px;
    font-size: 0.75rem;
    font-weight: 600;
    flex-shrink: 0;
  }
}

.conversation-lead {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  font-size: 0.75rem;
  color: var(--primary-color);

  i {
    font-size: 0.7rem;
  }
}

.empty-conversations {
  padding: 3rem 2rem;
  text-align: center;
  color: var(--text-secondary);

  i {
    font-size: 3rem;
    margin-bottom: 0.5rem;
    display: block;
    color: #25D366;
  }
}

// Main Chat
.chat-main {
  flex: 1;
  display: flex;
  flex-direction: column;
  background: #E5DDD5;
}

.chat-empty {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: var(--text-secondary);

  i {
    font-size: 5rem;
    color: #25D366;
    margin-bottom: 1rem;
  }

  h3 {
    font-size: 1.5rem;
    color: var(--text-primary);
    margin-bottom: 0.5rem;
  }
}

.chat-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.5rem;
  background: var(--surface-card);
  border-bottom: 1px solid var(--border-color);
}

.chat-contact {
  display: flex;
  gap: 1rem;
  align-items: center;

  .contact-avatar {
    position: relative;

    img,
    .avatar-placeholder {
      width: 45px;
      height: 45px;
      border-radius: 50%;
    }

    .avatar-placeholder {
      display: flex;
      align-items: center;
      justify-content: center;
      background: var(--primary-color);
      color: white;
      font-weight: 600;
    }

    .status-indicator {
      position: absolute;
      bottom: 0;
      right: 0;
      width: 12px;
      height: 12px;
      border-radius: 50%;
      border: 2px solid var(--surface-card);

      &.online {
        background: #10B981;
      }
    }
  }

  .contact-info {
    h3 {
      font-size: 1.125rem;
      font-weight: 600;
      color: var(--text-primary);
      margin-bottom: 0.125rem;
    }

    p {
      font-size: 0.875rem;
      color: var(--text-secondary);
    }
  }
}

.chat-actions {
  display: flex;
  gap: 0.5rem;

  .btn-icon {
    padding: 0.5rem;
    background: transparent;
    border: none;
    border-radius: 50%;
    color: var(--text-secondary);
    cursor: pointer;
    transition: all 0.2s;

    &:hover {
      background: var(--surface-ground);
      color: var(--primary-color);
    }
  }
}

.messages-container {
  flex: 1;
  overflow-y: auto;
  padding: 1.5rem;
  background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100"><rect fill="%23E5DDD5" width="100" height="100"/></svg>');
}

.message-group {
  margin-bottom: 1.5rem;
}

.message-date-divider {
  text-align: center;
  margin-bottom: 1rem;

  span {
    padding: 0.25rem 0.75rem;
    background: rgba(0, 0, 0, 0.1);
    border-radius: 8px;
    font-size: 0.75rem;
    color: #54656F;
  }
}

.message {
  display: flex;
  margin-bottom: 0.5rem;
  gap: 0.5rem;

  &.outbound {
    justify-content: flex-end;

    .message-content {
      background: #DCF8C6;
      border-radius: 8px 0 8px 8px;
    }
  }

  &.inbound {
    justify-content: flex-start;

    .message-content {
      background: white;
      border-radius: 0 8px 8px 8px;
    }
  }

  .message-content {
    max-width: 65%;
    padding: 0.75rem;
    box-shadow: 0 1px 0.5px rgba(0, 0, 0, 0.13);
  }

  .message-text {
    color: #111B21;
    line-height: 1.4;
    word-wrap: break-word;
    white-space: pre-wrap;
  }

  .message-media {
    img {
      max-width: 100%;
      border-radius: 4px;
      cursor: pointer;
    }

    .media-caption {
      margin-top: 0.5rem;
      color: #111B21;
    }
  }

  .message-audio {
    audio {
      width: 100%;
    }
  }

  .message-document {
    display: flex;
    align-items: center;
    gap: 0.5rem;

    i {
      font-size: 1.5rem;
      color: var(--primary-color);
    }

    a {
      color: var(--primary-color);
      text-decoration: none;

      &:hover {
        text-decoration: underline;
      }
    }
  }

  .message-meta {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 0.25rem;
    margin-top: 0.25rem;

    .message-time {
      font-size: 0.688rem;
      color: #667781;
    }

    i {
      font-size: 0.875rem;
      color: #53BDEB;

      &.text-blue {
        color: #53BDEB;
      }

      &.text-red {
        color: #EF4444;
      }
    }
  }

  .ai-badge {
    align-self: flex-end;
    padding: 0.25rem 0.5rem;
    background: #8B5CF6;
    color: white;
    border-radius: 4px;
    font-size: 0.7rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.25rem;
  }
}

.typing-indicator {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 0.5rem;

  .typing-dots {
    display: flex;
    gap: 0.25rem;
    padding: 0.75rem;
    background: white;
    border-radius: 18px;
    box-shadow: 0 1px 0.5px rgba(0, 0, 0, 0.13);

    span {
      width: 8px;
      height: 8px;
      background: #8696A0;
      border-radius: 50%;
      animation: typing 1.4s infinite;

      &:nth-child(2) {
        animation-delay: 0.2s;
      }

      &:nth-child(3) {
        animation-delay: 0.4s;
      }
    }
  }

  .typing-text {
    font-size: 0.75rem;
    color: #667781;
  }
}

@keyframes typing {
  0%, 60%, 100% {
    transform: translateY(0);
    opacity: 0.7;
  }
  30% {
    transform: translateY(-10px);
    opacity: 1;
  }
}

.chat-input-area {
  padding: 1rem 1.5rem;
  background: var(--surface-card);
  border-top: 1px solid var(--border-color);
}

.ai-suggestion {
  padding: 1rem;
  background: #F0F7FF;
  border-left: 3px solid #3B82F6;
  border-radius: var(--border-radius);
  margin-bottom: 1rem;

  .suggestion-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #3B82F6;

    i {
      font-size: 1.125rem;
    }

    .btn-close-sm {
      margin-left: auto;
      padding: 0.25rem;
      background: transparent;
      border: none;
      color: var(--text-secondary);
      cursor: pointer;

      &:hover {
        color: var(--text-primary);
      }
    }
  }

  .suggestion-text {
    color: var(--text-primary);
    margin-bottom: 0.75rem;
    line-height: 1.5;
  }
}

.input-row {
  display: flex;
  align-items: flex-end;
  gap: 0.75rem;

  .btn-icon {
    padding: 0.75rem;
    background: transparent;
    border: none;
    color: var(--text-secondary);
    cursor: pointer;
    font-size: 1.125rem;

    &:hover {
      color: var(--primary-color);
    }
  }

  .message-input {
    flex: 1;
    padding: 0.75rem 1rem;
    border: 1px solid var(--border-color);
    border-radius: 24px;
    background: var(--surface-ground);
    color: var(--text-primary);
    font-family: inherit;
    font-size: 0.9375rem;
    resize: none;
    max-height: 120px;

    &:focus {
      outline: none;
      border-color: var(--primary-color);
    }
  }

  .btn-send {
    padding: 0.75rem;
    background: #25D366;
    color: white;
    border: none;
    border-radius: 50%;
    width: 45px;
    height: 45px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;

    &:hover:not(:disabled) {
      background: #20BA5A;
      transform: scale(1.05);
    }

    &:disabled {
      background: var(--border-color);
      cursor: not-allowed;
    }
  }
}

.attach-menu {
  display: flex;
  gap: 0.75rem;
  margin-top: 0.75rem;
}

.attach-option {
  flex: 1;
  padding: 0.75rem;
  background: var(--surface-ground);
  border: 1px solid var(--border-color);
  border-radius: var(--border-radius);
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  transition: all 0.2s;

  i {
    font-size: 1.5rem;
    color: var(--primary-color);
  }

  span {
    font-size: 0.875rem;
    color: var(--text-secondary);
  }

  &:hover {
    background: var(--surface-hover);
    border-color: var(--primary-color);
  }
}

// Info Sidebar
.info-sidebar {
  width: 350px;
  border-left: 1px solid var(--border-color);
  background: var(--surface-card);
  display: flex;
  flex-direction: column;

  .sidebar-header {
    padding: 1.5rem;
    border-bottom: 1px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;

    h3 {
      font-size: 1.125rem;
      font-weight: 600;
      color: var(--text-primary);
    }

    .btn-close {
      padding: 0.5rem;
      background: transparent;
      border: none;
      color: var(--text-secondary);
      cursor: pointer;

      &:hover {
        color: var(--text-primary);
      }
    }
  }

  .info-content {
    flex: 1;
    overflow-y: auto;
    padding: 1.5rem;
  }

  .info-section {
    margin-bottom: 2rem;

    h4 {
      font-size: 0.875rem;
      font-weight: 600;
      color: var(--text-secondary);
      text-transform: uppercase;
      margin-bottom: 1rem;
    }
  }

  .info-item {
    display: flex;
    justify-content: space-between;
    padding: 0.75rem 0;
    border-bottom: 1px solid var(--border-color);

    .label {
      color: var(--text-secondary);
      font-size: 0.875rem;
    }

    .value {
      color: var(--text-primary);
      font-weight: 500;
    }
  }

  .lead-card {
    padding: 1rem;
    background: var(--surface-ground);
    border-radius: var(--border-radius);
    border: 1px solid var(--border-color);

    p {
      margin-bottom: 0.5rem;
      color: var(--text-secondary);
    }

    strong {
      color: var(--text-primary);
    }
  }

  .toggle-option {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid var(--border-color);

    span {
      color: var(--text-primary);
      font-size: 0.875rem;
    }
  }

  .switch {
    position: relative;
    display: inline-block;
    width: 44px;
    height: 24px;

    input {
      opacity: 0;
      width: 0;
      height: 0;

      &:checked + .slider {
        background-color: #25D366;

        &:before {
          transform: translateX(20px);
        }
      }
    }

    .slider {
      position: absolute;
      cursor: pointer;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-color: #ccc;
      transition: 0.4s;
      border-radius: 24px;

      &:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: 0.4s;
        border-radius: 50%;
      }
    }
  }

  .media-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.5rem;
  }

  .media-thumb {
    aspect-ratio: 1;
    overflow: hidden;
    border-radius: 4px;
    cursor: pointer;

    img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.2s;

      &:hover {
        transform: scale(1.1);
      }
    }
  }
}

// Modal
.modal-form {
  .form-group {
    margin-bottom: 1.5rem;

    label {
      display: block;
      font-size: 0.875rem;
      font-weight: 600;
      color: var(--text-secondary);
      margin-bottom: 0.5rem;

      &.required::after {
        content: '*';
        color: var(--red-500);
        margin-left: 0.25rem;
      }
    }
  }

  .form-textarea {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius);
    background: var(--surface-ground);
    color: var(--text-primary);
    font-size: 0.875rem;
    font-family: inherit;
    resize: vertical;

    &:focus {
      outline: none;
      border-color: var(--primary-color);
    }
  }
}

@media (max-width: 1200px) {
  .info-sidebar {
    position: absolute;
    right: 0;
    top: 0;
    bottom: 0;
    z-index: 10;
    box-shadow: var(--shadow-lg);
  }
}

@media (max-width: 768px) {
  .chat-sidebar {
    width: 100%;
    position: absolute;
    z-index: 5;
  }

  .chat-main {
    width: 100%;
  }

  .message .message-content {
    max-width: 85%;
  }
}
</style>
