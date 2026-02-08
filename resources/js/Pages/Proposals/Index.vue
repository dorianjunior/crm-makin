<template>
  <MainLayout title="Propostas Comerciais">
    <template #breadcrumbs>
      <Breadcrumbs :items="breadcrumbs" />
    </template>

    <div class="page-container">
      <div class="page-header">
        <div>
          <h1>Propostas Comerciais</h1>
          <p class="subtitle">Gerencie propostas e orçamentos</p>
        </div>
        <Button variant="primary" @click="createProposal">
          <i class="fa fa-plus"></i>
          Nova Proposta
        </Button>
      </div>

      <!-- Stats -->
      <div class="stats-grid">
        <StatCard
          title="Total de Propostas"
          :value="stats.total"
          icon="fa-file-invoice-dollar"
        />
        <StatCard
          title="Rascunhos"
          :value="stats.draft"
          icon="fa-file"
        />
        <StatCard
          title="Enviadas"
          :value="stats.sent"
          icon="fa-paper-plane"
        />
        <StatCard
          title="Aprovadas"
          :value="stats.approved"
          icon="fa-check-circle"
        />
      </div>

      <!-- Filters -->
      <div class="filters-card">
        <div class="filters-grid">
          <Input
            v-model="filters.search"
            placeholder="Buscar por lead ou número..."
            icon="fa-search"
            @input="loadProposals"
          />

          <Select
            v-model="filters.status"
            :options="statusOptions"
            placeholder="Todos os status"
            @change="loadProposals"
          />

          <Select
            v-model="filters.period"
            :options="periodOptions"
            placeholder="Todos os períodos"
            @change="loadProposals"
          />

          <Button variant="secondary" @click="clearFilters">
            <i class="fa fa-times"></i>
            Limpar Filtros
          </Button>
        </div>
      </div>

      <!-- Proposals Table -->
      <Table :columns="tableColumns" :data="proposals.data" striped hoverable>
        <template #cell-number="{ row }">
          <strong class="proposal-number">#{{ row.number }}</strong>
        </template>

        <template #cell-lead="{ row }">
          <div class="lead-info">
            <strong>{{ row.lead?.name }}</strong>
            <span v-if="row.lead?.email" class="text-secondary">{{ row.lead.email }}</span>
          </div>
        </template>

        <template #cell-status="{ row }">
          <span :class="['status-badge', `status-${row.status}`]">
            {{ getStatusLabel(row.status) }}
          </span>
        </template>

        <template #cell-value="{ row }">
          {{ formatCurrency(row.total_value) }}
        </template>

        <template #cell-valid_until="{ row }">
          <span :class="{ 'text-error': isExpired(row.valid_until) }">
            {{ formatDate(row.valid_until) }}
          </span>
        </template>

        <template #cell-created_at="{ row }">
          {{ formatDate(row.created_at) }}
        </template>

        <template #cell-actions="{ row }">
          <div class="action-buttons">
            <button @click="viewProposal(row)" class="action-btn" title="Visualizar">
              <i class="fa fa-eye"></i>
            </button>
            <button @click="editProposal(row)" class="action-btn" title="Editar">
              <i class="fa fa-edit"></i>
            </button>
            <button @click="duplicateProposal(row)" class="action-btn" title="Duplicar">
              <i class="fa fa-copy"></i>
            </button>
            <button
              v-if="row.status === 'draft'"
              @click="sendProposal(row)"
              class="action-btn success"
              title="Enviar"
            >
              <i class="fa fa-paper-plane"></i>
            </button>
            <button @click="downloadProposal(row)" class="action-btn" title="Download PDF">
              <i class="fa fa-download"></i>
            </button>
            <button @click="deleteProposal(row)" class="action-btn danger" title="Excluir">
              <i class="fa fa-trash"></i>
            </button>
          </div>
        </template>
      </Table>

      <!-- Pagination -->
      <Pagination
        :current-page="proposals.current_page"
        :last-page="proposals.last_page"
        :from="proposals.from"
        :to="proposals.to"
        :total="proposals.total"
        @page-change="handlePageChange"
      />
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

const { toast, confirmDelete } = useAlert();

const props = defineProps({
  proposals: Object,
  stats: Object,
  filters: Object
});

const filters = ref({
  search: props.filters?.search || '',
  status: props.filters?.status || '',
  period: props.filters?.period || ''
});

let searchTimeout = null;

const breadcrumbs = [
  { label: 'Propostas' }
];

const statusOptions = [
  { value: '', label: 'Todos os status' },
  { value: 'draft', label: 'Rascunho' },
  { value: 'sent', label: 'Enviada' },
  { value: 'viewed', label: 'Visualizada' },
  { value: 'approved', label: 'Aprovada' },
  { value: 'rejected', label: 'Rejeitada' }
];

const periodOptions = [
  { value: '', label: 'Todos os períodos' },
  { value: 'today', label: 'Hoje' },
  { value: 'week', label: 'Esta semana' },
  { value: 'month', label: 'Este mês' },
  { value: 'year', label: 'Este ano' }
];

const tableColumns = [
  { field: 'number', label: 'Número', width: '10%' },
  { field: 'lead', label: 'Lead', width: '20%' },
  { field: 'status', label: 'Status', width: '12%' },
  { field: 'value', label: 'Valor', width: '12%', align: 'right' },
  { field: 'valid_until', label: 'Validade', width: '12%' },
  { field: 'created_at', label: 'Data', width: '12%' },
  { field: 'actions', label: 'Ações', width: '15%', align: 'center' }
];

const getStatusLabel = (status) => {
  const labels = {
    draft: 'Rascunho',
    sent: 'Enviada',
    viewed: 'Visualizada',
    approved: 'Aprovada',
    rejected: 'Rejeitada'
  };
  return labels[status] || status;
};

const formatCurrency = (value) => {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  }).format(value || 0);
};

const formatDate = (date) => {
  if (!date) return 'N/A';
  return new Date(date).toLocaleDateString('pt-BR');
};

const isExpired = (date) => {
  if (!date) return false;
  return new Date(date) < new Date();
};

const loadProposals = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    router.get('/proposals', filters.value, {
      preserveState: true,
      preserveScroll: true
    });
  }, 300);
};

const clearFilters = () => {
  filters.value = {
    search: '',
    status: '',
    period: ''
  };
  router.get('/proposals', filters.value, {
    preserveState: true,
    preserveScroll: true,
    onSuccess: () => {
      toast('Filtros limpos com sucesso', 'success');
    }
  });
};

const createProposal = () => {
  router.visit('/proposals/create');
};

const viewProposal = (proposal) => {
  router.visit(`/proposals/${proposal.id}`);
};

const editProposal = (proposal) => {
  router.visit(`/proposals/${proposal.id}/edit`);
};

const duplicateProposal = (proposal) => {
  if (confirm(`Duplicar a proposta #${proposal.number}?`)) {
    router.post(`/proposals/${proposal.id}/duplicate`, {}, {
      preserveScroll: true,
      onSuccess: () => {
        toast('Proposta duplicada com sucesso', 'success');
      }
    });
  }
};

const sendProposal = (proposal) => {
  if (confirm(`Enviar proposta #${proposal.number} para ${proposal.lead?.email}?`)) {
    router.post(`/proposals/${proposal.id}/send`, {}, {
      preserveScroll: true,
      onSuccess: () => {
        toast('Proposta enviada com sucesso', 'success');
      }
    });
  }
};

const downloadProposal = (proposal) => {
  window.open(`/proposals/${proposal.id}/download`, '_blank');
};

const deleteProposal = async (proposal) => {
  const confirmed = await confirmDelete(
    'Excluir proposta',
    `Tem certeza que deseja excluir a proposta #${proposal.number}?`
  );

  if (confirmed) {
    router.delete(`/proposals/${proposal.id}`, {
      preserveScroll: true,
      onSuccess: () => {
        toast('Proposta excluída com sucesso', 'success');
      }
    });
  }
};

const handlePageChange = (page) => {
  router.get('/proposals', {
    ...filters.value,
    page
  }, {
    preserveState: true,
    preserveScroll: true
  });
};
</script>
