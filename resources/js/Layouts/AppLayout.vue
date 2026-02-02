<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import Menubar from 'primevue/menubar';
import Avatar from 'primevue/avatar';
import Menu from 'primevue/menu';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';

const page = usePage();
const toast = useToast();
const userMenu = ref();

const user = computed(() => page.props.auth.user);

const menuItems = ref([
    {
        label: 'Dashboard',
        icon: 'pi pi-home',
        to: '/dashboard',
    },
    {
        label: 'Leads',
        icon: 'pi pi-users',
        to: '/leads',
    },
    {
        label: 'CMS',
        icon: 'pi pi-file-edit',
        items: [
            { label: 'Sites', icon: 'pi pi-globe', to: '/cms/sites' },
            { label: 'Páginas', icon: 'pi pi-file', to: '/cms/pages' },
            { label: 'Posts', icon: 'pi pi-book', to: '/cms/posts' },
            { label: 'Menus', icon: 'pi pi-bars', to: '/cms/menus' },
        ],
    },
    {
        label: 'Social',
        icon: 'pi pi-comments',
        items: [
            { label: 'Instagram', icon: 'pi pi-instagram', to: '/social/instagram' },
            { label: 'WhatsApp', icon: 'pi pi-whatsapp', to: '/social/whatsapp' },
        ],
    },
]);

const userMenuItems = ref([
    {
        label: 'Perfil',
        icon: 'pi pi-user',
        command: () => {
            // Navigate to profile
        },
    },
    {
        label: 'Configurações',
        icon: 'pi pi-cog',
        command: () => {
            // Navigate to settings
        },
    },
    {
        separator: true,
    },
    {
        label: 'Sair',
        icon: 'pi pi-sign-out',
        command: () => {
            // Logout
        },
    },
]);

const toggleUserMenu = (event) => {
    userMenu.value.toggle(event);
};
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <Toast />

        <!-- Navbar -->
        <Menubar :model="menuItems" class="border-b border-gray-200">
            <template #start>
                <Link href="/dashboard" class="flex items-center gap-2 mr-4">
                    <i class="pi pi-bolt text-primary text-2xl"></i>
                    <span class="text-xl font-bold text-gray-800">CRM Makin</span>
                </Link>
            </template>

            <template #item="{ item, props }">
                <Link v-if="item.to" :href="item.to" v-bind="props.action" class="flex items-center gap-2">
                    <i :class="item.icon"></i>
                    <span>{{ item.label }}</span>
                </Link>
                <a v-else v-bind="props.action" class="flex items-center gap-2">
                    <i :class="item.icon"></i>
                    <span>{{ item.label }}</span>
                </a>
            </template>

            <template #end>
                <div class="flex items-center gap-3">
                    <span class="text-sm text-gray-600">{{ user?.name }}</span>
                    <Avatar
                        :label="user?.name.charAt(0).toUpperCase()"
                        class="cursor-pointer bg-primary text-white"
                        shape="circle"
                        @click="toggleUserMenu"
                    />
                    <Menu ref="userMenu" :model="userMenuItems" popup />
                </div>
            </template>
        </Menubar>

        <!-- Main Content -->
        <main class="container mx-auto p-6">
            <slot />
        </main>
    </div>
</template>

<style scoped>
/* PrimeVue custom styles if needed */
</style>
