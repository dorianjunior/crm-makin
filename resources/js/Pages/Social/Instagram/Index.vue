<template>
    <MainLayout title="Instagram">
        <template #breadcrumbs>
            <Breadcrumbs :items="breadcrumbs" />
        </template>

        <div class="page-container">
            <!-- Header -->
            <div class="page-header">
                <div>
                    <h1 class="page-title">Instagram</h1>
                    <p class="page-subtitle">Gerencie suas contas e posts do Instagram</p>
                </div>
                <Button @click="connectAccount" :loading="connecting" icon="fa-plus" variant="ghost">
                    Conectar Conta
                </Button>
            </div>

            <!-- Stats -->
            <div class="stats-grid" v-if="accounts.length > 0">
                <StatCard label="Contas Conectadas" :value="accounts.length" icon="fab fa-instagram" color="primary" />
                <StatCard label="Posts Recentes" :value="totalPosts" icon="fa fa-image" color="info" />
                <StatCard label="Total de Seguidores" :value="totalFollowers" icon="fa fa-users" color="success" />
                <StatCard label="Contas Ativas" :value="activeAccounts" icon="fa fa-check-circle" color="success" />
            </div>

            <!-- Connected Accounts -->
            <Card title="Contas Conectadas" icon="fab fa-instagram" v-if="accounts.length > 0">
                <div class="info-grid">
                    <div v-for="account in accounts" :key="account.id" class="social-account-item">
                        <div class="social-account-header">
                            <img :src="account.profile_picture_url || '/images/default-avatar.png'"
                                :alt="account.username" class="social-avatar" />
                            <div>
                                <h3 class="social-account-name">@{{ account.username }}</h3>
                                <div class="badge-group">
                                    <Badge :variant="account.is_active ? 'success' : 'error'">
                                        {{ account.is_active ? 'Ativa' : 'Inativa' }}
                                    </Badge>
                                    <Badge variant="info">{{ account.account_type }}</Badge>
                                </div>
                            </div>
                        </div>
                        <div class="social-account-actions">
                            <Button @click="viewPosts(account.id)" size="small" variant="outline"
                                icon="fa-images">
                                Ver Posts
                            </Button>
                            <Button @click="refreshToken(account.id)" size="small" variant="outline"
                                icon="fa-sync" :loading="refreshingToken[account.id]">
                                Atualizar Token
                            </Button>
                            <Button @click="disconnectAccount(account.id)" size="small" variant="error-outline"
                                icon="fa-unlink">
                                Desconectar
                            </Button>
                        </div>
                        <div class="social-account-meta">
                            <span class="meta-item">
                                <i class="fa fa-users"></i>
                                {{ formatNumber(account.followers_count) }} seguidores
                            </span>
                            <span class="meta-item">
                                <i class="fa fa-clock"></i>
                                Token expira: {{ formatDate(account.token_expires_at) }}
                            </span>
                        </div>
                    </div>
                </div>
            </Card>

            <!-- Recent Posts Preview -->
            <Card v-if="selectedAccount && posts.length > 0" :title="`Posts Recentes - @${selectedAccount.username}`"
                icon="fa fa-images">
                <div class="content-grid social-media-grid">
                    <div v-for="post in posts" :key="post.id" class="social-media-card">
                        <div class="social-media-preview">
                            <img v-if="post.media_type === 'IMAGE' || post.media_type === 'CAROUSEL_ALBUM'"
                                :src="post.media_url" :alt="post.caption || 'Post'" class="social-media-image" />
                            <video v-else-if="post.media_type === 'VIDEO'" :src="post.media_url"
                                class="social-media-video" controls />
                            <span class="media-type-badge">
                                <i :class="post.media_type === 'VIDEO' ? 'fa fa-video' : 'fa fa-image'"></i>
                                {{ post.media_type }}
                            </span>
                        </div>
                        <div class="social-media-content">
                            <p class="social-media-caption">{{ truncate(post.caption, 100) }}</p>
                            <div class="social-media-footer">
                                <div class="social-stats">
                                    <span v-if="post.like_count" class="stat-item">
                                        <i class="fa fa-heart"></i> {{ formatNumber(post.like_count) }}
                                    </span>
                                    <span v-if="post.comments_count" class="stat-item">
                                        <i class="fa fa-comment"></i> {{ formatNumber(post.comments_count) }}
                                    </span>
                                </div>
                                <span class="post-date">{{ formatDate(post.timestamp) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </Card>

            <!-- Empty State -->
            <div v-if="accounts.length === 0" class="empty-state">
                <div class="empty-state__icon">
                    <i class="fab fa-instagram"></i>
                </div>
                <h3 class="empty-state__title">Nenhuma conta conectada</h3>
                <p class="empty-state__description">Conecte sua conta do Instagram Business para começar a
                    gerenciar
                    seus posts e mensagens.</p>
                <Button @click="connectAccount" :loading="connecting" icon="fa-plus" variant="primary">
                    Conectar Primeira Conta
                </Button>
            </div>
        </div>
    </MainLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { useAlert } from '@/composables/useAlert';
import MainLayout from '@/Layouts/MainLayout.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import Button from '@/Components/Button.vue';
import Badge from '@/Components/Badge.vue';
import Card from '@/Components/Card.vue';
import StatCard from '@/Components/StatCard.vue';

const props = defineProps({
    accounts: {
        type: Array,
        default: () => []
    },
    selectedAccountId: {
        type: Number,
        default: null
    },
    posts: {
        type: Array,
        default: () => []
    }
});

const alert = useAlert();

const breadcrumbs = [
    { name: 'Dashboard', href: '/dashboard' },
    { name: 'Instagram' }
];

const connecting = ref(false);
const refreshingToken = ref({});
const selectedAccount = computed(() =>
    props.accounts.find(a => a.id === props.selectedAccountId)
);

// Stats
const totalPosts = computed(() => props.posts.length);
const totalFollowers = computed(() =>
    props.accounts.reduce((sum, acc) => sum + (acc.followers_count || 0), 0)
);
const activeAccounts = computed(() =>
    props.accounts.filter(a => a.is_active).length
);

// Connect Instagram Account
const connectAccount = async () => {
    connecting.value = true;
    try {
        // Get auth URL
        const response = await axios.get('/api/instagram/auth-url');
        const authUrl = response.data.auth_url;

        // Open popup
        const width = 600;
        const height = 700;
        const left = (screen.width / 2) - (width / 2);
        const top = (screen.height / 2) - (height / 2);

        const popup = window.open(
            authUrl,
            'Instagram Login',
            `width=${width},height=${height},left=${left},top=${top}`
        );

        // Listen for callback
        window.addEventListener('message', handleCallback);

    } catch (error) {
        alert.error('Erro ao conectar conta do Instagram');
        connecting.value = false;
    }
};

const handleCallback = async (event) => {
    if (event.data.type === 'instagram-callback') {
        const code = event.data.code;

        try {
            await axios.post('/api/instagram/connect', { code });
            alert.success('Conta conectada com sucesso!');
            router.reload();
        } catch (error) {
            alert.error('Erro ao conectar conta: ' + (error.response?.data?.message || 'Erro desconhecido'));
        } finally {
            connecting.value = false;
            window.removeEventListener('message', handleCallback);
        }
    }
};

// View posts from account
const viewPosts = (accountId) => {
    router.visit(`/instagram?account=${accountId}`);
};

// Refresh token
const refreshToken = async (accountId) => {
    refreshingToken.value[accountId] = true;
    try {
        await axios.post(`/api/instagram/${accountId}/refresh-token`);
        alert.success('Token atualizado com sucesso!');
        router.reload();
    } catch (error) {
        alert.error('Erro ao atualizar token');
    } finally {
        refreshingToken.value[accountId] = false;
    }
};

// Disconnect account
const disconnectAccount = async (accountId) => {
    if (!confirm('Deseja realmente desconectar esta conta?')) return;

    try {
        await axios.delete(`/api/instagram/${accountId}`);
        alert.success('Conta desconectada com sucesso!');
        router.reload();
    } catch (error) {
        alert.error('Erro ao desconectar conta');
    }
};

// Helpers
const formatNumber = (num) => {
    if (!num) return '0';
    if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M';
    if (num >= 1000) return (num / 1000).toFixed(1) + 'K';
    return num.toString();
};

const formatDate = (date) => {
    if (!date) return 'N/A';
    return new Date(date).toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
};

const truncate = (text, length) => {
    if (!text) return '';
    return text.length > length ? text.substring(0, length) + '...' : text;
};

onMounted(() => {
    // Debug
    console.log('Instagram Page Mounted', {
        accounts: props.accounts,
        posts: props.posts,
        selectedAccountId: props.selectedAccountId
    });

    // Check for OAuth callback in URL
    const urlParams = new URLSearchParams(window.location.search);
    const code = urlParams.get('code');

    if (code && window.opener) {
        // Send code to parent window
        window.opener.postMessage({ type: 'instagram-callback', code }, '*');
        window.close();
    }
});

</script>
