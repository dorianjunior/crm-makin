<script setup>
defineProps({
    variant: {
        type: String,
        default: 'info',
        validator: (value) => ['info', 'success', 'warning', 'error'].includes(value),
    },
    icon: String,
    closeable: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close']);

const defaultIcons = {
    info: 'fa-info-circle',
    success: 'fa-check-circle',
    warning: 'fa-exclamation-triangle',
    error: 'fa-exclamation-circle',
};
</script>

<template>
    <div :class="['alert-brutalist', `alert-brutalist--${variant}`]">
        <div class="alert-brutalist__icon">
            <i :class="`fas ${icon || defaultIcons[variant]}`"></i>
        </div>
        <div class="alert-brutalist__content">
            <slot />
        </div>
        <button
            v-if="closeable"
            type="button"
            class="alert-brutalist__close"
            @click="emit('close')"
        >
            <i class="fas fa-times"></i>
        </button>
    </div>
</template>

<style scoped>
.alert-brutalist {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 16px;
    border: 2px solid var(--border-color);
    background: var(--bg-secondary);
    font-family: 'Inter', sans-serif;
    font-size: 14px;
    line-height: 1.5;
}

.alert-brutalist__icon {
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 20px;
    font-size: 16px;
}

.alert-brutalist__content {
    flex: 1;
}

.alert-brutalist__close {
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 20px;
    height: 20px;
    padding: 0;
    background: transparent;
    border: none;
    cursor: pointer;
    opacity: 0.7;
    transition: opacity 120ms ease;
}

.alert-brutalist__close:hover {
    opacity: 1;
}

/* Variants */
.alert-brutalist--info {
    background: color-mix(in srgb, var(--color-info) 8%, var(--bg-secondary));
    border-color: var(--color-info);
    color: var(--color-info);
}

.alert-brutalist--success {
    background: color-mix(in srgb, var(--color-success) 8%, var(--bg-secondary));
    border-color: var(--color-success);
    color: var(--color-success);
}

.alert-brutalist--warning {
    background: color-mix(in srgb, var(--color-warning) 8%, var(--bg-secondary));
    border-color: var(--color-warning);
    color: var(--color-warning);
}

.alert-brutalist--error {
    background: color-mix(in srgb, var(--color-error) 8%, var(--bg-secondary));
    border-color: var(--color-error);
    color: var(--color-error);
}

/* Dark mode adjustments */
:root[data-theme='dark'] .alert-brutalist--info {
    background: color-mix(in srgb, var(--color-info) 12%, var(--bg-secondary));
}

:root[data-theme='dark'] .alert-brutalist--success {
    background: color-mix(in srgb, var(--color-success) 12%, var(--bg-secondary));
}

:root[data-theme='dark'] .alert-brutalist--warning {
    background: color-mix(in srgb, var(--color-warning) 12%, var(--bg-secondary));
}

:root[data-theme='dark'] .alert-brutalist--error {
    background: color-mix(in srgb, var(--color-error) 12%, var(--bg-secondary));
}
</style>
