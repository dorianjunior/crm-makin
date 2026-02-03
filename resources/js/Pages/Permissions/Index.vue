<template>
    <MainLayout>
        <div class="page-container">
            <div class="page-header">
                <div class="page-header__content">
                    <h1 class="page-header__title">PERMISSÕES</h1>
                    <p class="page-header__subtitle">Gerencie permissões e controle de acesso</p>
                </div>
                <div class="page-header__actions">
                    <button class="btn">
                        <i class="fas fa-plus"></i>
                        Nova Permissão
                    </button>
                </div>
            </div>

            <div class="grid grid--4">
                <div class="stat-card">
                    <div class="stat-card__icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <p class="stat-card__label">Total Permissões</p>
                    <h2 class="stat-card__value">48</h2>
                </div>

                <div class="stat-card">
                    <div class="stat-card__icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <p class="stat-card__label">Grupos</p>
                    <h2 class="stat-card__value">12</h2>
                </div>

                <div class="stat-card">
                    <div class="stat-card__icon">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <p class="stat-card__label">Roles Ativos</p>
                    <h2 class="stat-card__value">8</h2>
                </div>

                <div class="stat-card">
                    <div class="stat-card__icon">
                        <i class="fas fa-lock"></i>
                    </div>
                    <p class="stat-card__label">Restrições</p>
                    <h2 class="stat-card__value">24</h2>
                </div>
            </div>

            <div class="empty-state" style="margin-top: 32px;">
                <div class="empty-state__icon">
                    <i class="fas fa-lock"></i>
                </div>
                <h3 class="empty-state__title">Em Desenvolvimento</h3>
                <p class="empty-state__description">O sistema de permissões está sendo implementado.</p>
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
