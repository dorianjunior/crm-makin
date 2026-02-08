<template>
    <MainLayout title="Produtos e Serviços">
        <template #breadcrumbs>
            <Breadcrumbs :items="breadcrumbs" />
        </template>

        <div class="page-container">
            <!-- Header -->
            <div class="page-header">
                <div>
                    <h1>Produtos e Serviços</h1>
                    <p class="subtitle">Gerencie seu catálogo de ofertas</p>
                </div>
                <Button variant="primary" @click="createProduct">
                    <i class="fa fa-plus"></i>
                    Novo Produto
                </Button>
            </div>

            <!-- Stats -->
            <div class="stats-grid">
                <StatCard title="Total de Produtos" :value="stats.total" icon="fa-box" />
                <StatCard title="Ativos" :value="stats.active" icon="fa-check-circle" />
                <StatCard title="Inativos" :value="stats.inactive" icon="fa-times-circle" />
                <StatCard title="Valor Total" :value="formatCurrency(stats.total_value)" icon="fa-dollar-sign" />
            </div>

            <!-- Filters -->
            <div class="filters-card">
                <div class="filters-header">
                    <div class="filters-title">
                        <i class="fa fa-filter"></i>
                        <span>FILTROS</span>
                    </div>
                </div>
                <div class="filters-grid">
                    <Input v-model="filters.search" label="Buscar" placeholder="Nome ou SKU..." @input="loadProducts"
                        icon="fa fa-search" />

                    <Select v-model="filters.type" label="Tipo" :options="typeOptions"
                        @update:modelValue="loadProducts" />

                    <Select v-model="filters.active" label="Status" :options="statusOptions"
                        @update:modelValue="loadProducts" />

                    <div style="display: flex; align-items: flex-end;">
                        <Button variant="secondary" @click="clearFilters">
                            <i class="fa fa-times"></i>
                            Limpar Filtros
                        </Button>
                    </div>
                </div>
            </div>

            <!-- View Toggle -->
            <div class="view-controls">
                <div class="view-toggle">
                    <button @click="viewMode = 'grid'" :class="{ active: viewMode === 'grid' }" class="toggle-btn"
                        title="Grade">
                        <i class="fa fa-th-large"></i>
                    </button>
                    <button @click="viewMode = 'list'" :class="{ active: viewMode === 'list' }" class="toggle-btn"
                        title="Lista">
                        <i class="fa fa-list"></i>
                    </button>
                </div>
            </div>

            <!-- Grid View -->
            <div v-if="viewMode === 'grid'" class="products-grid">
                <div v-for="product in products.data" :key="product.id" class="product-card">
                    <div class="product-image">
                        <img v-if="product.image" :src="product.image" :alt="product.name" />
                        <span v-else class="image-placeholder">
                            <i class="fa fa-box"></i>
                        </span>
                        <span v-if="!product.active" class="inactive-badge">Inativo</span>
                    </div>

                    <div class="product-content">
                        <div class="product-header">
                            <h3 class="product-name">{{ product.name }}</h3>
                            <span class="type-badge" :class="product.type">
                                {{ product.type === 'product' ? 'Produto' : 'Serviço' }}
                            </span>
                        </div>

                        <p v-if="product.description" class="product-description">
                            {{ truncate(product.description, 100) }}
                        </p>

                        <div class="product-meta">
                            <div class="meta-item">
                                <i class="fa fa-barcode"></i>
                                <span>{{ product.sku || 'Sem SKU' }}</span>
                            </div>
                            <div class="meta-item price">
                                <i class="fa fa-dollar-sign"></i>
                                <strong>{{ formatCurrency(product.price) }}</strong>
                            </div>
                        </div>

                        <div class="product-actions">
                            <Button variant="secondary" size="sm" @click="editProduct(product)" title="Editar">
                                <i class="fa fa-edit"></i>
                            </Button>
                            <Button variant="secondary" size="sm" @click="duplicateProduct(product)" title="Duplicar">
                                <i class="fa fa-copy"></i>
                            </Button>
                            <Button variant="secondary" size="sm" @click="toggleProductStatus(product)"
                                :title="product.active ? 'Desativar' : 'Ativar'">
                                <i :class="product.active ? 'fa fa-eye-slash' : 'fa fa-eye'"></i>
                            </Button>
                            <Button variant="danger" size="sm" @click="deleteProduct(product)" title="Excluir">
                                <i class="fa fa-trash"></i>
                            </Button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- List View -->
            <div v-if="viewMode === 'list'">
                <Table :columns="tableColumns" :data="products.data" :striped="true" :hoverable="true">
                    <!-- Product Name Column -->
                    <template #cell-name="{ row }">
                        <div class="product-name-cell">
                            <strong>{{ row.name }}</strong>
                            <small v-if="row.description">{{ truncate(row.description, 50) }}</small>
                        </div>
                    </template>

                    <!-- SKU Column -->
                    <template #cell-sku="{ row }">
                        {{ row.sku || 'N/A' }}
                    </template>

                    <!-- Type Column -->
                    <template #cell-type="{ row }">
                        <span class="type-badge" :class="row.type">
                            {{ row.type === 'product' ? 'Produto' : 'Serviço' }}
                        </span>
                    </template>

                    <!-- Price Column -->
                    <template #cell-price="{ row }">
                        <span class="price">{{ formatCurrency(row.price) }}</span>
                    </template>

                    <!-- Status Column -->
                    <template #cell-status="{ row }">
                        <span :class="['status-badge', row.active ? 'active' : 'inactive']">
                            {{ row.active ? 'Ativo' : 'Inativo' }}
                        </span>
                    </template>

                    <!-- Created At Column -->
                    <template #cell-created_at="{ row }">
                        {{ formatDate(row.created_at) }}
                    </template>

                    <!-- Actions Column -->
                    <template #cell-actions="{ row }">
                        <div class="action-buttons">
                            <button @click="editProduct(row)" class="action-btn" title="Editar">
                                <i class="fa fa-edit"></i>
                            </button>
                            <button @click="duplicateProduct(row)" class="action-btn" title="Duplicar">
                                <i class="fa fa-copy"></i>
                            </button>
                            <button @click="toggleProductStatus(row)" class="action-btn"
                                :title="row.active ? 'Desativar' : 'Ativar'">
                                <i :class="row.active ? 'fa fa-eye-slash' : 'fa fa-eye'"></i>
                            </button>
                            <button @click="deleteProduct(row)" class="action-btn danger" title="Excluir">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </template>
                </Table>

                <!-- Pagination -->
                <Pagination :from="products.from" :to="products.to" :total="products.total"
                    :current-page="products.current_page" :last-page="products.last_page"
                    @page-change="handlePageChange" />
            </div>
        </div>
    </MainLayout>
</template>

<script setup>
import { ref, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { useAlert } from '@/composables/useAlert';
import MainLayout from '@/Layouts/MainLayout.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import Button from '@/Components/Button.vue';
import Input from '@/Components/Input.vue';
import Select from '@/Components/Select.vue';
import StatCard from '@/Components/StatCard.vue';
import Table from '@/Components/Table.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    products: Object,
    stats: Object,
    filters: Object,
});

const page = usePage();
const alert = useAlert();

const viewMode = ref('grid');
let searchTimeout = null;

// Initialize filters from server props so URLs are SSR-friendly
const filters = ref({
    search: props.filters?.search || '',
    type: props.filters?.type || '',
    active: props.filters?.active || '',
});

const breadcrumbs = [
    { name: 'Dashboard', href: '/dashboard' },
    { name: 'Produtos' }
];

const typeOptions = [
    { value: '', label: 'Todos os Tipos' },
    { value: 'product', label: 'Produto' },
    { value: 'service', label: 'Serviço' },
];

const statusOptions = [
    { value: '', label: 'Todos os Status' },
    { value: '1', label: 'Ativo' },
    { value: '0', label: 'Inativo' },
];

const tableColumns = [
    { field: 'name', label: 'Produto', width: '25%' },
    { field: 'sku', label: 'SKU', width: '12%' },
    { field: 'type', label: 'Tipo', width: '10%', align: 'center' },
    { field: 'price', label: 'Preço', width: '12%', align: 'right' },
    { field: 'status', label: 'Status', width: '10%', align: 'center' },
    { field: 'created_at', label: 'Criado em', width: '12%' },
    { field: 'actions', label: 'Ações', width: '15%', align: 'center' },
];

const formatCurrency = (value) => {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    }).format(value || 0);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('pt-BR');
};

const truncate = (text, length) => {
    if (!text) return '';
    return text.length > length ? text.substring(0, length) + '...' : text;
};

const loadProducts = () => {
    // Debounce search
    if (searchTimeout) clearTimeout(searchTimeout);

    searchTimeout = setTimeout(() => {
        router.get('/products', filters.value, {
            preserveState: true,
            preserveScroll: true,
        });
    }, 300);
};

const clearFilters = () => {
    filters.value = { search: '', type: '', active: '' };
    router.get('/products', {}, { preserveState: true });
    alert.toast('Filtros limpos!', 'success');
};

const createProduct = () => {
    router.visit('/products/create');
};

const editProduct = (product) => {
    router.visit(`/products/${product.id}/edit`);
};

const duplicateProduct = async (product) => {
    const res = await alert.confirm('Duplicar produto?', `Deseja duplicar "${product.name}"?`);
    if (!res.isConfirmed) return;

    router.post(`/products/${product.id}/duplicate`, {}, {
        preserveState: true,
        onSuccess: () => alert.toast('Produto duplicado!', 'success'),
        onError: () => alert.toast('Erro ao duplicar produto', 'error')
    });
};

const toggleProductStatus = (product) => {
    router.put(`/products/${product.id}`, {
        active: !product.active
    }, {
        preserveState: true,
        onSuccess: () => alert.toast('Status atualizado!', 'success'),
        onError: () => alert.toast('Erro ao atualizar status', 'error')
    });
};

const deleteProduct = async (product) => {
    const res = await alert.confirmDelete('Excluir produto?', `Tem certeza que deseja excluir "${product.name}"?`);
    if (!res.isConfirmed) return;

    router.delete(`/products/${product.id}`, {
        preserveState: true,
        onSuccess: () => alert.toast('Produto excluído!', 'success'),
        onError: () => alert.toast('Erro ao excluir produto', 'error')
    });
};

const handlePageChange = (page) => {
    router.get('/products', { ...filters.value, page }, {
        preserveState: true,
        preserveScroll: true
    });
};

// Show flash messages (server redirects) using toast
watch(() => page.props.value.flash, (flash) => {
    if (flash?.success) alert.toast(flash.success, 'success');
    if (flash?.error) alert.toast(flash.error, 'error');
});

</script>
