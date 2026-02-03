<template>
    <MainLayout>
        <div class="page-container">
            <div class="page-header">
                <div class="page-header__content">
                    <h1 class="page-header__title">RELATÓRIOS</h1>
                    <p class="page-header__subtitle">Gere relatórios detalhados e análises</p>
                </div>
                <div class="page-header__actions">
                    <button class="btn btn--secondary">
                        <i class="fas fa-filter"></i>
                        Filtrar
                    </button>
                    <button class="btn">
                        <i class="fas fa-file-export"></i>
                        Novo Relatório
                    </button>
                </div>
            </div>

            <div class="grid grid--4">
                <div class="stat-card">
                    <div class="stat-card__icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <p class="stat-card__label">Relatórios Salvos</p>
                    <h2 class="stat-card__value">18</h2>
                </div>

                <div class="stat-card">
                    <div class="stat-card__icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <p class="stat-card__label">Agendados</p>
                    <h2 class="stat-card__value">7</h2>
                </div>

                <div class="stat-card">
                    <div class="stat-card__icon">
                        <i class="fas fa-download"></i>
                    </div>
                    <p class="stat-card__label">Downloads</p>
                    <h2 class="stat-card__value">156</h2>
                </div>

                <div class="stat-card">
                    <div class="stat-card__icon">
                        <i class="fas fa-share-alt"></i>
                    </div>
                    <p class="stat-card__label">Compartilhados</p>
                    <h2 class="stat-card__value">12</h2>
                </div>
            </div>

            <div class="empty-state" style="margin-top: 32px;">
                <div class="empty-state__icon">
                    <i class="fas fa-file-invoice"></i>
                </div>
                <h3 class="empty-state__title">Em Desenvolvimento</h3>
                <p class="empty-state__description">O módulo de relatórios avançados está sendo desenvolvido.</p>
            </div>
        </div>
    </MainLayout>
</template>

<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
</script>

<style scoped lang="scss">
.page-container {
    padding: 32px;
}
</style>
