<script setup>

import axios from 'axios';
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import VueFeather from 'vue-feather';

const retrievedData = ref({});
const loadingDiv = ref(true);
const router = useRouter();

const statusLabels = {
    pending: 'Pendente',
    applied: 'Aplicada',
    canceled: 'Cancelada',
};

const getData = () => {
    axios.get(`/admin/vaccines/${router.currentRoute.value.params.id}`)
        .then((response) => {
            loadingDiv.value = false;
            retrievedData.value = response.data;
        }).catch(() => {
            loadingDiv.value = false;
        });
};

onMounted(() => {
    getData();
});

</script>

<template>
    <div v-if="!loadingDiv">
        <h1 class="h3 mb-3">Vacina</h1>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Vacina: {{ retrievedData.name }}</h5>
                        <router-link to="/admin/vacinas" class="btn btn-pill btn-primary mt-3">
                            <vue-feather type="arrow-left"></vue-feather>Voltar
                        </router-link>
                    </div>

                    <div class="card-body">
                        <p><strong>Nome:</strong> {{ retrievedData.name }}</p>
                        <p><strong>Fabricante:</strong> {{ retrievedData.manufacturer }}</p>
                        <p><strong>Lote:</strong> {{ retrievedData.batch }}</p>
                        <p><strong>Data de Validade:</strong> {{ retrievedData.expiry_date }}</p>
                        <p><strong>Stock Mínimo:</strong> {{ retrievedData.min_stock }}</p>

                        <hr>
                        <h5 class="card-title">Agendamentos ({{ retrievedData.vaccination_schedule?.length || 0 }})</h5>

                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Data Prevista</th>
                                        <th>Lote</th>
                                        <th>Galpão</th>
                                        <th>Estado</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody v-if="retrievedData.vaccination_schedule && retrievedData.vaccination_schedule.length > 0">
                                    <tr v-for="(schedule, index) in retrievedData.vaccination_schedule" :key="schedule.id">
                                        <td>#{{ index + 1 }}</td>
                                        <td>{{ schedule.scheduled_date }}</td>
                                        <td>{{ schedule.flock?.code || '-' }}</td>
                                        <td>{{ schedule.flock?.house?.name || '-' }}</td>
                                        <td>{{ statusLabels[schedule.status] || schedule.status }}</td>
                                        <td>
                                            <router-link :to="'/admin/calendario-vacinal/' + schedule.id"><vue-feather type="eye"></vue-feather></router-link>
                                        </td>
                                    </tr>
                                </tbody>
                                <tbody v-else>
                                    <tr>
                                        <td colspan="6" align="center">Nenhum agendamento registado</td>
                                    </tr>
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
                <div class="d-flex justify-content-center">Carregando Dados...</div>
            </div>
        </div>
    </div>
</template>
