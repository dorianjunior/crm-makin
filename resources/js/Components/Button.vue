<script setup>
const props = defineProps({
    variant: {
        type: String,
        default: 'primary',
        validator: (value) => ['primary', 'secondary', 'success', 'danger', 'warning', 'info'].includes(value),
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
});

const variants = {
    primary: 'bg-blue-600 hover:bg-blue-700 text-white border-transparent',
    secondary: 'bg-gray-600 hover:bg-gray-700 text-white border-transparent',
    success: 'bg-green-600 hover:bg-green-700 text-white border-transparent',
    danger: 'bg-red-600 hover:bg-red-700 text-white border-transparent',
    warning: 'bg-yellow-600 hover:bg-yellow-700 text-white border-transparent',
    info: 'bg-cyan-600 hover:bg-cyan-700 text-white border-transparent',
};

const sizes = {
    sm: 'px-3 py-1.5 text-sm',
    md: 'px-4 py-2 text-base',
    lg: 'px-6 py-3 text-lg',
};
</script>

<template>
    <button
        :class="[
            'inline-flex items-center justify-center font-medium rounded-lg border transition-colors duration-200',
            'focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500',
            'disabled:opacity-50 disabled:cursor-not-allowed',
            variants[variant],
            sizes[size],
            fullWidth ? 'w-full' : '',
        ]"
        :disabled="disabled || loading"
    >
        <i v-if="loading" class="fas fa-spinner fa-spin mr-2"></i>
        <i v-else-if="icon" :class="`fas ${icon}`" class="mr-2"></i>
        <slot />
        <i v-if="iconRight" :class="`fas ${iconRight}`" class="ml-2"></i>
    </button>
</template>
