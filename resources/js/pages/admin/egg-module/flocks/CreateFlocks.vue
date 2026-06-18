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
const houses = ref([]);
const lineages = ref([]);

const schema = yup.object({
    house_id: yup.string().required('Selecione o galpão'),
    lineage_id: yup.string().required('Selecione a linhagem'),
    code: yup.string().required(),
    birth_date: yup.string().required(),
    housing_date: yup.string().required(),
    initial_bird_count: yup.number().required().min(1, 'Mínimo 1 ave'),
    current_bird_count: yup.number().min(0, 'Mínimo 0').nullable(),
    expected_disposal_date: yup.string().nullable(),
    status: yup.string().oneOf(['growing', 'laying', 'disposed']).required(),
    observations: yup.string().nullable(),
});

const getAuxiliarData = () => {
    Promise.all([
        axios.get('/admin/houses-all'),
        axios.get('/admin/lineages-all'),
    ]).then(([housesResponse, lineagesResponse]) => {
        houses.value = housesResponse.data;
        lineages.value = lineagesResponse.data;
        loadingDiv.value = false;
    }).catch(() => {
        toastr.error('Erro ao carregar dados auxiliares');
        router.push({ path: '/admin/lotes' });
    });
};

const createRecordFunction = (values, actions) => {
    loading.value = true;

    const payload = {
        ...values,
        initial_bird_count: Number(values.initial_bird_count),
        current_bird_count: Number(values.current_bird_count ?? values.initial_bird_count),
    };

    axios.post('/admin/flocks', payload).then(() => {
        actions.resetForm();
        router.push({ path: '/admin/lotes' });
        toastr.success('Lote criado com sucesso');
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
        <h1 class="h3 mb-3">Lote</h1>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Formulário de criação de lotes.</h5>
                        <router-link to="/admin/lotes" class="btn btn-pill btn-primary mt-3">
                            <vue-feather type="arrow-left"></vue-feather>Voltar
                        </router-link>
                    </div>

                    <div class="card-body">
                        <Form @submit="createRecordFunction" :validation-schema="schema" v-slot="{ errors }" :initial-values="{ status: 'growing' }">
                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="house_id">Galpão</label>
                                    <Field as="select" class="form-control" :class="{'is-invalid': errors.house_id}" name="house_id" id="house_id">
                                        <option value="" disabled>Selecionar galpão</option>
                                        <option v-for="house in houses" :key="house.id" :value="house.id">
                                            {{ house.name }} ({{ house.farm?.name }})
                                        </option>
                                    </Field>
                                    <span class="invalid-feedback">{{ errors.house_id }}</span>
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="lineage_id">Linhagem</label>
                                    <Field as="select" class="form-control" :class="{'is-invalid': errors.lineage_id}" name="lineage_id" id="lineage_id">
                                        <option value="" disabled>Selecionar linhagem</option>
                                        <option v-for="lineage in lineages" :key="lineage.id" :value="lineage.id">{{ lineage.name }}</option>
                                    </Field>
                                    <span class="invalid-feedback">{{ errors.lineage_id }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <label class="form-label" for="code">Código</label>
                                    <Field type="text" class="form-control" :class="{'is-invalid': errors.code}" name="code" id="code" placeholder="Código único do lote"/>
                                    <span class="invalid-feedback">{{ errors.code }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="birth_date">Data de Nascimento</label>
                                    <Field type="date" class="form-control" :class="{'is-invalid': errors.birth_date}" name="birth_date" id="birth_date"/>
                                    <span class="invalid-feedback">{{ errors.birth_date }}</span>
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="housing_date">Data de Alojamento</label>
                                    <Field type="date" class="form-control" :class="{'is-invalid': errors.housing_date}" name="housing_date" id="housing_date"/>
                                    <span class="invalid-feedback">{{ errors.housing_date }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="initial_bird_count">Aves Iniciais</label>
                                    <Field type="number" class="form-control" :class="{'is-invalid': errors.initial_bird_count}" name="initial_bird_count" id="initial_bird_count" min="1"/>
                                    <span class="invalid-feedback">{{ errors.initial_bird_count }}</span>
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="current_bird_count">Aves Atuais</label>
                                    <Field type="number" class="form-control" :class="{'is-invalid': errors.current_bird_count}" name="current_bird_count" id="current_bird_count" min="0" placeholder="Igual às iniciais se vazio"/>
                                    <span class="invalid-feedback">{{ errors.current_bird_count }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="expected_disposal_date">Data Prevista de Descarte</label>
                                    <Field type="date" class="form-control" :class="{'is-invalid': errors.expected_disposal_date}" name="expected_disposal_date" id="expected_disposal_date"/>
                                    <span class="invalid-feedback">{{ errors.expected_disposal_date }}</span>
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="status">Estado</label>
                                    <Field as="select" class="form-control" :class="{'is-invalid': errors.status}" name="status" id="status">
                                        <option value="growing">Recria</option>
                                        <option value="laying">Postura</option>
                                        <option value="disposed">Descartado</option>
                                    </Field>
                                    <span class="invalid-feedback">{{ errors.status }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <label class="form-label" for="observations">Observações</label>
                                    <Field as="textarea" class="form-control" :class="{'is-invalid': errors.observations}" name="observations" id="observations" rows="3" placeholder="Observações"/>
                                    <span class="invalid-feedback">{{ errors.observations }}</span>
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
