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

<style scoped lang="scss">
.posts-index {
  padding: 2rem;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.page-title {
  font-size: 2rem;
  font-weight: 700;
  color: var(--text-primary);
  margin-bottom: 0.5rem;
}

.header-actions {
  display: flex;
  gap: 1rem;
  align-items: center;
}

.site-selector {
  padding: 0.75rem;
  border: 1px solid var(--border-color);
  border-radius: var(--border-radius);
  background: var(--surface-card);
  color: var(--text-primary);
  font-size: 0.875rem;
  min-width: 200px;
  cursor: pointer;

  &:focus {
    outline: none;
    border-color: var(--primary-color);
  }
}

.filters-card {
  background: var(--surface-card);
  border-radius: var(--border-radius);
  padding: 1.5rem;
  margin-bottom: 2rem;
  box-shadow: var(--shadow-sm);
}

.filters-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1rem;
}

.filter-item {
  label {
    display: block;
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--text-secondary);
    margin-bottom: 0.5rem;
  }
}

.form-select {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid var(--border-color);
  border-radius: var(--border-radius);
  background: var(--surface-ground);
  color: var(--text-primary);
  font-size: 0.875rem;

  &:focus {
    outline: none;
    border-color: var(--primary-color);
  }
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.loading,
.empty-state {
  background: var(--surface-card);
  border-radius: var(--border-radius);
  padding: 4rem 2rem;
  text-align: center;
  box-shadow: var(--shadow-sm);

  i {
    font-size: 3rem;
    color: var(--text-secondary);
    margin-bottom: 1rem;
    display: block;
  }

  h3 {
    font-size: 1.5rem;
    color: var(--text-primary);
    margin-bottom: 0.5rem;
  }

  p {
    color: var(--text-secondary);
    margin-bottom: 1.5rem;
  }
}

.posts-container {
  background: var(--surface-card);
  border-radius: var(--border-radius);
  padding: 2rem;
  box-shadow: var(--shadow-sm);
}

.posts-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 2rem;
  margin-bottom: 2rem;
}

.post-card {
  background: var(--surface-ground);
  border-radius: var(--border-radius);
  overflow: hidden;
  border: 1px solid var(--border-color);
  transition: all 0.3s;

  &:hover {
    box-shadow: var(--shadow-lg);
    transform: translateY(-4px);
  }
}

.post-image {
  position: relative;
  height: 200px;
  overflow: hidden;

  img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s;
  }

  &:hover img {
    transform: scale(1.05);
  }

  .image-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s;
  }

  &:hover .image-overlay {
    opacity: 1;
  }

  .overlay-btn {
    padding: 0.75rem 1.5rem;
    background: white;
    color: var(--text-primary);
    border: none;
    border-radius: var(--border-radius);
    font-weight: 600;
    cursor: pointer;
    transition: transform 0.2s;

    &:hover {
      transform: scale(1.05);
    }
  }
}

.post-image-placeholder {
  height: 200px;
  background: var(--surface-ground);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: var(--text-secondary);
  gap: 0.5rem;

  i {
    font-size: 3rem;
  }
}

.post-content {
  padding: 1.5rem;
}

.post-meta {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 1rem;
}

.badge {
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;

  &.badge-draft { background: #FEF3C7; color: #92400E; }
  &.badge-pending { background: #E3F2FD; color: #1976D2; }
  &.badge-published { background: #C8E6C9; color: #2E7D32; }
}

.category-badge {
  padding: 0.25rem 0.75rem;
  background: var(--primary-color);
  color: white;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 0.25rem;

  i {
    font-size: 0.7rem;
  }
}

.post-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: var(--text-primary);
  margin-bottom: 0.75rem;
  line-height: 1.3;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.post-excerpt {
  color: var(--text-secondary);
  font-size: 0.875rem;
  line-height: 1.5;
  margin-bottom: 1rem;
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.post-info {
  display: flex;
  gap: 1rem;
  margin-bottom: 1rem;
  padding: 0.75rem;
  background: var(--surface-card);
  border-radius: var(--border-radius);
}

.info-item {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  font-size: 0.8rem;
  color: var(--text-secondary);

  i {
    color: var(--primary-color);
  }
}

.post-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  margin-bottom: 1rem;
}

.tag {
  padding: 0.25rem 0.5rem;
  background: var(--surface-card);
  border-radius: 4px;
  font-size: 0.75rem;
  color: var(--text-secondary);
}

.tag-more {
  padding: 0.25rem 0.5rem;
  background: var(--primary-color);
  color: white;
  border-radius: 4px;
  font-size: 0.75rem;
  font-weight: 600;
}

.post-actions {
  display: flex;
  gap: 0.5rem;
  align-items: center;
  padding-top: 1rem;
  border-top: 1px solid var(--border-color);
}

.btn-icon {
  padding: 0.5rem;
  background: transparent;
  border: none;
  border-radius: var(--border-radius);
  color: var(--text-secondary);
  cursor: pointer;
  transition: all 0.2s;

  &:hover {
    background: var(--surface-card);
    color: var(--primary-color);
  }

  &.btn-danger:hover {
    color: var(--red-500);
  }
}

.pagination {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 1.5rem;
  border-top: 1px solid var(--border-color);
}

.pagination-info {
  color: var(--text-secondary);
  font-size: 0.875rem;
}

.pagination-buttons {
  display: flex;
  gap: 0.5rem;
}

.pagination-btn {
  padding: 0.5rem 0.75rem;
  background: var(--surface-ground);
  border: 1px solid var(--border-color);
  border-radius: var(--border-radius);
  color: var(--text-primary);
  cursor: pointer;
  transition: all 0.2s;

  &:hover:not(:disabled) {
    background: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
  }

  &.active {
    background: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
  }

  &:disabled {
    cursor: default;
    opacity: 0.5;
  }
}

.preview-container {
  height: 80vh;
}

.preview-iframe {
  width: 100%;
  height: 100%;
  border: none;
  border-radius: var(--border-radius);
  background: white;
}

.category-manager {
  .category-form {
    display: flex;
    gap: 0.75rem;
    margin-bottom: 1.5rem;
  }

  .category-list {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
  }

  .category-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.75rem;
    background: var(--surface-ground);
    border-radius: var(--border-radius);
    border: 1px solid var(--border-color);
  }

  .category-name {
    flex: 1;
    font-weight: 600;
    color: var(--text-primary);
  }

  .category-count {
    font-size: 0.875rem;
    color: var(--text-secondary);
  }

  .btn-icon-sm {
    padding: 0.25rem 0.5rem;
    background: transparent;
    border: none;
    border-radius: var(--border-radius);
    color: var(--text-secondary);
    cursor: pointer;
    transition: all 0.2s;

    &:hover {
      color: var(--red-500);
    }
  }
}

.text-muted {
  color: var(--text-secondary);
  font-size: 0.875rem;
}

@media (max-width: 768px) {
  .posts-grid {
    grid-template-columns: 1fr;
  }

  .header-actions {
    flex-direction: column;
    align-items: stretch;

    .site-selector {
      width: 100%;
    }
  }
}
</style>
