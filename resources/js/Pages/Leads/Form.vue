<template>
  <MainLayout>
    <div class="lead-form">
      <!-- Header -->
      <div class="page-header">
        <div>
          <h1 class="page-title">{{ isEditing ? 'Editar Lead' : 'Novo Lead' }}</h1>
          <Breadcrumbs :items="breadcrumbs" />
        </div>
      </div>

      <!-- Alert de erro -->
      <Alert
        v-if="Object.keys(errors).length > 0"
        type="error"
        title="Erro de validação"
        :dismissible="false"
      >
        Por favor, corrija os erros abaixo.
      </Alert>

      <form @submit.prevent="submitForm" class="form-card">
        <!-- Informações Básicas -->
        <div class="form-section">
          <h2 class="section-title">
            <i class="fa fa-user"></i> Informações Básicas
          </h2>

          <div class="form-grid">
            <div class="form-group">
              <label class="required">Nome Completo</label>
              <Input
                v-model="form.name"
                placeholder="Digite o nome completo"
                :error="errors.name"
              />
              <span v-if="errors.name" class="error-message">{{ errors.name }}</span>
            </div>

            <div class="form-group">
              <label class="required">Email</label>
              <Input
                v-model="form.email"
                type="email"
                placeholder="exemplo@email.com"
                icon="fa fa-envelope"
                :error="errors.email"
              />
              <span v-if="errors.email" class="error-message">{{ errors.email }}</span>
            </div>

            <div class="form-group">
              <label>Telefone</label>
              <Input
                v-model="form.phone"
                placeholder="(00) 00000-0000"
                icon="fa fa-phone"
                :error="errors.phone"
              />
              <span v-if="errors.phone" class="error-message">{{ errors.phone }}</span>
            </div>

            <div class="form-group">
              <label>WhatsApp</label>
              <Input
                v-model="form.whatsapp"
                placeholder="(00) 00000-0000"
                icon="fab fa-whatsapp"
                :error="errors.whatsapp"
              />
              <span v-if="errors.whatsapp" class="error-message">{{ errors.whatsapp }}</span>
            </div>

            <div class="form-group">
              <label>Empresa</label>
              <Input
                v-model="form.company"
                placeholder="Nome da empresa"
                icon="fa fa-building"
                :error="errors.company"
              />
            </div>

            <div class="form-group">
              <label>Cargo</label>
              <Input
                v-model="form.position"
                placeholder="Ex: Gerente de Marketing"
                :error="errors.position"
              />
            </div>
          </div>
        </div>

        <!-- Informações de Contato -->
        <div class="form-section">
          <h2 class="section-title">
            <i class="fa fa-map-marker-alt"></i> Endereço
          </h2>

          <div class="form-grid">
            <div class="form-group">
              <label>CEP</label>
              <Input
                v-model="form.address_zipcode"
                placeholder="00000-000"
                @blur="searchZipcode"
                :error="errors.address_zipcode"
              />
            </div>

            <div class="form-group">
              <label>Rua</label>
              <Input
                v-model="form.address_street"
                placeholder="Nome da rua"
                :error="errors.address_street"
              />
            </div>

            <div class="form-group">
              <label>Número</label>
              <Input
                v-model="form.address_number"
                placeholder="Número"
                :error="errors.address_number"
              />
            </div>

            <div class="form-group">
              <label>Complemento</label>
              <Input
                v-model="form.address_complement"
                placeholder="Apto, sala, etc"
                :error="errors.address_complement"
              />
            </div>

            <div class="form-group">
              <label>Bairro</label>
              <Input
                v-model="form.address_neighborhood"
                placeholder="Bairro"
                :error="errors.address_neighborhood"
              />
            </div>

            <div class="form-group">
              <label>Cidade</label>
              <Input
                v-model="form.address_city"
                placeholder="Cidade"
                :error="errors.address_city"
              />
            </div>

            <div class="form-group">
              <label>Estado</label>
              <select v-model="form.address_state" class="form-select">
                <option value="">Selecione</option>
                <option v-for="state in brazilianStates" :key="state" :value="state">
                  {{ state }}
                </option>
              </select>
            </div>
          </div>
        </div>

        <!-- Classificação -->
        <div class="form-section">
          <h2 class="section-title">
            <i class="fa fa-tags"></i> Classificação
          </h2>

          <div class="form-grid">
            <div class="form-group">
              <label class="required">Status</label>
              <select v-model="form.status" class="form-select" :class="{ 'is-invalid': errors.status }">
                <option value="new">Novo</option>
                <option value="contacted">Contatado</option>
                <option value="qualified">Qualificado</option>
                <option value="negotiation">Negociação</option>
                <option value="won">Ganho</option>
                <option value="lost">Perdido</option>
              </select>
              <span v-if="errors.status" class="error-message">{{ errors.status }}</span>
            </div>

            <div class="form-group">
              <label class="required">Fonte</label>
              <select v-model="form.source_id" class="form-select" :class="{ 'is-invalid': errors.source_id }">
                <option value="">Selecione uma fonte</option>
                <option v-for="source in sources" :key="source.id" :value="source.id">
                  {{ source.name }}
                </option>
              </select>
              <span v-if="errors.source_id" class="error-message">{{ errors.source_id }}</span>
            </div>

            <div class="form-group">
              <label>Pipeline</label>
              <select v-model="form.pipeline_id" class="form-select">
                <option value="">Selecione um pipeline</option>
                <option v-for="pipeline in pipelines" :key="pipeline.id" :value="pipeline.id">
                  {{ pipeline.name }}
                </option>
              </select>
            </div>

            <div class="form-group">
              <label>Estágio</label>
              <select v-model="form.stage_id" class="form-select" :disabled="!form.pipeline_id">
                <option value="">Selecione um estágio</option>
                <option v-for="stage in filteredStages" :key="stage.id" :value="stage.id">
                  {{ stage.name }}
                </option>
              </select>
            </div>

            <div class="form-group">
              <label>Responsável</label>
              <select v-model="form.assigned_to" class="form-select">
                <option value="">Nenhum</option>
                <option v-for="user in users" :key="user.id" :value="user.id">
                  {{ user.name }}
                </option>
              </select>
            </div>

            <div class="form-group">
              <label>Valor Estimado</label>
              <Input
                v-model="form.estimated_value"
                type="number"
                step="0.01"
                placeholder="0.00"
                icon="fa fa-dollar-sign"
                :error="errors.estimated_value"
              />
            </div>
          </div>
        </div>

        <!-- Observações -->
        <div class="form-section">
          <h2 class="section-title">
            <i class="fa fa-clipboard"></i> Observações
          </h2>

          <div class="form-group">
            <label>Anotações</label>
            <textarea
              v-model="form.notes"
              class="form-textarea"
              placeholder="Adicione observações sobre este lead..."
              rows="5"
            ></textarea>
          </div>
        </div>

        <!-- Actions -->
        <div class="form-actions">
          <Button
            type="button"
            label="Cancelar"
            @click="cancel"
            severity="secondary"
            outlined
          />
          <Button
            type="submit"
            :label="isEditing ? 'Atualizar Lead' : 'Criar Lead'"
            :loading="processing"
            icon="fa fa-save"
            severity="success"
          />
        </div>
      </form>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Button from '@/Components/Button.vue';
import Input from '@/Components/Input.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import Alert from '@/Components/Alert.vue';

const props = defineProps({
  lead: Object,
  sources: Array,
  pipelines: Array,
  stages: Array,
  users: Array,
  errors: Object,
});

const isEditing = computed(() => !!props.lead);

const breadcrumbs = computed(() => [
  { label: 'Dashboard', to: '/dashboard' },
  { label: 'Leads', to: '/leads' },
  { label: isEditing.value ? 'Editar' : 'Novo', active: true }
]);

const brazilianStates = [
  'AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA',
  'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN',
  'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'
];

const form = useForm({
  name: props.lead?.name || '',
  email: props.lead?.email || '',
  phone: props.lead?.phone || '',
  whatsapp: props.lead?.whatsapp || '',
  company: props.lead?.company || '',
  position: props.lead?.position || '',
  address_zipcode: props.lead?.address_zipcode || '',
  address_street: props.lead?.address_street || '',
  address_number: props.lead?.address_number || '',
  address_complement: props.lead?.address_complement || '',
  address_neighborhood: props.lead?.address_neighborhood || '',
  address_city: props.lead?.address_city || '',
  address_state: props.lead?.address_state || '',
  status: props.lead?.status || 'new',
  source_id: props.lead?.source_id || '',
  pipeline_id: props.lead?.pipeline_id || '',
  stage_id: props.lead?.stage_id || '',
  assigned_to: props.lead?.assigned_to || '',
  estimated_value: props.lead?.estimated_value || '',
  notes: props.lead?.notes || '',
});

const processing = ref(false);

const filteredStages = computed(() => {
  if (!form.pipeline_id) return [];
  return props.stages.filter(stage => stage.pipeline_id === form.pipeline_id);
});

watch(() => form.pipeline_id, (newVal, oldVal) => {
  if (newVal !== oldVal) {
    form.stage_id = '';
  }
});

const searchZipcode = async () => {
  const zipcode = form.address_zipcode.replace(/\D/g, '');

  if (zipcode.length !== 8) return;

  try {
    const response = await fetch(`https://viacep.com.br/ws/${zipcode}/json/`);
    const data = await response.json();

    if (!data.erro) {
      form.address_street = data.logradouro;
      form.address_neighborhood = data.bairro;
      form.address_city = data.localidade;
      form.address_state = data.uf;
    }
  } catch (error) {
    console.error('Erro ao buscar CEP:', error);
  }
};

const submitForm = () => {
  processing.value = true;

  if (isEditing.value) {
    form.put(`/leads/${props.lead.id}`, {
      onFinish: () => processing.value = false,
    });
  } else {
    form.post('/leads', {
      onFinish: () => processing.value = false,
    });
  }
};

const cancel = () => {
  router.visit('/leads');
};
</script>

<style scoped lang="scss">
.lead-form {
  padding: 2rem;
  max-width: 1200px;
  margin: 0 auto;
}

.page-header {
  margin-bottom: 2rem;
}

.page-title {
  font-size: 2rem;
  font-weight: 700;
  color: var(--text-primary);
  margin-bottom: 0.5rem;
}

.form-card {
  background: var(--surface-card);
  border-radius: var(--border-radius);
  padding: 2rem;
  box-shadow: var(--shadow-sm);
}

.form-section {
  margin-bottom: 2.5rem;

  &:last-of-type {
    margin-bottom: 2rem;
  }
}

.section-title {
  font-size: 1.25rem;
  font-weight: 600;
  color: var(--text-primary);
  margin-bottom: 1.5rem;
  padding-bottom: 0.75rem;
  border-bottom: 2px solid var(--border-color);
  display: flex;
  align-items: center;
  gap: 0.75rem;

  i {
    color: var(--primary-color);
  }
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 1.5rem;
}

.form-group {
  display: flex;
  flex-direction: column;

  label {
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

.form-select,
.form-textarea {
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

  &.is-invalid {
    border-color: var(--red-500);
  }

  &:disabled {
    opacity: 0.6;
    cursor: not-allowed;
  }
}

.form-textarea {
  resize: vertical;
  min-height: 100px;
  font-family: inherit;
}

.error-message {
  color: var(--red-500);
  font-size: 0.8rem;
  margin-top: 0.25rem;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  padding-top: 1.5rem;
  border-top: 1px solid var(--border-color);
}

@media (max-width: 768px) {
  .lead-form {
    padding: 1rem;
  }

  .form-card {
    padding: 1.5rem;
  }

  .form-grid {
    grid-template-columns: 1fr;
  }

  .form-actions {
    flex-direction: column-reverse;

    button {
      width: 100%;
    }
  }
}
</style>
