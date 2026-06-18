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
const classifications = ref([]);

const schema = yup.object({
    classification_id: yup.string().required('Selecione a classificação'),
    package_type: yup.string().required(),
    quantity_used: yup.number().required().min(1),
    packaged_eggs: yup.number().required().min(1),
    remaining_eggs: yup.number().min(0).nullable(),
    expiry_date: yup.string().required(),
});

const getAuxiliarData = () => {
    axios.get('/admin/egg-classifications-all')
        .then((response) => {
            classifications.value = response.data;
            loadingDiv.value = false;
        })
        .catch(() => {
            toastr.error('Erro ao carregar classificações');
            router.push({ path: '/admin/embalagem' });
        });
};

const classificationLabel = (item) => {
    const flock = item.flock?.code || 'Sem lote';
    return `#${item.id} — ${flock} — ${item.processing_date}`;
};

const createRecordFunction = (values, actions) => {
    loading.value = true;

    const payload = {
        ...values,
        quantity_used: Number(values.quantity_used),
        packaged_eggs: Number(values.packaged_eggs),
        remaining_eggs: Number(values.remaining_eggs || 0),
    };

    axios.post('/admin/packaging', payload).then(() => {
        actions.resetForm();
        router.push({ path: '/admin/embalagem' });
        toastr.success('Embalagem criada com sucesso');
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
        <h1 class="h3 mb-3">Embalagem</h1>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Formulário de registo de embalagem.</h5>
                        <router-link to="/admin/embalagem" class="btn btn-pill btn-primary mt-3">
                            <vue-feather type="arrow-left"></vue-feather>Voltar
                        </router-link>
                    </div>

                    <div class="card-body">
                        <div v-if="classifications.length === 0" class="alert alert-warning">
                            Não existem classificações registadas. Crie uma classificação de ovos primeiro.
                        </div>

                        <Form @submit="createRecordFunction" :validation-schema="schema" v-slot="{ errors }" :initial-values="{ package_type: 'tray', quantity_used: 1, packaged_eggs: 30, remaining_eggs: 0 }">
                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <label class="form-label" for="classification_id">Classificação</label>
                                    <Field as="select" class="form-control" :class="{'is-invalid': errors.classification_id}" name="classification_id" id="classification_id">
                                        <option value="">Selecione...</option>
                                        <option v-for="item in classifications" :key="item.id" :value="item.id">{{ classificationLabel(item) }}</option>
                                    </Field>
                                    <span class="invalid-feedback">{{ errors.classification_id }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="package_type">Tipo de Embalagem</label>
                                    <Field as="select" class="form-control" :class="{'is-invalid': errors.package_type}" name="package_type" id="package_type">
                                        <option value="tray">Bandeja</option>
                                        <option value="box">Caixa</option>
                                    </Field>
                                    <span class="invalid-feedback">{{ errors.package_type }}</span>
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="expiry_date">Data de Validade</label>
                                    <Field type="date" class="form-control" :class="{'is-invalid': errors.expiry_date}" name="expiry_date" id="expiry_date"/>
                                    <span class="invalid-feedback">{{ errors.expiry_date }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="quantity_used">Embalagens Usadas</label>
                                    <Field type="number" class="form-control" :class="{'is-invalid': errors.quantity_used}" name="quantity_used" id="quantity_used" min="1"/>
                                    <span class="invalid-feedback">{{ errors.quantity_used }}</span>
                                </div>
                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="packaged_eggs">Ovos Embalados</label>
                                    <Field type="number" class="form-control" :class="{'is-invalid': errors.packaged_eggs}" name="packaged_eggs" id="packaged_eggs" min="1"/>
                                    <span class="invalid-feedback">{{ errors.packaged_eggs }}</span>
                                </div>
                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="remaining_eggs">Ovos Restantes</label>
                                    <Field type="number" class="form-control" :class="{'is-invalid': errors.remaining_eggs}" name="remaining_eggs" id="remaining_eggs" min="0"/>
                                    <span class="invalid-feedback">{{ errors.remaining_eggs }}</span>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary" :disabled="loading || classifications.length === 0">
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
