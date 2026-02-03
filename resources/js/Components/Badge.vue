<script setup>
const props = defineProps({
    variant: {
        type: String,
        default: 'default',
        validator: (value) => ['default', 'primary', 'accent', 'success', 'warning', 'danger', 'info'].includes(value),
    },
    size: {
        type: String,
        default: 'md',
        validator: (value) => ['sm', 'md', 'lg'].includes(value),
    },
    icon: String,
    dot: Boolean,
    removable: Boolean,
});

const emit = defineEmits(['remove']);

const handleRemove = () => {
    emit('remove');
};
</script>

<template>
    <span
        :class="[
            'badge',
            `badge--${variant}`,
            `badge--${size}`,
        ]"
    >
        <span v-if="dot" class="badge__dot"></span>
        <i v-if="icon" :class="`fas ${icon}`" class="badge__icon"></i>
        <span class="badge__text">
            <slot />
        </span>
        <button
            v-if="removable"
            type="button"
            class="badge__remove"
            @click="handleRemove"
        >
            <i class="fas fa-times"></i>
        </button>
    </span>
</template>

<style scoped>
.badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    font-family: 'Space Grotesk', sans-serif;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    line-height: 1.2;
    border: 2px solid var(--border-color);
    background: var(--bg-secondary);
    color: var(--text-primary);
    white-space: nowrap;
}

/* Variants */
.badge--default {
    background: var(--bg-secondary);
    border-color: var(--border-color);
    color: var(--text-primary);
}

.badge--primary {
    background: var(--color-primary);
    border-color: var(--color-primary);
    color: #fff;
}

.badge--accent {
    background: var(--color-accent);
    border-color: var(--color-accent);
    color: #fff;
}

.badge--success {
    background: var(--color-success);
    border-color: var(--color-success);
    color: #fff;
}

.badge--warning {
    background: var(--color-warning);
    border-color: var(--color-warning);
    color: #fff;
}

.badge--danger {
    background: var(--color-error);
    border-color: var(--color-error);
    color: #fff;
}

.badge--info {
    background: var(--color-info);
    border-color: var(--color-info);
    color: #fff;
}

/* Sizes */
.badge--sm {
    padding: 2px 8px;
    font-size: 10px;
    gap: 4px;
}

.badge--lg {
    padding: 6px 12px;
    font-size: 12px;
    gap: 8px;
}

/* Elements */
.badge__dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: currentColor;
}

.badge__icon {
    font-size: 10px;
}

.badge__text {
    line-height: 1;
}

.badge__remove {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 14px;
    height: 14px;
    padding: 0;
    background: transparent;
    border: none;
    color: currentColor;
    cursor: pointer;
    opacity: 0.7;
    transition: opacity 120ms ease;
}

.badge__remove:hover {
    opacity: 1;
}

.badge__remove i {
    font-size: 9px;
}
</style>
