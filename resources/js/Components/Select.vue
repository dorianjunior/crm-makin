<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    modelValue: [String, Number, Array],
    options: {
        type: Array,
        required: true,
    },
    label: String,
    placeholder: {
        type: String,
        default: 'Selecione...',
    },
    error: String,
    helperText: String,
    disabled: Boolean,
    required: Boolean,
    multiple: Boolean,
    searchable: Boolean,
    valueKey: {
        type: String,
        default: 'value',
    },
    labelKey: {
        type: String,
        default: 'label',
    },
});

const emit = defineEmits(['update:modelValue']);

const isOpen = ref(false);
const searchTerm = ref('');
const selectRef = ref(null);

const selectId = `select-${Math.random().toString(36).substring(7)}`;

const hasError = computed(() => !!props.error);

const selectedOption = computed(() => {
    if (props.multiple) return null;
    return props.options.find(opt => opt[props.valueKey] === props.modelValue);
});

const displayText = computed(() => {
    if (props.multiple) {
        const selected = props.options.filter(opt =>
            props.modelValue?.includes(opt[props.valueKey])
        );
        return selected.length > 0
            ? selected.map(opt => opt[props.labelKey]).join(', ')
            : props.placeholder;
    }
    return selectedOption.value?.[props.labelKey] || props.placeholder;
});

const filteredOptions = computed(() => {
    if (!props.searchable || !searchTerm.value) return props.options;

    const search = searchTerm.value.toLowerCase();
    return props.options.filter(opt =>
        opt[props.labelKey].toLowerCase().includes(search)
    );
});

const toggleDropdown = () => {
    if (!props.disabled) {
        isOpen.value = !isOpen.value;
        if (isOpen.value && props.searchable) {
            searchTerm.value = '';
        }
    }
};

const selectOption = (option) => {
    if (props.multiple) {
        const currentValues = Array.isArray(props.modelValue) ? [...props.modelValue] : [];
        const optionValue = option[props.valueKey];
        const index = currentValues.indexOf(optionValue);

        if (index > -1) {
            currentValues.splice(index, 1);
        } else {
            currentValues.push(optionValue);
        }

        emit('update:modelValue', currentValues);
    } else {
        emit('update:modelValue', option[props.valueKey]);
        isOpen.value = false;
    }
};

const isSelected = (option) => {
    if (props.multiple) {
        return props.modelValue?.includes(option[props.valueKey]);
    }
    return props.modelValue === option[props.valueKey];
};

const handleClickOutside = (event) => {
    if (selectRef.value && !selectRef.value.contains(event.target)) {
        isOpen.value = false;
    }
};

// Close on outside click
if (typeof document !== 'undefined') {
    document.addEventListener('click', handleClickOutside);
}
</script>

<template>
    <div class="select" ref="selectRef">
        <!-- Label -->
        <label
            v-if="label"
            :for="selectId"
            class="select__label"
        >
            {{ label }}
            <span v-if="required" class="select__required">*</span>
        </label>

        <!-- Select Button -->
        <button
            type="button"
            :id="selectId"
            :class="[
                'select__button',
                { 'select__button--open': isOpen },
                { 'select__button--error': hasError },
                { 'select__button--disabled': disabled },
            ]"
            @click="toggleDropdown"
            :disabled="disabled"
        >
            <span :class="[
                'select__value',
                { 'select__value--placeholder': !modelValue || (Array.isArray(modelValue) && modelValue.length === 0) }
            ]">
                {{ displayText }}
            </span>
            <i :class="[
                'fas',
                isOpen ? 'fa-chevron-up' : 'fa-chevron-down',
                'select__icon'
            ]"></i>
        </button>

        <!-- Dropdown -->
        <div v-show="isOpen" class="select__dropdown">
            <!-- Search Input -->
            <div v-if="searchable" class="select__search">
                <i class="fas fa-search select__search-icon"></i>
                <input
                    v-model="searchTerm"
                    type="text"
                    placeholder="Buscar..."
                    class="select__search-input"
                    @click.stop
                />
            </div>

            <!-- Options List -->
            <div class="select__options">
                <div
                    v-for="option in filteredOptions"
                    :key="option[valueKey]"
                    :class="[
                        'select__option',
                        { 'select__option--selected': isSelected(option) }
                    ]"
                    @click="selectOption(option)"
                >
                    <span>{{ option[labelKey] }}</span>
                    <i v-if="isSelected(option)" class="fas fa-check select__check"></i>
                </div>

                <div v-if="filteredOptions.length === 0" class="select__empty">
                    Nenhuma opção encontrada
                </div>
            </div>
        </div>

        <!-- Helper / Error Text -->
        <div v-if="error || helperText" class="select__footer">
            <span v-if="error" class="select__error">
                <i class="fas fa-exclamation-circle"></i>
                {{ error }}
            </span>
            <span v-else-if="helperText" class="select__helper">
                {{ helperText }}
            </span>
        </div>
    </div>
</template>

<style scoped>
.select {
    position: relative;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.select__label {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--text-primary);
}

.select__required {
    color: var(--color-error);
    margin-left: 2px;
}

.select__button {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    padding: 12px 16px;
    font-family: 'Inter', sans-serif;
    font-size: 14px;
    font-weight: 500;
    text-align: left;
    background: var(--bg-primary);
    border: 2px solid var(--border-color);
    color: var(--text-primary);
    cursor: pointer;
    transition: all 180ms ease;
}

.select__button:hover:not(:disabled) {
    border-color: var(--border-bold, #262626);
}

.select__button--open {
    border-color: var(--color-accent);
}

.select__button--error {
    border-color: var(--color-error);
}

.select__button--disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.select__value {
    flex: 1;
    color: var(--text-primary);
}

.select__value--placeholder {
    color: var(--text-muted);
    opacity: 0.6;
}

.select__icon {
    font-size: 12px;
    color: var(--text-secondary);
    transition: transform 180ms ease;
}

.select__dropdown {
    position: absolute;
    top: calc(100% + 4px);
    left: 0;
    right: 0;
    z-index: 50;
    background: var(--bg-primary);
    border: 2px solid var(--border-color);
    max-height: 300px;
    overflow: hidden;
    animation: slideDown 180ms ease;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-8px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.select__search {
    position: relative;
    padding: 12px;
    border-bottom: 2px solid var(--border-color);
}

.select__search-icon {
    position: absolute;
    left: 24px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-secondary);
    font-size: 14px;
}

.select__search-input {
    width: 100%;
    padding: 8px 12px 8px 36px;
    font-family: 'Inter', sans-serif;
    font-size: 13px;
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    color: var(--text-primary);
    outline: none;
}

.select__search-input:focus {
    border-color: var(--color-accent);
}

.select__options {
    max-height: 236px;
    overflow-y: auto;
}

.select__option {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 16px;
    font-family: 'Inter', sans-serif;
    font-size: 14px;
    color: var(--text-primary);
    cursor: pointer;
    transition: all 120ms ease;
    border-bottom: 1px solid var(--border-color);
}

.select__option:last-child {
    border-bottom: none;
}

.select__option:hover {
    background: var(--bg-secondary);
}

.select__option--selected {
    background: var(--bg-secondary);
    font-weight: 600;
}

.select__check {
    color: var(--color-accent);
    font-size: 14px;
}

.select__empty {
    padding: 24px 16px;
    text-align: center;
    font-family: 'Inter', sans-serif;
    font-size: 13px;
    color: var(--text-muted);
}

.select__footer {
    display: flex;
    align-items: center;
    gap: 4px;
    font-family: 'Inter', sans-serif;
    font-size: 12px;
}

.select__error {
    display: flex;
    align-items: center;
    gap: 6px;
    color: var(--color-error);
    font-weight: 500;
}

.select__helper {
    color: var(--text-secondary);
}

/* Scrollbar */
.select__options::-webkit-scrollbar {
    width: 8px;
}

.select__options::-webkit-scrollbar-track {
    background: var(--bg-primary);
}

.select__options::-webkit-scrollbar-thumb {
    background: var(--border-color);
    border: 2px solid var(--bg-primary);
}

.select__options::-webkit-scrollbar-thumb:hover {
    background: var(--text-muted);
}
</style>
