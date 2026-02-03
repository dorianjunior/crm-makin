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
    <nav class="breadcrumbs" aria-label="Breadcrumb">
        <ol class="breadcrumbs__list">
            <li
                v-for="(item, index) in items"
                :key="index"
                class="breadcrumbs__item"
            >
                <i v-if="index > 0" class="fas fa-chevron-right breadcrumbs__separator"></i>

                <Link
                    v-if="item.href"
                    :href="item.href"
                    class="breadcrumbs__link"
                >
                    <i v-if="index === 0" class="fas fa-home breadcrumbs__icon"></i>
                    {{ item.name }}
                </Link>
                <span
                    v-else
                    class="breadcrumbs__current"
                >
                    {{ item.name }}
                </span>
            </li>
        </ol>
    </nav>
</template>

<style scoped>
.breadcrumbs {
    display: flex;
}

.breadcrumbs__list {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0;
    padding: 0;
    margin: 0;
    list-style: none;
}

.breadcrumbs__item {
    display: flex;
    align-items: center;
    gap: 8px;
}

.breadcrumbs__link {
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

.breadcrumbs__link:hover {
    color: var(--color-accent);
}

.breadcrumbs__icon {
    font-size: 12px;
}

.breadcrumbs__separator {
    font-size: 10px;
    color: var(--text-muted);
}

.breadcrumbs__current {
    font-family: 'Inter', sans-serif;
    font-size: 13px;
    font-weight: 600;
    color: var(--text-primary);
}
</style>
