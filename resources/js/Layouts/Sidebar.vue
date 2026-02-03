<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const props = defineProps({
    open: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(['toggle']);

const page = usePage();
const currentRoute = computed(() => page.url);

const menuItems = [
    {
        section: 'Principal',
        items: [
            { name: 'Dashboard', href: '/dashboard', icon: 'fa-chart-line', badge: null },
        ]
    },
    {
        section: 'CRM',
        items: [
            { name: 'Leads', href: '/leads', icon: 'fa-users', badge: null },
            { name: 'Pipelines', href: '/pipelines', icon: 'fa-columns', badge: null },
            { name: 'Atividades', href: '/activities', icon: 'fa-tasks', badge: null },
            { name: 'Tarefas', href: '/tasks', icon: 'fa-check-square', badge: null },
            { name: 'Produtos', href: '/products', icon: 'fa-box', badge: null },
            { name: 'Propostas', href: '/proposals', icon: 'fa-file-invoice-dollar', badge: null },
        ]
    },
    {
        section: 'CMS',
        items: [
            { name: 'Sites', href: '/sites', icon: 'fa-globe', badge: null },
            { name: 'Páginas', href: '/pages', icon: 'fa-file-alt', badge: null },
            { name: 'Posts', href: '/posts', icon: 'fa-blog', badge: null },
            { name: 'Portfólio', href: '/portfolios', icon: 'fa-images', badge: null },
            { name: 'Menus', href: '/menus', icon: 'fa-bars', badge: null },
        ]
    },
    {
        section: 'Social',
        items: [
            { name: 'Instagram', href: '/instagram', icon: 'fa-instagram', badge: null },
            { name: 'WhatsApp', href: '/whatsapp', icon: 'fa-whatsapp', badge: null },
            { name: 'Templates', href: '/message-templates', icon: 'fa-envelope', badge: null },
        ]
    },
    {
        section: 'IA & Automação',
        items: [
            { name: 'Conversas IA', href: '/ai/conversations', icon: 'fa-robot', badge: null },
            { name: 'Prompts', href: '/ai/prompts', icon: 'fa-wand-magic-sparkles', badge: null },
            { name: 'Configurações IA', href: '/ai/settings', icon: 'fa-sliders', badge: null },
        ]
    },
    {
        section: 'Notificações',
        items: [
            { name: 'Notificações', href: '/notifications', icon: 'fa-bell', badge: '5' },
            { name: 'Preferências', href: '/notifications/preferences', icon: 'fa-cog', badge: null },
        ]
    },
    {
        section: 'Relatórios',
        items: [
            { name: 'Relatórios', href: '/reports', icon: 'fa-chart-bar', badge: null },
            { name: 'Dashboards', href: '/dashboards', icon: 'fa-th-large', badge: null },
        ]
    },
    {
        section: 'Configurações',
        items: [
            { name: 'Empresa', href: '/company', icon: 'fa-building', badge: null },
            { name: 'Usuários', href: '/users', icon: 'fa-users-cog', badge: null },
            { name: 'Roles', href: '/roles', icon: 'fa-user-shield', badge: null },
            { name: 'Permissões', href: '/permissions', icon: 'fa-lock', badge: null },
        ]
    },
];

const isActive = (href) => {
    return currentRoute.value.startsWith(href);
};
</script>

<template>
    <aside
        :class="[
            'fixed top-0 left-0 z-50 h-screen transition-all duration-300 bg-primary border-r border-color',
            open ? 'w-64' : 'w-20'
        ]"
    >
        <!-- Logo -->
        <div class="flex items-center justify-between h-16 px-4 border-b border-color">
            <Link href="/dashboard" class="flex items-center space-x-3">
                <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-gradient-to-br from-blue-600 to-blue-800">
                    <i class="fas fa-rocket text-white text-xl"></i>
                </div>
                <span v-if="open" class="text-xl font-bold text-primary">
                    CRM Makin
                </span>
            </Link>

            <button
                @click="emit('toggle')"
                class="p-2 text-secondary rounded-lg hover:bg-tertiary transition-colors"
            >
                <i :class="open ? 'fa-angles-left' : 'fa-angles-right'" class="fas"></i>
            </button>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 p-4 space-y-4 overflow-y-auto h-[calc(100vh-4rem)]">
            <div v-for="section in menuItems" :key="section.section">
                <!-- Section Title -->
                <div v-if="open" class="mb-2 mt-2">
                    <h3 class="px-3 text-[10px] font-semibold text-tertiary uppercase tracking-wider">
                        {{ section.section }}
                    </h3>
                </div>
                <div v-else class="mb-2">
                    <div class="h-px bg-gray-300 dark:bg-gray-600"></div>
                </div>

                <!-- Menu Items -->
                <div class="space-y-1">
                    <Link
                        v-for="item in section.items"
                        :key="item.name"
                        :href="item.href"
                        :class="[
                            'flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-all duration-200',
                            isActive(item.href)
                                ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400'
                                : 'text-secondary hover:bg-tertiary'
                        ]"
                    >
                        <i :class="`fas ${item.icon}`" class="w-5 text-center"></i>
                        <span v-if="open" class="ml-3 flex-1">{{ item.name }}</span>
                        <span
                            v-if="open && item.badge"
                            class="ml-auto px-2 py-0.5 text-xs font-medium bg-red-500 text-white rounded-full"
                        >
                            {{ item.badge }}
                        </span>
                    </Link>
                </div>
            </div>
        </nav>
    </aside>
</template>

<style scoped>
/* Custom scrollbar */
nav::-webkit-scrollbar {
    width: 6px;
}

nav::-webkit-scrollbar-track {
    background: transparent;
}

nav::-webkit-scrollbar-thumb {
    background: #d1d5db;
    border-radius: 3px;
}

nav::-webkit-scrollbar-thumb:hover {
    background: #9ca3af;
}

.dark nav::-webkit-scrollbar-thumb {
    background: #4b5563;
}

.dark nav::-webkit-scrollbar-thumb:hover {
    background: #6b7280;
}
</style>
