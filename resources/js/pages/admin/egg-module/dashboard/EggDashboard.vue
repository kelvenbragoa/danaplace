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
const groupBy = ref('day');
const farmId = ref('');
const flockId = ref('');
const farms = ref([]);
const flocks = ref([]);

const overview = ref({
    period: {},
    feed_config: {},
    kpis: {},
    charts: {
        production_bar: { labels: [], totals: [], cracked: [], mortality: [] },
        size_bar: { labels: [], normal: [], grande: [], jumbo: [] },
        feed_bar: { labels: [], kg: [], cost_mzn: [], bags: [] },
        orders_bar: { labels: [], order_qty: [], shipped_qty: [], scheduled_qty: [] },
        size_totals_bar: { labels: [], data: [] },
        inventory_bar: { labels: [], data: [] },
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

const sizeColors = { normal: '#4aa0b5', grande: '#4a7ed6', jumbo: '#7e57c2' };

const chartOptions = {
    responsive: true,
    maintainAspectRatio: true,
    plugins: { legend: { position: 'bottom' } },
};

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
        { label: 'Produção (ovos)', backgroundColor: '#50B3C7', data: overview.value.charts?.production_bar?.totals || [] },
        { label: 'Rachados', backgroundColor: '#f87979', data: overview.value.charts?.production_bar?.cracked || [] },
        { label: 'Mortalidade', backgroundColor: '#f6c344', data: overview.value.charts?.production_bar?.mortality || [] },
    ],
}));

const sizeBarData = computed(() => ({
    labels: overview.value.charts?.size_bar?.labels || [],
    datasets: [
        { label: 'Normal', backgroundColor: sizeColors.normal, data: overview.value.charts?.size_bar?.normal || [] },
        { label: 'Grande', backgroundColor: sizeColors.grande, data: overview.value.charts?.size_bar?.grande || [] },
        { label: 'Jumbo', backgroundColor: sizeColors.jumbo, data: overview.value.charts?.size_bar?.jumbo || [] },
    ],
}));

const feedKgBarData = computed(() => ({
    labels: overview.value.charts?.feed_bar?.labels || [],
    datasets: [{
        label: 'Ração (kg)',
        backgroundColor: '#8bc34a',
        data: overview.value.charts?.feed_bar?.kg || [],
    }],
}));

const feedCostBarData = computed(() => ({
    labels: overview.value.charts?.feed_bar?.labels || [],
    datasets: [{
        label: 'Custo ração (MT)',
        backgroundColor: '#ff7043',
        data: overview.value.charts?.feed_bar?.cost_mzn || [],
    }],
}));

const ordersBarData = computed(() => ({
    labels: overview.value.charts?.orders_bar?.labels || [],
    datasets: [
        { label: 'Pedidos (ovos)', backgroundColor: '#4a7ed6', data: overview.value.charts?.orders_bar?.order_qty || [] },
        { label: 'Expedições (ovos)', backgroundColor: '#1cbb8c', data: overview.value.charts?.orders_bar?.shipped_qty || [] },
        { label: 'Programadas (ovos)', backgroundColor: '#e5a54b', data: overview.value.charts?.orders_bar?.scheduled_qty || [] },
    ],
}));

const sizeTotalsBarData = computed(() => ({
    labels: overview.value.charts?.size_totals_bar?.labels || [],
    datasets: [{
        label: 'Ovos por tamanho',
        backgroundColor: [sizeColors.normal, sizeColors.grande, sizeColors.jumbo],
        data: overview.value.charts?.size_totals_bar?.data || [],
    }],
}));

const inventoryBarData = computed(() => ({
    labels: overview.value.charts?.inventory_bar?.labels || [],
    datasets: [{
        label: 'Stock (ovos)',
        backgroundColor: pieColors,
        data: overview.value.charts?.inventory_bar?.data || [],
    }],
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
    datasets: [{ backgroundColor: pieColors, data: overview.value.charts?.quality_pie?.data || [] }],
}));

const ordersPieData = computed(() => ({
    labels: overview.value.charts?.orders_pie?.labels || [],
    datasets: [{ backgroundColor: pieColors, data: overview.value.charts?.orders_pie?.data || [] }],
}));

const expensesPieData = computed(() => ({
    labels: overview.value.charts?.expenses_pie?.labels || [],
    datasets: [{ backgroundColor: pieColors, data: overview.value.charts?.expenses_pie?.data || [] }],
}));

const hasChartData = (chart) => {
    if (!chart) return false;
    const arrays = ['data', 'totals', 'kg', 'normal', 'order_qty'].map(k => chart[k]).filter(Boolean);
    return arrays.some(arr => arr.some(v => Number(v) > 0));
};

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
    group_by: groupBy.value,
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

watch([period, groupBy, farmId, flockId], () => {
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
                <p class="text-muted mb-0">Produção, ração, pedidos e stock</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <router-link to="/admin/pedidos/calendario" class="btn btn-pill btn-outline-secondary btn-sm">
                    <vue-feather type="calendar"></vue-feather> Calendário
                </router-link>
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
                    <div class="col-md-3 mb-2">
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
                    <div class="col-md-3 mb-2">
                        <label class="form-label">Agrupar por</label>
                        <select class="form-control" v-model="groupBy">
                            <option value="day">Dia</option>
                            <option value="month">Mês</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label">Farma</label>
                        <select class="form-control" v-model="farmId">
                            <option value="">Todas</option>
                            <option v-for="farm in farms" :key="farm.id" :value="farm.id">{{ farm.name }}</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
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
                    <span v-if="overview.feed_config?.bag_kg">
                        · Ração: {{ overview.feed_config.bag_kg }} kg/saco = {{ formatMoney(overview.feed_config.bag_price_mzn) }} MT
                        ({{ formatMoney(overview.feed_config.price_per_kg) }} MT/kg)
                    </span>
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
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-6 mb-3">
                    <div class="card border-success h-100">
                        <div class="card-body py-3">
                            <h6 class="text-success mb-1">Ração consumida</h6>
                            <h3 class="mb-0">{{ formatNumber(overview.kpis.total_feed_kg) }} kg</h3>
                            <small class="text-muted">{{ formatNumber(overview.kpis.total_feed_bags) }} sacos</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-6 mb-3">
                    <div class="card border-warning h-100">
                        <div class="card-body py-3">
                            <h6 class="text-warning mb-1">Custo ração (estimado)</h6>
                            <h3 class="mb-0">{{ formatMoney(overview.kpis.total_feed_cost_mzn) }} MT</h3>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-6 mb-3">
                    <div class="card border-info h-100">
                        <div class="card-body py-3">
                            <h6 class="text-info mb-1">Stock disponível</h6>
                            <h3 class="mb-0">{{ formatNumber(overview.kpis.available_inventory) }}</h3>
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
                    <div class="card border-secondary h-100">
                        <div class="card-body py-3">
                            <h6 class="text-secondary mb-1">Pedidos abertos</h6>
                            <h3 class="mb-0">{{ overview.kpis.pending_orders || 0 }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-6 mb-3">
                    <div class="card border-success h-100">
                        <div class="card-body py-3">
                            <h6 class="text-success mb-1">Receita</h6>
                            <h3 class="mb-0">{{ formatMoney(overview.kpis.revenue) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-6 mb-3">
                    <div class="card border-dark h-100">
                        <div class="card-body py-3">
                            <h6 class="text-dark mb-1">Resultado estimado</h6>
                            <h3 class="mb-0">{{ formatMoney((overview.kpis.revenue || 0) - (overview.kpis.expenses || 0)) }}</h3>
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

            <!-- Produção por dia/mês -->
            <div class="row mb-4">
                <div class="col-12 mb-3">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Produção de ovos por {{ groupBy === 'month' ? 'mês' : 'dia' }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="chart" v-if="(overview.charts.production_bar?.labels || []).length">
                                <Bar :data="productionBarData" :options="chartOptions" />
                            </div>
                            <p v-else class="text-muted text-center mb-0">Sem dados de produção no período</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Partidos por tamanho -->
            <div class="row mb-4">
                <div class="col-lg-8 mb-3">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Produção por tamanho (Normal, Grande, Jumbo)</h5>
                        </div>
                        <div class="card-body">
                            <div class="chart" v-if="hasChartData(overview.charts.size_bar)">
                                <Bar :data="sizeBarData" :options="chartOptions" />
                            </div>
                            <p v-else class="text-muted text-center mb-0">Sem dados de tamanho — registe Normal/Grande/Jumbo na produção diária</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 mb-3">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Total por tamanho (período)</h5>
                        </div>
                        <div class="card-body">
                            <div class="chart" v-if="hasChartData(overview.charts.size_totals_bar)">
                                <Bar :data="sizeTotalsBarData" :options="chartOptions" />
                            </div>
                            <p v-else class="text-muted text-center mb-0">Sem dados</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ração -->
            <div class="row mb-4">
                <div class="col-lg-6 mb-3">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Consumo de ração (kg)</h5>
                        </div>
                        <div class="card-body">
                            <div class="chart" v-if="hasChartData(overview.charts.feed_bar)">
                                <Bar :data="feedKgBarData" :options="chartOptions" />
                            </div>
                            <p v-else class="text-muted text-center mb-0">Sem dados de ração</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 mb-3">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Custo de ração (MT)</h5>
                        </div>
                        <div class="card-body">
                            <div class="chart" v-if="hasChartData(overview.charts.feed_bar)">
                                <Bar :data="feedCostBarData" :options="chartOptions" />
                            </div>
                            <p v-else class="text-muted text-center mb-0">Sem dados de custo</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pedidos, expedições, programadas -->
            <div class="row mb-4">
                <div class="col-12 mb-3">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Pedidos, expedições e entregas programadas (ovos)</h5>
                        </div>
                        <div class="card-body">
                            <div class="chart" v-if="hasChartData(overview.charts.orders_bar)">
                                <Bar :data="ordersBarData" :options="chartOptions" />
                            </div>
                            <p v-else class="text-muted text-center mb-0">Sem pedidos ou expedições no período</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stock por categoria -->
            <div class="row mb-4">
                <div class="col-lg-6 mb-3">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Stock por categoria (Normal, Grande, Jumbo)</h5>
                        </div>
                        <div class="card-body">
                            <div class="chart" v-if="hasChartData(overview.charts.inventory_bar)">
                                <Bar :data="inventoryBarData" :options="chartOptions" />
                            </div>
                            <p v-else class="text-muted text-center mb-0">Sem stock disponível</p>
                        </div>
                    </div>
                </div>
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
            </div>

            <div class="row mb-4">
                <div class="col-lg-4 mb-3">
                    <div class="card h-100">
                        <div class="card-header"><h5 class="card-title mb-0">Qualidade</h5></div>
                        <div class="card-body">
                            <div class="chart chart-pie" v-if="hasChartData(overview.charts.quality_pie)">
                                <Pie :data="qualityPieData" :options="pieOptions" />
                            </div>
                            <p v-else class="text-muted text-center mb-0">Sem dados</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 mb-3">
                    <div class="card h-100">
                        <div class="card-header"><h5 class="card-title mb-0">Pedidos por estado</h5></div>
                        <div class="card-body">
                            <div class="chart chart-pie" v-if="hasChartData(overview.charts.orders_pie)">
                                <Pie :data="ordersPieData" :options="pieOptions" />
                            </div>
                            <p v-else class="text-muted text-center mb-0">Sem pedidos</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 mb-3">
                    <div class="card h-100">
                        <div class="card-header"><h5 class="card-title mb-0">Despesas</h5></div>
                        <div class="card-body">
                            <div class="chart chart-pie" v-if="hasChartData(overview.charts.expenses_pie)">
                                <Pie :data="expensesPieData" :options="pieOptions" />
                            </div>
                            <p v-else class="text-muted text-center mb-0">Sem despesas</p>
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
                                            <th>Categoria</th>
                                            <th>Qtd</th>
                                            <th>Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody v-if="overview.tables?.pending_orders?.length">
                                        <tr v-for="order in overview.tables.pending_orders" :key="order.id">
                                            <td>{{ order.customer_name }}</td>
                                            <td>{{ order.category?.name || '-' }}</td>
                                            <td>{{ order.quantity_dozens }}</td>
                                            <td>{{ orderStatusLabels[order.status] || order.status }}</td>
                                        </tr>
                                    </tbody>
                                    <tbody v-else>
                                        <tr><td colspan="4" class="text-center">Nenhum pedido aberto</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 mb-3">
                    <div class="card h-100">
                        <div class="card-header"><h5 class="card-title mb-0">Stock por categoria</h5></div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped mb-0">
                                    <thead>
                                        <tr>
                                            <th>Categoria</th>
                                            <th class="text-end">Quantidade</th>
                                        </tr>
                                    </thead>
                                    <tbody v-if="overview.tables?.inventory?.length">
                                        <tr v-for="(row, index) in overview.tables.inventory" :key="index">
                                            <td>{{ row.category }}</td>
                                            <td class="text-end">{{ formatNumber(row.quantity) }}</td>
                                        </tr>
                                    </tbody>
                                    <tbody v-else>
                                        <tr><td colspan="2" class="text-center">Sem stock</td></tr>
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
    height: 220px;
    max-width: 280px;
    margin: 0 auto;
}
</style>
