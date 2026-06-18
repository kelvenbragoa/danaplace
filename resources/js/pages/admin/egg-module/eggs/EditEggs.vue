<script setup>

import axios from 'axios';
import { ref, onMounted } from 'vue';
import { useToastr } from '../../../../toastr';
import { Form, Field } from 'vee-validate';
import { useRouter } from 'vue-router';
import * as yup from 'yup';
import VueFeather from 'vue-feather';

const retrievedData = ref({});
const categories = ref([]);
const rejectReasons = ref([]);
const loadingButtonSubmit = ref(false);
const loadingDiv = ref(true);
const toastr = useToastr();
const router = useRouter();

const schema = yup.object({
    category_id: yup.string().nullable(),
    quality: yup.string().required(),
    reject_reason: yup.string().nullable(),
    destination: yup.string().required(),
});

const getData = () => {
    Promise.all([
        axios.get(`/admin/eggs/${router.currentRoute.value.params.id}`),
        axios.get('/admin/egg-categories-all'),
        axios.get('/admin/reject-reasons-all'),
    ]).then(([eggResponse, categoriesResponse, reasonsResponse]) => {
        loadingDiv.value = false;
        retrievedData.value = eggResponse.data;
        categories.value = categoriesResponse.data;
        rejectReasons.value = reasonsResponse.data;
    }).catch(() => {
        loadingDiv.value = false;
        toastr.error('Registo não encontrado');
        router.push({ path: '/admin/ovos' });
    });
};

const editFunction = (values, actions) => {
    loadingButtonSubmit.value = true;

    const payload = {
        ...values,
        category_id: values.category_id || null,
        reject_reason: values.reject_reason || null,
    };

    axios.patch(`/admin/eggs/${retrievedData.value.id}`, payload).then(() => {
        router.push({ path: '/admin/ovos' });
        toastr.success('Ovo editado com sucesso');
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
        <h1 class="h3 mb-3">Ovo</h1>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Rastreio: {{ retrievedData.traceability_code }}</h5>
                        <router-link to="/admin/ovos" class="btn btn-pill btn-primary mt-3">
                            <vue-feather type="arrow-left"></vue-feather>Voltar
                        </router-link>
                    </div>

                    <div class="card-body">
                        <p class="text-muted mb-3">
                            Lote: <strong>{{ retrievedData.flock?.code }}</strong> — Postura: <strong>{{ retrievedData.lay_date }}</strong>
                        </p>

                        <Form @submit="editFunction" :validation-schema="schema" v-slot="{ errors }">
                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="category_id">Categoria</label>
                                    <Field as="select" class="form-control" :class="{'is-invalid': errors.category_id}" name="category_id" v-model="retrievedData.category_id" id="category_id">
                                        <option value="">Sem categoria</option>
                                        <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
                                    </Field>
                                    <span class="invalid-feedback">{{ errors.category_id }}</span>
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="quality">Qualidade</label>
                                    <Field as="select" class="form-control" :class="{'is-invalid': errors.quality}" name="quality" v-model="retrievedData.quality" id="quality">
                                        <option value="clean">Limpo</option>
                                        <option value="dirty">Sujo</option>
                                        <option value="cracked">Rachado</option>
                                        <option value="deformed">Deformado</option>
                                    </Field>
                                    <span class="invalid-feedback">{{ errors.quality }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="destination">Destino</label>
                                    <Field as="select" class="form-control" :class="{'is-invalid': errors.destination}" name="destination" v-model="retrievedData.destination" id="destination">
                                        <option value="packaged">Embalado</option>
                                        <option value="reject">Refugo</option>
                                        <option value="broken">Partido</option>
                                    </Field>
                                    <span class="invalid-feedback">{{ errors.destination }}</span>
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="reject_reason">Motivo de Refugo</label>
                                    <Field as="select" class="form-control" :class="{'is-invalid': errors.reject_reason}" name="reject_reason" v-model="retrievedData.reject_reason" id="reject_reason">
                                        <option value="">Nenhum</option>
                                        <option v-for="reason in rejectReasons" :key="reason.id" :value="reason.name">{{ reason.name }}</option>
                                    </Field>
                                    <span class="invalid-feedback">{{ errors.reject_reason }}</span>
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
