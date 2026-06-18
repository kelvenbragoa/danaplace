<script setup>

import axios from 'axios';
import { ref, onMounted, computed } from 'vue';
import { useToastr } from '../../../../toastr';
import { Form, Field } from 'vee-validate';
import { useRouter } from 'vue-router';
import * as yup from 'yup';
import VueFeather from 'vue-feather';

const retrievedData = ref({});
const flocks = ref([]);
const loadingButtonSubmit = ref(false);
const loadingDiv = ref(true);
const toastr = useToastr();
const router = useRouter();

const formValues = ref({
    washed_eggs: 0,
    unwashed_eggs: 0,
    total_rejects: 0,
});

const schema = yup.object({
    flock_id: yup.string().required('Selecione o lote'),
    processing_date: yup.string().required(),
    washed_eggs: yup.number().min(0).nullable(),
    unwashed_eggs: yup.number().min(0).nullable(),
    total_rejects: yup.number().min(0).nullable(),
});

const totalProcessedPreview = computed(() => {
    return Number(formValues.value.washed_eggs || 0) + Number(formValues.value.unwashed_eggs || 0);
});

const rejectPercentagePreview = computed(() => {
    if (!totalProcessedPreview.value) return 0;
    return Math.round((Number(formValues.value.total_rejects || 0) / totalProcessedPreview.value) * 100);
});

const getData = () => {
    Promise.all([
        axios.get(`/admin/egg-classifications/${router.currentRoute.value.params.id}`),
        axios.get('/admin/flocks-all'),
    ]).then(([classificationResponse, flocksResponse]) => {
        loadingDiv.value = false;
        retrievedData.value = classificationResponse.data;
        flocks.value = flocksResponse.data;
        formValues.value = {
            washed_eggs: classificationResponse.data.washed_eggs || 0,
            unwashed_eggs: classificationResponse.data.unwashed_eggs || 0,
            total_rejects: classificationResponse.data.total_rejects || 0,
        };
    }).catch(() => {
        loadingDiv.value = false;
    });
};

const updatePreview = (values) => {
    formValues.value = {
        washed_eggs: values.washed_eggs ?? retrievedData.value.washed_eggs,
        unwashed_eggs: values.unwashed_eggs ?? retrievedData.value.unwashed_eggs,
        total_rejects: values.total_rejects ?? retrievedData.value.total_rejects,
    };
};

const editFunction = (values, actions) => {
    loadingButtonSubmit.value = true;

    const payload = {
        ...values,
        washed_eggs: Number(values.washed_eggs || 0),
        unwashed_eggs: Number(values.unwashed_eggs || 0),
        total_rejects: Number(values.total_rejects || 0),
    };

    axios.patch(`/admin/egg-classifications/${retrievedData.value.id}`, payload).then(() => {
        router.push({ path: '/admin/classificacao-ovos' });
        toastr.success('Classificação editada com sucesso');
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
        <h1 class="h3 mb-3">Classificação de Ovos</h1>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Editar classificação</h5>
                        <router-link to="/admin/classificacao-ovos" class="btn btn-pill btn-primary mt-3">
                            <vue-feather type="arrow-left"></vue-feather>Voltar
                        </router-link>
                    </div>

                    <div class="card-body">
                        <Form @submit="editFunction" :validation-schema="schema" v-slot="{ errors, values }">
                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="flock_id">Lote</label>
                                    <Field as="select" class="form-control" :class="{'is-invalid': errors.flock_id}" name="flock_id" id="flock_id" v-model="retrievedData.flock_id">
                                        <option value="" disabled>Selecionar lote</option>
                                        <option v-for="flock in flocks" :key="flock.id" :value="flock.id">
                                            {{ flock.code }} - {{ flock.house?.name }}
                                        </option>
                                    </Field>
                                    <span class="invalid-feedback">{{ errors.flock_id }}</span>
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="processing_date">Data de Processamento</label>
                                    <Field type="date" class="form-control" :class="{'is-invalid': errors.processing_date}" name="processing_date" v-model="retrievedData.processing_date" id="processing_date"/>
                                    <span class="invalid-feedback">{{ errors.processing_date }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="washed_eggs">Ovos Lavados</label>
                                    <Field type="number" class="form-control" :class="{'is-invalid': errors.washed_eggs}" name="washed_eggs" v-model="retrievedData.washed_eggs" id="washed_eggs" min="0" @input="updatePreview(values)"/>
                                    <span class="invalid-feedback">{{ errors.washed_eggs }}</span>
                                </div>
                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="unwashed_eggs">Ovos Não Lavados</label>
                                    <Field type="number" class="form-control" :class="{'is-invalid': errors.unwashed_eggs}" name="unwashed_eggs" v-model="retrievedData.unwashed_eggs" id="unwashed_eggs" min="0" @input="updatePreview(values)"/>
                                    <span class="invalid-feedback">{{ errors.unwashed_eggs }}</span>
                                </div>
                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="total_rejects">Total Refugos</label>
                                    <Field type="number" class="form-control" :class="{'is-invalid': errors.total_rejects}" name="total_rejects" v-model="retrievedData.total_rejects" id="total_rejects" min="0" @input="updatePreview(values)"/>
                                    <span class="invalid-feedback">{{ errors.total_rejects }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <p class="text-muted mb-0">
                                        Total processado: <strong>{{ totalProcessedPreview }}</strong> |
                                        Taxa de refugo: <strong>{{ rejectPercentagePreview }}%</strong>
                                    </p>
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
