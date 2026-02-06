<script setup>
import { ref, computed } from 'vue';
import { usePage, Head } from '@inertiajs/vue3';
import { useTheme } from '@/composables/useTheme';
import Sidebar from './Sidebar.vue';
import Navbar from './Navbar.vue';

const props = defineProps({
    title: String,
    noWrapper: {
        type: Boolean,
        default: false,
    },
});

const page = usePage();
const sidebarOpen = ref(true);
const { isDark, toggleTheme } = useTheme();

const user = computed(() => page.props.auth?.user);

const toggleSidebar = () => {
    sidebarOpen.value = !sidebarOpen.value;
};
</script>

<template>
    <Head :title="title" />
    <div class="layout-root">
        <!-- Sidebar -->
        <Sidebar
            :open="sidebarOpen"
            @toggle="toggleSidebar"
        />

        <!-- Main Content -->
        <div
            :class="[
                'layout-shell',
                sidebarOpen ? 'layout-shell--open' : 'layout-shell--closed'
            ]"
        >
            <!-- Navbar -->
            <Navbar
                :user="user"
                :dark-mode="isDark()"
                :sidebar-open="sidebarOpen"
                @toggle-sidebar="toggleSidebar"
                @toggle-dark-mode="toggleTheme"
            />

            <!-- Page Content -->
            <main class="layout-main">
                <!-- Breadcrumbs -->
                <div v-if="$slots.breadcrumbs" class="layout-breadcrumbs">
                    <slot name="breadcrumbs" />
                </div>

                <!-- Page Header -->
                <div v-if="title || $slots.header" class="layout-header">
                    <slot name="header">
                        <h1 class="layout-title">
                            {{ title }}
                        </h1>
                    </slot>
                </div>

                <!-- Main Content -->
                <div v-if="!noWrapper" class="layout-content">
                    <slot />
                </div>
                <div v-else>
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>

<style scoped>
/* Layout metrics */
:global(:root) {
    --sidebar-open: 16rem;   /* 256px */
    --sidebar-closed: 5rem;  /* 80px */
    --navbar-height: 64px;   /* 4rem */
}


.layout-root {
    min-height: 100vh;
    background-color: var(--bg-secondary);
    position: relative;
}

.layout-shell {
    min-height: 100vh;
    padding-left: var(--sidebar-open);
    padding-top: calc(var(--navbar-height) + 16px);
    transition: padding 300ms ease;
    width: 100%;
}

.layout-shell--closed {
    padding-left: var(--sidebar-closed);
}

.layout-main {
    position: relative;
    z-index: var(--z-content, 10);
    padding: 0 24px 40px;
    overflow-x: hidden;
}

.layout-breadcrumbs {
    margin-bottom: 16px;
}

.layout-header {
    margin-bottom: 24px;
}

.layout-title {
    font-size: 28px;
    font-weight: 700;
    color: var(--text-primary);
    line-height: 1.2;
}

.layout-content {
    background: var(--bg-primary);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
    padding: 24px;
    border: 1px solid var(--border-color);
}

@media (max-width: 1024px) {
    .layout-shell,
    .layout-shell--closed {
        padding-left: 0;
        padding-top: calc(var(--navbar-height) + 12px);
    }

    .layout-main {
        padding: 0 16px 32px;
    }
}

/* Scrollbar customizado */
main::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

main::-webkit-scrollbar-track {
    background: transparent;
}

main::-webkit-scrollbar-thumb {
    background: #cbd5e0;
    border-radius: 4px;
}

main::-webkit-scrollbar-thumb:hover {
    background: #a0aec0;
}

/* Firefox */
main {
    scrollbar-width: thin;
    scrollbar-color: #cbd5e0 transparent;
}
</style>

