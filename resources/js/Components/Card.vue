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
            'card-brutalist',
            `card-brutalist--padding-${padding}`,
            { 'card-brutalist--hoverable': hoverable },
            { 'card-brutalist--clickable': clickable },
            { 'card-brutalist--no-border': !bordered },
        ]"
        @click="handleClick"
    >
        <!-- Header -->
        <div v-if="title || subtitle || $slots.header" class="card-brutalist__header">
            <slot name="header">
                <div v-if="title || subtitle" class="card-brutalist__header-content">
                    <h3 v-if="title" class="card-brutalist__title">{{ title }}</h3>
                    <p v-if="subtitle" class="card-brutalist__subtitle">{{ subtitle }}</p>
                </div>
            </slot>

            <div v-if="$slots.actions" class="card-brutalist__actions">
                <slot name="actions" />
            </div>
        </div>

        <!-- Body -->
        <div class="card-brutalist__body">
            <slot />
        </div>

        <!-- Footer -->
        <div v-if="$slots.footer" class="card-brutalist__footer">
            <slot name="footer" />
        </div>
    </div>
</template>

<style scoped>
.card-brutalist {
    background: var(--bg-primary);
    border: 2px solid var(--border-color);
    transition: all 180ms ease;
}

.card-brutalist--hoverable:hover {
    border-color: var(--border-bold, #262626);
    transform: translateX(2px);
}

.card-brutalist--clickable {
    cursor: pointer;
}

.card-brutalist--clickable:active {
    transform: translateY(1px);
}

.card-brutalist--no-border {
    border: none;
}

/* Padding variants */
.card-brutalist--padding-none {
    padding: 0;
}

.card-brutalist--padding-sm {
    padding: 16px;
}

.card-brutalist--padding-md {
    padding: 24px;
}

.card-brutalist--padding-lg {
    padding: 32px;
}

/* Header */
.card-brutalist__header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 20px;
}

.card-brutalist--padding-none .card-brutalist__header {
    padding: 24px 24px 0;
}

.card-brutalist__header-content {
    flex: 1;
}

.card-brutalist__title {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 18px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--text-primary);
    margin: 0 0 8px;
}

.card-brutalist__subtitle {
    font-family: 'Inter', sans-serif;
    font-size: 13px;
    color: var(--text-secondary);
    margin: 0;
}

.card-brutalist__actions {
    display: flex;
    align-items: center;
    gap: 8px;
}

/* Body */
.card-brutalist__body {
    font-family: 'Inter', sans-serif;
    font-size: 14px;
    line-height: 1.6;
    color: var(--text-primary);
}

.card-brutalist--padding-none .card-brutalist__body {
    padding: 0 24px;
}

/* Footer */
.card-brutalist__footer {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 2px solid var(--border-color);
}

.card-brutalist--padding-none .card-brutalist__footer {
    padding: 20px 24px 24px;
    margin-top: 0;
}

/* Dark mode adjustments */
:root[data-theme='dark'] .card-brutalist {
    background: var(--bg-secondary);
}
</style>
