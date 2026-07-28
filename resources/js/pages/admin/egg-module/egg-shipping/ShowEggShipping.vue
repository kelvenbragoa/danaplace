<script setup>

import axios from 'axios';
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useToastr } from '../../../../toastr';
import VueFeather from 'vue-feather';
import moment from 'moment';
import { usePaperizer } from 'paperizer';
import EggShippingInvoicePrint from './EggShippingInvoicePrint.vue';

const retrievedData = ref({});
const loadingDiv = ref(true);
const loadingDispatch = ref(false);
const loadingInvoice = ref(false);
const loadingTemp = ref(false);
const tempInput = ref(null);
const tempResult = ref(null);
const dispatchForm = ref({
    delivery_note_number: '',
    delivered_to: '',
    delivered_at: '',
});
const router = useRouter();
const toastr = useToastr();

const { paperize } = usePaperizer('print-egg-shipping-invoice', {
    styles: [
        'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
    ],
});

const isDispatched = computed(() => Boolean(retrievedData.value.delivered_at));

const nowForInput = () => {
    const date = new Date();
    date.setMinutes(date.getMinutes() - date.getTimezoneOffset());
    return date.toISOString().slice(0, 16);
};

const getData = () => {
    axios.get(`/admin/egg-shipping/${router.currentRoute.value.params.id}`)
        .then((response) => {
            loadingDiv.value = false;
            retrievedData.value = response.data;
            tempInput.value = response.data.vehicle_temperature;
            if (!response.data.delivered_at) {
                dispatchForm.value.delivered_to = response.data.order?.customer_name || '';
            }
        }).catch(() => {
            loadingDiv.value = false;
            toastr.error('Registo não encontrado');
            router.push({ path: '/admin/expedicao-ovos' });
        });
};

const dispatchNow = () => {
    if (!dispatchForm.value.delivered_to.trim()) {
        toastr.error('Informe a quem foi entregue');
        return;
    }

    if (!dispatchForm.value.delivered_at) {
        toastr.error('Informe a data e hora da entrega');
        return;
    }

    loadingDispatch.value = true;

    axios.post(`/admin/egg-shipping/${retrievedData.value.id}/dispatch`, {
        delivery_note_number: dispatchForm.value.delivery_note_number || null,
        delivered_to: dispatchForm.value.delivered_to.trim(),
        delivered_at: dispatchForm.value.delivered_at,
    }).then((response) => {
        retrievedData.value = response.data;
        toastr.success('Expedição despachada. Pedido marcado como Expedido.');
    }).catch((error) => {
        toastr.error(error.response?.data?.message || 'Erro ao despachar');
    }).finally(() => {
        loadingDispatch.value = false;
    });
};

const printInvoice = () => {
    loadingInvoice.value = true;

    try {
        paperize();
    } catch {
        toastr.error('Erro ao imprimir fatura');
    } finally {
        loadingInvoice.value = false;
    }
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
    dispatchForm.value.delivered_at = nowForInput();
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
                                <p><strong>Quantidade (pedido):</strong> {{ retrievedData.order?.quantity_dozens || '-' }}</p>

                                <h6 class="mt-3">Estoque</h6>
                                <p><strong>Rastreio:</strong> {{ retrievedData.inventory?.egg?.traceability_code || '-' }}</p>
                                <p><strong>Ovos expedidos:</strong> {{ retrievedData.quantity_eggs || retrievedData.order?.quantity_dozens || '-' }}</p>
                                <p><strong>Stock restante neste lote:</strong> {{ retrievedData.inventory?.quantity ?? '-' }}</p>
                                <p><strong>Galpão:</strong> {{ retrievedData.inventory?.house?.name || '-' }}</p>
                            </div>
                        </div>

                        <hr>

                        <div v-if="isDispatched" class="mb-4">
                            <h6>Guia de Entrega</h6>
                            <span class="badge badge-success mb-2">Expedido</span>
                            <p><strong>Nº Guia:</strong> {{ retrievedData.delivery_note_number || '-' }}</p>
                            <p><strong>Entregue a:</strong> {{ retrievedData.delivered_to }}</p>
                            <p><strong>Data/Hora:</strong> {{ moment(retrievedData.delivered_at).format('DD-MM-YYYY HH:mm') }}</p>
                        </div>

                        <div v-else class="card border-warning mb-4">
                            <div class="card-body">
                                <h6 class="card-title">Despachar expedição — Guia de Entrega</h6>
                                <p class="text-muted small mb-3">Ao despachar, o pedido do cliente passará para o estado <strong>Expedido</strong>.</p>
                                <div class="row">
                                    <div class="mb-3 col-md-4">
                                        <label class="form-label" for="delivery_note_number">Nº Guia de Entrega</label>
                                        <input type="text" id="delivery_note_number" class="form-control" v-model="dispatchForm.delivery_note_number" placeholder="Ex: GE-2026-001">
                                    </div>
                                    <div class="mb-3 col-md-4">
                                        <label class="form-label" for="delivered_to">Entregue a</label>
                                        <input type="text" id="delivered_to" class="form-control" v-model="dispatchForm.delivered_to" placeholder="Nome de quem recebeu" required>
                                    </div>
                                    <div class="mb-3 col-md-4">
                                        <label class="form-label" for="delivered_at">Data e Hora</label>
                                        <input type="datetime-local" id="delivered_at" class="form-control" v-model="dispatchForm.delivered_at" required>
                                    </div>
                                </div>
                                <button class="btn btn-warning" @click.prevent="dispatchNow" :disabled="loadingDispatch">
                                    <div v-if="loadingDispatch" class="spinner-border spinner-border-sm" role="status"></div>
                                    <span v-else>Despachar e registar entrega</span>
                                </button>
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

                        <button class="btn btn-success" @click.prevent="printInvoice" :disabled="loadingInvoice">
                            <div v-if="loadingInvoice" class="spinner-border spinner-border-sm" role="status"></div>
                            <span v-else>Imprimir Fatura</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <EggShippingInvoicePrint v-if="retrievedData.id" :shipping="retrievedData" />
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
