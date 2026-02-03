<script setup>
import { Head, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import StatCard from '@/Components/StatCard.vue';
import Alert from '@/Components/Alert.vue';

defineProps({
    stats: Object,
});

const quickActions = [
    { name: 'Novo Lead', icon: 'fa-user-plus', href: '/leads/create', color: 'blue' },
    { name: 'Nova Página', icon: 'fa-file-alt', href: '/pages/create', color: 'green' },
    { name: 'Novo Post', icon: 'fa-edit', href: '/posts/create', color: 'purple' },
    { name: 'Ver Mensagens', icon: 'fa-envelope', href: '/whatsapp', color: 'orange' },
];

const recentActivities = [
    { id: 1, type: 'lead', title: 'Novo lead cadastrado: João Silva', time: '5 min atrás', icon: 'fa-user-plus', color: 'blue' },
    { id: 2, type: 'proposal', title: 'Proposta #1234 aprovada', time: '1 hora atrás', icon: 'fa-check-circle', color: 'green' },
    { id: 3, type: 'message', title: 'Nova mensagem do Instagram', time: '2 horas atrás', icon: 'fa-instagram', color: 'purple' },
    { id: 4, type: 'page', title: 'Página "Sobre" publicada', time: '3 horas atrás', icon: 'fa-file-alt', color: 'blue' },
];
</script>

<template>
    <Head title="Dashboard" />

    <MainLayout title="Dashboard">
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-primary">Dashboard</h1>
                    <p class="mt-1 text-secondary">Bem-vindo ao CRM Makin 🚀</p>
                </div>
                <div class="flex space-x-3">
                    <Link
                        href="/leads/create"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors"
                    >
                        <i class="fas fa-plus mr-2"></i>
                        Novo Lead
                    </Link>
                </div>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <StatCard
                    title="Leads"
                    :value="stats?.leads || 0"
                    icon="fa-users"
                    icon-color="blue"
                    trend="vs mês anterior"
                    trend-value="+12.5%"
                    :trend-up="true"
                />
                <StatCard
                    title="Páginas CMS"
                    :value="stats?.pages || 0"
                    icon="fa-file-alt"
                    icon-color="green"
                    trend="vs mês anterior"
                    trend-value="+8.3%"
                    :trend-up="true"
                />
                <StatCard
                    title="Posts"
                    :value="stats?.posts || 0"
                    icon="fa-blog"
                    icon-color="purple"
                    trend="vs mês anterior"
                    trend-value="+15.7%"
                    :trend-up="true"
                />
                <StatCard
                    title="Mensagens"
                    :value="stats?.messages || 0"
                    icon="fa-comments"
                    icon-color="red"
                    trend="não lidas"
                    trend-value="-5.2%"
                    :trend-up="false"
                />
            </div>

            <!-- Welcome Alert -->
            <Alert variant="info">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="font-semibold mb-1">Bem-vindo ao CRM Makin!</h3>
                        <p class="text-sm">
                            Seu sistema está configurado e pronto para uso. Explore as funcionalidades através do menu lateral.
                        </p>
                    </div>
                </div>
            </Alert>

            <!-- Quick Actions -->
            <div class="bg-primary rounded-lg shadow-sm p-6 border border-color">
                <h2 class="text-xl font-semibold text-primary mb-4 flex items-center">
                    <i class="fas fa-bolt mr-2 text-blue-600"></i>
                    Ações Rápidas
                </h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <Link
                        v-for="action in quickActions"
                        :key="action.name"
                        :href="action.href"
                        class="flex flex-col items-center justify-center p-6 bg-secondary rounded-lg hover:bg-tertiary transition-colors group"
                    >
                        <div
                            :class="[
                                'w-12 h-12 rounded-full flex items-center justify-center mb-3 transition-transform group-hover:scale-110',
                                {
                                    'bg-blue-100 text-blue-600': action.color === 'blue',
                                    'bg-green-100 text-green-600': action.color === 'green',
                                    'bg-purple-100 text-purple-600': action.color === 'purple',
                                    'bg-orange-100 text-orange-600': action.color === 'orange',
                                }
                            ]"
                        >
                            <i :class="`fas ${action.icon} text-xl`"></i>
                        </div>
                        <span class="text-sm font-medium text-primary text-center">
                            {{ action.name }}
                        </span>
                    </Link>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Recent Activity -->
                <div class="bg-primary rounded-lg shadow-sm p-6 border border-color">
                    <h2 class="text-xl font-semibold text-primary mb-4 flex items-center">
                        <i class="fas fa-history mr-2 text-blue-600"></i>
                        Atividade Recente
                    </h2>
                    <div class="space-y-4">
                        <div
                            v-for="activity in recentActivities"
                            :key="activity.id"
                            class="flex items-start space-x-3 p-3 rounded-lg hover:bg-secondary transition-colors"
                        >
                            <div
                                :class="[
                                    'flex items-center justify-center w-10 h-10 rounded-full flex-shrink-0',
                                    {
                                        'bg-blue-100 text-blue-600': activity.color === 'blue',
                                        'bg-green-100 text-green-600': activity.color === 'green',
                                        'bg-purple-100 text-purple-600': activity.color === 'purple',
                                    }
                                ]"
                            >
                                <i :class="`fas ${activity.icon}`"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-primary">
                                    {{ activity.title }}
                                </p>
                                <p class="text-xs text-tertiary">
                                    {{ activity.time }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Performance Chart Placeholder -->
                <div class="bg-primary rounded-lg shadow-sm p-6 border border-color">
                    <h2 class="text-xl font-semibold text-primary mb-4 flex items-center">
                        <i class="fas fa-chart-line mr-2 text-blue-600"></i>
                        Performance
                    </h2>
                    <div class="flex items-center justify-center h-64 bg-secondary rounded-lg">
                        <div class="text-center text-tertiary">
                            <i class="fas fa-chart-bar text-6xl mb-4"></i>
                            <p class="text-lg">Gráfico em desenvolvimento</p>
                            <p class="text-sm">Em breve: Analytics e métricas</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </MainLayout>
</template>
