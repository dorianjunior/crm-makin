<script setup>
const props = defineProps({
    title: String,
    subtitle: String,
    hoverable: {
        type: Boolean,
        default: false,
    },
    clickable: {
        type: Boolean,
        default: false,
    },
    bordered: {
        type: Boolean,
        default: true,
    },
    padding: {
        type: String,
        default: 'md',
        validator: (value) => ['none', 'sm', 'md', 'lg'].includes(value),
    },
});

const emit = defineEmits(['click']);

const handleClick = () => {
    if (props.clickable) {
        emit('click');
    }
};
</script>

<template>
    <div
        :class="[
            'card',
            `card--padding-${padding}`,
            { 'card--hoverable': hoverable },
            { 'card--clickable': clickable },
            { 'card--no-border': !bordered },
        ]"
        @click="handleClick"
    >
        <!-- Header -->
        <div v-if="title || subtitle || $slots.header" class="card__header">
            <slot name="header">
                <div v-if="title || subtitle" class="card__header-content">
                    <h3 v-if="title" class="card__title">{{ title }}</h3>
                    <p v-if="subtitle" class="card__subtitle">{{ subtitle }}</p>
                </div>
            </slot>

            <div v-if="$slots.actions" class="card__actions">
                <slot name="actions" />
            </div>
        </div>

        <!-- Body -->
        <div class="card__body">
            <slot />
        </div>

        <!-- Footer -->
        <div v-if="$slots.footer" class="card__footer">
            <slot name="footer" />
        </div>
    </div>
</template>

<style scoped>
.card {
    background: var(--bg-primary);
    border: 2px solid var(--border-color);
    transition: all 180ms ease;
}

.card--hoverable:hover {
    border-color: var(--border-bold, #262626);
    transform: translateX(2px);
}

.card--clickable {
    cursor: pointer;
}

.card--clickable:active {
    transform: translateY(1px);
}

.card--no-border {
    border: none;
}

/* Padding variants */
.card--padding-none {
    padding: 0;
}

.card--padding-sm {
    padding: 16px;
}

.card--padding-md {
    padding: 24px;
}

.card--padding-lg {
    padding: 32px;
}

/* Header */
.card__header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 20px;
}

.card--padding-none .card__header {
    padding: 24px 24px 0;
}

.card__header-content {
    flex: 1;
}

.card__title {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 18px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--text-primary);
    margin: 0 0 8px;
}

.card__subtitle {
    font-family: 'Inter', sans-serif;
    font-size: 13px;
    color: var(--text-secondary);
    margin: 0;
}

.card__actions {
    display: flex;
    align-items: center;
    gap: 8px;
}

/* Body */
.card__body {
    font-family: 'Inter', sans-serif;
    font-size: 14px;
    line-height: 1.6;
    color: var(--text-primary);
}

.card--padding-none .card__body {
    padding: 0 24px;
}

/* Footer */
.card__footer {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 2px solid var(--border-color);
}

.card--padding-none .card__footer {
    padding: 20px 24px 24px;
    margin-top: 0;
}

/* Dark mode adjustments */
:root[data-theme='dark'] .card {
    background: var(--bg-secondary);
}
</style>
