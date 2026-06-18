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
const flocks = ref([]);
const vaccines = ref([]);

const schema = yup.object({
    flock_id: yup.string().required('Selecione o lote'),
    vaccine_id: yup.string().required('Selecione a vacina'),
    scheduled_date: yup.string().required(),
    administration_route: yup.string().oneOf(['injectable', 'water', 'feed']).required(),
    dosage: yup.string().nullable(),
    observations: yup.string().nullable(),
});

const getAuxiliarData = () => {
    Promise.all([
        axios.get('/admin/flocks-active'),
        axios.get('/admin/vaccines-all'),
    ]).then(([flocksResponse, vaccinesResponse]) => {
        flocks.value = flocksResponse.data;
        vaccines.value = vaccinesResponse.data;
        loadingDiv.value = false;
    }).catch(() => {
        toastr.error('Erro ao carregar dados auxiliares');
        router.push({ path: '/admin/calendario-vacinal' });
    });
};

const createRecordFunction = (values, actions) => {
    loading.value = true;

    axios.post('/admin/vaccination-schedule', values).then(() => {
        actions.resetForm();
        router.push({ path: '/admin/calendario-vacinal' });
        toastr.success('Vacinação agendada com sucesso');
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
        <h1 class="h3 mb-3">Calendário Vacinal</h1>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Agendar vacinação.</h5>
                        <router-link to="/admin/calendario-vacinal" class="btn btn-pill btn-primary mt-3">
                            <vue-feather type="arrow-left"></vue-feather>Voltar
                        </router-link>
                    </div>

                    <div class="card-body">
                        <Form @submit="createRecordFunction" :validation-schema="schema" v-slot="{ errors }" :initial-values="{ administration_route: 'injectable' }">
                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="flock_id">Lote</label>
                                    <Field as="select" class="form-control" :class="{'is-invalid': errors.flock_id}" name="flock_id" id="flock_id">
                                        <option value="" disabled>Selecionar lote</option>
                                        <option v-for="flock in flocks" :key="flock.id" :value="flock.id">
                                            {{ flock.code }} - {{ flock.house?.name }}
                                        </option>
                                    </Field>
                                    <span class="invalid-feedback">{{ errors.flock_id }}</span>
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="vaccine_id">Vacina</label>
                                    <Field as="select" class="form-control" :class="{'is-invalid': errors.vaccine_id}" name="vaccine_id" id="vaccine_id">
                                        <option value="" disabled>Selecionar vacina</option>
                                        <option v-for="vaccine in vaccines" :key="vaccine.id" :value="vaccine.id">
                                            {{ vaccine.name }} ({{ vaccine.batch }})
                                        </option>
                                    </Field>
                                    <span class="invalid-feedback">{{ errors.vaccine_id }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="scheduled_date">Data Prevista</label>
                                    <Field type="date" class="form-control" :class="{'is-invalid': errors.scheduled_date}" name="scheduled_date" id="scheduled_date"/>
                                    <span class="invalid-feedback">{{ errors.scheduled_date }}</span>
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="administration_route">Via de Administração</label>
                                    <Field as="select" class="form-control" :class="{'is-invalid': errors.administration_route}" name="administration_route" id="administration_route">
                                        <option value="injectable">Injetável</option>
                                        <option value="water">Água</option>
                                        <option value="feed">Ração</option>
                                    </Field>
                                    <span class="invalid-feedback">{{ errors.administration_route }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <label class="form-label" for="dosage">Dosagem</label>
                                    <Field type="text" class="form-control" :class="{'is-invalid': errors.dosage}" name="dosage" id="dosage" placeholder="Ex: 0.5ml/ave"/>
                                    <span class="invalid-feedback">{{ errors.dosage }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <label class="form-label" for="observations">Observações</label>
                                    <Field as="textarea" class="form-control" :class="{'is-invalid': errors.observations}" name="observations" id="observations" rows="3"/>
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
