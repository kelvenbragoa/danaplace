<script setup>

import { onMounted, ref, watch } from 'vue';
import axios from 'axios';

const loadingDiv = ref(true);
const flocks = ref([]);
const flockId = ref('');
const summary = ref({ by_flock: [] });

const loadData = () => {
    loadingDiv.value = true;

    axios.get('/admin/egg-kpis/mortality-rate', {
        params: { flock_id: flockId.value || undefined }
    }).then((response) => {
        summary.value = response.data;
        loadingDiv.value = false;
    }).catch(() => {
        loadingDiv.value = false;
    });
};

watch(flockId, () => loadData());

onMounted(() => {
    axios.get('/admin/flocks-all').then((response) => {
        flocks.value = response.data;
    });
    loadData();
});

const statusLabels = {
    growing: 'Cria',
    laying: 'Postura',
    disposed: 'Descartado',
};

</script>

<template>
    <div v-if="!loadingDiv">
        <h1 class="h3 mb-3">Taxa de Mortalidade</h1>

        <div class="row mb-3">
            <div class="col-md-4">
                <select class="form-control" v-model="flockId">
                    <option value="">Todos os lotes</option>
                    <option v-for="flock in flocks" :key="flock.id" :value="flock.id">{{ flock.code }}</option>
                </select>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="card border-danger h-100"><div class="card-body">
                    <h6 class="text-danger">Taxa Global Média</h6>
                    <h3>{{ summary.overall_rate?.toFixed?.(2) ?? summary.overall_rate ?? 0 }}%</h3>
                </div></div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Por lote</h5></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Lote</th>
                                <th>Aves Iniciais</th>
                                <th>Aves Atuais</th>
                                <th>Mortes</th>
                                <th>Taxa %</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody v-if="summary.by_flock?.length">
                            <tr v-for="row in summary.by_flock" :key="row.flock_code">
                                <td>{{ row.flock_code }}</td>
                                <td>{{ row.initial_birds }}</td>
                                <td>{{ row.current_birds }}</td>
                                <td>{{ row.total_deaths }}</td>
                                <td>{{ row.mortality_rate?.toFixed?.(2) ?? row.mortality_rate }}%</td>
                                <td>{{ statusLabels[row.status] || row.status }}</td>
                            </tr>
                        </tbody>
                        <tbody v-else>
                            <tr><td colspan="6" align="center">Sem dados</td></tr>
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
