<script setup>

import { onMounted, ref } from 'vue';
import axios from 'axios';
import { useToastr } from '../../../../toastr';
import { Bootstrap4Pagination } from 'laravel-vue-pagination';
import moment from 'moment';

const toastr = useToastr();
const loadingDiv = ref(true);
const loadingExport = ref(false);
const flocks = ref([]);
const flockId = ref('');
const startDate = ref('');
const endDate = ref('');
const report = ref({ summary: {}, details: { data: [] } });

const qualityLabels = { clean: 'Limpo', dirty: 'Sujo', cracked: 'Rachado', deformed: 'Deformado' };
const destinationLabels = { packaged: 'Embalado', reject: 'Refugo', broken: 'Partido' };

const today = new Date();
const monthAgo = new Date();
monthAgo.setMonth(today.getMonth() - 1);
endDate.value = today.toISOString().split('T')[0];
startDate.value = monthAgo.toISOString().split('T')[0];

const loadData = (page = 1) => {
    loadingDiv.value = true;
    axios.get('/admin/egg-reports/traceability', {
        params: { start_date: startDate.value, end_date: endDate.value, flock_id: flockId.value || undefined, page }
    }).then((response) => {
        report.value = response.data;
        loadingDiv.value = false;
    }).catch(() => { loadingDiv.value = false; toastr.error('Erro ao carregar'); });
};

const exportReport = () => {
    loadingExport.value = true;
    axios.get('/admin/egg-reports/export-excel/traceability', {
        params: { start_date: startDate.value, end_date: endDate.value, flock_id: flockId.value || undefined }
    }).then((response) => {
        const blob = new Blob([JSON.stringify(response.data, null, 2)], { type: 'application/json' });
        const link = document.createElement('a');
        link.href = window.URL.createObjectURL(blob);
        link.download = `rastreabilidade-${endDate.value}.json`;
        link.click();
        toastr.success('Exportado');
    }).finally(() => { loadingExport.value = false; });
};

onMounted(() => {
    axios.get('/admin/flocks-all').then((r) => { flocks.value = r.data; });
    loadData();
});

</script>

<template>
    <div v-if="!loadingDiv">
        <h1 class="h3 mb-3">Relatório Rastreabilidade</h1>

        <div class="row mb-3 align-items-end">
            <div class="col-md-2 mb-2">
                <select class="form-control form-control-sm" v-model="flockId"><option value="">Todos lotes</option>
                    <option v-for="f in flocks" :key="f.id" :value="f.id">{{ f.code }}</option></select>
            </div>
            <div class="col-md-2 mb-2"><input type="date" class="form-control form-control-sm" v-model="startDate"></div>
            <div class="col-md-2 mb-2"><input type="date" class="form-control form-control-sm" v-model="endDate"></div>
            <div class="col-md-4 mb-2">
                <button class="btn btn-primary btn-sm mr-2" @click="loadData()">Atualizar</button>
                <button class="btn btn-outline-secondary btn-sm" @click="exportReport" :disabled="loadingExport">Exportar JSON</button>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-4 mb-2"><div class="card border-primary"><div class="card-body py-3"><h6 class="text-primary">Total Ovos</h6><h4>{{ report.summary?.total_eggs ?? 0 }}</h4></div></div></div>
        </div>

        <div class="card">
            <div class="card-header"><h6 class="mb-0">Registos de rastreabilidade</h6></div>
            <div class="card-body p-0">
                <table class="table table-striped mb-0">
                    <thead><tr><th>Rastreio</th><th>Lote</th><th>Postura</th><th>Granja</th><th>Qualidade</th><th>Destino</th></tr></thead>
                    <tbody>
                        <tr v-for="row in report.details.data" :key="row.id">
                            <td>{{ row.traceability_code }}</td>
                            <td>{{ row.flock?.code }}</td>
                            <td>{{ moment(row.lay_date).format('DD-MM-YYYY') }}</td>
                            <td>{{ row.flock?.house?.farm?.name || '-' }}</td>
                            <td>{{ qualityLabels[row.quality] || row.quality }}</td>
                            <td>{{ destinationLabels[row.destination] || row.destination }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <Bootstrap4Pagination :data="report.details" @pagination-change-page="loadData"/>
        </div>
    </div>
    <div v-else class="card"><div class="card-body text-center py-5"><div class="spinner-border"></div></div></div>
</template>
