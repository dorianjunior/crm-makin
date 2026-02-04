// resources/js/composables/useLeadsWebSocket.js
import { ref, computed, onMounted, onUnmounted } from 'vue';

/**
 * Composable para Leads com WebSocket em tempo real
 *
 * Pré-requisitos:
 * 1. Laravel Echo configurado (resources/js/bootstrap.js)
 * 2. Soketi/Pusher rodando
 * 3. Events disparados no backend
 */
export function useLeadsWebSocket(props) {
    const leads = ref(props.leads?.data || []);
    const stats = ref(props.stats || {});
    const wsConnected = ref(false);

    const companyId = computed(() => window.auth?.user?.company_id);

    /**
     * Conectar ao canal WebSocket da empresa
     */
    const connectWebSocket = () => {
        if (!window.Echo || !companyId.value) {
            console.warn('Laravel Echo não está disponível');
            return;
        }

        const channel = window.Echo.channel(`company.${companyId.value}`);

        // Evento de conexão
        channel.on('pusher:subscription_succeeded', () => {
            wsConnected.value = true;
            console.log('✅ WebSocket conectado ao canal:', `company.${companyId.value}`);
        });

        // Evento: Novo lead criado
        channel.listen('.lead.created', (event) => {
            console.log('🎉 Novo lead recebido via WebSocket:', event);

            // Adicionar no topo da lista
            leads.value.unshift(event);

            // Atualizar estatísticas
            stats.value.total++;
            if (event.status === 'new') {
                stats.value.new_this_month++;
            }

            // Notificação visual (opcional)
            if (window.useAlert) {
                window.useAlert().success(`Novo lead: ${event.name}`);
            }
        });

        // Evento: Lead atualizado
        channel.listen('.lead.updated', (event) => {
            console.log('📝 Lead atualizado via WebSocket:', event);

            const index = leads.value.findIndex(l => l.id === event.id);
            if (index !== -1) {
                leads.value[index] = event;
            }
        });

        // Evento: Lead deletado
        channel.listen('.lead.deleted', (event) => {
            console.log('🗑️ Lead deletado via WebSocket:', event);

            const index = leads.value.findIndex(l => l.id === event.id);
            if (index !== -1) {
                leads.value.splice(index, 1);
                stats.value.total--;
            }
        });

        // Erro na conexão
        window.Echo.connector.pusher.connection.bind('error', (err) => {
            console.error('❌ Erro WebSocket:', err);
            wsConnected.value = false;
        });
    };

    /**
     * Desconectar do canal
     */
    const disconnectWebSocket = () => {
        if (window.Echo && companyId.value) {
            window.Echo.leave(`company.${companyId.value}`);
            wsConnected.value = false;
            console.log('🔌 Desconectado do WebSocket');
        }
    };

    // Lifecycle
    onMounted(() => {
        connectWebSocket();
    });

    onUnmounted(() => {
        disconnectWebSocket();
    });

    return {
        leads,
        stats,
        wsConnected,
        connectWebSocket,
        disconnectWebSocket,
    };
}

// Exemplo de uso no componente:
/*
<script setup>
import { useLeadsWebSocket } from '@/composables/useLeadsWebSocket';

const props = defineProps(['leads', 'stats']);

const {
    leads,
    stats,
    wsConnected
} = useLeadsWebSocket(props);
</script>

<template>
    <div>
        <div v-if="wsConnected" class="ws-indicator">
            🟢 Tempo Real Ativo
        </div>

        <!-- Sua lista de leads aqui -->
    </div>
</template>
*/
