<script setup>
import { Head, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import StatCard from '@/Components/StatCard.vue';
import Alert from '@/Components/Alert.vue';

defineProps({
    stats: Object,
});

const quickActions = [
    { name: 'Novo Lead', icon: 'fas fa-user-plus', href: '/leads/create' },
    { name: 'Nova Página', icon: 'fas fa-file-alt', href: '/pages/create' },
    { name: 'Novo Post', icon: 'fas fa-edit', href: '/posts/create' },
    { name: 'Mensagens', icon: 'fas fa-envelope', href: '/whatsapp' },
];

const recentActivities = [
    { id: 1, type: 'lead', title: 'Novo lead cadastrado: João Silva', time: '5 min atrás', icon: 'fas fa-user-plus' },
    { id: 2, type: 'proposal', title: 'Proposta #1234 aprovada', time: '1 hora atrás', icon: 'fas fa-check-circle' },
    { id: 3, type: 'message', title: 'Nova mensagem do Instagram', time: '2 horas atrás', icon: 'fab fa-instagram' },
    { id: 4, type: 'page', title: 'Página "Sobre" publicada', time: '3 horas atrás', icon: 'fas fa-file-alt' },
];
</script>

<template>
    <Head title="Dashboard" />

    <MainLayout title="Dashboard" :no-wrapper="true">
        <template #header>
            <div class="dash__header">
                <div>
                    <h1 class="dash__title">Dashboard</h1>
                    <p class="dash__subtitle">Sistema operacional — CRM Makin</p>
                </div>
                <Link href="/leads/create" class="dash__cta">
                    <span class="dash__cta-glow"></span>
                    <span class="dash__cta-icon"><i class="fas fa-plus"></i></span>
                    <span class="dash__cta-text">Novo Lead</span>
                </Link>
            </div>
        </template>

        <div class="dash">
            <div class="dash__stats">
                <StatCard
                    title="Leads Ativos"
                    :value="stats?.leads || 0"
                    icon="fa-users"
                    trend="vs mês anterior"
                    trend-value="+12.5%"
                    :trend-up="true"
                />
                <StatCard
                    title="Páginas CMS"
                    :value="stats?.pages || 0"
                    icon="fa-file-alt"
                    trend="vs mês anterior"
                    trend-value="+8.3%"
                    :trend-up="true"
                />
                <StatCard
                    title="Posts Blog"
                    :value="stats?.posts || 0"
                    icon="fa-blog"
                    trend="vs mês anterior"
                    trend-value="+15.7%"
                    :trend-up="true"
                />
                <StatCard
                    title="Mensagens"
                    :value="stats?.messages || 0"
                    icon="fa-comments"
                    trend="não lidas"
                    trend-value="-5.2%"
                    :trend-up="false"
                />
            </div>

            <div class="dash__card">
                <div class="section-header">
                    <div class="section-header__icon section-header__icon--accent">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <h2 class="section-header__title">Ações Rápidas</h2>
                </div>

                <div class="quick-grid">
                    <Link
                        v-for="action in quickActions"
                        :key="action.name"
                        :href="action.href"
                        class="quick-card"
                    >
                        <span class="quick-card__highlight"></span>
                        <span class="quick-card__icon"><i :class="`fas ${action.icon}`"></i></span>
                        <span class="quick-card__label">{{ action.name }}</span>
                    </Link>
                </div>
            </div>

            <div class="dash__grid">
                <div class="dash__panel dash__panel--wide">
                    <div class="section-header">
                        <div class="section-header__icon section-header__icon--accent">
                            <i class="fas fa-history"></i>
                        </div>
                        <h2 class="section-header__title">Timeline</h2>
                    </div>

                    <div class="timeline">
                        <div
                            v-for="activity in recentActivities"
                            :key="activity.id"
                            class="timeline__item"
                        >
                            <div class="timeline__dot"></div>
                            <div class="timeline__icon">
                                <i :class="`${activity.icon}`"></i>
                            </div>
                            <div class="timeline__content">
                                <div class="timeline__title">{{ activity.title }}</div>
                                <div class="timeline__time">{{ activity.time }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="dash__panel">
                    <div class="section-header">
                        <div class="section-header__icon section-header__icon--accent">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h2 class="section-header__title">Métricas</h2>
                    </div>

                    <div class="metrics">
                        <i class="fas fa-chart-bar metrics__icon"></i>
                        <p class="metrics__title">Em Desenvolvimento</p>
                        <p class="metrics__subtitle">Analytics Module v2.0</p>
                    </div>
                </div>
            </div>

            <div class="status-bar">
                <div class="status-bar__left">
                    <span class="status-bar__dot"></span>
                    <div>
                        <p class="status-bar__title">Sistema Operacional</p>
                        <p class="status-bar__subtitle">Todos os serviços funcionando normalmente</p>
                    </div>
                </div>
                <div class="status-bar__right">
                    <p class="status-bar__subtitle">Uptime: 99.98%</p>
                </div>
            </div>
        </div>
    </MainLayout>
</template>

<style scoped>
.dash {
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 32px;
    width: 100%;
}

.dash__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 16px;
}

.dash__title {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 48px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: -0.02em;
    color: #111827;
    line-height: 1;
}

.dash__subtitle {
    margin-top: 8px;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.12em;
    font-family: 'JetBrains Mono', monospace;
    color: #6b7280;
}

.dash__cta {
    position: relative;
    display: inline-flex;
    align-items: center;
    gap: 12px;
    padding: 14px 18px;
    border: 2px solid #e5e7eb;
    background: #fff;
    overflow: hidden;
    text-transform: uppercase;
    font-weight: 700;
    font-size: 13px;
    letter-spacing: 0.06em;
    color: #111827;
    transition: transform 180ms ease, border-color 180ms ease;
}

.dash__cta:hover {
    border-color: #ff6b35;
    transform: translateX(4px);
}

.dash__cta-glow {
    position: absolute;
    inset: 0;
    background: #ff6b35;
    opacity: 0;
    transition: opacity 180ms ease;
}

.dash__cta:hover .dash__cta-glow { opacity: 0.14; }

.dash__cta-icon {
    position: relative;
    z-index: 1;
    width: 40px;
    height: 40px;
    border: 2px solid #e5e7eb;
    background: #f9fafb;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #111827;
}

.dash__cta-text { position: relative; z-index: 1; font-family: 'Space Grotesk', sans-serif; }

.dash__stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 24px;
}

.dash__card {
    background: #fff;
    border: 2px solid #e5e7eb;
    padding: 32px;
}

.section-header {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 24px;
}

.section-header__icon {
    width: 42px;
    height: 42px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #f3f4f6;
    color: #111827;
}

.section-header__icon--accent { background: #ff6b35; color: #fff; }

.section-header__title {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 24px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: -0.01em;
    color: #111827;
}

.quick-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
}

.quick-card {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 16px;
    padding: 24px;
    border: 2px solid #e5e7eb;
    background: #fff;
    overflow: hidden;
    transition: transform 180ms ease, border-color 180ms ease;
}

.quick-card__highlight {
    position: absolute;
    inset: 0;
    background: #ff6b35;
    opacity: 0;
    transition: opacity 180ms ease;
}

.quick-card:hover {
    transform: translateX(4px);
    border-color: #ff6b35;
}

.quick-card:hover .quick-card__highlight { opacity: 0.12; }

.quick-card__icon {
    position: relative;
    z-index: 1;
    width: 56px;
    height: 56px;
    border: 2px solid #e5e7eb;
    background: #f9fafb;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    color: #111827;
}

.quick-card__label {
    position: relative;
    z-index: 1;
    font-family: 'Space Grotesk', sans-serif;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    font-size: 15px;
    color: #111827;
}

.dash__grid {
    display: grid;
    grid-template-columns: 1.75fr 1fr;
    gap: 24px;
}

.dash__panel {
    background: #fff;
    border: 2px solid #e5e7eb;
    padding: 32px;
}

.dash__panel--wide { grid-column: span 1; }

.timeline {
    position: relative;
    display: flex;
    flex-direction: column;
    gap: 18px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 22px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e5e7eb;
}

.timeline__item {
    position: relative;
    padding-left: 56px;
    padding-top: 6px;
    padding-bottom: 6px;
}

.timeline__dot {
    position: absolute;
    left: 16px;
    top: 14px;
    width: 10px;
    height: 10px;
    background: #ff6b35;
    border: 2px solid #fff;
    border-radius: 50%;
    box-shadow: 0 0 0 2px #e5e7eb;
}

.timeline__icon {
    position: absolute;
    left: 0;
    top: 0;
    width: 44px;
    height: 44px;
    border: 2px solid #e5e7eb;
    background: #f9fafb;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6b7280;
    transition: all 150ms ease;
}

.timeline__content { display: flex; flex-direction: column; gap: 4px; }
.timeline__title { font-weight: 700; color: #111827; font-size: 14px; }
.timeline__time { font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.08em; text-transform: uppercase; color: #9ca3af; }

.timeline__item:hover .timeline__icon { background: #ff6b35; color: #fff; border-color: #ff6b35; transform: translateY(-2px); }

.metrics {
    border: 2px dashed #e5e7eb;
    height: 320px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.metrics__icon { font-size: 56px; color: #d1d5db; }
.metrics__title {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 18px;
    font-weight: 700;
    text-transform: uppercase;
    color: #111827;
}

.metrics__subtitle {
    font-family: 'JetBrains Mono', monospace;
    font-size: 13px;
    text-transform: uppercase;
    color: #9ca3af;
}

.status-bar {
    background: #fff;
    border: 2px solid #e5e7eb;
    padding: 16px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}

.status-bar__left { display: flex; align-items: center; gap: 12px; }
.status-bar__right { text-align: right; }

.status-bar__dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: #10b981;
    box-shadow: 0 0 0 6px rgba(16, 185, 129, 0.18);
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

.status-bar__title {
    margin: 0;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #111827;
    font-size: 14px;
}

.status-bar__subtitle {
    margin: 2px 0 0;
    font-family: 'JetBrains Mono', monospace;
    font-size: 12px;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: #6b7280;
}

@media (max-width: 1100px) {
    .dash__grid { grid-template-columns: 1fr; }
}

@media (max-width: 768px) {
    .dash__header { flex-direction: column; align-items: flex-start; }
    .dash__cta { width: 100%; justify-content: center; }
}
</style>
