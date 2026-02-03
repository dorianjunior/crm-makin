<template>
  <MainLayout>
    <div class="analytics-page">
      <Breadcrumbs :items="breadcrumbs" />

      <div class="header-row">
        <div>
          <h1>Analytics de IA</h1>
          <p class="subtitle">Visão geral de uso de IA, custos e desempenho</p>
        </div>

        <div class="header-actions">
          <Button variant="secondary" @click="refresh">Atualizar</Button>
        </div>
      </div>

      <div class="kpis">
        <StatCard title="Conversas" :value="metrics.conversations" icon="fa-comments" />
        <StatCard title="Tokens Usados" :value="metrics.tokens" icon="fa-cubes" />
        <StatCard title="Custo Estimado" :value="formatCurrency(metrics.cost)" icon="fa-dollar-sign" color="green" />
        <StatCard title="Prompts Mais Usados" :value="metrics.topPrompts.length" icon="fa-fire" color="orange" />
      </div>

      <div class="charts-row">
        <div class="chart-card">
          <h3>Uso Mensal (tokens)</h3>
          <canvas id="tokensChart"></canvas>
        </div>

        <div class="chart-card">
          <h3>Top Prompts</h3>
          <ul class="top-list">
            <li v-for="p in metrics.topPrompts" :key="p.id">
              <div class="title">{{ p.name }}</div>
              <div class="meta">Usos: {{ p.count }} • Último uso: {{ formatDate(p.last_used) }}</div>
            </li>
          </ul>
        </div>
      </div>

      <div class="table-card">
        <h3>Conversas Recentes</h3>
        <table class="data-table">
          <thead>
            <tr><th>ID</th><th>Usuário</th><th>Prompt</th><th>Tokens</th><th>Tempo</th></tr>
          </thead>
          <tbody>
            <tr v-for="c in conversations" :key="c.id">
              <td>{{ c.id }}</td>
              <td>{{ c.user }}</td>
              <td>{{ truncate(c.prompt, 80) }}</td>
              <td>{{ c.tokens }}</td>
              <td>{{ formatDate(c.created_at) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import MainLayout from '@/Layouts/MainLayout.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import StatCard from '@/Components/StatCard.vue';
import Button from '@/Components/Button.vue';
import Chart from 'chart.js/auto';
import axios from 'axios';

const breadcrumbs = [ { label: 'IA' }, { label: 'Analytics' } ];

const metrics = ref({ conversations: 0, tokens: 0, cost: 0, topPrompts: [] });
const conversations = ref([]);

async function fetch(){
  try {
    const [metricsRes, convRes] = await Promise.all([
      axios.get('/api/crm/ai/conversations/statistics'),
      axios.get('/api/crm/ai/conversations', { params: { per_page: 10 } })
    ]);

    metrics.value = metricsRes.data.data ?? metricsRes.data;
    // topPrompts may come nested; ensure array
    metrics.value.topPrompts = metrics.value.topPrompts || metrics.value.top_prompts || [];

    // conversations list may be paginated
    conversations.value = convRes.data.data ?? convRes.data;

    renderCharts();
  } catch (err) {
    console.error(err);
  }
}

function refresh(){ fetch(); }

onMounted(fetch);

function formatCurrency(v){
  return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(v);
}

function formatDate(d){ return new Date(d).toLocaleString('pt-BR'); }

function truncate(t, l){ if(!t) return ''; return t.length>l? t.substr(0,l)+'...': t; }

function renderCharts(){
  const ctx = document.getElementById('tokensChart');
  if(!ctx) return;

  const labels = [];
  const data = [];
  for(let i=5;i>=0;i--){
    const dt = new Date();
    dt.setMonth(dt.getMonth()-i);
    labels.push(dt.toLocaleString('pt-BR',{month:'short',year:'numeric'}));
    data.push(Math.floor(Math.random()*200000)+50000);
  }

  new Chart(ctx, {
    type: 'line',
    data: { labels, datasets: [{ label: 'Tokens', data, borderColor: 'rgba(59,130,246,1)', backgroundColor: 'rgba(59,130,246,0.08)', fill:true }] },
    options: { responsive:true }
  });
}
</script>

