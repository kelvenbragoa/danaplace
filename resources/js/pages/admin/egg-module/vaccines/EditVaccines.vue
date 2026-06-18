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
    manufacturer: yup.string().required(),
    batch: yup.string().required(),
    expiry_date: yup.string().required(),
    min_stock: yup.number().min(0).nullable(),
});

const getData = () => {
    axios.get(`/admin/vaccines/${router.currentRoute.value.params.id}`)
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
        min_stock: Number(values.min_stock || 0),
    };

    axios.patch(`/admin/vaccines/${retrievedData.value.id}`, payload).then(() => {
        router.push({ path: '/admin/vacinas' });
        toastr.success('Vacina editada com sucesso');
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
        <h1 class="h3 mb-3">Vacina</h1>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Vacina: {{ retrievedData.name }}</h5>
                        <router-link to="/admin/vacinas" class="btn btn-pill btn-primary mt-3">
                            <vue-feather type="arrow-left"></vue-feather>Voltar
                        </router-link>
                    </div>

                    <div class="card-body">
                        <Form @submit="editFunction" :validation-schema="schema" v-slot="{ errors }">
                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <label class="form-label" for="name">Nome</label>
                                    <Field type="text" class="form-control" :class="{'is-invalid': errors.name}" name="name" v-model="retrievedData.name" id="name"/>
                                    <span class="invalid-feedback">{{ errors.name }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="manufacturer">Fabricante</label>
                                    <Field type="text" class="form-control" :class="{'is-invalid': errors.manufacturer}" name="manufacturer" v-model="retrievedData.manufacturer" id="manufacturer"/>
                                    <span class="invalid-feedback">{{ errors.manufacturer }}</span>
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="batch">Lote</label>
                                    <Field type="text" class="form-control" :class="{'is-invalid': errors.batch}" name="batch" v-model="retrievedData.batch" id="batch"/>
                                    <span class="invalid-feedback">{{ errors.batch }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="expiry_date">Data de Validade</label>
                                    <Field type="date" class="form-control" :class="{'is-invalid': errors.expiry_date}" name="expiry_date" v-model="retrievedData.expiry_date" id="expiry_date"/>
                                    <span class="invalid-feedback">{{ errors.expiry_date }}</span>
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="min_stock">Stock Mínimo</label>
                                    <Field type="number" class="form-control" :class="{'is-invalid': errors.min_stock}" name="min_stock" v-model="retrievedData.min_stock" id="min_stock" min="0"/>
                                    <span class="invalid-feedback">{{ errors.min_stock }}</span>
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
