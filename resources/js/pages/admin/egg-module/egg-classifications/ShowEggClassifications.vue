<script setup>

import axios from 'axios';
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import VueFeather from 'vue-feather';

const retrievedData = ref({});
const loadingDiv = ref(true);
const router = useRouter();

const totalProcessed = computed(() => {
    return (retrievedData.value.washed_eggs || 0) + (retrievedData.value.unwashed_eggs || 0);
});

const getData = () => {
    axios.get(`/admin/egg-classifications/${router.currentRoute.value.params.id}`)
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
        <h1 class="h3 mb-3">Classificação de Ovos</h1>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Classificação: {{ retrievedData.processing_date }} - Lote {{ retrievedData.flock?.code }}</h5>
                        <router-link to="/admin/classificacao-ovos" class="btn btn-pill btn-primary mt-3">
                            <vue-feather type="arrow-left"></vue-feather>Voltar
                        </router-link>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Data:</strong> {{ retrievedData.processing_date }}</p>
                                <p><strong>Lote:</strong> {{ retrievedData.flock?.code || '-' }}</p>
                                <p><strong>Galpão:</strong> {{ retrievedData.flock?.house?.name || '-' }}</p>
                                <p><strong>Granja:</strong> {{ retrievedData.flock?.house?.farm?.name || '-' }}</p>
                                <p><strong>Responsável:</strong> {{ retrievedData.responsible?.name || '-' }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Ovos Lavados:</strong> {{ retrievedData.washed_eggs }}</p>
                                <p><strong>Ovos Não Lavados:</strong> {{ retrievedData.unwashed_eggs }}</p>
                                <p><strong>Total Processado:</strong> {{ totalProcessed }}</p>
                                <p><strong>Total Refugos:</strong> <span class="text-danger">{{ retrievedData.total_rejects }}</span></p>
                                <p><strong>Taxa de Refugo:</strong> {{ Math.round(retrievedData.reject_percentage) }}%</p>
                            </div>
                        </div>

                        <hr v-if="retrievedData.packaging && retrievedData.packaging.length > 0">
                        <h5 v-if="retrievedData.packaging && retrievedData.packaging.length > 0" class="card-title">
                            Embalagens ({{ retrievedData.packaging.length }})
                        </h5>

                        <div v-if="retrievedData.packaging && retrievedData.packaging.length > 0" class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Tipo Embalagem</th>
                                        <th>Ovos Embalados</th>
                                        <th>Validade</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(pack, index) in retrievedData.packaging" :key="pack.id">
                                        <td>#{{ index + 1 }}</td>
                                        <td>{{ pack.package_type || '-' }}</td>
                                        <td>{{ pack.packaged_eggs || '-' }}</td>
                                        <td>{{ pack.expiry_date || '-' }}</td>
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
