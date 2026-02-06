<template>
    <MainLayout title="Visualizar Lead">
        <template #breadcrumbs>
            <Breadcrumbs :items="breadcrumbs" />
        </template>

        <div class="page-container">
            <!-- Header -->
            <div class="page-header">
                <div>
                    <h1 class="page-title">{{ lead.name }}</h1>
                    <p class="page-subtitle">Detalhes do lead</p>
                </div>
                <div class="page-header__actions">
                    <button class="btn btn--secondary" @click="goBack">
                        <i class="fas fa-arrow-left"></i>
                        Voltar
                    </button>
                    <button class="btn" @click="editLead">
                        <i class="fas fa-edit"></i>
                        Editar
                    </button>
                </div>
            </div>

            <div class="content-grid">
                <!-- Informações Principais -->
                <div class="card main-info">
                    <div class="card__header">
                        <h3 class="card__title">INFORMAÇÕES PRINCIPAIS</h3>
                        <span :class="['badge', `badge--${getStatusVariant(lead.status)}`]">
                            {{ statusLabel(lead.status) }}
                        </span>
                    </div>
                    <div class="card__body">
                        <div class="info-grid">
                            <div class="info-item">
                                <div class="info-item__label">
                                    <i class="fas fa-user"></i>
                                    Nome
                                </div>
                                <div class="info-item__value">{{ lead.name }}</div>
                            </div>

                            <div class="info-item" v-if="lead.company">
                                <div class="info-item__label">
                                    <i class="fas fa-building"></i>
                                    Empresa
                                </div>
                                <div class="info-item__value">{{ lead.company }}</div>
                            </div>

                            <div class="info-item" v-if="lead.email">
                                <div class="info-item__label">
                                    <i class="fas fa-envelope"></i>
                                    Email
                                </div>
                                <div class="info-item__value">
                                    <a :href="`mailto:${lead.email}`" class="info-link">{{ lead.email }}</a>
                                </div>
                            </div>

                            <div class="info-item" v-if="lead.phone">
                                <div class="info-item__label">
                                    <i class="fas fa-phone"></i>
                                    Telefone
                                </div>
                                <div class="info-item__value">
                                    <a :href="`tel:${lead.phone}`" class="info-link">{{ lead.phone }}</a>
                                </div>
                            </div>

                            <div class="info-item" v-if="lead.source">
                                <div class="info-item__label">
                                    <i class="fas fa-code-branch"></i>
                                    Fonte
                                </div>
                                <div class="info-item__value">
                                    <span class="badge badge--neutral">{{ lead.source.name }}</span>
                                </div>
                            </div>

                            <div class="info-item" v-if="lead.assigned_user">
                                <div class="info-item__label">
                                    <i class="fas fa-user-circle"></i>
                                    Responsável
                                </div>
                                <div class="info-item__value">
                                    {{ lead.assigned_user.name }}
                                </div>
                            </div>

                            <div class="info-item">
                                <div class="info-item__label">
                                    <i class="fas fa-calendar-plus"></i>
                                    Criado em
                                </div>
                                <div class="info-item__value">{{ formatDate(lead.created_at) }}</div>
                            </div>

                            <div class="info-item">
                                <div class="info-item__label">
                                    <i class="fas fa-calendar-check"></i>
                                    Atualizado em
                                </div>
                                <div class="info-item__value">{{ formatDate(lead.updated_at) }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Observações -->
                <div class="card notes-section" v-if="lead.notes">
                    <div class="card__header">
                        <h3 class="card__title">OBSERVAÇÕES</h3>
                    </div>
                    <div class="card__body">
                        <div class="notes-content">
                            {{ lead.notes }}
                        </div>
                    </div>
                </div>

                <!-- Ações Rápidas -->
                <div class="card quick-actions">
                    <div class="card__header">
                        <h3 class="card__title">AÇÕES RÁPIDAS</h3>
                    </div>
                    <div class="card__body">
                        <div class="actions-grid">
                            <button class="action-card" v-if="lead.email" @click="sendEmail">
                                <i class="fas fa-envelope"></i>
                                <span>Enviar Email</span>
                            </button>
                            <button class="action-card" v-if="lead.phone" @click="makeCall">
                                <i class="fas fa-phone"></i>
                                <span>Ligar</span>
                            </button>
                            <button class="action-card" v-if="lead.phone" @click="sendWhatsapp">
                                <i class="fab fa-whatsapp"></i>
                                <span>WhatsApp</span>
                            </button>
                            <button class="action-card" @click="addNote">
                                <i class="fas fa-sticky-note"></i>
                                <span>Adicionar Nota</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </MainLayout>
</template>

<script setup>
import { router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import { useAlert } from '@/composables/useAlert';

const alert = useAlert();

const props = defineProps({
    lead: Object,
});

const breadcrumbs = [
    { name: 'Dashboard', href: '/dashboard' },
    { name: 'Leads', href: '/leads' },
    { name: props.lead.name }
];

const getStatusVariant = (status) => {
    const variants = {
        new: 'info',
        contacted: 'primary',
        qualified: 'success',
        negotiation: 'warning',
        won: 'success',
        lost: 'danger',
    };
    return variants[status] || 'default';
};

const statusLabel = (status) => {
    const labels = {
        new: 'Novo',
        contacted: 'Contatado',
        qualified: 'Qualificado',
        negotiation: 'Negociação',
        won: 'Ganho',
        lost: 'Perdido',
    };
    return labels[status] || status;
};

const formatDate = (date) => {
    return new Date(date).toLocaleString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const goBack = () => {
    router.visit('/leads');
};

const editLead = () => {
    router.visit(`/leads/${props.lead.id}/edit`);
};

const sendEmail = () => {
    window.location.href = `mailto:${props.lead.email}`;
};

const makeCall = () => {
    window.location.href = `tel:${props.lead.phone}`;
};

const sendWhatsapp = () => {
    const phone = props.lead.phone.replace(/\D/g, '');
    window.open(`https://wa.me/55${phone}`, '_blank');
};

const addNote = () => {
    alert.info('Funcionalidade em desenvolvimento');
};
</script>

<style scoped lang="scss">
.page-container {
    padding: 32px;
}

.content-grid {
    display: grid;
    gap: 24px;
}

.main-info {
    grid-column: 1;
}

.card__header {
    padding: 24px;
    border-bottom: 2px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card__title {
    font-size: 14px;
    font-weight: 700;
    letter-spacing: 1px;
    color: var(--text-primary);
    margin: 0;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 24px;
}

.info-item {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.info-item__label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--text-secondary);

    i {
        color: #FF6B35;
        width: 16px;
    }
}

.info-item__value {
    font-size: 14px;
    color: var(--text-primary);
    font-weight: 500;
    padding-left: 24px;
}

.info-link {
    color: #FF6B35;
    text-decoration: none;
    transition: all 0.2s ease;

    &:hover {
        text-decoration: underline;
    }
}

.notes-section {
    .notes-content {
        white-space: pre-wrap;
        line-height: 1.6;
        color: var(--text-primary);
    }
}

.quick-actions {
    .actions-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 16px;
    }
}

.action-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
    padding: 24px;
    border: 2px solid var(--border-color);
    background: var(--bg-primary);
    color: var(--text-primary);
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 13px;
    font-weight: 600;

    i {
        font-size: 24px;
        color: #FF6B35;
    }

    &:hover {
        border-color: #FF6B35;
        transform: translateY(-2px);

        i {
            transform: scale(1.1);
        }
    }
}

.page-header__actions {
    display: flex;
    gap: 12px;
}

@media (max-width: 768px) {
    .page-container {
        padding: 16px;
    }

    .info-grid {
        grid-template-columns: 1fr;
    }

    .actions-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>
