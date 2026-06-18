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
    name: yup.string().required().max(20),
    min_weight: yup.number().required().min(0),
    max_weight: yup.number().required().min(0).test('gt-min', 'Deve ser maior que o peso mínimo', function (value) {
        return value > this.parent.min_weight;
    }),
    is_active: yup.boolean(),
});

const getData = () => {
    axios.get(`/admin/egg-categories/${router.currentRoute.value.params.id}`)
        .then((response) => {
            loadingDiv.value = false;
            retrievedData.value = response.data;
        }).catch(() => {
            loadingDiv.value = false;
            toastr.error('Registo não encontrado');
            router.push({ path: '/admin/categorias-ovos' });
        });
};

const editFunction = (values, actions) => {
    loadingButtonSubmit.value = true;

    const payload = {
        ...values,
        min_weight: Number(values.min_weight),
        max_weight: Number(values.max_weight),
        is_active: values.is_active === true || values.is_active === 'true',
    };

    axios.patch(`/admin/egg-categories/${retrievedData.value.id}`, payload).then(() => {
        router.push({ path: '/admin/categorias-ovos' });
        toastr.success('Categoria editada com sucesso');
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
        <h1 class="h3 mb-3">Categoria de Ovos</h1>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Categoria: {{ retrievedData.name }}</h5>
                        <router-link to="/admin/categorias-ovos" class="btn btn-pill btn-primary mt-3">
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
                                    <label class="form-label" for="min_weight">Peso Mínimo (g)</label>
                                    <Field type="number" step="0.01" class="form-control" :class="{'is-invalid': errors.min_weight}" name="min_weight" v-model="retrievedData.min_weight" id="min_weight" min="0"/>
                                    <span class="invalid-feedback">{{ errors.min_weight }}</span>
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="max_weight">Peso Máximo (g)</label>
                                    <Field type="number" step="0.01" class="form-control" :class="{'is-invalid': errors.max_weight}" name="max_weight" v-model="retrievedData.max_weight" id="max_weight" min="0"/>
                                    <span class="invalid-feedback">{{ errors.max_weight }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <div class="form-check">
                                        <Field type="checkbox" class="form-check-input" name="is_active" id="is_active" :value="true" v-model="retrievedData.is_active"/>
                                        <label class="form-check-label" for="is_active">Ativo</label>
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
                    <div class="spinner-border" role="status"><span class="sr-only"></span></div>
                </div>
                <br>
                <div class="d-flex justify-content-center">Carregando Dados...</div>
            </div>
        </div>
    </div>
</template>
