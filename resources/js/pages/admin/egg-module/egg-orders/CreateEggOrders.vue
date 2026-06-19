<script setup>

import { ref, onMounted } from 'vue';
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
const categories = ref([]);
const customers = ref([]);
const useManualCustomer = ref(false);
const selectedCustomerId = ref('');

const today = new Date().toISOString().split('T')[0];

const schema = yup.object({
    customer_id: yup.string().nullable(),
    customer_name: yup.string().nullable().max(100),
    customer_tax_id: yup.string().nullable(),
    customer_email: yup.string().email().nullable(),
    customer_phone: yup.string().nullable(),
    order_date: yup.string().required(),
    expected_delivery_date: yup.string().nullable(),
    category_id: yup.string().required('Selecione a categoria'),
    quantity_dozens: yup.number().required().min(1),
    unit_price: yup.number().min(0).nullable(),
    observations: yup.string().nullable(),
});

const getAuxiliarData = () => {
    Promise.all([
        axios.get('/admin/egg-categories-all'),
        axios.get('/admin/egg-customers-all'),
    ]).then(([categoriesResponse, customersResponse]) => {
        categories.value = categoriesResponse.data;
        customers.value = customersResponse.data;
        loadingDiv.value = false;
    }).catch(() => {
        toastr.error('Erro ao carregar dados');
        router.push({ path: '/admin/pedidos' });
    });
};

const onCustomerChange = (customerId, setFieldValue) => {
    selectedCustomerId.value = customerId;
    if (!customerId) {
        return;
    }
    const customer = customers.value.find((item) => String(item.id) === String(customerId));
    if (customer) {
        setFieldValue('customer_name', customer.name);
        setFieldValue('customer_tax_id', customer.tax_id || '');
        setFieldValue('customer_email', customer.email || '');
        setFieldValue('customer_phone', customer.phone || '');
    }
};

const createRecordFunction = (values, actions) => {
    if (!useManualCustomer.value && !values.customer_id) {
        actions.setErrors({ customer_id: 'Selecione um cliente' });
        return;
    }
    if (useManualCustomer.value && !values.customer_name) {
        actions.setErrors({ customer_name: 'Nome do cliente é obrigatório' });
        return;
    }

    loading.value = true;

    const payload = {
        ...values,
        customer_id: useManualCustomer.value ? null : (values.customer_id || null),
        quantity_dozens: Number(values.quantity_dozens),
        unit_price: values.unit_price ? Number(values.unit_price) : null,
    };

    if (useManualCustomer.value) {
        delete payload.customer_id;
    }

    axios.post('/admin/egg-orders', payload).then(() => {
        actions.resetForm();
        router.push({ path: '/admin/pedidos' });
        toastr.success('Pedido criado com sucesso');
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
        <h1 class="h3 mb-3">Pedido</h1>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Formulário de registo de pedidos.</h5>
                        <router-link to="/admin/pedidos" class="btn btn-pill btn-primary mt-3">
                            <vue-feather type="arrow-left"></vue-feather>Voltar
                        </router-link>
                    </div>

                    <div class="card-body">
                        <Form @submit="createRecordFunction" :validation-schema="schema" v-slot="{ errors, setFieldValue }" :initial-values="{ order_date: today, quantity_dozens: 1, customer_id: '' }">
                            <div class="mb-3">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" id="mode_select" :value="false" v-model="useManualCustomer">
                                    <label class="form-check-label" for="mode_select">Cliente registado</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" id="mode_manual" :value="true" v-model="useManualCustomer">
                                    <label class="form-check-label" for="mode_manual">Preencher manualmente</label>
                                </div>
                            </div>

                            <div v-if="!useManualCustomer" class="row">
                                <div class="mb-3 col-md-12">
                                    <label class="form-label" for="customer_id">Cliente</label>
                                    <Field as="select" class="form-control" :class="{'is-invalid': errors.customer_id}" name="customer_id" id="customer_id" @change="onCustomerChange($event.target.value, setFieldValue)">
                                        <option value="">Selecione um cliente...</option>
                                        <option v-for="customer in customers" :key="customer.id" :value="customer.id">
                                            {{ customer.name }} — {{ customer.portal_code }}
                                        </option>
                                    </Field>
                                    <span class="invalid-feedback">{{ errors.customer_name }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-8">
                                    <label class="form-label" for="customer_name">Nome do Cliente</label>
                                    <Field type="text" class="form-control" :class="{'is-invalid': errors.customer_name}" name="customer_name" id="customer_name" :disabled="!useManualCustomer && !!selectedCustomerId"/>
                                    <span class="invalid-feedback">{{ errors.customer_name }}</span>
                                </div>
                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="customer_tax_id">NUIT</label>
                                    <Field type="text" class="form-control" :class="{'is-invalid': errors.customer_tax_id}" name="customer_tax_id" id="customer_tax_id" :disabled="!useManualCustomer && !!selectedCustomerId"/>
                                    <span class="invalid-feedback">{{ errors.customer_tax_id }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="customer_email">Email</label>
                                    <Field type="email" class="form-control" :class="{'is-invalid': errors.customer_email}" name="customer_email" id="customer_email" :disabled="!useManualCustomer && !!selectedCustomerId"/>
                                    <span class="invalid-feedback">{{ errors.customer_email }}</span>
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="customer_phone">Telefone</label>
                                    <Field type="text" class="form-control" :class="{'is-invalid': errors.customer_phone}" name="customer_phone" id="customer_phone" :disabled="!useManualCustomer && !!selectedCustomerId"/>
                                    <span class="invalid-feedback">{{ errors.customer_phone }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="order_date">Data do Pedido</label>
                                    <Field type="date" class="form-control" :class="{'is-invalid': errors.order_date}" name="order_date" id="order_date"/>
                                    <span class="invalid-feedback">{{ errors.order_date }}</span>
                                </div>
                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="expected_delivery_date">Entrega Prevista</label>
                                    <Field type="date" class="form-control" :class="{'is-invalid': errors.expected_delivery_date}" name="expected_delivery_date" id="expected_delivery_date"/>
                                    <span class="invalid-feedback">{{ errors.expected_delivery_date }}</span>
                                </div>
                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="category_id">Categoria</label>
                                    <Field as="select" class="form-control" :class="{'is-invalid': errors.category_id}" name="category_id" id="category_id">
                                        <option value="">Selecione...</option>
                                        <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
                                    </Field>
                                    <span class="invalid-feedback">{{ errors.category_id }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="quantity_dozens">Quantidade</label>
                                    <Field type="number" class="form-control" :class="{'is-invalid': errors.quantity_dozens}" name="quantity_dozens" id="quantity_dozens" min="1"/>
                                    <span class="invalid-feedback">{{ errors.quantity_dozens }}</span>
                                </div>
                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="unit_price">Preço Unitário</label>
                                    <Field type="number" step="0.01" class="form-control" :class="{'is-invalid': errors.unit_price}" name="unit_price" id="unit_price" min="0"/>
                                    <span class="invalid-feedback">{{ errors.unit_price }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <label class="form-label" for="observations">Observações</label>
                                    <Field as="textarea" class="form-control" :class="{'is-invalid': errors.observations}" name="observations" id="observations" rows="3"/>
                                    <span class="invalid-feedback">{{ errors.observations }}</span>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary" :disabled="loading">
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
