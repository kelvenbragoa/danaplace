<script setup>

import axios from 'axios';
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useToastr } from '../../../../toastr';
import VueFeather from 'vue-feather';
import moment from 'moment';

const retrievedData = ref({});
const loadingDiv = ref(true);
const loadingDispatch = ref(false);
const loadingInvoice = ref(false);
const loadingTemp = ref(false);
const tempInput = ref(null);
const tempResult = ref(null);
const router = useRouter();
const toastr = useToastr();

const getData = () => {
    axios.get(`/admin/egg-shipping/${router.currentRoute.value.params.id}`)
        .then((response) => {
            loadingDiv.value = false;
            retrievedData.value = response.data;
            tempInput.value = response.data.vehicle_temperature;
        }).catch(() => {
            loadingDiv.value = false;
            toastr.error('Registo não encontrado');
            router.push({ path: '/admin/expedicao-ovos' });
        });
};

const dispatchNow = () => {
    loadingDispatch.value = true;

    axios.post(`/admin/egg-shipping/${retrievedData.value.id}/dispatch`)
        .then((response) => {
            retrievedData.value = response.data;
            toastr.success('Expedição despachada com data de hoje');
        }).catch(() => {
            toastr.error('Erro ao despachar');
        }).finally(() => {
            loadingDispatch.value = false;
        });
};

const printInvoice = () => {
    loadingInvoice.value = true;

    axios.get(`/admin/egg-shipping/invoice/${retrievedData.value.id}/print`)
        .then((response) => {
            console.log('Fatura:', response.data);
            toastr.success('Dados da fatura gerados (ver consola)');
        }).catch(() => {
            toastr.error('Erro ao gerar fatura');
        }).finally(() => {
            loadingInvoice.value = false;
        });
};

const validateTemperature = () => {
    if (tempInput.value === null || tempInput.value === '') {
        toastr.error('Informe a temperatura');
        return;
    }

    loadingTemp.value = true;

    axios.post('/admin/egg-shipping/validate-temperature', {
        temperature: Number(tempInput.value),
    }).then((response) => {
        tempResult.value = response.data;
        if (response.data.valid) {
            toastr.success(response.data.message);
        } else {
            toastr.warning(response.data.message);
        }
    }).catch(() => {
        toastr.error('Erro ao validar temperatura');
    }).finally(() => {
        loadingTemp.value = false;
    });
};

onMounted(() => {
    getData();
});

</script>

<template>
    <div v-if="!loadingDiv">
        <h1 class="h3 mb-3">Expedição de Ovos</h1>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Expedição #{{ retrievedData.id }} — {{ retrievedData.invoice_number }}</h5>
                        <router-link to="/admin/expedicao-ovos" class="btn btn-pill btn-primary mt-3">
                            <vue-feather type="arrow-left"></vue-feather>Voltar
                        </router-link>
                        <router-link :to="'/admin/expedicao-ovos/' + retrievedData.id + '/edit'" class="btn btn-pill btn-secondary mt-3 ml-2">
                            <vue-feather type="edit-2"></vue-feather>Editar
                        </router-link>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Transporte</h6>
                                <p><strong>Data:</strong> {{ moment(retrievedData.shipping_date).format('DD-MM-YYYY') }}</p>
                                <p><strong>Fatura:</strong> {{ retrievedData.invoice_number }}</p>
                                <p><strong>Transportadora:</strong> {{ retrievedData.carrier }}</p>
                                <p><strong>Motorista:</strong> {{ retrievedData.driver_name }}</p>
                                <p><strong>Matrícula:</strong> {{ retrievedData.vehicle_plate }}</p>
                                <p><strong>Temperatura:</strong> {{ retrievedData.vehicle_temperature ?? '-' }} °C</p>
                                <p><strong>Lacre:</strong> {{ retrievedData.seal_number || '-' }}</p>
                                <p><strong>Certificado Sanitário:</strong> {{ retrievedData.health_certificate || '-' }}</p>
                                <p><strong>Responsável:</strong> {{ retrievedData.responsible?.name || '-' }}</p>
                            </div>
                            <div class="col-md-6">
                                <h6>Pedido</h6>
                                <p><strong>Cliente:</strong> {{ retrievedData.order?.customer_name || '-' }}</p>
                                <p><strong>Categoria:</strong> {{ retrievedData.order?.category?.name || '-' }}</p>
                                <p><strong>Dúzias:</strong> {{ retrievedData.order?.quantity_dozens || '-' }}</p>

                                <h6 class="mt-3">Estoque</h6>
                                <p><strong>Rastreio:</strong> {{ retrievedData.inventory?.egg?.traceability_code || '-' }}</p>
                                <p><strong>Quantidade:</strong> {{ retrievedData.inventory?.quantity || '-' }}</p>
                                <p><strong>Galpão:</strong> {{ retrievedData.inventory?.house?.name || '-' }}</p>
                            </div>
                        </div>

                        <hr>

                        <div class="row align-items-end mb-3">
                            <div class="col-md-3">
                                <label class="form-label" for="temp_check">Validar Temperatura (°C)</label>
                                <input type="number" step="0.1" id="temp_check" class="form-control" v-model="tempInput">
                            </div>
                            <div class="col-md-3">
                                <button class="btn btn-info" @click.prevent="validateTemperature" :disabled="loadingTemp">
                                    <div v-if="loadingTemp" class="spinner-border spinner-border-sm" role="status"></div>
                                    <span v-else>Validar</span>
                                </button>
                            </div>
                            <div class="col-md-6" v-if="tempResult">
                                <span class="badge" :class="tempResult.valid ? 'badge-success' : 'badge-danger'">
                                    {{ tempResult.message }}
                                </span>
                            </div>
                        </div>

                        <button class="btn btn-warning mr-2" @click.prevent="dispatchNow" :disabled="loadingDispatch">
                            <div v-if="loadingDispatch" class="spinner-border spinner-border-sm" role="status"></div>
                            <span v-else>Despachar Hoje</span>
                        </button>

                        <button class="btn btn-success" @click.prevent="printInvoice" :disabled="loadingInvoice">
                            <div v-if="loadingInvoice" class="spinner-border spinner-border-sm" role="status"></div>
                            <span v-else>Gerar Fatura</span>
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
