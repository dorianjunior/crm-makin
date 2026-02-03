<script setup>
defineProps({
    title: String,
    value: [String, Number],
    icon: String,
    iconColor: {
        type: String,
        default: 'blue',
    },
    trend: String,
    trendValue: String,
    trendUp: Boolean,
});

const iconColors = {
    blue: 'bg-blue-100 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400',
    green: 'bg-green-100 text-green-600 dark:bg-green-900/20 dark:text-green-400',
    red: 'bg-red-100 text-red-600 dark:bg-red-900/20 dark:text-red-400',
    yellow: 'bg-yellow-100 text-yellow-600 dark:bg-yellow-900/20 dark:text-yellow-400',
    purple: 'bg-purple-100 text-purple-600 dark:bg-purple-900/20 dark:text-purple-400',
};
</script>

<template>
    <div class="bg-primary rounded-lg shadow-sm p-6 border border-color">
        <div class="flex items-center justify-between">
            <div class="flex-1">
                <p class="text-sm font-medium text-secondary mb-1">
                    {{ title }}
                </p>
                <p class="text-2xl font-bold text-primary">
                    {{ value }}
                </p>
                <div v-if="trend" class="flex items-center mt-2">
                    <i
                        :class="[
                            'fas text-xs mr-1',
                            trendUp ? 'fa-arrow-up text-green-500' : 'fa-arrow-down text-red-500'
                        ]"
                    ></i>
                    <span
                        :class="[
                            'text-xs font-medium',
                            trendUp ? 'text-green-500' : 'text-red-500'
                        ]"
                    >
                        {{ trendValue }}
                    </span>
                    <span class="text-xs text-tertiary ml-1">{{ trend }}</span>
                </div>
            </div>
            <div
                :class="[
                    'flex items-center justify-center w-12 h-12 rounded-full',
                    iconColors[iconColor]
                ]"
            >
                <i :class="`fas ${icon} text-xl`"></i>
            </div>
        </div>
    </div>
</template>
