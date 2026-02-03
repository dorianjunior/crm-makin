<template>
  <MainLayout>
    <div class="pipelines-index">
      <!-- Header -->
      <div class="page-header">
        <div>
          <h1 class="page-title">Pipelines de Vendas</h1>
          <Breadcrumbs :items="breadcrumbs" />
        </div>
        <Button
          label="Novo Pipeline"
          icon="fa fa-plus"
          @click="createPipeline"
          severity="success"
        />
      </div>

      <!-- Stats -->
      <div class="stats-grid">
        <StatCard
          title="Total de Pipelines"
          :value="pipelines.length"
          icon="fa fa-layer-group"
          color="blue"
        />
        <StatCard
          title="Pipelines Ativos"
          :value="activePipelines"
          icon="fa fa-check-circle"
          color="green"
        />
        <StatCard
          title="Total de Estágios"
          :value="totalStages"
          icon="fa fa-list"
          color="purple"
        />
        <StatCard
          title="Leads em Pipelines"
          :value="totalLeads"
          icon="fa fa-users"
          color="orange"
        />
      </div>

      <!-- Pipelines List -->
      <div class="pipelines-grid">
        <div v-for="pipeline in pipelines" :key="pipeline.id" class="pipeline-card">
          <div class="pipeline-header">
            <div class="pipeline-info">
              <h3>{{ pipeline.name }}</h3>
              <p v-if="pipeline.description" class="pipeline-description">
                {{ pipeline.description }}
              </p>
              <div class="pipeline-meta">
                <span :class="['badge', pipeline.is_active ? 'badge-success' : 'badge-danger']">
                  {{ pipeline.is_active ? 'Ativo' : 'Inativo' }}
                </span>
                <span v-if="pipeline.is_default" class="badge badge-primary">
                  <i class="fa fa-star"></i> Padrão
                </span>
              </div>
            </div>
            <div class="pipeline-actions">
              <button @click="editPipeline(pipeline)" class="btn-icon" title="Editar">
                <i class="fa fa-edit"></i>
              </button>
              <button
                @click="toggleActive(pipeline)"
                class="btn-icon"
                :title="pipeline.is_active ? 'Desativar' : 'Ativar'"
              >
                <i :class="pipeline.is_active ? 'fa fa-toggle-on' : 'fa fa-toggle-off'"></i>
              </button>
              <button
                v-if="!pipeline.is_default"
                @click="setDefault(pipeline)"
                class="btn-icon"
                title="Definir como padrão"
              >
                <i class="fa fa-star"></i>
              </button>
              <button
                @click="deletePipeline(pipeline)"
                class="btn-icon btn-danger"
                title="Excluir"
                :disabled="pipeline.is_default"
              >
                <i class="fa fa-trash"></i>
              </button>
            </div>
          </div>

          <div class="pipeline-stats">
            <div class="stat-item">
              <i class="fa fa-list"></i>
              <span>{{ pipeline.stages_count }} estágios</span>
            </div>
            <div class="stat-item">
              <i class="fa fa-users"></i>
              <span>{{ pipeline.leads_count }} leads</span>
            </div>
            <div class="stat-item">
              <i class="fa fa-dollar-sign"></i>
              <span>{{ formatCurrency(pipeline.total_value) }}</span>
            </div>
          </div>

          <!-- Stages -->
          <div class="stages-container">
            <div class="stages-header">
              <h4>Estágios</h4>
              <button @click="createStage(pipeline)" class="btn-add-stage">
                <i class="fa fa-plus"></i> Adicionar Estágio
              </button>
            </div>

            <draggable
              v-if="pipeline.stages && pipeline.stages.length > 0"
              v-model="pipeline.stages"
              @end="updateStagesOrder(pipeline)"
              class="stages-list"
              item-key="id"
              handle=".drag-handle"
            >
              <template #item="{ element: stage }">
                <div class="stage-item">
                  <div class="stage-handle drag-handle">
                    <i class="fa fa-grip-vertical"></i>
                  </div>
                  <div class="stage-info">
                    <div class="stage-name">{{ stage.name }}</div>
                    <div class="stage-meta">
                      <span class="stage-leads">{{ stage.leads_count }} leads</span>
                      <span class="stage-value">{{ formatCurrency(stage.total_value) }}</span>
                      <span class="stage-probability">{{ stage.probability }}% prob.</span>
                    </div>
                  </div>
                  <div class="stage-color" :style="{ backgroundColor: stage.color }"></div>
                  <div class="stage-actions">
                    <button @click="editStage(stage)" class="btn-icon-sm" title="Editar">
                      <i class="fa fa-edit"></i>
                    </button>
                    <button @click="deleteStage(stage)" class="btn-icon-sm btn-danger" title="Excluir">
                      <i class="fa fa-trash"></i>
                    </button>
                  </div>
                </div>
              </template>
            </draggable>

            <div v-else class="stages-empty">
              <i class="fa fa-inbox"></i>
              <p>Nenhum estágio criado ainda</p>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="pipelines.length === 0" class="empty-state">
          <i class="fa fa-layer-group"></i>
          <h3>Nenhum pipeline criado</h3>
          <p>Crie seu primeiro pipeline de vendas para começar</p>
          <Button
            label="Criar Primeiro Pipeline"
            icon="fa fa-plus"
            @click="createPipeline"
            severity="success"
          />
        </div>
      </div>
    </div>

    <!-- Modal de confirmação de exclusão Pipeline -->
    <Modal
      v-model:visible="showDeletePipelineModal"
      title="Confirmar Exclusão"
      @confirm="confirmDeletePipeline"
    >
      <p>Tem certeza que deseja excluir o pipeline <strong>{{ pipelineToDelete?.name }}</strong>?</p>
      <p class="text-danger">⚠️ Todos os leads deste pipeline serão movidos para o pipeline padrão.</p>
      <p class="text-muted">Esta ação não pode ser desfeita.</p>
    </Modal>

    <!-- Modal de confirmação de exclusão Stage -->
    <Modal
      v-model:visible="showDeleteStageModal"
      title="Confirmar Exclusão"
      @confirm="confirmDeleteStage"
    >
      <p>Tem certeza que deseja excluir o estágio <strong>{{ stageToDelete?.name }}</strong>?</p>
      <p class="text-danger">⚠️ Todos os leads deste estágio serão movidos para o primeiro estágio.</p>
      <p class="text-muted">Esta ação não pode ser desfeita.</p>
    </Modal>

    <!-- Modal de Pipeline Form -->
    <Modal
      v-model:visible="showPipelineModal"
      :title="editingPipeline ? 'Editar Pipeline' : 'Novo Pipeline'"
      size="large"
      @confirm="savePipeline"
    >
      <div class="modal-form">
        <div class="form-group">
          <label class="required">Nome do Pipeline</label>
          <Input
            v-model="pipelineForm.name"
            placeholder="Ex: Vendas B2B, Vendas Consultoria..."
          />
        </div>

        <div class="form-group">
          <label>Descrição</label>
          <textarea
            v-model="pipelineForm.description"
            class="form-textarea"
            placeholder="Descreva o propósito deste pipeline..."
            rows="3"
          ></textarea>
        </div>

        <div class="form-group">
          <label class="checkbox-label">
            <input type="checkbox" v-model="pipelineForm.is_active" />
            <span>Pipeline ativo</span>
          </label>
        </div>

        <div class="form-group">
          <label class="checkbox-label">
            <input type="checkbox" v-model="pipelineForm.is_default" />
            <span>Definir como pipeline padrão</span>
          </label>
        </div>
      </div>
    </Modal>

    <!-- Modal de Stage Form -->
    <Modal
      v-model:visible="showStageModal"
      :title="editingStage ? 'Editar Estágio' : 'Novo Estágio'"
      @confirm="saveStage"
    >
      <div class="modal-form">
        <div class="form-group">
          <label class="required">Nome do Estágio</label>
          <Input
            v-model="stageForm.name"
            placeholder="Ex: Prospecção, Proposta, Fechamento..."
          />
        </div>

        <div class="form-group">
          <label class="required">Probabilidade de Conversão (%)</label>
          <Input
            v-model="stageForm.probability"
            type="number"
            min="0"
            max="100"
            placeholder="0-100"
          />
          <small class="form-hint">Percentual de chance do lead fechar neste estágio</small>
        </div>

        <div class="form-group">
          <label class="required">Cor</label>
          <div class="color-picker">
            <input
              v-model="stageForm.color"
              type="color"
              class="color-input"
            />
            <Input
              v-model="stageForm.color"
              placeholder="#000000"
            />
          </div>
        </div>
      </div>
    </Modal>
  </MainLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Button from '@/Components/Button.vue';
import Input from '@/Components/Input.vue';
import StatCard from '@/Components/StatCard.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import Modal from '@/Components/Modal.vue';
import draggable from 'vuedraggable';

const props = defineProps({
  pipelines: Array,
});

const breadcrumbs = [
  { label: 'Dashboard', to: '/dashboard' },
  { label: 'Pipelines', active: true }
];

const activePipelines = computed(() =>
  props.pipelines.filter(p => p.is_active).length
);

const totalStages = computed(() =>
  props.pipelines.reduce((sum, p) => sum + (p.stages?.length || 0), 0)
);

const totalLeads = computed(() =>
  props.pipelines.reduce((sum, p) => sum + (p.leads_count || 0), 0)
);

const showDeletePipelineModal = ref(false);
const pipelineToDelete = ref(null);
const showDeleteStageModal = ref(false);
const stageToDelete = ref(null);
const showPipelineModal = ref(false);
const showStageModal = ref(false);
const editingPipeline = ref(null);
const editingStage = ref(null);
const currentPipeline = ref(null);

const pipelineForm = ref({
  name: '',
  description: '',
  is_active: true,
  is_default: false,
});

const stageForm = ref({
  name: '',
  probability: 50,
  color: '#3B82F6',
});

const resetPipelineForm = () => {
  pipelineForm.value = {
    name: '',
    description: '',
    is_active: true,
    is_default: false,
  };
  editingPipeline.value = null;
};

const resetStageForm = () => {
  stageForm.value = {
    name: '',
    probability: 50,
    color: '#3B82F6',
  };
  editingStage.value = null;
  currentPipeline.value = null;
};

const createPipeline = () => {
  resetPipelineForm();
  showPipelineModal.value = true;
};

const editPipeline = (pipeline) => {
  editingPipeline.value = pipeline;
  pipelineForm.value = {
    name: pipeline.name,
    description: pipeline.description || '',
    is_active: pipeline.is_active,
    is_default: pipeline.is_default,
  };
  showPipelineModal.value = true;
};

const savePipeline = () => {
  if (editingPipeline.value) {
    router.put(`/pipelines/${editingPipeline.value.id}`, pipelineForm.value, {
      onSuccess: () => {
        showPipelineModal.value = false;
        resetPipelineForm();
      },
    });
  } else {
    router.post('/pipelines', pipelineForm.value, {
      onSuccess: () => {
        showPipelineModal.value = false;
        resetPipelineForm();
      },
    });
  }
};

const deletePipeline = (pipeline) => {
  pipelineToDelete.value = pipeline;
  showDeletePipelineModal.value = true;
};

const confirmDeletePipeline = () => {
  router.delete(`/pipelines/${pipelineToDelete.value.id}`, {
    onSuccess: () => {
      showDeletePipelineModal.value = false;
      pipelineToDelete.value = null;
    },
  });
};

const toggleActive = (pipeline) => {
  router.patch(`/pipelines/${pipeline.id}`, {
    is_active: !pipeline.is_active
  });
};

const setDefault = (pipeline) => {
  router.post(`/pipelines/${pipeline.id}/set-default`);
};

const createStage = (pipeline) => {
  currentPipeline.value = pipeline;
  resetStageForm();
  showStageModal.value = true;
};

const editStage = (stage) => {
  editingStage.value = stage;
  stageForm.value = {
    name: stage.name,
    probability: stage.probability,
    color: stage.color,
  };
  showStageModal.value = true;
};

const saveStage = () => {
  const data = {
    ...stageForm.value,
    pipeline_id: editingStage.value?.pipeline_id || currentPipeline.value.id,
  };

  if (editingStage.value) {
    router.put(`/stages/${editingStage.value.id}`, data, {
      onSuccess: () => {
        showStageModal.value = false;
        resetStageForm();
      },
    });
  } else {
    router.post('/stages', data, {
      onSuccess: () => {
        showStageModal.value = false;
        resetStageForm();
      },
    });
  }
};

const deleteStage = (stage) => {
  stageToDelete.value = stage;
  showDeleteStageModal.value = true;
};

const confirmDeleteStage = () => {
  router.delete(`/stages/${stageToDelete.value.id}`, {
    onSuccess: () => {
      showDeleteStageModal.value = false;
      stageToDelete.value = null;
    },
  });
};

const updateStagesOrder = (pipeline) => {
  const order = pipeline.stages.map((stage, index) => ({
    id: stage.id,
    order: index + 1,
  }));

  router.post(`/pipelines/${pipeline.id}/stages/reorder`, { stages: order });
};

const formatCurrency = (value) => {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL',
  }).format(value || 0);
};
</script>

<style scoped lang="scss">
.pipelines-index {
  padding: 2rem;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.page-title {
  font-size: 2rem;
  font-weight: 700;
  color: var(--text-primary);
  margin-bottom: 0.5rem;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.pipelines-grid {
  display: grid;
  gap: 2rem;
}

.pipeline-card {
  background: var(--surface-card);
  border-radius: var(--border-radius);
  padding: 1.5rem;
  box-shadow: var(--shadow-sm);
}

.pipeline-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1.5rem;
  padding-bottom: 1rem;
  border-bottom: 2px solid var(--border-color);
}

.pipeline-info {
  flex: 1;

  h3 {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 0.5rem;
  }
}

.pipeline-description {
  color: var(--text-secondary);
  margin-bottom: 0.75rem;
  font-size: 0.9rem;
}

.pipeline-meta {
  display: flex;
  gap: 0.5rem;
}

.badge {
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;

  &.badge-success {
    background: #c8e6c9;
    color: #2e7d32;
  }

  &.badge-danger {
    background: #ffcdd2;
    color: #c62828;
  }

  &.badge-primary {
    background: #e3f2fd;
    color: #1976d2;
  }
}

.pipeline-actions {
  display: flex;
  gap: 0.5rem;
}

.btn-icon,
.btn-icon-sm {
  padding: 0.5rem;
  background: transparent;
  border: none;
  border-radius: var(--border-radius);
  color: var(--text-secondary);
  cursor: pointer;
  transition: all 0.2s;

  &:hover:not(:disabled) {
    background: var(--surface-ground);
    color: var(--primary-color);
  }

  &.btn-danger:hover {
    color: var(--red-500);
  }

  &:disabled {
    opacity: 0.4;
    cursor: not-allowed;
  }
}

.btn-icon-sm {
  padding: 0.25rem 0.5rem;
  font-size: 0.875rem;
}

.pipeline-stats {
  display: flex;
  gap: 2rem;
  margin-bottom: 1.5rem;
}

.stat-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: var(--text-secondary);
  font-size: 0.9rem;

  i {
    color: var(--primary-color);
  }
}

.stages-container {
  background: var(--surface-ground);
  border-radius: var(--border-radius);
  padding: 1rem;
}

.stages-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;

  h4 {
    font-size: 1rem;
    font-weight: 600;
    color: var(--text-primary);
  }
}

.btn-add-stage {
  padding: 0.5rem 1rem;
  background: var(--primary-color);
  color: white;
  border: none;
  border-radius: var(--border-radius);
  font-size: 0.875rem;
  cursor: pointer;
  transition: background 0.2s;

  &:hover {
    opacity: 0.9;
  }

  i {
    margin-right: 0.5rem;
  }
}

.stages-list {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.stage-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.75rem;
  background: var(--surface-card);
  border-radius: var(--border-radius);
  transition: all 0.2s;

  &:hover {
    box-shadow: var(--shadow-sm);
  }
}

.stage-handle {
  cursor: grab;
  color: var(--text-secondary);
  padding: 0.25rem;

  &:active {
    cursor: grabbing;
  }
}

.stage-info {
  flex: 1;
}

.stage-name {
  font-weight: 600;
  color: var(--text-primary);
  margin-bottom: 0.25rem;
}

.stage-meta {
  display: flex;
  gap: 1rem;
  font-size: 0.8rem;
  color: var(--text-secondary);
}

.stage-color {
  width: 24px;
  height: 24px;
  border-radius: 4px;
  border: 2px solid var(--border-color);
}

.stage-actions {
  display: flex;
  gap: 0.25rem;
}

.stages-empty {
  padding: 2rem;
  text-align: center;
  color: var(--text-secondary);

  i {
    font-size: 2rem;
    margin-bottom: 0.5rem;
    display: block;
  }

  p {
    margin: 0;
  }
}

.empty-state {
  grid-column: 1 / -1;
  padding: 4rem 2rem;
  text-align: center;
  background: var(--surface-card);
  border-radius: var(--border-radius);

  i {
    font-size: 4rem;
    color: var(--text-secondary);
    margin-bottom: 1rem;
  }

  h3 {
    font-size: 1.5rem;
    color: var(--text-primary);
    margin-bottom: 0.5rem;
  }

  p {
    color: var(--text-secondary);
    margin-bottom: 1.5rem;
  }
}

.modal-form {
  .form-group {
    margin-bottom: 1.5rem;

    &:last-child {
      margin-bottom: 0;
    }

    label {
      display: block;
      font-size: 0.875rem;
      font-weight: 600;
      color: var(--text-secondary);
      margin-bottom: 0.5rem;

      &.required::after {
        content: '*';
        color: var(--red-500);
        margin-left: 0.25rem;
      }
    }
  }

  .form-textarea {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius);
    background: var(--surface-ground);
    color: var(--text-primary);
    font-size: 0.875rem;
    font-family: inherit;
    resize: vertical;

    &:focus {
      outline: none;
      border-color: var(--primary-color);
    }
  }

  .form-hint {
    display: block;
    font-size: 0.75rem;
    color: var(--text-secondary);
    margin-top: 0.25rem;
  }

  .checkbox-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;

    input[type="checkbox"] {
      width: 18px;
      height: 18px;
      cursor: pointer;
    }

    span {
      font-weight: 400;
    }
  }

  .color-picker {
    display: flex;
    gap: 1rem;
    align-items: center;

    .color-input {
      width: 60px;
      height: 40px;
      border: 1px solid var(--border-color);
      border-radius: var(--border-radius);
      cursor: pointer;
    }
  }
}

.text-danger {
  color: var(--red-500);
  font-weight: 500;
}

.text-muted {
  color: var(--text-secondary);
  font-size: 0.875rem;
}
</style>
