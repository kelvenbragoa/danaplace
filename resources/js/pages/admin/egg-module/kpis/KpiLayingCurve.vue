<script setup>

import { onMounted, ref, watch } from 'vue';
import axios from 'axios';
import { useToastr } from '../../../../toastr';
import moment from 'moment';
const toastr = useToastr();
const loadingDiv = ref(true);
const flocks = ref([]);
const flockId = ref('');
const data = ref(null);

const loadData = () => {
    if (!flockId.value) {
        data.value = null;
        loadingDiv.value = false;
        return;
    }

    loadingDiv.value = true;

    axios.get('/admin/egg-kpis/laying-curve', {
        params: { flock_id: flockId.value }
    }).then((response) => {
        data.value = response.data;
        loadingDiv.value = false;
    }).catch((error) => {
        loadingDiv.value = false;
        toastr.error(error.response?.data?.error || 'Erro ao carregar curva');
    });
};

watch(flockId, () => loadData());

onMounted(() => {
    axios.get('/admin/flocks-active').then((response) => {
        flocks.value = response.data;
        if (flocks.value.length > 0) {
            flockId.value = flocks.value[0].id;
        } else {
            loadingDiv.value = false;
        }
    }).catch(() => {
        axios.get('/admin/flocks-all').then((response) => {
            flocks.value = response.data;
            loadingDiv.value = false;
        });
    });
});

</script>

<template>
    <div>
        <h1 class="h3 mb-3">Curva de Postura</h1>

        <div class="row mb-3">
            <div class="col-md-4">
                <select class="form-control" v-model="flockId">
                    <option value="">Selecione o lote</option>
                    <option v-for="flock in flocks" :key="flock.id" :value="flock.id">{{ flock.code }}</option>
                </select>
            </div>
        </div>

        <div v-if="!flockId" class="alert alert-info">Selecione um lote para ver a curva de postura.</div>

        <div v-else-if="!loadingDiv && data">
            <div class="row mb-3">
                <div class="col-md-6">
                    <p><strong>Lote:</strong> {{ data.flock_code }}</p>
                    <p><strong>Linhagem:</strong> {{ data.lineage }}</p>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-4 mb-3">
                    <div class="card h-100">
                        <div class="card-header"><h6 class="mb-0">Produção Real</h6></div>
                        <div class="card-body p-0">
                            <div class="table-responsive" style="max-height: 400px;">
                                <table class="table table-sm table-striped mb-0">
                                    <thead><tr><th>Data</th><th>Ovos</th></tr></thead>
                                    <tbody>
                                        <tr v-for="row in data.actual" :key="row.date">
                                            <td>{{ moment(row.date).format('DD-MM-YYYY') }}</td>
                                            <td>{{ row.total_eggs }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 mb-3">
                    <div class="card h-100">
                        <div class="card-header"><h6 class="mb-0">Projeção Esperada</h6></div>
                        <div class="card-body p-0">
                            <div class="table-responsive" style="max-height: 400px;">
                                <table class="table table-sm table-striped mb-0">
                                    <thead><tr><th>Data</th><th>Ovos esp.</th></tr></thead>
                                    <tbody>
                                        <tr v-for="row in data.expected" :key="row.date">
                                            <td>{{ moment(row.date).format('DD-MM-YYYY') }}</td>
                                            <td>{{ row.expected_eggs }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 mb-3">
                    <div class="card h-100">
                        <div class="card-header"><h6 class="mb-0">Desvio</h6></div>
                        <div class="card-body p-0">
                            <div class="table-responsive" style="max-height: 400px;">
                                <table class="table table-sm table-striped mb-0">
                                    <thead><tr><th>Data</th><th>Real</th><th>Esp.</th><th>%</th></tr></thead>
                                    <tbody>
                                        <tr v-for="row in data.deviation" :key="row.date">
                                            <td>{{ moment(row.date).format('DD-MM-YYYY') }}</td>
                                            <td>{{ row.actual }}</td>
                                            <td>{{ row.expected }}</td>
                                            <td :class="{'text-danger': row.percentage < 0, 'text-success': row.percentage > 0}">
                                                {{ row.percentage?.toFixed?.(1) ?? row.percentage }}%
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-else class="card">
            <div class="card-body text-center py-5">
                <div class="spinner-border" role="status"></div>
                <div class="mt-2">Carregando curva...</div>
            </div>
        </div>
    </div>
</template>
