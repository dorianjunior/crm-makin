<template>
    <MainLayout title="Novo Lead">
        <template #breadcrumbs>
            <Breadcrumbs :items="breadcrumbs" />
        </template>

        <div class="page-container">
            <div class="page-header">
                <div class="page-header__content">
                    <h1 class="page-header__title">NOVO LEAD</h1>
                    <p class="page-header__subtitle">Cadastre um novo lead no sistema</p>
                </div>
            </div>

            <form @submit.prevent="submit" class="card">
                <div class="card__body">
                    <div class="form-grid">
                        <Input
                            v-model="form.name"
                            label="Nome *"
                            placeholder="Nome completo"
                            :error="showError('name')"
                            :success="isFieldValid('name')"
                            show-validation
                            required
                            @blur="markFieldAsTouched('name')"
                        />

                        <Input
                            v-model="form.email"
                            label="Email"
                            type="email"
                            placeholder="email@exemplo.com"
                            icon="fa-envelope"
                            :error="showError('email')"
                            :success="isFieldValid('email')"
                            show-validation
                            @blur="markFieldAsTouched('email')"
                        />

                        <Input
                            v-model="form.phone"
                            label="Telefone"
                            placeholder="(00) 00000-0000"
                            icon="fa-phone"
                            mask="phone"
                            :error="showError('phone')"
                            :success="isFieldValid('phone')"
                            show-validation
                            @blur="markFieldAsTouched('phone')"
                        />

                        <Input
                            v-model="form.company"
                            label="Empresa"
                            placeholder="Nome da empresa"
                            icon="fa-building"
                            :error="form.errors.company"
                        />

                        <Select
                            v-model="form.status"
                            label="Status *"
                            :options="statusOptions"
                            :error="form.errors.status"
                            required
                        />

                        <Select
                            v-model="form.source_id"
                            label="Fonte"
                            :options="sourceOptions"
                            :error="form.errors.source_id"
                            placeholder="Selecione uma fonte"
                        />

                        <Select
                            v-model="form.assigned_to"
                            label="Responsável"
                            :options="userOptions"
                            :error="form.errors.assigned_to"
                            placeholder="Selecione um responsável"
                        />
                    </div>

                    <div style="margin-top: 24px;">
                        <label class="form-label">Notas</label>
                        <textarea
                            v-model="form.notes"
                            class="form-textarea"
                            rows="4"
                            placeholder="Observações sobre o lead..."
                        ></textarea>
                        <span v-if="form.errors.notes" class="form-error">{{ form.errors.notes }}</span>
                    </div>
                </div>

                <div class="card__footer">
                    <button type="button" class="btn btn--secondary" @click="cancel">
                        Cancelar
                    </button>
                    <button type="submit" class="btn" :disabled="form.processing">
                        <i class="fas fa-save"></i>
                        {{ form.processing ? 'Salvando...' : 'Salvar Lead' }}
                    </button>
                </div>
            </form>
        </div>
    </MainLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Input from '@/Components/Input.vue';
import Select from '@/Components/Select.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import { useAlert } from '@/composables/useAlert';
import { useFormValidation } from '@/composables/useFormValidation';

const alert = useAlert();
const { validateField, touchField, isFieldTouched, getFieldError } = useFormValidation();

const props = defineProps({
    sources: {
        type: Array,
        default: () => []
    },
    users: {
        type: Array,
        default: () => []
    },
});

const breadcrumbs = [
    { name: 'Dashboard', href: '/dashboard' },
    { name: 'Leads', href: '/leads' },
    { name: 'Novo Lead' }
];

const form = useForm({
    name: '',
    email: '',
    phone: '',
    company: '',
    status: 'new',
    source_id: '',
    assigned_to: '',
    notes: '',
});

// Estado de validação local
const fieldErrors = ref({});
const touchedFields = ref({});

// Regras de validação
const validationRules = {
    name: ['required'],
    email: ['email'],
    phone: ['phone'],
};

// Validar campo individual
const validateFieldLocal = (fieldName, showError = true) => {
    const rules = validationRules[fieldName] || [];
    const isValid = validateField(fieldName, form[fieldName], rules, fieldName);

    if (showError) {
        fieldErrors.value[fieldName] = getFieldError(fieldName);
    }

    return isValid;
};

// Marcar campo como tocado
const markFieldAsTouched = (fieldName) => {
    touchedFields.value[fieldName] = true;
    validateFieldLocal(fieldName);
};

// Verificar se mostra erro
const showError = (fieldName) => {
    return form.errors[fieldName] || (touchedFields.value[fieldName] && fieldErrors.value[fieldName]);
};

// Verificar se campo é válido (para ícone de sucesso)
const isFieldValid = (fieldName) => {
    const rules = validationRules[fieldName];
    if (!rules || rules.length === 0) return false;
    return touchedFields.value[fieldName] && !showError(fieldName) && form[fieldName];
};

// Watch para validar em tempo real após campo ser tocado
watch(() => form.name, () => {
    if (touchedFields.value.name) validateFieldLocal('name');
});

watch(() => form.email, () => {
    if (touchedFields.value.email) validateFieldLocal('email');
});

watch(() => form.phone, () => {
    if (touchedFields.value.phone) validateFieldLocal('phone');
});

const statusOptions = [
    { value: 'new', label: 'Novo' },
    { value: 'contacted', label: 'Contatado' },
    { value: 'qualified', label: 'Qualificado' },
    { value: 'negotiation', label: 'Negociação' },
    { value: 'won', label: 'Ganho' },
    { value: 'lost', label: 'Perdido' },
];

const sourceOptions = computed(() =>
    props.sources.map(source => ({ value: source.id, label: source.name }))
);

const userOptions = computed(() =>
    props.users.map(user => ({ value: user.id, label: user.name }))
);

const submit = () => {
    // Marcar todos os campos como tocados
    Object.keys(validationRules).forEach(field => {
        touchedFields.value[field] = true;
        validateFieldLocal(field);
    });

    // Verificar se há erros de validação locais
    const hasLocalErrors = Object.values(fieldErrors.value).some(error => error !== null);
    if (hasLocalErrors) {
        alert.error('Por favor, corrija os erros no formulário');
        return;
    }

    form.post('/leads', {
        preserveScroll: true,
        onSuccess: () => {
            alert.success('Lead criado com sucesso!');
        },
        onError: (errors) => {
            alert.error('Erro ao criar lead. Verifique os campos.');
        },
    });
};

const cancel = () => {
    if (form.isDirty) {
        if (confirm('Deseja realmente cancelar? As alterações não serão salvas.')) {
            router.visit('/leads');
        }
    } else {
        router.visit('/leads');
    }
};
</script>

<style scoped lang="scss">
.page-container {
    padding: 32px;
    max-width: 1200px;
    margin: 0 auto;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 24px;
}

@media (max-width: 768px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
}

.form-label {
    display: block;
    font-size: 14px;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 8px;
}

.form-textarea {
    width: 100%;
    padding: 12px;
    border: 2px solid var(--border-color);
    background: var(--bg-primary);
    color: var(--text-primary);
    font-size: 14px;
    resize: vertical;
    font-family: inherit;

    &:focus {
        outline: none;
        border-color: #FF6B35;
    }
}

.form-error {
    display: block;
    color: #dc3545;
    font-size: 12px;
    margin-top: 4px;
}

.card__footer {
    padding: 24px;
    border-top: 2px solid var(--border-color);
    display: flex;
    justify-content: flex-end;
    gap: 12px;
}
</style>
