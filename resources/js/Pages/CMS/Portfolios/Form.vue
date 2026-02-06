<template>
  <MainLayout title="Projeto do Portfólio">
    <div class="portfolio-form-page">
      <Breadcrumbs :items="breadcrumbs" />

      <div class="page-header">
        <div>
          <h1>{{ portfolio?.id ? 'Editar Projeto' : 'Novo Projeto' }}</h1>
          <p class="subtitle">{{ portfolio?.id ? 'Atualize as informações do projeto' : 'Adicione um novo projeto ao portfólio' }}</p>
        </div>
        <div class="header-actions">
          <Button variant="secondary" @click="saveDraft" :disabled="saving">
            <i class="fa fa-save"></i>
            Salvar Rascunho
          </Button>
          <Button variant="primary" @click="saveAndPublish" :disabled="saving">
            <i class="fa fa-paper-plane"></i>
            {{ portfolio?.status === 'published' ? 'Atualizar' : 'Publicar' }}
          </Button>
        </div>
      </div>

      <div class="form-layout">
        <!-- Main Content -->
        <div class="main-content">
          <div class="card">
            <div class="form-group">
              <label>Título do Projeto *</label>
              <Input v-model="form.title" placeholder="Digite o título do projeto" @input="generateSlug" />
              <span v-if="errors.title" class="error-message">{{ errors.title }}</span>
            </div>

            <div class="form-group">
              <label>Slug</label>
              <Input v-model="form.slug" placeholder="url-do-projeto" />
              <span class="help-text">URL: {{ baseUrl }}/portfolio/{{ form.slug || 'url-do-projeto' }}</span>
            </div>

            <div class="form-group">
              <label>Cliente</label>
              <Input v-model="form.client" placeholder="Nome do cliente" />
            </div>

            <div class="form-group">
              <label>Resumo</label>
              <textarea v-model="form.excerpt" rows="3" placeholder="Breve descrição do projeto"></textarea>
            </div>

            <div class="form-group">
              <label>Descrição Completa *</label>
              <textarea v-model="form.description" rows="10" placeholder="Descreva o projeto em detalhes"></textarea>
              <span v-if="errors.description" class="error-message">{{ errors.description }}</span>
            </div>
          </div>

          <!-- Project Details -->
          <div class="card">
            <h3 class="card-title">
              <i class="fa fa-info-circle"></i>
              Detalhes do Projeto
            </h3>

            <div class="form-row">
              <div class="form-group">
                <label>Data de Início</label>
                <Input type="date" v-model="form.start_date" />
              </div>

              <div class="form-group">
                <label>Data de Conclusão</label>
                <Input type="date" v-model="form.end_date" />
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label>Duração</label>
                <Input v-model="form.duration" placeholder="Ex: 3 meses" />
              </div>

              <div class="form-group">
                <label>Orçamento</label>
                <Input v-model="form.budget" placeholder="R$ 50.000,00" />
              </div>
            </div>

            <div class="form-group">
              <label>URL do Projeto</label>
              <Input v-model="form.url" type="url" placeholder="https://exemplo.com" />
            </div>

            <div class="form-group">
              <label>Tecnologias Utilizadas</label>
              <div class="tech-input">
                <div class="tech-list">
                  <span v-for="(tech, index) in form.technologies" :key="index" class="tech-badge">
                    {{ tech }}
                    <button type="button" @click="removeTech(index)">
                      <i class="fa fa-times"></i>
                    </button>
                  </span>
                </div>
                <Input
                  v-model="techInput"
                  placeholder="Digite uma tecnologia e pressione Enter"
                  @keydown.enter.prevent="addTech"
                />
              </div>
            </div>

            <div class="form-group">
              <label>Desafios</label>
              <textarea v-model="form.challenges" rows="4" placeholder="Quais foram os principais desafios?"></textarea>
            </div>

            <div class="form-group">
              <label>Soluções</label>
              <textarea v-model="form.solutions" rows="4" placeholder="Como foram resolvidos?"></textarea>
            </div>

            <div class="form-group">
              <label>Resultados</label>
              <textarea v-model="form.results" rows="4" placeholder="Quais foram os resultados alcançados?"></textarea>
            </div>
          </div>

          <!-- Gallery -->
          <div class="card">
            <h3 class="card-title">
              <i class="fa fa-images"></i>
              Galeria de Imagens
            </h3>

            <div class="gallery-upload">
              <div class="gallery-grid">
                <div
                  v-for="(image, index) in form.gallery"
                  :key="index"
                  class="gallery-item"
                  draggable="true"
                  @dragstart="dragStart(index)"
                  @drop="drop(index)"
                  @dragover.prevent
                >
                  <img :src="image.url" :alt="image.caption">
                  <button type="button" @click="removeGalleryImage(index)" class="remove-btn">
                    <i class="fa fa-times"></i>
                  </button>
                  <Input
                    v-model="image.caption"
                    placeholder="Legenda da imagem"
                    class="caption-input"
                  />
                </div>

                <div class="gallery-upload-btn" @click="$refs.galleryInput.click()">
                  <i class="fa fa-plus"></i>
                  <span>Adicionar Imagens</span>
                </div>
              </div>

              <input
                ref="galleryInput"
                type="file"
                accept="image/*"
                multiple
                @change="handleGalleryUpload"
                style="display: none"
              >
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="sidebar-content">
          <!-- Publication -->
          <div class="card">
            <h3 class="card-title">Publicação</h3>

            <div class="form-group">
              <label>Status</label>
              <select v-model="form.status" class="form-select">
                <option value="draft">Rascunho</option>
                <option value="published">Publicado</option>
                <option value="archived">Arquivado</option>
              </select>
            </div>

            <div class="form-group">
              <label>
                <input type="checkbox" v-model="form.featured">
                Marcar como destaque
              </label>
            </div>

            <div v-if="portfolio?.id" class="project-meta">
              <div class="meta-item">
                <strong>Criado em:</strong>
                <span>{{ formatDate(portfolio.created_at) }}</span>
              </div>
              <div class="meta-item">
                <strong>Atualizado em:</strong>
                <span>{{ formatDate(portfolio.updated_at) }}</span>
              </div>
              <div class="meta-item">
                <strong>Visualizações:</strong>
                <span>{{ portfolio.views || 0 }}</span>
              </div>
            </div>
          </div>

          <!-- Site -->
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

          <!-- Category -->
          <div class="card">
            <h3 class="card-title">
              <i class="fa fa-folder"></i>
              Categoria
            </h3>
            <div class="form-group">
              <select v-model="form.category_id" class="form-select">
                <option value="">Selecione uma categoria</option>
                <option v-for="category in categories" :key="category.id" :value="category.id">
                  {{ category.name }}
                </option>
              </select>
            </div>
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
                  <button type="button" @click="removeTag(index)">
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
          </div>

          <!-- Featured Image -->
          <div class="card">
            <h3 class="card-title">
              <i class="fa fa-image"></i>
              Imagem de Capa
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

          <!-- Testimonial -->
          <div class="card">
            <h3 class="card-title">
              <i class="fa fa-quote-left"></i>
              Depoimento do Cliente
            </h3>
            <div class="form-group">
              <textarea
                v-model="form.testimonial"
                rows="4"
                placeholder="Feedback do cliente sobre o projeto"
              ></textarea>
            </div>
            <div class="form-group">
              <Input
                v-model="form.testimonial_author"
                placeholder="Nome do autor"
              />
            </div>
            <div class="form-group">
              <Input
                v-model="form.testimonial_position"
                placeholder="Cargo do autor"
              />
            </div>
          </div>
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

const props = defineProps({
  portfolio: Object,
  sites: Array,
  categories: Array,
  errors: Object
});

const baseUrl = window.location.origin;
const saving = ref(false);
const techInput = ref('');
const tagInput = ref('');
const imageInput = ref(null);
const galleryInput = ref(null);
const draggedIndex = ref(null);

const form = ref({
  title: props.portfolio?.title || '',
  slug: props.portfolio?.slug || '',
  client: props.portfolio?.client || '',
  excerpt: props.portfolio?.excerpt || '',
  description: props.portfolio?.description || '',
  start_date: props.portfolio?.start_date || '',
  end_date: props.portfolio?.end_date || '',
  duration: props.portfolio?.duration || '',
  budget: props.portfolio?.budget || '',
  url: props.portfolio?.url || '',
  technologies: props.portfolio?.technologies || [],
  challenges: props.portfolio?.challenges || '',
  solutions: props.portfolio?.solutions || '',
  results: props.portfolio?.results || '',
  gallery: props.portfolio?.gallery || [],
  status: props.portfolio?.status || 'draft',
  featured: props.portfolio?.featured || false,
  site_id: props.portfolio?.site_id || '',
  category_id: props.portfolio?.category_id || '',
  tags: props.portfolio?.tags?.map(t => t.name) || [],
  featured_image: props.portfolio?.featured_image || null,
  testimonial: props.portfolio?.testimonial || '',
  testimonial_author: props.portfolio?.testimonial_author || '',
  testimonial_position: props.portfolio?.testimonial_position || ''
});

const breadcrumbs = [
  { label: 'CMS', url: '/cms' },
  { label: 'Portfólios', url: '/cms/portfolios' },
  { label: props.portfolio?.id ? 'Editar' : 'Novo' }
];

const generateSlug = () => {
  if (!props.portfolio?.id && form.value.title) {
    form.value.slug = form.value.title
      .toLowerCase()
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '')
      .replace(/[^a-z0-9]+/g, '-')
      .replace(/^-+|-+$/g, '');
  }
};

const addTech = () => {
  if (techInput.value.trim() && !form.value.technologies.includes(techInput.value.trim())) {
    form.value.technologies.push(techInput.value.trim());
    techInput.value = '';
  }
};

const removeTech = (index) => {
  form.value.technologies.splice(index, 1);
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

const handleGalleryUpload = (e) => {
  const files = Array.from(e.target.files);
  files.forEach(file => {
    const reader = new FileReader();
    reader.onload = (event) => {
      form.value.gallery.push({
        url: event.target.result,
        caption: ''
      });
    };
    reader.readAsDataURL(file);
  });
};

const removeGalleryImage = (index) => {
  form.value.gallery.splice(index, 1);
};

const dragStart = (index) => {
  draggedIndex.value = index;
};

const drop = (dropIndex) => {
  if (draggedIndex.value !== null && draggedIndex.value !== dropIndex) {
    const draggedItem = form.value.gallery[draggedIndex.value];
    form.value.gallery.splice(draggedIndex.value, 1);
    form.value.gallery.splice(dropIndex, 0, draggedItem);
  }
  draggedIndex.value = null;
};

const saveDraft = () => {
  form.value.status = 'draft';
  savePortfolio();
};

const saveAndPublish = () => {
  if (form.value.status === 'draft') {
    form.value.status = 'published';
  }
  savePortfolio();
};

const savePortfolio = () => {
  saving.value = true;
  const url = props.portfolio?.id ? `/cms/portfolios/${props.portfolio.id}` : '/cms/portfolios';
  const method = props.portfolio?.id ? 'put' : 'post';

  router[method](url, form.value, {
    onFinish: () => {
      saving.value = false;
    },
    onSuccess: () => {
      if (!props.portfolio?.id) {
        router.visit('/cms/portfolios');
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

