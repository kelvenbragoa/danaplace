<script setup>

import axios from 'axios';
import { ref, onMounted } from 'vue';
import { useToastr } from '../../../../toastr';
import { Form, Field } from 'vee-validate';
import { useRouter } from 'vue-router';
import * as yup from 'yup';
import VueFeather from 'vue-feather';
import moment from 'moment';

const retrievedData = ref({});
const loadingButtonSubmit = ref(false);
const loadingDiv = ref(true);
const toastr = useToastr();
const router = useRouter();

const schema = yup.object({
    carrier: yup.string().required(),
    vehicle_plate: yup.string().required(),
    driver_name: yup.string().required(),
    vehicle_temperature: yup.number().nullable(),
    seal_number: yup.string().nullable(),
    health_certificate: yup.string().nullable(),
    delivery_note_number: yup.string().nullable(),
    delivered_to: yup.string().nullable(),
    delivered_at: yup.string().nullable(),
});



const formatDeliveredAtForInput = (value) => {
    if (!value) {
        return '';
    }

    return moment(value).format('YYYY-MM-DDTHH:mm');
};

const getData = () => {
    axios.get(`/admin/egg-shipping/${router.currentRoute.value.params.id}`)
        .then((response) => {
            loadingDiv.value = false;
            retrievedData.value = {
                ...response.data,
                delivered_at: formatDeliveredAtForInput(response.data.delivered_at),
            };
        }).catch(() => {
            loadingDiv.value = false;
            toastr.error('Registo não encontrado');
            router.push({ path: '/admin/expedicao-ovos' });
        });
};

const editFunction = (values, actions) => {
    loadingButtonSubmit.value = true;

    const payload = {
        ...values,
        vehicle_temperature: values.vehicle_temperature ? Number(values.vehicle_temperature) : null,
    };

    axios.patch(`/admin/egg-shipping/${retrievedData.value.id}`, payload).then(() => {
        router.push({ path: '/admin/expedicao-ovos' });
        toastr.success('Expedição editada com sucesso');
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
        <h1 class="h3 mb-3">Expedição de Ovos</h1>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Expedição #{{ retrievedData.id }}</h5>
                        <router-link to="/admin/expedicao-ovos" class="btn btn-pill btn-primary mt-3">
                            <vue-feather type="arrow-left"></vue-feather>Voltar
                        </router-link>
                    </div>

                    <div class="card-body">
                        <p class="text-muted mb-3">
                            Fatura: <strong>{{ retrievedData.invoice_number }}</strong>
                            — Cliente: <strong>{{ retrievedData.order?.customer_name }}</strong>
                            — Data: <strong>{{ retrievedData.shipping_date }}</strong>
                        </p>

                        <Form @submit="editFunction" :validation-schema="schema" v-slot="{ errors }">
                            <div class="row">
                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="carrier">Transportadora</label>
                                    <Field type="text" class="form-control" :class="{'is-invalid': errors.carrier}" name="carrier" v-model="retrievedData.carrier" id="carrier"/>
                                    <span class="invalid-feedback">{{ errors.carrier }}</span>
                                </div>
                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="vehicle_plate">Matrícula</label>
                                    <Field type="text" class="form-control" :class="{'is-invalid': errors.vehicle_plate}" name="vehicle_plate" v-model="retrievedData.vehicle_plate" id="vehicle_plate"/>
                                    <span class="invalid-feedback">{{ errors.vehicle_plate }}</span>
                                </div>
                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="driver_name">Motorista</label>
                                    <Field type="text" class="form-control" :class="{'is-invalid': errors.driver_name}" name="driver_name" v-model="retrievedData.driver_name" id="driver_name"/>
                                    <span class="invalid-feedback">{{ errors.driver_name }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="vehicle_temperature">Temperatura (°C)</label>
                                    <Field type="number" step="0.1" class="form-control" :class="{'is-invalid': errors.vehicle_temperature}" name="vehicle_temperature" v-model="retrievedData.vehicle_temperature" id="vehicle_temperature"/>
                                    <span class="invalid-feedback">{{ errors.vehicle_temperature }}</span>
                                </div>
                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="seal_number">Nº Lacre</label>
                                    <Field type="text" class="form-control" :class="{'is-invalid': errors.seal_number}" name="seal_number" v-model="retrievedData.seal_number" id="seal_number"/>
                                    <span class="invalid-feedback">{{ errors.seal_number }}</span>
                                </div>
                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="health_certificate">Certificado Sanitário</label>
                                    <Field type="text" class="form-control" :class="{'is-invalid': errors.health_certificate}" name="health_certificate" v-model="retrievedData.health_certificate" id="health_certificate"/>
                                    <span class="invalid-feedback">{{ errors.health_certificate }}</span>
                                </div>
                            </div>

                            <h6 class="mt-2 mb-3">Guia de Entrega</h6>
                            <div class="row">
                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="delivery_note_number">Nº Guia de Entrega</label>
                                    <Field type="text" class="form-control" :class="{'is-invalid': errors.delivery_note_number}" name="delivery_note_number" v-model="retrievedData.delivery_note_number" id="delivery_note_number"/>
                                    <span class="invalid-feedback">{{ errors.delivery_note_number }}</span>
                                </div>
                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="delivered_to">Entregue a</label>
                                    <Field type="text" class="form-control" :class="{'is-invalid': errors.delivered_to}" name="delivered_to" v-model="retrievedData.delivered_to" id="delivered_to"/>
                                    <span class="invalid-feedback">{{ errors.delivered_to }}</span>
                                </div>
                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="delivered_at">Data e Hora da Entrega</label>
                                    <Field type="datetime-local" class="form-control" :class="{'is-invalid': errors.delivered_at}" name="delivered_at" v-model="retrievedData.delivered_at" id="delivered_at"/>
                                    <span class="invalid-feedback">{{ errors.delivered_at }}</span>
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
