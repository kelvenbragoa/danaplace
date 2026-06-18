<script setup>

import axios from 'axios';
import { ref, onMounted } from 'vue';
import { useToastr } from '../../../../toastr';
import { Form, Field } from 'vee-validate';
import { useRouter } from 'vue-router';
import * as yup from 'yup';
import VueFeather from 'vue-feather';

const retrievedData = ref({});
const flocks = ref([]);
const vaccines = ref([]);
const loadingButtonSubmit = ref(false);
const loadingDiv = ref(true);
const toastr = useToastr();
const router = useRouter();

const schema = yup.object({
    flock_id: yup.string().required('Selecione o lote'),
    vaccine_id: yup.string().required('Selecione a vacina'),
    scheduled_date: yup.string().required(),
    administration_route: yup.string().oneOf(['injectable', 'water', 'feed']).required(),
    dosage: yup.string().nullable(),
    observations: yup.string().nullable(),
});

const getData = () => {
    Promise.all([
        axios.get(`/admin/vaccination-schedule/${router.currentRoute.value.params.id}`),
        axios.get('/admin/flocks-active'),
        axios.get('/admin/vaccines-all'),
    ]).then(([scheduleResponse, flocksResponse, vaccinesResponse]) => {
        loadingDiv.value = false;
        retrievedData.value = scheduleResponse.data;
        flocks.value = flocksResponse.data;
        vaccines.value = vaccinesResponse.data;
    }).catch(() => {
        loadingDiv.value = false;
    });
};

const editFunction = (values, actions) => {
    loadingButtonSubmit.value = true;

    axios.patch(`/admin/vaccination-schedule/${retrievedData.value.id}`, values).then(() => {
        router.push({ path: '/admin/calendario-vacinal' });
        toastr.success('Agendamento editado com sucesso');
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
        <h1 class="h3 mb-3">Calendário Vacinal</h1>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Editar agendamento</h5>
                        <router-link to="/admin/calendario-vacinal" class="btn btn-pill btn-primary mt-3">
                            <vue-feather type="arrow-left"></vue-feather>Voltar
                        </router-link>
                    </div>

                    <div class="card-body">
                        <div v-if="retrievedData.status !== 'pending'" class="alert alert-info">
                            Este agendamento já foi {{ retrievedData.status === 'applied' ? 'aplicado' : 'cancelado' }}. Apenas observações e dosagem podem ser ajustadas com cuidado.
                        </div>

                        <Form @submit="editFunction" :validation-schema="schema" v-slot="{ errors }">
                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="flock_id">Lote</label>
                                    <Field as="select" class="form-control" :class="{'is-invalid': errors.flock_id}" name="flock_id" id="flock_id" v-model="retrievedData.flock_id" :disabled="retrievedData.status !== 'pending'">
                                        <option value="" disabled>Selecionar lote</option>
                                        <option v-for="flock in flocks" :key="flock.id" :value="flock.id">
                                            {{ flock.code }} - {{ flock.house?.name }}
                                        </option>
                                    </Field>
                                    <span class="invalid-feedback">{{ errors.flock_id }}</span>
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="vaccine_id">Vacina</label>
                                    <Field as="select" class="form-control" :class="{'is-invalid': errors.vaccine_id}" name="vaccine_id" id="vaccine_id" v-model="retrievedData.vaccine_id" :disabled="retrievedData.status !== 'pending'">
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
                                    <Field type="date" class="form-control" :class="{'is-invalid': errors.scheduled_date}" name="scheduled_date" v-model="retrievedData.scheduled_date" id="scheduled_date" :disabled="retrievedData.status !== 'pending'"/>
                                    <span class="invalid-feedback">{{ errors.scheduled_date }}</span>
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="administration_route">Via de Administração</label>
                                    <Field as="select" class="form-control" :class="{'is-invalid': errors.administration_route}" name="administration_route" id="administration_route" v-model="retrievedData.administration_route" :disabled="retrievedData.status !== 'pending'">
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
                                    <Field type="text" class="form-control" :class="{'is-invalid': errors.dosage}" name="dosage" v-model="retrievedData.dosage" id="dosage"/>
                                    <span class="invalid-feedback">{{ errors.dosage }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <label class="form-label" for="observations">Observações</label>
                                    <Field as="textarea" class="form-control" :class="{'is-invalid': errors.observations}" name="observations" v-model="retrievedData.observations" id="observations" rows="3"/>
                                    <span class="invalid-feedback">{{ errors.observations }}</span>
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
