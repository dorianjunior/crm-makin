import { ref, computed, onMounted, onUnmounted } from 'vue';
import { leadService } from '@/services/leadService';

export function useLeads(options = {}) {
    const {
        autoRefresh = false,
        refreshInterval = 30000, // 30 segundos
        initialFilters = {},
    } = options;

    const leads = ref({ data: [], meta: {} });
    const loading = ref(false);
    const error = ref(null);
    const filters = ref(initialFilters);
    const sources = ref([]);
    const stats = ref({
        total: 0,
        new_this_month: 0,
        qualified: 0,
        conversion_rate: 0,
    });

    let refreshTimer = null;

    /**
     * Load leads from API
     */
    const loadLeads = async (showLoading = true) => {
        try {
            if (showLoading) {
                loading.value = true;
            }
            error.value = null;

            const response = await leadService.getLeads(filters.value);

            leads.value = {
                data: response.data || [],
                meta: response.meta || {},
            };

            // Update stats if available from API response
            if (response.stats) {
                stats.value = response.stats;
            }

            return response;
        } catch (err) {
            error.value = err.message || 'Erro ao carregar leads';
            console.error('Error loading leads:', err);
            throw err;
        } finally {
            loading.value = false;
        }
    };

    /**
     * Create a new lead
     */
    const createLead = async (data) => {
        try {
            loading.value = true;
            const response = await leadService.createLead(data);
            await loadLeads(false); // Reload list without showing loader
            return response;
        } catch (err) {
            error.value = err.response?.data?.message || 'Erro ao criar lead';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    /**
     * Update a lead
     */
    const updateLead = async (id, data) => {
        try {
            loading.value = true;
            const response = await leadService.updateLead(id, data);
            await loadLeads(false); // Reload list without showing loader
            return response;
        } catch (err) {
            error.value = err.response?.data?.message || 'Erro ao atualizar lead';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    /**
     * Delete a lead
     */
    const deleteLead = async (id) => {
        try {
            loading.value = true;
            await leadService.deleteLead(id);
            await loadLeads(false); // Reload list without showing loader
        } catch (err) {
            error.value = err.response?.data?.message || 'Erro ao deletar lead';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    /**
     * Assign lead to user
     */
    const assignLead = async (id, userId) => {
        try {
            const response = await leadService.assignLead(id, userId);
            await loadLeads(false);
            return response;
        } catch (err) {
            error.value = err.response?.data?.message || 'Erro ao atribuir lead';
            throw err;
        }
    };

    /**
     * Change lead status
     */
    const changeLeadStatus = async (id, status) => {
        try {
            const response = await leadService.changeStatus(id, status);
            await loadLeads(false);
            return response;
        } catch (err) {
            error.value = err.response?.data?.message || 'Erro ao alterar status';
            throw err;
        }
    };

    /**
     * Load sources
     */
    const loadSources = async () => {
        try {
            const response = await leadService.getSources();
            sources.value = response.data || [];
        } catch (err) {
            console.error('Error loading sources:', err);
        }
    };

    /**
     * Update filters and reload
     */
    const updateFilters = async (newFilters) => {
        filters.value = { ...filters.value, ...newFilters };
        await loadLeads();
    };

    /**
     * Clear all filters
     */
    const clearFilters = async () => {
        filters.value = {};
        await loadLeads();
    };

    /**
     * Start auto-refresh
     */
    const startAutoRefresh = () => {
        if (refreshTimer) {
            clearInterval(refreshTimer);
        }

        refreshTimer = setInterval(() => {
            loadLeads(false); // Refresh sem mostrar loading
        }, refreshInterval);
    };

    /**
     * Stop auto-refresh
     */
    const stopAutoRefresh = () => {
        if (refreshTimer) {
            clearInterval(refreshTimer);
            refreshTimer = null;
        }
    };

    /**
     * Refresh manually
     */
    const refresh = () => {
        return loadLeads(false);
    };

    // Computed
    const hasLeads = computed(() => leads.value.data.length > 0);
    const totalLeads = computed(() => leads.value.meta?.total || 0);
    const currentPage = computed(() => leads.value.meta?.current_page || 1);
    const lastPage = computed(() => leads.value.meta?.last_page || 1);

    // Lifecycle
    onMounted(async () => {
        await loadLeads();
        await loadSources();

        if (autoRefresh) {
            startAutoRefresh();
        }
    });

    onUnmounted(() => {
        stopAutoRefresh();
    });

    return {
        // State
        leads,
        loading,
        error,
        filters,
        sources,
        stats,

        // Computed
        hasLeads,
        totalLeads,
        currentPage,
        lastPage,

        // Methods
        loadLeads,
        createLead,
        updateLead,
        deleteLead,
        assignLead,
        changeLeadStatus,
        loadSources,
        updateFilters,
        clearFilters,
        refresh,
        startAutoRefresh,
        stopAutoRefresh,
    };
}
