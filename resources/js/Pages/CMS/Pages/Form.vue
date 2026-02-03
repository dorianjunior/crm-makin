<template>
  <MainLayout>
    <div class="page-form">
      <Breadcrumbs :items="breadcrumbs" />

      <div class="page-header">
        <div>
          <h1>{{ isEdit ? 'Editar Página' : 'Nova Página' }}</h1>
          <p class="subtitle">{{ currentSite?.name }}</p>
        </div>
        <div class="header-actions">
          <Button variant="secondary" @click="goBack">
            <i class="fa fa-arrow-left"></i>
            Voltar
          </Button>
          <Button variant="secondary" @click="saveDraft" :disabled="saving">
            <i class="fa fa-save"></i>
            Salvar Rascunho
          </Button>
          <Button variant="primary" @click="publishPage" :disabled="saving">
            <i class="fa fa-check"></i>
            Publicar
          </Button>
        </div>
      </div>

      <div class="form-layout">
        <!-- Main Content -->
        <div class="form-main">
          <div class="form-card">
            <div class="form-group">
              <label>Título da Página *</label>
              <Input
                v-model="form.title"
                placeholder="Digite o título da página"
                @input="generateSlug"
              />
            </div>

            <div class="form-group">
              <label>Slug *</label>
              <div class="slug-input">
                <span class="slug-prefix">{{ siteUrl }}/</span>
                <Input v-model="form.slug" />
              </div>
              <span class="help-text">URL amigável da página</span>
            </div>

            <div class="form-group">
              <label>Conteúdo</label>
              <div class="editor-toolbar">
                <div class="toolbar-group">
                  <button
                    @click="execCommand('bold')"
                    class="toolbar-btn"
                    title="Negrito"
                    type="button"
                  >
                    <i class="fa fa-bold"></i>
                  </button>
                  <button
                    @click="execCommand('italic')"
                    class="toolbar-btn"
                    title="Itálico"
                    type="button"
                  >
                    <i class="fa fa-italic"></i>
                  </button>
                  <button
                    @click="execCommand('underline')"
                    class="toolbar-btn"
                    title="Sublinhado"
                    type="button"
                  >
                    <i class="fa fa-underline"></i>
                  </button>
                </div>

                <div class="toolbar-group">
                  <button
                    @click="execCommand('formatBlock', 'h1')"
                    class="toolbar-btn"
                    title="Título 1"
                    type="button"
                  >
                    H1
                  </button>
                  <button
                    @click="execCommand('formatBlock', 'h2')"
                    class="toolbar-btn"
                    title="Título 2"
                    type="button"
                  >
                    H2
                  </button>
                  <button
                    @click="execCommand('formatBlock', 'h3')"
                    class="toolbar-btn"
                    title="Título 3"
                    type="button"
                  >
                    H3
                  </button>
                  <button
                    @click="execCommand('formatBlock', 'p')"
                    class="toolbar-btn"
                    title="Parágrafo"
                    type="button"
                  >
                    P
                  </button>
                </div>

                <div class="toolbar-group">
                  <button
                    @click="execCommand('insertUnorderedList')"
                    class="toolbar-btn"
                    title="Lista"
                    type="button"
                  >
                    <i class="fa fa-list-ul"></i>
                  </button>
                  <button
                    @click="execCommand('insertOrderedList')"
                    class="toolbar-btn"
                    title="Lista Numerada"
                    type="button"
                  >
                    <i class="fa fa-list-ol"></i>
                  </button>
                </div>

                <div class="toolbar-group">
                  <button
                    @click="execCommand('justifyLeft')"
                    class="toolbar-btn"
                    title="Alinhar à Esquerda"
                    type="button"
                  >
                    <i class="fa fa-align-left"></i>
                  </button>
                  <button
                    @click="execCommand('justifyCenter')"
                    class="toolbar-btn"
                    title="Centralizar"
                    type="button"
                  >
                    <i class="fa fa-align-center"></i>
                  </button>
                  <button
                    @click="execCommand('justifyRight')"
                    class="toolbar-btn"
                    title="Alinhar à Direita"
                    type="button"
                  >
                    <i class="fa fa-align-right"></i>
                  </button>
                </div>

                <div class="toolbar-group">
                  <button
                    @click="insertLink"
                    class="toolbar-btn"
                    title="Inserir Link"
                    type="button"
                  >
                    <i class="fa fa-link"></i>
                  </button>
                  <button
                    @click="showImageModal = true"
                    class="toolbar-btn"
                    title="Inserir Imagem"
                    type="button"
                  >
                    <i class="fa fa-image"></i>
                  </button>
                  <button
                    @click="showVideoModal = true"
                    class="toolbar-btn"
                    title="Inserir Vídeo"
                    type="button"
                  >
                    <i class="fa fa-video"></i>
                  </button>
                </div>

                <div class="toolbar-group">
                  <button
                    @click="toggleCodeView"
                    class="toolbar-btn"
                    :class="{ active: codeView }"
                    title="Visualizar HTML"
                    type="button"
                  >
                    <i class="fa fa-code"></i>
                  </button>
                </div>
              </div>

              <div v-if="!codeView" class="editor-content">
                <div
                  ref="editor"
                  class="editor"
                  contenteditable="true"
                  @input="updateContent"
                  v-html="form.content"
                ></div>
              </div>

              <div v-else class="code-editor">
                <textarea
                  v-model="form.content"
                  @input="updateContentFromCode"
                  class="code-textarea"
                ></textarea>
              </div>
            </div>

            <!-- Page Blocks -->
            <div class="form-group">
              <label>Blocos de Conteúdo</label>
              <div class="blocks-list">
                <div
                  v-for="(block, index) in form.blocks"
                  :key="index"
                  class="block-item"
                >
                  <div class="block-header">
                    <select v-model="block.type" class="form-select">
                      <option value="text">Texto</option>
                      <option value="image">Imagem</option>
                      <option value="gallery">Galeria</option>
                      <option value="video">Vídeo</option>
                      <option value="cta">Call to Action</option>
                      <option value="testimonial">Depoimento</option>
                      <option value="faq">FAQ</option>
                    </select>
                    <button @click="removeBlock(index)" class="remove-block-btn">
                      <i class="fa fa-trash"></i>
                    </button>
                  </div>

                  <!-- Text Block -->
                  <div v-if="block.type === 'text'" class="block-content">
                    <Input
                      v-model="block.title"
                      placeholder="Título do bloco"
                      class="mb-2"
                    />
                    <textarea
                      v-model="block.content"
                      placeholder="Conteúdo do bloco"
                      rows="4"
                    ></textarea>
                  </div>

                  <!-- Image Block -->
                  <div v-if="block.type === 'image'" class="block-content">
                    <Input v-model="block.url" placeholder="URL da imagem" class="mb-2" />
                    <Input v-model="block.alt" placeholder="Texto alternativo" class="mb-2" />
                    <Input v-model="block.caption" placeholder="Legenda" />
                  </div>

                  <!-- CTA Block -->
                  <div v-if="block.type === 'cta'" class="block-content">
                    <Input v-model="block.title" placeholder="Título" class="mb-2" />
                    <Input v-model="block.text" placeholder="Texto" class="mb-2" />
                    <Input v-model="block.button_text" placeholder="Texto do botão" class="mb-2" />
                    <Input v-model="block.button_url" placeholder="URL do botão" />
                  </div>
                </div>

                <Button variant="secondary" size="small" @click="addBlock">
                  <i class="fa fa-plus"></i>
                  Adicionar Bloco
                </Button>
              </div>
            </div>

            <!-- SEO Section -->
            <div class="form-section">
              <h3>SEO</h3>

              <div class="form-group">
                <label>Meta Título</label>
                <Input
                  v-model="form.meta_title"
                  placeholder="Título para mecanismos de busca"
                  maxlength="60"
                />
                <span class="char-count">{{ form.meta_title?.length || 0 }}/60</span>
              </div>

              <div class="form-group">
                <label>Meta Descrição</label>
                <textarea
                  v-model="form.meta_description"
                  placeholder="Descrição para mecanismos de busca"
                  rows="3"
                  maxlength="160"
                ></textarea>
                <span class="char-count">{{ form.meta_description?.length || 0 }}/160</span>
              </div>

              <div class="form-group">
                <label>Palavras-chave</label>
                <Input
                  v-model="form.meta_keywords"
                  placeholder="palavra1, palavra2, palavra3"
                />
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="form-sidebar">
          <!-- Status Card -->
          <div class="sidebar-card">
            <h3>Status</h3>

            <div class="form-group">
              <label>
                <input type="radio" v-model="form.status" value="draft">
                Rascunho
              </label>
            </div>

            <div class="form-group">
              <label>
                <input type="radio" v-model="form.status" value="published">
                Publicado
              </label>
            </div>

            <div v-if="form.published_at" class="info-text">
              <i class="fa fa-calendar"></i>
              Publicado em: {{ formatDate(form.published_at) }}
            </div>
          </div>

          <!-- Template Card -->
          <div class="sidebar-card">
            <h3>Template</h3>

            <div class="form-group">
              <label>Layout</label>
              <select v-model="form.template" class="form-select">
                <option value="default">Padrão</option>
                <option value="full-width">Largura Total</option>
                <option value="sidebar-left">Sidebar Esquerda</option>
                <option value="sidebar-right">Sidebar Direita</option>
                <option value="landing">Landing Page</option>
              </select>
            </div>
          </div>

          <!-- Featured Image -->
          <div class="sidebar-card">
            <h3>Imagem Destacada</h3>

            <div v-if="form.featured_image" class="featured-image-preview">
              <img :src="form.featured_image" alt="Imagem destacada">
              <button @click="form.featured_image = null" class="remove-image-btn">
                <i class="fa fa-times"></i>
              </button>
            </div>

            <div v-else class="featured-image-placeholder">
              <i class="fa fa-image"></i>
              <p>Nenhuma imagem</p>
            </div>

            <input
              ref="featuredImageInput"
              type="file"
              accept="image/*"
              @change="handleFeaturedImageUpload"
              style="display: none"
            >

            <Button
              variant="secondary"
              size="small"
              @click="$refs.featuredImageInput.click()"
              class="mt-2"
            >
              <i class="fa fa-upload"></i>
              {{ form.featured_image ? 'Alterar Imagem' : 'Enviar Imagem' }}
            </Button>
          </div>

          <!-- Parent Page -->
          <div class="sidebar-card">
            <h3>Página Pai</h3>

            <div class="form-group">
              <label>Hierarquia</label>
              <select v-model="form.parent_id" class="form-select">
                <option :value="null">Nenhuma (raiz)</option>
                <option
                  v-for="page in parentPages"
                  :key="page.id"
                  :value="page.id"
                >
                  {{ page.title }}
                </option>
              </select>
            </div>
          </div>

          <!-- Order -->
          <div class="sidebar-card">
            <h3>Ordem</h3>

            <div class="form-group">
              <label>Posição no menu</label>
              <Input v-model="form.order" type="number" min="0" />
            </div>
          </div>

          <!-- Actions -->
          <div class="sidebar-card">
            <h3>Ações</h3>

            <Button variant="secondary" @click="previewPage" class="mb-2" block>
              <i class="fa fa-eye"></i>
              Visualizar
            </Button>

            <Button
              v-if="isEdit"
              variant="danger"
              @click="deletePage"
              block
            >
              <i class="fa fa-trash"></i>
              Excluir Página
            </Button>
          </div>
        </div>
      </div>
    </div>

    <!-- Image Modal -->
    <Modal v-if="showImageModal" @close="showImageModal = false">
      <template #header>
        <h2>Inserir Imagem</h2>
      </template>

      <template #body>
        <div class="form-group">
          <label>URL da Imagem</label>
          <Input v-model="imageUrl" placeholder="https://exemplo.com/imagem.jpg" />
        </div>

        <div class="form-group">
          <label>Texto Alternativo</label>
          <Input v-model="imageAlt" placeholder="Descrição da imagem" />
        </div>
      </template>

      <template #footer>
        <Button variant="secondary" @click="showImageModal = false">
          Cancelar
        </Button>
        <Button variant="primary" @click="insertImage">
          Inserir
        </Button>
      </template>
    </Modal>

    <!-- Video Modal -->
    <Modal v-if="showVideoModal" @close="showVideoModal = false">
      <template #header>
        <h2>Inserir Vídeo</h2>
      </template>

      <template #body>
        <div class="form-group">
          <label>URL do Vídeo (YouTube ou Vimeo)</label>
          <Input v-model="videoUrl" placeholder="https://www.youtube.com/watch?v=..." />
        </div>
      </template>

      <template #footer>
        <Button variant="secondary" @click="showVideoModal = false">
          Cancelar
        </Button>
        <Button variant="primary" @click="insertVideo">
          Inserir
        </Button>
      </template>
    </Modal>
  </MainLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import Button from '@/Components/Button.vue';
import Input from '@/Components/Input.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
  page: Object,
  site: Object,
  parentPages: Array
});

const editor = ref(null);
const featuredImageInput = ref(null);
const codeView = ref(false);
const saving = ref(false);
const showImageModal = ref(false);
const showVideoModal = ref(false);
const imageUrl = ref('');
const imageAlt = ref('');
const videoUrl = ref('');

const isEdit = computed(() => !!props.page);
const currentSite = computed(() => props.site);
const siteUrl = computed(() => currentSite.value?.domain || 'site.com');

const form = ref({
  site_id: props.site?.id,
  title: props.page?.title || '',
  slug: props.page?.slug || '',
  content: props.page?.content || '',
  blocks: props.page?.blocks || [],
  status: props.page?.status || 'draft',
  template: props.page?.template || 'default',
  featured_image: props.page?.featured_image || null,
  parent_id: props.page?.parent_id || null,
  order: props.page?.order || 0,
  meta_title: props.page?.meta_title || '',
  meta_description: props.page?.meta_description || '',
  meta_keywords: props.page?.meta_keywords || '',
  published_at: props.page?.published_at || null
});

const breadcrumbs = [
  { label: 'Páginas', to: '/pages' },
  { label: isEdit.value ? 'Editar' : 'Nova Página' }
];

const generateSlug = () => {
  if (!isEdit.value && form.value.title) {
    form.value.slug = form.value.title
      .toLowerCase()
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '')
      .replace(/[^\w\s-]/g, '')
      .replace(/\s+/g, '-')
      .replace(/-+/g, '-')
      .trim();
  }
};

const execCommand = (command, value = null) => {
  document.execCommand(command, false, value);
  editor.value?.focus();
};

const updateContent = () => {
  if (editor.value) {
    form.value.content = editor.value.innerHTML;
  }
};

const updateContentFromCode = () => {
  if (editor.value) {
    editor.value.innerHTML = form.value.content;
  }
};

const toggleCodeView = () => {
  codeView.value = !codeView.value;
};

const insertLink = () => {
  const url = prompt('Digite a URL:');
  if (url) {
    execCommand('createLink', url);
  }
};

const insertImage = () => {
  if (imageUrl.value) {
    const img = `<img src="${imageUrl.value}" alt="${imageAlt.value || ''}" style="max-width: 100%;">`;
    document.execCommand('insertHTML', false, img);
    showImageModal.value = false;
    imageUrl.value = '';
    imageAlt.value = '';
  }
};

const insertVideo = () => {
  if (videoUrl.value) {
    let embedUrl = videoUrl.value;

    // Convert YouTube URL to embed
    if (videoUrl.value.includes('youtube.com') || videoUrl.value.includes('youtu.be')) {
      const videoId = videoUrl.value.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&\s]+)/)?.[1];
      if (videoId) {
        embedUrl = `https://www.youtube.com/embed/${videoId}`;
      }
    }

    // Convert Vimeo URL to embed
    if (videoUrl.value.includes('vimeo.com')) {
      const videoId = videoUrl.value.match(/vimeo\.com\/(\d+)/)?.[1];
      if (videoId) {
        embedUrl = `https://player.vimeo.com/video/${videoId}`;
      }
    }

    const iframe = `<div class="video-container"><iframe src="${embedUrl}" frameborder="0" allowfullscreen style="width: 100%; aspect-ratio: 16/9;"></iframe></div>`;
    document.execCommand('insertHTML', false, iframe);
    showVideoModal.value = false;
    videoUrl.value = '';
  }
};

const addBlock = () => {
  form.value.blocks.push({
    type: 'text',
    title: '',
    content: ''
  });
};

const removeBlock = (index) => {
  form.value.blocks.splice(index, 1);
};

const handleFeaturedImageUpload = (e) => {
  const file = e.target.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = (event) => {
      form.value.featured_image = event.target.result;
    };
    reader.readAsDataURL(file);
  }
};

const saveDraft = () => {
  form.value.status = 'draft';
  save();
};

const publishPage = () => {
  form.value.status = 'published';
  form.value.published_at = new Date().toISOString();
  save();
};

const save = () => {
  saving.value = true;

  const url = isEdit.value
    ? `/pages/${props.page.id}`
    : '/pages';

  const method = isEdit.value ? 'put' : 'post';

  router[method](url, form.value, {
    preserveScroll: true,
    onFinish: () => {
      saving.value = false;
    }
  });
};

const previewPage = () => {
  if (isEdit.value) {
    window.open(`/pages/${props.page.id}/preview`, '_blank');
  }
};

const deletePage = () => {
  if (confirm('Tem certeza que deseja excluir esta página?')) {
    router.delete(`/pages/${props.page.id}`);
  }
};

const goBack = () => {
  router.visit('/pages');
};

const formatDate = (date) => {
  return new Date(date).toLocaleString('pt-BR');
};
</script>

