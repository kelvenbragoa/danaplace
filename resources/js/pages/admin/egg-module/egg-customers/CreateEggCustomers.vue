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
    name: yup.string().required().max(100),
    tax_id: yup.string().nullable().max(18),
    email: yup.string().email().nullable(),
    phone: yup.string().nullable().max(20),
    address: yup.string().nullable().max(255),
    is_active: yup.boolean(),
});

const createRecordFunction = (values, actions) => {
    loading.value = true;

    const payload = {
        ...values,
        is_active: values.is_active === true || values.is_active === 'true',
    };

    axios.post('/admin/egg-customers', payload).then((response) => {
        actions.resetForm();
        toastr.success(`Cliente criado. Código portal: ${response.data.portal_code}`);
        router.push({ path: '/admin/clientes-ovos' });
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
        <h1 class="h3 mb-3">Cliente de Ovos</h1>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Formulário de registo de clientes.</h5>
                        <p class="text-muted mb-0">Ao guardar, será gerado automaticamente um código de acesso ao portal de pedidos.</p>
                        <router-link to="/admin/clientes-ovos" class="btn btn-pill btn-primary mt-3">
                            <vue-feather type="arrow-left"></vue-feather>Voltar
                        </router-link>
                    </div>

                    <div class="card-body">
                        <Form @submit="createRecordFunction" :validation-schema="schema" v-slot="{ errors }" :initial-values="{ is_active: true }">
                            <div class="row">
                                <div class="mb-3 col-md-8">
                                    <label class="form-label" for="name">Nome</label>
                                    <Field type="text" class="form-control" :class="{'is-invalid': errors.name}" name="name" id="name"/>
                                    <span class="invalid-feedback">{{ errors.name }}</span>
                                </div>
                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="tax_id">NUIT</label>
                                    <Field type="text" class="form-control" :class="{'is-invalid': errors.tax_id}" name="tax_id" id="tax_id"/>
                                    <span class="invalid-feedback">{{ errors.tax_id }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="email">Email</label>
                                    <Field type="email" class="form-control" :class="{'is-invalid': errors.email}" name="email" id="email"/>
                                    <span class="invalid-feedback">{{ errors.email }}</span>
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="phone">Telefone</label>
                                    <Field type="text" class="form-control" :class="{'is-invalid': errors.phone}" name="phone" id="phone"/>
                                    <span class="invalid-feedback">{{ errors.phone }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <label class="form-label" for="address">Morada</label>
                                    <Field type="text" class="form-control" :class="{'is-invalid': errors.address}" name="address" id="address"/>
                                    <span class="invalid-feedback">{{ errors.address }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <div class="form-check">
                                        <Field type="checkbox" class="form-check-input" name="is_active" id="is_active" :value="true"/>
                                        <label class="form-check-label" for="is_active">Ativo (pode aceder ao portal)</label>
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
