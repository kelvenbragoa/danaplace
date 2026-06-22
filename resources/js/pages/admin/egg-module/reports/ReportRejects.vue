<script setup>

import { onMounted, ref } from 'vue';
import axios from 'axios';
import { useToastr } from '../../../../toastr';
import moment from 'moment';

const toastr = useToastr();
const loadingDiv = ref(true);
const flocks = ref([]);
const flockId = ref('');
const startDate = ref('');
const endDate = ref('');
const report = ref({ summary: {}, by_classification_date: [], reject_eggs: [] });

const today = new Date();
const monthAgo = new Date();
monthAgo.setMonth(today.getMonth() - 1);
endDate.value = today.toISOString().split('T')[0];
startDate.value = monthAgo.toISOString().split('T')[0];

const loadData = () => {
    loadingDiv.value = true;
    axios.get('/admin/egg-reports/rejects', {
        params: { start_date: startDate.value, end_date: endDate.value, flock_id: flockId.value || undefined }
    }).then((response) => {
        report.value = response.data;
        loadingDiv.value = false;
    }).catch(() => { loadingDiv.value = false; toastr.error('Erro ao carregar'); });
};

onMounted(() => {
    axios.get('/admin/flocks-all').then((r) => { flocks.value = r.data; });
    loadData();
});

</script>

<template>
    <div v-if="!loadingDiv">
        <h1 class="h3 mb-3">Relatório de Refugos</h1>

        <div class="row mb-3 align-items-end">
            <div class="col-md-2 mb-2">
                <select class="form-control form-control-sm" v-model="flockId"><option value="">Todos lotes</option>
                    <option v-for="f in flocks" :key="f.id" :value="f.id">{{ f.code }}</option></select>
            </div>
            <div class="col-md-2 mb-2"><input type="date" class="form-control form-control-sm" v-model="startDate"></div>
            <div class="col-md-2 mb-2"><input type="date" class="form-control form-control-sm" v-model="endDate"></div>
            <div class="col-md-2 mb-2"><button class="btn btn-primary btn-sm" @click="loadData">Atualizar</button></div>
        </div>

        <div class="row mb-4">
            <div class="col-md-3 mb-2"><div class="card border-danger"><div class="card-body py-3"><h6 class="text-danger">Total Refugos</h6><h4>{{ report.summary?.total_rejects ?? 0 }}</h4></div></div></div>
            <div class="col-md-3 mb-2"><div class="card border-warning"><div class="card-body py-3"><h6>Taxa Refugo</h6><h4>{{ report.summary?.reject_rate ?? 0 }}%</h4></div></div></div>
            <div class="col-md-2 mb-2"><div class="card"><div class="card-body py-3"><small>Rachados</small><h5>{{ report.summary?.cracked ?? 0 }}</h5></div></div></div>
            <div class="col-md-2 mb-2"><div class="card"><div class="card-body py-3"><small>Sujos</small><h5>{{ report.summary?.dirty ?? 0 }}</h5></div></div></div>
            <div class="col-md-2 mb-2"><div class="card"><div class="card-body py-3"><small>Deformados</small><h5>{{ report.summary?.deformed ?? 0 }}</h5></div></div></div>
        </div>

        <div class="card mb-4">
            <div class="card-header"><h6 class="mb-0">Classificação por data</h6></div>
            <div class="card-body p-0">
                <table class="table table-sm table-striped mb-0">
                    <thead><tr><th>Data</th><th>Refugos</th><th>Processados</th><th>% Médio</th></tr></thead>
                    <tbody>
                        <tr v-for="row in report.by_classification_date" :key="row.date">
                            <td>{{ moment(row.date).format('DD-MM-YYYY') }}</td><td>{{ row.total_rejects }}</td><td>{{ row.total_processed }}</td><td>{{ row.avg_reject_percentage?.toFixed?.(1) ?? row.avg_reject_percentage }}%</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h6 class="mb-0">Ovos marcados como refugo</h6></div>
            <div class="card-body p-0">
                <table class="table table-striped mb-0">
                    <thead><tr><th>Rastreio</th><th>Lote</th><th>Postura</th><th>Motivo</th></tr></thead>
                    <tbody>
                        <tr v-for="egg in report.reject_eggs" :key="egg.id">
                            <td>{{ egg.traceability_code }}</td><td>{{ egg.flock?.code }}</td><td>{{ moment(egg.lay_date).format('DD-MM-YYYY') }}</td><td>{{ egg.reject_reason || '-' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div v-else class="card"><div class="card-body text-center py-5"><div class="spinner-border"></div></div></div>
</template>
