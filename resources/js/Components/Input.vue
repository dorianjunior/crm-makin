<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    modelValue: [String, Number],
    label: String,
    type: {
        type: String,
        default: 'text',
    },
    placeholder: String,
    error: String,
    disabled: Boolean,
    required: Boolean,
    icon: String,
    iconRight: String,
});

const emit = defineEmits(['update:modelValue']);

const input = ref(null);

watch(() => props.modelValue, (value) => {
    if (input.value && input.value.value !== value) {
        input.value.value = value;
    }
});

const focus = () => {
    input.value?.focus();
};

defineExpose({ focus });
</script>

<template>
    <div class="w-full">
        <label
            v-if="label"
            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
        >
            {{ label }}
            <span v-if="required" class="text-red-500">*</span>
        </label>
        
        <div class="relative">
            <div v-if="icon" class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                <i :class="`fas ${icon}`" class="text-gray-400"></i>
            </div>
            
            <input
                ref="input"
                :type="type"
                :value="modelValue"
                :placeholder="placeholder"
                :disabled="disabled"
                :class="[
                    'w-full px-4 py-2 border rounded-lg transition-colors',
                    'focus:ring-2 focus:ring-blue-500 focus:border-transparent',
                    'dark:bg-gray-700 dark:border-gray-600 dark:text-white',
                    'disabled:bg-gray-100 disabled:cursor-not-allowed dark:disabled:bg-gray-800',
                    error ? 'border-red-500' : 'border-gray-300',
                    icon ? 'pl-10' : '',
                    iconRight ? 'pr-10' : '',
                ]"
                @input="emit('update:modelValue', $event.target.value)"
            />
            
            <div v-if="iconRight" class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                <i :class="`fas ${iconRight}`" class="text-gray-400"></i>
            </div>
        </div>
        
        <p v-if="error" class="mt-1 text-sm text-red-500">
            {{ error }}
        </p>
    </div>
</template>
