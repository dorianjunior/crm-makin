<template>
  <MainLayout title="Posts do Blog">
    <div class="page-container">
      <!-- Header -->
      <div class="page-header">
        <div class="page-header__content">
          <h1 class="page-header__title">POSTS DO BLOG</h1>
          <p class="page-header__subtitle">Gerencie artigos e conteúdo do blog</p>
        </div>
        <div class="page-header__actions">
          <button class="btn btn--secondary" @click="router.visit('/cms/posts/categories')">
            <i class="fas fa-tags"></i>
            Categorias
          </button>
          <button class="btn" @click="createPost">
            <i class="fas fa-plus"></i>
            Novo Post
          </button>
        </div>
      </div>

      <!-- Stats -->
      <div class="grid grid--5">
        <div class="stat-card">
          <div class="stat-card__label">Total</div>
          <div class="stat-card__value">{{ stats.total }}</div>
        </div>
        <div class="stat-card">
          <div class="stat-card__label">Publicados</div>
          <div class="stat-card__value">{{ stats.published }}</div>
        </div>
        <div class="stat-card">
          <div class="stat-card__label">Rascunhos</div>
          <div class="stat-card__value">{{ stats.draft }}</div>
        </div>
        <div class="stat-card">
          <div class="stat-card__label">Comentários</div>
          <div class="stat-card__value">{{ stats.comments }}</div>
        </div>
        <div class="stat-card">
          <div class="stat-card__label">Visualizações</div>
          <div class="stat-card__value">{{ stats.total_views }}</div>
        </div>
      </div>

      <!-- Filters -->
      <div class="filters">
        <button
          v-for="filter in filters"
          :key="filter.value"
          class="filter-btn"
          :class="{ 'filter-btn--active': activeFilter === filter.value }"
          @click="activeFilter = filter.value"
        >
          {{ filter.label }} ({{ filter.count }})
        </button>
      </div>

      <!-- Posts List -->
      <div v-if="filteredPosts.length > 0" class="posts-list">
        <div v-for="post in filteredPosts" :key="post.id" class="post-card">
          <div v-if="post.featured_image" class="post-card__image" :style="`background-image: url(${post.featured_image})`">
            <span v-if="post.is_featured" class="featured-badge">
              <i class="fas fa-star"></i>
              DESTAQUE
            </span>
          </div>
          <div class="post-card__content">
            <div class="post-card__header">
              <div class="post-card__categories">
                <span v-for="category in post.categories" :key="category" class="category-tag">
                  {{ category }}
                </span>
              </div>
              <span :class="['badge', `badge--${getStatusColor(post.status)}`]">
                {{ post.status.toUpperCase() }}
              </span>
            </div>

            <h3 class="post-card__title">{{ post.title }}</h3>
            <p class="post-card__excerpt">{{ post.excerpt }}</p>

            <div class="post-card__meta">
              <div class="meta-item">
                <i class="fas fa-user"></i>
                {{ post.author_name }}
              </div>
              <div class="meta-item">
                <i class="fas fa-calendar"></i>
                {{ formatDate(post.published_at || post.created_at) }}
              </div>
              <div class="meta-item">
                <i class="fas fa-eye"></i>
                {{ post.views_count }} views
              </div>
              <div class="meta-item">
                <i class="fas fa-comments"></i>
                {{ post.comments_count }} comentários
              </div>
            </div>

            <div class="post-card__actions">
              <button class="btn btn--sm" @click="editPost(post.id)">
                <i class="fas fa-edit"></i>
                Editar
              </button>
              <button class="btn btn--sm btn--secondary" @click="viewPost(post.slug)">
                <i class="fas fa-eye"></i>
                Visualizar
              </button>
              <button class="btn btn--sm btn--danger" @click="deletePost(post.id)">
                <i class="fas fa-trash"></i>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="empty-state">
        <i class="fas fa-blog empty-state__icon"></i>
        <h3 class="empty-state__title">Nenhum post encontrado</h3>
        <p class="empty-state__text">Crie seu primeiro post para começar seu blog</p>
        <button class="btn" @click="createPost">
          <i class="fas fa-plus"></i>
          Criar Primeiro Post
        </button>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';

const props = defineProps({
  posts: Array,
  stats: Object,
});

const activeFilter = ref('all');

const filters = computed(() => [
  { label: 'Todos', value: 'all', count: props.posts?.length || 0 },
  { label: 'Publicados', value: 'published', count: props.posts?.filter(p => p.status === 'published').length || 0 },
  { label: 'Rascunhos', value: 'draft', count: props.posts?.filter(p => p.status === 'draft').length || 0 },
  { label: 'Destaques', value: 'featured', count: props.posts?.filter(p => p.is_featured).length || 0 },
]);

const filteredPosts = computed(() => {
  if (!props.posts) return [];
  if (activeFilter.value === 'all') return props.posts;
  if (activeFilter.value === 'featured') return props.posts.filter(p => p.is_featured);
  return props.posts.filter(p => p.status === activeFilter.value);
});

const getStatusColor = (status) => {
  const colors = {
    published: 'success',
    draft: 'warning',
    scheduled: 'info',
    archived: 'neutral'
  };
  return colors[status] || 'neutral';
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('pt-BR');
};

const createPost = () => {
  router.visit('/cms/posts/create');
};

const editPost = (id) => {
  router.visit(`/cms/posts/${id}/edit`);
};

const viewPost = (slug) => {
  window.open(`/blog/${slug}`, '_blank');
};

const deletePost = (id) => {
  if (confirm('Tem certeza que deseja excluir este post?')) {
    router.delete(`/cms/posts/${id}`);
  }
};
</script>

<style scoped lang="scss">
.page-container {
  padding: 32px;
}

.filters {
  display: flex;
  gap: 16px;
  margin: 32px 0;
  flex-wrap: wrap;
}

.filter-btn {
  padding: 12px 24px;
  border: 2px solid var(--border-color);
  background: var(--bg-primary);
  color: var(--text-primary);
  font-family: 'Space Grotesk', sans-serif;
  font-size: 14px;
  font-weight: 700;
  text-transform: uppercase;
  cursor: pointer;
  transition: all 0.2s ease;

  &:hover {
    border-color: #FF6B35;
    transform: translateX(2px);
  }

  &--active {
    background: #FF6B35;
    color: var(--bg-primary);
    border-color: #FF6B35;
  }
}

.posts-list {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(450px, 1fr));
  gap: 24px;
}

.post-card {
  border: 2px solid var(--border-color);
  background: var(--bg-primary);
  display: flex;
  flex-direction: column;
  transition: all 0.2s ease;

  &:hover {
    border-color: #FF6B35;
    transform: translateY(-2px);
  }

  &__image {
    height: 240px;
    background-size: cover;
    background-position: center;
    border-bottom: 2px solid var(--border-color);
    position: relative;

    .featured-badge {
      position: absolute;
      top: 16px;
      right: 16px;
      background: #FF6B35;
      color: var(--bg-primary);
      padding: 8px 16px;
      font-family: 'Space Grotesk', sans-serif;
      font-size: 11px;
      font-weight: 700;
      letter-spacing: 0.1em;
      display: flex;
      align-items: center;
      gap: 6px;
    }
  }

  &__content {
    padding: 24px;
    flex: 1;
    display: flex;
    flex-direction: column;
  }

  &__header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 16px;
  }

  &__categories {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    flex: 1;

    .category-tag {
      padding: 4px 12px;
      border: 2px solid var(--border-color);
      font-family: 'JetBrains Mono', monospace;
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      color: var(--text-secondary);
    }
  }

  &__title {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 24px;
    font-weight: 700;
    margin: 0 0 12px;
    color: var(--text-primary);
    line-height: 1.3;
  }

  &__excerpt {
    font-size: 14px;
    line-height: 1.6;
    color: var(--text-secondary);
    margin: 0 0 20px;
    flex: 1;
  }

  &__meta {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
    margin-bottom: 20px;
    padding-top: 16px;
    border-top: 2px solid var(--border-color);

    .meta-item {
      display: flex;
      align-items: center;
      gap: 6px;
      font-size: 12px;
      color: var(--text-secondary);

      i {
        color: #FF6B35;
      }
    }
  }

  &__actions {
    display: flex;
    gap: 8px;
  }
}

@media (max-width: 768px) {
  .posts-list {
    grid-template-columns: 1fr;
  }

  .post-card__actions {
    flex-direction: column;

    .btn {
      width: 100%;
    }
  }
}
</style>
