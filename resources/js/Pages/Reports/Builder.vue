<template>
  <MainLayout>
    <div class="reports-builder">
      <!-- Header -->
      <div class="page-header">
        <div>
          <h1 class="page-title">
            <i class="fa fa-chart-bar"></i>
            Construtor de Relatórios
          </h1>
          <Breadcrumbs :items="breadcrumbs" />
        </div>
        <div class="header-actions">
          <Button
            label="Meus Relatórios"
            icon="fa fa-folder"
            @click="showSavedReports = true"
            outlined
          />
          <Button
            label="Exportar"
            icon="fa fa-download"
            @click="exportReport"
            :disabled="!hasData"
            outlined
          />
          <Button
            label="Gerar Relatório"
            icon="fa fa-play"
            @click="generateReport"
            :loading="generating"
          />
        </div>
      </div>

      <div class="builder-container">
        <!-- Configuration Sidebar -->
        <div class="builder-sidebar">
          <div class="sidebar-section">
            <h3>Tipo de Relatório</h3>
            <div class="report-types">
              <button
                v-for="type in reportTypes"
                :key="type.value"
                :class="['type-btn', { active: reportConfig.type === type.value }]"
                @click="reportConfig.type = type.value"
              >
                <i :class="type.icon"></i>
                <span>{{ type.label }}</span>
              </button>
            </div>
          </div>

          <div class="sidebar-section">
            <h3>Período</h3>
            <div class="form-group">
              <select v-model="reportConfig.period" class="form-select">
                <option value="today">Hoje</option>
                <option value="yesterday">Ontem</option>
                <option value="this_week">Esta Semana</option>
                <option value="last_week">Semana Passada</option>
                <option value="this_month">Este Mês</option>
                <option value="last_month">Mês Passado</option>
                <option value="this_quarter">Este Trimestre</option>
                <option value="this_year">Este Ano</option>
                <option value="custom">Personalizado</option>
              </select>
            </div>

            <div v-if="reportConfig.period === 'custom'" class="date-range">
              <div class="form-group">
                <label>Data Inicial</label>
                <Input v-model="reportConfig.start_date" type="date" size="small" />
              </div>
              <div class="form-group">
                <label>Data Final</label>
                <Input v-model="reportConfig.end_date" type="date" size="small" />
              </div>
            </div>
          </div>

          <div class="sidebar-section">
            <h3>Métricas</h3>
            <div class="metrics-list">
              <label
                v-for="metric in availableMetrics"
                :key="metric.value"
                class="metric-checkbox"
              >
                <input
                  type="checkbox"
                  :value="metric.value"
                  v-model="reportConfig.metrics"
                />
                <span>{{ metric.label }}</span>
              </label>
            </div>
          </div>

          <div v-if="reportConfig.type === 'leads'" class="sidebar-section">
            <h3>Filtros de Leads</h3>
            <div class="form-group">
              <label>Status</label>
              <select v-model="reportConfig.filters.status" class="form-select" multiple>
                <option value="new">Novo</option>
                <option value="contacted">Contatado</option>
                <option value="qualified">Qualificado</option>
                <option value="proposal">Proposta</option>
                <option value="negotiation">Negociação</option>
                <option value="won">Ganho</option>
                <option value="lost">Perdido</option>
              </select>
            </div>

            <div class="form-group">
              <label>Fonte</label>
              <select v-model="reportConfig.filters.source" class="form-select">
                <option :value="null">Todas</option>
                <option value="website">Website</option>
                <option value="whatsapp">WhatsApp</option>
                <option value="instagram">Instagram</option>
                <option value="referral">Indicação</option>
                <option value="ads">Anúncios</option>
              </select>
            </div>

            <div class="form-group">
              <label>Pipeline</label>
              <select v-model="reportConfig.filters.pipeline_id" class="form-select">
                <option :value="null">Todos</option>
                <option v-for="pipeline in pipelines" :key="pipeline.id" :value="pipeline.id">
                  {{ pipeline.name }}
                </option>
              </select>
            </div>
          </div>

          <div v-if="reportConfig.type === 'activities'" class="sidebar-section">
            <h3>Filtros de Atividades</h3>
            <div class="form-group">
              <label>Tipo</label>
              <select v-model="reportConfig.filters.activity_type" class="form-select" multiple>
                <option value="call">Ligação</option>
                <option value="email">E-mail</option>
                <option value="meeting">Reunião</option>
                <option value="note">Nota</option>
                <option value="task">Tarefa</option>
              </select>
            </div>

            <div class="form-group">
              <label>Usuário</label>
              <select v-model="reportConfig.filters.user_id" class="form-select">
                <option :value="null">Todos</option>
                <option v-for="user in users" :key="user.id" :value="user.id">
                  {{ user.name }}
                </option>
              </select>
            </div>
          </div>

          <div class="sidebar-section">
            <h3>Visualização</h3>
            <div class="form-group">
              <label>Tipo de Gráfico</label>
              <select v-model="reportConfig.chart_type" class="form-select">
                <option value="bar">Barras</option>
                <option value="line">Linha</option>
                <option value="pie">Pizza</option>
                <option value="doughnut">Rosquinha</option>
                <option value="area">Área</option>
                <option value="table">Tabela</option>
              </select>
            </div>

            <div class="form-group">
              <label>Agrupar por</label>
              <select v-model="reportConfig.group_by" class="form-select">
                <option value="day">Dia</option>
                <option value="week">Semana</option>
                <option value="month">Mês</option>
                <option value="quarter">Trimestre</option>
                <option value="year">Ano</option>
              </select>
            </div>
          </div>

          <div class="sidebar-actions">
            <Button
              label="Salvar Relatório"
              icon="fa fa-save"
              @click="showSaveModal = true"
              :disabled="!hasData"
              outlined
              block
            />
            <Button
              label="Limpar"
              icon="fa fa-eraser"
              @click="clearReport"
              outlined
              block
              severity="secondary"
            />
          </div>
        </div>

        <!-- Report Preview -->
        <div class="builder-main">
          <div v-if="!hasData" class="empty-state">
            <i class="fa fa-chart-bar"></i>
            <h3>Configure seu relatório</h3>
            <p>Selecione o tipo de relatório, período e métricas desejadas</p>
            <Button
              label="Gerar Relatório"
              icon="fa fa-play"
              @click="generateReport"
            />
          </div>

          <template v-else>
            <!-- Report Stats -->
            <div class="report-stats">
              <div v-for="stat in reportData.stats" :key="stat.label" class="stat-card">
                <div class="stat-icon" :style="{ background: stat.color }">
                  <i :class="stat.icon"></i>
                </div>
                <div class="stat-content">
                  <h3>{{ stat.value }}</h3>
                  <p>{{ stat.label }}</p>
                  <span v-if="stat.trend" :class="['trend', stat.trend > 0 ? 'up' : 'down']">
                    <i :class="stat.trend > 0 ? 'fa fa-arrow-up' : 'fa fa-arrow-down'"></i>
                    {{ Math.abs(stat.trend) }}%
                  </span>
                </div>
              </div>
            </div>

            <!-- Chart Display -->
            <div class="report-chart-container">
              <div class="chart-header">
                <h2>{{ reportData.title }}</h2>
                <div class="chart-actions">
                  <button
                    v-for="chartType in ['bar', 'line', 'pie']"
                    :key="chartType"
                    :class="['chart-type-btn', { active: reportConfig.chart_type === chartType }]"
                    @click="reportConfig.chart_type = chartType; generateReport()"
                    :title="chartType"
                  >
                    <i :class="getChartIcon(chartType)"></i>
                  </button>
                </div>
              </div>

              <div v-if="reportConfig.chart_type === 'table'" class="report-table">
                <table>
                  <thead>
                    <tr>
                      <th v-for="column in reportData.table.columns" :key="column">
                        {{ column }}
                      </th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(row, index) in reportData.table.rows" :key="index">
                      <td v-for="(cell, cellIndex) in row" :key="cellIndex">
                        {{ cell }}
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <div v-else class="chart-canvas">
                <canvas ref="reportChart"></canvas>
              </div>
            </div>

            <!-- Detailed Data Table -->
            <div v-if="reportData.details" class="report-details">
              <div class="details-header">
                <h3>Detalhes</h3>
                <Button
                  label="Exportar CSV"
                  icon="fa fa-file-csv"
                  @click="exportCSV"
                  size="small"
                  outlined
                />
              </div>

              <div class="details-table">
                <table>
                  <thead>
                    <tr>
                      <th v-for="column in reportData.details.columns" :key="column.key">
                        {{ column.label }}
                      </th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="row in reportData.details.rows" :key="row.id">
                      <td v-for="column in reportData.details.columns" :key="column.key">
                        {{ formatCell(row[column.key], column.format) }}
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </template>
        </div>
      </div>
    </div>

    <!-- Save Report Modal -->
    <Modal
      v-model:visible="showSaveModal"
      title="Salvar Relatório"
      @confirm="saveReport"
    >
      <div class="modal-form">
        <div class="form-group">
          <label class="required">Nome do Relatório</label>
          <Input v-model="saveForm.name" placeholder="Ex: Leads do Mês" />
        </div>

        <div class="form-group">
          <label>Descrição</label>
          <textarea
            v-model="saveForm.description"
            class="form-textarea"
            rows="3"
            placeholder="Descreva o propósito deste relatório..."
          ></textarea>
        </div>

        <div class="form-group">
          <label class="checkbox-label">
            <input type="checkbox" v-model="saveForm.is_scheduled" />
            <span>Agendar envio automático</span>
          </label>
        </div>

        <div v-if="saveForm.is_scheduled" class="scheduled-config">
          <div class="form-group">
            <label>Frequência</label>
            <select v-model="saveForm.schedule_frequency" class="form-select">
              <option value="daily">Diário</option>
              <option value="weekly">Semanal</option>
              <option value="monthly">Mensal</option>
            </select>
          </div>

          <div class="form-group">
            <label>Enviar para</label>
            <Input v-model="saveForm.schedule_emails" placeholder="email@example.com, outro@example.com" />
            <small class="help-text">Separe múltiplos e-mails com vírgula</small>
          </div>
        </div>
      </div>
    </Modal>

    <!-- Saved Reports Modal -->
    <Modal
      v-model:visible="showSavedReports"
      title="Relatórios Salvos"
      size="large"
      :showFooter="false"
    >
      <div class="saved-reports-list">
        <div v-for="report in savedReports" :key="report.id" class="saved-report-item">
          <div class="report-icon">
            <i class="fa fa-chart-bar"></i>
          </div>
          <div class="report-info">
            <h4>{{ report.name }}</h4>
            <p>{{ report.description }}</p>
            <div class="report-meta">
              <span><i class="fa fa-clock"></i> {{ formatDate(report.updated_at) }}</span>
              <span v-if="report.is_scheduled">
                <i class="fa fa-calendar"></i> Agendado
              </span>
            </div>
          </div>
          <div class="report-actions">
            <Button
              label="Carregar"
              icon="fa fa-folder-open"
              @click="loadReport(report)"
              size="small"
              outlined
            />
            <button @click="deleteReport(report)" class="btn-icon-danger">
              <i class="fa fa-trash"></i>
            </button>
          </div>
        </div>

        <div v-if="savedReports.length === 0" class="empty-state">
          <i class="fa fa-folder-open"></i>
          <p>Nenhum relatório salvo</p>
        </div>
      </div>
    </Modal>
  </MainLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Button from '@/Components/Button.vue';
import Input from '@/Components/Input.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
  pipelines: Array,
  users: Array,
  savedReports: Array,
});

const breadcrumbs = [
  { label: 'Dashboard', to: '/dashboard' },
  { label: 'Relatórios', active: true }
];

const reportTypes = [
  { value: 'leads', label: 'Leads', icon: 'fa fa-users' },
  { value: 'activities', label: 'Atividades', icon: 'fa fa-history' },
  { value: 'revenue', label: 'Receita', icon: 'fa fa-dollar-sign' },
  { value: 'conversion', label: 'Conversão', icon: 'fa fa-chart-line' },
  { value: 'social', label: 'Social Media', icon: 'fa fa-share-alt' },
  { value: 'ai', label: 'IA', icon: 'fa fa-robot' },
];

const availableMetrics = [
  { value: 'total', label: 'Total' },
  { value: 'new', label: 'Novos' },
  { value: 'converted', label: 'Convertidos' },
  { value: 'revenue', label: 'Receita' },
  { value: 'avg_value', label: 'Valor Médio' },
  { value: 'response_time', label: 'Tempo de Resposta' },
];

const reportConfig = ref({
  type: 'leads',
  period: 'this_month',
  start_date: null,
  end_date: null,
  metrics: ['total', 'new', 'converted'],
  chart_type: 'bar',
  group_by: 'day',
  filters: {
    status: [],
    source: null,
    pipeline_id: null,
    activity_type: [],
    user_id: null,
  },
});

const reportData = ref(null);
const generating = ref(false);
const showSaveModal = ref(false);
const showSavedReports = ref(false);
const reportChart = ref(null);

const saveForm = ref({
  name: '',
  description: '',
  is_scheduled: false,
  schedule_frequency: 'weekly',
  schedule_emails: '',
});

const hasData = computed(() => reportData.value !== null);

const generateReport = async () => {
  generating.value = true;

  try {
    const response = await fetch('/api/reports/generate', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(reportConfig.value),
    });

    reportData.value = await response.json();

    // Initialize chart here (would use Chart.js or similar)
    console.log('Generated report data:', reportData.value);
  } catch (error) {
    console.error('Error generating report:', error);
  } finally {
    generating.value = false;
  }
};

const exportReport = () => {
  const params = new URLSearchParams(reportConfig.value);
  window.open(`/api/reports/export?${params}`, '_blank');
};

const exportCSV = () => {
  // Implement CSV export
  console.log('Exporting CSV');
};

const clearReport = () => {
  reportData.value = null;
  reportConfig.value = {
    type: 'leads',
    period: 'this_month',
    start_date: null,
    end_date: null,
    metrics: ['total', 'new', 'converted'],
    chart_type: 'bar',
    group_by: 'day',
    filters: {},
  };
};

const saveReport = () => {
  router.post('/reports', {
    ...saveForm.value,
    config: reportConfig.value,
  }, {
    onSuccess: () => {
      showSaveModal.value = false;
      saveForm.value = {
        name: '',
        description: '',
        is_scheduled: false,
        schedule_frequency: 'weekly',
        schedule_emails: '',
      };
    },
  });
};

const loadReport = (report) => {
  reportConfig.value = report.config;
  showSavedReports.value = false;
  generateReport();
};

const deleteReport = (report) => {
  if (confirm('Deseja realmente excluir este relatório?')) {
    router.delete(`/reports/${report.id}`);
  }
};

const getChartIcon = (type) => {
  const icons = {
    bar: 'fa fa-chart-bar',
    line: 'fa fa-chart-line',
    pie: 'fa fa-chart-pie',
    doughnut: 'fa fa-chart-pie',
    area: 'fa fa-chart-area',
  };
  return icons[type] || 'fa fa-chart-bar';
};

const formatCell = (value, format) => {
  if (!value) return '-';

  if (format === 'currency') {
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value);
  }

  if (format === 'date') {
    return new Date(value).toLocaleDateString('pt-BR');
  }

  if (format === 'datetime') {
    return new Date(value).toLocaleString('pt-BR');
  }

  return value;
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('pt-BR');
};
</script>

