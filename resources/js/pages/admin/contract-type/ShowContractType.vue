<script setup>

import axios from 'axios';
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import VueFeather from 'vue-feather';

const retrievedData = ref({});
const loadingDiv = ref(true);
const router = useRouter();

const getData = () => {
    axios.get(`/contract-types/${router.currentRoute.value.params.id}`)
        .then((response) => {
            loadingDiv.value = false;
            retrievedData.value = response.data.contract_type;
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
        <h1 class="h3 mb-3">Tipo de Contrato</h1>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Tipo de Contrato: {{ retrievedData.name }}</h5>
                        <router-link to="/admin/contract-types" class="btn btn-pill btn-primary mt-3">
                            <vue-feather type="arrow-left"></vue-feather>Voltar
                        </router-link>
                    </div>

                    <div class="card-body">
                        <p><strong>Nome:</strong> {{ retrievedData.name }}</p>
                        <p><strong>Técnicos associados:</strong> {{ retrievedData.technicians_count }}</p>

                        <h6 class="mt-4">Campos extras configurados</h6>
                        <div v-if="retrievedData.extra_fields?.length" class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Chave</th>
                                        <th>Rótulo</th>
                                        <th>Tipo</th>
                                        <th>Obrigatório</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="field in retrievedData.extra_fields" :key="field.key">
                                        <td>{{ field.key }}</td>
                                        <td>{{ field.label }}</td>
                                        <td>{{ field.type }}</td>
                                        <td>{{ field.required ? 'Sim' : 'Não' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <p v-else class="text-muted">Nenhum campo extra configurado.</p>
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
