<template>
  <MainLayout title="Páginas">
    <template #breadcrumbs>
      <Breadcrumbs :items="breadcrumbs" />
    </template>

    <div class="page-container">
      <div class="page-header">
        <div>
          <h1 class="page-header__title">PÁGINAS CMS</h1>
          <p class="page-header__subtitle">Gerencie páginas estáticas do seu site</p>
        </div>
        <Button variant="primary" @click="openCreateModal">
          <i class="fas fa-plus"></i>
          Nova Página
        </Button>
      </div>

      <!-- Stats -->
      <div class="stats-grid">
        <StatCard title="Total" :value="stats.total" icon="fa-file-alt" />
        <StatCard title="Publicadas" :value="stats.published" icon="fa-check-circle" />
        <StatCard title="Rascunhos" :value="stats.draft" icon="fa-edit" />
        <StatCard title="Pendentes" :value="stats.pending" icon="fa-clock" />
      </div>

      <!-- Filters -->
      <div class="filters-card">
        <div class="filters-header">
          <h3 class="filters-header__title">Filtros</h3>
          <button v-if="hasActiveFilters" class="btn btn--sm btn--ghost" @click="clearFilters">
            <i class="fas fa-times"></i>
            Limpar Filtros
          </button>
        </div>
        <div class="filters-grid">
          <Input v-model="filters.search" placeholder="Buscar por título ou slug..." icon="fa-search"
            @update:modelValue="loadPages" />
          <Select v-model="filters.status" :options="statusOptions" @update:modelValue="loadPages" />
          <Select v-model="filters.site_id" :options="siteOptions" @update:modelValue="loadPages" />
        </div>
      </div>

      <!-- Pages Table -->
      <Table :columns="tableColumns" :data="pages.data" striped hoverable>
        <template #cell-title="{ row }">
          <div class="page-title-cell">
            <i class="fas fa-file-alt"></i>
            <div>
              <div class="page-title-cell__title">{{ row.title }}</div>
              <div class="page-title-cell__slug">/{{ row.slug }}</div>
            </div>
          </div>
        </template>

        <template #cell-status="{ row }">
          <span class="badge" :class="`badge--${getStatusColor(row.status)}`">
            {{ getStatusLabel(row.status) }}
          </span>
        </template>

        <template #cell-site="{ row }">
          <div v-if="row.site">
            <div class="site-cell__name">{{ row.site.name }}</div>
            <div class="site-cell__domain">{{ row.site.domain }}</div>
          </div>
        </template>

        <template #cell-creator="{ row }">
          <div v-if="row.creator">{{ row.creator.name }}</div>
          <div v-else class="text-muted">-</div>
        </template>

        <template #cell-created_at="{ row }">
          <div class="date-cell">
            <div>{{ formatDate(row.created_at) }}</div>
            <div v-if="row.published_at" class="date-cell__sub">Pub: {{ formatDate(row.published_at) }}</div>
          </div>
        </template>

        <template #cell-actions="{ row }">
          <div class="actions-cell">
            <button class="btn btn--sm btn--ghost" title="Ver página" @click="viewPage(row)">
              <i class="fas fa-eye"></i>
            </button>
            <button class="btn btn--sm btn--ghost" title="Editar" @click="editPage(row)">
              <i class="fas fa-edit"></i>
            </button>
            <button v-if="row.status === 'draft' || row.status === 'pending'" class="btn btn--sm btn--ghost"
              title="Publicar" @click="publishPage(row)">
              <i class="fas fa-check"></i>
            </button>
            <button v-if="row.status === 'published'" class="btn btn--sm btn--ghost" title="Despublicar"
              @click="unpublishPage(row)">
              <i class="fas fa-times"></i>
            </button>
            <button class="btn btn--sm btn--ghost" title="Duplicar" @click="duplicatePage(row)">
              <i class="fas fa-copy"></i>
            </button>
            <button class="btn btn--sm btn--ghost btn--danger" title="Excluir" @click="deletePage(row)">
              <i class="fas fa-trash"></i>
            </button>
          </div>
        </template>
      </Table>

      <!-- Pagination -->
      <Pagination :current-page="pages.current_page" :last-page="pages.last_page" :from="pages.from" :to="pages.to"
        :total="pages.total" @page-changed="handlePageChange" />
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import Button from '@/Components/Button.vue';
import Input from '@/Components/Input.vue';
import Select from '@/Components/Select.vue';
import StatCard from '@/Components/StatCard.vue';
import Table from '@/Components/Table.vue';
import Pagination from '@/Components/Pagination.vue';
import { useAlert } from '@/Composables/useAlert';

const { toast, confirmDelete, prompt } = useAlert();

const props = defineProps({
  pages: Object,
  stats: Object,
  sites: Array,
  filters: Object
});

const filters = ref({
  search: props.filters?.search || '',
  status: props.filters?.status || '',
  site_id: props.filters?.site_id || ''
});

let searchTimeout = null;

const breadcrumbs = [
  { label: 'Páginas' }
];

const statusOptions = [
  { value: '', label: 'Todos os status' },
  { value: 'published', label: 'Publicadas' },
  { value: 'draft', label: 'Rascunhos' },
  { value: 'pending', label: 'Pendentes' }
];

const siteOptions = computed(() => [
  { value: '', label: 'Todos os sites' },
  ...(props.sites?.map(site => ({
    value: site.id,
    label: site.name
  })) || [])
]);

const tableColumns = [
  { field: 'title', label: 'Título / Slug', width: '25%' },
  { field: 'status', label: 'Status', width: '12%' },
  { field: 'site', label: 'Site', width: '15%' },
  { field: 'creator', label: 'Autor', width: '12%' },
  { field: 'created_at', label: 'Criado em', width: '13%' },
  { field: 'actions', label: 'Ações', width: '23%', align: 'center' }
];

const hasActiveFilters = computed(() => {
  return filters.value.search || filters.value.status || filters.value.site_id;
});

const getStatusLabel = (status) => {
  const labels = {
    published: 'Publicado',
    draft: 'Rascunho',
    pending: 'Pendente'
  };
  return labels[status] || status;
};

const getStatusColor = (status) => {
  const colors = {
    published: 'success',
    draft: 'warning',
    pending: 'info'
  };
  return colors[status] || 'neutral';
};

const formatDate = (date) => {
  if (!date) return 'N/A';
  return new Date(date).toLocaleDateString('pt-BR');
};

const loadPages = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    router.get('/pages', filters.value, {
      preserveState: true,
      preserveScroll: true
    });
  }, 300);
};

const clearFilters = () => {
  filters.value = {
    search: '',
    status: '',
    site_id: ''
  };
  router.get('/pages', filters.value, {
    preserveState: true,
    preserveScroll: true,
    onSuccess: () => {
      toast('Filtros limpos com sucesso', 'success');
    }
  });
};

const openCreateModal = async () => {
  // Primeiro seleciona o site
  if (!props.sites || props.sites.length === 0) {
    toast('É necessário ter pelo menos um site cadastrado', 'error');
    return;
  }

  const siteResult = await prompt(
    'Nova Página',
    'Selecione o site',
    '',
    'select',
    'Escolha um site',
    false,
    props.sites.map(site => ({ value: site.id, label: site.name }))
  );

  if (!siteResult.isConfirmed || !siteResult.value) {
    return;
  }

  const titleResult = await prompt(
    'Nova Página',
    'Título da página',
    '',
    'text',
    'Digite o título da página'
  );

  if (titleResult.isConfirmed && titleResult.value) {
    const contentResult = await prompt(
      'Nova Página',
      'Conteúdo da página',
      '',
      'textarea',
      'Digite o conteúdo (em HTML)'
    );

    if (contentResult.isConfirmed && contentResult.value) {
      router.post('/pages', {
        site_id: siteResult.value,
        title: titleResult.value,
        content: contentResult.value,
        status: 'draft'
      }, {
        onSuccess: () => {
          toast('Página criada com sucesso!', 'success');
        },
        onError: () => {
          toast('Erro ao criar página', 'error');
        }
      });
    }
  }
};

const viewPage = (page) => {
  if (page.site && page.slug) {
    window.open(`https://${page.site.domain}/${page.slug}`, '_blank');
  }
};

const editPage = async (page) => {
  const titleResult = await prompt(
    'Editar Página',
    'Título da página',
    page.title,
    'text',
    'Digite o título da página'
  );

  if (titleResult.isConfirmed && titleResult.value) {
    const contentResult = await prompt(
      'Editar Página',
      'Conteúdo da página',
      page.content,
      'textarea',
      'Digite o conteúdo (em HTML)'
    );

    if (contentResult.isConfirmed && contentResult.value) {
      router.put(`/pages/${page.id}`, {
        title: titleResult.value,
        content: contentResult.value
      }, {
        onSuccess: () => {
          toast('Página atualizada com sucesso!', 'success');
        },
        onError: () => {
          toast('Erro ao atualizar página', 'error');
        }
      });
    }
  }
};

const publishPage = async (page) => {
  const confirmed = await confirmDelete(
    'Publicar página',
    `Tem certeza que deseja publicar a página "${page.title}"?`
  );

  if (confirmed) {
    router.post(`/pages/${page.id}/publish`, {}, {
      preserveScroll: true,
      onSuccess: () => {
        toast('Página publicada com sucesso!', 'success');
      },
      onError: () => {
        toast('Erro ao publicar página', 'error');
      }
    });
  }
};

const unpublishPage = async (page) => {
  const confirmed = await confirmDelete(
    'Despublicar página',
    `Tem certeza que deseja despublicar a página "${page.title}"?`
  );

  if (confirmed) {
    router.post(`/pages/${page.id}/unpublish`, {}, {
      preserveScroll: true,
      onSuccess: () => {
        toast('Página despublicada com sucesso!', 'success');
      },
      onError: () => {
        toast('Erro ao despublicar página', 'error');
      }
    });
  }
};

const duplicatePage = async (page) => {
  const confirmed = await confirmDelete(
    'Duplicar página',
    `Tem certeza que deseja duplicar a página "${page.title}"?`
  );

  if (confirmed) {
    router.post(`/pages/${page.id}/duplicate`, {}, {
      preserveScroll: true,
      onSuccess: () => {
        toast('Página duplicada com sucesso!', 'success');
      },
      onError: () => {
        toast('Erro ao duplicar página', 'error');
      }
    });
  }
};

const deletePage = async (page) => {
  const confirmed = await confirmDelete(
    'Excluir página',
    `Tem certeza que deseja excluir a página "${page.title}"? Esta ação não pode ser desfeita.`
  );

  if (confirmed) {
    router.delete(`/pages/${page.id}`, {
      preserveScroll: true,
      onSuccess: () => {
        toast('Página excluída com sucesso!', 'success');
      },
      onError: () => {
        toast('Erro ao excluir página', 'error');
      }
    });
  }
};

const handlePageChange = (page) => {
  router.get('/pages', {
    ...filters.value,
    page
  }, {
    preserveState: true,
    preserveScroll: true
  });
};
</script>
