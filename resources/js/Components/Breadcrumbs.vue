<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    items: {
        type: Array,
        required: true,
    },
});
</script>

<template>
    <nav class="flex" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <Link
                    href="/dashboard"
                    class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white"
                >
                    <i class="fas fa-home mr-2"></i>
                    Dashboard
                </Link>
            </li>
            <li v-for="(item, index) in items" :key="index">
                <div class="flex items-center">
                    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                    <Link
                        v-if="item.href"
                        :href="item.href"
                        class="text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white"
                    >
                        {{ item.name }}
                    </Link>
                    <span
                        v-else
                        class="text-sm font-medium text-gray-500 dark:text-gray-400"
                    >
                        {{ item.name }}
                    </span>
                </div>
            </li>
        </ol>
    </nav>
</template>
