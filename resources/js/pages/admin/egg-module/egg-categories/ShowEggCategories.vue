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
    axios.get(`/admin/egg-categories/${router.currentRoute.value.params.id}`)
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
        <h1 class="h3 mb-3">Categoria de Ovos</h1>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Categoria: {{ retrievedData.name }}</h5>
                        <router-link to="/admin/categorias-ovos" class="btn btn-pill btn-primary mt-3">
                            <vue-feather type="arrow-left"></vue-feather>Voltar
                        </router-link>
                    </div>

                    <div class="card-body">
                        <p><strong>Nome:</strong> {{ retrievedData.name }}</p>
                        <p><strong>Peso Mínimo:</strong> {{ retrievedData.min_weight }} g</p>
                        <p><strong>Peso Máximo:</strong> {{ retrievedData.max_weight }} g</p>
                        <p>
                            <strong>Estado:</strong>
                            <span class="badge" :class="retrievedData.is_active ? 'badge-success' : 'badge-secondary'">
                                {{ retrievedData.is_active ? 'Ativo' : 'Inativo' }}
                            </span>
                        </p>
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
