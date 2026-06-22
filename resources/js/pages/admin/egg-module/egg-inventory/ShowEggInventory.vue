<script setup>

import axios from 'axios';
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useToastr } from '../../../../toastr';
import VueFeather from 'vue-feather';
import moment from 'moment';

const retrievedData = ref({});
const loadingDiv = ref(true);
const loadingReserve = ref(false);
const loadingRelease = ref(false);
const reserveQuantity = ref(1);
const router = useRouter();
const toastr = useToastr();

const statusLabels = {
    available: 'Disponível',
    reserved: 'Reservado',
    shipped: 'Expedido',
};

const qualityLabels = {
    clean: 'Limpo',
    dirty: 'Sujo',
    cracked: 'Rachado',
    deformed: 'Deformado',
};

const getData = () => {
    axios.get(`/admin/egg-inventory/${router.currentRoute.value.params.id}`)
        .then((response) => {
            loadingDiv.value = false;
            retrievedData.value = response.data;
            reserveQuantity.value = response.data.quantity;
        }).catch(() => {
            loadingDiv.value = false;
            toastr.error('Registo não encontrado');
            router.push({ path: '/admin/estoque-ovos' });
        });
};

const reserveStock = () => {
    loadingReserve.value = true;

    axios.post(`/admin/egg-inventory/${retrievedData.value.id}/reserve`, {
        quantity: Number(reserveQuantity.value),
    }).then(() => {
        toastr.success('Stock reservado com sucesso');
        getData();
    }).catch((error) => {
        toastr.error(error.response?.data?.message || 'Erro ao reservar stock');
    }).finally(() => {
        loadingReserve.value = false;
    });
};

const releaseStock = () => {
    loadingRelease.value = true;

    axios.post(`/admin/egg-inventory/${retrievedData.value.id}/release`)
        .then(() => {
            toastr.success('Stock libertado com sucesso');
            getData();
        }).catch(() => {
            toastr.error('Erro ao libertar stock');
        }).finally(() => {
            loadingRelease.value = false;
        });
};

onMounted(() => {
    getData();
});

</script>

<template>
    <div v-if="!loadingDiv">
        <h1 class="h3 mb-3">Estoque de Ovos</h1>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Detalhe do estoque #{{ retrievedData.id }}</h5>
                        <router-link to="/admin/estoque-ovos" class="btn btn-pill btn-primary mt-3">
                            <vue-feather type="arrow-left"></vue-feather>Voltar
                        </router-link>
                        <router-link :to="'/admin/estoque-ovos/' + retrievedData.id + '/edit'" class="btn btn-pill btn-secondary mt-3 ml-2">
                            <vue-feather type="edit-2"></vue-feather>Editar
                        </router-link>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Dados do Estoque</h6>
                                <p><strong>Quantidade:</strong> {{ retrievedData.quantity }}</p>
                                <p><strong>Data de Entrada:</strong> {{ moment(retrievedData.entry_date).format('DD-MM-YYYY') }}</p>
                                <p><strong>Data de Saída:</strong> {{ moment(retrievedData.exit_date).format('DD-MM-YYYY') || '-' }}</p>
                                <p><strong>Localização:</strong> {{ retrievedData.location || '-' }}</p>
                                <p><strong>Estado:</strong> {{ statusLabels[retrievedData.status] || retrievedData.status }}</p>
                                <p><strong>Galpão:</strong> {{ retrievedData.house?.name || '-' }}</p>
                                <p><strong>Granja:</strong> {{ retrievedData.house?.farm?.name || '-' }}</p>
                            </div>
                            <div class="col-md-6">
                                <h6>Dados do Ovo</h6>
                                <p><strong>Rastreio:</strong> {{ retrievedData.egg?.traceability_code || '-' }}</p>
                                <p><strong>Categoria:</strong> {{ retrievedData.egg?.category?.name || '-' }}</p>
                                <p><strong>Data de Postura:</strong> {{ moment(retrievedData.egg?.lay_date).format('DD-MM-YYYY') || '-' }}</p>
                                <p><strong>Qualidade:</strong> {{ qualityLabels[retrievedData.egg?.quality] || retrievedData.egg?.quality || '-' }}</p>
                                <p><strong>Lote:</strong> {{ retrievedData.egg?.flock?.code || '-' }}</p>
                            </div>
                        </div>

                        <hr v-if="retrievedData.status === 'available' || retrievedData.status === 'reserved'">

                        <div v-if="retrievedData.status === 'available'" class="row align-items-end">
                            <div class="col-md-4">
                                <label class="form-label" for="reserve_quantity">Quantidade a reservar</label>
                                <input type="number" id="reserve_quantity" class="form-control" v-model="reserveQuantity" :max="retrievedData.quantity" min="1">
                            </div>
                            <div class="col-md-4">
                                <button class="btn btn-warning" @click.prevent="reserveStock" :disabled="loadingReserve">
                                    <div v-if="loadingReserve" class="spinner-border spinner-border-sm" role="status"></div>
                                    <span v-else>Reservar Stock</span>
                                </button>
                            </div>
                        </div>

                        <div v-if="retrievedData.status === 'reserved'" class="mt-2">
                            <button class="btn btn-success" @click.prevent="releaseStock" :disabled="loadingRelease">
                                <div v-if="loadingRelease" class="spinner-border spinner-border-sm" role="status"></div>
                                <span v-else>Libertar Reserva</span>
                            </button>
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
