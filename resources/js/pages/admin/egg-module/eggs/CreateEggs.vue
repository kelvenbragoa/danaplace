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
const rejectReasons = ref([]);

const today = new Date().toISOString().split('T')[0];

const schema = yup.object({
    flock_id: yup.string().required('Selecione o lote'),
    lay_date: yup.string().required(),
    quality: yup.string().required(),
    reject_reason: yup.string().nullable(),
    destination: yup.string().required(),
});

const getAuxiliarData = () => {
    Promise.all([
        axios.get('/admin/flocks-all'),
        axios.get('/admin/reject-reasons-all'),
    ]).then(([flocksResponse, reasonsResponse]) => {
        flocks.value = flocksResponse.data;
        rejectReasons.value = reasonsResponse.data;
        loadingDiv.value = false;
    }).catch(() => {
        toastr.error('Erro ao carregar dados auxiliares');
        router.push({ path: '/admin/ovos' });
    });
};

const createRecordFunction = (values, actions) => {
    loading.value = true;

    const payload = {
        ...values,
        reject_reason: values.reject_reason || null,
    };

    axios.post('/admin/eggs', payload).then(() => {
        actions.resetForm();
        router.push({ path: '/admin/ovos' });
        toastr.success('Ovo registado com sucesso');
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
        <h1 class="h3 mb-3">Ovo</h1>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Formulário de registo de ovos.</h5>
                        <router-link to="/admin/ovos" class="btn btn-pill btn-primary mt-3">
                            <vue-feather type="arrow-left"></vue-feather>Voltar
                        </router-link>
                    </div>

                    <div class="card-body">
                        <Form @submit="createRecordFunction" :validation-schema="schema" v-slot="{ errors }" :initial-values="{ lay_date: today, quality: 'clean', destination: 'packaged' }">
                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="flock_id">Lote</label>
                                    <Field as="select" class="form-control" :class="{'is-invalid': errors.flock_id}" name="flock_id" id="flock_id">
                                        <option value="">Selecione...</option>
                                        <option v-for="flock in flocks" :key="flock.id" :value="flock.id">{{ flock.code }}</option>
                                    </Field>
                                    <span class="invalid-feedback">{{ errors.flock_id }}</span>
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="lay_date">Data de Postura</label>
                                    <Field type="date" class="form-control" :class="{'is-invalid': errors.lay_date}" name="lay_date" id="lay_date"/>
                                    <span class="invalid-feedback">{{ errors.lay_date }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="quality">Qualidade</label>
                                    <Field as="select" class="form-control" :class="{'is-invalid': errors.quality}" name="quality" id="quality">
                                        <option value="clean">Limpo</option>
                                        <option value="dirty">Sujo</option>
                                        <option value="cracked">Rachado</option>
                                        <option value="deformed">Deformado</option>
                                    </Field>
                                    <span class="invalid-feedback">{{ errors.quality }}</span>
                                </div>
                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="destination">Destino</label>
                                    <Field as="select" class="form-control" :class="{'is-invalid': errors.destination}" name="destination" id="destination">
                                        <option value="packaged">Embalado</option>
                                        <option value="reject">Refugo</option>
                                        <option value="broken">Partido</option>
                                    </Field>
                                    <span class="invalid-feedback">{{ errors.destination }}</span>
                                </div>
                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="reject_reason">Motivo de Refugo</label>
                                    <Field as="select" class="form-control" :class="{'is-invalid': errors.reject_reason}" name="reject_reason" id="reject_reason">
                                        <option value="">Nenhum</option>
                                        <option v-for="reason in rejectReasons" :key="reason.id" :value="reason.name">{{ reason.name }}</option>
                                    </Field>
                                    <span class="invalid-feedback">{{ errors.reject_reason }}</span>
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
                    <div class="spinner-border" role="status"><span class="sr-only"></span></div>
                </div>
                <br>
                <div class="d-flex justify-content-center">Carregando Dados...</div>
            </div>
        </div>
    </div>
</template>
