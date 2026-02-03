<template>
  <MainLayout>
    <div class="proposal-form">
      <Breadcrumbs :items="breadcrumbs" />

      <div class="page-header">
        <div>
          <h1>{{ isEdit ? 'Editar Proposta' : 'Nova Proposta' }}</h1>
          <p v-if="isEdit" class="subtitle">Proposta #{{ proposal?.number }}</p>
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
          <Button variant="primary" @click="sendProposal" :disabled="saving || !form.lead_id">
            <i class="fa fa-paper-plane"></i>
            Enviar Proposta
          </Button>
        </div>
      </div>

      <div class="form-layout">
        <!-- Main Content -->
        <div class="form-main">
          <!-- Lead Selection -->
          <div class="form-card">
            <h3>Cliente</h3>

            <div class="form-group">
              <label>Selecione o Lead *</label>
              <select v-model="form.lead_id" class="form-select" @change="loadLeadData">
                <option value="">Selecione um lead...</option>
                <option v-for="lead in leads" :key="lead.id" :value="lead.id">
                  {{ lead.name }} - {{ lead.email }}
                </option>
              </select>
            </div>

            <div v-if="selectedLead" class="lead-preview">
              <div class="preview-item">
                <span class="label">Nome:</span>
                <span class="value">{{ selectedLead.name }}</span>
              </div>
              <div class="preview-item">
                <span class="label">Email:</span>
                <span class="value">{{ selectedLead.email }}</span>
              </div>
              <div v-if="selectedLead.phone" class="preview-item">
                <span class="label">Telefone:</span>
                <span class="value">{{ selectedLead.phone }}</span>
              </div>
              <div v-if="selectedLead.company" class="preview-item">
                <span class="label">Empresa:</span>
                <span class="value">{{ selectedLead.company }}</span>
              </div>
            </div>
          </div>

          <!-- Items -->
          <div class="form-card">
            <div class="card-header">
              <h3>Itens da Proposta</h3>
              <Button variant="secondary" size="small" @click="addItem">
                <i class="fa fa-plus"></i>
                Adicionar Item
              </Button>
            </div>

            <div class="items-list">
              <div
                v-for="(item, index) in form.items"
                :key="index"
                class="item-row"
              >
                <div class="item-number">{{ index + 1 }}</div>

                <div class="item-form">
                  <div class="form-row">
                    <div class="form-group flex-3">
                      <label>Produto/Serviço *</label>
                      <select
                        v-model="item.product_id"
                        class="form-select"
                        @change="loadProductData(index)"
                      >
                        <option value="">Selecione um produto...</option>
                        <option v-for="product in products" :key="product.id" :value="product.id">
                          {{ product.name }} - {{ formatCurrency(product.price) }}
                        </option>
                      </select>
                    </div>

                    <div class="form-group">
                      <label>Quantidade *</label>
                      <Input
                        v-model="item.quantity"
                        type="number"
                        min="1"
                        @input="calculateItemTotal(index)"
                      />
                    </div>

                    <div class="form-group">
                      <label>Preço Unit.</label>
                      <Input
                        v-model="item.unit_price"
                        type="number"
                        step="0.01"
                        min="0"
                        @input="calculateItemTotal(index)"
                      />
                    </div>

                    <div class="form-group">
                      <label>Desconto (%)</label>
                      <Input
                        v-model="item.discount"
                        type="number"
                        step="1"
                        min="0"
                        max="100"
                        @input="calculateItemTotal(index)"
                      />
                    </div>

                    <div class="form-group">
                      <label>Total</label>
                      <div class="total-display">
                        {{ formatCurrency(item.total) }}
                      </div>
                    </div>

                    <div class="item-actions">
                      <button @click="removeItem(index)" class="remove-btn" title="Remover">
                        <i class="fa fa-trash"></i>
                      </button>
                    </div>
                  </div>

                  <div class="form-group">
                    <label>Descrição do Item</label>
                    <textarea
                      v-model="item.description"
                      rows="2"
                      placeholder="Detalhes adicionais sobre este item..."
                    ></textarea>
                  </div>
                </div>
              </div>

              <div v-if="!form.items.length" class="empty-items">
                <i class="fa fa-inbox"></i>
                <p>Nenhum item adicionado</p>
                <Button variant="primary" size="small" @click="addItem">
                  <i class="fa fa-plus"></i>
                  Adicionar Primeiro Item
                </Button>
              </div>
            </div>

            <!-- Totals -->
            <div class="totals-section">
              <div class="total-row">
                <span class="label">Subtotal:</span>
                <span class="value">{{ formatCurrency(subtotal) }}</span>
              </div>

              <div class="total-row">
                <span class="label">Desconto:</span>
                <span class="value discount">-{{ formatCurrency(totalDiscount) }}</span>
              </div>

              <div class="total-row grand-total">
                <span class="label">Total Geral:</span>
                <span class="value">{{ formatCurrency(grandTotal) }}</span>
              </div>
            </div>
          </div>

          <!-- Additional Info -->
          <div class="form-card">
            <h3>Informações Adicionais</h3>

            <div class="form-group">
              <label>Observações</label>
              <textarea
                v-model="form.notes"
                rows="4"
                placeholder="Observações internas sobre a proposta..."
              ></textarea>
            </div>

            <div class="form-group">
              <label>Termos e Condições</label>
              <textarea
                v-model="form.terms"
                rows="6"
                placeholder="Termos e condições da proposta..."
              ></textarea>
              <span class="help-text">
                Estes termos serão exibidos no PDF da proposta
              </span>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="form-sidebar">
          <!-- Status -->
          <div class="sidebar-card">
            <h3>Status</h3>

            <div class="status-info">
              <span :class="['status-badge-large', form.status]">
                {{ getStatusLabel(form.status) }}
              </span>
            </div>

            <div v-if="form.sent_at" class="info-text">
              <i class="fa fa-paper-plane"></i>
              Enviada em: {{ formatDateTime(form.sent_at) }}
            </div>

            <div v-if="form.viewed_at" class="info-text">
              <i class="fa fa-eye"></i>
              Visualizada em: {{ formatDateTime(form.viewed_at) }}
            </div>
          </div>

          <!-- Validity -->
          <div class="sidebar-card">
            <h3>Validade</h3>

            <div class="form-group">
              <label>Válida até *</label>
              <Input
                v-model="form.valid_until"
                type="date"
                :min="today"
              />
            </div>

            <div class="form-group">
              <label>Dias de Validade</label>
              <div class="validity-days">
                <button
                  v-for="days in [7, 15, 30, 60]"
                  :key="days"
                  @click="setValidityDays(days)"
                  class="days-btn"
                >
                  {{ days }} dias
                </button>
              </div>
            </div>
          </div>

          <!-- Payment Terms -->
          <div class="sidebar-card">
            <h3>Condições de Pagamento</h3>

            <div class="form-group">
              <label>Forma de Pagamento</label>
              <select v-model="form.payment_method" class="form-select">
                <option value="">Selecione...</option>
                <option value="pix">PIX</option>
                <option value="bank_transfer">Transferência Bancária</option>
                <option value="credit_card">Cartão de Crédito</option>
                <option value="boleto">Boleto</option>
                <option value="installments">Parcelado</option>
              </select>
            </div>

            <div v-if="form.payment_method === 'installments'" class="form-group">
              <label>Número de Parcelas</label>
              <Input
                v-model="form.installments"
                type="number"
                min="2"
                max="12"
              />
            </div>

            <div class="form-group">
              <label>Observações de Pagamento</label>
              <textarea
                v-model="form.payment_notes"
                rows="3"
                placeholder="Ex: Entrada de 50% + 2x de..."
              ></textarea>
            </div>
          </div>

          <!-- Template -->
          <div class="sidebar-card">
            <h3>Modelo de PDF</h3>

            <div class="form-group">
              <label>Template</label>
              <select v-model="form.template" class="form-select">
                <option value="default">Padrão</option>
                <option value="modern">Moderno</option>
                <option value="minimal">Minimalista</option>
                <option value="corporate">Corporativo</option>
              </select>
            </div>

            <div class="form-group">
              <label>Cor Primária</label>
              <div class="color-picker">
                <input type="color" v-model="form.primary_color">
                <Input v-model="form.primary_color" readonly />
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="sidebar-card">
            <h3>Ações</h3>

            <Button variant="secondary" @click="previewProposal" block class="mb-2">
              <i class="fa fa-eye"></i>
              Visualizar PDF
            </Button>

            <Button
              v-if="isEdit"
              variant="secondary"
              @click="duplicateProposal"
              block
              class="mb-2"
            >
              <i class="fa fa-copy"></i>
              Duplicar
            </Button>

            <Button
              v-if="isEdit && form.status === 'draft'"
              variant="danger"
              @click="deleteProposal"
              block
            >
              <i class="fa fa-trash"></i>
              Excluir
            </Button>
          </div>

          <!-- Summary -->
          <div v-if="form.items.length" class="sidebar-card summary-card">
            <h3>Resumo</h3>

            <div class="summary-item">
              <span class="label">Total de Itens:</span>
              <span class="value">{{ form.items.length }}</span>
            </div>

            <div class="summary-item">
              <span class="label">Quantidade Total:</span>
              <span class="value">{{ totalQuantity }}</span>
            </div>

            <div class="summary-item">
              <span class="label">Desconto Total:</span>
              <span class="value">{{ formatCurrency(totalDiscount) }}</span>
            </div>

            <div class="summary-item highlight">
              <span class="label">Valor Total:</span>
              <span class="value">{{ formatCurrency(grandTotal) }}</span>
            </div>
          </div>
        </div>
      </div>
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

const props = defineProps({
  proposal: Object,
  leads: Array,
  products: Array
});

const saving = ref(false);
const selectedLead = ref(null);

const isEdit = computed(() => !!props.proposal);

const today = computed(() => {
  return new Date().toISOString().split('T')[0];
});

const form = ref({
  lead_id: props.proposal?.lead_id || '',
  status: props.proposal?.status || 'draft',
  valid_until: props.proposal?.valid_until || getDefaultValidUntil(),
  payment_method: props.proposal?.payment_method || '',
  installments: props.proposal?.installments || 1,
  payment_notes: props.proposal?.payment_notes || '',
  template: props.proposal?.template || 'default',
  primary_color: props.proposal?.primary_color || '#1160b7',
  notes: props.proposal?.notes || '',
  terms: props.proposal?.terms || getDefaultTerms(),
  items: props.proposal?.items || [],
  sent_at: props.proposal?.sent_at || null,
  viewed_at: props.proposal?.viewed_at || null
});

const breadcrumbs = [
  { label: 'Propostas', to: '/proposals' },
  { label: isEdit.value ? 'Editar' : 'Nova Proposta' }
];

// Computed
const subtotal = computed(() => {
  return form.value.items.reduce((sum, item) => {
    const itemTotal = (item.unit_price || 0) * (item.quantity || 0);
    return sum + itemTotal;
  }, 0);
});

const totalDiscount = computed(() => {
  return form.value.items.reduce((sum, item) => {
    const itemTotal = (item.unit_price || 0) * (item.quantity || 0);
    const discount = itemTotal * ((item.discount || 0) / 100);
    return sum + discount;
  }, 0);
});

const grandTotal = computed(() => {
  return subtotal.value - totalDiscount.value;
});

const totalQuantity = computed(() => {
  return form.value.items.reduce((sum, item) => sum + (parseInt(item.quantity) || 0), 0);
});

// Methods
function getDefaultValidUntil() {
  const date = new Date();
  date.setDate(date.getDate() + 30);
  return date.toISOString().split('T')[0];
}

function getDefaultTerms() {
  return `1. Validade desta proposta: 30 dias
2. Prazo de entrega: A combinar
3. Forma de pagamento: Conforme descrito acima
4. Garantia: 90 dias
5. Esta proposta não inclui taxas bancárias ou impostos adicionais`;
}

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

const formatDateTime = (date) => {
  if (!date) return 'N/A';
  return new Date(date).toLocaleString('pt-BR');
};

const loadLeadData = () => {
  selectedLead.value = props.leads.find(l => l.id === form.value.lead_id);
};

const loadProductData = (index) => {
  const item = form.value.items[index];
  const product = props.products.find(p => p.id === item.product_id);

  if (product) {
    item.unit_price = product.price;
    item.description = product.description;
    calculateItemTotal(index);
  }
};

const calculateItemTotal = (index) => {
  const item = form.value.items[index];
  const subtotal = (item.unit_price || 0) * (item.quantity || 1);
  const discount = subtotal * ((item.discount || 0) / 100);
  item.total = subtotal - discount;
};

const addItem = () => {
  form.value.items.push({
    product_id: '',
    quantity: 1,
    unit_price: 0,
    discount: 0,
    total: 0,
    description: ''
  });
};

const removeItem = (index) => {
  form.value.items.splice(index, 1);
};

const setValidityDays = (days) => {
  const date = new Date();
  date.setDate(date.getDate() + days);
  form.value.valid_until = date.toISOString().split('T')[0];
};

const saveDraft = () => {
  form.value.status = 'draft';
  save();
};

const sendProposal = () => {
  if (!form.value.lead_id) {
    alert('Selecione um lead para a proposta');
    return;
  }

  if (!form.value.items.length) {
    alert('Adicione pelo menos um item à proposta');
    return;
  }

  form.value.status = 'sent';
  form.value.sent_at = new Date().toISOString();
  save();
};

const save = () => {
  saving.value = true;

  // Prepare data
  const data = {
    ...form.value,
    total_value: grandTotal.value,
    subtotal: subtotal.value,
    discount: totalDiscount.value
  };

  const url = isEdit.value
    ? `/proposals/${props.proposal.id}`
    : '/proposals';

  const method = isEdit.value ? 'put' : 'post';

  router[method](url, data, {
    preserveScroll: true,
    onFinish: () => {
      saving.value = false;
    }
  });
};

const previewProposal = () => {
  if (isEdit.value) {
    window.open(`/proposals/${props.proposal.id}/preview`, '_blank');
  } else {
    alert('Salve a proposta primeiro para visualizar o PDF');
  }
};

const duplicateProposal = () => {
  if (confirm('Duplicar esta proposta?')) {
    router.post(`/proposals/${props.proposal.id}/duplicate`);
  }
};

const deleteProposal = () => {
  if (confirm('Tem certeza que deseja excluir esta proposta?')) {
    router.delete(`/proposals/${props.proposal.id}`);
  }
};

const goBack = () => {
  router.visit('/proposals');
};

// Load initial lead data if editing
if (form.value.lead_id) {
  loadLeadData();
}
</script>

<style scoped lang="scss">
.proposal-form {
  padding: 2rem;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 2rem;

  h1 {
    margin: 0 0 0.5rem 0;
    color: var(--text-primary);
  }

  .subtitle {
    margin: 0;
    color: var(--text-secondary);
  }

  .header-actions {
    display: flex;
    gap: 1rem;
  }
}

.form-layout {
  display: grid;
  grid-template-columns: 1fr 350px;
  gap: 2rem;
}

.form-main,
.form-sidebar {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.form-card,
.sidebar-card {
  background: var(--bg-primary);
  border: 1px solid var(--border-color);
  border-radius: 8px;
  padding: 2rem;

  h3 {
    margin: 0 0 1.5rem 0;
    color: var(--text-primary);
    font-size: 1.1rem;
  }

  .card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;

    h3 {
      margin: 0;
    }
  }
}

.form-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 1rem;
  margin-bottom: 1rem;

  .flex-3 {
    grid-column: span 3;
  }
}

.form-group {
  margin-bottom: 1rem;

  &:last-child {
    margin-bottom: 0;
  }

  label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: var(--text-primary);
  }

  textarea {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid var(--border-color);
    border-radius: 4px;
    font-family: inherit;
    background: var(--bg-primary);
    color: var(--text-primary);
    resize: vertical;

    &:focus {
      outline: none;
      border-color: var(--color-primary);
    }
  }

  .form-select {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid var(--border-color);
    border-radius: 4px;
    background: var(--bg-primary);
    color: var(--text-primary);

    &:focus {
      outline: none;
      border-color: var(--color-primary);
    }
  }

  .help-text {
    display: block;
    margin-top: 0.25rem;
    font-size: 0.85rem;
    color: var(--text-secondary);
  }
}

.lead-preview {
  margin-top: 1rem;
  padding: 1rem;
  background: var(--bg-secondary);
  border-radius: 4px;

  .preview-item {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 0.5rem;

    &:last-child {
      margin-bottom: 0;
    }

    .label {
      color: var(--text-secondary);
      font-weight: 500;
      min-width: 80px;
    }

    .value {
      color: var(--text-primary);
    }
  }
}

// Items List
.items-list {
  .item-row {
    display: flex;
    gap: 1rem;
    margin-bottom: 1.5rem;
    padding: 1.5rem;
    background: var(--bg-secondary);
    border-radius: 8px;

    .item-number {
      width: 40px;
      height: 40px;
      background: var(--color-primary);
      color: white;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 600;
      flex-shrink: 0;
    }

    .item-form {
      flex: 1;
    }

    .item-actions {
      display: flex;
      align-items: flex-start;

      .remove-btn {
        width: 40px;
        height: 40px;
        border: 1px solid var(--border-color);
        background: var(--bg-primary);
        color: var(--color-error);
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.2s;

        &:hover {
          background: var(--color-error);
          color: white;
        }
      }
    }
  }

  .empty-items {
    text-align: center;
    padding: 3rem;
    color: var(--text-secondary);

    i {
      font-size: 3rem;
      opacity: 0.3;
      margin-bottom: 1rem;
    }

    p {
      margin: 0 0 1rem 0;
    }
  }
}

.total-display {
  padding: 0.75rem;
  background: var(--bg-primary);
  border: 1px solid var(--border-color);
  border-radius: 4px;
  color: var(--color-success);
  font-weight: 600;
  text-align: right;
}

.totals-section {
  margin-top: 2rem;
  padding-top: 2rem;
  border-top: 2px solid var(--border-color);

  .total-row {
    display: flex;
    justify-content: space-between;
    padding: 0.75rem;
    margin-bottom: 0.5rem;

    .label {
      color: var(--text-secondary);
      font-size: 1rem;
    }

    .value {
      color: var(--text-primary);
      font-weight: 600;
      font-size: 1.1rem;

      &.discount {
        color: var(--color-error);
      }
    }

    &.grand-total {
      background: var(--bg-secondary);
      border-radius: 4px;
      padding: 1rem;
      margin-top: 1rem;

      .label {
        font-size: 1.2rem;
        font-weight: 600;
        color: var(--text-primary);
      }

      .value {
        font-size: 1.5rem;
        color: var(--color-success);
      }
    }
  }
}

// Sidebar
.status-info {
  text-align: center;
  margin-bottom: 1rem;

  .status-badge-large {
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 600;

    &.draft {
      background: rgba(107, 114, 128, 0.2);
      color: #6b7280;
    }

    &.sent {
      background: rgba(59, 130, 246, 0.2);
      color: var(--color-info);
    }

    &.viewed {
      background: rgba(245, 158, 11, 0.2);
      color: var(--color-warning);
    }

    &.approved {
      background: rgba(16, 185, 129, 0.2);
      color: var(--color-success);
    }

    &.rejected {
      background: rgba(239, 68, 68, 0.2);
      color: var(--color-error);
    }
  }
}

.info-text {
  padding: 0.75rem;
  background: var(--bg-secondary);
  border-radius: 4px;
  color: var(--text-secondary);
  font-size: 0.9rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 0.5rem;

  &:last-child {
    margin-bottom: 0;
  }
}

.validity-days {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0.5rem;

  .days-btn {
    padding: 0.5rem;
    border: 1px solid var(--border-color);
    background: var(--bg-primary);
    color: var(--text-primary);
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.2s;

    &:hover {
      background: var(--color-primary);
      color: white;
      border-color: var(--color-primary);
    }
  }
}

.color-picker {
  display: flex;
  gap: 0.5rem;
  align-items: center;

  input[type="color"] {
    width: 50px;
    height: 45px;
    border: 1px solid var(--border-color);
    border-radius: 4px;
    cursor: pointer;
  }
}

.summary-card {
  .summary-item {
    display: flex;
    justify-content: space-between;
    padding: 0.75rem;
    background: var(--bg-secondary);
    border-radius: 4px;
    margin-bottom: 0.5rem;

    &:last-child {
      margin-bottom: 0;
    }

    .label {
      color: var(--text-secondary);
    }

    .value {
      color: var(--text-primary);
      font-weight: 500;
    }

    &.highlight {
      background: var(--color-primary);

      .label,
      .value {
        color: white;
        font-weight: 600;
      }
    }
  }
}

.mb-2 {
  margin-bottom: 0.5rem;
}

@media (max-width: 1024px) {
  .form-layout {
    grid-template-columns: 1fr;
  }

  .form-row {
    grid-template-columns: 1fr;

    .flex-3 {
      grid-column: span 1;
    }
  }
}
</style>
