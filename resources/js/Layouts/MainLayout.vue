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

<!-- Styles moved to resources/scss/_layout-brutalist.scss -->

