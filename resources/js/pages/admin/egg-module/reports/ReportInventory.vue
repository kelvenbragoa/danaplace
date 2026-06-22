<script setup>

import { onMounted, ref } from 'vue';
import axios from 'axios';
import { useToastr } from '../../../../toastr';
import { Bootstrap4Pagination } from 'laravel-vue-pagination';  
import moment from 'moment';

const toastr = useToastr();
const loadingDiv = ref(true);
const statusFilter = ref('');
const report = ref({ summary: {}, by_category: [], details: { data: [] } });

const statusLabels = { available: 'Disponível', reserved: 'Reservado', shipped: 'Expedido' };

const loadData = (page = 1) => {
    loadingDiv.value = true;
    axios.get('/admin/egg-reports/inventory', {
        params: { status: statusFilter.value || undefined, page }
    }).then((response) => {
        report.value = response.data;
        loadingDiv.value = false;
    }).catch(() => { loadingDiv.value = false; toastr.error('Erro ao carregar'); });
};

onMounted(() => loadData());

</script>

<template>
    <div v-if="!loadingDiv">
        <h1 class="h3 mb-3">Relatório Estoque Ovos</h1>

        <div class="row mb-3">
            <div class="col-md-3">
                <select class="form-control form-control-sm" v-model="statusFilter" @change="loadData()">
                    <option value="">Todos estados</option>
                    <option value="available">Disponível</option>
                    <option value="reserved">Reservado</option>
                    <option value="shipped">Expedido</option>
                </select>
            </div>
            <div class="col-md-2"><button class="btn btn-primary btn-sm" @click="loadData()">Atualizar</button></div>
        </div>

        <div class="row mb-4">
            <div class="col-md-3 mb-2"><div class="card border-primary"><div class="card-body py-3"><h6 class="text-primary">Quantidade Total</h6><h4>{{ report.summary?.total_quantity ?? 0 }}</h4></div></div></div>
            <div class="col-md-3 mb-2"><div class="card border-success"><div class="card-body py-3"><h6 class="text-success">Disponível</h6><h4>{{ report.summary?.available ?? 0 }}</h4></div></div></div>
            <div class="col-md-3 mb-2"><div class="card border-warning"><div class="card-body py-3"><h6 class="text-warning">Reservado</h6><h4>{{ report.summary?.reserved ?? 0 }}</h4></div></div></div>
            <div class="col-md-3 mb-2"><div class="card border-info"><div class="card-body py-3"><h6 class="text-info">Expedido</h6><h4>{{ report.summary?.shipped ?? 0 }}</h4></div></div></div>
        </div>

        <div class="card mb-4">
            <div class="card-header"><h6 class="mb-0">Por categoria</h6></div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Categoria</th><th>Quantidade</th><th>Registos</th></tr></thead>
                    <tbody>
                        <tr v-for="row in report.by_category" :key="row.category">
                            <td>{{ row.category }}</td><td>{{ row.quantity }}</td><td>{{ row.records }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h6 class="mb-0">Detalhe</h6></div>
            <div class="card-body p-0">
                <table class="table table-striped mb-0">
                    <thead><tr><th>Rastreio</th><th>Categoria</th><th>Galpão</th><th>Qtd</th><th>Estado</th><th>Entrada</th></tr></thead>
                    <tbody>
                        <tr v-for="row in report.details.data" :key="row.id">
                            <td>{{ row.egg?.traceability_code }}</td>
                            <td>{{ row.egg?.category?.name || '-' }}</td>
                            <td>{{ row.house?.name }}</td>
                            <td>{{ row.quantity }}</td>
                            <td>{{ statusLabels[row.status] || row.status }}</td>
                            <td>{{ moment(row.entry_date).format('DD-MM-YYYY') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <Bootstrap4Pagination :data="report.details" @pagination-change-page="loadData"/>
        </div>
    </div>
    <div v-else class="card"><div class="card-body text-center py-5"><div class="spinner-border"></div></div></div>
</template>
