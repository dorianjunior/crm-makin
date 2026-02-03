<script setup>
import { ref, computed, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Sidebar from './Sidebar.vue';
import Navbar from './Navbar.vue';

const props = defineProps({
    title: String,
});

const page = usePage();
const sidebarOpen = ref(true);
const darkMode = ref(false);

const user = computed(() => page.props.auth?.user);

const toggleSidebar = () => {
    sidebarOpen.value = !sidebarOpen.value;
};

const toggleDarkMode = () => {
    darkMode.value = !darkMode.value;
    localStorage.setItem('darkMode', darkMode.value.toString());
    applyTheme();
};

const applyTheme = () => {
    if (darkMode.value) {
        document.documentElement.classList.add('dark');
        document.documentElement.setAttribute('data-theme', 'dark');
    } else {
        document.documentElement.classList.remove('dark');
        document.documentElement.setAttribute('data-theme', 'light');
    }
};

// Aplicar tema no carregamento
onMounted(() => {
    const savedTheme = localStorage.getItem('darkMode');
    darkMode.value = savedTheme === 'true';
    applyTheme();
});
</script>

<template>
    <div class="min-h-screen bg-secondary">
        <!-- Sidebar -->
        <Sidebar
            :open="sidebarOpen"
            @toggle="toggleSidebar"
        />

        <!-- Main Content -->
        <div
            :class="{ 'lg:ml-64': sidebarOpen, 'lg:ml-20': !sidebarOpen }"
            class="transition-all duration-300 min-h-screen"
        >
            <!-- Navbar -->
            <Navbar
                :user="user"
                :dark-mode="darkMode"
                @toggle-sidebar="toggleSidebar"
                @toggle-dark-mode="toggleDarkMode"
            />

            <!-- Page Content -->
            <main class="p-6 pt-20 relative z-0">
                <!-- Breadcrumbs -->
                <div v-if="$slots.breadcrumbs" class="mb-4">
                    <slot name="breadcrumbs" />
                </div>

                <!-- Page Header -->
                <div v-if="title || $slots.header" class="mb-6">
                    <slot name="header">
                        <h1 class="text-2xl font-bold text-primary">
                            {{ title }}
                        </h1>
                    </slot>
                </div>

                <!-- Main Content -->
                <div class="bg-primary rounded-lg shadow-sm p-6">
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>

<style scoped lang="scss">
/* Scrollbar customizado */
main {
    @include custom-scrollbar;
}
</style>
