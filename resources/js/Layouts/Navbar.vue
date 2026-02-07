<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';

const props = defineProps({
    user: Object,
    darkMode: Boolean,
    sidebarOpen: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(['toggle-sidebar', 'toggle-dark-mode']);

const showUserMenu = ref(false);
const showNotifications = ref(false);

const notifications = ref([
    { id: 1, title: 'Novo lead cadastrado', time: '5 min atrás', icon: 'fas fa-user-plus', color: 'blue' },
    { id: 2, title: 'Proposta aprovada', time: '1 hora atrás', icon: 'fas fa-check-circle', color: 'green' },
    { id: 3, title: 'Mensagem do Instagram', time: '2 horas atrás', icon: 'fab fa-instagram', color: 'purple' },
]);

const logout = () => {
    router.post('/logout');
};
</script>

<template>
    <nav class="navbar" :class="sidebarOpen ? 'navbar--open' : 'navbar--closed'">
        <!-- Fixed Brand in Sidebar Space -->
        <div class="navbar__brand-area" :class="sidebarOpen ? 'navbar__brand-area--open' : 'navbar__brand-area--closed'">
            <Link href="/dashboard" class="navbar__brand">
                <span class="navbar__brand-mark">
                    <i class="fas fa-rocket"></i>
                </span>
                <span v-if="sidebarOpen" class="navbar__brand-text">MAKIN</span>
            </Link>
        </div>

        <div class="navbar__inner">
            <!-- Search Bar -->
            <div class="navbar__left">
                <div class="navbar__search">
                    <div class="navbar__search-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <input
                        type="text"
                        placeholder="Buscar leads, páginas, conversas..."
                        class="navbar__search-input"
                    />
                </div>
            </div>

            <!-- Right Side Actions -->
            <div class="navbar__right">
                <!-- Dark Mode Toggle -->
                <button
                    @click="emit('toggle-dark-mode')"
                    class="navbar__icon-btn"
                    :title="darkMode ? 'Modo Claro' : 'Modo Escuro'"
                >
                    <i :class="darkMode ? 'fas fa-sun' : 'fas fa-moon'"></i>
                </button>

                <!-- Notifications -->
                <div class="navbar__dropdown">
                    <button
                        @click="showNotifications = !showNotifications"
                        class="navbar__icon-btn"
                    >
                        <i class="fas fa-bell"></i>
                        <span class="navbar__badge"></span>
                    </button>

                    <transition
                        enter-active-class="fade-in"
                        enter-from-class="fade-in--from"
                        enter-to-class="fade-in--to"
                        leave-active-class="fade-out"
                        leave-from-class="fade-out--from"
                        leave-to-class="fade-out--to"
                    >
                        <div
                            v-show="showNotifications"
                            @click.away="showNotifications = false"
                            class="dropdown dropdown--right dropdown--wide"
                        >
                            <div class="dropdown__header">
                                <h3>Notificações</h3>
                                <Link href="/notifications" class="dropdown__link">Ver todas</Link>
                            </div>

                            <div class="dropdown__list">
                                <Link
                                    v-for="notification in notifications"
                                    :key="notification.id"
                                    href="#"
                                    class="dropdown__item"
                                >
                                    <div :class="['dropdown__icon', `dropdown__icon--${notification.color}`]">
                                        <i :class="`${notification.icon}`"></i>
                                    </div>
                                    <div class="dropdown__content">
                                        <p class="dropdown__title">{{ notification.title }}</p>
                                        <p class="dropdown__subtitle">{{ notification.time }}</p>
                                    </div>
                                </Link>
                            </div>
                        </div>
                    </transition>
                </div>

                <!-- User Menu -->
                <div class="navbar__dropdown">
                    <button
                        @click="showUserMenu = !showUserMenu"
                        class="navbar__user"
                    >
                        <div class="navbar__avatar">
                            {{ user?.name?.charAt(0)?.toUpperCase() || 'U' }}
                        </div>
                        <div class="navbar__user-info">
                            <p class="navbar__user-name">{{ user?.name || 'Usuário' }}</p>
                            <p class="navbar__user-email">{{ user?.email || 'email@example.com' }}</p>
                        </div>
                        <i class="fas fa-chevron-down"></i>
                    </button>

                    <transition
                        enter-active-class="fade-in"
                        enter-from-class="fade-in--from"
                        enter-to-class="fade-in--to"
                        leave-active-class="fade-out"
                        leave-from-class="fade-out--from"
                        leave-to-class="fade-out--to"
                    >
                        <div
                            v-show="showUserMenu"
                            @click.away="showUserMenu = false"
                            class="dropdown dropdown--right"
                        >
                            <div class="dropdown__header dropdown__header--compact">
                                <p class="dropdown__title">{{ user?.name }}</p>
                                <p class="dropdown__subtitle dropdown__subtitle--truncate">{{ user?.email }}</p>
                            </div>

                            <div class="dropdown__list dropdown__list--flush">
                                <Link href="/profile" class="dropdown__item dropdown__item--row">
                                    <i class="fas fa-user"></i>
                                    <span>Meu Perfil</span>
                                </Link>
                                <Link href="/settings" class="dropdown__item dropdown__item--row">
                                    <i class="fas fa-cog"></i>
                                    <span>Configurações</span>
                                </Link>
                                <Link href="/help" class="dropdown__item dropdown__item--row">
                                    <i class="fas fa-question-circle"></i>
                                    <span>Ajuda</span>
                                </Link>
                            </div>

                            <div class="dropdown__footer">
                                <button @click="logout" class="dropdown__item dropdown__item--row dropdown__item--danger">
                                    <i class="fas fa-sign-out-alt"></i>
                                    <span>Sair</span>
                                </button>
                            </div>
                        </div>
                    </transition>
                </div>

                <!-- Mobile Menu Toggle -->
                <button
                    @click="emit('toggle-sidebar')"
                    class="navbar__icon-btn navbar__icon-btn--mobile"
                    aria-label="Menu"
                >
                    <i class="fas fa-ellipsis-v"></i>
                </button>
            </div>
        </div>
    </nav>
</template>
