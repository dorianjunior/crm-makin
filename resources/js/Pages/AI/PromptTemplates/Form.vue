<template>
  <div class="prompt-form">
    <div class="form-row">
      <div class="form-group flex-2">
        <label>Nome do Template *</label>
        <Input v-model="form.name" placeholder="Nome do template" />
      </div>

      <div class="form-group">
        <label>Categoria</label>
        <select v-model="form.category" class="form-select">
          <option value="Geral">Geral</option>
          <option value="Vendas">Vendas</option>
          <option value="Suporte">Suporte</option>
          <option value="Marketing">Marketing</option>
        </select>
      </div>

      <div class="form-group">
        <label>Ativo</label>
        <select v-model="form.active" class="form-select">
          <option :value="true">Sim</option>
          <option :value="false">Não</option>
        </select>
      </div>
    </div>

    <div class="form-card content-card">
      <label>Prompt (use variáveis como {{lead_name}}, {{company}})</label>
      <textarea v-model="form.content" rows="8" placeholder="Escreva o prompt a ser enviado para a IA..."></textarea>

      <div class="variables-palette">
        <strong>Variáveis:</strong>
        <button v-for="v in variables" :key="v" class="var-btn" @click="insertVar(v)">{{ v }}</button>
      </div>

      <div class="preview-area">
        <h4>Preview</h4>
        <div class="preview-box" v-html="renderPreview()"></div>
      </div>
    </div>

    <div class="form-actions">
      <Button variant="secondary" size="small" @click="$emit('cancel')">Cancelar</Button>
      <Button variant="primary" size="small" @click="save">Salvar</Button>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import Input from '@/Components/Input.vue';
import Button from '@/Components/Button.vue';
import Swal from 'sweetalert2';
import axios from 'axios';

const props = defineProps({ initial: Object });
const emit = defineEmits(['saved','cancel']);

const form = ref({
  name: props.initial?.name || '',
  category: props.initial?.category || 'Geral',
  active: props.initial?.active ?? true,
  content: props.initial?.content || ''
});

const variables = ['{{lead_name}}','{{company}}','{{lead_email}}','{{meeting_date}}','{{product_name}}'];

function insertVar(v){
  form.value.content += ' ' + v;
}

function renderPreview(){
  let out = form.value.content || '';
  out = out.replace(/{{\s*lead_name\s*}}/g, '<strong>João Silva</strong>');
  out = out.replace(/{{\s*company\s*}}/g, '<strong>Empresa X</strong>');
  return out.replace(/\n/g, '<br>');
}

async function save(){
  if(!form.value.name) return Swal.fire('Erro', 'Nome do template é obrigatório', 'error');

  const payload = {
    name: form.value.name,
    category: form.value.category,
    is_active: !!form.value.active,
    user_prompt_template: form.value.content,
    // system_prompt optional - keep same as user for now
    system_prompt: form.value.content,
  };

  try {
    if (props.initial?.id) {
      await axios.put(`/api/crm/ai/templates/${props.initial.id}`, payload);
    } else {
      await axios.post('/api/crm/ai/templates', payload);
    }

    Swal.fire('Salvo', 'Template salvo com sucesso', 'success');
    emit('saved');
  } catch (err) {
    console.error(err);
    const msg = err.response?.data?.message || 'Falha ao salvar template';
    Swal.fire('Erro', msg, 'error');
  }
}
</script>

