<template>
    <div class="error-page">
        <div class="error-container">
            <div class="error-content">
                <div class="error-code">404</div>
                <div class="error-message">
                    <h1 class="error-title">PÁGINA NÃO ENCONTRADA</h1>
                    <p class="error-description">A página que você procura não existe ou foi movida.</p>
                </div>
                <Link href="/dashboard" class="btn">
                    <i class="fas fa-arrow-left"></i>
                    Voltar ao Dashboard
                </Link>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<style scoped lang="scss">
.error-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--bg-primary);
    padding: 32px;
}

.error-container {
    max-width: 800px;
    width: 100%;
}

.error-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 32px;
    text-align: center;
    background: var(--bg-primary);
    border: 2px solid var(--border-color);
    padding: 64px 48px;
}

.error-code {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 160px;
    font-weight: 800;
    line-height: 1;
    color: var(--text-primary);
    position: relative;

    &::after {
        content: '';
        position: absolute;
        bottom: -8px;
        left: 50%;
        transform: translateX(-50%);
        width: 120px;
        height: 4px;
        background: #FF6B35;
    }
}

.error-message {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.error-title {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 32px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--text-primary);
    margin: 0;
}

.error-description {
    font-size: 16px;
    color: var(--text-secondary);
    margin: 0;
}

@media (max-width: 768px) {
    .error-content {
        padding: 48px 32px;
    }

    .error-code {
        font-size: 120px;
    }

    .error-title {
        font-size: 24px;
    }

    .error-description {
        font-size: 14px;
    }
}
</style>
