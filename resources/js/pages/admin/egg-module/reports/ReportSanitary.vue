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
const report = ref({ summary: {}, vaccinations: { data: [] }, mortality: { data: [] } });

const today = new Date();
const monthAgo = new Date();
monthAgo.setMonth(today.getMonth() - 1);
endDate.value = today.toISOString().split('T')[0];
startDate.value = monthAgo.toISOString().split('T')[0];

const scheduleStatus = { pending: 'Pendente', applied: 'Aplicada', canceled: 'Cancelada' };

const loadData = () => {
    loadingDiv.value = true;
    axios.get('/admin/egg-reports/sanitary', {
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
        <h1 class="h3 mb-3">Relatório Sanitário</h1>

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
            <div class="col-md-3 mb-2"><div class="card border-info"><div class="card-body py-3"><h6 class="text-info">Vacinações</h6><h4>{{ report.summary?.vaccinations_total ?? 0 }}</h4></div></div></div>
            <div class="col-md-3 mb-2"><div class="card border-success"><div class="card-body py-3"><h6 class="text-success">Aplicadas</h6><h4>{{ report.summary?.vaccinations_applied ?? 0 }}</h4></div></div></div>
            <div class="col-md-3 mb-2"><div class="card border-warning"><div class="card-body py-3"><h6 class="text-warning">Pendentes</h6><h4>{{ report.summary?.vaccinations_pending ?? 0 }}</h4></div></div></div>
            <div class="col-md-3 mb-2"><div class="card border-danger"><div class="card-body py-3"><h6 class="text-danger">Mortalidade</h6><h4>{{ report.summary?.mortality_total ?? 0 }} aves</h4></div></div></div>
        </div>

        <div class="row">
            <div class="col-lg-6 mb-3">
                <div class="card h-100">
                    <div class="card-header"><h6 class="mb-0">Calendário Vacinal</h6></div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-striped mb-0">
                            <thead><tr><th>Data</th><th>Lote</th><th>Vacina</th><th>Estado</th></tr></thead>
                            <tbody>
                                <tr v-for="row in report.vaccinations.data" :key="row.id">
                                    <td>{{ moment(row.scheduled_date).format('DD-MM-YYYY') }}</td>
                                    <td>{{ row.flock?.code }}</td>
                                    <td>{{ row.vaccine?.name }}</td>
                                    <td>{{ scheduleStatus[row.status] || row.status }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mb-3">
                <div class="card h-100">
                    <div class="card-header"><h6 class="mb-0">Mortalidade</h6></div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-striped mb-0">
                            <thead><tr><th>Data</th><th>Lote</th><th>Qtd</th><th>Causa</th></tr></thead>
                            <tbody>
                                <tr v-for="row in report.mortality.data" :key="row.id">
                                    <td>{{ moment(row.date).format('DD-MM-YYYY') }}</td>
                                    <td>{{ row.flock?.code }}</td>
                                    <td>{{ row.quantity }}</td>
                                    <td>{{ row.probable_cause || '-' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div v-else class="card"><div class="card-body text-center py-5"><div class="spinner-border"></div></div></div>
</template>
