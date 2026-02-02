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
            <div class="border-b border-gray-200 pb-4">
                <h1 class="text-3xl font-bold text-[#002050]">Dashboard</h1>
                <p class="text-gray-600 mt-1">Bem-vindo ao CRM Makin</p>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Leads Card -->
                <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-[#1160b7] hover:shadow-lg transition-shadow">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-[#002050]">Leads</h3>
                        <i class="fas fa-users text-3xl text-[#1160b7]"></i>
                    </div>
                    <p class="text-3xl font-bold text-[#002050]">{{ stats?.leads || 0 }}</p>
                    <p class="text-sm text-gray-500 mt-1">Total de leads</p>
                </div>

                <!-- Pages Card -->
                <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-[#1160b7] hover:shadow-lg transition-shadow">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-[#002050]">Páginas</h3>
                        <i class="fas fa-file-alt text-3xl text-[#1160b7]"></i>
                    </div>
                    <p class="text-3xl font-bold text-[#002050]">{{ stats?.pages || 0 }}</p>
                    <p class="text-sm text-gray-500 mt-1">Páginas criadas</p>
                </div>

                <!-- Posts Card -->
                <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-[#1160b7] hover:shadow-lg transition-shadow">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-[#002050]">Posts</h3>
                        <i class="fas fa-blog text-3xl text-[#1160b7]"></i>
                    </div>
                    <p class="text-3xl font-bold text-[#002050]">{{ stats?.posts || 0 }}</p>
                    <p class="text-sm text-gray-500 mt-1">Posts publicados</p>
                </div>

                <!-- Messages Card -->
                <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-[#d24726] hover:shadow-lg transition-shadow">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-[#002050]">Mensagens</h3>
                        <i class="fas fa-comments text-3xl text-[#d24726]"></i>
                    </div>
                    <p class="text-3xl font-bold text-[#002050]">{{ stats?.messages || 0 }}</p>
                    <p class="text-sm text-gray-500 mt-1">Não lidas</p>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow-md p-6 border border-gray-200">
                <h2 class="text-xl font-semibold text-[#002050] mb-4">
                    <i class="fas fa-bolt mr-2 text-[#1160b7]"></i>
                    Ações Rápidas
                </h2>
                <div class="flex flex-wrap gap-3">
                    <button class="px-4 py-2 bg-[#1160b7] text-white rounded-md hover:bg-[#002050] transition-colors shadow">
                        <i class="fas fa-plus mr-2"></i>
                        Novo Lead
                    </button>
                    <button class="px-4 py-2 bg-[#dfe2e8] text-[#002050] rounded-md hover:bg-[#b1d6f0] transition-colors shadow">
                        <i class="fas fa-file-medical mr-2"></i>
                        Nova Página
                    </button>
                    <button class="px-4 py-2 bg-[#dfe2e8] text-[#002050] rounded-md hover:bg-[#b1d6f0] transition-colors shadow">
                        <i class="fas fa-edit mr-2"></i>
                        Novo Post
                    </button>
                    <button class="px-4 py-2 bg-[#d24726] text-white rounded-md hover:bg-[#b13419] transition-colors shadow">
                        <i class="fas fa-envelope mr-2"></i>
                        Ver Mensagens
                    </button>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="bg-white rounded-lg shadow-md p-6 border border-gray-200">
                <h2 class="text-xl font-semibold text-[#002050] mb-4">
                    <i class="fas fa-history mr-2 text-[#1160b7]"></i>
                    Atividade Recente
                </h2>
                <div class="text-center py-12 text-gray-400">
                    <i class="fas fa-inbox text-6xl mb-4"></i>
                    <p class="text-lg">Nenhuma atividade recente</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
