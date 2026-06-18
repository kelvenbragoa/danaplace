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
    description: yup.string().nullable(),
    is_active: yup.boolean(),
});

const createRecordFunction = (values, actions) => {
    loading.value = true;

    const payload = {
        ...values,
        is_active: values.is_active === true || values.is_active === 'true',
    };

    axios.post('/admin/reject-reasons', payload).then(() => {
        actions.resetForm();
        router.push({ path: '/admin/motivos-refugo' });
        toastr.success('Motivo de refugo criado com sucesso');
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
        <h1 class="h3 mb-3">Motivo de Refugo</h1>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Formulário de registo de motivos de refugo.</h5>
                        <router-link to="/admin/motivos-refugo" class="btn btn-pill btn-primary mt-3">
                            <vue-feather type="arrow-left"></vue-feather>Voltar
                        </router-link>
                    </div>

                    <div class="card-body">
                        <Form @submit="createRecordFunction" :validation-schema="schema" v-slot="{ errors }" :initial-values="{ is_active: true }">
                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <label class="form-label" for="name">Nome</label>
                                    <Field type="text" class="form-control" :class="{'is-invalid': errors.name}" name="name" id="name" placeholder="Ex: Rachado, Sujo, Deformado"/>
                                    <span class="invalid-feedback">{{ errors.name }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <label class="form-label" for="description">Descrição</label>
                                    <Field as="textarea" class="form-control" :class="{'is-invalid': errors.description}" name="description" id="description" rows="3" placeholder="Descrição opcional"/>
                                    <span class="invalid-feedback">{{ errors.description }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <div class="form-check">
                                        <Field type="checkbox" class="form-check-input" name="is_active" id="is_active" :value="true"/>
                                        <label class="form-check-label" for="is_active">Ativo</label>
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
