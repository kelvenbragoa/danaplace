<script setup>

import { onMounted, ref } from 'vue';
import axios from 'axios';
import VueFeather from 'vue-feather';
import moment from 'moment'

const loadingDiv = ref(true);
const dashboard = ref({});
const productionStats = ref([]);
const mortalityStats = ref([]);
const inventoryStats = ref([]);
const financialStats = ref({});
const realtimeAlerts = ref([]);

const orderStatusLabels = {
    pending: 'Pendente',
    approved: 'Aprovado',
};

const formatCurrency = (value) => {
    if (value === null || value === undefined || Number.isNaN(Number(value))) return '-';
    return Number(value).toFixed(2);
};

const loadDashboard = () => {
    axios.get('/admin/egg-dashboard').then((response) => {
        dashboard.value = response.data;
    });
};

const loadCharts = () => {
    Promise.all([
        axios.get('/admin/egg-dashboard/production-stats', { params: { days: 14 } }),
        axios.get('/admin/egg-dashboard/mortality-stats', { params: { days: 14 } }),
        axios.get('/admin/egg-dashboard/inventory-stats'),
        axios.get('/admin/egg-dashboard/financial-stats'),
        axios.get('/admin/egg-dashboard/realtime-alerts'),
    ]).then(([production, mortality, inventory, financial, alerts]) => {
        productionStats.value = production.data;
        mortalityStats.value = mortality.data;
        inventoryStats.value = inventory.data;
        financialStats.value = financial.data;
        realtimeAlerts.value = alerts.data;
        loadingDiv.value = false;
    }).catch(() => {
        loadingDiv.value = false;
    });
};

onMounted(() => {
    loadDashboard();
    loadCharts();
});

</script>

<template>
    <div v-if="!loadingDiv">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">Dashboard Ovos</h1>
                <p class="text-muted mb-0">Visão geral da produção avícola</p>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-lg-2 col-md-4 col-6 mb-3">
                <div class="card border-primary h-100">
                    <div class="card-body py-3">
                        <h6 class="text-primary mb-1">Lotes Ativos</h6>
                        <h3 class="mb-0">{{ dashboard.summary?.active_flocks || 0 }}</h3>
                        <small class="text-muted">de {{ dashboard.summary?.total_flocks || 0 }} total</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-6 mb-3">
                <div class="card border-success h-100">
                    <div class="card-body py-3">
                        <h6 class="text-success mb-1">Ovos Hoje</h6>
                        <h3 class="mb-0">{{ dashboard.summary?.total_eggs_today || 0 }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-6 mb-3">
                <div class="card border-danger h-100">
                    <div class="card-body py-3">
                        <h6 class="text-danger mb-1">Mortalidade Hoje</h6>
                        <h3 class="mb-0">{{ dashboard.summary?.total_mortality_today || 0 }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-6 mb-3">
                <div class="card border-info h-100">
                    <div class="card-body py-3">
                        <h6 class="text-info mb-1">Estoque Disp.</h6>
                        <h3 class="mb-0">{{ dashboard.summary?.available_inventory || 0 }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-6 mb-3">
                <div class="card border-warning h-100">
                    <div class="card-body py-3">
                        <h6 class="text-warning mb-1">Pedidos Pendentes</h6>
                        <h3 class="mb-0">{{ dashboard.summary?.pending_orders || 0 }}</h3>
                        <small class="text-muted">de {{ dashboard.summary?.total_eggs_pending_orders || 0 }} total</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-6 mb-3">
                <div class="card border-secondary h-100">
                    <div class="card-body py-3">
                        <h6 class="text-secondary mb-1">Receita (mês)</h6>
                        <h3 class="mb-0">{{ formatCurrency(financialStats.total_revenue) }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-lg-6 mb-3">
                <div class="card h-100">
                    <div class="card-header"><h5 class="card-title mb-0">Produção Hoje por Lote</h5></div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th>Lote</th>
                                        <th>Total</th>
                                        <th>Rachados</th>
                                        <th>% Postura</th>
                                    </tr>
                                </thead>
                                <tbody v-if="dashboard.production_today?.length">
                                    <tr v-for="item in dashboard.production_today" :key="item.flock_code">
                                        <td>{{ item.flock_code }}</td>
                                        <td>{{ item.total_eggs }}</td>
                                        <td>{{ item.cracked }}</td>
                                        <td>{{ item.laying_rate }}%</td>
                                    </tr>
                                </tbody>
                                <tbody v-else>
                                    <tr><td colspan="4" align="center">Sem produção registada hoje</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 mb-3">
                <div class="card h-100">
                    <div class="card-header"><h5 class="card-title mb-0">Mortalidade Hoje</h5></div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th>Lote</th>
                                        <th>Quantidade</th>
                                        <th>Causa</th>
                                    </tr>
                                </thead>
                                <tbody v-if="dashboard.mortality_today?.length">
                                    <tr v-for="item in dashboard.mortality_today" :key="item.flock_code + item.quantity">
                                        <td>{{ item.flock_code }}</td>
                                        <td>{{ item.quantity }}</td>
                                        <td>{{ item.cause || '-' }}</td>
                                    </tr>
                                </tbody>
                                <tbody v-else>
                                    <tr><td colspan="3" align="center">Sem mortalidade registada hoje</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-lg-4 mb-3">
                <div class="card h-100">
                    <div class="card-header"><h5 class="card-title mb-0">Estoque por Categoria</h5></div>
                    <div class="card-body">
                        <div v-if="dashboard.inventory_status?.length">
                            <div v-for="item in dashboard.inventory_status" :key="item.category" class="mb-2">
                                <div class="d-flex justify-content-between small">
                                    <span>{{ item.category }}</span>
                                    <strong>{{ item.quantity }}</strong>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-info" :style="{ width: Math.min(100, (item.quantity / (dashboard.summary?.available_inventory || 1)) * 100) + '%' }"></div>
                                </div>
                            </div>
                        </div>
                        <p v-else class="text-muted mb-0">Sem estoque disponível</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 mb-3">
                <div class="card h-100">
                    <div class="card-header"><h5 class="card-title mb-0">Pedidos Pendentes</h5></div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>Cliente</th>
                                        <th>Quantidade</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody v-if="dashboard.pending_orders?.length">
                                    <tr v-for="order in dashboard.pending_orders" :key="order.id">
                                        <td>{{ order.customer_name }}</td>
                                        <td>{{ order.quantity_dozens }}</td>
                                        <td>{{ orderStatusLabels[order.status] || order.status }}</td>
                                    </tr>
                                </tbody>
                                <tbody v-else>
                                    <tr><td colspan="3" align="center">Nenhum pedido pendente</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 mb-3">
                <div class="card h-100">
                    <div class="card-header"><h5 class="card-title mb-0">Alertas</h5></div>
                    <div class="card-body">
                        <div v-if="realtimeAlerts.length || dashboard.recent_alerts?.length">
                            <div v-for="(alert, index) in realtimeAlerts" :key="'rt-' + index" class="alert alert-warning py-2 small mb-2">
                                {{ alert.message }}
                            </div>
                            <div v-for="alert in dashboard.recent_alerts" :key="alert.id" class="border-bottom pb-2 mb-2 small">
                                <strong>{{ alert.title }}</strong><br>
                                {{ alert.message }}
                            </div>
                        </div>
                        <p v-else class="text-muted mb-0">Sem alertas ativos</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6 mb-3">
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Produção — últimos 14 dias</h5></div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th>Data</th>
                                        <th>Total</th>
                                        <th>Rachados</th>
                                        <th>Sujos</th>
                                    </tr>
                                </thead>
                                <tbody v-if="productionStats.length">
                                    <tr v-for="row in productionStats" :key="row.date">
                                        <td>{{ moment(row.date).format('DD-MM-YYYY') }}</td>
                                        <td>{{ row.total_eggs }}</td>
                                        <td>{{ row.cracked_eggs }}</td>
                                        <td>{{ row.dirty_eggs }}</td>
                                    </tr>
                                </tbody>
                                <tbody v-else>
                                    <tr><td colspan="4" align="center">Sem dados</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 mb-3">
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Mortalidade — últimos 14 dias</h5></div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th>Data</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody v-if="mortalityStats.length">
                                    <tr v-for="row in mortalityStats" :key="row.date">
                                        <td>{{ moment(row.date).format('DD-MM-YYYY') }}</td>
                                        <td>{{ row.total_mortality }}</td>
                                    </tr>
                                </tbody>
                                <tbody v-else>
                                    <tr><td colspan="2" align="center">Sem dados</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div v-else>
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-center">
                    <div class="spinner-border" role="status"><span class="sr-only"></span></div>
                </div>
                <br>
                <div class="d-flex justify-content-center">Carregando Dashboard...</div>
            </div>
        </div>
    </div>
</template>
