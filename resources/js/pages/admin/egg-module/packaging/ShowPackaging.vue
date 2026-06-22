<script setup>

import axios from 'axios';
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useToastr } from '../../../../toastr';
import VueFeather from 'vue-feather';
import moment from 'moment';

const retrievedData = ref({});
const loadingDiv = ref(true);
const loadingQr = ref(false);
const router = useRouter();
const toastr = useToastr();

const packageTypeLabels = {
    tray: 'Bandeja',
    box: 'Caixa',
};

const getData = () => {
    axios.get(`/admin/packaging/${router.currentRoute.value.params.id}`)
        .then((response) => {
            loadingDiv.value = false;
            retrievedData.value = response.data;
        }).catch(() => {
            loadingDiv.value = false;
            toastr.error('Registo não encontrado');
            router.push({ path: '/admin/embalagem' });
        });
};

const generateQr = () => {
    loadingQr.value = true;

    axios.get(`/admin/packaging/generate-qr/${retrievedData.value.id}`)
        .then((response) => {
            retrievedData.value.qr_code = response.data.qr_code;
            toastr.success('QR Code gerado com sucesso');
        }).catch(() => {
            toastr.error('Erro ao gerar QR Code');
        }).finally(() => {
            loadingQr.value = false;
        });
};

onMounted(() => {
    getData();
});

</script>

<template>
    <div v-if="!loadingDiv">
        <h1 class="h3 mb-3">Embalagem</h1>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Embalagem #{{ retrievedData.id }}</h5>
                        <router-link to="/admin/embalagem" class="btn btn-pill btn-primary mt-3">
                            <vue-feather type="arrow-left"></vue-feather>Voltar
                        </router-link>
                        <router-link :to="'/admin/embalagem/' + retrievedData.id + '/edit'" class="btn btn-pill btn-secondary mt-3 ml-2">
                            <vue-feather type="edit-2"></vue-feather>Editar
                        </router-link>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Classificação:</strong> #{{ retrievedData.classification_id }}</p>
                                <p><strong>Lote:</strong> {{ retrievedData.classification?.flock?.code || '-' }}</p>
                                <p><strong>Galpão:</strong> {{ retrievedData.classification?.flock?.house?.name || '-' }}</p>
                                <p><strong>Tipo:</strong> {{ packageTypeLabels[retrievedData.package_type] || retrievedData.package_type }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Embalagens Usadas:</strong> {{ retrievedData.quantity_used }}</p>
                                <p><strong>Ovos Embalados:</strong> {{ retrievedData.packaged_eggs }}</p>
                                <p><strong>Ovos Restantes:</strong> {{ retrievedData.remaining_eggs }}</p>
                                <p><strong>Validade:</strong> {{ moment(retrievedData.expiry_date).format('DD-MM-YYYY') }}</p>
                            </div>
                        </div>

                        <hr>

                        <p><strong>QR Code:</strong> {{ retrievedData.qr_code }}</p>

                        <button class="btn btn-info" @click.prevent="generateQr" :disabled="loadingQr">
                            <div v-if="loadingQr" class="spinner-border spinner-border-sm" role="status"></div>
                            <span v-else>Gerar QR Code</span>
                        </button>
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
