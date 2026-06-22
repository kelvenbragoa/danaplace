<script setup>

import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { useToastr } from '../../../../toastr';
import { Form, Field } from 'vee-validate';
import * as yup from 'yup';
import { useRouter } from 'vue-router';
import VueFeather from 'vue-feather';

const loading = ref(false);
const loadingDiv = ref(true);
const toastr = useToastr();
const router = useRouter();
const orders = ref([]);
const inventory = ref([]);

const today = new Date().toISOString().split('T')[0];

const schema = yup.object({
    order_id: yup.string().required('Selecione o pedido'),
    inventory_id: yup.string().required('Selecione o estoque'),
    shipping_date: yup.string().required(),
    invoice_number: yup.string().required(),
    carrier: yup.string().required(),
    vehicle_plate: yup.string().required(),
    driver_name: yup.string().required(),
    vehicle_temperature: yup.number().nullable(),
    seal_number: yup.string().nullable(),
    health_certificate: yup.string().nullable(),
});

const shippableOrders = computed(() => orders.value.filter(order => order.status === 'picked'));

const getAuxiliarData = () => {
    Promise.all([
        axios.get('/admin/egg-orders/pending-orders'),
        axios.get('/admin/egg-inventory/fifo-list'),
    ]).then(([ordersResponse, inventoryResponse]) => {
        orders.value = ordersResponse.data;
        inventory.value = inventoryResponse.data;
        loadingDiv.value = false;
    }).catch(() => {
        toastr.error('Erro ao carregar dados auxiliares');
        router.push({ path: '/admin/expedicao-ovos' });
    });
};

const orderLabel = (order) => {
    const category = order.category?.name ? ` — ${order.category.name}` : '';
    return `#${order.id} — ${order.customer_name}${category} (${order.quantity_dozens} dz)`;
};

const inventoryLabel = (item) => {
    const trace = item.egg?.traceability_code || 'Sem rastreio';
    const category = item.egg?.category?.name ? ` — ${item.egg.category.name}` : '';
    return `#${item.id} — ${trace}${category} — ${item.quantity} un.`;
};

const createRecordFunction = (values, actions) => {
    loading.value = true;

    const payload = {
        ...values,
        vehicle_temperature: values.vehicle_temperature ? Number(values.vehicle_temperature) : null,
    };

    axios.post('/admin/egg-shipping', payload).then(() => {
        actions.resetForm();
        router.push({ path: '/admin/expedicao-ovos' });
        toastr.success('Expedição criada com sucesso');
    }).catch((error) => {
        toastr.error('Erro ao adicionar. ' + (error.response?.data?.message || ''));
        if (error.response?.data?.errors) {
            actions.setErrors(error.response.data.errors);
        }
    }).finally(() => {
        loading.value = false;
    });
};

onMounted(() => {
    getAuxiliarData();
});

</script>

<template>
    <div v-if="!loadingDiv">
        <h1 class="h3 mb-3">Expedição de Ovos</h1>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Formulário de registo de expedição.</h5>
                        <router-link to="/admin/expedicao-ovos" class="btn btn-pill btn-primary mt-3">
                            <vue-feather type="arrow-left"></vue-feather>Voltar
                        </router-link>
                    </div>

                    <div class="card-body">
                        <div v-if="shippableOrders.length === 0" class="alert alert-warning">
                            Não existem pedidos em estado <strong>Separado</strong>. Marque um pedido como separado antes de expedir.
                        </div>
                        <div v-if="inventory.length === 0" class="alert alert-warning">
                            Não existe estoque disponível para expedição.
                        </div>

                        <Form @submit="createRecordFunction" :validation-schema="schema" v-slot="{ errors }" :initial-values="{ shipping_date: today }">
                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="order_id">Pedido</label>
                                    <Field as="select" class="form-control" :class="{'is-invalid': errors.order_id}" name="order_id" id="order_id">
                                        <option value="">Selecione...</option>
                                        <option v-for="order in shippableOrders" :key="order.id" :value="order.id">{{ orderLabel(order) }}</option>
                                    </Field>
                                    <span class="invalid-feedback">{{ errors.order_id }}</span>
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="inventory_id">Estoque</label>
                                    <Field as="select" class="form-control" :class="{'is-invalid': errors.inventory_id}" name="inventory_id" id="inventory_id">
                                        <option value="">Selecione...</option>
                                        <option v-for="item in inventory" :key="item.id" :value="item.id">{{ inventoryLabel(item) }}</option>
                                    </Field>
                                    <span class="invalid-feedback">{{ errors.inventory_id }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="shipping_date">Data de Expedição</label>
                                    <Field type="date" class="form-control" :class="{'is-invalid': errors.shipping_date}" name="shipping_date" id="shipping_date"/>
                                    <span class="invalid-feedback">{{ errors.shipping_date }}</span>
                                </div>
                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="invoice_number">Nº Fatura</label>
                                    <Field type="text" class="form-control" :class="{'is-invalid': errors.invoice_number}" name="invoice_number" id="invoice_number" placeholder="Ex: FT-2026-001"/>
                                    <span class="invalid-feedback">{{ errors.invoice_number }}</span>
                                </div>
                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="carrier">Transportadora</label>
                                    <Field type="text" class="form-control" :class="{'is-invalid': errors.carrier}" name="carrier" id="carrier"/>
                                    <span class="invalid-feedback">{{ errors.carrier }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="vehicle_plate">Matrícula</label>
                                    <Field type="text" class="form-control" :class="{'is-invalid': errors.vehicle_plate}" name="vehicle_plate" id="vehicle_plate"/>
                                    <span class="invalid-feedback">{{ errors.vehicle_plate }}</span>
                                </div>
                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="driver_name">Motorista</label>
                                    <Field type="text" class="form-control" :class="{'is-invalid': errors.driver_name}" name="driver_name" id="driver_name"/>
                                    <span class="invalid-feedback">{{ errors.driver_name }}</span>
                                </div>
                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="vehicle_temperature">Temperatura (°C)</label>
                                    <Field type="number" step="0.1" class="form-control" :class="{'is-invalid': errors.vehicle_temperature}" name="vehicle_temperature" id="vehicle_temperature"/>
                                    <span class="invalid-feedback">{{ errors.vehicle_temperature }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="seal_number">Nº Selo</label>
                                    <Field type="text" class="form-control" :class="{'is-invalid': errors.seal_number}" name="seal_number" id="seal_number"/>
                                    <span class="invalid-feedback">{{ errors.seal_number }}</span>
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="health_certificate">Certificado Sanitário</label>
                                    <Field type="text" class="form-control" :class="{'is-invalid': errors.health_certificate}" name="health_certificate" id="health_certificate"/>
                                    <span class="invalid-feedback">{{ errors.health_certificate }}</span>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary" :disabled="loading || shippableOrders.length === 0 || inventory.length === 0">
                                <div v-if="loading" class="spinner-border spinner-border-sm" role="status"></div>
                                <span v-else>Submeter</span>
                            </button>
                        </Form>
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
