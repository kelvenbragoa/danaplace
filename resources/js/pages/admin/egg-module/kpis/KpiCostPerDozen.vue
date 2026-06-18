<script setup>

import { onMounted, ref, watch } from 'vue';
import axios from 'axios';

const loadingDiv = ref(true);
const flocks = ref([]);
const flockId = ref('');
const period = ref('month');
const data = ref({});

const loadData = () => {
    loadingDiv.value = true;

    axios.get('/admin/egg-kpis/cost-per-dozen', {
        params: {
            flock_id: flockId.value || undefined,
            period: period.value,
        }
    }).then((response) => {
        data.value = response.data;
        loadingDiv.value = false;
    }).catch(() => {
        loadingDiv.value = false;
    });
};

watch([flockId, period], () => loadData());

onMounted(() => {
    axios.get('/admin/flocks-all').then((response) => {
        flocks.value = response.data;
    });
    loadData();
});

</script>

<template>
    <div v-if="!loadingDiv">
        <h1 class="h3 mb-3">Custo por Dúzia</h1>

        <div class="row mb-3">
            <div class="col-md-4">
                <select class="form-control" v-model="flockId">
                    <option value="">Todos os lotes</option>
                    <option v-for="flock in flocks" :key="flock.id" :value="flock.id">{{ flock.code }}</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-control" v-model="period">
                    <option value="week">Última semana</option>
                    <option value="month">Último mês</option>
                    <option value="year">Último ano</option>
                </select>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card border-primary h-100"><div class="card-body">
                    <h6 class="text-primary">Custo / Dúzia</h6>
                    <h3>{{ data.cost_per_dozen ?? 0 }}</h3>
                </div></div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-success h-100"><div class="card-body">
                    <h6 class="text-success">Total Ovos</h6>
                    <h3>{{ data.total_eggs ?? 0 }}</h3>
                </div></div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-info h-100"><div class="card-body">
                    <h6 class="text-info">Total Dúzias</h6>
                    <h3>{{ data.total_dozens ?? 0 }}</h3>
                </div></div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-warning h-100"><div class="card-body">
                    <h6 class="text-warning">Custo Total</h6>
                    <h3>{{ data.total_cost ?? 0 }}</h3>
                </div></div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Breakdown de custos</h5></div>
            <div class="card-body">
                <div class="row" v-if="data.breakdown">
                    <div class="col-md-3"><p><strong>Ração:</strong> {{ data.breakdown.feed_cost }}</p></div>
                    <div class="col-md-3"><p><strong>Água:</strong> {{ data.breakdown.water_cost }}</p></div>
                    <div class="col-md-3"><p><strong>Mão de obra:</strong> {{ data.breakdown.labor_cost }}</p></div>
                    <div class="col-md-3"><p><strong>Outros:</strong> {{ data.breakdown.other_costs }}</p></div>
                </div>
                <p class="text-muted small mb-0" v-if="data.start_date">
                    Período: {{ data.start_date }} — {{ data.end_date }}
                </p>
            </div>
        </div>
    </div>

    <div v-else>
        <div class="card"><div class="card-body text-center py-5">
            <div class="spinner-border" role="status"></div>
            <div class="mt-2">Carregando custos...</div>
        </div></div>
    </div>
</template>
