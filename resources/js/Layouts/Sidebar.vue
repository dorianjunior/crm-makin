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
            { name: 'Dashboard', href: '/dashboard', icon: 'fas fa-chart-line', badge: null },
        ]
    },
    {
        section: 'CRM',
        items: [
            { name: 'Leads', href: '/leads', icon: 'fas fa-users', badge: null },
            { name: 'Pipelines', href: '/pipelines', icon: 'fas fa-columns', badge: null },
            { name: 'Atividades', href: '/activities', icon: 'fas fa-tasks', badge: null },
            { name: 'Tarefas', href: '/tasks', icon: 'fas fa-check-square', badge: null },
            { name: 'Produtos', href: '/products', icon: 'fas fa-box', badge: null },
            { name: 'Propostas', href: '/proposals', icon: 'fas fa-file-invoice-dollar', badge: null },
        ]
    },
    {
        section: 'CMS',
        items: [
            { name: 'Sites', href: '/sites', icon: 'fas fa-globe', badge: null },
            { name: 'Páginas', href: '/pages', icon: 'fas fa-file-alt', badge: null },
            { name: 'Posts', href: '/posts', icon: 'fas fa-blog', badge: null },
            { name: 'Portfólio', href: '/portfolios', icon: 'fas fa-images', badge: null },
            { name: 'Menus', href: '/menus', icon: 'fas fa-bars', badge: null },
        ]
    },
    {
        section: 'Social',
        items: [
            { name: 'Instagram', href: '/instagram', icon: 'fab fa-instagram', badge: null },
            { name: 'WhatsApp', href: '/whatsapp', icon: 'fab fa-whatsapp', badge: null },
            { name: 'Templates', href: '/message-templates', icon: 'fas fa-envelope', badge: null },
        ]
    },
    {
        section: 'IA & Automação',
        items: [
            { name: 'Conversas IA', href: '/ai/conversations', icon: 'fas fa-robot', badge: null },
            { name: 'Prompts', href: '/ai/prompts', icon: 'fas fa-wand-magic-sparkles', badge: null },
            { name: 'Configurações IA', href: '/ai/settings', icon: 'fas fa-sliders', badge: null },
        ]
    },
    {
        section: 'Notificações',
        items: [
            { name: 'Notificações', href: '/notifications', icon: 'fas fa-bell', badge: '5' },
            { name: 'Preferências', href: '/notifications/preferences', icon: 'fas fa-cog', badge: null },
        ]
    },
    {
        section: 'Relatórios',
        items: [
            { name: 'Relatórios', href: '/reports', icon: 'fas fa-chart-bar', badge: null },
            { name: 'Dashboards', href: '/dashboards', icon: 'fas fa-th-large', badge: null },
        ]
    },
    {
        section: 'Configurações',
        items: [
            { name: 'Empresa', href: '/company', icon: 'fas fa-building', badge: null },
            { name: 'Usuários', href: '/users', icon: 'fas fa-users-cog', badge: null },
            { name: 'Roles', href: '/roles', icon: 'fas fa-user-shield', badge: null },
            { name: 'Permissões', href: '/permissions', icon: 'fas fa-lock', badge: null },
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
            'sidebar',
            open ? 'sidebar--open' : 'sidebar--closed'
        ]"
    >
        <!-- Desktop Toggle Button -->
        <button
            @click="emit('toggle')"
            class="sidebar__toggle sidebar__toggle--desktop"
            aria-label="Alternar sidebar"
        >
            <i :class="['fas', open ? 'fas fa-angles-left' : 'fas fa-angles-right']"></i>
        </button>

        <!-- Navigation -->
        <nav class="sidebar__nav">
            <div v-for="section in menuItems" :key="section.section" class="sidebar__section">
                <!-- Section Title -->
                <div v-if="open" class="sidebar__section-header">
                    <h3 class="sidebar__section-title">
                        {{ section.section }}
                    </h3>
                </div>
                <div v-else class="sidebar__section-divider">
                    <div class="sidebar__divider-line"></div>
                </div>

                <!-- Menu Items -->
                <div class="sidebar__menu-items">
                    <Link
                        v-for="item in section.items"
                        :key="item.name"
                        :href="item.href"
                        :class="['sidebar__menu-link', { 'sidebar__menu-link--active': isActive(item.href) }]"
                    >
                        <i :class="`${item.icon}`" class="sidebar__menu-icon"></i>
                        <span v-if="open" class="sidebar__menu-label">{{ item.name }}</span>
                        <span v-if="open && item.badge" class="sidebar__menu-badge">
                            {{ item.badge }}
                        </span>
                    </Link>
                </div>
            </div>
        </nav>
    </aside>
</template>

<!-- Estilos globais definidos em resources/scss/_sidebar.scss -->
