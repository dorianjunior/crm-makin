<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';

const props = defineProps({
    user: Object,
    darkMode: Boolean,
});

const emit = defineEmits(['toggle-sidebar', 'toggle-dark-mode']);

const showUserMenu = ref(false);
const showNotifications = ref(false);

const notifications = ref([
    { id: 1, title: 'Novo lead cadastrado', time: '5 min atrás', icon: 'fa-user-plus', color: 'blue' },
    { id: 2, title: 'Proposta aprovada', time: '1 hora atrás', icon: 'fa-check-circle', color: 'green' },
    { id: 3, title: 'Mensagem do Instagram', time: '2 horas atrás', icon: 'fa-instagram', color: 'purple' },
]);

const logout = () => {
    router.post('/logout');
};
</script>

<template>
    <nav class="fixed top-0 right-0 left-0 lg:left-64 z-40 bg-primary border-b border-color transition-all duration-300">
        <div class="px-4 py-3">
            <div class="flex items-center justify-between">
                <!-- Mobile Menu Button & Search -->
                <div class="flex items-center space-x-4">
                    <button
                        @click="emit('toggle-sidebar')"
                        class="lg:hidden p-2 text-secondary rounded-lg hover:bg-tertiary"
                    >
                        <i class="fas fa-bars"></i>
                    </button>

                    <!-- Search Bar -->
                    <div class="hidden md:block">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <i class="fas fa-search text-gray-400 text-xs"></i>
                            </div>
                            <input
                                type="text"
                                placeholder="Buscar leads, páginas, conversas..."
                                class="w-80 pl-9 pr-4 py-2 text-sm bg-secondary border border-color rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-primary placeholder:text-gray-400"
                            />
                        </div>
                    </div>
                </div>

                <!-- Right Side Actions -->
                <div class="flex items-center space-x-3">
                    <!-- Dark Mode Toggle -->
                    <button
                        @click="emit('toggle-dark-mode')"
                        class="p-2 text-secondary rounded-lg hover:bg-tertiary transition-colors"
                        :title="darkMode ? 'Modo Claro' : 'Modo Escuro'"
                    >
                        <i :class="darkMode ? 'fa-sun' : 'fa-moon'" class="fas"></i>
                    </button>

                    <!-- Notifications -->
                    <div class="relative">
                        <button
                            @click="showNotifications = !showNotifications"
                            class="relative p-2 text-secondary rounded-lg hover:bg-tertiary transition-colors"
                        >
                            <i class="fas fa-bell"></i>
                            <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                        </button>

                        <!-- Notifications Dropdown -->
                        <transition
                            enter-active-class="transition ease-out duration-100"
                            enter-from-class="transform opacity-0 scale-95"
                            enter-to-class="transform opacity-100 scale-100"
                            leave-active-class="transition ease-in duration-75"
                            leave-from-class="transform opacity-100 scale-100"
                            leave-to-class="transform opacity-0 scale-95"
                        >
                            <div
                                v-show="showNotifications"
                                @click.away="showNotifications = false"
                                class="absolute right-0 mt-2 w-80 bg-primary rounded-lg shadow-lg border border-color overflow-hidden"
                            >
                                <!-- Header -->
                                <div class="px-4 py-3 border-b border-color">
                                    <div class="flex items-center justify-between">
                                        <h3 class="font-semibold text-primary">Notificações</h3>
                                        <Link
                                            href="/notifications"
                                            class="text-xs text-blue-600 hover:text-blue-700"
                                        >
                                            Ver todas
                                        </Link>
                                    </div>
                                </div>

                                <!-- Notifications List -->
                                <div class="max-h-96 overflow-y-auto">
                                    <Link
                                        v-for="notification in notifications"
                                        :key="notification.id"
                                        href="#"
                                        class="block px-4 py-3 hover:bg-tertiary transition-colors border-b border-color last:border-0"
                                    >
                                        <div class="flex items-start space-x-3">
                                            <div
                                                :class="[
                                                    'flex items-center justify-center w-10 h-10 rounded-full',
                                                    {
                                                        'bg-blue-100 text-blue-600': notification.color === 'blue',
                                                        'bg-green-100 text-green-600': notification.color === 'green',
                                                        'bg-purple-100 text-purple-600': notification.color === 'purple',
                                                    }
                                                ]"
                                            >
                                                <i :class="`fas ${notification.icon} text-sm`"></i>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-primary">
                                                    {{ notification.title }}
                                                </p>
                                                <p class="text-xs text-tertiary">
                                                    {{ notification.time }}
                                                </p>
                                            </div>
                                        </div>
                                    </Link>
                                </div>
                            </div>
                        </transition>
                    </div>

                    <!-- User Menu -->
                    <div class="relative">
                        <button
                            @click="showUserMenu = !showUserMenu"
                            class="flex items-center space-x-3 p-2 rounded-lg hover:bg-tertiary transition-colors"
                        >
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-600 to-blue-800 flex items-center justify-center text-white font-semibold">
                                {{ user?.name?.charAt(0)?.toUpperCase() || 'U' }}
                            </div>
                            <div class="hidden md:block text-left">
                                <p class="text-sm font-medium text-primary">
                                    {{ user?.name || 'Usuário' }}
                                </p>
                                <p class="text-xs text-tertiary">
                                    {{ user?.email || 'email@example.com' }}
                                </p>
                            </div>
                            <i class="fas fa-chevron-down text-xs text-secondary"></i>
                        </button>

                        <!-- User Dropdown -->
                        <transition
                            enter-active-class="transition ease-out duration-100"
                            enter-from-class="transform opacity-0 scale-95"
                            enter-to-class="transform opacity-100 scale-100"
                            leave-active-class="transition ease-in duration-75"
                            leave-from-class="transform opacity-100 scale-100"
                            leave-to-class="transform opacity-0 scale-95"
                        >
                            <div
                                v-show="showUserMenu"
                                @click.away="showUserMenu = false"
                                class="absolute right-0 mt-2 w-56 bg-primary rounded-lg shadow-lg border border-color overflow-hidden"
                            >
                                <div class="px-4 py-3 border-b border-color">
                                    <p class="text-sm font-medium text-primary">
                                        {{ user?.name }}
                                    </p>
                                    <p class="text-xs text-tertiary truncate">
                                        {{ user?.email }}
                                    </p>
                                </div>

                                <div class="py-2">
                                    <Link
                                        href="/profile"
                                        class="flex items-center px-4 py-2 text-sm text-secondary hover:bg-tertiary"
                                    >
                                        <i class="fas fa-user w-5"></i>
                                        <span class="ml-3">Meu Perfil</span>
                                    </Link>
                                    <Link
                                        href="/settings"
                                        class="flex items-center px-4 py-2 text-sm text-secondary hover:bg-tertiary"
                                    >
                                        <i class="fas fa-cog w-5"></i>
                                        <span class="ml-3">Configurações</span>
                                    </Link>
                                    <Link
                                        href="/help"
                                        class="flex items-center px-4 py-2 text-sm text-secondary hover:bg-tertiary"
                                    >
                                        <i class="fas fa-question-circle w-5"></i>
                                        <span class="ml-3">Ajuda</span>
                                    </Link>
                                </div>

                                <div class="border-t border-color">
                                    <button
                                        @click="logout"
                                        class="w-full flex items-center px-4 py-2 text-sm text-red-600 hover:bg-tertiary"
                                    >
                                        <i class="fas fa-sign-out-alt w-5"></i>
                                        <span class="ml-3">Sair</span>
                                    </button>
                                </div>
                            </div>
                        </transition>
                    </div>
                </div>
            </div>
        </div>
    </nav>
</template>
