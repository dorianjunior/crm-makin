<script setup>
import { ref, computed, watch } from 'vue';
import { useMask } from '@/composables/useMask';

const props = defineProps({
    modelValue: [String, Number],
    type: {
        type: String,
        default: 'text',
    },
    label: String,
    placeholder: String,
    error: String,
    helperText: String,
    icon: String,
    iconRight: String,
    disabled: Boolean,
    required: Boolean,
    readonly: Boolean,
    maxlength: [String, Number],
    rows: {
        type: [String, Number],
        default: 3,
    },
    autofocus: Boolean,
    mask: String,
    success: Boolean,
    showValidation: Boolean,
});

const emit = defineEmits(['update:modelValue', 'blur', 'focus', 'input']);

const { applyMask } = useMask();

const input = ref(null);
const isFocused = ref(false);
const localValue = ref(props.modelValue || '');

const inputId = `input-${Math.random().toString(36).substring(7)}`;

const hasError = computed(() => !!props.error);
const hasSuccess = computed(() => props.success && !hasError.value && localValue.value);
const showValidationIcon = computed(() => props.showValidation && !isFocused.value);

watch(() => props.modelValue, (value) => {
    localValue.value = value || '';
    if (input.value && input.value.value !== value) {
        input.value.value = value;
    }
});

const handleInput = (event) => {
    let value = event.target.value;

    // Aplica máscara se especificada
    if (props.mask) {
        value = applyMask(value, props.mask);
        // Atualiza o campo visualmente
        if (input.value) {
            input.value.value = value;
        }
    }

    localValue.value = value;
    emit('update:modelValue', value);
    emit('input', value);
};

const handleFocus = (event) => {
    isFocused.value = true;
    emit('focus', event);
};

const handleBlur = (event) => {
    isFocused.value = false;
    emit('blur', event);
};

const focus = () => {
    input.value?.focus();
};

defineExpose({ focus });
</script>

<template>
    <div class="input">
        <!-- Label -->
        <label
            v-if="label"
            :for="inputId"
            class="input__label"
        >
            {{ label }}
            <span v-if="required" class="input__required">*</span>
        </label>

        <!-- Input Wrapper -->
        <div
            :class="[
                'input__wrapper',
                { 'input__wrapper--focused': isFocused },
                { 'input__wrapper--error': hasError },
                { 'input__wrapper--success': hasSuccess },
                { 'input__wrapper--disabled': disabled },
            ]"
        >
            <i v-if="icon" :class="`fas ${icon}`" class="input__icon"></i>

            <!-- Textarea -->
            <textarea
                v-if="type === 'textarea'"
                :id="inputId"
                ref="input"
                :value="modelValue"
                :placeholder="placeholder"
                :disabled="disabled"
                :readonly="readonly"
                :required="required"
                :maxlength="maxlength"
                :rows="rows"
                class="input__field input__field--textarea"
                @input="handleInput"
                @focus="handleFocus"
                @blur="handleBlur"
            />

            <!-- Regular Input -->
            <input
                v-else
                :id="inputId"
                ref="input"
                :type="type"
                :value="modelValue"
                :placeholder="placeholder"
                :disabled="disabled"
                :readonly="readonly"
                :required="required"
                :maxlength="maxlength"
                class="input__field"
                @input="handleInput"
                @focus="handleFocus"
                @blur="handleBlur"
            />

            <!-- Validation Icons -->
            <i
                v-if="showValidationIcon && hasError"
                class="fas fa-exclamation-circle input__icon-validation input__icon-validation--error"
                title="Campo inválido"
            ></i>
            <i
                v-else-if="showValidationIcon && hasSuccess"
                class="fas fa-check-circle input__icon-validation input__icon-validation--success"
                title="Campo válido"
            ></i>
            <i v-else-if="iconRight" :class="`fas ${iconRight}`" class="input__icon-right"></i>
        </div>

        <!-- Helper / Error Text -->
        <div v-if="error || helperText" class="input__footer">
            <span
                v-if="error"
                class="input__error"
            >
                <i class="fas fa-exclamation-circle"></i>
                {{ error }}
            </span>
            <span
                v-else-if="helperText"
                class="input__helper"
            >
                {{ helperText }}
            </span>
        </div>

        <!-- Character Count -->
        <div
            v-if="maxlength && modelValue"
            class="input__count"
        >
            {{ modelValue.length }} / {{ maxlength }}
        </div>
    </div>
</template>

<style scoped>
.input {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.input__label {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--text-primary);
}

.input__required {
    color: var(--color-error);
    margin-left: 2px;
}

.input__wrapper {
    position: relative;
    display: flex;
    align-items: center;
    background: var(--bg-primary);
    border: 2px solid var(--border-color);
    transition: all 180ms ease;
}

.input__wrapper--focused {
    border-color: var(--color-accent);
}

.input__wrapper--error {
    border-color: var(--color-error);
}

.input__wrapper--disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.input__field {
    flex: 1;
    padding: 12px 16px;
    font-family: 'Inter', sans-serif;
    font-size: 14px;
    font-weight: 500;
    line-height: 1.5;
    color: var(--text-primary);
    background: transparent;
    border: none;
    outline: none;
    transition: all 0.2s ease;
}

.input__field--textarea {
    resize: vertical;
    min-height: 100px;
}

.input__field::placeholder {
    color: var(--text-muted);
    opacity: 0.6;
}

.input__field:disabled {
    cursor: not-allowed;
}

.input__icon,
.input__icon-right {
    flex-shrink: 0;
    font-size: 16px;
    color: var(--text-secondary);
}

.input__icon {
    margin-left: 16px;
}

.input__icon-right {
    margin-right: 16px;
}

/* Validation Icons */
.input__icon-validation {
    flex-shrink: 0;
    margin-right: 12px;
    font-size: 18px;
    animation: fadeIn 0.3s ease;
}

.input__icon-validation--error {
    color: var(--color-error);
}

.input__icon-validation--success {
    color: var(--color-success);
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: scale(0.8);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

.input__footer {
    display: flex;
    align-items: center;
    gap: 4px;
    font-family: 'Inter', sans-serif;
    font-size: 12px;
    line-height: 1.4;
}

.input__error {
    display: flex;
    align-items: center;
    gap: 6px;
    color: var(--color-error);
    font-weight: 500;
    animation: slideIn 0.2s ease;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-4px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.input__helper {
    color: var(--text-secondary);
}

.input__count {
    font-family: 'JetBrains Mono', monospace;
    font-size: 11px;
    color: var(--text-muted);
    text-align: right;
}

/* Success State */
.input__wrapper--success {
    border-color: var(--color-success);
}

.input__wrapper--success:hover {
    border-color: var(--color-success);
}

/* Dark mode adjustments */
:root[data-theme='dark'] .input__field {
    color: var(--text-primary);
}

:root[data-theme='dark'] .input__wrapper {
    background: var(--bg-secondary);
}
</style>
