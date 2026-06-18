<script setup>

import { onMounted, ref, watch } from 'vue';
import axios from 'axios';

const loadingDiv = ref(true);
const flocks = ref([]);
const flockId = ref('');
const days = ref(30);
const summary = ref({ data: [] });

const loadData = () => {
    loadingDiv.value = true;

    axios.get('/admin/egg-kpis/feed-conversion', {
        params: {
            flock_id: flockId.value || undefined,
            days: days.value,
        }
    }).then((response) => {
        summary.value = response.data;
        loadingDiv.value = false;
    }).catch(() => {
        loadingDiv.value = false;
    });
};

watch([flockId, days], () => loadData());

onMounted(() => {
    axios.get('/admin/flocks-all').then((response) => {
        flocks.value = response.data;
    });
    loadData();
});

</script>

<template>
    <div v-if="!loadingDiv">
        <h1 class="h3 mb-3">Conversão Alimentar</h1>

        <div class="row mb-3">
            <div class="col-md-4">
                <select class="form-control" v-model="flockId">
                    <option value="">Todos os lotes</option>
                    <option v-for="flock in flocks" :key="flock.id" :value="flock.id">{{ flock.code }}</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-control" v-model="days">
                    <option :value="7">Últimos 7 dias</option>
                    <option :value="30">Últimos 30 dias</option>
                    <option :value="90">Últimos 90 dias</option>
                </select>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="card border-primary h-100"><div class="card-body">
                    <h6 class="text-primary">Conversão Média (kg/ovo)</h6>
                    <h3>{{ summary.average_conversion?.toFixed?.(3) ?? summary.average_conversion ?? 0 }}</h3>
                </div></div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card border-success h-100"><div class="card-body">
                    <h6 class="text-success">Melhor</h6>
                    <h3>{{ summary.best_conversion?.toFixed?.(3) ?? summary.best_conversion ?? 0 }}</h3>
                </div></div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card border-warning h-100"><div class="card-body">
                    <h6 class="text-warning">Pior</h6>
                    <h3>{{ summary.worst_conversion?.toFixed?.(3) ?? summary.worst_conversion ?? 0 }}</h3>
                </div></div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Detalhe diário</h5></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Lote</th>
                                <th>Ração (kg)</th>
                                <th>Ovos</th>
                                <th>Conversão</th>
                            </tr>
                        </thead>
                        <tbody v-if="summary.data?.length">
                            <tr v-for="row in summary.data" :key="row.date + row.flock_code">
                                <td>{{ row.date }}</td>
                                <td>{{ row.flock_code }}</td>
                                <td>{{ row.feed_consumption_kg }}</td>
                                <td>{{ row.total_eggs }}</td>
                                <td>{{ row.conversion_rate }}</td>
                            </tr>
                        </tbody>
                        <tbody v-else>
                            <tr><td colspan="5" align="center">Sem dados no período</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div v-else>
        <div class="card"><div class="card-body text-center py-5">
            <div class="spinner-border" role="status"></div>
            <div class="mt-2">Carregando KPIs...</div>
        </div></div>
    </div>
</template>
