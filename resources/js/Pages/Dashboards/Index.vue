<template>
    <MainLayout>
        <div class="page-container">
            <div class="page-header">
                <div class="page-header__content">
                    <h1 class="page-header__title">DASHBOARDS</h1>
                    <p class="page-header__subtitle">Visualize e analise métricas personalizadas</p>
                </div>
                <div class="page-header__actions">
                    <button class="btn">
                        <i class="fas fa-plus"></i>
                        Novo Dashboard
                    </button>
                </div>
            </div>

            <div class="grid grid--4">
                <div class="stat-card">
                    <div class="stat-card__icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <p class="stat-card__label">Dashboards Ativos</p>
                    <h2 class="stat-card__value">6</h2>
                </div>

                <div class="stat-card">
                    <div class="stat-card__icon">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <p class="stat-card__label">Widgets</p>
                    <h2 class="stat-card__value">24</h2>
                </div>

                <div class="stat-card">
                    <div class="stat-card__icon">
                        <i class="fas fa-eye"></i>
                    </div>
                    <p class="stat-card__label">Visualizações</p>
                    <h2 class="stat-card__value">1.2K</h2>
                </div>

                <div class="stat-card">
                    <div class="stat-card__icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <p class="stat-card__label">Compartilhados</p>
                    <h2 class="stat-card__value">3</h2>
                </div>
            </div>

            <div class="empty-state" style="margin-top: 32px;">
                <div class="empty-state__icon">
                    <i class="fas fa-chart-pie"></i>
                </div>
                <h3 class="empty-state__title">Em Desenvolvimento</h3>
                <p class="empty-state__description">O sistema de dashboards personalizados está sendo construído.</p>
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
