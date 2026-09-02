<script setup>

import { ref, onMounted, computed, watch } from 'vue';
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
const selectedOrderId = ref('');
const selectedOrder = ref(null);
const generatedInvoiceNumber = ref('');

const today = new Date().toISOString().split('T')[0];

const schema = yup.object({
    order_id: yup.string().required('Selecione o pedido'),
    shipping_date: yup.string().required(),
    invoice_number: yup.string().required(),
    carrier: yup.string().required(),
    vehicle_plate: yup.string().required(),
    driver_name: yup.string().required(),
    vehicle_temperature: yup.number().nullable(),
    seal_number: yup.string().nullable(),
    health_certificate: yup.string().nullable(),
});

const shippableOrders = computed(() =>
    orders.value.filter(order => order.status === 'picked' && !order.shipping)
);

const reservedItems = computed(() => selectedOrder.value?.items || []);

const reservedTotal = computed(() =>
    reservedItems.value.reduce((sum, item) => sum + Number(item.quantity || 0), 0)
);

const canSubmit = computed(() => {
    if (!selectedOrderId.value || !selectedOrder.value) return false;
    if (reservedItems.value.length === 0) return false;
    return reservedTotal.value === Number(selectedOrder.value.quantity_dozens || 0);
});

const getAuxiliarData = () => {
    Promise.all([
        axios.get('/admin/egg-orders/pending-orders'),
        axios.get('/admin/egg-shipping/next-invoice-number'),
    ]).then(([ordersResponse, invoiceResponse]) => {
        orders.value = ordersResponse.data;
        generatedInvoiceNumber.value = invoiceResponse.data.invoice_number || '';
        loadingDiv.value = false;
    }).catch(() => {
        toastr.error('Erro ao carregar dados auxiliares');
        router.push({ path: '/admin/expedicao-ovos' });
    });
};

const orderLabel = (order) => {
    const category = order.category?.name ? ` — ${order.category.name}` : '';
    return `#${order.id} — ${order.customer_name}${category} (${order.quantity_dozens} ovos)`;
};

const loadSelectedOrder = (orderId) => {
    if (!orderId) {
        selectedOrder.value = null;
        return;
    }

    axios.get(`/admin/egg-orders/${orderId}`)
        .then((response) => {
            selectedOrder.value = response.data;
        })
        .catch(() => {
            selectedOrder.value = null;
            toastr.error('Erro ao carregar stock reservado do pedido');
        });
};

watch(selectedOrderId, (id) => {
    loadSelectedOrder(id);
});

const createRecordFunction = (values, actions) => {
    if (!canSubmit.value) {
        toastr.error('O pedido deve estar separado com stock reservado antes de expedir.');
        return;
    }

    loading.value = true;

    const payload = {
        ...values,
        invoice_number: generatedInvoiceNumber.value || values.invoice_number,
        vehicle_temperature: values.vehicle_temperature ? Number(values.vehicle_temperature) : null,
    };

    axios.post('/admin/egg-shipping', payload).then(() => {
        actions.resetForm();
        router.push({ path: '/admin/expedicao-ovos' });
        toastr.success('Expedição registada com sucesso.');
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
                        <h5 class="card-title">Registo de expedição (logística)</h5>
                        <router-link to="/admin/expedicao-ovos" class="btn btn-pill btn-primary mt-3">
                            <vue-feather type="arrow-left"></vue-feather>Voltar
                        </router-link>
                    </div>

                    <div class="card-body">
                        <div v-if="shippableOrders.length === 0" class="alert alert-warning">
                            Não existem pedidos separados prontos para expedição.
                            <router-link to="/admin/separacao-ovos">Ir para Separação de Ovos</router-link>
                        </div>

                        <Form @submit="createRecordFunction" :validation-schema="schema" v-slot="{ errors }" :initial-values="{ shipping_date: today, invoice_number: generatedInvoiceNumber }">
                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <label class="form-label" for="order_id">Pedido (separado)</label>
                                    <Field as="select" class="form-control" :class="{'is-invalid': errors.order_id}" name="order_id" id="order_id" v-model="selectedOrderId">
                                        <option value="">Selecione...</option>
                                        <option v-for="order in shippableOrders" :key="order.id" :value="order.id">{{ orderLabel(order) }}</option>
                                    </Field>
                                    <span class="invalid-feedback">{{ errors.order_id }}</span>
                                </div>
                            </div>

                            <div v-if="selectedOrder" class="mb-4">
                                <h6 class="mb-2">Stock reservado na separação</h6>
                                <div class="table-responsive" v-if="reservedItems.length">
                                    <table class="table table-sm table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Stock</th>
                                                <th>Categoria</th>
                                                <th class="text-end">Quantidade</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="item in reservedItems" :key="item.id">
                                                <td>#{{ item.inventory_id }} — {{ item.inventory?.egg?.traceability_code || '—' }}</td>
                                                <td>{{ item.inventory?.egg?.category?.name || '—' }}</td>
                                                <td class="text-end">{{ item.quantity }}</td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="2" class="text-end">Total reservado</th>
                                                <th class="text-end">{{ reservedTotal }} / {{ selectedOrder.quantity_dozens }}</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                <p v-else class="text-danger mb-0">Este pedido não tem stock reservado. Separe-o primeiro.</p>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="shipping_date">Data de Expedição</label>
                                    <Field type="date" class="form-control" :class="{'is-invalid': errors.shipping_date}" name="shipping_date" id="shipping_date"/>
                                    <span class="invalid-feedback">{{ errors.shipping_date }}</span>
                                </div>
                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="invoice_number">Nº Fatura</label>
                                    <Field type="text" class="form-control bg-light" :class="{'is-invalid': errors.invoice_number}" name="invoice_number" id="invoice_number" v-model="generatedInvoiceNumber" readonly/>
                                    <small class="text-muted">Gerado automaticamente</small>
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

                            <button type="submit" class="btn btn-primary" :disabled="loading || !canSubmit">
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
            <div class="card-body text-center py-5">
                <div class="spinner-border" role="status"></div>
                <div class="mt-2">Carregando Dados...</div>
            </div>
        </div>
    </div>
</template>
