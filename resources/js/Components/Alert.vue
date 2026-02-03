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

const variants = {
    info: {
        bg: 'bg-blue-50 dark:bg-blue-900/20',
        border: 'border-blue-200 dark:border-blue-800',
        text: 'text-blue-800 dark:text-blue-400',
        icon: 'fa-info-circle',
    },
    success: {
        bg: 'bg-green-50 dark:bg-green-900/20',
        border: 'border-green-200 dark:border-green-800',
        text: 'text-green-800 dark:text-green-400',
        icon: 'fa-check-circle',
    },
    warning: {
        bg: 'bg-yellow-50 dark:bg-yellow-900/20',
        border: 'border-yellow-200 dark:border-yellow-800',
        text: 'text-yellow-800 dark:text-yellow-400',
        icon: 'fa-exclamation-triangle',
    },
    error: {
        bg: 'bg-red-50 dark:bg-red-900/20',
        border: 'border-red-200 dark:border-red-800',
        text: 'text-red-800 dark:text-red-400',
        icon: 'fa-exclamation-circle',
    },
};
</script>

<template>
    <div
        :class="[
            'flex items-start p-4 rounded-lg border',
            variants[variant].bg,
            variants[variant].border,
        ]"
    >
        <div :class="['flex-shrink-0', variants[variant].text]">
            <i :class="`fas ${icon || variants[variant].icon}`"></i>
        </div>
        <div :class="['ml-3 flex-1', variants[variant].text]">
            <slot />
        </div>
        <button
            v-if="closeable"
            @click="emit('close')"
            :class="['flex-shrink-0 ml-3 opacity-70 hover:opacity-100', variants[variant].text]"
        >
            <i class="fas fa-times"></i>
        </button>
    </div>
</template>
