<template>
    <MainLayout title="Ajuda">
        <div class="page-container">
            <div class="page-header">
                <div class="page-header__content">
                    <h1 class="page-header__title">AJUDA</h1>
                    <p class="page-header__subtitle">Central de ajuda e documentação</p>
                </div>
            </div>

            <div class="grid grid--3">
                <div class="card">
                    <div class="card__header">
                        <div class="section-icon">
                            <i class="fas fa-book"></i>
                        </div>
                        <h3 class="card__title">Documentação</h3>
                    </div>
                    <div class="card__body">
                        <p>Acesse a documentação completa do sistema com guias e tutoriais.</p>
                    </div>
                    <div class="card__footer">
                        <button class="btn btn--secondary btn--small">
                            <i class="fas fa-arrow-right"></i>
                            Acessar
                        </button>
                    </div>
                </div>

                <div class="card">
                    <div class="card__header">
                        <div class="section-icon">
                            <i class="fas fa-video"></i>
                        </div>
                        <h3 class="card__title">Vídeos</h3>
                    </div>
                    <div class="card__body">
                        <p>Assista tutoriais em vídeo para aprender a usar todas as funcionalidades.</p>
                    </div>
                    <div class="card__footer">
                        <button class="btn btn--secondary btn--small">
                            <i class="fas fa-arrow-right"></i>
                            Ver Vídeos
                        </button>
                    </div>
                </div>

                <div class="card">
                    <div class="card__header">
                        <div class="section-icon">
                            <i class="fas fa-headset"></i>
                        </div>
                        <h3 class="card__title">Suporte</h3>
                    </div>
                    <div class="card__body">
                        <p>Entre em contato com nossa equipe de suporte para obter ajuda personalizada.</p>
                    </div>
                    <div class="card__footer">
                        <button class="btn btn--secondary btn--small">
                            <i class="fas fa-arrow-right"></i>
                            Contatar
                        </button>
                    </div>
                </div>
            </div>

            <div class="empty-state" style="margin-top: 32px;">
                <div class="empty-state__icon">
                    <i class="fas fa-wrench"></i>
                </div>
                <h3 class="empty-state__title">Em Desenvolvimento</h3>
                <p class="empty-state__description">Esta seção está sendo construída e estará disponível em breve.</p>
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

.section-icon {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #FF6B35;
    color: var(--bg-primary);
    font-size: 24px;
}
</style>
