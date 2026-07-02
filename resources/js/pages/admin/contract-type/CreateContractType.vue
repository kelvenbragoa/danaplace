<script setup>

import { ref } from 'vue';
import axios from 'axios';
import { useToastr } from '../../../toastr';
import { Form, Field, FieldArray } from 'vee-validate';
import * as yup from 'yup';
import { useRouter } from 'vue-router';
import VueFeather from 'vue-feather';

const loading = ref(false);
const toastr = useToastr();
const loadingDiv = ref(false);
const router = useRouter();

const fieldTypes = [
    { value: 'text', label: 'Texto' },
    { value: 'number', label: 'Número' },
    { value: 'date', label: 'Data' },
    { value: 'textarea', label: 'Texto longo' },
];

const schema = yup.object({
    name: yup.string().required('Nome é obrigatório'),
    extra_fields: yup.array().of(
        yup.object().shape({
            key: yup.string().required('Chave é obrigatória'),
            label: yup.string().required('Rótulo é obrigatório'),
            type: yup.string().required('Tipo é obrigatório'),
            required: yup.boolean(),
        })
    ),
});

const createRecordFunction = (values, actions) => {
    loading.value = true;

    axios.post('/contract-types', values).then(() => {
        actions.resetForm();
        router.push({ path: '/admin/contract-types' });
        toastr.success('Tipo de contrato criado com sucesso');
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
    <div v-if="!loadingDiv">
        <h1 class="h3 mb-3">Tipo de Contrato</h1>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Formulário de criação de tipos de contrato.</h5>
                        <router-link to="/admin/contract-types" class="btn btn-pill btn-primary mt-3">
                            <vue-feather type="arrow-left"></vue-feather>Voltar
                        </router-link>
                    </div>

                    <div class="card-body">
                        <Form @submit="createRecordFunction" :validation-schema="schema" v-slot="{ errors }" :initial-values="{ extra_fields: [] }">
                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <label class="form-label" for="name">Nome</label>
                                    <Field type="text" class="form-control" :class="{'is-invalid': errors.name}" name="name" id="name" placeholder="Ex: Tempo Determinado"/>
                                    <span class="invalid-feedback">{{ errors.name }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12 mb-3">
                                    <h6>Campos extras para este tipo de contrato</h6>
                                    <p class="text-muted small">Defina os campos adicionais que aparecerão ao cadastrar técnicos com este tipo de contrato.</p>

                                    <FieldArray name="extra_fields" v-slot="{ fields, push, remove }">
                                        <div v-for="(field, idx) in fields" :key="field.key" class="card mb-3">
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="mb-3 col-md-3">
                                                        <label class="form-label">Chave</label>
                                                        <Field type="text" class="form-control" :name="`extra_fields[${idx}].key`" placeholder="ex: data_termino"/>
                                                    </div>
                                                    <div class="mb-3 col-md-3">
                                                        <label class="form-label">Rótulo</label>
                                                        <Field type="text" class="form-control" :name="`extra_fields[${idx}].label`" placeholder="ex: Data de Término"/>
                                                    </div>
                                                    <div class="mb-3 col-md-3">
                                                        <label class="form-label">Tipo</label>
                                                        <Field as="select" class="form-control" :name="`extra_fields[${idx}].type`">
                                                            <option v-for="type in fieldTypes" :key="type.value" :value="type.value">{{ type.label }}</option>
                                                        </Field>
                                                    </div>
                                                    <div class="mb-3 col-md-2">
                                                        <label class="form-label">Obrigatório</label>
                                                        <div class="form-check mt-2">
                                                            <Field type="checkbox" class="form-check-input" :name="`extra_fields[${idx}].required`" :value="true"/>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3 col-md-1 d-flex align-items-end">
                                                        <button type="button" class="btn btn-outline-danger" @click="remove(idx)">
                                                            <vue-feather type="trash" size="16"></vue-feather>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <button type="button" class="btn btn-outline-primary" @click="push({ key: '', label: '', type: 'text', required: false })">
                                            <vue-feather type="plus" size="16"></vue-feather> Adicionar campo
                                        </button>
                                    </FieldArray>
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
