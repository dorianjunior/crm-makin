<template>
    <MainLayout title="Instagram">
        <template #breadcrumbs>
            <Breadcrumbs :items="breadcrumbs" />
        </template>

        <div class="page-container">
            <!-- Header -->
            <div class="page-header">
                <div>
                    <h1 class="page-title">INSTAGRAM</h1>
                    <p class="page-subtitle">Gerencie sua integração com o Instagram</p>
                </div>
            </div>
        </div>
    </MainLayout>
</template>

<script setup>
import { ref, computed, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { useAlert } from '@/composables/useAlert';
import MainLayout from '@/Layouts/MainLayout.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import Button from '@/Components/Button.vue';
import Input from '@/Components/Input.vue';
import Select from '@/Components/Select.vue';
import Badge from '@/Components/Badge.vue';
import Card from '@/Components/Card.vue';
import Table from '@/Components/Table.vue';

const alert = useAlert();

const breadcrumbs = [
    { name: 'Dashboard', href: '/dashboard' },
    { name: 'Instagram' }
];

</script>
