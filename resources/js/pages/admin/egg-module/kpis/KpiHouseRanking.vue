<script setup>

import { onMounted, ref } from 'vue';
import axios from 'axios';

const loadingDiv = ref(true);
const ranking = ref([]);
const startDate = ref('');
const endDate = ref('');

const today = new Date();
const monthAgo = new Date();
monthAgo.setMonth(today.getMonth() - 1);
endDate.value = today.toISOString().split('T')[0];
startDate.value = monthAgo.toISOString().split('T')[0];

const loadData = () => {
    loadingDiv.value = true;

    axios.get('/admin/egg-kpis/house-ranking', {
        params: {
            start_date: startDate.value,
            end_date: endDate.value,
        }
    }).then((response) => {
        ranking.value = response.data;
        loadingDiv.value = false;
    }).catch(() => {
        loadingDiv.value = false;
    });
};

onMounted(() => {
    loadData();
});

</script>

<template>
    <div v-if="!loadingDiv">
        <h1 class="h3 mb-3">Ranking de Galpões</h1>

        <div class="row mb-3 align-items-end">
            <div class="col-md-3 mb-2">
                <label class="form-label small mb-0">De</label>
                <input type="date" class="form-control" v-model="startDate">
            </div>
            <div class="col-md-3 mb-2">
                <label class="form-label small mb-0">Até</label>
                <input type="date" class="form-control" v-model="endDate">
            </div>
            <div class="col-md-3 mb-2">
                <button class="btn btn-primary" @click.prevent="loadData">Atualizar</button>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Ranking por eficiência</h5></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Galpão</th>
                                <th>Lote</th>
                                <th>Produção Total</th>
                                <th>Taxa Média %</th>
                                <th>Score Eficiência</th>
                            </tr>
                        </thead>
                        <tbody v-if="ranking.length">
                            <tr v-for="(row, index) in ranking" :key="row.flock_code">
                                <td>
                                    <span class="badge" :class="index === 0 ? 'badge-warning' : 'badge-light'">#{{ index + 1 }}</span>
                                </td>
                                <td>{{ row.house_name }}</td>
                                <td>{{ row.flock_code }}</td>
                                <td>{{ row.total_production }}</td>
                                <td>{{ row.avg_laying_rate }}%</td>
                                <td><strong>{{ row.efficiency_score }}</strong></td>
                            </tr>
                        </tbody>
                        <tbody v-else>
                            <tr><td colspan="6" align="center">Sem lotes em postura no período</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div v-else>
        <div class="card"><div class="card-body text-center py-5">
            <div class="spinner-border" role="status"></div>
            <div class="mt-2">Carregando ranking...</div>
        </div></div>
    </div>
</template>
