<template>
  <MainLayout>
    <div class="tasks-index">
      <!-- Header -->
      <div class="page-header">
        <div>
          <h1 class="page-title">Tarefas</h1>
          <Breadcrumbs :items="breadcrumbs" />
        </div>
        <Button
          label="Nova Tarefa"
          icon="fa fa-plus"
          @click="createTask"
          severity="success"
        />
      </div>

      <!-- Filters -->
      <div class="filters-card">
        <div class="filters-grid">
          <div class="filter-item">
            <label>Buscar</label>
            <Input
              v-model="filters.search"
              placeholder="Buscar tarefas..."
              icon="fa fa-search"
              @input="debouncedSearch"
            />
          </div>

          <div class="filter-item">
            <label>Status</label>
            <select v-model="filters.status" @change="loadTasks" class="form-select">
              <option value="">Todos</option>
              <option value="pending">Pendente</option>
              <option value="in_progress">Em Andamento</option>
              <option value="completed">Concluída</option>
              <option value="cancelled">Cancelada</option>
            </select>
          </div>

          <div class="filter-item">
            <label>Prioridade</label>
            <select v-model="filters.priority" @change="loadTasks" class="form-select">
              <option value="">Todas</option>
              <option value="low">Baixa</option>
              <option value="medium">Média</option>
              <option value="high">Alta</option>
              <option value="urgent">Urgente</option>
            </select>
          </div>

          <div class="filter-item">
            <label>Responsável</label>
            <select v-model="filters.assigned_to" @change="loadTasks" class="form-select">
              <option value="">Todos</option>
              <option v-for="user in users" :key="user.id" :value="user.id">
                {{ user.name }}
              </option>
            </select>
          </div>
        </div>

        <div class="filters-actions">
          <Button
            label="Limpar Filtros"
            @click="clearFilters"
            severity="secondary"
            size="small"
            outlined
          />
        </div>
      </div>

      <!-- Stats -->
      <div class="stats-grid">
        <StatCard
          title="Total de Tarefas"
          :value="stats.total"
          icon="fa fa-tasks"
          color="blue"
        />
        <StatCard
          title="Pendentes"
          :value="stats.pending"
          icon="fa fa-clock"
          color="orange"
        />
        <StatCard
          title="Em Andamento"
          :value="stats.in_progress"
          icon="fa fa-spinner"
          color="purple"
        />
        <StatCard
          title="Concluídas"
          :value="stats.completed"
          icon="fa fa-check-circle"
          color="green"
        />
      </div>

      <!-- Tasks Grid -->
      <div class="tasks-container">
        <div v-if="loading" class="loading">
          <i class="fa fa-spinner fa-spin"></i> Carregando...
        </div>

        <div v-else-if="tasks.data.length === 0" class="empty-state">
          <i class="fa fa-inbox"></i>
          <h3>Nenhuma tarefa encontrada</h3>
          <p>Crie sua primeira tarefa para começar</p>
          <Button
            label="Criar Primeira Tarefa"
            icon="fa fa-plus"
            @click="createTask"
            severity="success"
          />
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
                <button @click="toggleComplete(task)" class="btn-icon" :title="task.status === 'completed' ? 'Reabrir' : 'Concluir'">
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
                <div v-if="task.due_date" class="meta-item" :class="{ 'overdue': isOverdue(task.due_date) && task.status !== 'completed' }">
                  <i class="fa fa-calendar"></i>
                  <span>{{ formatDate(task.due_date) }}</span>
                </div>

                <div v-if="task.assigned_user" class="meta-item">
                  <i class="fa fa-user"></i>
                  <span>{{ task.assigned_user.name }}</span>
                </div>

                <div v-if="task.lead" class="meta-item">
                  <i class="fa fa-user-tag"></i>
                  <router-link :to="`/leads/${task.lead.id}`">
                    {{ task.lead.name }}
                  </router-link>
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
        <div v-if="tasks.data.length > 0" class="pagination">
          <div class="pagination-info">
            Mostrando {{ tasks.from }} a {{ tasks.to }} de {{ tasks.total }} registros
          </div>
          <div class="pagination-buttons">
            <button
              @click="changePage(page)"
              v-for="page in paginationPages"
              :key="page"
              :class="['pagination-btn', { active: page === tasks.current_page }]"
              :disabled="page === '...'"
            >
              {{ page }}
            </button>
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
      <p>Tem certeza que deseja excluir a tarefa <strong>{{ taskToDelete?.title }}</strong>?</p>
      <p class="text-muted">Esta ação não pode ser desfeita.</p>
    </Modal>

    <!-- Modal de criação/edição de tarefa -->
    <Modal
      v-model:visible="showTaskModal"
      :title="editingTask ? 'Editar Tarefa' : 'Nova Tarefa'"
      size="large"
      @confirm="saveTask"
    >
      <div class="modal-form">
        <div class="form-group">
          <label class="required">Título</label>
          <Input
            v-model="taskForm.title"
            placeholder="Digite o título da tarefa"
          />
        </div>

        <div class="form-group">
          <label>Descrição</label>
          <textarea
            v-model="taskForm.description"
            class="form-textarea"
            placeholder="Descreva os detalhes da tarefa..."
            rows="4"
          ></textarea>
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
            <Input
              v-model="taskForm.due_date"
              type="datetime-local"
            />
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

const props = defineProps({
  tasks: Object,
  leads: Array,
  users: Array,
  stats: Object,
});

const breadcrumbs = [
  { label: 'Dashboard', to: '/dashboard' },
  { label: 'Tarefas', active: true }
];

const loading = ref(false);
const filters = ref({
  search: '',
  status: '',
  priority: '',
  assigned_to: '',
});

const showDeleteModal = ref(false);
const taskToDelete = ref(null);
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

const paginationPages = computed(() => {
  const pages = [];
  const current = props.tasks.current_page;
  const last = props.tasks.last_page;

  if (last <= 7) {
    for (let i = 1; i <= last; i++) {
      pages.push(i);
    }
  } else {
    if (current <= 3) {
      pages.push(1, 2, 3, 4, '...', last);
    } else if (current >= last - 2) {
      pages.push(1, '...', last - 3, last - 2, last - 1, last);
    } else {
      pages.push(1, '...', current - 1, current, current + 1, '...', last);
    }
  }

  return pages;
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
  if (editingTask.value) {
    router.put(`/tasks/${editingTask.value.id}`, taskForm.value, {
      onSuccess: () => {
        showTaskModal.value = false;
        resetTaskForm();
      },
    });
  } else {
    router.post('/tasks', taskForm.value, {
      onSuccess: () => {
        showTaskModal.value = false;
        resetTaskForm();
      },
    });
  }
};

const toggleComplete = (task) => {
  const newStatus = task.status === 'completed' ? 'pending' : 'completed';
  router.patch(`/tasks/${task.id}`, { status: newStatus });
};

const deleteTask = (task) => {
  taskToDelete.value = task;
  showDeleteModal.value = true;
};

const confirmDelete = () => {
  router.delete(`/tasks/${taskToDelete.value.id}`, {
    onSuccess: () => {
      showDeleteModal.value = false;
      taskToDelete.value = null;
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
</script>

<style scoped lang="scss">
.tasks-index {
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

.filters-card {
  background: var(--surface-card);
  border-radius: var(--border-radius);
  padding: 1.5rem;
  margin-bottom: 2rem;
  box-shadow: var(--shadow-sm);
}

.filters-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1rem;
  margin-bottom: 1rem;
}

.filter-item {
  label {
    display: block;
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--text-secondary);
    margin-bottom: 0.5rem;
  }
}

.form-select {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid var(--border-color);
  border-radius: var(--border-radius);
  background: var(--surface-ground);
  color: var(--text-primary);
  font-size: 0.875rem;
  transition: border-color 0.2s;

  &:focus {
    outline: none;
    border-color: var(--primary-color);
  }
}

.filters-actions {
  display: flex;
  justify-content: flex-end;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.tasks-container {
  background: var(--surface-card);
  border-radius: var(--border-radius);
  padding: 2rem;
  box-shadow: var(--shadow-sm);
}

.loading,
.empty-state {
  padding: 4rem 2rem;
  text-align: center;

  i {
    font-size: 3rem;
    color: var(--text-secondary);
    margin-bottom: 1rem;
    display: block;
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

.tasks-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.task-card {
  background: var(--surface-ground);
  border-radius: var(--border-radius);
  border: 1px solid var(--border-color);
  overflow: hidden;
  transition: all 0.2s;

  &:hover {
    box-shadow: var(--shadow-md);
    transform: translateY(-2px);
  }
}

.task-header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem;
  background: var(--surface-card);
  border-bottom: 1px solid var(--border-color);
}

.task-status-indicator {
  width: 4px;
  height: 40px;
  border-radius: 2px;

  &.status-pending { background: #F59E0B; }
  &.status-in_progress { background: #8B5CF6; }
  &.status-completed { background: #10B981; }
  &.status-cancelled { background: #6B7280; }
}

.task-priority {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;

  &.priority-low { background: #E5E7EB; color: #6B7280; }
  &.priority-medium { background: #DBEAFE; color: #1E40AF; }
  &.priority-high { background: #FED7AA; color: #C2410C; }
  &.priority-urgent { background: #FEE2E2; color: #991B1B; }

  i {
    font-size: 0.7rem;
  }
}

.task-actions {
  margin-left: auto;
  display: flex;
  gap: 0.25rem;
}

.btn-icon {
  padding: 0.5rem;
  background: transparent;
  border: none;
  border-radius: var(--border-radius);
  color: var(--text-secondary);
  cursor: pointer;
  transition: all 0.2s;

  &:hover {
    background: var(--surface-ground);
    color: var(--primary-color);
  }

  &.btn-danger:hover {
    color: var(--red-500);
  }
}

.task-content {
  padding: 1rem;
}

.task-title {
  font-size: 1.125rem;
  font-weight: 600;
  color: var(--text-primary);
  margin-bottom: 0.5rem;

  &.completed {
    text-decoration: line-through;
    opacity: 0.6;
  }
}

.task-description {
  color: var(--text-secondary);
  font-size: 0.875rem;
  line-height: 1.5;
  margin-bottom: 1rem;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.task-meta {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  margin-bottom: 1rem;
}

.meta-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  color: var(--text-secondary);

  i {
    color: var(--primary-color);
    width: 16px;
  }

  &.overdue {
    color: var(--red-500);

    i {
      color: var(--red-500);
    }
  }

  a {
    color: var(--primary-color);
    text-decoration: none;

    &:hover {
      text-decoration: underline;
    }
  }
}

.task-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 0.75rem;
  border-top: 1px solid var(--border-color);
}

.status-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;

  &.badge-pending { background: #FEF3C7; color: #92400E; }
  &.badge-in_progress { background: #EDE9FE; color: #6D28D9; }
  &.badge-completed { background: #D1FAE5; color: #065F46; }
  &.badge-cancelled { background: #F3F4F6; color: #374151; }
}

.completed-date {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  font-size: 0.75rem;
  color: var(--text-secondary);

  i {
    color: var(--green-500);
  }
}

.pagination {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 1.5rem;
  border-top: 1px solid var(--border-color);
}

.pagination-info {
  color: var(--text-secondary);
  font-size: 0.875rem;
}

.pagination-buttons {
  display: flex;
  gap: 0.5rem;
}

.pagination-btn {
  padding: 0.5rem 0.75rem;
  background: var(--surface-ground);
  border: 1px solid var(--border-color);
  border-radius: var(--border-radius);
  color: var(--text-primary);
  cursor: pointer;
  transition: all 0.2s;

  &:hover:not(:disabled) {
    background: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
  }

  &.active {
    background: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
  }

  &:disabled {
    cursor: default;
    opacity: 0.5;
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

  .form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    margin-bottom: 1.5rem;
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
}

.text-muted {
  color: var(--text-secondary);
  font-size: 0.875rem;
}
</style>
