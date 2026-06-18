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
    name: yup.string().required(),
    tax_id: yup.string().required(),
    address: yup.string().nullable(),
    phone: yup.string().nullable(),
    email: yup.string().email('Email inválido').nullable(),
    is_active: yup.boolean(),
});

const getData = () => {
    axios.get(`/admin/farms/${router.currentRoute.value.params.id}`)
        .then((response) => {
            loadingDiv.value = false;
            retrievedData.value = response.data;
        }).catch(() => {
            loadingDiv.value = false;
        });
};

const editFunction = (values, actions) => {
    loadingButtonSubmit.value = true;

    const payload = {
        ...values,
        is_active: Boolean(values.is_active),
    };

    axios.patch(`/admin/farms/${retrievedData.value.id}`, payload).then(() => {
        router.push({ path: '/admin/granjas' });
        toastr.success('Granja editada com sucesso');
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
        <h1 class="h3 mb-3">Granja</h1>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Granja: {{ retrievedData.name }}</h5>
                        <router-link to="/admin/granjas" class="btn btn-pill btn-primary mt-3">
                            <vue-feather type="arrow-left"></vue-feather>Voltar
                        </router-link>
                    </div>

                    <div class="card-body">
                        <Form @submit="editFunction" :validation-schema="schema" v-slot="{ errors }">
                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <label class="form-label" for="name">Nome</label>
                                    <Field type="text" class="form-control" :class="{'is-invalid': errors.name}" name="name" v-model="retrievedData.name" id="name" placeholder="Nome"/>
                                    <span class="invalid-feedback">{{ errors.name }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <label class="form-label" for="tax_id">NUIT</label>
                                    <Field type="text" class="form-control" :class="{'is-invalid': errors.tax_id}" name="tax_id" v-model="retrievedData.tax_id" id="tax_id" placeholder="NUIT"/>
                                    <span class="invalid-feedback">{{ errors.tax_id }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <label class="form-label" for="address">Endereço</label>
                                    <Field type="text" class="form-control" :class="{'is-invalid': errors.address}" name="address" v-model="retrievedData.address" id="address" placeholder="Endereço"/>
                                    <span class="invalid-feedback">{{ errors.address }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <label class="form-label" for="phone">Telefone</label>
                                    <Field type="text" class="form-control" :class="{'is-invalid': errors.phone}" name="phone" v-model="retrievedData.phone" id="phone" placeholder="Telefone"/>
                                    <span class="invalid-feedback">{{ errors.phone }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <label class="form-label" for="email">Email</label>
                                    <Field type="text" class="form-control" :class="{'is-invalid': errors.email}" name="email" v-model="retrievedData.email" id="email" placeholder="Email"/>
                                    <span class="invalid-feedback">{{ errors.email }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <div class="form-check">
                                        <Field type="checkbox" class="form-check-input" name="is_active" id="is_active" v-model="retrievedData.is_active" :value="true"/>
                                        <label class="form-check-label" for="is_active">Ativa</label>
                                    </div>
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
