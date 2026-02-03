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
    <nav class="breadcrumbs-brutalist" aria-label="Breadcrumb">
        <ol class="breadcrumbs-brutalist__list">
            <li class="breadcrumbs-brutalist__item">
                <Link href="/dashboard" class="breadcrumbs-brutalist__link">
                    <i class="fas fa-home breadcrumbs-brutalist__icon"></i>
                    Dashboard
                </Link>
            </li>
            
            <li
                v-for="(item, index) in items"
                :key="index"
                class="breadcrumbs-brutalist__item"
            >
                <i class="fas fa-chevron-right breadcrumbs-brutalist__separator"></i>
                
                <Link
                    v-if="item.href"
                    :href="item.href"
                    class="breadcrumbs-brutalist__link"
                >
                    {{ item.name }}
                </Link>
                <span
                    v-else
                    class="breadcrumbs-brutalist__current"
                >
                    {{ item.name }}
                </span>
            </li>
        </ol>
    </nav>
</template>

<style scoped>
.breadcrumbs-brutalist {
    display: flex;
}

.breadcrumbs-brutalist__list {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0;
    padding: 0;
    margin: 0;
    list-style: none;
}

.breadcrumbs-brutalist__item {
    display: flex;
    align-items: center;
    gap: 8px;
}

.breadcrumbs-brutalist__link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-family: 'Inter', sans-serif;
    font-size: 13px;
    font-weight: 500;
    color: var(--text-secondary);
    text-decoration: none;
    transition: color 120ms ease;
}

.breadcrumbs-brutalist__link:hover {
    color: var(--color-accent);
}

.breadcrumbs-brutalist__icon {
    font-size: 12px;
}

.breadcrumbs-brutalist__separator {
    font-size: 10px;
    color: var(--text-muted);
}

.breadcrumbs-brutalist__current {
    font-family: 'Inter', sans-serif;
    font-size: 13px;
    font-weight: 600;
    color: var(--text-primary);
}
</style>
