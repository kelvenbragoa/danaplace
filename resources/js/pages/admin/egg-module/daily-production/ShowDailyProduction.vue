<script setup>

import axios from 'axios';
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import VueFeather from 'vue-feather';

const retrievedData = ref({});
const loadingDiv = ref(true);
const router = useRouter();

const getData = () => {
    axios.get(`/admin/daily-production/${router.currentRoute.value.params.id}`)
        .then((response) => {
            loadingDiv.value = false;
            retrievedData.value = response.data;
        }).catch(() => {
            loadingDiv.value = false;
        });
};

const layingRate = computed(() => {
    const birds = retrievedData.value.flock?.current_bird_count || 0;
    if (!birds) return 0;
    return Math.round((retrievedData.value.total_eggs / birds) * 100);
});

const feedConversion = computed(() => {
    if (!retrievedData.value.total_eggs) return 0;
    return (retrievedData.value.feed_consumption_kg / retrievedData.value.total_eggs).toFixed(3);
});

onMounted(() => {
    getData();
});

</script>

<template>
    <div v-if="!loadingDiv">
        <h1 class="h3 mb-3">Produção Diária</h1>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Produção: {{ retrievedData.date }} - Lote {{ retrievedData.flock?.code }}</h5>
                        <router-link to="/admin/producao-diaria" class="btn btn-pill btn-primary mt-3">
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
                                <p><strong>Total de Ovos:</strong> {{ retrievedData.total_eggs }}</p>
                                <p><strong>Ovos Limpos:</strong> {{ retrievedData.clean_eggs }}</p>
                                <p><strong>Partidos:</strong> {{ retrievedData.cracked_eggs }}</p>
                                <p><strong>Sujos:</strong> {{ retrievedData.dirty_eggs }}</p>
                                <p><strong>Deformados:</strong> {{ retrievedData.deformed_eggs }}</p>
                                <p><strong>Taxa de Postura:</strong> {{ layingRate }}%</p>
                            </div>
                        </div>

                        <hr>

                        <div class="row">
                            <div class="col-md-4">
                                <p><strong>Ração (kg):</strong> {{ retrievedData.feed_consumption_kg }}</p>
                            </div>
                            <div class="col-md-4">
                                <p><strong>Água (L):</strong> {{ retrievedData.water_consumption_liters }}</p>
                            </div>
                            <div class="col-md-4">
                                <p><strong>Horas de Luz:</strong> {{ retrievedData.light_hours }}</p>
                            </div>
                        </div>

                        <p><strong>Conversão Alimentar:</strong> {{ feedConversion }} kg/ovo</p>

                        <p v-if="retrievedData.observations"><strong>Observações:</strong> {{ retrievedData.observations }}</p>
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
