<script setup>
const props = defineProps({
    modelValue: [Boolean, Array],
    value: [String, Number, Boolean],
    label: String,
    description: String,
    disabled: Boolean,
    indeterminate: Boolean,
});

const emit = defineEmits(['update:modelValue']);

const checkboxId = `checkbox-${Math.random().toString(36).substring(7)}`;

const handleChange = (event) => {
    const isChecked = event.target.checked;

    if (Array.isArray(props.modelValue)) {
        const newValue = [...props.modelValue];
        if (isChecked) {
            newValue.push(props.value);
        } else {
            const index = newValue.indexOf(props.value);
            if (index > -1) {
                newValue.splice(index, 1);
            }
        }
        emit('update:modelValue', newValue);
    } else {
        emit('update:modelValue', isChecked);
    }
};

const isChecked = () => {
    if (Array.isArray(props.modelValue)) {
        return props.modelValue.includes(props.value);
    }
    return props.modelValue;
};
</script>

<template>
    <div class="checkbox">
        <div class="checkbox__wrapper">
            <input
                :id="checkboxId"
                type="checkbox"
                :checked="isChecked()"
                :value="value"
                :disabled="disabled"
                :indeterminate="indeterminate"
                class="checkbox__input"
                @change="handleChange"
            />
            <label
                :for="checkboxId"
                :class="[
                    'checkbox__box',
                    { 'checkbox__box--checked': isChecked() },
                    { 'checkbox__box--indeterminate': indeterminate },
                    { 'checkbox__box--disabled': disabled },
                ]"
            >
                <i v-if="indeterminate" class="fas fa-minus checkbox__icon"></i>
                <i v-else-if="isChecked()" class="fas fa-check checkbox__icon"></i>
            </label>
        </div>

        <div v-if="label || description" class="checkbox__content">
            <label
                :for="checkboxId"
                class="checkbox__label"
            >
                {{ label }}
            </label>
            <p v-if="description" class="checkbox__description">
                {{ description }}
            </p>
        </div>
    </div>
</template>

<style scoped>
.checkbox {
    display: flex;
    align-items: flex-start;
    gap: 12px;
}

.checkbox__wrapper {
    position: relative;
    flex-shrink: 0;
}

.checkbox__input {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
}

.checkbox__box {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 20px;
    height: 20px;
    border: 2px solid var(--border-color);
    background: var(--bg-primary);
    cursor: pointer;
    transition: all 180ms ease;
}

.checkbox__box:hover:not(.checkbox__box--disabled) {
    border-color: var(--color-accent);
}

.checkbox__box--checked {
    background: var(--color-accent);
    border-color: var(--color-accent);
}

.checkbox__box--indeterminate {
    background: var(--color-accent);
    border-color: var(--color-accent);
}

.checkbox__box--disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.checkbox__icon {
    font-size: 11px;
    color: #fff;
}

.checkbox__input:focus-visible + .checkbox__box {
    outline: 2px solid var(--color-accent);
    outline-offset: 2px;
}

.checkbox__content {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.checkbox__label {
    font-family: 'Inter', sans-serif;
    font-size: 14px;
    font-weight: 500;
    color: var(--text-primary);
    cursor: pointer;
}

.checkbox__description {
    font-family: 'Inter', sans-serif;
    font-size: 12px;
    color: var(--text-secondary);
    line-height: 1.4;
    margin: 0;
}
</style>
