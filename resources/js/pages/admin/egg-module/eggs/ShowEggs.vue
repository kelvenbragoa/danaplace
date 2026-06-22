<script setup>

import axios from 'axios';
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import VueFeather from 'vue-feather';
import moment from 'moment';

const retrievedData = ref({});
const loadingDiv = ref(true);
const router = useRouter();

const qualityLabels = {
    clean: 'Limpo',
    dirty: 'Sujo',
    cracked: 'Rachado',
    deformed: 'Deformado',
};

const destinationLabels = {
    packaged: 'Embalado',
    reject: 'Refugo',
    broken: 'Partido',
};

const getData = () => {
    axios.get(`/admin/eggs/${router.currentRoute.value.params.id}`)
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
        <h1 class="h3 mb-3">Ovo</h1>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Rastreio: {{ retrievedData.traceability_code }}</h5>
                        <router-link to="/admin/ovos" class="btn btn-pill btn-primary mt-3">
                            <vue-feather type="arrow-left"></vue-feather>Voltar
                        </router-link>
                        <router-link :to="'/admin/ovos/' + retrievedData.id + '/edit'" class="btn btn-pill btn-secondary mt-3 ml-2">
                            <vue-feather type="edit-2"></vue-feather>Editar
                        </router-link>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Código de Rastreio:</strong> {{ retrievedData.traceability_code }}</p>
                                <p><strong>Data de Postura:</strong> {{ moment(retrievedData.lay_date).format('DD-MM-YYYY') }}</p>
                                <p><strong>Qualidade:</strong> {{ qualityLabels[retrievedData.quality] || retrievedData.quality }}</p>
                                <p><strong>Destino:</strong> {{ destinationLabels[retrievedData.destination] || retrievedData.destination }}</p>
                                <p><strong>Motivo Refugo:</strong> {{ retrievedData.reject_reason || '-' }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Lote:</strong> {{ retrievedData.flock?.code || '-' }}</p>
                                <p><strong>Categoria:</strong> {{ retrievedData.category?.name || '-' }}</p>
                                <p><strong>Classificação:</strong> {{ retrievedData.classification ? 'ID #' + retrievedData.classification.id : '-' }}</p>
                                <p><strong>Data Classificação:</strong> {{ moment(retrievedData.classification_date).format('DD-MM-YYYY') || '-' }}</p>
                                <p><strong>Em Estoque:</strong> {{ retrievedData.inventory ? 'Sim (' + retrievedData.inventory.quantity + ')' : 'Não' }}</p>
                            </div>
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
