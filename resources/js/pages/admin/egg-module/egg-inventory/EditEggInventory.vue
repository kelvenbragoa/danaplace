<script setup>

import axios from 'axios';
import { ref, onMounted } from 'vue';
import { useToastr } from '../../../../toastr';
import { Form, Field } from 'vee-validate';
import { useRouter } from 'vue-router';
import * as yup from 'yup';
import VueFeather from 'vue-feather';

const retrievedData = ref({});
const houses = ref([]);
const loadingButtonSubmit = ref(false);
const loadingDiv = ref(true);
const toastr = useToastr();
const router = useRouter();

const schema = yup.object({
    house_id: yup.string().required(),
    quantity: yup.number().required().min(1),
    entry_date: yup.string().required(),
    location: yup.string().nullable(),
    status: yup.string().required(),
});

const getData = () => {
    Promise.all([
        axios.get(`/admin/egg-inventory/${router.currentRoute.value.params.id}`),
        axios.get('/admin/houses-all'),
    ]).then(([inventoryResponse, housesResponse]) => {
        loadingDiv.value = false;
        retrievedData.value = inventoryResponse.data;
        houses.value = housesResponse.data;
    }).catch(() => {
        loadingDiv.value = false;
        toastr.error('Registo não encontrado');
        router.push({ path: '/admin/estoque-ovos' });
    });
};

const editFunction = (values, actions) => {
    loadingButtonSubmit.value = true;

    const payload = {
        ...values,
        quantity: Number(values.quantity),
    };

    axios.patch(`/admin/egg-inventory/${retrievedData.value.id}`, payload).then(() => {
        router.push({ path: '/admin/estoque-ovos' });
        toastr.success('Registo editado com sucesso');
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
        <h1 class="h3 mb-3">Estoque de Ovos</h1>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Editar estoque #{{ retrievedData.id }}</h5>
                        <router-link to="/admin/estoque-ovos" class="btn btn-pill btn-primary mt-3">
                            <vue-feather type="arrow-left"></vue-feather>Voltar
                        </router-link>
                    </div>

                    <div class="card-body">
                        <p class="text-muted mb-3">
                            Ovo: <strong>{{ retrievedData.egg?.traceability_code }}</strong>
                            <span v-if="retrievedData.egg?.category"> — {{ retrievedData.egg.category.name }}</span>
                        </p>

                        <Form @submit="editFunction" :validation-schema="schema" v-slot="{ errors }">
                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="house_id">Galpão</label>
                                    <Field as="select" class="form-control" :class="{'is-invalid': errors.house_id}" name="house_id" v-model="retrievedData.house_id" id="house_id">
                                        <option value="">Selecione...</option>
                                        <option v-for="house in houses" :key="house.id" :value="house.id">
                                            {{ house.name }} ({{ house.farm?.name || 'Sem granja' }})
                                        </option>
                                    </Field>
                                    <span class="invalid-feedback">{{ errors.house_id }}</span>
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="quantity">Quantidade</label>
                                    <Field type="number" class="form-control" :class="{'is-invalid': errors.quantity}" name="quantity" v-model="retrievedData.quantity" id="quantity" min="1"/>
                                    <span class="invalid-feedback">{{ errors.quantity }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="entry_date">Data de Entrada</label>
                                    <Field type="date" class="form-control" :class="{'is-invalid': errors.entry_date}" name="entry_date" v-model="retrievedData.entry_date" id="entry_date"/>
                                    <span class="invalid-feedback">{{ errors.entry_date }}</span>
                                </div>
                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="location">Localização</label>
                                    <Field type="text" class="form-control" :class="{'is-invalid': errors.location}" name="location" v-model="retrievedData.location" id="location"/>
                                    <span class="invalid-feedback">{{ errors.location }}</span>
                                </div>
                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="status">Estado</label>
                                    <Field as="select" class="form-control" :class="{'is-invalid': errors.status}" name="status" v-model="retrievedData.status" id="status">
                                        <option value="available">Disponível</option>
                                        <option value="reserved">Reservado</option>
                                        <option value="shipped">Expedido</option>
                                    </Field>
                                    <span class="invalid-feedback">{{ errors.status }}</span>
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
