<script setup>

import axios from 'axios';
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import VueFeather from 'vue-feather';

const retrievedData = ref({});
const loadingDiv = ref(true);
const router = useRouter();

const getData = () => {
    axios.get(`/admin/mortality/${router.currentRoute.value.params.id}`)
        .then((response) => {
            loadingDiv.value = false;
            retrievedData.value = response.data;
        }).catch(() => {
            loadingDiv.value = false;
        });
};

const mortalityRate = computed(() => {
    const flock = retrievedData.value.flock;
    if (!flock?.initial_bird_count) return 0;
    const deaths = flock.initial_bird_count - flock.current_bird_count;
    return Math.round((deaths / flock.initial_bird_count) * 100);
});

onMounted(() => {
    getData();
});

</script>

<template>
    <div v-if="!loadingDiv">
        <h1 class="h3 mb-3">Mortalidade</h1>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Registo: {{ retrievedData.date }} - Lote {{ retrievedData.flock?.code }}</h5>
                        <router-link to="/admin/mortalidade" class="btn btn-pill btn-primary mt-3">
                            <vue-feather type="arrow-left"></vue-feather>Voltar
                        </router-link>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Data:</strong> {{ retrievedData.date }}</p>
                                <p><strong>Lote:</strong> {{ retrievedData.flock?.code || '-' }}</p>
                                <p><strong>Galpão:</strong> {{ retrievedData.flock?.house?.name || '-' }}</p>
                                <p><strong>Granja:</strong> {{ retrievedData.flock?.house?.farm?.name || '-' }}</p>
                                <p><strong>Linhagem:</strong> {{ retrievedData.flock?.lineage?.name || '-' }}</p>
                                <p><strong>Responsável:</strong> {{ retrievedData.responsible?.name || '-' }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Quantidade:</strong> <span class="text-danger">{{ retrievedData.quantity }}</span></p>
                                <p><strong>Causa Provável:</strong> {{ retrievedData.probable_cause || '-' }}</p>
                                <p>
                                    <strong>Necropsia:</strong>
                                    <span class="badge bg-success" v-if="retrievedData.necropsy_performed">Sim</span>
                                    <span class="badge bg-secondary" v-else>Não</span>
                                </p>
                                <p><strong>Aves Atuais do Lote:</strong> {{ retrievedData.flock?.current_bird_count }}</p>
                                <p><strong>Taxa de Mortalidade do Lote:</strong> {{ mortalityRate }}%</p>
                            </div>
                        </div>

                        <div v-if="retrievedData.necropsy_report">
                            <hr>
                            <p><strong>Relatório de Necropsia:</strong></p>
                            <p>{{ retrievedData.necropsy_report }}</p>
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
