<script setup>

import axios from 'axios';
import { ref, onMounted } from 'vue';
import { useToastr } from '../../../../toastr';
import { Form, Field } from 'vee-validate';
import { useRouter } from 'vue-router';
import * as yup from 'yup';
import VueFeather from 'vue-feather';

const retrievedData = ref({});
const farms = ref([]);
const loadingButtonSubmit = ref(false);
const loadingDiv = ref(true);
const toastr = useToastr();
const router = useRouter();

const schema = yup.object({
    farm_id: yup.string().required('Selecione a granja'),
    name: yup.string().required(),
    code: yup.string().required(),
    bird_capacity: yup.number().required().min(0, 'Capacidade mínima é 0'),
    boxes: yup.number().min(0, 'Mínimo é 0').nullable(),
    has_automation: yup.boolean(),
    is_active: yup.boolean(),
});

const getData = () => {
    Promise.all([
        axios.get(`/admin/houses/${router.currentRoute.value.params.id}`),
        axios.get('/admin/farms-all'),
    ]).then(([houseResponse, farmsResponse]) => {
        loadingDiv.value = false;
        retrievedData.value = houseResponse.data;
        farms.value = farmsResponse.data;
    }).catch(() => {
        loadingDiv.value = false;
    });
};

const editFunction = (values, actions) => {
    loadingButtonSubmit.value = true;

    const payload = {
        ...values,
        bird_capacity: Number(values.bird_capacity),
        boxes: Number(values.boxes || 0),
        has_automation: Boolean(values.has_automation),
        is_active: Boolean(values.is_active),
    };

    axios.patch(`/admin/houses/${retrievedData.value.id}`, payload).then(() => {
        router.push({ path: '/admin/galpoes' });
        toastr.success('Galpão editado com sucesso');
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
        <h1 class="h3 mb-3">Galpão</h1>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Galpão: {{ retrievedData.name }}</h5>
                        <router-link to="/admin/galpoes" class="btn btn-pill btn-primary mt-3">
                            <vue-feather type="arrow-left"></vue-feather>Voltar
                        </router-link>
                    </div>

                    <div class="card-body">
                        <Form @submit="editFunction" :validation-schema="schema" v-slot="{ errors }">
                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <label class="form-label" for="farm_id">Granja</label>
                                    <Field as="select" class="form-control" :class="{'is-invalid': errors.farm_id}" name="farm_id" id="farm_id" v-model="retrievedData.farm_id">
                                        <option value="" disabled>Selecionar granja</option>
                                        <option v-for="farm in farms" :key="farm.id" :value="farm.id">{{ farm.name }}</option>
                                    </Field>
                                    <span class="invalid-feedback">{{ errors.farm_id }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <label class="form-label" for="name">Nome</label>
                                    <Field type="text" class="form-control" :class="{'is-invalid': errors.name}" name="name" v-model="retrievedData.name" id="name" placeholder="Nome"/>
                                    <span class="invalid-feedback">{{ errors.name }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <label class="form-label" for="code">Código</label>
                                    <Field type="text" class="form-control" :class="{'is-invalid': errors.code}" name="code" v-model="retrievedData.code" id="code" placeholder="Código"/>
                                    <span class="invalid-feedback">{{ errors.code }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="bird_capacity">Capacidade de Aves</label>
                                    <Field type="number" class="form-control" :class="{'is-invalid': errors.bird_capacity}" name="bird_capacity" v-model="retrievedData.bird_capacity" id="bird_capacity" min="0"/>
                                    <span class="invalid-feedback">{{ errors.bird_capacity }}</span>
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="boxes">Caixas</label>
                                    <Field type="number" class="form-control" :class="{'is-invalid': errors.boxes}" name="boxes" v-model="retrievedData.boxes" id="boxes" min="0"/>
                                    <span class="invalid-feedback">{{ errors.boxes }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <div class="form-check">
                                        <Field type="checkbox" class="form-check-input" name="has_automation" id="has_automation" v-model="retrievedData.has_automation" :value="true"/>
                                        <label class="form-check-label" for="has_automation">Com automação</label>
                                    </div>
                                </div>
                                <div class="mb-3 col-md-6">
                                    <div class="form-check">
                                        <Field type="checkbox" class="form-check-input" name="is_active" id="is_active" v-model="retrievedData.is_active" :value="true"/>
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
