import { ref, computed } from 'vue';

export function useFormValidation() {
    const errors = ref({});
    const touched = ref({});

    // Validadores
    const validators = {
        required: (value, fieldName) => {
            if (!value || String(value).trim() === '') {
                return `${fieldName} é obrigatório`;
            }
            return null;
        },

        email: (value) => {
            if (!value) return null;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                return 'Email inválido';
            }
            return null;
        },

        phone: (value) => {
            if (!value) return null;
            const phoneRegex = /^\(\d{2}\) \d{4,5}-\d{4}$/;
            if (!phoneRegex.test(value)) {
                return 'Telefone inválido (use: (00) 00000-0000)';
            }
            return null;
        },

        minLength: (min) => (value, fieldName) => {
            if (!value) return null;
            if (String(value).length < min) {
                return `${fieldName} deve ter pelo menos ${min} caracteres`;
            }
            return null;
        },

        maxLength: (max) => (value, fieldName) => {
            if (!value) return null;
            if (String(value).length > max) {
                return `${fieldName} deve ter no máximo ${max} caracteres`;
            }
            return null;
        },
    };

    // Validar campo individual
    const validateField = (fieldName, value, rules, label) => {
        if (!rules || rules.length === 0) {
            errors.value[fieldName] = null;
            return true;
        }

        for (const rule of rules) {
            let validator;
            let params = [];

            if (typeof rule === 'string') {
                validator = validators[rule];
            } else if (typeof rule === 'object') {
                const [ruleName, ...ruleParams] = Object.entries(rule)[0];
                validator = validators[ruleName](...ruleParams);
            } else if (typeof rule === 'function') {
                validator = rule;
            }

            if (validator) {
                const error = validator(value, label || fieldName);
                if (error) {
                    errors.value[fieldName] = error;
                    return false;
                }
            }
        }

        errors.value[fieldName] = null;
        return true;
    };

    // Validar formulário completo
    const validateForm = (formData, rules) => {
        let isValid = true;
        errors.value = {};

        Object.keys(rules).forEach(fieldName => {
            const fieldRules = rules[fieldName].rules || [];
            const fieldLabel = rules[fieldName].label || fieldName;
            const fieldValue = formData[fieldName];

            if (!validateField(fieldName, fieldValue, fieldRules, fieldLabel)) {
                isValid = false;
            }
        });

        return isValid;
    };

    // Marcar campo como tocado
    const touchField = (fieldName) => {
        touched.value[fieldName] = true;
    };

    // Verificar se campo foi tocado
    const isFieldTouched = (fieldName) => {
        return touched.value[fieldName] || false;
    };

    // Obter erro do campo
    const getFieldError = (fieldName) => {
        return errors.value[fieldName] || null;
    };

    // Verificar se campo tem erro
    const hasFieldError = (fieldName) => {
        return !!errors.value[fieldName];
    };

    // Limpar erros
    const clearErrors = () => {
        errors.value = {};
        touched.value = {};
    };

    // Limpar erro específico
    const clearFieldError = (fieldName) => {
        errors.value[fieldName] = null;
    };

    return {
        errors,
        touched,
        validators,
        validateField,
        validateForm,
        touchField,
        isFieldTouched,
        getFieldError,
        hasFieldError,
        clearErrors,
        clearFieldError,
    };
}
