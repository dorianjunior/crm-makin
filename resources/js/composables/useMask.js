/**
 * Composable para aplicar máscaras em campos de formulário
 */
export function useMask() {
    // Máscara para telefone brasileiro
    const phoneMask = (value) => {
        if (!value) return '';

        // Remove tudo que não é dígito
        const numbers = value.replace(/\D/g, '');

        // Aplica a máscara
        if (numbers.length <= 10) {
            // (00) 0000-0000
            return numbers
                .replace(/(\d{2})(\d)/, '($1) $2')
                .replace(/(\d{4})(\d)/, '$1-$2')
                .slice(0, 14);
        } else {
            // (00) 00000-0000
            return numbers
                .replace(/(\d{2})(\d)/, '($1) $2')
                .replace(/(\d{5})(\d)/, '$1-$2')
                .slice(0, 15);
        }
    };

    // Máscara para CPF
    const cpfMask = (value) => {
        if (!value) return '';

        const numbers = value.replace(/\D/g, '');

        return numbers
            .replace(/(\d{3})(\d)/, '$1.$2')
            .replace(/(\d{3})(\d)/, '$1.$2')
            .replace(/(\d{3})(\d{1,2})$/, '$1-$2')
            .slice(0, 14);
    };

    // Máscara para CNPJ
    const cnpjMask = (value) => {
        if (!value) return '';

        const numbers = value.replace(/\D/g, '');

        return numbers
            .replace(/(\d{2})(\d)/, '$1.$2')
            .replace(/(\d{3})(\d)/, '$1.$2')
            .replace(/(\d{3})(\d)/, '$1/$2')
            .replace(/(\d{4})(\d{1,2})$/, '$1-$2')
            .slice(0, 18);
    };

    // Máscara para CEP
    const cepMask = (value) => {
        if (!value) return '';

        const numbers = value.replace(/\D/g, '');

        return numbers
            .replace(/(\d{5})(\d)/, '$1-$2')
            .slice(0, 9);
    };

    // Máscara para moeda (Real)
    const currencyMask = (value) => {
        if (!value) return '';

        const numbers = value.replace(/\D/g, '');
        const amount = (parseInt(numbers) / 100).toFixed(2);

        return amount.replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    };

    // Máscara para data (DD/MM/YYYY)
    const dateMask = (value) => {
        if (!value) return '';

        const numbers = value.replace(/\D/g, '');

        return numbers
            .replace(/(\d{2})(\d)/, '$1/$2')
            .replace(/(\d{2})(\d)/, '$1/$2')
            .slice(0, 10);
    };

    // Máscara para cartão de crédito
    const creditCardMask = (value) => {
        if (!value) return '';

        const numbers = value.replace(/\D/g, '');

        return numbers
            .replace(/(\d{4})(\d)/, '$1 $2')
            .replace(/(\d{4})(\d)/, '$1 $2')
            .replace(/(\d{4})(\d)/, '$1 $2')
            .slice(0, 19);
    };

    // Remove máscara (retorna só números)
    const unmask = (value) => {
        if (!value) return '';
        return value.replace(/\D/g, '');
    };

    // Aplica máscara baseado no tipo
    const applyMask = (value, type) => {
        const masks = {
            phone: phoneMask,
            cpf: cpfMask,
            cnpj: cnpjMask,
            cep: cepMask,
            currency: currencyMask,
            date: dateMask,
            creditCard: creditCardMask,
        };

        const maskFn = masks[type];
        return maskFn ? maskFn(value) : value;
    };

    return {
        phoneMask,
        cpfMask,
        cnpjMask,
        cepMask,
        currencyMask,
        dateMask,
        creditCardMask,
        unmask,
        applyMask,
    };
}
