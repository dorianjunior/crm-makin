<template>
  <MainLayout>
    <div class="instagram-messages">
      <!-- Header -->
      <div class="page-header">
        <div>
          <h1 class="page-title">
            <i class="fab fa-instagram"></i>
            Instagram Direct
          </h1>
          <Breadcrumbs :items="breadcrumbs" />
        </div>
        <div class="header-actions">
          <Button
            label="Sincronizar"
            icon="fa fa-sync"
            @click="syncMessages"
            :loading="syncing"
            outlined
          />
          <Button
            label="Configurações"
            icon="fa fa-cog"
            @click="router.visit('/social/instagram/settings')"
            outlined
          />
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="stats-grid">
        <StatCard
          icon="fa fa-comments"
          label="Total de Conversas"
          :value="stats.total"
          color="purple"
        />
        <StatCard
          icon="fa fa-envelope"
          label="Não Respondidas"
          :value="stats.pending"
          color="orange"
        />
        <StatCard
          icon="fa fa-clock"
          label="Tempo Médio de Resposta"
          :value="stats.avg_response_time"
          color="blue"
        />
        <StatCard
          icon="fa fa-user-check"
          label="Taxa de Conversão"
          :value="stats.conversion_rate + '%'"
          color="green"
        />
      </div>

      <div class="messages-container">
        <!-- Sidebar - Lista de Conversas -->
        <div class="messages-sidebar">
          <div class="sidebar-header">
            <h2>Mensagens</h2>
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
              <i class="fa fa-inbox"></i>
              Principal
            </button>
            <button
              :class="['filter-btn', { active: filter === 'general' }]"
              @click="filter = 'general'"
            >
              <i class="fa fa-folder"></i>
              Geral
            </button>
            <button
              :class="['filter-btn', { active: filter === 'unread' }]"
              @click="filter = 'unread'"
            >
              <i class="fa fa-circle"></i>
              Não Lidas
              <span v-if="unreadCount > 0" class="badge-count">{{ unreadCount }}</span>
            </button>
          </div>

          <div class="conversations-list">
            <div
              v-for="conversation in filteredConversations"
              :key="conversation.id"
              :class="['conversation-item', {
                active: activeConversation?.id === conversation.id,
                unread: conversation.unread_count > 0
              }]"
              @click="selectConversation(conversation)"
            >
              <div class="conversation-avatar">
                <img :src="conversation.profile_picture" :alt="conversation.username" />
                <span v-if="conversation.is_verified" class="verified-badge">
                  <i class="fa fa-check-circle"></i>
                </span>
              </div>

              <div class="conversation-content">
                <div class="conversation-header">
                  <h3 class="conversation-name">{{ conversation.username }}</h3>
                  <span class="conversation-time">{{ formatTime(conversation.last_message_at) }}</span>
                </div>

                <div class="conversation-preview">
                  <div class="preview-content">
                    <i v-if="conversation.last_message_type === 'story_reply'" class="fa fa-reply"></i>
                    <i v-else-if="conversation.last_message_type === 'story_mention'" class="fa fa-at"></i>
                    <i v-else-if="conversation.last_message_type === 'media'" class="fa fa-image"></i>
                    <span>{{ conversation.last_message_preview }}</span>
                  </div>
                  <span v-if="conversation.unread_count > 0" class="unread-badge">
                    {{ conversation.unread_count }}
                  </span>
                </div>

                <div v-if="conversation.lead" class="conversation-tags">
                  <span class="tag-lead">
                    <i class="fa fa-user-tag"></i>
                    Lead
                  </span>
                </div>
              </div>
            </div>

            <div v-if="filteredConversations.length === 0" class="empty-conversations">
              <i class="fab fa-instagram"></i>
              <p>Nenhuma conversa encontrada</p>
            </div>
          </div>
        </div>

        <!-- Main Chat Area -->
        <div class="messages-main">
          <div v-if="!activeConversation" class="messages-empty">
            <i class="fab fa-instagram"></i>
            <h3>Selecione uma conversa</h3>
            <p>Escolha uma conversa da lista para visualizar as mensagens</p>
          </div>

          <template v-else>
            <!-- Chat Header -->
            <div class="messages-header">
              <div class="chat-profile">
                <div class="profile-avatar">
                  <img :src="activeConversation.profile_picture" :alt="activeConversation.username" />
                  <span v-if="activeConversation.is_verified" class="verified-badge">
                    <i class="fa fa-check-circle"></i>
                  </span>
                </div>
                <div class="profile-info">
                  <h3>{{ activeConversation.username }}</h3>
                  <p>
                    {{ activeConversation.full_name }}
                    <span v-if="activeConversation.followers_count">
                      · {{ formatFollowers(activeConversation.followers_count) }} seguidores
                    </span>
                  </p>
                </div>
              </div>

              <div class="chat-actions">
                <button
                  class="btn-icon"
                  title="Ver Perfil"
                  @click="viewInstagramProfile(activeConversation)"
                >
                  <i class="fab fa-instagram"></i>
                </button>
                <button
                  v-if="activeConversation.lead"
                  class="btn-icon"
                  title="Ver Lead"
                  @click="viewLead(activeConversation.lead)"
                >
                  <i class="fa fa-user-tag"></i>
                </button>
                <button
                  v-else
                  class="btn-icon"
                  title="Converter em Lead"
                  @click="convertToLead"
                >
                  <i class="fa fa-user-plus"></i>
                </button>
                <button class="btn-icon" title="Marcar como não lida" @click="markAsUnread">
                  <i class="fa fa-envelope"></i>
                </button>
                <button class="btn-icon" title="Mais opções">
                  <i class="fa fa-ellipsis-v"></i>
                </button>
              </div>
            </div>

            <!-- Messages Area -->
            <div class="messages-content" ref="messagesContainer">
              <div v-for="(group, date) in groupedMessages" :key="date" class="message-group">
                <div class="message-date-divider">
                  <span>{{ formatDateDivider(date) }}</span>
                </div>

                <div v-for="message in group" :key="message.id" :class="['message', message.direction]">
                  <!-- Story Reply Context -->
                  <div v-if="message.story_share" class="story-context">
                    <div class="story-preview">
                      <img :src="message.story_share.media_url" alt="Story" />
                    </div>
                    <p class="story-label">Resposta ao Story</p>
                  </div>

                  <!-- Story Mention Context -->
                  <div v-if="message.story_mention" class="story-context">
                    <div class="story-preview">
                      <img :src="message.story_mention.media_url" alt="Story" />
                    </div>
                    <p class="story-label">
                      <i class="fa fa-at"></i>
                      Mencionou você no Story
                    </p>
                  </div>

                  <div class="message-bubble">
                    <!-- Text Message -->
                    <div v-if="message.type === 'text'" class="message-text">
                      {{ message.content }}
                    </div>

                    <!-- Image Message -->
                    <div v-else-if="message.type === 'image'" class="message-media">
                      <img :src="message.media_url" :alt="message.caption" @click="openMediaViewer(message)" />
                      <p v-if="message.caption" class="media-caption">{{ message.caption }}</p>
                    </div>

                    <!-- Video Message -->
                    <div v-else-if="message.type === 'video'" class="message-media">
                      <video controls :src="message.media_url">
                        <source :src="message.media_url" type="video/mp4">
                      </video>
                      <p v-if="message.caption" class="media-caption">{{ message.caption }}</p>
                    </div>

                    <!-- Voice Message -->
                    <div v-else-if="message.type === 'voice'" class="message-voice">
                      <button class="play-btn">
                        <i class="fa fa-play"></i>
                      </button>
                      <div class="voice-waveform"></div>
                      <span class="voice-duration">{{ message.duration }}s</span>
                    </div>

                    <!-- Like -->
                    <div v-else-if="message.type === 'like'" class="message-like">
                      ❤️
                    </div>

                    <!-- Reel Share -->
                    <div v-else-if="message.type === 'reel_share'" class="message-reel">
                      <i class="fab fa-instagram"></i>
                      <span>Compartilhou um Reel</span>
                      <a :href="message.reel_url" target="_blank">Ver Reel</a>
                    </div>

                    <!-- Post Share -->
                    <div v-else-if="message.type === 'post_share'" class="message-post">
                      <i class="fa fa-image"></i>
                      <span>Compartilhou uma publicação</span>
                      <a :href="message.post_url" target="_blank">Ver Post</a>
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
                <img :src="activeConversation.profile_picture" :alt="activeConversation.username" class="typing-avatar" />
                <div class="typing-bubble">
                  <div class="typing-dots">
                    <span></span>
                    <span></span>
                    <span></span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Quick Reactions -->
            <div v-if="showQuickReactions" class="quick-reactions">
              <button @click="sendQuickReaction('❤️')" class="reaction-btn">❤️</button>
              <button @click="sendQuickReaction('😂')" class="reaction-btn">😂</button>
              <button @click="sendQuickReaction('😮')" class="reaction-btn">😮</button>
              <button @click="sendQuickReaction('😢')" class="reaction-btn">😢</button>
              <button @click="sendQuickReaction('👏')" class="reaction-btn">👏</button>
              <button @click="sendQuickReaction('🔥')" class="reaction-btn">🔥</button>
            </div>

            <!-- Input Area -->
            <div class="messages-input-area">
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
                  label="Usar"
                  icon="fa fa-check"
                  @click="useAISuggestion"
                  size="small"
                />
              </div>

              <div class="input-row">
                <button class="btn-icon" @click="showQuickReactions = !showQuickReactions">
                  <i class="fa fa-heart"></i>
                </button>
                <button class="btn-icon" @click="showMediaPicker = !showMediaPicker">
                  <i class="fa fa-image"></i>
                </button>
                <button class="btn-icon" @click="recordVoice">
                  <i class="fa fa-microphone"></i>
                </button>

                <textarea
                  v-model="messageText"
                  @keydown.enter.exact.prevent="sendMessage"
                  placeholder="Mensagem..."
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
            </div>
          </template>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, computed, onMounted, nextTick, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Button from '@/Components/Button.vue';
import Input from '@/Components/Input.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import StatCard from '@/Components/StatCard.vue';

const props = defineProps({
  conversations: Array,
  stats: Object,
  initialConversation: Object,
});

const breadcrumbs = [
  { label: 'Dashboard', to: '/dashboard' },
  { label: 'Social', to: '/social' },
  { label: 'Instagram', active: true }
];

const searchQuery = ref('');
const filter = ref('all');
const activeConversation = ref(props.initialConversation || null);
const messages = ref([]);
const messageText = ref('');
const isTyping = ref(false);
const messagesContainer = ref(null);
const messageInput = ref(null);
const aiSuggestion = ref(null);
const showQuickReactions = ref(false);
const showMediaPicker = ref(false);
const syncing = ref(false);

const unreadCount = computed(() => {
  return props.conversations.filter(c => c.unread_count > 0).length;
});

const filteredConversations = computed(() => {
  let filtered = props.conversations;

  if (searchQuery.value) {
    filtered = filtered.filter(c =>
      c.username.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      c.full_name?.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
  }

  if (filter.value === 'unread') {
    filtered = filtered.filter(c => c.unread_count > 0);
  } else if (filter.value === 'general') {
    filtered = filtered.filter(c => !c.is_primary);
  } else if (filter.value === 'all') {
    filtered = filtered.filter(c => c.is_primary);
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
  showQuickReactions.value = false;

  // Mark as read
  router.post(`/social/instagram/conversations/${conversation.id}/mark-read`, {}, {
    preserveState: true,
  });
};

const loadMessages = async (conversationId) => {
  try {
    const response = await fetch(`/api/social/instagram/conversations/${conversationId}/messages`);
    const data = await response.json();
    messages.value = data.messages;
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
  };

  router.post('/social/instagram/messages', message, {
    preserveState: true,
    onSuccess: () => {
      messageText.value = '';
      aiSuggestion.value = null;
      scrollToBottom();
    },
  });
};

const sendQuickReaction = async (emoji) => {
  if (!activeConversation.value) return;

  const message = {
    conversation_id: activeConversation.value.id,
    type: 'like',
    content: emoji,
  };

  router.post('/social/instagram/messages', message, {
    preserveState: true,
    onSuccess: () => {
      showQuickReactions.value = false;
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
  router.post(`/social/instagram/conversations/${activeConversation.value.id}/convert-to-lead`);
};

const viewLead = (lead) => {
  router.visit(`/leads/${lead.id}`);
};

const viewInstagramProfile = (conversation) => {
  window.open(`https://instagram.com/${conversation.username}`, '_blank');
};

const markAsUnread = () => {
  router.post(`/social/instagram/conversations/${activeConversation.value.id}/mark-unread`);
};

const syncMessages = async () => {
  syncing.value = true;
  router.post('/social/instagram/sync', {}, {
    preserveState: true,
    onFinish: () => {
      syncing.value = false;
    },
  });
};

const recordVoice = () => {
  // Implement voice recording
};

const openMediaViewer = (message) => {
  // Implement media viewer modal
};

const scrollToBottom = () => {
  nextTick(() => {
    if (messagesContainer.value) {
      messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
    }
  });
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

const formatFollowers = (count) => {
  if (count >= 1000000) {
    return (count / 1000000).toFixed(1) + 'M';
  }
  if (count >= 1000) {
    return (count / 1000).toFixed(1) + 'K';
  }
  return count;
};

const getMessageStatusIcon = (status) => {
  const icons = {
    sent: 'fa fa-check',
    delivered: 'fa fa-check-double',
    seen: 'fa fa-check-double text-blue',
    failed: 'fa fa-exclamation-circle text-red',
  };
  return icons[status] || 'fa fa-clock';
};

onMounted(() => {
  if (activeConversation.value) {
    loadMessages(activeConversation.value.id);
  }

  // Setup polling for new messages
  const interval = setInterval(() => {
    if (activeConversation.value) {
      // Poll for updates
    }
  }, 10000);

  return () => clearInterval(interval);
});
</script>

<style scoped lang="scss">
.instagram-messages {
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
      background: linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }
  }

  .header-actions {
    display: flex;
    gap: 0.75rem;
  }
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
  margin-bottom: 1.5rem;
}

.messages-container {
  display: flex;
  gap: 0;
  background: var(--surface-card);
  border-radius: var(--border-radius);
  box-shadow: var(--shadow-md);
  overflow: hidden;
  flex: 1;
}

// Sidebar - identical structure to WhatsApp but with Instagram styling
.messages-sidebar {
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
    font-size: 1.5rem;
    font-weight: 700;
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
  padding: 1rem 1.5rem;
  gap: 0.5rem;
  border-bottom: 1px solid var(--border-color);
  overflow-x: auto;
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
  white-space: nowrap;

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
    background: linear-gradient(90deg, rgba(240, 148, 51, 0.1) 0%, transparent 100%);
    border-left: 3px solid #f09433;
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

  img {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    border: 2px solid transparent;
    background: linear-gradient(white, white) padding-box,
                linear-gradient(45deg, #f09433, #e6683c, #dc2743, #cc2366, #bc1888) border-box;
  }

  .verified-badge {
    position: absolute;
    bottom: 0;
    right: 0;
    width: 20px;
    height: 20px;
    background: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;

    i {
      color: #3897F0;
      font-size: 0.875rem;
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

  .preview-content {
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
    background: #E4405F;
    color: white;
    border-radius: 10px;
    font-size: 0.75rem;
    font-weight: 600;
    flex-shrink: 0;
  }
}

.conversation-tags {
  display: flex;
  gap: 0.5rem;

  .tag-lead {
    padding: 0.125rem 0.5rem;
    background: var(--primary-color);
    color: white;
    border-radius: 4px;
    font-size: 0.7rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.25rem;
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
    background: linear-gradient(45deg, #f09433, #e6683c, #dc2743, #cc2366, #bc1888);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
  }
}

// Main Messages
.messages-main {
  flex: 1;
  display: flex;
  flex-direction: column;
  background: white;
}

.messages-empty {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: var(--text-secondary);

  i {
    font-size: 5rem;
    margin-bottom: 1rem;
    background: linear-gradient(45deg, #f09433, #e6683c, #dc2743, #cc2366, #bc1888);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
  }

  h3 {
    font-size: 1.5rem;
    color: var(--text-primary);
    margin-bottom: 0.5rem;
  }
}

.messages-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.5rem;
  border-bottom: 1px solid var(--border-color);
  background: var(--surface-card);
}

.chat-profile {
  display: flex;
  gap: 1rem;
  align-items: center;

  .profile-avatar {
    position: relative;

    img {
      width: 45px;
      height: 45px;
      border-radius: 50%;
    }

    .verified-badge {
      position: absolute;
      bottom: -2px;
      right: -2px;
      width: 18px;
      height: 18px;
      background: white;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;

      i {
        color: #3897F0;
        font-size: 0.75rem;
      }
    }
  }

  .profile-info {
    h3 {
      font-size: 1rem;
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

.messages-content {
  flex: 1;
  overflow-y: auto;
  padding: 1.5rem;
}

.message-group {
  margin-bottom: 1.5rem;
}

.message-date-divider {
  text-align: center;
  margin-bottom: 1rem;

  span {
    padding: 0.25rem 0.75rem;
    background: var(--surface-ground);
    border-radius: 8px;
    font-size: 0.75rem;
    color: var(--text-secondary);
    font-weight: 600;
  }
}

.message {
  display: flex;
  margin-bottom: 0.75rem;
  gap: 0.5rem;

  &.outbound {
    justify-content: flex-end;

    .message-bubble {
      background: #E4405F;
      color: white;
      border-radius: 20px 20px 4px 20px;
    }
  }

  &.inbound {
    justify-content: flex-start;

    .message-bubble {
      background: var(--surface-ground);
      color: var(--text-primary);
      border-radius: 20px 20px 20px 4px;
    }
  }

  .story-context {
    max-width: 50%;
    margin-bottom: 0.5rem;

    .story-preview {
      width: 100%;
      aspect-ratio: 9/16;
      max-height: 200px;
      border-radius: 12px;
      overflow: hidden;
      margin-bottom: 0.5rem;

      img {
        width: 100%;
        height: 100%;
        object-fit: cover;
      }
    }

    .story-label {
      font-size: 0.75rem;
      color: var(--text-secondary);
      display: flex;
      align-items: center;
      gap: 0.25rem;
    }
  }

  .message-bubble {
    max-width: 60%;
    padding: 0.75rem 1rem;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
  }

  .message-text {
    line-height: 1.5;
    word-wrap: break-word;
    white-space: pre-wrap;
  }

  .message-media {
    img, video {
      max-width: 300px;
      border-radius: 8px;
      cursor: pointer;
    }

    .media-caption {
      margin-top: 0.5rem;
    }
  }

  .message-voice {
    display: flex;
    align-items: center;
    gap: 0.75rem;

    .play-btn {
      width: 32px;
      height: 32px;
      border-radius: 50%;
      background: white;
      color: var(--primary-color);
      border: none;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .voice-waveform {
      flex: 1;
      height: 24px;
      background: linear-gradient(90deg,
        rgba(255,255,255,0.3) 0%,
        rgba(255,255,255,0.3) 20%,
        rgba(255,255,255,0.5) 40%,
        rgba(255,255,255,0.7) 50%,
        rgba(255,255,255,0.5) 60%,
        rgba(255,255,255,0.3) 80%,
        rgba(255,255,255,0.3) 100%);
      border-radius: 12px;
    }

    .voice-duration {
      font-size: 0.75rem;
      opacity: 0.8;
    }
  }

  .message-like {
    font-size: 3rem;
  }

  .message-reel,
  .message-post {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    align-items: center;

    i {
      font-size: 2rem;
    }

    a {
      color: inherit;
      text-decoration: underline;
    }
  }

  .message-meta {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 0.25rem;
    margin-top: 0.25rem;

    .message-time {
      font-size: 0.7rem;
      opacity: 0.7;
    }

    i {
      font-size: 0.75rem;

      &.text-blue {
        color: #3897F0;
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
  gap: 0.75rem;

  .typing-avatar {
    width: 24px;
    height: 24px;
    border-radius: 50%;
  }

  .typing-bubble {
    padding: 0.75rem 1rem;
    background: var(--surface-ground);
    border-radius: 20px;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
  }

  .typing-dots {
    display: flex;
    gap: 0.25rem;

    span {
      width: 8px;
      height: 8px;
      background: var(--text-secondary);
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
}

@keyframes typing {
  0%, 60%, 100% {
    transform: translateY(0);
    opacity: 0.7;
  }
  30% {
    transform: translateY(-6px);
    opacity: 1;
  }
}

.quick-reactions {
  display: flex;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.75rem;
  background: var(--surface-card);
  border-top: 1px solid var(--border-color);
}

.reaction-btn {
  padding: 0.5rem;
  background: transparent;
  border: 1px solid var(--border-color);
  border-radius: 50%;
  font-size: 1.5rem;
  cursor: pointer;
  transition: all 0.2s;

  &:hover {
    background: var(--surface-ground);
    transform: scale(1.1);
  }
}

.messages-input-area {
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
    padding: 0.5rem;
    background: transparent;
    border: none;
    color: var(--text-secondary);
    cursor: pointer;
    font-size: 1.25rem;

    &:hover {
      color: #E4405F;
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
      border-color: #E4405F;
    }
  }

  .btn-send {
    padding: 0.75rem;
    background: linear-gradient(45deg, #f09433, #dc2743, #bc1888);
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
      transform: scale(1.05);
      box-shadow: 0 4px 12px rgba(228, 64, 95, 0.3);
    }

    &:disabled {
      background: var(--border-color);
      cursor: not-allowed;
    }
  }
}

@media (max-width: 768px) {
  .messages-sidebar {
    width: 100%;
    position: absolute;
    z-index: 5;
  }

  .messages-main {
    width: 100%;
  }

  .message .message-bubble {
    max-width: 80%;
  }
}
</style>
