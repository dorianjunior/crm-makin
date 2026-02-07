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

<!-- Styles moved to resources/scss/_leads.scss -->
