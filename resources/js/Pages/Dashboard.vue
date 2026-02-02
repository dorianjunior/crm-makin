<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
    stats: Object,
});
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
                <p class="text-gray-600 mt-1">Bem-vindo ao CRM Makin</p>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Leads Card -->
                <div class="bg-white rounded-lg shadow p-6 border border-gray-200">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-700">Leads</h3>
                        <span class="text-3xl">👥</span>
                    </div>
                    <p class="text-3xl font-bold text-gray-900">{{ stats?.leads || 0 }}</p>
                    <p class="text-sm text-gray-500 mt-1">Total de leads</p>
                </div>

                <!-- Pages Card -->
                <div class="bg-white rounded-lg shadow p-6 border border-gray-200">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-700">Páginas</h3>
                        <span class="text-3xl">📄</span>
                    </div>
                    <p class="text-3xl font-bold text-gray-900">{{ stats?.pages || 0 }}</p>
                    <p class="text-sm text-gray-500 mt-1">Páginas criadas</p>
                </div>

                <!-- Posts Card -->
                <div class="bg-white rounded-lg shadow p-6 border border-gray-200">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-700">Posts</h3>
                        <span class="text-3xl">📝</span>
                    </div>
                    <p class="text-3xl font-bold text-gray-900">{{ stats?.posts || 0 }}</p>
                    <p class="text-sm text-gray-500 mt-1">Posts publicados</p>
                </div>

                <!-- Messages Card -->
                <div class="bg-white rounded-lg shadow p-6 border border-gray-200">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-700">Mensagens</h3>
                        <span class="text-3xl">💬</span>
                    </div>
                    <p class="text-3xl font-bold text-gray-900">{{ stats?.messages || 0 }}</p>
                    <p class="text-sm text-gray-500 mt-1">Não lidas</p>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow p-6 border border-gray-200">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Ações Rápidas</h2>
                <div class="flex flex-wrap gap-3">
                    <button class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition">
                        + Novo Lead
                    </button>
                    <button class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition">
                        + Nova Página
                    </button>
                    <button class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition">
                        + Novo Post
                    </button>
                    <button class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition">
                        📧 Ver Mensagens
                    </button>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="bg-white rounded-lg shadow p-6 border border-gray-200">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Atividade Recente</h2>
                <div class="text-center py-12 text-gray-500">
                    <div class="text-6xl mb-4">📭</div>
                    <p class="text-lg">Nenhuma atividade recente</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
