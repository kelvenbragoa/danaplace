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
    supplier: yup.string().required(),
    production_days: yup.number().required().min(0),
    is_active: yup.boolean(),
});

const createRecordFunction = (values, actions) => {
    loading.value = true;

    const payload = {
        ...values,
        production_days: Number(values.production_days),
        is_active: Boolean(values.is_active),
    };

    axios.post('/admin/lineages', payload).then(() => {
        actions.resetForm();
        router.push({ path: '/admin/linhagens' });
        toastr.success('Linhagem criada com sucesso');
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
        <h1 class="h3 mb-3">Linhagem</h1>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Formulário de criação de linhagens.</h5>
                        <router-link to="/admin/linhagens" class="btn btn-pill btn-primary mt-3">
                            <vue-feather type="arrow-left"></vue-feather>Voltar
                        </router-link>
                    </div>

                    <div class="card-body">
                        <Form @submit="createRecordFunction" :validation-schema="schema" v-slot="{ errors }" :initial-values="{ production_days: 420, is_active: true }">
                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <label class="form-label" for="name">Nome</label>
                                    <Field type="text" class="form-control" :class="{'is-invalid': errors.name}" name="name" id="name" placeholder="Nome da linhagem"/>
                                    <span class="invalid-feedback">{{ errors.name }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <label class="form-label" for="supplier">Fornecedor</label>
                                    <Field type="text" class="form-control" :class="{'is-invalid': errors.supplier}" name="supplier" id="supplier" placeholder="Fornecedor"/>
                                    <span class="invalid-feedback">{{ errors.supplier }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <label class="form-label" for="production_days">Dias de Produção</label>
                                    <Field type="number" class="form-control" :class="{'is-invalid': errors.production_days}" name="production_days" id="production_days" min="0"/>
                                    <span class="invalid-feedback">{{ errors.production_days }}</span>
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
