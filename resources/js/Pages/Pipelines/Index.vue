<template>
    <MainLayout title="Pipelines">
        <template #breadcrumbs>
            <Breadcrumbs :items="breadcrumbs" />
        </template>

        <div class="page-container">
            <!-- Header -->
            <div class="page-header">
                <div>
                    <h1 class="page-title">Pipelines de Vendas</h1>
                    <p class="page-subtitle">Gerencie seus pipelines e estágios de vendas</p>
                </div>
                <div class="page-header__actions">
                    <Button variant="success" icon="fa fa-plus" @click="createPipeline">
                        Novo Pipeline
                    </Button>
                </div>
            </div>

            <!-- Stats -->
            <div class="stats-grid">
                <StatCard title="Total de Pipelines" :value="pipelines?.length ?? 0" icon="fa fa-layer-group" color="blue" />
                <StatCard title="Pipelines Ativos" :value="activePipelines" icon="fa fa-check-circle" color="green" />
                <StatCard title="Total de Estágios" :value="totalStages" icon="fa fa-list" color="purple" />
                <StatCard title="Leads em Pipelines" :value="totalLeads" icon="fa fa-users" color="orange" />
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
                            <button @click="toggleActive(pipeline)" class="btn-icon"
                                :title="pipeline.is_active ? 'Desativar' : 'Ativar'">
                                <i :class="pipeline.is_active ? 'fa fa-toggle-on' : 'fa fa-toggle-off'"></i>
                            </button>
                            <button v-if="!pipeline.is_default" @click="setDefault(pipeline)" class="btn-icon"
                                title="Definir como padrão">
                                <i class="fa fa-star"></i>
                            </button>
                            <button @click="deletePipeline(pipeline)" class="btn-icon btn-danger" title="Excluir"
                                :disabled="pipeline.is_default">
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
                    </div>

                    <!-- Stages -->
                    <div class="stages-container">
                        <div class="stages-header">
                            <h4>Estágios</h4>
                            <button @click="createStage(pipeline)" class="btn-add-stage">
                                <i class="fa fa-plus"></i> Adicionar Estágio
                            </button>
                        </div>

                        <VueDraggable
                            v-if="pipeline.stages && pipeline.stages.length > 0"
                            v-model="pipeline.stages"
                            @end="updateStagesOrder(pipeline)"
                            class="stages-list"
                            item-key="id"
                            handle=".drag-handle"
                            tag="div"
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
                                            <span class="stage-probability">{{ stage.probability }}% prob.</span>
                                        </div>
                                    </div>
                                    <div class="stage-color" :style="{ backgroundColor: stage.color }"></div>
                                    <div class="stage-actions">
                                        <button @click="editStage(stage)" class="btn-icon-sm" title="Editar">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                        <button @click="deleteStage(stage)" class="btn-icon-sm btn-danger"
                                            title="Excluir">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </VueDraggable>

                        <div v-else class="stages-empty">
                            <i class="fa fa-inbox"></i>
                            <p>Nenhum estágio criado ainda</p>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="!pipelines || pipelines.length === 0" class="empty-state">
                    <i class="fa fa-layer-group"></i>
                    <h3>Nenhum pipeline criado</h3>
                    <p>Crie seu primeiro pipeline de vendas para começar</p>
                    <Button variant="success" icon="fa fa-plus" @click="createPipeline">
                        Criar Primeiro Pipeline
                    </Button>
                </div>
            </div>

            <!-- Modal de confirmação de exclusão Pipeline -->
            <Modal v-model:visible="showDeletePipelineModal" title="Confirmar Exclusão" @confirm="confirmDeletePipeline">
                <p>Tem certeza que deseja excluir o pipeline <strong>{{ pipelineToDelete?.name }}</strong>?</p>
                <p class="text-danger">⚠️ Todos os leads deste pipeline serão movidos para o pipeline padrão.</p>
                <p class="text-muted">Esta ação não pode ser desfeita.</p>
            </Modal>

            <!-- Modal de confirmação de exclusão Stage -->
            <Modal v-model:visible="showDeleteStageModal" title="Confirmar Exclusão" @confirm="confirmDeleteStage">
                <p>Tem certeza que deseja excluir o estágio <strong>{{ stageToDelete?.name }}</strong>?</p>
                <p class="text-danger">⚠️ Todos os leads deste estágio serão movidos para o primeiro estágio.</p>
                <p class="text-muted">Esta ação não pode ser desfeita.</p>
            </Modal>

            <!-- Modal de Pipeline Form -->
            <Modal v-model:visible="showPipelineModal" :title="editingPipeline ? 'Editar Pipeline' : 'Novo Pipeline'"
                size="large" @confirm="savePipeline">
                <div class="modal-form">
                    <div class="form-group">
                        <label class="required">Nome do Pipeline</label>
                        <Input v-model="pipelineForm.name" placeholder="Ex: Vendas B2B, Vendas Consultoria..." />
                    </div>

                    <div class="form-group">
                        <label>Descrição</label>
                        <textarea v-model="pipelineForm.description" class="form-textarea"
                            placeholder="Descreva o propósito deste pipeline..." rows="3"></textarea>
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
            <Modal v-model:visible="showStageModal" :title="editingStage ? 'Editar Estágio' : 'Novo Estágio'"
                @confirm="saveStage">
                <div class="modal-form">
                    <div class="form-group">
                        <label class="required">Nome do Estágio</label>
                        <Input v-model="stageForm.name" placeholder="Ex: Prospecção, Proposta, Fechamento..." />
                    </div>

                    <div class="form-group">
                        <label class="required">Probabilidade de Conversão (%)</label>
                        <Input v-model="stageForm.probability" type="number" min="0" max="100" placeholder="0-100" />
                        <small class="form-hint">Percentual de chance do lead fechar neste estágio</small>
                    </div>

                    <div class="form-group">
                        <label class="required">Cor</label>
                        <div class="color-picker">
                            <input v-model="stageForm.color" type="color" class="color-input" />
                            <Input v-model="stageForm.color" placeholder="#000000" />
                        </div>
                    </div>
                </div>
            </Modal>
        </div>
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
import VueDraggable from 'vuedraggable';

const props = defineProps({
    pipelines: {
        type: Array,
        default: () => []
    },
});

const breadcrumbs = [
    { name: 'Dashboard', href: '/dashboard' },
    { name: 'Pipelines' }
];

const activePipelines = computed(() =>
    props.pipelines?.filter(p => p.is_active).length ?? 0
);

const totalStages = computed(() =>
    props.pipelines?.reduce((sum, p) => sum + (p.stages?.length || 0), 0) ?? 0
);

const totalLeads = computed(() =>
    props.pipelines?.reduce((sum, p) => sum + (p.leads_count || 0), 0) ?? 0
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
</script>

<!-- Styles moved to resources/scss/_pipelines.scss -->
