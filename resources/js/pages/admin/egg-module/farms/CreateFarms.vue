<script setup>

import { ref, onMounted } from 'vue';
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
    tax_id: yup.string().required(),
    address: yup.string().nullable(),
    phone: yup.string().nullable(),
    email: yup.string().email('Email inválido').nullable(),
    is_active: yup.boolean(),
});

const createRecordFunction = (values, actions) => {
    loading.value = true;

    const payload = {
        ...values,
        is_active: Boolean(values.is_active),
    };

    axios.post('/admin/farms', payload).then(() => {
        actions.resetForm();
        router.push({ path: '/admin/granjas' });
        toastr.success('Granja criada com sucesso');
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
        <h1 class="h3 mb-3">Granja</h1>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Formulário de criação de granjas.</h5>
                        <router-link to="/admin/granjas" class="btn btn-pill btn-primary mt-3">
                            <vue-feather type="arrow-left"></vue-feather>Voltar
                        </router-link>
                    </div>

                    <div class="card-body">
                        <Form @submit="createRecordFunction" :validation-schema="schema" v-slot="{ errors }" :initial-values="{ is_active: true }">
                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <label class="form-label" for="name">Nome</label>
                                    <Field type="text" class="form-control" :class="{'is-invalid': errors.name}" name="name" id="name" placeholder="Nome da granja"/>
                                    <span class="invalid-feedback">{{ errors.name }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <label class="form-label" for="tax_id">NUIT</label>
                                    <Field type="text" class="form-control" :class="{'is-invalid': errors.tax_id}" name="tax_id" id="tax_id" placeholder="NUIT"/>
                                    <span class="invalid-feedback">{{ errors.tax_id }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <label class="form-label" for="address">Endereço</label>
                                    <Field type="text" class="form-control" :class="{'is-invalid': errors.address}" name="address" id="address" placeholder="Endereço"/>
                                    <span class="invalid-feedback">{{ errors.address }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <label class="form-label" for="phone">Telefone</label>
                                    <Field type="text" class="form-control" :class="{'is-invalid': errors.phone}" name="phone" id="phone" placeholder="Telefone"/>
                                    <span class="invalid-feedback">{{ errors.phone }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <label class="form-label" for="email">Email</label>
                                    <Field type="text" class="form-control" :class="{'is-invalid': errors.email}" name="email" id="email" placeholder="Email"/>
                                    <span class="invalid-feedback">{{ errors.email }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <div class="form-check">
                                        <Field type="checkbox" class="form-check-input" name="is_active" id="is_active" :value="true"/>
                                        <label class="form-check-label" for="is_active">Ativa</label>
                                    </div>
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
