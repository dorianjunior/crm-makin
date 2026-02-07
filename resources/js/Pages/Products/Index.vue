<template>
  <MainLayout title="Produtos e Serviços">
    <div class="page-container">
      <Breadcrumbs :items="breadcrumbs" />

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
      <div class="stats-row">
        <StatCard
          label="Total de Produtos"
          :value="stats.total"
          icon="fa fa-box"
          color="primary"
        />
        <StatCard
          label="Ativos"
          :value="stats.active"
          icon="fa fa-check-circle"
          color="success"
        />
        <StatCard
          label="Inativos"
          :value="stats.inactive"
          icon="fa fa-times-circle"
          color="error"
        />
        <StatCard
          label="Valor Total"
          :value="formatCurrency(stats.total_value)"
          icon="fa fa-dollar-sign"
          color="info"
        />
      </div>

      <!-- Filters -->
      <div class="filters-card">
        <div class="filters-grid">
          <div class="filter-item">
            <label class="form-label">Buscar</label>
            <Input
              v-model="filters.search"
              placeholder="Nome ou SKU..."
              @input="loadProducts"
              icon="fa fa-search"
            />
          </div>

          <div class="filter-item">
            <label class="form-label">Tipo</label>
            <select v-model="filters.type" @change="loadProducts" class="form-select">
              <option value="">Todos</option>
              <option value="product">Produto</option>
              <option value="service">Serviço</option>
            </select>
          </div>

          <div class="filter-item">
            <label class="form-label">Status</label>
            <select v-model="filters.active" @change="loadProducts" class="form-select">
              <option value="">Todos</option>
              <option value="1">Ativo</option>
              <option value="0">Inativo</option>
            </select>
          </div>

          <div class="filter-actions">
            <Button variant="secondary" @click="clearFilters">
              <i class="fa fa-times"></i>
              Limpar
            </Button>
          </div>
        </div>
      </div>

      <!-- View Toggle -->
      <div class="view-controls">
        <div class="view-toggle">
          <button
            @click="viewMode = 'grid'"
            :class="{ active: viewMode === 'grid' }"
            class="toggle-btn"
            title="Grade"
          >
            <i class="fa fa-th-large"></i>
          </button>
          <button
            @click="viewMode = 'list'"
            :class="{ active: viewMode === 'list' }"
            class="toggle-btn"
            title="Lista"
          >
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
              <Button variant="secondary" size="sm" @click="toggleProductStatus(product)" :title="product.active ? 'Desativar' : 'Ativar'">
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
      <div v-if="viewMode === 'list'" class="table-card">
        <table class="data-table">
          <thead>
            <tr>
              <th>Produto</th>
              <th>SKU</th>
              <th>Tipo</th>
              <th>Preço</th>
              <th>Status</th>
              <th>Criado em</th>
              <th>Ações</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="product in products.data" :key="product.id">
              <td>
                <div class="product-name-cell">
                  <strong>{{ product.name }}</strong>
                  <small v-if="product.description">{{ truncate(product.description, 50) }}</small>
                </div>
              </td>
              <td>{{ product.sku || 'N/A' }}</td>
              <td>
                <span class="type-badge" :class="product.type">
                  {{ product.type === 'product' ? 'Produto' : 'Serviço' }}
                </span>
              </td>
              <td class="price">{{ formatCurrency(product.price) }}</td>
              <td>
                <span :class="['status-badge', product.active ? 'active' : 'inactive']">
                  {{ product.active ? 'Ativo' : 'Inativo' }}
                </span>
              </td>
              <td>{{ formatDate(product.created_at) }}</td>
              <td>
                <div class="action-buttons">
                  <button @click="editProduct(product)" class="action-btn" title="Editar">
                    <i class="fa fa-edit"></i>
                  </button>
                  <button @click="duplicateProduct(product)" class="action-btn" title="Duplicar">
                    <i class="fa fa-copy"></i>
                  </button>
                  <button @click="toggleProductStatus(product)" class="action-btn" :title="product.active ? 'Desativar' : 'Ativar'">
                    <i :class="product.active ? 'fa fa-eye-slash' : 'fa fa-eye'"></i>
                  </button>
                  <button @click="deleteProduct(product)" class="action-btn danger" title="Excluir">
                    <i class="fa fa-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Pagination -->
        <Pagination
          :from="products.from"
          :to="products.to"
          :total="products.total"
          :current-page="products.current_page"
          :last-page="products.last_page"
          @page-change="handlePageChange"
        />
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
import StatCard from '@/Components/StatCard.vue';
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

