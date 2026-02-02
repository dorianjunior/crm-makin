<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';

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
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <Card>
                    <template #title>
                        <div class="flex items-center justify-between">
                            <span class="text-lg">Leads</span>
                            <i class="pi pi-users text-blue-500 text-2xl"></i>
                        </div>
                    </template>
                    <template #content>
                        <p class="text-3xl font-bold text-gray-900">{{ stats?.leads || 0 }}</p>
                        <p class="text-sm text-gray-500 mt-1">Total de leads</p>
                    </template>
                </Card>

                <Card>
                    <template #title>
                        <div class="flex items-center justify-between">
                            <span class="text-lg">Páginas</span>
                            <i class="pi pi-file text-green-500 text-2xl"></i>
                        </div>
                    </template>
                    <template #content>
                        <p class="text-3xl font-bold text-gray-900">{{ stats?.pages || 0 }}</p>
                        <p class="text-sm text-gray-500 mt-1">Páginas criadas</p>
                    </template>
                </Card>

                <Card>
                    <template #title>
                        <div class="flex items-center justify-between">
                            <span class="text-lg">Posts</span>
                            <i class="pi pi-book text-purple-500 text-2xl"></i>
                        </div>
                    </template>
                    <template #content>
                        <p class="text-3xl font-bold text-gray-900">{{ stats?.posts || 0 }}</p>
                        <p class="text-sm text-gray-500 mt-1">Posts publicados</p>
                    </template>
                </Card>

                <Card>
                    <template #title>
                        <div class="flex items-center justify-between">
                            <span class="text-lg">Mensagens</span>
                            <i class="pi pi-comments text-orange-500 text-2xl"></i>
                        </div>
                    </template>
                    <template #content>
                        <p class="text-3xl font-bold text-gray-900">{{ stats?.messages || 0 }}</p>
                        <p class="text-sm text-gray-500 mt-1">Não lidas</p>
                    </template>
                </Card>
            </div>

            <!-- Quick Actions -->
            <Card>
                <template #title>Ações Rápidas</template>
                <template #content>
                    <div class="flex flex-wrap gap-3">
                        <Button label="Novo Lead" icon="pi pi-plus" />
                        <Button label="Nova Página" icon="pi pi-file-plus" severity="secondary" />
                        <Button label="Novo Post" icon="pi pi-pencil" severity="secondary" />
                        <Button label="Ver Mensagens" icon="pi pi-envelope" severity="secondary" />
                    </div>
                </template>
            </Card>

            <!-- Recent Activity -->
            <Card>
                <template #title>Atividade Recente</template>
                <template #content>
                    <div class="text-center py-8 text-gray-500">
                        <i class="pi pi-inbox text-4xl mb-3"></i>
                        <p>Nenhuma atividade recente</p>
                    </div>
                </template>
            </Card>
        </div>
    </AppLayout>
</template>
