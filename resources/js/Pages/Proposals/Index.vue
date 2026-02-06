<template>
  <MainLayout title="Propostas Comerciais">
    <div class="proposals-page">
      <Breadcrumbs :items="breadcrumbs" />

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
      <div class="stats-row">
        <StatCard
          label="Total de Propostas"
          :value="stats.total"
          icon="fa fa-file-invoice-dollar"
          color="primary"
        />
        <StatCard
          label="Rascunhos"
          :value="stats.draft"
          icon="fa fa-file"
          color="info"
        />
        <StatCard
          label="Enviadas"
          :value="stats.sent"
          icon="fa fa-paper-plane"
          color="warning"
        />
        <StatCard
          label="Aprovadas"
          :value="stats.approved"
          icon="fa fa-check-circle"
          color="success"
        />
      </div>

      <!-- Filters -->
      <div class="filters-card">
        <div class="filters-grid">
          <div class="filter-item">
            <label>Buscar</label>
            <Input
              v-model="filters.search"
              placeholder="Lead ou número..."
              icon="fa fa-search"
            />
          </div>

          <div class="filter-item">
            <label>Status</label>
            <select v-model="filters.status" class="form-select">
              <option value="">Todos</option>
              <option value="draft">Rascunho</option>
              <option value="sent">Enviada</option>
              <option value="viewed">Visualizada</option>
              <option value="approved">Aprovada</option>
              <option value="rejected">Rejeitada</option>
            </select>
          </div>

          <div class="filter-item">
            <label>Período</label>
            <select v-model="filters.period" class="form-select">
              <option value="">Todos</option>
              <option value="today">Hoje</option>
              <option value="week">Esta semana</option>
              <option value="month">Este mês</option>
              <option value="year">Este ano</option>
            </select>
          </div>

          <div class="filter-actions">
            <Button variant="secondary" @click="applyFilters">
              <i class="fa fa-filter"></i>
              Filtrar
            </Button>
            <Button variant="secondary" @click="clearFilters">
              <i class="fa fa-times"></i>
              Limpar
            </Button>
          </div>
        </div>
      </div>

      <!-- Proposals Table -->
      <div class="table-card">
        <table class="data-table">
          <thead>
            <tr>
              <th>Número</th>
              <th>Lead</th>
              <th>Valor</th>
              <th>Status</th>
              <th>Data</th>
              <th>Validade</th>
              <th>Ações</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="proposal in proposals.data" :key="proposal.id">
              <td>
                <strong class="proposal-number">#{{ proposal.number }}</strong>
              </td>
              <td>
                <div class="lead-info">
                  <strong>{{ proposal.lead?.name }}</strong>
                  <span v-if="proposal.lead?.company">{{ proposal.lead.company }}</span>
                </div>
              </td>
              <td class="value">{{ formatCurrency(proposal.total_value) }}</td>
              <td>
                <span :class="['status-badge', proposal.status]">
                  {{ getStatusLabel(proposal.status) }}
                </span>
              </td>
              <td>{{ formatDate(proposal.created_at) }}</td>
              <td>
                <span :class="{ 'text-error': isExpired(proposal.valid_until) }">
                  {{ formatDate(proposal.valid_until) }}
                </span>
              </td>
              <td>
                <div class="action-buttons">
                  <button @click="viewProposal(proposal)" class="action-btn" title="Visualizar">
                    <i class="fa fa-eye"></i>
                  </button>
                  <button @click="editProposal(proposal)" class="action-btn" title="Editar">
                    <i class="fa fa-edit"></i>
                  </button>
                  <button @click="downloadProposal(proposal)" class="action-btn" title="Download PDF">
                    <i class="fa fa-download"></i>
                  </button>
                  <button
                    v-if="proposal.status === 'draft'"
                    @click="sendProposal(proposal)"
                    class="action-btn success"
                    title="Enviar"
                  >
                    <i class="fa fa-paper-plane"></i>
                  </button>
                  <button
                    @click="duplicateProposal(proposal)"
                    class="action-btn"
                    title="Duplicar"
                  >
                    <i class="fa fa-copy"></i>
                  </button>
                  <button
                    @click="deleteProposal(proposal)"
                    class="action-btn danger"
                    title="Excluir"
                  >
                    <i class="fa fa-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Pagination -->
        <div class="pagination">
          <button
            @click="goToPage(proposals.current_page - 1)"
            :disabled="!proposals.prev_page_url"
            class="pagination-btn"
          >
            <i class="fa fa-chevron-left"></i>
          </button>

          <span class="pagination-info">
            Página {{ proposals.current_page }} de {{ proposals.last_page }}
          </span>

          <button
            @click="goToPage(proposals.current_page + 1)"
            :disabled="!proposals.next_page_url"
            class="pagination-btn"
          >
            <i class="fa fa-chevron-right"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- View Modal -->
    <Modal v-if="showViewModal" @close="showViewModal = false" size="xlarge">
      <template #header>
        <h2>Proposta #{{ currentProposal?.number }}</h2>
      </template>

      <template #body>
        <div class="proposal-view">
          <!-- Header -->
          <div class="proposal-header">
            <div class="proposal-info">
              <div class="info-row">
                <span class="label">Lead:</span>
                <span class="value">{{ currentProposal?.lead?.name }}</span>
              </div>
              <div class="info-row">
                <span class="label">Email:</span>
                <span class="value">{{ currentProposal?.lead?.email }}</span>
              </div>
              <div class="info-row">
                <span class="label">Data:</span>
                <span class="value">{{ formatDate(currentProposal?.created_at) }}</span>
              </div>
              <div class="info-row">
                <span class="label">Validade:</span>
                <span class="value">{{ formatDate(currentProposal?.valid_until) }}</span>
              </div>
            </div>

            <div class="proposal-status-card">
              <span :class="['status-badge-large', currentProposal?.status]">
                {{ getStatusLabel(currentProposal?.status) }}
              </span>
            </div>
          </div>

          <!-- Items -->
          <div class="proposal-items">
            <h3>Itens da Proposta</h3>
            <table class="items-table">
              <thead>
                <tr>
                  <th>Item</th>
                  <th>Qtd</th>
                  <th>Preço Unit.</th>
                  <th>Desconto</th>
                  <th>Total</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in currentProposal?.items" :key="item.id">
                  <td>
                    <strong>{{ item.product?.name }}</strong>
                    <p v-if="item.description">{{ item.description }}</p>
                  </td>
                  <td>{{ item.quantity }}</td>
                  <td>{{ formatCurrency(item.unit_price) }}</td>
                  <td>{{ item.discount }}%</td>
                  <td>{{ formatCurrency(item.total) }}</td>
                </tr>
              </tbody>
              <tfoot>
                <tr>
                  <td colspan="4" class="text-right"><strong>Subtotal:</strong></td>
                  <td><strong>{{ formatCurrency(currentProposal?.subtotal) }}</strong></td>
                </tr>
                <tr v-if="currentProposal?.discount > 0">
                  <td colspan="4" class="text-right">Desconto:</td>
                  <td>-{{ formatCurrency(currentProposal?.discount) }}</td>
                </tr>
                <tr class="total-row">
                  <td colspan="4" class="text-right"><strong>Total:</strong></td>
                  <td><strong class="total-value">{{ formatCurrency(currentProposal?.total_value) }}</strong></td>
                </tr>
              </tfoot>
            </table>
          </div>

          <!-- Notes -->
          <div v-if="currentProposal?.notes" class="proposal-notes">
            <h3>Observações</h3>
            <p>{{ currentProposal.notes }}</p>
          </div>

          <!-- Terms -->
          <div v-if="currentProposal?.terms" class="proposal-terms">
            <h3>Termos e Condições</h3>
            <p>{{ currentProposal.terms }}</p>
          </div>
        </div>
      </template>

      <template #footer>
        <Button variant="secondary" @click="showViewModal = false">
          Fechar
        </Button>
        <Button variant="secondary" @click="downloadProposal(currentProposal)">
          <i class="fa fa-download"></i>
          Download PDF
        </Button>
        <Button
          v-if="currentProposal?.status === 'draft'"
          variant="primary"
          @click="sendProposal(currentProposal)"
        >
          <i class="fa fa-paper-plane"></i>
          Enviar Proposta
        </Button>
      </template>
    </Modal>
  </MainLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import Button from '@/Components/Button.vue';
import Input from '@/Components/Input.vue';
import StatCard from '@/Components/StatCard.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
  proposals: Object,
  stats: Object
});

const showViewModal = ref(false);
const currentProposal = ref(null);

const filters = ref({
  search: '',
  status: '',
  period: ''
});

const breadcrumbs = [
  { label: 'Propostas' }
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

const applyFilters = () => {
  router.get('/proposals', filters.value, {
    preserveState: true,
    preserveScroll: true
  });
};

const clearFilters = () => {
  filters.value = {
    search: '',
    status: '',
    period: ''
  };
  applyFilters();
};

const createProposal = () => {
  router.visit('/proposals/create');
};

const viewProposal = (proposal) => {
  currentProposal.value = proposal;
  showViewModal.value = true;
};

const editProposal = (proposal) => {
  router.visit(`/proposals/${proposal.id}/edit`);
};

const downloadProposal = (proposal) => {
  window.open(`/proposals/${proposal.id}/download`, '_blank');
};

const sendProposal = (proposal) => {
  if (confirm(`Enviar proposta #${proposal.number} para ${proposal.lead?.email}?`)) {
    router.post(`/proposals/${proposal.id}/send`, {}, {
      preserveScroll: true,
      onSuccess: () => {
        showViewModal.value = false;
      }
    });
  }
};

const duplicateProposal = (proposal) => {
  if (confirm(`Duplicar a proposta #${proposal.number}?`)) {
    router.post(`/proposals/${proposal.id}/duplicate`, {}, {
      preserveScroll: true
    });
  }
};

const deleteProposal = (proposal) => {
  if (confirm(`Tem certeza que deseja excluir a proposta #${proposal.number}?`)) {
    router.delete(`/proposals/${proposal.id}`, {
      preserveScroll: true
    });
  }
};

const goToPage = (page) => {
  router.get('/proposals', {
    ...filters.value,
    page
  }, {
    preserveState: true,
    preserveScroll: true
  });
};
</script>

