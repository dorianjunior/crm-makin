<template>
    <MainLayout title="Sites">
        <template #breadcrumbs>
            <Breadcrumbs :items="breadcrumbs" />
        </template>

        <div class="page-container">
            <div class="page-header">
                <div>
                    <h1 class="page-title">Sites CMS</h1>
                    <p class="page-subtitle">Gerencie os sites da sua empresa</p>
                </div>
                <Button variant="primary" @click="openCreateModal">
                    <i class="fas fa-plus"></i>
                    Novo Site
                </Button>
            </div>

            <!-- Stats -->
            <div class="stats-grid">
                <StatCard title="Total de Sites" :value="stats.total" icon="fa-globe" />
                <StatCard title="Ativos" :value="stats.active" icon="fa-check-circle" />
                <StatCard title="Inativos" :value="stats.inactive" icon="fa-times-circle" />
                <StatCard title="Total de Páginas" :value="stats.total_pages" icon="fa-file-alt" />
                <StatCard title="Total de Posts" :value="stats.total_posts" icon="fa-blog" />
            </div>

            <!-- Filters -->
            <div class="filters-card">
                <div class="filters-header">
                    <div class="filters-title">
                        <i class="fa fa-filter"></i>
                        <span>FILTROS</span>
                    </div>
                    <Button variant="ghost" size="sm" @click="clearFilters">Limpar filtros</Button>
                </div>
                <div class="filters-grid">
                    <Input v-model="filters.search" placeholder="Buscar por nome ou domínio..." icon="fa-search"
                        @input="loadSites" />

                    <Select v-model="filters.status" :options="statusOptions" @change="loadSites" />
                </div>
            </div>

            <!-- Sites Table -->
            <Table :columns="tableColumns" :data="sites.data" striped hoverable>
                <template #cell-name="{ row }">
                    <div class="info-item__value">
                        <strong>{{ row.name }}</strong>
                        <br>
                        <a :href="`https://${row.domain}`" target="_blank" class="info-link">
                            {{ row.domain }}
                            <i class="fas fa-external-link-alt" style="font-size: 0.75rem; margin-left: 4px;"></i>
                        </a>
                    </div>
                </template>

                <template #cell-status="{ row }">
                    <span :class="['badge', row.active ? 'badge--success' : 'badge--error']">
                        {{ row.active ? 'Ativo' : 'Inativo' }}
                    </span>
                </template>

                <template #cell-pages_count="{ row }">
                    <span class="stat-number" style="font-size: 1rem;">
                        {{ row.pages_count || 0 }}
                    </span>
                </template>

                <template #cell-posts_count="{ row }">
                    <span class="stat-number" style="font-size: 1rem;">
                        {{ row.posts_count || 0 }}
                    </span>
                </template>

                <template #cell-created_at="{ row }">
                    {{ formatDate(row.created_at) }}
                </template>

                <template #cell-actions="{ row }">
                    <div class="action-buttons">
                        <Button variant="ghost" size="sm" @click="viewSite(row)" title="Ver site">
                            <i class="fas fa-eye"></i>
                        </Button>
                        <Button variant="ghost" size="sm" @click="editSite(row)" title="Editar">
                            <i class="fas fa-edit"></i>
                        </Button>
                        <Button variant="ghost" size="sm" @click="showApiKey(row)" title="Ver API Key">
                            <i class="fas fa-key"></i>
                        </Button>
                        <Button variant="ghost" size="sm" @click="toggleActive(row)"
                            :title="row.active ? 'Desativar' : 'Ativar'">
                            <i :class="['fas', row.active ? 'fa-toggle-on' : 'fa-toggle-off']"></i>
                        </Button>
                        <Button variant="danger" size="sm" @click="deleteSite(row)" title="Excluir">
                            <i class="fas fa-trash"></i>
                        </Button>
                    </div>
                </template>
            </Table>

            <!-- Pagination -->
            <Pagination :current-page="sites.current_page" :last-page="sites.last_page" :from="sites.from"
                :to="sites.to" :total="sites.total" @page-change="handlePageChange" />
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
import Select from '@/Components/Select.vue';
import StatCard from '@/Components/StatCard.vue';
import Table from '@/Components/Table.vue';
import Pagination from '@/Components/Pagination.vue';
import { useAlert } from '@/Composables/useAlert';

const { toast, confirmDelete, prompt } = useAlert();

const props = defineProps({
    sites: Object,
    stats: Object,
    filters: Object
});

const filters = ref({
    search: props.filters?.search || '',
    status: props.filters?.status || ''
});

let searchTimeout = null;

const breadcrumbs = [
    { label: 'Sites' }
];

const statusOptions = [
    { value: '', label: 'Todos os status' },
    { value: 'active', label: 'Ativos' },
    { value: 'inactive', label: 'Inativos' }
];

const tableColumns = [
    { field: 'name', label: 'Nome / Domínio', width: '30%' },
    { field: 'status', label: 'Status', width: '12%' },
    { field: 'pages_count', label: 'Páginas', width: '10%', align: 'center' },
    { field: 'posts_count', label: 'Posts', width: '10%', align: 'center' },
    { field: 'created_at', label: 'Criado em', width: '15%' },
    { field: 'actions', label: 'Ações', width: '23%', align: 'center' }
];

const formatDate = (date) => {
    if (!date) return 'N/A';
    return new Date(date).toLocaleDateString('pt-BR');
};

const loadSites = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get('/sites', filters.value, {
            preserveState: true,
            preserveScroll: true
        });
    }, 300);
};

const clearFilters = () => {
    filters.value = {
        search: '',
        status: ''
    };
    router.get('/sites', filters.value, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            toast('Filtros limpos com sucesso', 'success');
        }
    });
};

const openCreateModal = async () => {
    const result = await prompt(
        'Novo Site',
        'Nome do site',
        '',
        'text',
        'Digite o nome do site'
    );

    if (result.isConfirmed && result.value) {
        const domain = await prompt(
            'Novo Site',
            'Domínio do site',
            '',
            'text',
            'exemplo.com.br'
        );

        if (domain.isConfirmed && domain.value) {
            router.post('/sites', {
                name: result.value,
                domain: domain.value,
                active: true
            }, {
                preserveScroll: true,
                onSuccess: () => {
                    toast('Site criado com sucesso!', 'success');
                },
                onError: (errors) => {
                    const errorMessages = Object.values(errors).flat().join(', ');
                    toast(errorMessages, 'error');
                }
            });
        }
    }
};

const viewSite = (site) => {
    window.open(`https://${site.domain}`, '_blank');
};

const editSite = async (site) => {
    const result = await prompt(
        'Editar Site',
        'Nome do site',
        site.name,
        'text',
        'Digite o nome do site'
    );

    if (result.isConfirmed && result.value) {
        const domain = await prompt(
            'Editar Site',
            'Domínio do site',
            site.domain,
            'text',
            'exemplo.com.br'
        );

        if (domain.isConfirmed && domain.value) {
            router.put(`/sites/${site.id}`, {
                name: result.value,
                domain: domain.value
            }, {
                preserveScroll: true,
                onSuccess: () => {
                    toast('Site atualizado com sucesso!', 'success');
                },
                onError: (errors) => {
                    const errorMessages = Object.values(errors).flat().join(', ');
                    toast(errorMessages, 'error');
                }
            });
        }
    }
};

const showApiKey = async (site) => {
    await prompt(
        'API Key',
        `API Key do site ${site.name}`,
        site.api_key,
        'text',
        '',
        true // readonly
    );
};

const toggleActive = async (site) => {
    const action = site.active ? 'desativar' : 'ativar';
    const confirmed = await confirmDelete(
        `${action.charAt(0).toUpperCase() + action.slice(1)} site`,
        `Tem certeza que deseja ${action} o site ${site.name}?`
    );

    if (confirmed) {
        router.patch(`/sites/${site.id}/toggle-active`, {}, {
            preserveScroll: true,
            onSuccess: () => {
                toast(`Site ${action}do com sucesso!`, 'success');
            }
        });
    }
};

const deleteSite = async (site) => {
    const confirmed = await confirmDelete(
        'Excluir site',
        `Tem certeza que deseja excluir o site ${site.name}? Esta ação não pode ser desfeita.`
    );

    if (confirmed) {
        router.delete(`/sites/${site.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                toast('Site excluído com sucesso!', 'success');
            }
        });
    }
};

const handlePageChange = (page) => {
    router.get('/sites', {
        ...filters.value,
        page
    }, {
        preserveState: true,
        preserveScroll: true
    });
};
</script>
