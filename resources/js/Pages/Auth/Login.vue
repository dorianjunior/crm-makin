<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const processing = ref(false);

const submit = () => {
    processing.value = true;
    form.post('/login', {
        onFinish: () => {
            processing.value = false;
            form.reset('password');
        },
    });
};
</script>

<template>
    <Head title="Login" />

    <div class="login">
        <div class="login__container">
            <!-- Branding -->
            <div class="login__brand">
                <div class="login__logo">
                    <div class="login__logo-icon">
                        <i class="fas fa-rocket"></i>
                    </div>
                </div>
                <h1 class="login__title">MAKIN</h1>
                <p class="login__subtitle">Marketing Inteligente</p>
            </div>

            <!-- Form Card -->
            <div class="login__card">
                <div class="login__header">
                    <div class="login__header-icon">
                        <i class="fas fa-sign-in-alt"></i>
                    </div>
                    <h2 class="login__heading">Entrar no Sistema</h2>
                </div>

                <form @submit.prevent="submit" class="login__form">
                    <!-- Email -->
                    <div class="form__group">
                        <label for="email" class="form__label">Email</label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            class="form__input"
                            :class="{ 'form__input--error': form.errors.email }"
                            placeholder="seu@email.com"
                            required
                            autofocus
                        />
                        <span v-if="form.errors.email" class="form__error">
                            {{ form.errors.email }}
                        </span>
                    </div>

                    <!-- Password -->
                    <div class="form__group">
                        <label for="password" class="form__label">Senha</label>
                        <input
                            id="password"
                            v-model="form.password"
                            type="password"
                            class="form__input"
                            :class="{ 'form__input--error': form.errors.password }"
                            placeholder="••••••••"
                            required
                        />
                        <span v-if="form.errors.password" class="form__error">
                            {{ form.errors.password }}
                        </span>
                    </div>

                    <!-- Remember Me -->
                    <div class="form__checkbox">
                        <label class="checkbox">
                            <input
                                v-model="form.remember"
                                type="checkbox"
                                class="checkbox__input"
                            />
                            <span class="checkbox__box"></span>
                            <span class="checkbox__label">Lembrar-me</span>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        class="btn btn--primary"
                        :disabled="form.processing || processing"
                    >
                        <span class="btn__glow"></span>
                        <span class="btn__icon">
                            <i class="fas fa-arrow-right"></i>
                        </span>
                        <span class="btn__text">
                            {{ form.processing || processing ? 'Entrando...' : 'Entrar' }}
                        </span>
                    </button>
                </form>

                <!-- Footer Info -->
                <div class="login__footer">
                    <div class="login__status">
                        <span class="status__dot"></span>
                        <span class="status__text">Sistema Online</span>
                    </div>
                    <span class="login__version">v2.0</span>
                </div>
            </div>
        </div>

        <!-- Background Pattern -->
        <div class="login__bg">
            <div class="login__grid"></div>
        </div>
    </div>
</template>

<style scoped>
.login {
    position: relative;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    background: var(--bg-secondary);
    overflow: hidden;
}

.login__container {
    position: relative;
    z-index: 10;
    width: 100%;
    max-width: 480px;
}

.login__brand {
    text-align: center;
    margin-bottom: 48px;
}

.login__logo {
    display: inline-flex;
    margin-bottom: 24px;
}

.login__logo-icon {
    width: 80px;
    height: 80px;
    border: 3px solid var(--border-color);
    background: var(--bg-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 36px;
    color: #ff6b35;
    transition: transform 300ms ease, border-color 300ms ease;
}

.login__logo-icon:hover {
    transform: rotate(-5deg) scale(1.05);
    border-color: #ff6b35;
}

.login__title {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 56px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: -0.02em;
    color: var(--text-primary);
    line-height: 1;
    margin: 0 0 12px;
}

.login__subtitle {
    font-family: 'JetBrains Mono', monospace;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.12em;
    color: var(--text-secondary);
    margin: 0;
}

.login__card {
    background: var(--bg-primary);
    border: 2px solid var(--border-color);
    padding: 40px;
    position: relative;
}

.login__card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: #ff6b35;
}

.login__header {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 32px;
}

.login__header-icon {
    width: 48px;
    height: 48px;
    background: #ff6b35;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 20px;
}

.login__heading {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 24px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: -0.01em;
    color: var(--text-primary);
    margin: 0;
}

.login__form {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.form__group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.form__label {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--text-secondary);
}

.form__input {
    width: 100%;
    padding: 14px 16px;
    border: 2px solid var(--border-color);
    background: var(--bg-secondary);
    color: var(--text-primary);
    font-family: 'Inter', sans-serif;
    font-size: 15px;
    transition: border-color 180ms ease, background 180ms ease;
}

.form__input:focus {
    outline: none;
    border-color: #ff6b35;
    background: var(--bg-primary);
}

.form__input::placeholder {
    color: var(--text-tertiary);
}

.form__input--error {
    border-color: #dc2626;
}

.form__error {
    font-family: 'JetBrains Mono', monospace;
    font-size: 11px;
    color: #dc2626;
    text-transform: uppercase;
    letter-spacing: 0.06em;
}

.form__checkbox {
    display: flex;
    align-items: center;
}

.checkbox {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
}

.checkbox__input {
    display: none;
}

.checkbox__box {
    width: 20px;
    height: 20px;
    border: 2px solid var(--border-color);
    background: var(--bg-secondary);
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    transition: all 180ms ease;
}

.checkbox__input:checked + .checkbox__box {
    background: #ff6b35;
    border-color: #ff6b35;
}

.checkbox__input:checked + .checkbox__box::after {
    content: '✓';
    color: #fff;
    font-size: 14px;
    font-weight: bold;
}

.checkbox__label {
    font-family: 'Inter', sans-serif;
    font-size: 14px;
    color: var(--text-secondary);
    user-select: none;
}

.btn {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    padding: 16px 24px;
    border: 2px solid var(--border-color);
    background: var(--bg-primary);
    overflow: hidden;
    cursor: pointer;
    transition: transform 180ms ease, border-color 180ms ease;
}

.btn:hover:not(:disabled) {
    border-color: #ff6b35;
    transform: translateX(4px);
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn--primary {
    border-color: #ff6b35;
}

.btn__glow {
    position: absolute;
    inset: 0;
    background: #ff6b35;
    opacity: 0;
    transition: opacity 180ms ease;
}

.btn:hover:not(:disabled) .btn__glow {
    opacity: 0.14;
}

.btn__icon {
    position: relative;
    z-index: 1;
    width: 32px;
    height: 32px;
    border: 2px solid var(--border-color);
    background: var(--bg-secondary);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-primary);
    transition: all 180ms ease;
}

.btn:hover:not(:disabled) .btn__icon {
    background: #ff6b35;
    border-color: #ff6b35;
    color: #fff;
}

.btn__text {
    position: relative;
    z-index: 1;
    font-family: 'Space Grotesk', sans-serif;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    font-size: 14px;
    color: var(--text-primary);
}

.login__footer {
    margin-top: 32px;
    padding-top: 24px;
    border-top: 2px solid var(--border-color);
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.login__status {
    display: flex;
    align-items: center;
    gap: 10px;
}

.status__dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #10b981;
    box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.18);
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.5;
    }
}

.status__text {
    font-family: 'JetBrains Mono', monospace;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--text-tertiary);
}

.login__version {
    font-family: 'JetBrains Mono', monospace;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--text-tertiary);
}

.login__bg {
    position: absolute;
    inset: 0;
    z-index: 1;
    pointer-events: none;
}

.login__grid {
    width: 100%;
    height: 100%;
    background-image:
        linear-gradient(var(--border-color) 1px, transparent 1px),
        linear-gradient(90deg, var(--border-color) 1px, transparent 1px);
    background-size: 40px 40px;
    opacity: 0.3;
}

@media (max-width: 640px) {
    .login {
        padding: 1rem;
    }

    .login__card {
        padding: 24px;
    }

    .login__title {
        font-size: 42px;
    }

    .login__heading {
        font-size: 20px;
    }
}
</style>
