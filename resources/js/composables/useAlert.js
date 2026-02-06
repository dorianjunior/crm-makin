// =============================================================================
// useAlert - SweetAlert2 Composable with Brutalist Theme
// =============================================================================

import Swal from 'sweetalert2';

/**
 * Composable for brutalist-styled alerts and confirmations
 * Supports dark/light themes automatically
 */
export function useAlert() {
    /**
     * Get current theme for alert styling
     */
    const isDark = () => {
        return document.documentElement.classList.contains('dark') ||
               document.documentElement.getAttribute('data-theme') === 'dark';
    };

    /**
     * Base configuration for brutalist alerts
     */
    const getBaseConfig = (includeExtraButtons = false) => {
        const config = {
            customClass: {
                popup: 'brutalist-popup',
                title: 'brutalist-title',
                htmlContainer: 'brutalist-content',
                confirmButton: 'brutalist-btn brutalist-btn--confirm',
            },
            buttonsStyling: false,
            showClass: {
                popup: 'brutalist-show',
            },
            hideClass: {
                popup: 'brutalist-hide',
            },
        };

        // Adiciona classes de botões extras apenas se necessário
        if (includeExtraButtons) {
            config.customClass.cancelButton = 'brutalist-btn brutalist-btn--cancel';
            config.customClass.denyButton = 'brutalist-btn brutalist-btn--deny';
        }

        return config;
    };

    /**
     * Success alert
     */
    const success = (title, message = '') => {
        return Swal.fire({
            ...getBaseConfig(),
            icon: 'success',
            title,
            text: message,
            confirmButtonText: 'OK',
            showCancelButton: false,
            showDenyButton: false,
            iconColor: '#10b981',
        });
    };

    /**
     * Error alert
     */
    const error = (title, message = '') => {
        return Swal.fire({
            ...getBaseConfig(),
            icon: 'error',
            title,
            text: message,
            confirmButtonText: 'OK',
            showCancelButton: false,
            showDenyButton: false,
            iconColor: '#ef4444',
        });
    };

    /**
     * Warning alert
     */
    const warning = (title, message = '') => {
        return Swal.fire({
            ...getBaseConfig(),
            icon: 'warning',
            title,
            text: message,
            confirmButtonText: 'OK',
            showCancelButton: false,
            showDenyButton: false,
            iconColor: '#f59e0b',
        });
    };

    /**
     * Info alert
     */
    const info = (title, message = '') => {
        return Swal.fire({
            ...getBaseConfig(),
            icon: 'info',
            title,
            text: message,
            confirmButtonText: 'OK',
            showCancelButton: false,
            showDenyButton: false,
            iconColor: '#3b82f6',
        });
    };

    /**
     * Confirmation dialog (delete action)
     */
    const confirmDelete = (title = 'Tem certeza?', message = 'Esta ação não pode ser desfeita!') => {
        return Swal.fire({
            ...getBaseConfig(true),
            icon: 'warning',
            title,
            text: message,
            confirmButtonText: 'Sim, excluir',
            cancelButtonText: 'Cancelar',
            denyButtonText: 'Não',
            showCancelButton: true,
            showDenyButton: false,
            iconColor: '#ef4444',
            reverseButtons: true,
        });
    };

    /**
     * Generic confirmation dialog
     */
    const confirm = (title, message = '', confirmText = 'Confirmar', cancelText = 'Cancelar') => {
        return Swal.fire({
            ...getBaseConfig(true),
            icon: 'question',
            title,
            text: message,
            showCancelButton: true,
            showDenyButton: false,
            confirmButtonText: confirmText,
            cancelButtonText: cancelText,
            iconColor: '#FF6B35',
            reverseButtons: true,
        });
    };

    /**
     * Loading alert
     */
    const loading = (title = 'Processando...', message = 'Por favor, aguarde.') => {
        return Swal.fire({
            ...getBaseConfig(),
            title,
            text: message,
            allowOutsideClick: false,
            allowEscapeKey: false,
            showDenyButton: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            },
        });
    };

    /**
     * Close any open alert
     */
    const close = () => {
        Swal.close();
    };

    /**
     * Toast notification (small, bottom-right)
     */
    const toast = (message, type = 'success', duration = 3000) => {
        const Toast = Swal.mixin({
            toast: true,
            position: 'bottom-end',
            showConfirmButton: false,
            timer: duration,
            timerProgressBar: true,
            customClass: {
                popup: 'brutalist-toast',
                title: 'brutalist-toast-title',
            },
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            },
        });

        const iconColors = {
            success: '#10b981',
            error: '#ef4444',
            warning: '#f59e0b',
            info: '#3b82f6',
        };

        return Toast.fire({
            icon: type,
            title: message,
            iconColor: iconColors[type] || '#FF6B35',
        });
    };

    return {
        success,
        error,
        warning,
        info,
        confirm,
        confirmDelete,
        loading,
        close,
        toast,
    };
}
