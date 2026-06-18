<script setup>

import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useToastr } from '../../../../toastr';
import { Form, Field } from 'vee-validate';
import * as yup from 'yup';
import { useRouter } from 'vue-router';
import VueFeather from 'vue-feather';

const loading = ref(false);
const loadingDiv = ref(true);
const toastr = useToastr();
const router = useRouter();
const eggs = ref([]);
const houses = ref([]);

const today = new Date().toISOString().split('T')[0];

const schema = yup.object({
    egg_id: yup.string().required('Selecione o ovo'),
    house_id: yup.string().required('Selecione o galpão'),
    quantity: yup.number().required().min(1),
    entry_date: yup.string().required(),
    location: yup.string().nullable(),
});

const getAuxiliarData = () => {
    Promise.all([
        axios.get('/admin/eggs-all'),
        axios.get('/admin/houses-all'),
    ]).then(([eggsResponse, housesResponse]) => {
        eggs.value = eggsResponse.data;
        houses.value = housesResponse.data;
        loadingDiv.value = false;
    }).catch(() => {
        toastr.error('Erro ao carregar dados auxiliares');
        router.push({ path: '/admin/estoque-ovos' });
    });
};

const eggLabel = (egg) => {
    const category = egg.category?.name ? ` - ${egg.category.name}` : '';
    const flock = egg.flock?.code ? ` (${egg.flock.code})` : '';
    return `${egg.traceability_code}${category}${flock}`;
};

const createRecordFunction = (values, actions) => {
    loading.value = true;

    const payload = {
        ...values,
        quantity: Number(values.quantity),
    };

    axios.post('/admin/egg-inventory', payload).then(() => {
        actions.resetForm();
        router.push({ path: '/admin/estoque-ovos' });
        toastr.success('Registo de estoque criado com sucesso');
    }).catch((error) => {
        toastr.error('Erro ao adicionar. ' + (error.response?.data?.message || ''));
        if (error.response?.data?.errors) {
            actions.setErrors(error.response.data.errors);
        }
    }).finally(() => {
        loading.value = false;
    });
};

onMounted(() => {
    getAuxiliarData();
});

</script>

<template>
    <div v-if="!loadingDiv">
        <h1 class="h3 mb-3">Estoque de Ovos</h1>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Formulário de entrada no estoque.</h5>
                        <router-link to="/admin/estoque-ovos" class="btn btn-pill btn-primary mt-3">
                            <vue-feather type="arrow-left"></vue-feather>Voltar
                        </router-link>
                    </div>

                    <div class="card-body">
                        <div v-if="eggs.length === 0" class="alert alert-warning">
                            Não existem ovos registados. É necessário criar ovos antes de adicionar ao estoque.
                        </div>

                        <Form @submit="createRecordFunction" :validation-schema="schema" v-slot="{ errors }" :initial-values="{ entry_date: today, quantity: 1 }">
                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <label class="form-label" for="egg_id">Ovo (Rastreio)</label>
                                    <Field as="select" class="form-control" :class="{'is-invalid': errors.egg_id}" name="egg_id" id="egg_id">
                                        <option value="">Selecione...</option>
                                        <option v-for="egg in eggs" :key="egg.id" :value="egg.id">{{ eggLabel(egg) }}</option>
                                    </Field>
                                    <span class="invalid-feedback">{{ errors.egg_id }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="house_id">Galpão</label>
                                    <Field as="select" class="form-control" :class="{'is-invalid': errors.house_id}" name="house_id" id="house_id">
                                        <option value="">Selecione...</option>
                                        <option v-for="house in houses" :key="house.id" :value="house.id">
                                            {{ house.name }} ({{ house.farm?.name || 'Sem granja' }})
                                        </option>
                                    </Field>
                                    <span class="invalid-feedback">{{ errors.house_id }}</span>
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="quantity">Quantidade</label>
                                    <Field type="number" class="form-control" :class="{'is-invalid': errors.quantity}" name="quantity" id="quantity" min="1"/>
                                    <span class="invalid-feedback">{{ errors.quantity }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="entry_date">Data de Entrada</label>
                                    <Field type="date" class="form-control" :class="{'is-invalid': errors.entry_date}" name="entry_date" id="entry_date"/>
                                    <span class="invalid-feedback">{{ errors.entry_date }}</span>
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="location">Localização</label>
                                    <Field type="text" class="form-control" :class="{'is-invalid': errors.location}" name="location" id="location" placeholder="Ex: Câmara A, Prateleira 3"/>
                                    <span class="invalid-feedback">{{ errors.location }}</span>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary" :disabled="loading || eggs.length === 0">
                                <div v-if="loading" class="spinner-border spinner-border-sm" role="status"></div>
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
