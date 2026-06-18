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

const today = new Date().toISOString().split('T')[0];

const schema = yup.object({
    flock_id: yup.string().required('Selecione o lote'),
    date: yup.string().required(),
    quantity: yup.number().required().min(1, 'Mínimo 1 ave'),
    probable_cause: yup.string().nullable(),
    necropsy_performed: yup.boolean(),
    necropsy_report: yup.string().nullable(),
});

const getAuxiliarData = () => {
    axios.get('/admin/flocks-active')
        .then((response) => {
            flocks.value = response.data;
            loadingDiv.value = false;
        })
        .catch(() => {
            toastr.error('Erro ao carregar lotes');
            router.push({ path: '/admin/mortalidade' });
        });
};

const createRecordFunction = (values, actions) => {
    loading.value = true;

    const payload = {
        ...values,
        quantity: Number(values.quantity),
        necropsy_performed: Boolean(values.necropsy_performed),
    };

    axios.post('/admin/mortality', payload).then(() => {
        actions.resetForm();
        router.push({ path: '/admin/mortalidade' });
        toastr.success('Mortalidade registada com sucesso');
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
        <h1 class="h3 mb-3">Mortalidade</h1>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Registo de mortalidade.</h5>
                        <router-link to="/admin/mortalidade" class="btn btn-pill btn-primary mt-3">
                            <vue-feather type="arrow-left"></vue-feather>Voltar
                        </router-link>
                    </div>

                    <div class="card-body">
                        <Form @submit="createRecordFunction" :validation-schema="schema" v-slot="{ errors }" :initial-values="{ date: today, necropsy_performed: false }">
                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="flock_id">Lote</label>
                                    <Field as="select" class="form-control" :class="{'is-invalid': errors.flock_id}" name="flock_id" id="flock_id">
                                        <option value="" disabled>Selecionar lote</option>
                                        <option v-for="flock in flocks" :key="flock.id" :value="flock.id">
                                            {{ flock.code }} - {{ flock.house?.name }} ({{ flock.current_bird_count }} aves)
                                        </option>
                                    </Field>
                                    <span class="invalid-feedback">{{ errors.flock_id }}</span>
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="date">Data</label>
                                    <Field type="date" class="form-control" :class="{'is-invalid': errors.date}" name="date" id="date"/>
                                    <span class="invalid-feedback">{{ errors.date }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="quantity">Quantidade de Aves</label>
                                    <Field type="number" class="form-control" :class="{'is-invalid': errors.quantity}" name="quantity" id="quantity" min="1"/>
                                    <span class="invalid-feedback">{{ errors.quantity }}</span>
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="probable_cause">Causa Provável</label>
                                    <Field type="text" class="form-control" :class="{'is-invalid': errors.probable_cause}" name="probable_cause" id="probable_cause" placeholder="Ex: Doença respiratória"/>
                                    <span class="invalid-feedback">{{ errors.probable_cause }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <div class="form-check">
                                        <Field type="checkbox" class="form-check-input" name="necropsy_performed" id="necropsy_performed" :value="true"/>
                                        <label class="form-check-label" for="necropsy_performed">Necropsia realizada</label>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <label class="form-label" for="necropsy_report">Relatório de Necropsia</label>
                                    <Field as="textarea" class="form-control" :class="{'is-invalid': errors.necropsy_report}" name="necropsy_report" id="necropsy_report" rows="4" placeholder="Detalhes da necropsia..."/>
                                    <span class="invalid-feedback">{{ errors.necropsy_report }}</span>
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
