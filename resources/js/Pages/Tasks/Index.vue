<template>
    <MainLayout title="Tarefas">
        <template #breadcrumbs>
            <Breadcrumbs :items="breadcrumbs" />
        </template>

        <div class="page-container">
            <!-- Header -->
            <div class="page-header">
                <div>
                    <h1 class="page-title">TAREFAS</h1>
                    <p class="page-subtitle">Gerencie suas tarefas e acompanhe o progresso</p>
                </div>
                <div class="page-header__actions">
                    <Button variant="secondary" icon="fa-download" @click="showComingSoon('Exportar Tarefas')">
                        Exportar
                    </Button>
                    <Button variant="success" icon="fa-plus" @click="createTask">
                        Nova Tarefa
                    </Button>
                </div>
            </div>

            <!-- Stats -->
            <div class="stats-grid">
                <StatCard title="Total de Tarefas" :value="stats.total" icon="fa fa-tasks" color="blue"
                    @click="showComingSoon('Detalhes de Tarefas')" />
                <StatCard title="Pendentes" :value="stats.pending" icon="fa fa-clock" color="orange"
                    @click="showComingSoon('Filtrar Pendentes')" />
                <StatCard title="Em Andamento" :value="stats.in_progress" icon="fa fa-spinner" color="purple"
                    @click="showComingSoon('Filtrar Em Andamento')" />
                <StatCard title="Concluídas" :value="stats.completed" icon="fa fa-check-circle" color="green"
                    @click="showComingSoon('Filtrar Concluídas')" />
            </div>

            <!-- Filters -->
            <div class="filters-card">
                <div class="filters-header">
                    <div class="filters-title">
                        <i class="fa fa-filter"></i>
                        <span>FILTROS</span>
                    </div>
                </div>

                <div class="filters-grid">
                    <Input v-model="filters.search" placeholder="Buscar tarefas..." icon="fa-search"
                        @input="debouncedSearch" />

                    <Select v-model="filters.status" label="Status" :options="statusOptions" placeholder="Todos"
                        @update:modelValue="loadTasks" />

                    <Select v-model="filters.priority" label="Prioridade" :options="priorityOptions" placeholder="Todas"
                        @update:modelValue="loadTasks" />

                    <Select v-model="filters.assigned_to" label="Responsável" :options="userOptions" placeholder="Todos"
                        @update:modelValue="loadTasks" />

                    <button class="btn btn--secondary" @click="clearFilters" title="Limpar todos os filtros">
                        <i class="fas fa-times"></i>
                        Limpar
                    </button>
                </div>

                <div v-if="hasActiveFilters" class="active-filters">
                    <span class="active-filters__label">Filtros ativos:</span>
                    <span v-if="filters.search" class="filter-tag">
                        <i class="fa fa-search"></i> "{{ filters.search }}"
                        <button @click="filters.search = ''; loadTasks()">×</button>
                    </span>
                    <span v-if="filters.status" class="filter-tag">
                        Status: {{ getStatusLabel(filters.status) }}
                        <button @click="filters.status = ''; loadTasks()">×</button>
                    </span>
                    <span v-if="filters.priority" class="filter-tag">
                        Prioridade: {{ getPriorityLabel(filters.priority) }}
                        <button @click="filters.priority = ''; loadTasks()">×</button>
                    </span>
                    <span v-if="filters.assigned_to" class="filter-tag">
                        Responsável: {{ getUserName(filters.assigned_to) }}
                        <button @click="filters.assigned_to = ''; loadTasks()">×</button>
                    </span>
                </div>
            </div>

            <!-- View Toggle -->
            <div class="view-controls">
                <div class="view-toggle">
                    <button :class="['toggle-btn', { active: viewMode === 'grid' }]" @click="viewMode = 'grid'"
                        title="Visualização em Grade">
                        <i class="fa fa-th-large"></i>
                    </button>
                    <button :class="['toggle-btn', { active: viewMode === 'calendar' }]"
                        @click="showComingSoon('Visualização em Calendário')" title="Visualização em Calendário">
                        <i class="fa fa-calendar-alt"></i>
                    </button>
                    <button :class="['toggle-btn', { active: viewMode === 'list' }]"
                        @click="showComingSoon('Visualização em Lista')" title="Visualização em Lista">
                        <i class="fa fa-list"></i>
                    </button>
                </div>
            </div>

            <!-- Tasks Grid -->
            <div class="tasks-container">
                <div v-if="loading" class="loading">
                    <i class="fa fa-spinner fa-spin"></i> Carregando...
                </div>

                <div v-else-if="tasks.data.length === 0" class="empty-state">
                    <div class="empty-state__icon">
                        <i class="fa fa-tasks"></i>
                    </div>
                    <h2 class="empty-state__title">NENHUMA TAREFA ENCONTRADA</h2>
                    <p class="empty-state__subtitle">
                        {{ hasActiveFilters ?
                        'Nenhuma tarefa corresponde aos filtros aplicados' :
                        'Crie sua primeira tarefa para começar a organizar seu trabalho' }}
                    </p>

                    <div class="empty-state__actions">
                        <Button v-if="!hasActiveFilters" variant="success" icon="fa-plus" size="lg" @click="createTask">
                            Criar Primeira Tarefa
                        </Button>
                        <Button v-else variant="secondary" icon="fa-times" @click="clearFilters">
                            Limpar Filtros
                        </Button>
                    </div>

                    <div v-if="!hasActiveFilters" class="empty-state__suggestions">
                        <p class="suggestions-title">Dicas rápidas:</p>
                        <ul class="suggestions-list">
                            <li><i class="fa fa-lightbulb"></i> Use prioridades para organizar tarefas importantes</li>
                            <li><i class="fa fa-calendar"></i> Defina datas de vencimento para não perder prazos</li>
                            <li><i class="fa fa-user"></i> Atribua tarefas a membros da equipe</li>
                            <li><i class="fa fa-link"></i> Vincule tarefas a leads para melhor contexto</li>
                        </ul>
                    </div>
                </div>

                <div v-else class="tasks-grid">
                    <div v-for="task in tasks.data" :key="task.id" class="task-card">
                        <div class="task-header">
                            <div class="task-status-indicator" :class="'status-' + task.status"></div>
                            <div class="task-priority" :class="'priority-' + task.priority">
                                <i class="fa fa-flag"></i>
                                {{ getPriorityLabel(task.priority) }}
                            </div>
                            <div class="task-actions">
                                <button @click="toggleComplete(task)" class="btn-icon"
                                    :title="task.status === 'completed' ? 'Reabrir' : 'Concluir'">
                                    <i :class="task.status === 'completed' ? 'fa fa-undo' : 'fa fa-check'"></i>
                                </button>
                                <button @click="editTask(task)" class="btn-icon" title="Editar">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button @click="deleteTask(task)" class="btn-icon btn-danger" title="Excluir">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                        </div>

                        <div class="task-content">
                            <h3 class="task-title" :class="{ 'completed': task.status === 'completed' }">
                                {{ task.title }}
                            </h3>

                            <p v-if="task.description" class="task-description">
                                {{ task.description }}
                            </p>

                            <div class="task-meta">
                                <div v-if="task.due_date" class="meta-item"
                                    :class="{ 'overdue': isOverdue(task.due_date) && task.status !== 'completed' }">
                                    <i class="fa fa-calendar"></i>
                                    <span>{{ formatDate(task.due_date) }}</span>
                                </div>

                                <div v-if="task.assigned_user" class="meta-item">
                                    <i class="fa fa-user"></i>
                                    <span>{{ task.assigned_user.name }}</span>
                                </div>

                                <div v-if="task.lead" class="meta-item">
                                    <i class="fa fa-user-tag"></i>
                                    <a href="#" @click.prevent="showComingSoon('Visualização de Lead')">
                                        {{ task.lead.name }}
                                    </a>
                                </div>
                            </div>

                            <div class="task-footer">
                                <span :class="['status-badge', 'badge-' + task.status]">
                                    {{ getStatusLabel(task.status) }}
                                </span>

                                <span v-if="task.completed_at" class="completed-date">
                                    <i class="fa fa-check"></i>
                                    Concluída em {{ formatDate(task.completed_at) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <Pagination v-if="tasks.data.length > 0" :from="tasks.from" :to="tasks.to" :total="tasks.total"
                    :current-page="tasks.current_page" :last-page="tasks.last_page" @page-change="changePage" />
            </div>

            <!-- Modal de criação/edição de tarefa -->
            <Modal v-model:visible="showTaskModal" :title="editingTask ? 'Editar Tarefa' : 'Nova Tarefa'" size="large"
                @confirm="saveTask">
                <div class="modal-form">
                    <div class="form-group">
                        <label class="required">Título</label>
                        <Input v-model="taskForm.title" placeholder="Digite o título da tarefa" />
                    </div>

                    <div class="form-group">
                        <label>Descrição</label>
                        <textarea v-model="taskForm.description" class="form-textarea"
                            placeholder="Descreva os detalhes da tarefa..." rows="4"></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="required">Status</label>
                            <select v-model="taskForm.status" class="form-select">
                                <option value="pending">Pendente</option>
                                <option value="in_progress">Em Andamento</option>
                                <option value="completed">Concluída</option>
                                <option value="cancelled">Cancelada</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="required">Prioridade</label>
                            <select v-model="taskForm.priority" class="form-select">
                                <option value="low">Baixa</option>
                                <option value="medium">Média</option>
                                <option value="high">Alta</option>
                                <option value="urgent">Urgente</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Data de Vencimento</label>
                            <Input v-model="taskForm.due_date" type="datetime-local" />
                        </div>

                        <div class="form-group">
                            <label>Responsável</label>
                            <select v-model="taskForm.assigned_to" class="form-select">
                                <option value="">Nenhum</option>
                                <option v-for="user in users" :key="user.id" :value="user.id">
                                    {{ user.name }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Lead Relacionado</label>
                        <select v-model="taskForm.lead_id" class="form-select">
                            <option value="">Nenhum</option>
                            <option v-for="lead in leads" :key="lead.id" :value="lead.id">
                                {{ lead.name }}
                            </option>
                        </select>
                    </div>
                </div>
            </Modal>
        </div>
    </MainLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { useAlert } from '@/composables/useAlert';
import MainLayout from '@/Layouts/MainLayout.vue';
import Button from '@/Components/Button.vue';
import Input from '@/Components/Input.vue';
import Select from '@/Components/Select.vue';
import StatCard from '@/Components/StatCard.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import Pagination from '@/Components/Pagination.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    tasks: Object,
    leads: Array,
    users: Array,
    stats: Object,
});

const breadcrumbs = [
    { name: 'Dashboard', href: '/dashboard' },
    { name: 'Tarefas' }
];

const alert = useAlert();
const loading = ref(false);
const viewMode = ref('grid');
const filters = ref({
    search: '',
    status: '',
    priority: '',
    assigned_to: '',
});

const statusOptions = computed(() => [
    { value: '', label: 'Todos' },
    { value: 'pending', label: 'Pendente' },
    { value: 'in_progress', label: 'Em Andamento' },
    { value: 'completed', label: 'Concluída' },
    { value: 'cancelled', label: 'Cancelada' },
]);

const priorityOptions = computed(() => [
    { value: '', label: 'Todas' },
    { value: 'low', label: 'Baixa' },
    { value: 'medium', label: 'Média' },
    { value: 'high', label: 'Alta' },
    { value: 'urgent', label: 'Urgente' },
]);

const userOptions = computed(() => [
    { value: '', label: 'Todos' },
    ...props.users.map(user => ({ value: user.id, label: user.name }))
]);

const hasActiveFilters = computed(() => {
    return filters.value.search ||
        filters.value.status ||
        filters.value.priority ||
        filters.value.assigned_to;
});

const getUserName = (userId) => {
    const user = props.users.find(u => u.id === userId);
    return user ? user.name : '';
};

const showTaskModal = ref(false);
const editingTask = ref(null);

const taskForm = ref({
    title: '',
    description: '',
    status: 'pending',
    priority: 'medium',
    due_date: '',
    assigned_to: '',
    lead_id: '',
});

let searchTimeout = null;
const debouncedSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        loadTasks();
    }, 500);
};

const loadTasks = () => {
    loading.value = true;
    router.get('/tasks', filters.value, {
        preserveState: true,
        preserveScroll: true,
        onFinish: () => loading.value = false,
    });
};

const clearFilters = () => {
    filters.value = {
        search: '',
        status: '',
        priority: '',
        assigned_to: '',
    };
    loadTasks();
};

const changePage = (page) => {
    if (page === '...') return;
    router.get(`/tasks?page=${page}`, filters.value, {
        preserveState: true,
    });
};

const resetTaskForm = () => {
    taskForm.value = {
        title: '',
        description: '',
        status: 'pending',
        priority: 'medium',
        due_date: '',
        assigned_to: '',
        lead_id: '',
    };
    editingTask.value = null;
};

const createTask = () => {
    resetTaskForm();
    showTaskModal.value = true;
};

const editTask = (task) => {
    editingTask.value = task;
    taskForm.value = {
        title: task.title,
        description: task.description || '',
        status: task.status,
        priority: task.priority,
        due_date: task.due_date ? new Date(task.due_date).toISOString().slice(0, 16) : '',
        assigned_to: task.assigned_to || '',
        lead_id: task.lead_id || '',
    };
    showTaskModal.value = true;
};

const saveTask = () => {
    if (!taskForm.value.title) {
        alert.error('Erro', 'O título da tarefa é obrigatório!');
        return;
    }

    if (editingTask.value) {
        router.put(`/tasks/${editingTask.value.id}`, taskForm.value, {
            onSuccess: () => {
                showTaskModal.value = false;
                resetTaskForm();
                alert.toast('Tarefa atualizada!', 'success', 2000);
            },
            onError: () => {
                alert.error('Erro', 'Não foi possível atualizar a tarefa.');
            },
        });
    } else {
        router.post('/tasks', taskForm.value, {
            onSuccess: () => {
                showTaskModal.value = false;
                resetTaskForm();
                alert.toast('Tarefa criada!', 'success', 2000);
            },
            onError: () => {
                alert.error('Erro', 'Não foi possível criar a tarefa.');
            },
        });
    }
};

const toggleComplete = (task) => {
    const newStatus = task.status === 'completed' ? 'pending' : 'completed';
    router.patch(`/tasks/${task.id}`, { status: newStatus }, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            alert.toast(
                newStatus === 'completed' ? 'Tarefa Concluída!' : 'Tarefa Reaberta!',
                'success',
                2000
            );
        },
    });
};

const deleteTask = async (task) => {
    const result = await alert.confirmDelete(
        'Excluir tarefa?',
        `Tem certeza que deseja excluir a tarefa "${task.title}"? Esta ação não pode ser desfeita.`
    );

    if (!result.isConfirmed) return;

    router.delete(`/tasks/${task.id}`, {
        onSuccess: () => {
            alert.toast('Tarefa excluída!', 'success', 2000);
        },
    });
};

const getStatusLabel = (status) => {
    const labels = {
        pending: 'Pendente',
        in_progress: 'Em Andamento',
        completed: 'Concluída',
        cancelled: 'Cancelada',
    };
    return labels[status] || status;
};

const getPriorityLabel = (priority) => {
    const labels = {
        low: 'Baixa',
        medium: 'Média',
        high: 'Alta',
        urgent: 'Urgente',
    };
    return labels[priority] || priority;
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    });
};

const isOverdue = (dueDate) => {
    return new Date(dueDate) < new Date();
};

const showComingSoon = (feature) => {
    alert.info(
        'Em Desenvolvimento',
        `A funcionalidade "${feature}" está em desenvolvimento e estará disponível em breve!`
    );
};
</script>
