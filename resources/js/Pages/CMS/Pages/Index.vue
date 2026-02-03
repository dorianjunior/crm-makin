<template>
  <MainLayout>
    <div class="pages-index">
      <!-- Header -->
      <div class="page-header">
        <div>
          <h1 class="page-title">Páginas - {{ currentSite?.name }}</h1>
          <Breadcrumbs :items="breadcrumbs" />
        </div>
        <div class="header-actions">
          <select v-model="selectedSiteId" @change="changeSite" class="site-selector">
            <option v-for="site in sites" :key="site.id" :value="site.id">
              {{ site.name }}
            </option>
          </select>
          <Button
            label="Nova Página"
            icon="fa fa-plus"
            @click="createPage"
            severity="success"
          />
        </div>
      </div>

      <!-- Filters -->
      <div class="filters-card">
        <div class="filters-grid">
          <div class="filter-item">
            <label>Buscar</label>
            <Input
              v-model="filters.search"
              placeholder="Título ou slug..."
              icon="fa fa-search"
              @input="debouncedSearch"
            />
          </div>

          <div class="filter-item">
            <label>Status</label>
            <select v-model="filters.status" @change="loadPages" class="form-select">
              <option value="">Todos</option>
              <option value="draft">Rascunho</option>
              <option value="pending">Pendente</option>
              <option value="published">Publicado</option>
            </select>
          </div>

          <div class="filter-item">
            <label>Criado por</label>
            <select v-model="filters.created_by" @change="loadPages" class="form-select">
              <option value="">Todos</option>
              <option v-for="user in users" :key="user.id" :value="user.id">
                {{ user.name }}
              </option>
            </select>
          </div>
        </div>
      </div>

      <!-- Stats -->
      <div class="stats-grid">
        <StatCard
          title="Total de Páginas"
          :value="stats.total"
          icon="fa fa-file-alt"
          color="blue"
        />
        <StatCard
          title="Publicadas"
          :value="stats.published"
          icon="fa fa-check-circle"
          color="green"
        />
        <StatCard
          title="Rascunhos"
          :value="stats.draft"
          icon="fa fa-edit"
          color="orange"
        />
        <StatCard
          title="Pendentes"
          :value="stats.pending"
          icon="fa fa-clock"
          color="purple"
        />
      </div>

      <!-- Table -->
      <div class="table-card">
        <div v-if="loading" class="table-loading">
          <i class="fa fa-spinner fa-spin"></i> Carregando...
        </div>

        <div v-else-if="pages.data.length === 0" class="table-empty">
          <i class="fa fa-inbox"></i>
          <p>Nenhuma página encontrada</p>
          <Button
            label="Criar Primeira Página"
            icon="fa fa-plus"
            @click="createPage"
            severity="success"
          />
        </div>

        <table v-else class="data-table">
          <thead>
            <tr>
              <th @click="sort('title')">
                Título
                <i v-if="sortField === 'title'" :class="sortIcon"></i>
              </th>
              <th>Slug</th>
              <th @click="sort('status')">
                Status
                <i v-if="sortField === 'status'" :class="sortIcon"></i>
              </th>
              <th>Versões</th>
              <th>Criado por</th>
              <th @click="sort('published_at')">
                Publicado em
                <i v-if="sortField === 'published_at'" :class="sortIcon"></i>
              </th>
              <th class="text-center">Ações</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="page in pages.data" :key="page.id">
              <td>
                <div class="page-title-cell">
                  <strong>{{ page.title }}</strong>
                  <span v-if="page.meta_title" class="meta-title">{{ page.meta_title }}</span>
                </div>
              </td>
              <td>
                <code class="slug-code">{{ page.slug }}</code>
              </td>
              <td>
                <span :class="['badge', 'badge-' + page.status]">
                  {{ getStatusLabel(page.status) }}
                </span>
              </td>
              <td>
                <div class="versions-cell">
                  <i class="fa fa-code-branch"></i>
                  <span>{{ page.versions_count || 0 }} versões</span>
                </div>
              </td>
              <td>
                <div class="user-badge">
                  <i class="fa fa-user-circle"></i>
                  {{ page.creator?.name }}
                </div>
              </td>
              <td>
                <span v-if="page.published_at">
                  {{ formatDate(page.published_at) }}
                </span>
                <span v-else class="text-muted">-</span>
              </td>
              <td class="text-center">
                <div class="action-buttons">
                  <button @click="previewPage(page)" class="btn-icon" title="Preview">
                    <i class="fa fa-eye"></i>
                  </button>
                  <button @click="editPage(page)" class="btn-icon" title="Editar">
                    <i class="fa fa-edit"></i>
                  </button>
                  <button
                    v-if="page.status === 'draft'"
                    @click="publishPage(page)"
                    class="btn-icon"
                    title="Publicar"
                  >
                    <i class="fa fa-paper-plane"></i>
                  </button>
                  <button @click="duplicatePage(page)" class="btn-icon" title="Duplicar">
                    <i class="fa fa-copy"></i>
                  </button>
                  <button @click="viewVersions(page)" class="btn-icon" title="Versões">
                    <i class="fa fa-code-branch"></i>
                  </button>
                  <button @click="deletePage(page)" class="btn-icon btn-danger" title="Excluir">
                    <i class="fa fa-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Pagination -->
        <div v-if="pages.data.length > 0" class="pagination">
          <div class="pagination-info">
            Mostrando {{ pages.from }} a {{ pages.to }} de {{ pages.total }} registros
          </div>
          <div class="pagination-buttons">
            <button
              @click="changePage(page)"
              v-for="page in paginationPages"
              :key="page"
              :class="['pagination-btn', { active: page === pages.current_page }]"
              :disabled="page === '...'"
            >
              {{ page }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de confirmação de exclusão -->
    <Modal
      v-model:visible="showDeleteModal"
      title="Confirmar Exclusão"
      @confirm="confirmDelete"
    >
      <p>Tem certeza que deseja excluir a página <strong>{{ pageToDelete?.title }}</strong>?</p>
      <p class="text-muted">Esta ação não pode ser desfeita.</p>
    </Modal>

    <!-- Modal de Preview -->
    <Modal
      v-model:visible="showPreviewModal"
      :title="'Preview: ' + pageToPreview?.title"
      size="fullscreen"
      :showFooter="false"
    >
      <div class="preview-container">
        <iframe
          v-if="previewUrl"
          :src="previewUrl"
          class="preview-iframe"
        ></iframe>
        <div v-else class="preview-loading">
          <i class="fa fa-spinner fa-spin"></i>
          Carregando preview...
        </div>
      </div>
    </Modal>
  </MainLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Button from '@/Components/Button.vue';
import Input from '@/Components/Input.vue';
import StatCard from '@/Components/StatCard.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
  pages: Object,
  sites: Array,
  currentSite: Object,
  users: Array,
  stats: Object,
});

const breadcrumbs = computed(() => [
  { label: 'Dashboard', to: '/dashboard' },
  { label: 'CMS', to: '/cms' },
  { label: 'Sites', to: '/cms/sites' },
  { label: props.currentSite?.name, active: true }
]);

const loading = ref(false);
const selectedSiteId = ref(props.currentSite?.id);
const filters = ref({
  search: '',
  status: '',
  created_by: '',
});

const sortField = ref('published_at');
const sortDirection = ref('desc');
const showDeleteModal = ref(false);
const pageToDelete = ref(null);
const showPreviewModal = ref(false);
const pageToPreview = ref(null);
const previewUrl = ref('');

const sortIcon = computed(() => {
  return sortDirection.value === 'asc' ? 'fa fa-sort-up' : 'fa fa-sort-down';
});

const paginationPages = computed(() => {
  const pages = [];
  const current = props.pages.current_page;
  const last = props.pages.last_page;

  if (last <= 7) {
    for (let i = 1; i <= last; i++) {
      pages.push(i);
    }
  } else {
    if (current <= 3) {
      pages.push(1, 2, 3, 4, '...', last);
    } else if (current >= last - 2) {
      pages.push(1, '...', last - 3, last - 2, last - 1, last);
    } else {
      pages.push(1, '...', current - 1, current, current + 1, '...', last);
    }
  }

  return pages;
});

watch(() => props.currentSite?.id, (newId) => {
  selectedSiteId.value = newId;
});

let searchTimeout = null;
const debouncedSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    loadPages();
  }, 500);
};

const changeSite = () => {
  router.visit(`/cms/sites/${selectedSiteId.value}/pages`);
};

const loadPages = () => {
  loading.value = true;
  router.get(`/cms/sites/${selectedSiteId.value}/pages`, {
    ...filters.value,
    sort: sortField.value,
    direction: sortDirection.value,
  }, {
    preserveState: true,
    preserveScroll: true,
    onFinish: () => loading.value = false,
  });
};

const sort = (field) => {
  if (sortField.value === field) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortField.value = field;
    sortDirection.value = 'asc';
  }
  loadPages();
};

const changePage = (page) => {
  if (page === '...') return;
  router.get(`/cms/sites/${selectedSiteId.value}/pages?page=${page}`, filters.value, {
    preserveState: true,
  });
};

const createPage = () => {
  router.visit(`/cms/sites/${selectedSiteId.value}/pages/create`);
};

const editPage = (page) => {
  router.visit(`/cms/sites/${selectedSiteId.value}/pages/${page.id}/edit`);
};

const previewPage = (page) => {
  pageToPreview.value = page;
  previewUrl.value = page.preview_url;
  showPreviewModal.value = true;
};

const publishPage = (page) => {
  router.post(`/cms/sites/${selectedSiteId.value}/pages/${page.id}/publish`);
};

const duplicatePage = (page) => {
  router.post(`/cms/sites/${selectedSiteId.value}/pages/${page.id}/duplicate`);
};

const viewVersions = (page) => {
  router.visit(`/cms/sites/${selectedSiteId.value}/pages/${page.id}/versions`);
};

const deletePage = (page) => {
  pageToDelete.value = page;
  showDeleteModal.value = true;
};

const confirmDelete = () => {
  router.delete(`/cms/sites/${selectedSiteId.value}/pages/${pageToDelete.value.id}`, {
    onSuccess: () => {
      showDeleteModal.value = false;
      pageToDelete.value = null;
    },
  });
};

const getStatusLabel = (status) => {
  const labels = {
    draft: 'Rascunho',
    pending: 'Pendente',
    published: 'Publicado',
  };
  return labels[status] || status;
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('pt-BR', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
};
</script>

