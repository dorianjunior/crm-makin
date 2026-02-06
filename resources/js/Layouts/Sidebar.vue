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
                <div v-if="open" class="section-header">
                    <h3 class="section-title">
                        {{ section.section }}
                    </h3>
                </div>
                <div v-else class="section-divider">
                    <div class="divider-line"></div>
                </div>

                <!-- Menu Items -->
                <div class="menu-items">
                    <Link
                        v-for="item in section.items"
                        :key="item.name"
                        :href="item.href"
                        :class="['menu-link', { 'menu-link--active': isActive(item.href) }]"
                    >
                        <i :class="`${item.icon}`" class="menu-icon"></i>
                        <span v-if="open" class="menu-label">{{ item.name }}</span>
                        <span v-if="open && item.badge" class="menu-badge">
                            {{ item.badge }}
                        </span>
                    </Link>
                </div>
            </div>
        </nav>
    </aside>
</template>

<style scoped>
.sidebar {
    position: fixed;
    top: var(--navbar-height);
    left: 0;
    z-index: 40;
    height: 100vh;
    background: var(--bg-primary);
    border-right: 2px solid var(--border-color);
    transition: all 300ms ease;
}

.sidebar--open {
    width: 256px;
    transform: translateX(0);
}

.sidebar--closed {
    width: 80px;
    transform: translateX(0);
}

.sidebar__nav {
    padding: 24px 16px 80px;
    height: 100%;
    overflow-y: auto;
}

.sidebar__section {
    margin-bottom: 1rem;
}

.section-header {
    margin-bottom: 0.5rem;
    margin-top: 0.5rem;
}

.section-title {
    padding: 0 0.75rem;
    font-size: 9px;
    line-height: 1.1;
    letter-spacing: 0.14em;
    font-weight: 600;
    text-transform: uppercase;
    color: var(--text-tertiary);
}

.section-divider {
    margin-bottom: 0.5rem;
}

.divider-line {
    height: 1px;
    background: var(--border-color);
}

.menu-items {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.menu-link {
    display: flex;
    align-items: center;
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
    font-weight: 500;
    border-radius: 0.5rem;
    transition: all 200ms ease;
    color: var(--text-secondary);
    text-decoration: none;
}

.menu-link:hover {
    background: var(--bg-tertiary);
}

.menu-link--active {
    background: var(--accent-light, #FFB3A0);
    color: var(--accent, #FF6B35);
}

.menu-icon {
    width: 1.25rem;
    text-align: center;
}

.menu-label {
    margin-left: 0.75rem;
    flex: 1;
}

.menu-badge {
    margin-left: auto;
    padding: 0.125rem 0.5rem;
    font-size: 0.75rem;
    font-weight: 500;
    background: #ef4444;
    color: #ffffff;
    border-radius: 9999px;
}

.sidebar__toggle {
    position: absolute;
    top: 16px;
    right: 16px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border: 2px solid var(--border-color);
    background: var(--bg-primary);
    color: var(--text-secondary);
    transition: background-color 150ms ease, color 150ms ease, border-color 150ms ease;
    z-index: 10;
}

.sidebar__toggle:hover {
    background: var(--bg-secondary);
    color: var(--text-primary);
    border-color: var(--border-bold, #262626);
}

.sidebar__toggle--desktop {
    display: none;
}

@media (min-width: 769px) {
    .sidebar__toggle--desktop {
        display: inline-flex;
    }
}

@media (max-width: 768px) {
    .sidebar--closed {
        transform: translateX(-100%);
        width: 256px;
    }

    .sidebar--open {
        width: 256px;
        transform: translateX(0);
    }
}

/* Custom scrollbar */
nav::-webkit-scrollbar {
    width: 6px;
}

nav::-webkit-scrollbar-track {
    background: transparent;
}

nav::-webkit-scrollbar-thumb {
    background: var(--scrollbar-thumb, #d1d5db);
    border-radius: 3px;
}

nav::-webkit-scrollbar-thumb:hover {
    background: var(--scrollbar-thumb-hover, #9ca3af);
}
</style>
