<script setup>
import { ref, watch, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    title: String,
    size: {
        type: String,
        default: 'md',
        validator: (value) => ['sm', 'md', 'lg', 'xl', 'full'].includes(value),
    },
    closeable: {
        type: Boolean,
        default: true,
    },
    persistent: Boolean,
    showFooter: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close', 'confirm', 'cancel']);

const modalRef = ref(null);

const close = () => {
    if (!props.persistent) {
        emit('close');
    }
};

const confirm = () => {
    emit('confirm');
    if (!props.persistent) {
        close();
    }
};

const cancel = () => {
    emit('cancel');
    close();
};

const handleClickOutside = (event) => {
    if (modalRef.value && !modalRef.value.contains(event.target) && !props.persistent) {
        close();
    }
};

const handleEscape = (event) => {
    if (event.key === 'Escape' && props.show && props.closeable && !props.persistent) {
        close();
    }
};

watch(() => props.show, (newValue) => {
    if (newValue) {
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = '';
    }
});

onMounted(() => {
    document.addEventListener('keydown', handleEscape);
});

onUnmounted(() => {
    document.removeEventListener('keydown', handleEscape);
    document.body.style.overflow = '';
});
</script>

<template>
    <Teleport to="body">
        <Transition name="modal-fade">
            <div
                v-if="show"
                class="modal"
                @click="handleClickOutside"
            >
                <Transition name="modal-slide">
                    <div
                        v-if="show"
                        ref="modalRef"
                        :class="[
                            'modal__content',
                            `modal__content--${size}`,
                        ]"
                        @click.stop
                    >
                        <!-- Header -->
                        <div v-if="title || $slots.header || closeable" class="modal__header">
                            <h2 v-if="title" class="modal__title">
                                {{ title }}
                            </h2>
                            <slot name="header" />

                            <button
                                v-if="closeable"
                                type="button"
                                class="modal__close"
                                @click="close"
                            >
                                <i class="fas fa-times"></i>
                            </button>
                        </div>

                        <!-- Body -->
                        <div class="modal__body">
                            <slot />
                        </div>

                        <!-- Footer -->
                        <div v-if="showFooter || $slots.footer" class="modal__footer">
                            <slot name="footer">
                                <div class="modal__actions">
                                    <slot name="actions">
                                        <button
                                            type="button"
                                            class="btn btn--secondary"
                                            @click="cancel"
                                        >
                                            Cancelar
                                        </button>
                                        <button
                                            type="button"
                                            class="btn btn--accent"
                                            @click="confirm"
                                        >
                                            Confirmar
                                        </button>
                                    </slot>
                                </div>
                            </slot>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>

<style>
/* Transitions */
.modal-fade-enter-active,
.modal-fade-leave-active {
    transition: opacity 200ms ease;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
    opacity: 0;
}

.modal-slide-enter-active,
.modal-slide-leave-active {
    transition: transform 200ms cubic-bezier(0.16, 1, 0.3, 1);
}

.modal-slide-enter-from,
.modal-slide-leave-to {
    transform: translateY(-20px);
}

/* Modal */
.modal {
    position: fixed;
    inset: 0;
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    background: rgba(0, 0, 0, 0.7);
    backdrop-filter: blur(2px);
}

.modal__content {
    position: relative;
    display: flex;
    flex-direction: column;
    width: 100%;
    max-height: calc(100vh - 40px);
    background: var(--bg-primary);
    border: 2px solid var(--border-color);
    overflow: hidden;
}

/* Sizes */
.modal__content--sm {
    max-width: 400px;
}

.modal__content--md {
    max-width: 600px;
}

.modal__content--lg {
    max-width: 800px;
}

.modal__content--xl {
    max-width: 1200px;
}

.modal__content--full {
    max-width: calc(100vw - 40px);
    max-height: calc(100vh - 40px);
}

/* Header */
.modal__header {
    position: relative;
    padding: 24px;
    border-bottom: 2px solid var(--border-color);
}

.modal__title {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 20px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--text-primary);
    margin: 0;
    padding-right: 40px;
}

.modal__close {
    position: absolute;
    top: 24px;
    right: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    padding: 0;
    background: transparent;
    border: 2px solid var(--border-color);
    color: var(--text-primary);
    cursor: pointer;
    transition: all 180ms ease;
}

.modal__close:hover {
    background: var(--color-error);
    border-color: var(--color-error);
    color: #fff;
}

/* Body */
.modal__body {
    flex: 1;
    padding: 24px;
    overflow-y: auto;
    font-family: 'Inter', sans-serif;
    font-size: 14px;
    line-height: 1.6;
    color: var(--text-primary);
}

/* Footer */
.modal__footer {
    padding: 24px;
    border-top: 2px solid var(--border-color);
    background: var(--bg-secondary);
}

.modal__actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 12px;
}

/* Scrollbar */
.modal__body::-webkit-scrollbar {
    width: 8px;
}

.modal__body::-webkit-scrollbar-track {
    background: var(--bg-primary);
}

.modal__body::-webkit-scrollbar-thumb {
    background: var(--border-color);
    border: 2px solid var(--bg-primary);
}

.modal__body::-webkit-scrollbar-thumb:hover {
    background: var(--text-muted);
}

/* Dark mode */
:root[data-theme='dark'] .modal__content {
    background: var(--bg-secondary);
}
</style>
