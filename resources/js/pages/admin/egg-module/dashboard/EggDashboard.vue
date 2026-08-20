<script setup>

import { computed, onMounted, ref, watch } from 'vue';
import axios from 'axios';
import moment from 'moment';
import VueFeather from 'vue-feather';
import { Bar, Pie } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, ArcElement } from 'chart.js';

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, ArcElement);

const loadingDiv = ref(true);
const skipWatch = ref(true);

const period = ref('30');
const farmId = ref('');
const flockId = ref('');
const farms = ref([]);
const flocks = ref([]);

const overview = ref({
    period: {},
    kpis: {},
    charts: {
        production_bar: { labels: [], totals: [], cracked: [], mortality: [] },
        quality_pie: { labels: [], data: [] },
        flock_bar: { labels: [], data: [] },
        inventory_pie: { labels: [], data: [] },
        orders_pie: { labels: [], data: [] },
        expenses_pie: { labels: [], data: [] },
    },
    tables: {
        top_flocks: [],
        inventory: [],
        pending_orders: [],
        alerts: [],
    },
});

const realtimeAlerts = ref([]);

const pieColors = [
    '#4aa0b5', '#d64a6e', '#f6c344', '#4a7ed6', '#50B3C7',
    '#8bc34a', '#ff7043', '#7e57c2', '#26a69a', '#ef5350',
];

const chartOptions = { responsive: true, maintainAspectRatio: true };
const pieOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'bottom',
            labels: { boxWidth: 12, font: { size: 11 } },
        },
    },
};

const orderStatusLabels = {
    pending: 'Pendente',
    approved: 'Aprovado',
    picked: 'Separado',
    shipped: 'Expedido',
};

const formatMoney = (value) =>
    Number(value || 0).toLocaleString('pt-PT', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

const formatNumber = (value) => Number(value || 0).toLocaleString('pt-PT');

const filteredFlocks = computed(() => {
    if (!farmId.value) return flocks.value;
    return flocks.value.filter(f =>
        String(f.house?.farm_id) === String(farmId.value)
        || String(f.house?.farm?.id) === String(farmId.value)
    );
});

const productionBarData = computed(() => ({
    labels: overview.value.charts?.production_bar?.labels || [],
    datasets: [
        {
            label: 'Produção (ovos)',
            backgroundColor: '#50B3C7',
            data: overview.value.charts?.production_bar?.totals || [],
        },
        {
            label: 'Rachados',
            backgroundColor: '#f87979',
            data: overview.value.charts?.production_bar?.cracked || [],
        },
        {
            label: 'Mortalidade',
            backgroundColor: '#f6c344',
            data: overview.value.charts?.production_bar?.mortality || [],
        },
    ],
}));

const flockBarData = computed(() => ({
    labels: overview.value.charts?.flock_bar?.labels || [],
    datasets: [{
        label: 'Ovos por lote',
        backgroundColor: '#4a7ed6',
        data: overview.value.charts?.flock_bar?.data || [],
    }],
}));

const qualityPieData = computed(() => ({
    labels: overview.value.charts?.quality_pie?.labels || [],
    datasets: [{
        backgroundColor: pieColors,
        data: overview.value.charts?.quality_pie?.data || [],
    }],
}));

const inventoryPieData = computed(() => ({
    labels: overview.value.charts?.inventory_pie?.labels || [],
    datasets: [{
        backgroundColor: pieColors,
        data: overview.value.charts?.inventory_pie?.data || [],
    }],
}));

const ordersPieData = computed(() => ({
    labels: overview.value.charts?.orders_pie?.labels || [],
    datasets: [{
        backgroundColor: pieColors,
        data: overview.value.charts?.orders_pie?.data || [],
    }],
}));

const expensesPieData = computed(() => ({
    labels: overview.value.charts?.expenses_pie?.labels || [],
    datasets: [{
        backgroundColor: pieColors,
        data: overview.value.charts?.expenses_pie?.data || [],
    }],
}));

const hasChartData = (chart) => (chart?.data?.length || chart?.totals?.length || 0) > 0
    && (chart?.data || chart?.totals || []).some(v => Number(v) > 0);

const loadFilters = async () => {
    const [farmsRes, flocksRes] = await Promise.all([
        axios.get('/admin/farms-all'),
        axios.get('/admin/flocks-all'),
    ]);
    farms.value = farmsRes.data;
    flocks.value = flocksRes.data;
};

const filterParams = () => ({
    period: period.value,
    farm_id: farmId.value || undefined,
    flock_id: flockId.value || undefined,
});

const loadOverview = () => {
    loadingDiv.value = true;

    return Promise.all([
        axios.get('/admin/egg-dashboard/overview', { params: filterParams() }),
        axios.get('/admin/egg-dashboard/realtime-alerts'),
    ]).then(([overviewRes, alertsRes]) => {
        overview.value = overviewRes.data;
        realtimeAlerts.value = alertsRes.data;
        loadingDiv.value = false;
    }).catch(() => {
        loadingDiv.value = false;
    });
};

watch(farmId, () => {
    if (flockId.value) {
        const stillValid = filteredFlocks.value.some(f => String(f.id) === String(flockId.value));
        if (!stillValid) flockId.value = '';
    }
});

watch([period, farmId, flockId], () => {
    if (skipWatch.value) return;
    loadOverview();
});

onMounted(async () => {
    await loadFilters();
    await loadOverview();
    skipWatch.value = false;
});

</script>

<template>
    <div>
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div>
                <h1 class="h3 mb-0">Dashboard Ovos</h1>
                <p class="text-muted mb-0">Visão geral do negócio da produção avícola</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <router-link to="/admin/despesas-ovos/dashboard" class="btn btn-pill btn-outline-primary btn-sm">
                    <vue-feather type="dollar-sign"></vue-feather> Despesas
                </router-link>
                <router-link to="/admin/producao-diaria" class="btn btn-pill btn-primary btn-sm">
                    <vue-feather type="plus"></vue-feather> Produção
                </router-link>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Período</label>
                        <select class="form-control" v-model="period">
                            <option value="7">Últimos 7 dias</option>
                            <option value="14">Últimos 14 dias</option>
                            <option value="30">Últimos 30 dias</option>
                            <option value="90">Últimos 90 dias</option>
                            <option value="month">Mês corrente</option>
                            <option value="year">Ano corrente</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Farma</label>
                        <select class="form-control" v-model="farmId">
                            <option value="">Todas</option>
                            <option v-for="farm in farms" :key="farm.id" :value="farm.id">{{ farm.name }}</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Lote</label>
                        <select class="form-control" v-model="flockId">
                            <option value="">Todos</option>
                            <option v-for="flock in filteredFlocks" :key="flock.id" :value="flock.id">
                                {{ flock.code }} — {{ flock.house?.name || '' }}
                            </option>
                        </select>
                    </div>
                </div>
                <p class="text-muted small mb-0" v-if="overview.period?.start_date">
                    Período: {{ moment(overview.period.start_date).format('DD-MM-YYYY') }}
                    — {{ moment(overview.period.end_date).format('DD-MM-YYYY') }}
                </p>
            </div>
        </div>

        <div v-if="!loadingDiv">
            <div class="row mb-4">
                <div class="col-lg-3 col-md-4 col-6 mb-3">
                    <div class="card border-primary h-100">
                        <div class="card-body py-3">
                            <h6 class="text-primary mb-1">Ovos (período)</h6>
                            <h3 class="mb-0">{{ formatNumber(overview.kpis.total_eggs) }}</h3>
                            <small class="text-muted">{{ overview.kpis.active_flocks || 0 }} lotes ativos</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-6 mb-3">
                    <div class="card border-danger h-100">
                        <div class="card-body py-3">
                            <h6 class="text-danger mb-1">Mortalidade</h6>
                            <h3 class="mb-0">{{ formatNumber(overview.kpis.total_mortality) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-6 mb-3">
                    <div class="card border-info h-100">
                        <div class="card-body py-3">
                            <h6 class="text-info mb-1">Estoque disponível</h6>
                            <h3 class="mb-0">{{ formatNumber(overview.kpis.available_inventory) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-6 mb-3">
                    <div class="card border-warning h-100">
                        <div class="card-body py-3">
                            <h6 class="text-warning mb-1">Pedidos abertos</h6>
                            <h3 class="mb-0">{{ overview.kpis.pending_orders || 0 }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-6 mb-3">
                    <div class="card border-success h-100">
                        <div class="card-body py-3">
                            <h6 class="text-success mb-1">Receita (período)</h6>
                            <h3 class="mb-0">{{ formatMoney(overview.kpis.revenue) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-6 mb-3">
                    <div class="card border-secondary h-100">
                        <div class="card-body py-3">
                            <h6 class="text-secondary mb-1">Despesas</h6>
                            <h3 class="mb-0">{{ formatMoney(overview.kpis.expenses) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-6 mb-3">
                    <div class="card border-primary h-100">
                        <div class="card-body py-3">
                            <h6 class="text-primary mb-1">Ovos expedidos</h6>
                            <h3 class="mb-0">{{ formatNumber(overview.kpis.shipped_eggs) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-6 mb-3">
                    <div class="card border-dark h-100">
                        <div class="card-body py-3">
                            <h6 class="text-dark mb-1">Resultado estimado</h6>
                            <h3 class="mb-0">{{ formatMoney((overview.kpis.revenue || 0) - (overview.kpis.expenses || 0)) }}</h3>
                            <small class="text-muted">receita − despesas</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4" v-if="realtimeAlerts.length || overview.tables?.alerts?.length">
                <div class="col-12">
                    <div class="card border-warning">
                        <div class="card-body py-3">
                            <h6 class="text-warning mb-2">Alertas</h6>
                            <div v-for="(alert, index) in realtimeAlerts" :key="'rt-' + index" class="small mb-1">• {{ alert.message }}</div>
                            <div v-for="alert in overview.tables.alerts" :key="alert.id" class="small mb-1">
                                • <strong>{{ alert.title || 'Alerta' }}</strong> — {{ alert.message }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-lg-8 mb-3">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Produção, rachados e mortalidade</h5>
                        </div>
                        <div class="card-body">
                            <div class="chart" v-if="(overview.charts.production_bar?.labels || []).length">
                                <Bar :data="productionBarData" :options="chartOptions" />
                            </div>
                            <p v-else class="text-muted text-center mb-0">Sem dados de produção no período</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 mb-3">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Qualidade dos ovos</h5>
                        </div>
                        <div class="card-body">
                            <div class="chart chart-pie" v-if="hasChartData(overview.charts.quality_pie)">
                                <Pie :data="qualityPieData" :options="pieOptions" />
                            </div>
                            <p v-else class="text-muted text-center mb-0">Sem dados de qualidade</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-lg-6 mb-3">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Top lotes (produção)</h5>
                        </div>
                        <div class="card-body">
                            <div class="chart" v-if="(overview.charts.flock_bar?.labels || []).length">
                                <Bar :data="flockBarData" :options="chartOptions" />
                            </div>
                            <p v-else class="text-muted text-center mb-0">Sem produção por lote</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 mb-3">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Estoque por categoria</h5>
                        </div>
                        <div class="card-body">
                            <div class="chart chart-pie" v-if="hasChartData(overview.charts.inventory_pie)">
                                <Pie :data="inventoryPieData" :options="pieOptions" />
                            </div>
                            <p v-else class="text-muted text-center mb-0">Sem estoque disponível</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-lg-6 mb-3">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Pedidos por estado</h5>
                        </div>
                        <div class="card-body">
                            <div class="chart chart-pie" v-if="hasChartData(overview.charts.orders_pie)">
                                <Pie :data="ordersPieData" :options="pieOptions" />
                            </div>
                            <p v-else class="text-muted text-center mb-0">Sem pedidos no período</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 mb-3">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Despesas por categoria</h5>
                        </div>
                        <div class="card-body">
                            <div class="chart chart-pie" v-if="hasChartData(overview.charts.expenses_pie)">
                                <Pie :data="expensesPieData" :options="pieOptions" />
                            </div>
                            <p v-else class="text-muted text-center mb-0">Sem despesas no período</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-lg-6 mb-3">
                    <div class="card h-100">
                        <div class="card-header"><h5 class="card-title mb-0">Pedidos pendentes / aprovados</h5></div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped mb-0">
                                    <thead>
                                        <tr>
                                            <th>Cliente</th>
                                            <th>Qtd</th>
                                            <th>Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody v-if="overview.tables?.pending_orders?.length">
                                        <tr v-for="order in overview.tables.pending_orders" :key="order.id">
                                            <td>{{ order.customer_name }}</td>
                                            <td>{{ order.quantity_dozens }}</td>
                                            <td>{{ orderStatusLabels[order.status] || order.status }}</td>
                                        </tr>
                                    </tbody>
                                    <tbody v-else>
                                        <tr><td colspan="3" class="text-center">Nenhum pedido aberto</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 mb-3">
                    <div class="card h-100">
                        <div class="card-header"><h5 class="card-title mb-0">Ranking de lotes</h5></div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped mb-0">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Lote</th>
                                            <th class="text-end">Ovos</th>
                                        </tr>
                                    </thead>
                                    <tbody v-if="overview.tables?.top_flocks?.length">
                                        <tr v-for="(row, index) in overview.tables.top_flocks" :key="row.flock_id">
                                            <td>{{ index + 1 }}</td>
                                            <td>{{ row.flock_code }}</td>
                                            <td class="text-end">{{ formatNumber(row.total_eggs) }}</td>
                                        </tr>
                                    </tbody>
                                    <tbody v-else>
                                        <tr><td colspan="3" class="text-center">Sem dados</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-else class="card">
            <div class="card-body text-center py-5">
                <div class="spinner-border" role="status"></div>
                <div class="mt-2">Carregando Dashboard...</div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.chart-pie {
    position: relative;
    height: 420px;
    max-width: 480px;
    margin: 0 auto;
}
</style>
