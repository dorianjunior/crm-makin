<script setup>
const props = defineProps({
    variant: {
        type: String,
        default: 'primary',
        validator: (value) => ['primary', 'secondary', 'accent', 'success', 'danger', 'warning', 'ghost'].includes(value),
    },
    size: {
        type: String,
        default: 'md',
        validator: (value) => ['sm', 'md', 'lg'].includes(value),
    },
    loading: {
        type: Boolean,
        default: false,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    icon: String,
    iconRight: String,
    fullWidth: {
        type: Boolean,
        default: false,
    },
    type: {
        type: String,
        default: 'button',
    },
});

const emit = defineEmits(['click']);

const handleClick = (event) => {
    if (!props.disabled && !props.loading) {
        emit('click', event);
    }
};
</script>

<template>
    <button
        :type="type"
        :class="[
            'btn',
            `btn--${variant}`,
            `btn--${size}`,
            { 'btn--full': fullWidth },
            { 'btn--loading': loading },
        ]"
        :disabled="disabled || loading"
        @click="handleClick"
    >
        <i v-if="loading" class="fas fa-spinner fa-spin btn__icon"></i>
        <i v-else-if="icon" :class="`fas ${icon}`" class="btn__icon"></i>

        <span class="btn__text">
            <slot />
        </span>

        <i v-if="iconRight && !loading" :class="`fas ${iconRight}`" class="btn__icon-right"></i>
    </button>
</template>

<style scoped>
.btn {
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 12px 24px;
    font-family: 'Space Grotesk', sans-serif;
    font-size: 13px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    line-height: 1;
    border: 2px solid var(--border-color);
    background: var(--bg-primary);
    color: var(--text-primary);
    cursor: pointer;
    transition: all 180ms ease;
    overflow: hidden;
}

.btn::before {
    content: '';
    position: absolute;
    inset: 0;
    background: var(--color-accent);
    opacity: 0;
    transition: opacity 180ms ease;
    z-index: 0;
}

.btn:hover::before {
    opacity: 0.08;
}

.btn:active {
    transform: translateY(1px);
}

.btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none !important;
}

.btn__icon,
.btn__icon-right,
.btn__text {
    position: relative;
    z-index: 1;
}

/* Variants */
.btn--primary {
    background: var(--color-primary);
    border-color: var(--color-primary);
    color: #fff;
}

.btn--primary:hover:not(:disabled) {
    background: var(--color-primary-dark);
    border-color: var(--color-primary-dark);
    transform: translateX(2px);
}

.btn--secondary {
    background: var(--bg-secondary);
    border-color: var(--border-color);
    color: var(--text-primary);
}

.btn--secondary:hover:not(:disabled) {
    background: var(--bg-tertiary);
    border-color: var(--border-bold, #262626);
}

.btn--accent {
    background: var(--color-accent);
    border-color: var(--color-accent);
    color: #fff;
}

.btn--accent:hover:not(:disabled) {
    background: var(--color-accent-dark);
    border-color: var(--color-accent-dark);
    transform: translateX(3px);
}

.btn--accent::before {
    background: #fff;
}

.btn--success {
    background: var(--color-success);
    border-color: var(--color-success);
    color: #fff;
}

.btn--success:hover:not(:disabled) {
    opacity: 0.9;
}

.btn--danger {
    background: var(--color-error);
    border-color: var(--color-error);
    color: #fff;
}

.btn--danger:hover:not(:disabled) {
    opacity: 0.9;
}

.btn--warning {
    background: var(--color-warning);
    border-color: var(--color-warning);
    color: #fff;
}

.btn--warning:hover:not(:disabled) {
    opacity: 0.9;
}

.btn--ghost {
    background: transparent;
    border-color: var(--border-color);
    color: var(--text-primary);
}

.btn--ghost:hover:not(:disabled) {
    background: var(--bg-secondary);
    border-color: var(--border-bold, #262626);
}

/* Sizes */
.btn--sm {
    padding: 8px 16px;
    font-size: 11px;
    gap: 8px;
}

.btn--lg {
    padding: 16px 32px;
    font-size: 15px;
    gap: 12px;
}

/* Full width */
.btn--full {
    width: 100%;
}

/* Loading state */
.btn--loading {
    pointer-events: none;
}

/* Focus */
.btn:focus-visible {
    outline: 2px solid var(--color-accent);
    outline-offset: 2px;
}
</style>
