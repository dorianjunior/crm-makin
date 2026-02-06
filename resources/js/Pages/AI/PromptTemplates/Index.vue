<template>
  <MainLayout title="Templates de Prompt">
    <div class="page">
      <Breadcrumbs :items="breadcrumbs" />

      <div class="page-header">
        <div>
          <h1>Templates de Prompt</h1>
          <p class="subtitle">Gerencie templates reutilizáveis para prompts de IA</p>
        </div>

        <div class="actions">
          <Button variant="secondary" @click="openCreate">
            <i class="fa fa-plus"></i>
            Novo Template
          </Button>
        </div>
      </div>

      <div class="stats-row">
        <StatCard title="Total" :value="stats.total" icon="fa-file-alt" />
        <StatCard title="Ativos" :value="stats.active" icon="fa-check-circle" color="green" />
        <StatCard title="Categoria(s)" :value="stats.categories" icon="fa-tags" color="blue" />
      </div>

      <div class="filters-card">
        <div class="filter-row">
          <Input v-model="filters.q" placeholder="Pesquisar por nome ou conteúdo..." @input="fetch" />
          <select v-model="filters.category" class="form-select" @change="fetch">
            <option value="">Todas as categorias</option>
            <option v-for="c in categories" :key="c" :value="c">{{ c }}</option>
          </select>
          <select v-model="filters.status" class="form-select" @change="fetch">
            <option value="">Todos</option>
            <option value="active">Ativo</option>
            <option value="inactive">Inativo</option>
          </select>
        </div>
      </div>

      <div class="list-card">
        <table class="data-table">
          <thead>
            <tr>
              <th>Nome</th>
              <th>Categoria</th>
              <th>Variáveis</th>
              <th>Status</th>
              <th>Última Atualização</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="template in templates" :key="template.id">
              <td>
                <div class="name">{{ template.name }}</div>
                <div class="small text-muted">{{ truncate(template.content, 80) }}</div>
              </td>
              <td>{{ template.category || '-' }}</td>
              <td>
                <div class="vars">
                  <span v-for="v in parseVariables(template.content)" :key="v" class="chip">{{ v }}</span>
                </div>
              </td>
              <td>
                <span :class="['status-badge', template.active ? 'active' : 'inactive']">{{ template.active ? 'Ativo' : 'Inativo' }}</span>
              </td>
              <td>{{ formatDate(template.updated_at) }}</td>
              <td class="actions">
                <Button size="small" variant="secondary" @click="preview(template)"><i class="fa fa-eye"></i></Button>
                <Button size="small" variant="primary" @click="edit(template)"><i class="fa fa-edit"></i></Button>
                <Button size="small" variant="secondary" @click="duplicate(template)"><i class="fa fa-copy"></i></Button>
                <Button size="small" variant="danger" @click="confirmDelete(template)"><i class="fa fa-trash"></i></Button>
              </td>
            </tr>

            <tr v-if="!templates.length">
              <td colspan="6" class="empty">Nenhum template encontrado</td>
            </tr>
          </tbody>
        </table>
      </div>

      <Modal v-model:show="showForm" :title="formTitle" size="large">
        <template #body>
          <PromptForm :initial="editingTemplate" @saved="onSaved" />
        </template>
      </Modal>

      <Modal v-model:show="showPreview" title="Preview do Template" size="large">
        <template #body>
          <div class="preview-box">
            <h3>{{ previewTemplate.name }}</h3>
            <div class="preview-content" v-html="highlightVariables(previewTemplate.content)"></div>
          </div>
        </template>
      </Modal>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue';
import MainLayout from '@/Layouts/MainLayout.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import Button from '@/Components/Button.vue';
import Input from '@/Components/Input.vue';
import Modal from '@/Components/Modal.vue';
import StatCard from '@/Components/StatCard.vue';
import PromptForm from './Form.vue';
import Swal from 'sweetalert2';
import axios from 'axios';

const props = defineProps({ initial: Object });

const breadcrumbs = [
  { label: 'IA' },
  { label: 'Prompt Templates' }
];

const templates = ref([]);
const categories = ref(['Geral', 'Vendas', 'Suporte', 'Marketing']);
const filters = reactive({ q: '', category: '', status: '' });
const stats = reactive({ total: 0, active: 0, categories: categories.value.length });

const showForm = ref(false);
const showPreview = ref(false);
const editingTemplate = ref(null);
const previewTemplate = ref({});

const formTitle = computed(() => (editingTemplate.value ? 'Editar Template' : 'Novo Template'));

onMounted(() => fetch());

async function fetch() {
  try {
    const params = {};
    if (filters.q) params.q = filters.q;
    if (filters.category) params.category = filters.category;
    if (filters.status === 'active') params.is_active = 1;
    if (filters.status === 'inactive') params.is_active = 0;

    const res = await axios.get('/api/crm/ai/templates', { params });
    // API returns { data: [...] } or plain array
    templates.value = res.data.data ?? res.data;

    stats.total = templates.value.length;
    stats.active = templates.value.filter(t => t.is_active || t.active).length;
  } catch (err) {
    console.error(err);
    Swal.fire('Erro', 'Não foi possível carregar os templates', 'error');
  }
}

function truncate(text, length){
  if(!text) return '';
  return text.length > length ? text.substr(0, length) + '...' : text;
}

function parseVariables(content){
  if(!content) return [];
  const matches = content.match(/{{\s*([^}]+)\s*}}/g) || [];
  return [...new Set(matches.map(m => m.replace(/{{|}}/g,'').trim()))];
}

function highlightVariables(content){
  if(!content) return '';
  return content.replace(/{{\s*([^}]+)\s*}}/g, '<span class="var">{{$1}}</span>');
}

function openCreate(){
  editingTemplate.value = null;
  showForm.value = true;
}

function edit(t){
  editingTemplate.value = { ...t };
  showForm.value = true;
}

function preview(t){
  previewTemplate.value = t;
  showPreview.value = true;
}

function onSaved(){
  showForm.value = false;
  fetch();
  Swal.fire('Salvo', 'Template salvo com sucesso', 'success');
}

async function duplicate(t){
  const result = await Swal.fire({
    title: 'Duplicar template?',
    text: 'Um novo template será criado como cópia deste.',
    icon: 'question',
    showCancelButton: true
  });

  if (!result.isConfirmed) return;

  try {
    const res = await axios.post(`/api/crm/ai/templates/${t.id}/duplicate`);
    Swal.fire('Duplicado', res.data.message || 'Template duplicado', 'success');
    fetch();
  } catch (err) {
    console.error(err);
    Swal.fire('Erro', 'Falha ao duplicar template', 'error');
  }
}

async function confirmDelete(t){
  const result = await Swal.fire({
    title: 'Excluir template?',
    text: 'Essa ação é irreversível.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Excluir'
  });

  if (!result.isConfirmed) return;

  try {
    await axios.delete(`/api/crm/ai/templates/${t.id}`);
    Swal.fire('Excluído', 'Template removido', 'success');
    fetch();
  } catch (err) {
    console.error(err);
    Swal.fire('Erro', 'Falha ao excluir template', 'error');
  }
}

function formatDate(date){
  return new Date(date).toLocaleString('pt-BR');
}
</script>

