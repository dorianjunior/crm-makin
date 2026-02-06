<template>
  <MainLayout title="Instagram Direct">
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

