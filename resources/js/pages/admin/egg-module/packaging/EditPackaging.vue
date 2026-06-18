<script setup>

import axios from 'axios';
import { ref, onMounted } from 'vue';
import { useToastr } from '../../../../toastr';
import { Form, Field } from 'vee-validate';
import { useRouter } from 'vue-router';
import * as yup from 'yup';
import VueFeather from 'vue-feather';

const retrievedData = ref({});
const loadingButtonSubmit = ref(false);
const loadingDiv = ref(true);
const toastr = useToastr();
const router = useRouter();

const schema = yup.object({
    package_type: yup.string().required(),
    quantity_used: yup.number().required().min(1),
    packaged_eggs: yup.number().required().min(1),
    remaining_eggs: yup.number().min(0).nullable(),
    expiry_date: yup.string().required(),
});

const getData = () => {
    axios.get(`/admin/packaging/${router.currentRoute.value.params.id}`)
        .then((response) => {
            loadingDiv.value = false;
            retrievedData.value = response.data;
        }).catch(() => {
            loadingDiv.value = false;
            toastr.error('Registo não encontrado');
            router.push({ path: '/admin/embalagem' });
        });
};

const editFunction = (values, actions) => {
    loadingButtonSubmit.value = true;

    const payload = {
        ...values,
        quantity_used: Number(values.quantity_used),
        packaged_eggs: Number(values.packaged_eggs),
        remaining_eggs: Number(values.remaining_eggs || 0),
    };

    axios.patch(`/admin/packaging/${retrievedData.value.id}`, payload).then(() => {
        router.push({ path: '/admin/embalagem' });
        toastr.success('Embalagem editada com sucesso');
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
        <h1 class="h3 mb-3">Embalagem</h1>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Embalagem #{{ retrievedData.id }}</h5>
                        <router-link to="/admin/embalagem" class="btn btn-pill btn-primary mt-3">
                            <vue-feather type="arrow-left"></vue-feather>Voltar
                        </router-link>
                    </div>

                    <div class="card-body">
                        <p class="text-muted mb-3">
                            Classificação: <strong>#{{ retrievedData.classification_id }}</strong>
                            — Lote: <strong>{{ retrievedData.classification?.flock?.code || '-' }}</strong>
                        </p>

                        <Form @submit="editFunction" :validation-schema="schema" v-slot="{ errors }">
                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="package_type">Tipo de Embalagem</label>
                                    <Field as="select" class="form-control" :class="{'is-invalid': errors.package_type}" name="package_type" v-model="retrievedData.package_type" id="package_type">
                                        <option value="tray">Bandeja</option>
                                        <option value="box">Caixa</option>
                                    </Field>
                                    <span class="invalid-feedback">{{ errors.package_type }}</span>
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="expiry_date">Data de Validade</label>
                                    <Field type="date" class="form-control" :class="{'is-invalid': errors.expiry_date}" name="expiry_date" v-model="retrievedData.expiry_date" id="expiry_date"/>
                                    <span class="invalid-feedback">{{ errors.expiry_date }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="quantity_used">Embalagens Usadas</label>
                                    <Field type="number" class="form-control" :class="{'is-invalid': errors.quantity_used}" name="quantity_used" v-model="retrievedData.quantity_used" id="quantity_used" min="1"/>
                                    <span class="invalid-feedback">{{ errors.quantity_used }}</span>
                                </div>
                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="packaged_eggs">Ovos Embalados</label>
                                    <Field type="number" class="form-control" :class="{'is-invalid': errors.packaged_eggs}" name="packaged_eggs" v-model="retrievedData.packaged_eggs" id="packaged_eggs" min="1"/>
                                    <span class="invalid-feedback">{{ errors.packaged_eggs }}</span>
                                </div>
                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="remaining_eggs">Ovos Restantes</label>
                                    <Field type="number" class="form-control" :class="{'is-invalid': errors.remaining_eggs}" name="remaining_eggs" v-model="retrievedData.remaining_eggs" id="remaining_eggs" min="0"/>
                                    <span class="invalid-feedback">{{ errors.remaining_eggs }}</span>
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
