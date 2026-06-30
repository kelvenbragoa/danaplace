<script setup>

import axios from 'axios';
import { ref, onMounted } from 'vue';
import { useToastr } from '../../../../toastr';
import { Form, Field } from 'vee-validate';
import { useRouter } from 'vue-router';
import * as yup from 'yup';
import VueFeather from 'vue-feather';

const retrievedData = ref({});
const loadingButtonSubmit = ref(false);
const loadingDiv = ref(true);
const toastr = useToastr();
const router = useRouter();

const schema = yup.object({
    customer_name: yup.string().required(),
    customer_phone: yup.string().nullable(),
    expected_delivery_date: yup.string().nullable(),
    quantity_dozens: yup.number().required().min(1),
    unit_price: yup.number().min(0).nullable(),
    observations: yup.string().nullable(),
});

const getData = () => {
    axios.get(`/admin/egg-orders/${router.currentRoute.value.params.id}`)
        .then((response) => {
            loadingDiv.value = false;
            retrievedData.value = response.data;
        }).catch(() => {
            loadingDiv.value = false;
            toastr.error('Registo não encontrado');
            router.push({ path: '/admin/pedidos' });
        });
};

const editFunction = (values, actions) => {
    loadingButtonSubmit.value = true;

    const payload = {
        ...values,
        quantity_dozens: Number(values.quantity_dozens),
        unit_price: values.unit_price ? Number(values.unit_price) : null,
    };

    axios.patch(`/admin/egg-orders/${retrievedData.value.id}`, payload).then(() => {
        router.push({ path: '/admin/pedidos' });
        toastr.success('Pedido editado com sucesso');
    }).catch((error) => {
        toastr.error('Erro ao editar');
        if (error.response?.data?.errors) {
            actions.setErrors(error.response.data.errors);
        }
    }).finally(() => {
        loadingButtonSubmit.value = false;
    });
};

onMounted(() => {
    getData();
});

</script>

<template>
    <div v-if="!loadingDiv">
        <h1 class="h3 mb-3">Pedido</h1>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Pedido #{{ retrievedData.id }}</h5>
                        <router-link to="/admin/pedidos" class="btn btn-pill btn-primary mt-3">
                            <vue-feather type="arrow-left"></vue-feather>Voltar
                        </router-link>
                    </div>

                    <div class="card-body">
                        <p class="text-muted mb-3">
                            Cliente: <strong>{{ retrievedData.customer_name }}</strong>
                            — Categoria: <strong>{{ retrievedData.category?.name }}</strong>
                        </p>

                        <Form @submit="editFunction" :validation-schema="schema" v-slot="{ errors }">
                            <div class="row">
                                <div class="mb-3 col-md-8">
                                    <label class="form-label" for="customer_name">Nome do Cliente</label>
                                    <Field type="text" class="form-control" :class="{'is-invalid': errors.customer_name}" name="customer_name" v-model="retrievedData.customer_name" id="customer_name"/>
                                    <span class="invalid-feedback">{{ errors.customer_name }}</span>
                                </div>
                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="customer_phone">Telefone</label>
                                    <Field type="text" class="form-control" :class="{'is-invalid': errors.customer_phone}" name="customer_phone" v-model="retrievedData.customer_phone" id="customer_phone"/>
                                    <span class="invalid-feedback">{{ errors.customer_phone }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="expected_delivery_date">Entrega Prevista</label>
                                    <Field type="date" class="form-control" :class="{'is-invalid': errors.expected_delivery_date}" name="expected_delivery_date" v-model="retrievedData.expected_delivery_date" id="expected_delivery_date"/>
                                    <span class="invalid-feedback">{{ errors.expected_delivery_date }}</span>
                                </div>
                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="quantity_dozens">Quantidade</label>
                                    <Field type="number" class="form-control" :class="{'is-invalid': errors.quantity_dozens}" name="quantity_dozens" v-model="retrievedData.quantity_dozens" id="quantity_dozens" min="1"/>
                                    <span class="invalid-feedback">{{ errors.quantity_dozens }}</span>
                                </div>
                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="unit_price">Preço Unitário</label>
                                    <Field type="number" step="0.01" class="form-control" :class="{'is-invalid': errors.unit_price}" name="unit_price" v-model="retrievedData.unit_price" id="unit_price" min="0"/>
                                    <span class="invalid-feedback">{{ errors.unit_price }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <label class="form-label" for="observations">Observações</label>
                                    <Field as="textarea" class="form-control" :class="{'is-invalid': errors.observations}" name="observations" v-model="retrievedData.observations" id="observations" rows="3"/>
                                    <span class="invalid-feedback">{{ errors.observations }}</span>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary" :disabled="loadingButtonSubmit">
                                <div v-if="loadingButtonSubmit" class="spinner-border spinner-border-sm" role="status"></div>
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
