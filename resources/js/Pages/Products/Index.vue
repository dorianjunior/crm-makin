<template>
  <MainLayout>
    <div class="products-page">
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
            <label>Buscar</label>
            <Input
              v-model="filters.search"
              placeholder="Nome ou SKU..."
              icon="fa fa-search"
            />
          </div>

          <div class="filter-item">
            <label>Tipo</label>
            <select v-model="filters.type" class="form-select">
              <option value="">Todos</option>
              <option value="product">Produto</option>
              <option value="service">Serviço</option>
            </select>
          </div>

          <div class="filter-item">
            <label>Status</label>
            <select v-model="filters.is_active" class="form-select">
              <option value="">Todos</option>
              <option value="1">Ativo</option>
              <option value="0">Inativo</option>
            </select>
          </div>

          <div class="filter-actions">
            <Button variant="secondary" @click="applyFilters">
              <i class="fa fa-filter"></i>
              Filtrar
            </Button>
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
          >
            <i class="fa fa-th"></i>
          </button>
          <button
            @click="viewMode = 'list'"
            :class="{ active: viewMode === 'list' }"
            class="toggle-btn"
          >
            <i class="fa fa-list"></i>
          </button>
        </div>
      </div>

      <!-- Grid View -->
      <div v-if="viewMode === 'grid'" class="products-grid">
        <div v-for="product in products.data" :key="product.id" class="product-card">
          <div class="product-image">
            <img
              v-if="product.image"
              :src="product.image"
              :alt="product.name"
            >
            <div v-else class="image-placeholder">
              <i class="fa fa-box"></i>
            </div>
            <span v-if="!product.is_active" class="inactive-badge">Inativo</span>
          </div>

          <div class="product-content">
            <div class="product-header">
              <h3>{{ product.name }}</h3>
              <span :class="['type-badge', product.type]">
                {{ product.type === 'product' ? 'Produto' : 'Serviço' }}
              </span>
            </div>

            <p v-if="product.description" class="product-description">
              {{ product.description }}
            </p>

            <div class="product-meta">
              <div class="meta-item">
                <span class="label">SKU:</span>
                <span class="value">{{ product.sku || 'N/A' }}</span>
              </div>
              <div class="meta-item">
                <span class="label">Preço:</span>
                <span class="value price">{{ formatCurrency(product.price) }}</span>
              </div>
            </div>

            <div class="product-actions">
              <Button variant="secondary" size="small" @click="editProduct(product)">
                <i class="fa fa-edit"></i>
                Editar
              </Button>
              <Button variant="danger" size="small" @click="deleteProduct(product)">
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
                <div class="product-info">
                  <div class="product-thumb">
                    <img v-if="product.image" :src="product.image" :alt="product.name">
                    <i v-else class="fa fa-box"></i>
                  </div>
                  <div>
                    <strong>{{ product.name }}</strong>
                    <p v-if="product.description">{{ truncate(product.description, 50) }}</p>
                  </div>
                </div>
              </td>
              <td>{{ product.sku || 'N/A' }}</td>
              <td>
                <span :class="['type-badge', product.type]">
                  {{ product.type === 'product' ? 'Produto' : 'Serviço' }}
                </span>
              </td>
              <td class="price">{{ formatCurrency(product.price) }}</td>
              <td>
                <span :class="['status-badge', product.is_active ? 'active' : 'inactive']">
                  {{ product.is_active ? 'Ativo' : 'Inativo' }}
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
                  <button
                    @click="toggleProductStatus(product)"
                    class="action-btn"
                    :title="product.is_active ? 'Desativar' : 'Ativar'"
                  >
                    <i :class="product.is_active ? 'fa fa-eye-slash' : 'fa fa-eye'"></i>
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
        <div class="pagination">
          <button
            @click="goToPage(products.current_page - 1)"
            :disabled="!products.prev_page_url"
            class="pagination-btn"
          >
            <i class="fa fa-chevron-left"></i>
          </button>

          <span class="pagination-info">
            Página {{ products.current_page }} de {{ products.last_page }}
          </span>

          <button
            @click="goToPage(products.current_page + 1)"
            :disabled="!products.next_page_url"
            class="pagination-btn"
          >
            <i class="fa fa-chevron-right"></i>
          </button>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import Button from '@/Components/Button.vue';
import Input from '@/Components/Input.vue';
import StatCard from '@/Components/StatCard.vue';

const props = defineProps({
  products: Object,
  stats: Object
});

const viewMode = ref('grid');

const filters = ref({
  search: '',
  type: '',
  is_active: ''
});

const breadcrumbs = [
  { label: 'Produtos' }
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

const applyFilters = () => {
  router.get('/products', filters.value, {
    preserveState: true,
    preserveScroll: true
  });
};

const clearFilters = () => {
  filters.value = {
    search: '',
    type: '',
    is_active: ''
  };
  applyFilters();
};

const createProduct = () => {
  router.visit('/products/create');
};

const editProduct = (product) => {
  router.visit(`/products/${product.id}/edit`);
};

const duplicateProduct = (product) => {
  if (confirm(`Duplicar o produto "${product.name}"?`)) {
    router.post(`/products/${product.id}/duplicate`, {}, {
      preserveScroll: true
    });
  }
};

const toggleProductStatus = (product) => {
  router.put(`/products/${product.id}`, {
    is_active: !product.is_active
  }, {
    preserveScroll: true
  });
};

const deleteProduct = (product) => {
  if (confirm(`Tem certeza que deseja excluir o produto "${product.name}"?`)) {
    router.delete(`/products/${product.id}`, {
      preserveScroll: true
    });
  }
};

const goToPage = (page) => {
  router.get('/products', {
    ...filters.value,
    page
  }, {
    preserveState: true,
    preserveScroll: true
  });
};
</script>

<style scoped lang="scss">
.products-page {
  padding: 2rem;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 2rem;

  h1 {
    margin: 0 0 0.5rem 0;
    color: var(--text-primary);
  }

  .subtitle {
    margin: 0;
    color: var(--text-secondary);
  }
}

.stats-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.filters-card {
  background: var(--bg-primary);
  border: 1px solid var(--border-color);
  border-radius: 8px;
  padding: 1.5rem;
  margin-bottom: 2rem;

  .filters-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    align-items: end;

    .filter-item {
      label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: var(--text-primary);
      }

      .form-select {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid var(--border-color);
        border-radius: 4px;
        background: var(--bg-primary);
        color: var(--text-primary);

        &:focus {
          outline: none;
          border-color: var(--color-primary);
        }
      }
    }

    .filter-actions {
      display: flex;
      gap: 0.5rem;
    }
  }
}

.view-controls {
  display: flex;
  justify-content: flex-end;
  margin-bottom: 1.5rem;

  .view-toggle {
    display: flex;
    border: 1px solid var(--border-color);
    border-radius: 4px;
    overflow: hidden;

    .toggle-btn {
      padding: 0.5rem 1rem;
      border: none;
      background: var(--bg-primary);
      color: var(--text-primary);
      cursor: pointer;
      transition: all 0.2s;
      border-right: 1px solid var(--border-color);

      &:last-child {
        border-right: none;
      }

      &:hover {
        background: var(--bg-secondary);
      }

      &.active {
        background: var(--color-primary);
        color: white;
      }
    }
  }
}

// Grid View
.products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1.5rem;

  .product-card {
    background: var(--bg-primary);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    overflow: hidden;
    transition: all 0.3s;

    &:hover {
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      transform: translateY(-2px);
    }

    .product-image {
      position: relative;
      width: 100%;
      height: 200px;
      background: var(--bg-secondary);
      display: flex;
      align-items: center;
      justify-content: center;

      img {
        width: 100%;
        height: 100%;
        object-fit: cover;
      }

      .image-placeholder {
        font-size: 4rem;
        color: var(--text-secondary);
        opacity: 0.3;
      }

      .inactive-badge {
        position: absolute;
        top: 0.75rem;
        right: 0.75rem;
        padding: 0.35rem 0.75rem;
        background: rgba(239, 68, 68, 0.9);
        color: white;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 500;
      }
    }

    .product-content {
      padding: 1.5rem;

      .product-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1rem;

        h3 {
          margin: 0;
          color: var(--text-primary);
          font-size: 1.1rem;
          flex: 1;
        }
      }

      .product-description {
        color: var(--text-secondary);
        font-size: 0.9rem;
        line-height: 1.5;
        margin: 0 0 1rem 0;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
      }

      .product-meta {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        margin-bottom: 1rem;
        padding: 1rem;
        background: var(--bg-secondary);
        border-radius: 4px;

        .meta-item {
          display: flex;
          justify-content: space-between;

          .label {
            color: var(--text-secondary);
            font-size: 0.9rem;
          }

          .value {
            color: var(--text-primary);
            font-weight: 500;

            &.price {
              color: var(--color-success);
              font-size: 1.1rem;
              font-weight: 600;
            }
          }
        }
      }

      .product-actions {
        display: flex;
        gap: 0.5rem;
      }
    }
  }
}

.type-badge {
  padding: 0.35rem 0.75rem;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 500;

  &.product {
    background: rgba(59, 130, 246, 0.1);
    color: var(--color-info);
  }

  &.service {
    background: rgba(139, 92, 246, 0.1);
    color: #8b5cf6;
  }
}

// List View
.table-card {
  background: var(--bg-primary);
  border: 1px solid var(--border-color);
  border-radius: 8px;
  overflow: hidden;

  .data-table {
    width: 100%;
    border-collapse: collapse;

    thead {
      background: var(--bg-secondary);

      th {
        padding: 1rem;
        text-align: left;
        font-weight: 600;
        color: var(--text-primary);
        border-bottom: 1px solid var(--border-color);
      }
    }

    tbody {
      tr {
        border-bottom: 1px solid var(--border-color);
        transition: background 0.2s;

        &:hover {
          background: var(--bg-secondary);
        }

        &:last-child {
          border-bottom: none;
        }

        td {
          padding: 1rem;
          color: var(--text-primary);

          &.price {
            color: var(--color-success);
            font-weight: 600;
          }
        }
      }
    }
  }

  .pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    border-top: 1px solid var(--border-color);

    .pagination-btn {
      padding: 0.5rem 1rem;
      border: 1px solid var(--border-color);
      background: var(--bg-primary);
      color: var(--text-primary);
      border-radius: 4px;
      cursor: pointer;

      &:disabled {
        opacity: 0.5;
        cursor: not-allowed;
      }

      &:not(:disabled):hover {
        background: var(--bg-secondary);
      }
    }

    .pagination-info {
      color: var(--text-secondary);
    }
  }
}

.product-info {
  display: flex;
  align-items: center;
  gap: 1rem;

  .product-thumb {
    width: 50px;
    height: 50px;
    background: var(--bg-secondary);
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;

    img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      border-radius: 4px;
    }

    i {
      font-size: 1.5rem;
      color: var(--text-secondary);
      opacity: 0.3;
    }
  }

  strong {
    display: block;
    margin-bottom: 0.25rem;
  }

  p {
    margin: 0;
    font-size: 0.85rem;
    color: var(--text-secondary);
  }
}

.status-badge {
  padding: 0.35rem 0.75rem;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 500;

  &.active {
    background: rgba(16, 185, 129, 0.1);
    color: var(--color-success);
  }

  &.inactive {
    background: rgba(107, 114, 128, 0.1);
    color: #6b7280;
  }
}

.action-buttons {
  display: flex;
  gap: 0.5rem;

  .action-btn {
    width: 32px;
    height: 32px;
    border: 1px solid var(--border-color);
    background: var(--bg-primary);
    color: var(--text-primary);
    border-radius: 4px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;

    &:hover {
      background: var(--bg-secondary);
      border-color: var(--color-primary);
      color: var(--color-primary);
    }

    &.danger:hover {
      border-color: var(--color-error);
      color: var(--color-error);
    }
  }
}

@media (max-width: 768px) {
  .products-page {
    padding: 1rem;
  }

  .products-grid {
    grid-template-columns: 1fr;
  }

  .table-card {
    overflow-x: auto;
  }
}
</style>
