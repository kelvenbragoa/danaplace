<script setup>

import { ref } from 'vue';
import axios from 'axios';
import { useToastr } from '../../../../toastr';
import { Form, Field } from 'vee-validate';
import * as yup from 'yup';
import { useRouter } from 'vue-router';
import VueFeather from 'vue-feather';

const loading = ref(false);
const toastr = useToastr();
const router = useRouter();

const schema = yup.object({
    name: yup.string().required(),
    manufacturer: yup.string().required(),
    batch: yup.string().required(),
    expiry_date: yup.string().required(),
    min_stock: yup.number().min(0).nullable(),
});

const createRecordFunction = (values, actions) => {
    loading.value = true;

    const payload = {
        ...values,
        min_stock: Number(values.min_stock || 0),
    };

    axios.post('/admin/vaccines', payload).then(() => {
        actions.resetForm();
        router.push({ path: '/admin/vacinas' });
        toastr.success('Vacina criada com sucesso');
    }).catch((error) => {
        toastr.error('Erro ao adicionar. ' + (error.response?.data?.message || ''));
        if (error.response?.data?.errors) {
            actions.setErrors(error.response.data.errors);
        }
    }).finally(() => {
        loading.value = false;
    });
};

</script>

<template>
    <div>
        <h1 class="h3 mb-3">Vacina</h1>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Formulário de registo de vacinas.</h5>
                        <router-link to="/admin/vacinas" class="btn btn-pill btn-primary mt-3">
                            <vue-feather type="arrow-left"></vue-feather>Voltar
                        </router-link>
                    </div>

                    <div class="card-body">
                        <Form @submit="createRecordFunction" :validation-schema="schema" v-slot="{ errors }" :initial-values="{ min_stock: 0 }">
                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <label class="form-label" for="name">Nome</label>
                                    <Field type="text" class="form-control" :class="{'is-invalid': errors.name}" name="name" id="name" placeholder="Nome da vacina"/>
                                    <span class="invalid-feedback">{{ errors.name }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="manufacturer">Fabricante</label>
                                    <Field type="text" class="form-control" :class="{'is-invalid': errors.manufacturer}" name="manufacturer" id="manufacturer" placeholder="Fabricante"/>
                                    <span class="invalid-feedback">{{ errors.manufacturer }}</span>
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="batch">Lote</label>
                                    <Field type="text" class="form-control" :class="{'is-invalid': errors.batch}" name="batch" id="batch" placeholder="Lote único"/>
                                    <span class="invalid-feedback">{{ errors.batch }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="expiry_date">Data de Validade</label>
                                    <Field type="date" class="form-control" :class="{'is-invalid': errors.expiry_date}" name="expiry_date" id="expiry_date"/>
                                    <span class="invalid-feedback">{{ errors.expiry_date }}</span>
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="min_stock">Stock Mínimo</label>
                                    <Field type="number" class="form-control" :class="{'is-invalid': errors.min_stock}" name="min_stock" id="min_stock" min="0"/>
                                    <span class="invalid-feedback">{{ errors.min_stock }}</span>
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
</template>
