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

<style scoped>
.navbar {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    height: var(--navbar-height);
    background: var(--bg-primary);
    border-bottom: 2px solid var(--border-color);
    z-index: var(--z-navbar, 30);
    transition: all 300ms ease;
}

.navbar--open {
    padding-left: var(--sidebar-open);
}

.navbar--closed {
    padding-left: var(--sidebar-closed);
}

.navbar__brand-area {
    position: fixed;
    top: 0;
    left: 0;
    height: var(--navbar-height);
    background: var(--bg-primary);
    border-right: 2px solid var(--border-color);
    border-bottom: 2px solid var(--border-color);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 16px;
    transition: width 300ms ease;
    z-index: 50;
}

.navbar__brand-area--open {
    width: 256px;
}

.navbar__brand-area--closed {
    width: 80px;
}

.navbar__brand {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 8px 12px;
    background: var(--bg-primary);
    color: var(--text-primary);
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    line-height: 1.1;
    text-decoration: none;
    transition: all 200ms ease;
}

.navbar__brand-mark {
    width: 36px;
    height: 36px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #ff6b35;
    color: var(--bg-primary);
    font-size: 16px;
    flex-shrink: 0;
}

.navbar__brand-text {
    font-size: 40px;
    font-weight: 800;
    font-family: 'Space Grotesk', sans-serif;
    letter-spacing: 0.12em;
    white-space: nowrap;
}

.navbar__inner {
    margin: 0 auto;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 24px;
    gap: 16px;
}

.navbar__left {
    display: flex;
    align-items: center;
    gap: 12px;
    flex: 1;
}

.navbar__right {
    display: flex;
    align-items: center;
    gap: 10px;
}

.navbar__icon-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    /* border-radius: 6px; */
    border: 2px solid var(--border-color);
    background: var(--bg-primary);
    color: var(--text-secondary);
    transition: background-color 150ms ease, color 150ms ease, border-color 150ms ease;
}

.navbar__icon-btn--ghost {
    background: transparent;
}

.navbar__icon-btn:hover {
    background: var(--bg-secondary);
    color: var(--text-primary);
    border-color: var(--border-bold, #262626);
}

.navbar__icon-btn--mobile {
    display: none;
}

.navbar__search {
    position: relative;
    display: flex;
    align-items: center;
    width: 360px;
}

.navbar__search-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-tertiary);
    font-size: 12px;
    display: flex;
    align-items: center;
}

.navbar__search-input {
    width: 100%;
    height: 42px;
    padding: 10px 14px 10px 36px;
    /* border-radius: 6px; */
    border: 2px solid var(--border-color);
    background: var(--bg-primary);
    color: var(--text-primary);
    font-size: 14px;
    line-height: 1.4;
    transition: border-color 150ms ease, box-shadow 150ms ease;
}

.navbar__search-input::placeholder {
    color: var(--text-tertiary);
}

.navbar__search-input:focus {
    outline: none;
    border-color: var(--color-info);
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.15);
}

.navbar__dropdown { position: relative; }

.navbar__badge {
    position: absolute;
    top: 6px;
    right: 6px;
    width: 9px;
    height: 9px;
    border-radius: 50%;
    background: #ef4444;
}

.dropdown {
    position: absolute;
    top: calc(100% + 8px);
    background: var(--bg-primary);
    border: 2px solid var(--border-color);
    overflow: hidden;
    min-width: 220px;
    z-index: var(--z-dropdown, 1000);
}

.dropdown--wide { width: 320px; }
.dropdown--right { right: 0; }

.dropdown__header {
    padding: 12px 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid var(--border-color);
}

.dropdown__header h3 {
    margin: 0;
    font-size: 14px;
    font-weight: 600;
    font-family: 'Space Grotesk', sans-serif;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--text-primary);
}

.dropdown__link {
    font-size: 12px;
    color: #FF6B35;
}

.dropdown__list {
    max-height: 340px;
    overflow-y: auto;
}

.dropdown__item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 12px 14px;
    color: var(--text-primary);
    border-bottom: 1px solid var(--border-color);
    transition: background 150ms ease;
}

.dropdown__item:last-child { border-bottom: none; }

.dropdown__item:hover { background: #FF6B35; color: var(--bg-primary); }

.dropdown__item:hover .dropdown__title,
.dropdown__item:hover .dropdown__subtitle {
    color: var(--bg-primary);
}

.dropdown__item:hover .dropdown__icon {
    background: var(--bg-primary);
    color: #FF6B35;
}

.dropdown__icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    background: var(--bg-secondary);
    color: var(--text-secondary);
}



.dropdown__content { flex: 1; min-width: 0; }
.dropdown__title { margin: 0; font-size: 14px; font-weight: 600; }
.dropdown__subtitle { margin: 2px 0 0; font-size: 12px; color: var(--text-tertiary); }
.dropdown__subtitle--truncate { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

.dropdown__header--compact { align-items: flex-start; flex-direction: column; gap: 2px; }

.dropdown__list--flush .dropdown__item { border-bottom: none; padding: 10px 14px; gap: 10px; align-items: center; }
.dropdown__item--row { align-items: center; gap: 10px; font-size: 14px; }
.dropdown__item--danger { color: #FF6B35; }
.dropdown__footer { border-top: 1px solid var(--border-color); }

.navbar__user {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 6px 12px;
    /* border-radius: 6px; */
    background: var(--bg-primary);
    border: 2px solid var(--border-color);
    color: var(--text-primary);
    transition: background 150ms ease, border-color 150ms ease;
}

.navbar__user:hover { background: var(--bg-tertiary); }

.navbar__avatar {
    width: 36px;
    height: 36px;
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    border: 2px solid var(--border-color);
}

.navbar__user-info { display: none; flex-direction: column; line-height: 1.2; }
.navbar__user-name { margin: 0; font-size: 14px; font-weight: 600; }
.navbar__user-email { margin: 0; font-size: 12px; color: var(--text-tertiary); }

.fade-in { transition: opacity 120ms ease, transform 120ms ease; }
.fade-in--from { opacity: 0; transform: translateY(-4px); }
.fade-in--to { opacity: 1; transform: translateY(0); }
.fade-out { transition: opacity 120ms ease, transform 120ms ease; }
.fade-out--from { opacity: 1; transform: translateY(0); }
.fade-out--to { opacity: 0; transform: translateY(-4px); }

@media (max-width: 1024px) {
    .navbar--open,
    .navbar--closed {
        padding-left: 0;
    }

    .navbar__brand-area {
        display: none;
    }

    .navbar__inner { padding: 0 16px; }
    .navbar__user-info { display: none; }
}

@media (max-width: 768px) {
    .navbar__icon-btn--mobile { display: inline-flex; }
    .navbar__search { display: none; }
}

@media (min-width: 1024px) {
    .navbar__user-info { display: flex; }
}
</style>
