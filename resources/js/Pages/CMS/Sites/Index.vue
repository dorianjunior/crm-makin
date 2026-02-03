<template>
  <MainLayout>
    <div class="sites-index">
      <!-- Header -->
      <div class="page-header">
        <div>
          <h1 class="page-title">Sites</h1>
          <Breadcrumbs :items="breadcrumbs" />
        </div>
        <Button
          label="Novo Site"
          icon="fa fa-plus"
          @click="createSite"
          severity="success"
        />
      </div>

      <!-- Stats -->
      <div class="stats-grid">
        <StatCard
          title="Total de Sites"
          :value="stats.total"
          icon="fa fa-globe"
          color="blue"
        />
        <StatCard
          title="Sites Ativos"
          :value="stats.active"
          icon="fa fa-check-circle"
          color="green"
        />
        <StatCard
          title="Total de Páginas"
          :value="stats.total_pages"
          icon="fa fa-file-alt"
          color="purple"
        />
        <StatCard
          title="Conteúdos Publicados"
          :value="stats.published_content"
          icon="fa fa-paper-plane"
          color="orange"
        />
      </div>

      <!-- Sites Grid -->
      <div v-if="sites.length === 0" class="empty-state">
        <i class="fa fa-globe"></i>
        <h3>Nenhum site criado</h3>
        <p>Crie seu primeiro site para começar a gerenciar conteúdo</p>
        <Button
          label="Criar Primeiro Site"
          icon="fa fa-plus"
          @click="createSite"
          severity="success"
        />
      </div>

      <div v-else class="sites-grid">
        <div v-for="site in sites" :key="site.id" class="site-card">
          <div class="site-card-header">
            <div class="site-info">
              <h3 class="site-name">{{ site.name }}</h3>
              <a :href="site.domain" target="_blank" class="site-domain">
                <i class="fa fa-external-link-alt"></i>
                {{ site.domain }}
              </a>
            </div>
            <div class="site-status">
              <span :class="['badge', site.is_active ? 'badge-success' : 'badge-danger']">
                {{ site.is_active ? 'Ativo' : 'Inativo' }}
              </span>
            </div>
          </div>

          <div v-if="site.description" class="site-description">
            {{ site.description }}
          </div>

          <div class="site-meta">
            <div class="meta-item">
              <i class="fa fa-file-alt"></i>
              <span>{{ site.pages_count }} páginas</span>
            </div>
            <div class="meta-item">
              <i class="fa fa-blog"></i>
              <span>{{ site.posts_count }} posts</span>
            </div>
            <div class="meta-item">
              <i class="fa fa-briefcase"></i>
              <span>{{ site.portfolios_count }} portfólios</span>
            </div>
          </div>

          <div class="site-settings">
            <div class="setting-item">
              <span class="setting-label">Idioma:</span>
              <span class="setting-value">{{ site.default_language || 'pt-BR' }}</span>
            </div>
            <div class="setting-item">
              <span class="setting-label">Timezone:</span>
              <span class="setting-value">{{ site.timezone || 'America/Sao_Paulo' }}</span>
            </div>
          </div>

          <div class="site-actions">
            <Button
              label="Gerenciar Conteúdo"
              icon="fa fa-cog"
              @click="manageSite(site)"
              outlined
              size="small"
            />
            <button @click="editSite(site)" class="btn-icon" title="Editar">
              <i class="fa fa-edit"></i>
            </button>
            <button @click="toggleActive(site)" class="btn-icon" :title="site.is_active ? 'Desativar' : 'Ativar'">
              <i :class="site.is_active ? 'fa fa-toggle-on' : 'fa fa-toggle-off'"></i>
            </button>
            <button @click="viewSettings(site)" class="btn-icon" title="Configurações">
              <i class="fa fa-cog"></i>
            </button>
            <button @click="deleteSite(site)" class="btn-icon btn-danger" title="Excluir">
              <i class="fa fa-trash"></i>
            </button>
          </div>

          <div class="site-footer">
            <div class="footer-item">
              <i class="fa fa-calendar"></i>
              Criado em {{ formatDate(site.created_at) }}
            </div>
            <div class="footer-item">
              <i class="fa fa-clock"></i>
              Atualizado {{ formatRelativeDate(site.updated_at) }}
            </div>
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
      <p>Tem certeza que deseja excluir o site <strong>{{ siteToDelete?.name }}</strong>?</p>
      <p class="text-danger">⚠️ Todo o conteúdo associado (páginas, posts, menus, etc.) será permanentemente excluído.</p>
      <p class="text-muted">Esta ação não pode ser desfeita.</p>
    </Modal>

    <!-- Modal de Form Site -->
    <Modal
      v-model:visible="showSiteModal"
      :title="editingSite ? 'Editar Site' : 'Novo Site'"
      size="large"
      @confirm="saveSite"
    >
      <div class="modal-form">
        <div class="form-group">
          <label class="required">Nome do Site</label>
          <Input
            v-model="siteForm.name"
            placeholder="Ex: Site Institucional, Blog da Empresa..."
          />
        </div>

        <div class="form-group">
          <label class="required">Domínio</label>
          <Input
            v-model="siteForm.domain"
            placeholder="https://www.exemplo.com.br"
            icon="fa fa-globe"
          />
          <small class="form-hint">URL completa do site (com https://)</small>
        </div>

        <div class="form-group">
          <label>Descrição</label>
          <textarea
            v-model="siteForm.description"
            class="form-textarea"
            placeholder="Descreva o propósito deste site..."
            rows="3"
          ></textarea>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Idioma Padrão</label>
            <select v-model="siteForm.default_language" class="form-select">
              <option value="pt-BR">Português (BR)</option>
              <option value="en-US">English (US)</option>
              <option value="es-ES">Español</option>
            </select>
          </div>

          <div class="form-group">
            <label>Timezone</label>
            <select v-model="siteForm.timezone" class="form-select">
              <option value="America/Sao_Paulo">América/São Paulo</option>
              <option value="America/New_York">América/New York</option>
              <option value="Europe/London">Europa/Londres</option>
            </select>
          </div>
        </div>

        <div class="form-group">
          <label class="checkbox-label">
            <input type="checkbox" v-model="siteForm.is_active" />
            <span>Site ativo</span>
          </label>
        </div>

        <div class="form-section">
          <h4>SEO Padrão</h4>

          <div class="form-group">
            <label>Meta Title</label>
            <Input
              v-model="siteForm.meta_title"
              placeholder="Título para mecanismos de busca"
              maxlength="60"
            />
            <small class="form-hint">{{ siteForm.meta_title?.length || 0 }}/60 caracteres</small>
          </div>

          <div class="form-group">
            <label>Meta Description</label>
            <textarea
              v-model="siteForm.meta_description"
              class="form-textarea"
              placeholder="Descrição para mecanismos de busca..."
              rows="2"
              maxlength="160"
            ></textarea>
            <small class="form-hint">{{ siteForm.meta_description?.length || 0 }}/160 caracteres</small>
          </div>

          <div class="form-group">
            <label>Meta Keywords</label>
            <Input
              v-model="siteForm.meta_keywords"
              placeholder="palavra-chave, outra palavra, etc"
            />
          </div>
        </div>
      </div>
    </Modal>
  </MainLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Button from '@/Components/Button.vue';
import Input from '@/Components/Input.vue';
import StatCard from '@/Components/StatCard.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
  sites: Array,
  stats: Object,
});

const breadcrumbs = [
  { label: 'Dashboard', to: '/dashboard' },
  { label: 'CMS', to: '/cms' },
  { label: 'Sites', active: true }
];

const showDeleteModal = ref(false);
const siteToDelete = ref(null);
const showSiteModal = ref(false);
const editingSite = ref(null);

const siteForm = ref({
  name: '',
  domain: '',
  description: '',
  default_language: 'pt-BR',
  timezone: 'America/Sao_Paulo',
  is_active: true,
  meta_title: '',
  meta_description: '',
  meta_keywords: '',
});

const resetSiteForm = () => {
  siteForm.value = {
    name: '',
    domain: '',
    description: '',
    default_language: 'pt-BR',
    timezone: 'America/Sao_Paulo',
    is_active: true,
    meta_title: '',
    meta_description: '',
    meta_keywords: '',
  };
  editingSite.value = null;
};

const createSite = () => {
  resetSiteForm();
  showSiteModal.value = true;
};

const editSite = (site) => {
  editingSite.value = site;
  siteForm.value = {
    name: site.name,
    domain: site.domain,
    description: site.description || '',
    default_language: site.default_language || 'pt-BR',
    timezone: site.timezone || 'America/Sao_Paulo',
    is_active: site.is_active,
    meta_title: site.meta_title || '',
    meta_description: site.meta_description || '',
    meta_keywords: site.meta_keywords || '',
  };
  showSiteModal.value = true;
};

const saveSite = () => {
  if (editingSite.value) {
    router.put(`/cms/sites/${editingSite.value.id}`, siteForm.value, {
      onSuccess: () => {
        showSiteModal.value = false;
        resetSiteForm();
      },
    });
  } else {
    router.post('/cms/sites', siteForm.value, {
      onSuccess: () => {
        showSiteModal.value = false;
        resetSiteForm();
      },
    });
  }
};

const manageSite = (site) => {
  router.visit(`/cms/sites/${site.id}/content`);
};

const viewSettings = (site) => {
  router.visit(`/cms/sites/${site.id}/settings`);
};

const toggleActive = (site) => {
  router.patch(`/cms/sites/${site.id}`, {
    is_active: !site.is_active
  });
};

const deleteSite = (site) => {
  siteToDelete.value = site;
  showDeleteModal.value = true;
};

const confirmDelete = () => {
  router.delete(`/cms/sites/${siteToDelete.value.id}`, {
    onSuccess: () => {
      showDeleteModal.value = false;
      siteToDelete.value = null;
    },
  });
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('pt-BR', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  });
};

const formatRelativeDate = (date) => {
  const now = new Date();
  const then = new Date(date);
  const diffMs = now - then;
  const diffMins = Math.floor(diffMs / 60000);
  const diffHours = Math.floor(diffMs / 3600000);
  const diffDays = Math.floor(diffMs / 86400000);

  if (diffMins < 1) return 'agora';
  if (diffMins < 60) return `há ${diffMins} min`;
  if (diffHours < 24) return `há ${diffHours}h`;
  if (diffDays < 30) return `há ${diffDays}d`;
  return formatDate(date);
};
</script>

