import api from './api';

export const leadService = {
    /**
     * Get all leads with filters
     */
    async getLeads(params = {}) {
        const response = await api.get('/crm/leads', { params });
        return response.data;
    },

    /**
     * Get a single lead
     */
    async getLead(id) {
        const response = await api.get(`/crm/leads/${id}`);
        return response.data;
    },

    /**
     * Create a new lead
     */
    async createLead(data) {
        const response = await api.post('/crm/leads', data);
        return response.data;
    },

    /**
     * Update a lead
     */
    async updateLead(id, data) {
        const response = await api.put(`/crm/leads/${id}`, data);
        return response.data;
    },

    /**
     * Delete a lead
     */
    async deleteLead(id) {
        await api.delete(`/crm/leads/${id}`);
    },

    /**
     * Assign lead to user
     */
    async assignLead(id, userId) {
        const response = await api.post(`/crm/leads/${id}/assign`, {
            user_id: userId,
        });
        return response.data;
    },

    /**
     * Change lead status
     */
    async changeStatus(id, status) {
        const response = await api.post(`/crm/leads/${id}/change-status`, {
            status,
        });
        return response.data;
    },

    /**
     * Get lead sources
     */
    async getSources() {
        const response = await api.get('/crm/lead-sources');
        return response.data;
    },
};
