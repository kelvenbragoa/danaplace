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

const today = new Date().toISOString().split('T')[0];

const schema = yup.object({
    customer_name: yup.string().required(),
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
    axios.get('/admin/egg-categories-all')
        .then((response) => {
            categories.value = response.data;
            loadingDiv.value = false;
        })
        .catch(() => {
            toastr.error('Erro ao carregar categorias');
            router.push({ path: '/admin/pedidos' });
        });
};

const createRecordFunction = (values, actions) => {
    loading.value = true;

    const payload = {
        ...values,
        quantity_dozens: Number(values.quantity_dozens),
        unit_price: values.unit_price ? Number(values.unit_price) : null,
    };

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
                        <Form @submit="createRecordFunction" :validation-schema="schema" v-slot="{ errors }" :initial-values="{ order_date: today, quantity_dozens: 1 }">
                            <div class="row">
                                <div class="mb-3 col-md-8">
                                    <label class="form-label" for="customer_name">Nome do Cliente</label>
                                    <Field type="text" class="form-control" :class="{'is-invalid': errors.customer_name}" name="customer_name" id="customer_name"/>
                                    <span class="invalid-feedback">{{ errors.customer_name }}</span>
                                </div>
                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="customer_tax_id">NIF</label>
                                    <Field type="text" class="form-control" :class="{'is-invalid': errors.customer_tax_id}" name="customer_tax_id" id="customer_tax_id"/>
                                    <span class="invalid-feedback">{{ errors.customer_tax_id }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="customer_email">Email</label>
                                    <Field type="email" class="form-control" :class="{'is-invalid': errors.customer_email}" name="customer_email" id="customer_email"/>
                                    <span class="invalid-feedback">{{ errors.customer_email }}</span>
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="customer_phone">Telefone</label>
                                    <Field type="text" class="form-control" :class="{'is-invalid': errors.customer_phone}" name="customer_phone" id="customer_phone"/>
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
                                    <label class="form-label" for="quantity_dozens">Quantidade (dúzias)</label>
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
