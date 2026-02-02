<script setup>
import { ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const showUserMenu = ref(false);

const user = page.props.auth.user;

const navigation = [
    { name: 'Dashboard', href: '/test-dashboard', icon: 'fas fa-home' },
    { name: 'Leads', href: '/leads', icon: 'fas fa-users' },
    { name: 'CMS', href: '/cms', icon: 'fas fa-file-alt' },
    { name: 'Social', href: '/social', icon: 'fas fa-comments' },
];
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Navbar -->
        <nav class="bg-white border-b border-gray-200">
            <div class="mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <!-- Logo e Menu -->
                    <div class="flex">
                        <!-- Logo -->
                        <Link href="/test-dashboard" class="flex items-center px-2 space-x-2">
                            <i class="fas fa-rocket text-2xl text-[#1160b7]"></i>
                            <span class="text-2xl font-bold text-[#002050]">CRM Makin</span>
                        </Link>

                        <!-- Navigation Links -->
                        <div class="hidden sm:ml-6 sm:flex sm:space-x-4">
                            <Link
                                v-for="item in navigation"
                                :key="item.name"
                                :href="item.href"
                                class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 hover:text-[#1160b7] hover:bg-[#dfe2e8] rounded-md transition-colors"
                            >
                                <i :class="item.icon" class="mr-2"></i>
                                {{ item.name }}
                            </Link>
                        </div>
                    </div>

                    <!-- User Menu -->
                    <div class="flex items-center">
                        <div class="relative">
                            <button
                                @click="showUserMenu = !showUserMenu"
                                class="flex items-center space-x-3 focus:outline-none"
                            >
                                <span class="text-sm text-gray-700">{{ user?.name || 'Usuário' }}</span>
                                <div class="h-8 w-8 rounded-full bg-[#1160b7] flex items-center justify-center text-white font-semibold">
                                    {{ (user?.name || 'U').charAt(0).toUpperCase() }}
                                </div>
                            </button>

                            <!-- Dropdown -->
                            <div
                                v-show="showUserMenu"
                                @click="showUserMenu = false"
                                class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 ring-1 ring-black ring-opacity-5 z-10"
                            >
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Perfil</a>
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Configurações</a>
                                <hr class="my-1">
                                <a href="#" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100">Sair</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="py-6">
            <div class="mx-auto px-4 sm:px-6 lg:px-8">
                <slot />
            </div>
        </main>
    </div>
</template>
