<script setup>

import axios from 'axios';
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import VueFeather from 'vue-feather';
import moment from 'moment';

const retrievedData = ref({});
const loadingDiv = ref(true);
const router = useRouter();

const getData = () => {
    axios.get(`/admin/farms/${router.currentRoute.value.params.id}`)
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
        <h1 class="h3 mb-3">Granja</h1>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Granja: {{ retrievedData.name }}</h5>
                        <router-link to="/admin/granjas" class="btn btn-pill btn-primary mt-3">
                            <vue-feather type="arrow-left"></vue-feather>Voltar
                        </router-link>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-xl-12 col-xxl-12 d-flex">
                                <div class="w-100">
                                    <p><strong>Nome:</strong> {{ retrievedData.name }}</p>
                                    <p><strong>NUIT:</strong> {{ retrievedData.tax_id }}</p>
                                    <p><strong>Endereço:</strong> {{ retrievedData.address || '-' }}</p>
                                    <p><strong>Telefone:</strong> {{ retrievedData.phone || '-' }}</p>
                                    <p><strong>Email:</strong> {{ retrievedData.email || '-' }}</p>
                                    <p>
                                        <strong>Estado:</strong>
                                        <span class="badge bg-success" v-if="retrievedData.is_active">Ativa</span>
                                        <span class="badge bg-danger" v-else>Inativa</span>
                                    </p>

                                    <hr>
                                    <h5 class="card-title">Galpões ({{ retrievedData.houses?.length || 0 }})</h5>

                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Nome</th>
                                                    <th>Código</th>
                                                    <th>Capacidade</th>
                                                    <th>Caixas</th>
                                                    <th>Automação</th>
                                                    <th>Estado</th>
                                                    <th>Ações</th>
                                                </tr>
                                            </thead>
                                            <tbody v-if="retrievedData.houses && retrievedData.houses.length > 0">
                                                <tr v-for="(house, index) in retrievedData.houses" :key="house.id">
                                                    <td>#{{ index + 1 }}</td>
                                                    <td>{{ house.name }}</td>
                                                    <td>{{ house.code || '-' }}</td>
                                                    <td>{{ house.bird_capacity }}</td>
                                                    <td>{{ house.boxes }}</td>
                                                    <td>
                                                        <span class="badge bg-success" v-if="house.has_automation">Sim</span>
                                                        <span class="badge bg-secondary" v-else>Não</span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-success" v-if="house.is_active">Ativo</span>
                                                        <span class="badge bg-danger" v-else>Inativo</span>
                                                    </td>
                                                    <td>
                                                        <router-link :to="'/admin/galpoes/' + house.id"><vue-feather type="eye"></vue-feather></router-link>
                                                    </td>
                                                </tr>
                                            </tbody>
                                            <tbody v-else>
                                                <tr>
                                                    <td colspan="8" align="center">Nenhum galpão registado</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
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
