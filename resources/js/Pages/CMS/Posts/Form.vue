<template>
  <MainLayout title="Post do Blog">
    <div class="posts-form-page">
      <Breadcrumbs :items="breadcrumbs" />

      <div class="page-header">
        <div>
          <h1>{{ post?.id ? 'Editar Post' : 'Novo Post' }}</h1>
          <p class="subtitle">{{ post?.id ? 'Atualize as informações do post' : 'Crie um novo post para o blog' }}</p>
        </div>
        <div class="header-actions">
          <Button variant="secondary" @click="saveDraft" :disabled="saving">
            <i class="fa fa-save"></i>
            Salvar Rascunho
          </Button>
          <Button variant="primary" @click="saveAndPublish" :disabled="saving">
            <i class="fa fa-paper-plane"></i>
            {{ post?.status === 'published' ? 'Atualizar' : 'Publicar' }}
          </Button>
        </div>
      </div>

      <div class="form-layout">
        <!-- Main Content Column -->
        <div class="main-content">
          <div class="card">
            <div class="form-group">
              <label>Título *</label>
              <Input
                v-model="form.title"
                placeholder="Digite o título do post"
                @input="generateSlug"
              />
              <span v-if="errors.title" class="error-message">{{ errors.title }}</span>
            </div>

            <div class="form-group">
              <label>Slug</label>
              <Input
                v-model="form.slug"
                placeholder="url-do-post"
              />
              <span class="help-text">URL amigável: {{ baseUrl }}/blog/{{ form.slug || 'url-do-post' }}</span>
              <span v-if="errors.slug" class="error-message">{{ errors.slug }}</span>
            </div>

            <div class="form-group">
              <label>Resumo</label>
              <textarea
                v-model="form.excerpt"
                rows="3"
                placeholder="Breve descrição do post (será exibida na listagem)"
              ></textarea>
              <span class="help-text">{{ form.excerpt?.length || 0 }}/300 caracteres</span>
            </div>

            <div class="form-group">
              <label>Conteúdo *</label>
              <div class="editor-wrapper">
                <div class="editor-toolbar">
                  <div class="toolbar-group">
                    <button type="button" @click="execCommand('bold')" class="toolbar-btn" title="Negrito">
                      <i class="fa fa-bold"></i>
                    </button>
                    <button type="button" @click="execCommand('italic')" class="toolbar-btn" title="Itálico">
                      <i class="fa fa-italic"></i>
                    </button>
                    <button type="button" @click="execCommand('underline')" class="toolbar-btn" title="Sublinhado">
                      <i class="fa fa-underline"></i>
                    </button>
                    <button type="button" @click="execCommand('strikeThrough')" class="toolbar-btn" title="Tachado">
                      <i class="fa fa-strikethrough"></i>
                    </button>
                  </div>

                  <div class="toolbar-divider"></div>

                  <div class="toolbar-group">
                    <button type="button" @click="execCommand('formatBlock', '<h2>')" class="toolbar-btn" title="Título 2">
                      H2
                    </button>
                    <button type="button" @click="execCommand('formatBlock', '<h3>')" class="toolbar-btn" title="Título 3">
                      H3
                    </button>
                    <button type="button" @click="execCommand('formatBlock', '<p>')" class="toolbar-btn" title="Parágrafo">
                      P
                    </button>
                  </div>

                  <div class="toolbar-divider"></div>

                  <div class="toolbar-group">
                    <button type="button" @click="execCommand('insertUnorderedList')" class="toolbar-btn" title="Lista">
                      <i class="fa fa-list-ul"></i>
                    </button>
                    <button type="button" @click="execCommand('insertOrderedList')" class="toolbar-btn" title="Lista Numerada">
                      <i class="fa fa-list-ol"></i>
                    </button>
                    <button type="button" @click="execCommand('indent')" class="toolbar-btn" title="Aumentar Recuo">
                      <i class="fa fa-indent"></i>
                    </button>
                    <button type="button" @click="execCommand('outdent')" class="toolbar-btn" title="Diminuir Recuo">
                      <i class="fa fa-outdent"></i>
                    </button>
                  </div>

                  <div class="toolbar-divider"></div>

                  <div class="toolbar-group">
                    <button type="button" @click="execCommand('justifyLeft')" class="toolbar-btn" title="Alinhar à Esquerda">
                      <i class="fa fa-align-left"></i>
                    </button>
                    <button type="button" @click="execCommand('justifyCenter')" class="toolbar-btn" title="Centralizar">
                      <i class="fa fa-align-center"></i>
                    </button>
                    <button type="button" @click="execCommand('justifyRight')" class="toolbar-btn" title="Alinhar à Direita">
                      <i class="fa fa-align-right"></i>
                    </button>
                    <button type="button" @click="execCommand('justifyFull')" class="toolbar-btn" title="Justificar">
                      <i class="fa fa-align-justify"></i>
                    </button>
                  </div>

                  <div class="toolbar-divider"></div>

                  <div class="toolbar-group">
                    <button type="button" @click="insertLink" class="toolbar-btn" title="Inserir Link">
                      <i class="fa fa-link"></i>
                    </button>
                    <button type="button" @click="insertImage" class="toolbar-btn" title="Inserir Imagem">
                      <i class="fa fa-image"></i>
                    </button>
                    <button type="button" @click="insertVideo" class="toolbar-btn" title="Inserir Vídeo">
                      <i class="fa fa-video"></i>
                    </button>
                  </div>

                  <div class="toolbar-divider"></div>

                  <div class="toolbar-group">
                    <button type="button" @click="toggleCodeView" class="toolbar-btn" :class="{ active: showCode }" title="Ver HTML">
                      <i class="fa fa-code"></i>
                    </button>
                  </div>
                </div>

                <div v-if="!showCode"
                  ref="editorContent"
                  class="editor-content"
                  contenteditable="true"
                  @input="updateContent"
                  @paste="handlePaste"
                ></div>

                <textarea
                  v-else
                  v-model="form.content"
                  class="editor-code"
                  @input="updateFromCode"
                ></textarea>
              </div>
              <span v-if="errors.content" class="error-message">{{ errors.content }}</span>
            </div>
          </div>

          <!-- SEO Section -->
          <div class="card">
            <h3 class="card-title">
              <i class="fa fa-search"></i>
              SEO e Meta Tags
            </h3>

            <div class="form-group">
              <label>Meta Título</label>
              <Input
                v-model="form.meta_title"
                placeholder="Título para mecanismos de busca"
                maxlength="60"
              />
              <span class="help-text">{{ form.meta_title?.length || 0 }}/60 caracteres (ideal: 50-60)</span>
            </div>

            <div class="form-group">
              <label>Meta Descrição</label>
              <textarea
                v-model="form.meta_description"
                rows="3"
                placeholder="Descrição para mecanismos de busca"
                maxlength="160"
              ></textarea>
              <span class="help-text">{{ form.meta_description?.length || 0 }}/160 caracteres (ideal: 150-160)</span>
            </div>

            <div class="form-group">
              <label>Palavras-chave (separadas por vírgula)</label>
              <Input
                v-model="form.meta_keywords"
                placeholder="marketing, vendas, CRM"
              />
            </div>

            <div class="form-group">
              <label>
                <input type="checkbox" v-model="form.index_page">
                Permitir indexação por mecanismos de busca
              </label>
            </div>
          </div>
        </div>

        <!-- Sidebar Column -->
        <div class="sidebar-content">
          <!-- Status & Visibility -->
          <div class="card">
            <h3 class="card-title">Publicação</h3>

            <div class="form-group">
              <label>Status</label>
              <select v-model="form.status" class="form-select">
                <option value="draft">Rascunho</option>
                <option value="published">Publicado</option>
                <option value="scheduled">Agendado</option>
                <option value="archived">Arquivado</option>
              </select>
            </div>

            <div v-if="form.status === 'scheduled'" class="form-group">
              <label>Data de Publicação</label>
              <Input
                type="datetime-local"
                v-model="form.publish_at"
              />
            </div>

            <div class="form-group">
              <label>Visibilidade</label>
              <select v-model="form.visibility" class="form-select">
                <option value="public">Público</option>
                <option value="private">Privado</option>
                <option value="password">Protegido por Senha</option>
              </select>
            </div>

            <div v-if="form.visibility === 'password'" class="form-group">
              <label>Senha</label>
              <Input
                type="password"
                v-model="form.password"
                placeholder="Digite uma senha"
              />
            </div>

            <div class="form-group">
              <label>
                <input type="checkbox" v-model="form.featured">
                Marcar como destaque
              </label>
              <span class="help-text">Posts em destaque aparecem no topo da listagem</span>
            </div>

            <div class="form-group">
              <label>
                <input type="checkbox" v-model="form.allow_comments">
                Permitir comentários
              </label>
            </div>

            <div v-if="post?.id" class="post-meta">
              <div class="meta-item">
                <strong>Criado em:</strong>
                <span>{{ formatDate(post.created_at) }}</span>
              </div>
              <div class="meta-item">
                <strong>Atualizado em:</strong>
                <span>{{ formatDate(post.updated_at) }}</span>
              </div>
              <div v-if="post.published_at" class="meta-item">
                <strong>Publicado em:</strong>
                <span>{{ formatDate(post.published_at) }}</span>
              </div>
              <div class="meta-item">
                <strong>Visualizações:</strong>
                <span>{{ post.views || 0 }}</span>
              </div>
            </div>
          </div>

          <!-- Site Selection -->
          <div class="card">
            <h3 class="card-title">Site</h3>
            <div class="form-group">
              <label>Site *</label>
              <select v-model="form.site_id" class="form-select">
                <option value="">Selecione um site</option>
                <option v-for="site in sites" :key="site.id" :value="site.id">
                  {{ site.name }}
                </option>
              </select>
              <span v-if="errors.site_id" class="error-message">{{ errors.site_id }}</span>
            </div>
          </div>

          <!-- Categories -->
          <div class="card">
            <h3 class="card-title">
              <i class="fa fa-folder"></i>
              Categorias
            </h3>
            <div class="categories-list">
              <label v-for="category in categories" :key="category.id" class="category-item">
                <input
                  type="checkbox"
                  :value="category.id"
                  v-model="form.categories"
                >
                {{ category.name }}
              </label>
            </div>
            <Button variant="link" size="small" @click="showCategoryModal = true">
              <i class="fa fa-plus"></i>
              Nova Categoria
            </Button>
          </div>

          <!-- Tags -->
          <div class="card">
            <h3 class="card-title">
              <i class="fa fa-tags"></i>
              Tags
            </h3>
            <div class="tags-input">
              <div class="tags-list">
                <span v-for="(tag, index) in form.tags" :key="index" class="tag">
                  {{ tag }}
                  <button type="button" @click="removeTag(index)" class="tag-remove">
                    <i class="fa fa-times"></i>
                  </button>
                </span>
              </div>
              <Input
                v-model="tagInput"
                placeholder="Digite uma tag e pressione Enter"
                @keydown.enter.prevent="addTag"
              />
            </div>
            <span class="help-text">Pressione Enter para adicionar cada tag</span>
          </div>

          <!-- Featured Image -->
          <div class="card">
            <h3 class="card-title">
              <i class="fa fa-image"></i>
              Imagem Destacada
            </h3>
            <div class="featured-image">
              <div v-if="form.featured_image" class="image-preview">
                <img :src="form.featured_image" alt="Featured">
                <button type="button" @click="removeFeaturedImage" class="remove-image">
                  <i class="fa fa-times"></i>
                </button>
              </div>
              <div v-else class="image-placeholder">
                <i class="fa fa-image"></i>
                <p>Nenhuma imagem selecionada</p>
              </div>
              <input
                ref="imageInput"
                type="file"
                accept="image/*"
                @change="handleImageUpload"
                style="display: none"
              >
              <Button variant="secondary" size="small" @click="$refs.imageInput.click()">
                <i class="fa fa-upload"></i>
                {{ form.featured_image ? 'Alterar Imagem' : 'Enviar Imagem' }}
              </Button>
            </div>
          </div>

          <!-- Author -->
          <div class="card">
            <h3 class="card-title">
              <i class="fa fa-user"></i>
              Autor
            </h3>
            <div class="form-group">
              <select v-model="form.author_id" class="form-select">
                <option value="">Selecione um autor</option>
                <option v-for="user in users" :key="user.id" :value="user.id">
                  {{ user.name }}
                </option>
              </select>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Category Modal -->
    <Modal v-if="showCategoryModal" @close="showCategoryModal = false" title="Nova Categoria">
      <div class="form-group">
        <label>Nome da Categoria</label>
        <Input v-model="newCategory.name" placeholder="Digite o nome" />
      </div>
      <div class="form-group">
        <label>Slug</label>
        <Input v-model="newCategory.slug" placeholder="categoria-slug" />
      </div>
      <div class="form-group">
        <label>Descrição</label>
        <textarea v-model="newCategory.description" rows="3"></textarea>
      </div>
      <template #footer>
        <Button variant="secondary" @click="showCategoryModal = false">Cancelar</Button>
        <Button variant="primary" @click="createCategory">Criar Categoria</Button>
      </template>
    </Modal>
  </MainLayout>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue';
import { router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import Button from '@/Components/Button.vue';
import Input from '@/Components/Input.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
  post: Object,
  sites: Array,
  categories: Array,
  users: Array,
  errors: Object
});

const baseUrl = window.location.origin;

const form = ref({
  title: props.post?.title || '',
  slug: props.post?.slug || '',
  excerpt: props.post?.excerpt || '',
  content: props.post?.content || '',
  status: props.post?.status || 'draft',
  visibility: props.post?.visibility || 'public',
  password: props.post?.password || '',
  featured: props.post?.featured || false,
  allow_comments: props.post?.allow_comments ?? true,
  site_id: props.post?.site_id || '',
  categories: props.post?.categories?.map(c => c.id) || [],
  tags: props.post?.tags?.map(t => t.name) || [],
  author_id: props.post?.author_id || '',
  featured_image: props.post?.featured_image || null,
  publish_at: props.post?.publish_at || null,
  meta_title: props.post?.meta_title || '',
  meta_description: props.post?.meta_description || '',
  meta_keywords: props.post?.meta_keywords || '',
  index_page: props.post?.index_page ?? true
});

const editorContent = ref(null);
const showCode = ref(false);
const saving = ref(false);
const tagInput = ref('');
const imageInput = ref(null);
const showCategoryModal = ref(false);
const newCategory = ref({
  name: '',
  slug: '',
  description: ''
});

const breadcrumbs = computed(() => [
  { label: 'CMS', url: '/cms' },
  { label: 'Posts', url: '/cms/posts' },
  { label: props.post?.id ? 'Editar' : 'Novo' }
]);

onMounted(() => {
  if (editorContent.value && form.value.content) {
    editorContent.value.innerHTML = form.value.content;
  }
});

const generateSlug = () => {
  if (!props.post?.id && form.value.title) {
    form.value.slug = form.value.title
      .toLowerCase()
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '')
      .replace(/[^a-z0-9]+/g, '-')
      .replace(/^-+|-+$/g, '');
  }
};

const execCommand = (command, value = null) => {
  document.execCommand(command, false, value);
  editorContent.value?.focus();
};

const insertLink = () => {
  const url = prompt('Digite a URL:');
  if (url) {
    execCommand('createLink', url);
  }
};

const insertImage = () => {
  const url = prompt('Digite a URL da imagem:');
  if (url) {
    execCommand('insertImage', url);
  }
};

const insertVideo = () => {
  const url = prompt('Digite a URL do vídeo (YouTube ou Vimeo):');
  if (url) {
    let embedUrl = '';
    if (url.includes('youtube.com') || url.includes('youtu.be')) {
      const videoId = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&\s]+)/)?.[1];
      embedUrl = `https://www.youtube.com/embed/${videoId}`;
    } else if (url.includes('vimeo.com')) {
      const videoId = url.match(/vimeo\.com\/(\d+)/)?.[1];
      embedUrl = `https://player.vimeo.com/video/${videoId}`;
    }

    if (embedUrl) {
      const iframe = `<iframe src="${embedUrl}" width="560" height="315" frameborder="0" allowfullscreen></iframe>`;
      execCommand('insertHTML', iframe);
    }
  }
};

const toggleCodeView = () => {
  if (!showCode.value) {
    form.value.content = editorContent.value?.innerHTML || '';
  } else {
    nextTick(() => {
      if (editorContent.value) {
        editorContent.value.innerHTML = form.value.content;
      }
    });
  }
  showCode.value = !showCode.value;
};

const updateContent = () => {
  form.value.content = editorContent.value?.innerHTML || '';
};

const updateFromCode = () => {
  // Content is already updated via v-model
};

const handlePaste = (e) => {
  e.preventDefault();
  const text = e.clipboardData.getData('text/plain');
  document.execCommand('insertText', false, text);
};

const addTag = () => {
  if (tagInput.value.trim() && !form.value.tags.includes(tagInput.value.trim())) {
    form.value.tags.push(tagInput.value.trim());
    tagInput.value = '';
  }
};

const removeTag = (index) => {
  form.value.tags.splice(index, 1);
};

const handleImageUpload = (e) => {
  const file = e.target.files[0];
  if (file) {
    // In a real app, upload to server and get URL
    const reader = new FileReader();
    reader.onload = (event) => {
      form.value.featured_image = event.target.result;
    };
    reader.readAsDataURL(file);
  }
};

const removeFeaturedImage = () => {
  form.value.featured_image = null;
  if (imageInput.value) {
    imageInput.value.value = '';
  }
};

const createCategory = () => {
  router.post('/cms/categories', newCategory.value, {
    preserveScroll: true,
    onSuccess: () => {
      showCategoryModal.value = false;
      newCategory.value = { name: '', slug: '', description: '' };
    }
  });
};

const saveDraft = () => {
  form.value.status = 'draft';
  savePost();
};

const saveAndPublish = () => {
  if (form.value.status === 'draft') {
    form.value.status = 'published';
  }
  savePost();
};

const savePost = () => {
  saving.value = true;
  const url = props.post?.id ? `/cms/posts/${props.post.id}` : '/cms/posts';
  const method = props.post?.id ? 'put' : 'post';

  router[method](url, form.value, {
    onFinish: () => {
      saving.value = false;
    },
    onSuccess: () => {
      if (!props.post?.id) {
        router.visit('/cms/posts');
      }
    }
  });
};

const formatDate = (date) => {
  return new Date(date).toLocaleString('pt-BR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};
</script>

