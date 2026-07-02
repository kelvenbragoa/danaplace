<script setup>

import axios from 'axios';
import { ref, onMounted } from 'vue';
import { useToastr } from '../../../toastr';
import { Form, Field, FieldArray } from 'vee-validate';
import { useRouter } from 'vue-router';
import * as yup from 'yup';
import VueFeather from 'vue-feather';

const retrievedData = ref({ extra_fields: [] });
const loadingButtonSubmit = ref(false);
const loadingDiv = ref(true);
const toastr = useToastr();
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

const getData = () => {
    axios.get(`/contract-types/${router.currentRoute.value.params.id}/edit`)
        .then((response) => {
            loadingDiv.value = false;
            retrievedData.value = {
                ...response.data,
                extra_fields: response.data.extra_fields || [],
            };
        }).catch(() => {
            loadingDiv.value = false;
        });
};

const editFunction = (values, actions) => {
    loadingButtonSubmit.value = true;

    axios.patch(`/contract-types/${retrievedData.value.id}`, values).then(() => {
        actions.resetForm();
        router.push({ path: '/admin/contract-types' });
        toastr.success('Tipo de contrato editado com sucesso');
    }).catch((error) => {
        loadingButtonSubmit.value = false;
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
        <h1 class="h3 mb-3">Tipo de Contrato</h1>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Tipo de Contrato: {{ retrievedData.name }}</h5>
                        <router-link to="/admin/contract-types" class="btn btn-pill btn-primary mt-3">
                            <vue-feather type="arrow-left"></vue-feather>Voltar
                        </router-link>
                    </div>

                    <div class="card-body">
                        <Form @submit="editFunction" :validation-schema="schema" v-slot="{ errors }" :initial-values="retrievedData">
                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <label class="form-label" for="name">Nome</label>
                                    <Field type="text" class="form-control" :class="{'is-invalid': errors.name}" name="name" id="name" placeholder="Nome"/>
                                    <span class="invalid-feedback">{{ errors.name }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12 mb-3">
                                    <h6>Campos extras</h6>

                                    <FieldArray name="extra_fields" v-slot="{ fields, push, remove }">
                                        <div v-for="(field, idx) in fields" :key="field.key" class="card mb-3">
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="mb-3 col-md-3">
                                                        <label class="form-label">Chave</label>
                                                        <Field type="text" class="form-control" :name="`extra_fields[${idx}].key`"/>
                                                    </div>
                                                    <div class="mb-3 col-md-3">
                                                        <label class="form-label">Rótulo</label>
                                                        <Field type="text" class="form-control" :name="`extra_fields[${idx}].label`"/>
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
