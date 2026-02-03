<template>
  <MainLayout title="Sites CMS">
    <template #breadcrumbs>
      <Breadcrumbs :items="breadcrumbs" />
    </template>

    <div class="sites-page">
      <!-- Header -->
      <div class="sites-page__header">
        <div class="sites-page__header-content">
          <h1 class="sites-page__title">Sites</h1>
          <p class="sites-page__subtitle">Gerencie seus sites e conteúdos</p>
        </div>
        <Button
          variant="accent"
          icon="fa-plus"
          @click="createSite"
        >
          Novo Site
        </Button>
      </div>

      <!-- Stats -->
      <div class="sites-page__stats">
        <StatCard
          title="Total de Sites"
          :value="stats.total"
          icon="fa-globe"
        />
        <StatCard
          title="Sites Ativos"
          :value="stats.active"
          icon="fa-check-circle"
        />
        <StatCard
          title="Total de Páginas"
          :value="stats.total_pages"
          icon="fa-file-alt"
        />
        <StatCard
          title="Conteúdos Publicados"
          :value="stats.published_content"
          icon="fa-paper-plane"
        />
      </div>

      <!-- Sites Grid -->
      <div v-if="sites.length === 0" class="sites-page__empty">
        <Card padding="lg">
          <div class="sites-page__empty-content">
            <i class="fas fa-globe"></i>
            <h3>Nenhum site criado</h3>
            <p>Crie seu primeiro site para começar a gerenciar conteúdo</p>
            <Button
              variant="accent"
              icon="fa-plus"
              @click="createSite"
            >
              Criar Primeiro Site
            </Button>
          </div>
        </Card>
      </div>

      <div v-else class="sites-page__grid">
        <Card
          v-for="site in sites"
          :key="site.id"
          hoverable
          padding="md"
        >
          <template #header>
            <div class="site-card__header">
              <div class="site-card__info">
                <h3 class="site-card__name">{{ site.name }}</h3>
                <a :href="site.domain" target="_blank" class="site-card__domain">
                  <i class="fas fa-external-link-alt"></i>
                  {{ site.domain }}
                </a>
              </div>
              <Badge :variant="site.is_active ? 'success' : 'danger'">
                {{ site.is_active ? 'Ativo' : 'Inativo' }}
              </Badge>
            </div>
          </template>

          <div class="site-card__body">
            <p v-if="site.description" class="site-card__description">
              {{ site.description }}
            </p>

            <div class="site-card__meta">
              <div class="site-card__meta-item">
                <i class="fas fa-file-alt"></i>
                <span>{{ site.pages_count }} páginas</span>
              </div>
              <div class="site-card__meta-item">
                <i class="fas fa-blog"></i>
                <span>{{ site.posts_count }} posts</span>
              </div>
              <div class="site-card__meta-item">
                <i class="fas fa-briefcase"></i>
                <span>{{ site.portfolios_count }} portfólios</span>
              </div>
            </div>

            <div class="site-card__settings">
              <div class="site-card__setting">
                <span class="site-card__setting-label">Idioma:</span>
                <Badge variant="default" size="sm">{{ site.default_language || 'pt-BR' }}</Badge>
              </div>
              <div class="site-card__setting">
                <span class="site-card__setting-label">Timezone:</span>
                <Badge variant="default" size="sm">{{ site.timezone || 'America/Sao_Paulo' }}</Badge>
              </div>
            </div>
          </div>

          <template #footer>
            <div class="site-card__footer">
              <div class="site-card__footer-info">
                <div class="site-card__footer-item">
                  <i class="fas fa-calendar"></i>
                  {{ formatDate(site.created_at) }}
                </div>
                <div class="site-card__footer-item">
                  <i class="fas fa-clock"></i>
                  {{ formatRelativeDate(site.updated_at) }}
                </div>
              </div>

              <div class="site-card__actions">
                <Button
                  variant="secondary"
                  size="sm"
                  @click="manageSite(site)"
                >
                  Gerenciar
                </Button>
                <Button
                  variant="ghost"
                  size="sm"
                  icon="fa-edit"
                  @click="editSite(site)"
                />
                <Button
                  variant="ghost"
                  size="sm"
                  :icon="site.is_active ? 'fa-toggle-on' : 'fa-toggle-off'"
                  @click="toggleActive(site)"
                />
                <Button
                  variant="ghost"
                  size="sm"
                  icon="fa-cog"
                  @click="viewSettings(site)"
                />
                <Button
                  variant="ghost"
                  size="sm"
                  icon="fa-trash"
                  @click="deleteSite(site)"
                />
              </div>
            </div>
          </template>
        </Card>
      </div>
    </div>

    <!-- Modal de Form Site -->
    <Modal
      :show="showSiteModal"
      :title="editingSite ? 'Editar Site' : 'Novo Site'"
      size="lg"
      show-footer
      @close="showSiteModal = false"
      @confirm="saveSite"
      @cancel="showSiteModal = false"
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
import Badge from '@/Components/Badge.vue';
import Card from '@/Components/Card.vue';
import StatCard from '@/Components/StatCard.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import { useAlert } from '@/composables/useAlert';

const alert = useAlert();

const props = defineProps({
  sites: Array,
  stats: Object,
});

const breadcrumbs = [
  { name: 'Dashboard', href: '/dashboard' },
  { name: 'CMS', href: '/cms' },
  { name: 'Sites' }
];

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
  }, {
    onSuccess: () => {
      alert.toast(site.is_active ? 'Site ativado!' : 'Site desativado!');
    },
  });
};

const deleteSite = async (site) => {
  const confirmed = await alert.confirm(
    `Tem certeza que deseja excluir o site <strong>${site.name}</strong>?`,
    'warning',
    {
      html: '⚠️ Todo o conteúdo associado (páginas, posts, menus, etc.) será permanentemente excluído.<br><br>Esta ação não pode ser desfeita.',
      confirmButtonText: 'Sim, excluir site',
      cancelButtonText: 'Cancelar',
    }
  );

  if (confirmed) {
    router.delete(`/cms/sites/${site.id}`, {
      onSuccess: () => {
        alert.success('Site excluído com sucesso!');
      },
      onError: () => {
        alert.error('Erro ao excluir site. Tente novamente.');
      },
    });
  }
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
  const diffInHours = Math.floor((now - then) / (1000 * 60 * 60));

  if (diffInHours < 24) {
    return 'Hoje';
  } else if (diffInHours < 48) {
    return 'Ontem';
  } else if (diffInHours < 168) {
    return `há ${Math.floor(diffInHours / 24)} dias`;
  } else {
    return formatDate(date);
  }
};
</script>

<style scoped>
.sites-page {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

/* Header */
.sites-page__header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 24px;
}

.sites-page__header-content {
  flex: 1;
}

.sites-page__title {
  font-family: 'Space Grotesk', sans-serif;
  font-size: 32px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  color: var(--text-primary);
  margin: 0 0 8px;
}

.sites-page__subtitle {
  font-family: 'Inter', sans-serif;
  font-size: 14px;
  color: var(--text-secondary);
  margin: 0;
}

/* Stats */
.sites-page__stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
}

/* Empty State */
.sites-page__empty-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 16px;
  padding: 48px 24px;
  text-align: center;
}

.sites-page__empty-content i {
  font-size: 64px;
  color: var(--text-muted);
}

.sites-page__empty-content h3 {
  font-family: 'Space Grotesk', sans-serif;
  font-size: 24px;
  font-weight: 700;
  color: var(--text-primary);
  margin: 0;
}

.sites-page__empty-content p {
  font-family: 'Inter', sans-serif;
  font-size: 14px;
  color: var(--text-secondary);
  margin: 0;
}

/* Grid */
.sites-page__grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
  gap: 20px;
}

/* Site Card */
.site-card__header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
}

.site-card__info {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.site-card__name {
  font-family: 'Space Grotesk', sans-serif;
  font-size: 18px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  color: var(--text-primary);
  margin: 0;
}

.site-card__domain {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-family: 'JetBrains Mono', monospace;
  font-size: 12px;
  color: var(--color-accent);
  text-decoration: none;
  transition: opacity 120ms ease;
}

.site-card__domain:hover {
  opacity: 0.8;
}

.site-card__body {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.site-card__description {
  font-family: 'Inter', sans-serif;
  font-size: 13px;
  line-height: 1.6;
  color: var(--text-secondary);
  margin: 0;
}

.site-card__meta {
  display: flex;
  align-items: center;
  gap: 16px;
  flex-wrap: wrap;
}

.site-card__meta-item {
  display: flex;
  align-items: center;
  gap: 6px;
  font-family: 'Inter', sans-serif;
  font-size: 13px;
  color: var(--text-secondary);
}

.site-card__meta-item i {
  font-size: 14px;
  color: var(--text-muted);
}

.site-card__settings {
  display: flex;
  gap: 16px;
  flex-wrap: wrap;
}

.site-card__setting {
  display: flex;
  align-items: center;
  gap: 8px;
}

.site-card__setting-label {
  font-family: 'Inter', sans-serif;
  font-size: 12px;
  color: var(--text-secondary);
}

.site-card__footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  flex-wrap: wrap;
}

.site-card__footer-info {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.site-card__footer-item {
  display: flex;
  align-items: center;
  gap: 6px;
  font-family: 'Inter', sans-serif;
  font-size: 11px;
  color: var(--text-muted);
}

.site-card__footer-item i {
  font-size: 10px;
}

.site-card__actions {
  display: flex;
  align-items: center;
  gap: 4px;
  flex-wrap: wrap;
}

@media (max-width: 768px) {
  .sites-page__header {
    flex-direction: column;
    align-items: stretch;
  }

  .sites-page__grid {
    grid-template-columns: 1fr;
  }

  .site-card__footer {
    flex-direction: column;
    align-items: flex-start;
  }

  .site-card__actions {
    width: 100%;
    justify-content: flex-start;
  }
}
</style> 
