<template>
  <MainLayout>
    <div class="posts-index">
      <!-- Header -->
      <div class="page-header">
        <div>
          <h1 class="page-title">Posts - {{ currentSite?.name }}</h1>
          <Breadcrumbs :items="breadcrumbs" />
        </div>
        <div class="header-actions">
          <select v-model="selectedSiteId" @change="changeSite" class="site-selector">
            <option v-for="site in sites" :key="site.id" :value="site.id">
              {{ site.name }}
            </option>
          </select>
          <Button
            label="Novo Post"
            icon="fa fa-plus"
            @click="createPost"
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
              placeholder="Título ou conteúdo..."
              icon="fa fa-search"
              @input="debouncedSearch"
            />
          </div>

          <div class="filter-item">
            <label>Status</label>
            <select v-model="filters.status" @change="loadPosts" class="form-select">
              <option value="">Todos</option>
              <option value="draft">Rascunho</option>
              <option value="pending">Pendente</option>
              <option value="published">Publicado</option>
            </select>
          </div>

          <div class="filter-item">
            <label>Categoria</label>
            <select v-model="filters.category_id" @change="loadPosts" class="form-select">
              <option value="">Todas</option>
              <option v-for="category in categories" :key="category.id" :value="category.id">
                {{ category.name }}
              </option>
            </select>
          </div>

          <div class="filter-item">
            <label>Autor</label>
            <select v-model="filters.author_id" @change="loadPosts" class="form-select">
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
          title="Total de Posts"
          :value="stats.total"
          icon="fa fa-blog"
          color="blue"
        />
        <StatCard
          title="Publicados"
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
          title="Categorias"
          :value="categories.length"
          icon="fa fa-folder"
          color="purple"
        />
      </div>

      <!-- Posts Grid -->
      <div v-if="loading" class="loading">
        <i class="fa fa-spinner fa-spin"></i> Carregando...
      </div>

      <div v-else-if="posts.data.length === 0" class="empty-state">
        <i class="fa fa-blog"></i>
        <h3>Nenhum post encontrado</h3>
        <p>Crie seu primeiro post para começar</p>
        <Button
          label="Criar Primeiro Post"
          icon="fa fa-plus"
          @click="createPost"
          severity="success"
        />
      </div>

      <div v-else class="posts-container">
        <div class="posts-grid">
          <div v-for="post in posts.data" :key="post.id" class="post-card">
            <div v-if="post.featured_image" class="post-image">
              <img :src="post.featured_image" :alt="post.title" />
              <div class="image-overlay">
                <button @click="previewPost(post)" class="overlay-btn">
                  <i class="fa fa-eye"></i> Preview
                </button>
              </div>
            </div>
            <div v-else class="post-image-placeholder">
              <i class="fa fa-image"></i>
              <span>Sem imagem</span>
            </div>

            <div class="post-content">
              <div class="post-meta">
                <span :class="['badge', 'badge-' + post.status]">
                  {{ getStatusLabel(post.status) }}
                </span>
                <span v-if="post.category" class="category-badge">
                  <i class="fa fa-folder"></i>
                  {{ post.category.name }}
                </span>
              </div>

              <h3 class="post-title">{{ post.title }}</h3>

              <p v-if="post.excerpt" class="post-excerpt">
                {{ post.excerpt }}
              </p>

              <div class="post-info">
                <div class="info-item">
                  <i class="fa fa-user"></i>
                  <span>{{ post.author?.name }}</span>
                </div>
                <div v-if="post.published_at" class="info-item">
                  <i class="fa fa-calendar"></i>
                  <span>{{ formatDate(post.published_at) }}</span>
                </div>
                <div v-if="post.read_time" class="info-item">
                  <i class="fa fa-clock"></i>
                  <span>{{ post.read_time }} min</span>
                </div>
              </div>

              <div v-if="post.tags && post.tags.length > 0" class="post-tags">
                <span v-for="tag in post.tags.slice(0, 3)" :key="tag" class="tag">
                  {{ tag }}
                </span>
                <span v-if="post.tags.length > 3" class="tag-more">
                  +{{ post.tags.length - 3 }}
                </span>
              </div>

              <div class="post-actions">
                <Button
                  label="Editar"
                  icon="fa fa-edit"
                  @click="editPost(post)"
                  size="small"
                  outlined
                />
                <button @click="duplicatePost(post)" class="btn-icon" title="Duplicar">
                  <i class="fa fa-copy"></i>
                </button>
                <button
                  v-if="post.status === 'draft'"
                  @click="publishPost(post)"
                  class="btn-icon"
                  title="Publicar"
                >
                  <i class="fa fa-paper-plane"></i>
                </button>
                <button @click="deletePost(post)" class="btn-icon btn-danger" title="Excluir">
                  <i class="fa fa-trash"></i>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="posts.data.length > 0" class="pagination">
          <div class="pagination-info">
            Mostrando {{ posts.from }} a {{ posts.to }} de {{ posts.total }} registros
          </div>
          <div class="pagination-buttons">
            <button
              @click="changePage(page)"
              v-for="page in paginationPages"
              :key="page"
              :class="['pagination-btn', { active: page === posts.current_page }]"
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
      <p>Tem certeza que deseja excluir o post <strong>{{ postToDelete?.title }}</strong>?</p>
      <p class="text-muted">Esta ação não pode ser desfeita.</p>
    </Modal>

    <!-- Modal de Preview -->
    <Modal
      v-model:visible="showPreviewModal"
      :title="'Preview: ' + postToPreview?.title"
      size="fullscreen"
      :showFooter="false"
    >
      <div class="preview-container">
        <iframe
          v-if="previewUrl"
          :src="previewUrl"
          class="preview-iframe"
        ></iframe>
      </div>
    </Modal>

    <!-- Modal de Categoria -->
    <Modal
      v-model:visible="showCategoryModal"
      title="Gerenciar Categorias"
      size="medium"
    >
      <div class="category-manager">
        <div class="category-form">
          <Input
            v-model="newCategoryName"
            placeholder="Nome da nova categoria"
            @keyup.enter="addCategory"
          />
          <Button
            label="Adicionar"
            icon="fa fa-plus"
            @click="addCategory"
            size="small"
          />
        </div>

        <div class="category-list">
          <div v-for="category in categories" :key="category.id" class="category-item">
            <span class="category-name">{{ category.name }}</span>
            <span class="category-count">{{ category.posts_count }} posts</span>
            <button @click="deleteCategory(category)" class="btn-icon-sm btn-danger">
              <i class="fa fa-trash"></i>
            </button>
          </div>
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
  posts: Object,
  sites: Array,
  currentSite: Object,
  categories: Array,
  users: Array,
  stats: Object,
});

const breadcrumbs = computed(() => [
  { label: 'Dashboard', to: '/dashboard' },
  { label: 'CMS', to: '/cms' },
  { label: 'Sites', to: '/cms/sites' },
  { label: props.currentSite?.name, to: `/cms/sites/${props.currentSite?.id}` },
  { label: 'Posts', active: true }
]);

const loading = ref(false);
const selectedSiteId = ref(props.currentSite?.id);
const filters = ref({
  search: '',
  status: '',
  category_id: '',
  author_id: '',
});

const showDeleteModal = ref(false);
const postToDelete = ref(null);
const showPreviewModal = ref(false);
const postToPreview = ref(null);
const previewUrl = ref('');
const showCategoryModal = ref(false);
const newCategoryName = ref('');

const paginationPages = computed(() => {
  const pages = [];
  const current = props.posts.current_page;
  const last = props.posts.last_page;

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
    loadPosts();
  }, 500);
};

const changeSite = () => {
  router.visit(`/cms/sites/${selectedSiteId.value}/posts`);
};

const loadPosts = () => {
  loading.value = true;
  router.get(`/cms/sites/${selectedSiteId.value}/posts`, filters.value, {
    preserveState: true,
    preserveScroll: true,
    onFinish: () => loading.value = false,
  });
};

const changePage = (page) => {
  if (page === '...') return;
  router.get(`/cms/sites/${selectedSiteId.value}/posts?page=${page}`, filters.value, {
    preserveState: true,
  });
};

const createPost = () => {
  router.visit(`/cms/sites/${selectedSiteId.value}/posts/create`);
};

const editPost = (post) => {
  router.visit(`/cms/sites/${selectedSiteId.value}/posts/${post.id}/edit`);
};

const previewPost = (post) => {
  postToPreview.value = post;
  previewUrl.value = post.preview_url;
  showPreviewModal.value = true;
};

const publishPost = (post) => {
  router.post(`/cms/sites/${selectedSiteId.value}/posts/${post.id}/publish`);
};

const duplicatePost = (post) => {
  router.post(`/cms/sites/${selectedSiteId.value}/posts/${post.id}/duplicate`);
};

const deletePost = (post) => {
  postToDelete.value = post;
  showDeleteModal.value = true;
};

const confirmDelete = () => {
  router.delete(`/cms/sites/${selectedSiteId.value}/posts/${postToDelete.value.id}`, {
    onSuccess: () => {
      showDeleteModal.value = false;
      postToDelete.value = null;
    },
  });
};

const addCategory = () => {
  if (!newCategoryName.value.trim()) return;

  router.post(`/cms/sites/${selectedSiteId.value}/categories`, {
    name: newCategoryName.value
  }, {
    onSuccess: () => {
      newCategoryName.value = '';
    },
  });
};

const deleteCategory = (category) => {
  if (category.posts_count > 0) {
    alert('Não é possível excluir uma categoria com posts associados.');
    return;
  }

  router.delete(`/cms/sites/${selectedSiteId.value}/categories/${category.id}`);
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
  });
};
</script>

