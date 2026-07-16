<script setup>

import { computed, onMounted, ref, watch } from 'vue';
import axios from 'axios';
import moment from 'moment';
import VueFeather from 'vue-feather';
import { Bar, Pie } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, ArcElement } from 'chart.js';

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, ArcElement);

const loadingDiv = ref(true);
const dashboard = ref({
    summary: {},
    period: {},
    by_category: [],
    by_farm: [],
    recent_expenses: [],
    technicians: [],
    departments: [],
    selected_department: null,
    charts: { pie: { labels: [], data: [] }, bar_categories: { labels: [], data: [] }, bar_months: { labels: [], data: [] } },
});

const period = ref('month');
const departmentId = ref('');
const farmId = ref('');
const farms = ref([]);
const skipWatch = ref(true);

const pieColors = [
    '#4aa0b5', '#d64a6e', '#f6c344', '#4a7ed6', '#50B3C7',
    '#8bc34a', '#ff7043', '#7e57c2', '#26a69a', '#ef5350', '#78909c',
];

const formatMoney = (value) => {
    return Number(value || 0).toLocaleString('pt-PT', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const pieChartData = computed(() => ({
    labels: dashboard.value.charts?.pie?.labels || [],
    datasets: [{
        backgroundColor: pieColors,
        data: dashboard.value.charts?.pie?.data || [],
    }],
}));

const categoryBarData = computed(() => ({
    labels: dashboard.value.charts?.bar_categories?.labels || [],
    datasets: [{
        label: 'Valor por categoria',
        backgroundColor: '#4aa0b5',
        data: dashboard.value.charts?.bar_categories?.data || [],
    }],
}));

const monthBarData = computed(() => ({
    labels: (dashboard.value.charts?.bar_months?.labels || []).map((m) => moment(m + '-01').format('MMM YYYY')),
    datasets: [{
        label: 'Despesas mensais',
        backgroundColor: '#d64a6e',
        data: dashboard.value.charts?.bar_months?.data || [],
    }],
}));

const chartOptions = {
    responsive: true,
    maintainAspectRatio: true,
};

const pieOptions = {
    responsive: true,
    maintainAspectRatio: true,
    plugins: {
        legend: { position: 'bottom' },
    },
};

const loadFarms = () => {
    return axios.get('/admin/farms-all').then((response) => {
        farms.value = response.data;
    });
};

const loadDashboard = () => {
    loadingDiv.value = true;

    return axios.get('/admin/egg-expenses/dashboard', {
        params: {
            period: period.value,
            department_id: departmentId.value || undefined,
            farm_id: farmId.value || undefined,
        },
    }).then((response) => {
        dashboard.value = response.data;
        loadingDiv.value = false;
    }).catch(() => {
        loadingDiv.value = false;
    });
};

watch([period, departmentId, farmId], () => {
    if (skipWatch.value) return;
    loadDashboard();
});

onMounted(async () => {
    await loadFarms();
    await loadDashboard();
    skipWatch.value = false;
});

</script>

<template>
    <div>
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div>
                <h1 class="h3 mb-0">Dashboard de Despesas</h1>
                <p class="text-muted mb-0">Custos operacionais da produção avícola e salários por departamento</p>
            </div>
            <div>
                <router-link to="/admin/despesas-ovos" class="btn btn-pill btn-primary">
                    <vue-feather type="list"></vue-feather> Ver despesas
                </router-link>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Período</label>
                        <select class="form-control" v-model="period">
                            <option value="week">Última semana</option>
                            <option value="month">Mês corrente</option>
                            <option value="30">Últimos 30 dias</option>
                            <option value="90">Últimos 90 dias</option>
                            <option value="year">Ano corrente</option>
                            <option value="yeartodate">Início do ano até hoje</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Departamento (salários)</label>
                        <select class="form-control" v-model="departmentId">
                            <option value="">Sugestão: Produção Avícola</option>
                            <option value="all">Todos os departamentos</option>
                            <option
                                v-for="dept in dashboard.departments"
                                :key="dept.id"
                                :value="dept.id"
                            >{{ dept.name }}</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Granja</label>
                        <select class="form-control" v-model="farmId">
                            <option value="">Todas as granjas</option>
                            <option v-for="farm in farms" :key="farm.id" :value="farm.id">{{ farm.name }}</option>
                        </select>
                    </div>
                </div>
                <p class="text-muted small mb-0" v-if="dashboard.period?.start_date">
                    Período: {{ moment(dashboard.period.start_date).format('DD-MM-YYYY') }}
                    — {{ moment(dashboard.period.end_date).format('DD-MM-YYYY') }}
                    ({{ dashboard.period.months }} mês/meses)
                </p>
            </div>
        </div>

        <div v-if="!loadingDiv">
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="card border-primary h-100">
                        <div class="card-body">
                            <h6 class="text-primary">Despesas operacionais</h6>
                            <h3>{{ formatMoney(dashboard.summary.operational_total) }}</h3>
                            <small class="text-muted">{{ dashboard.summary.expense_count || 0 }} registos</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card border-warning h-100">
                        <div class="card-body">
                            <h6 class="text-warning">Folha salarial (mês)</h6>
                            <h3>{{ formatMoney(dashboard.summary.monthly_payroll) }}</h3>
                            <small class="text-muted">{{ dashboard.summary.technician_count || 0 }} técnicos ativos</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card border-info h-100">
                        <div class="card-body">
                            <h6 class="text-info">Salários no período</h6>
                            <h3>{{ formatMoney(dashboard.summary.salary_cost) }}</h3>
                            <small class="text-muted">folha × {{ dashboard.period?.months || 1 }} mês(es)</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card border-danger h-100">
                        <div class="card-body">
                            <h6 class="text-danger">Total combinado</h6>
                            <h3>{{ formatMoney(dashboard.summary.combined_total) }}</h3>
                            <small class="text-muted">operacional + salários</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-lg-6 mb-3">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Distribuição por categoria (pizza)</h5>
                        </div>
                        <div class="card-body">
                            <div class="chart" v-if="(dashboard.charts?.pie?.data || []).length">
                                <Pie :data="pieChartData" :options="pieOptions" />
                            </div>
                            <p v-else class="text-muted text-center mb-0">Sem dados no período</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 mb-3">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Valores por categoria</h5>
                        </div>
                        <div class="card-body">
                            <div class="chart">
                                <Bar :data="categoryBarData" :options="chartOptions" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-lg-8 mb-3">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Evolução mensal das despesas</h5>
                        </div>
                        <div class="card-body">
                            <div class="chart" v-if="(dashboard.charts?.bar_months?.data || []).length">
                                <Bar :data="monthBarData" :options="chartOptions" />
                            </div>
                            <p v-else class="text-muted text-center mb-0">Sem evolução mensal no período</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 mb-3">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Por granja</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped mb-0">
                                    <thead>
                                        <tr>
                                            <th>Granja</th>
                                            <th class="text-end">Valor</th>
                                        </tr>
                                    </thead>
                                    <tbody v-if="dashboard.by_farm?.length">
                                        <tr v-for="row in dashboard.by_farm" :key="row.farm_id || 'none'">
                                            <td>{{ row.farm_name }}</td>
                                            <td class="text-end">{{ formatMoney(row.total) }}</td>
                                        </tr>
                                    </tbody>
                                    <tbody v-else>
                                        <tr><td colspan="2" class="text-center">Sem dados</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-lg-6 mb-3">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Detalhe por categoria</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped mb-0">
                                    <thead>
                                        <tr>
                                            <th>Categoria</th>
                                            <th>Qtd</th>
                                            <th class="text-end">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="row in dashboard.by_category" :key="row.key">
                                            <td>{{ row.label }}</td>
                                            <td>{{ row.count }}</td>
                                            <td class="text-end">{{ formatMoney(row.total) }}</td>
                                        </tr>
                                        <tr class="fw-bold">
                                            <td>Salários técnicos</td>
                                            <td>{{ dashboard.summary.technician_count || 0 }}</td>
                                            <td class="text-end">{{ formatMoney(dashboard.summary.salary_cost) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 mb-3">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                Técnicos
                                <span v-if="dashboard.selected_department"> — {{ dashboard.selected_department.name }}</span>
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped mb-0">
                                    <thead>
                                        <tr>
                                            <th>Nome</th>
                                            <th>Cargo</th>
                                            <th class="text-end">Salário</th>
                                        </tr>
                                    </thead>
                                    <tbody v-if="dashboard.technicians?.length">
                                        <tr v-for="tech in dashboard.technicians" :key="tech.id">
                                            <td>{{ tech.name }}</td>
                                            <td>{{ tech.position || '-' }}</td>
                                            <td class="text-end">{{ formatMoney(tech.salary) }}</td>
                                        </tr>
                                    </tbody>
                                    <tbody v-else>
                                        <tr><td colspan="3" class="text-center">Nenhum técnico neste departamento</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Despesas recentes</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Título</th>
                                    <th>Categoria</th>
                                    <th>Granja</th>
                                    <th class="text-end">Valor</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody v-if="dashboard.recent_expenses?.length">
                                <tr v-for="item in dashboard.recent_expenses" :key="item.id">
                                    <td>{{ moment(item.expense_date).format('DD-MM-YYYY') }}</td>
                                    <td>{{ item.title }}</td>
                                    <td>{{ item.category_label || item.category }}</td>
                                    <td>{{ item.farm?.name || '-' }}</td>
                                    <td class="text-end">{{ formatMoney(item.amount) }}</td>
                                    <td>
                                        <router-link :to="'/admin/despesas-ovos/' + item.id">
                                            <vue-feather type="eye"></vue-feather>
                                        </router-link>
                                    </td>
                                </tr>
                            </tbody>
                            <tbody v-else>
                                <tr><td colspan="6" class="text-center">Sem despesas no período</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div v-else class="card">
            <div class="card-body text-center py-5">
                <div class="spinner-border" role="status"></div>
                <div class="mt-2">Carregando dashboard...</div>
            </div>
        </div>
    </div>
</template>
