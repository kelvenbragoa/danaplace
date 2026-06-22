<script setup>

import axios from 'axios';
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import VueFeather from 'vue-feather';
import moment from 'moment';

const retrievedData = ref({});
const loadingDiv = ref(true);
const router = useRouter();

const statusLabels = {
    growing: 'Recria',
    laying: 'Postura',
    disposed: 'Descartado',
};

const getData = () => {
    axios.get(`/admin/lineages/${router.currentRoute.value.params.id}`)
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
        <h1 class="h3 mb-3">Linhagem</h1>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Linhagem: {{ retrievedData.name }}</h5>
                        <router-link to="/admin/linhagens" class="btn btn-pill btn-primary mt-3">
                            <vue-feather type="arrow-left"></vue-feather>Voltar
                        </router-link>
                    </div>

                    <div class="card-body">
                        <p><strong>Nome:</strong> {{ retrievedData.name }}</p>
                        <p><strong>Fornecedor:</strong> {{ retrievedData.supplier }}</p>
                        <p><strong>Dias de Produção:</strong> {{ retrievedData.production_days }}</p>
                        <p>
                            <strong>Estado:</strong>
                            <span class="badge bg-success" v-if="retrievedData.is_active">Ativa</span>
                            <span class="badge bg-danger" v-else>Inativa</span>
                        </p>

                        <hr>
                        <h5 class="card-title">Lotes ({{ retrievedData.flocks?.length || 0 }})</h5>

                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Código</th>
                                        <th>Galpão</th>
                                        <th>Aves Atuais</th>
                                        <th>Estado</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody v-if="retrievedData.flocks && retrievedData.flocks.length > 0">
                                    <tr v-for="(flock, index) in retrievedData.flocks" :key="flock.id">
                                        <td>#{{ index + 1 }}</td>
                                        <td>{{ flock.code }}</td>
                                        <td>{{ flock.house?.name || '-' }}</td>
                                        <td>{{ flock.current_bird_count }}</td>
                                        <td>{{ statusLabels[flock.status] || flock.status }}</td>
                                        <td>
                                            <router-link :to="'/admin/lotes/' + flock.id"><vue-feather type="eye"></vue-feather></router-link>
                                        </td>
                                    </tr>
                                </tbody>
                                <tbody v-else>
                                    <tr>
                                        <td colspan="6" align="center">Nenhum lote registado</td>
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
                    <div class="spinner-border" role="status">
                        <span class="sr-only"></span>
                    </div>
                </div>
                <br>
                <div class="d-flex justify-content-center">
                    Carregando Dados...
                </div>
            </div>
        </div>
    </div>
</template>
