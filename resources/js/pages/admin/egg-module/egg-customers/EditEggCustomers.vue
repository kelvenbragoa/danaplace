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
const loadingRegenerate = ref(false);
const toastr = useToastr();
const router = useRouter();

const schema = yup.object({
    name: yup.string().required().max(100),
    tax_id: yup.string().nullable().max(18),
    email: yup.string().email().nullable(),
    phone: yup.string().nullable().max(20),
    address: yup.string().nullable().max(255),
    is_active: yup.boolean(),
});

const getData = () => {
    axios.get(`/admin/egg-customers/${router.currentRoute.value.params.id}`)
        .then((response) => {
            loadingDiv.value = false;
            retrievedData.value = response.data;
        }).catch(() => {
            loadingDiv.value = false;
            toastr.error('Registo não encontrado');
            router.push({ path: '/admin/clientes-ovos' });
        });
};

const editFunction = (values, actions) => {
    loadingButtonSubmit.value = true;

    const payload = {
        ...values,
        is_active: values.is_active === true || values.is_active === 'true',
    };

    axios.patch(`/admin/egg-customers/${retrievedData.value.id}`, payload).then(() => {
        router.push({ path: '/admin/clientes-ovos' });
        toastr.success('Cliente editado com sucesso');
    }).catch((error) => {
        toastr.error('Erro ao editar');
        if (error.response?.data?.errors) {
            actions.setErrors(error.response.data.errors);
        }
    }).finally(() => {
        loadingButtonSubmit.value = false;
    });
};

const regenerateCode = () => {
    loadingRegenerate.value = true;
    axios.post(`/admin/egg-customers/${retrievedData.value.id}/regenerate-portal-code`)
        .then((response) => {
            retrievedData.value.portal_code = response.data.portal_code;
            toastr.success('Novo código gerado com sucesso');
        }).catch(() => {
            toastr.error('Erro ao regenerar código');
        }).finally(() => {
            loadingRegenerate.value = false;
        });
};

const copyPortalAccess = () => {
    const url = `${window.location.origin}/portal/pedidos-ovos`;
    const text = `Portal: ${url}\nCódigo: ${retrievedData.value.portal_code}`;
    navigator.clipboard.writeText(text).then(() => {
        toastr.success('Link e código copiados');
    });
};

onMounted(() => {
    getData();
});

</script>

<template>
    <div v-if="!loadingDiv">
        <h1 class="h3 mb-3">Cliente de Ovos</h1>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Cliente: {{ retrievedData.name }}</h5>
                        <router-link to="/admin/clientes-ovos" class="btn btn-pill btn-primary mt-3">
                            <vue-feather type="arrow-left"></vue-feather>Voltar
                        </router-link>
                    </div>

                    <div class="card-body">
                        <div class="alert alert-info">
                            <strong>Código de acesso ao portal:</strong> <code>{{ retrievedData.portal_code }}</code>
                            <div class="mt-2">
                                <button type="button" class="btn btn-sm btn-outline-primary me-2" @click="copyPortalAccess">
                                    Copiar link e código
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-warning" @click="regenerateCode" :disabled="loadingRegenerate">
                                    Regenerar código
                                </button>
                            </div>
                            <small class="d-block mt-2 text-muted">Portal: /portal/pedidos-ovos</small>
                        </div>

                        <Form @submit="editFunction" :validation-schema="schema" v-slot="{ errors }">
                            <div class="row">
                                <div class="mb-3 col-md-8">
                                    <label class="form-label" for="name">Nome</label>
                                    <Field type="text" class="form-control" :class="{'is-invalid': errors.name}" name="name" v-model="retrievedData.name" id="name"/>
                                    <span class="invalid-feedback">{{ errors.name }}</span>
                                </div>
                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="tax_id">NUIT</label>
                                    <Field type="text" class="form-control" :class="{'is-invalid': errors.tax_id}" name="tax_id" v-model="retrievedData.tax_id" id="tax_id"/>
                                    <span class="invalid-feedback">{{ errors.tax_id }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="email">Email</label>
                                    <Field type="email" class="form-control" :class="{'is-invalid': errors.email}" name="email" v-model="retrievedData.email" id="email"/>
                                    <span class="invalid-feedback">{{ errors.email }}</span>
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="phone">Telefone</label>
                                    <Field type="text" class="form-control" :class="{'is-invalid': errors.phone}" name="phone" v-model="retrievedData.phone" id="phone"/>
                                    <span class="invalid-feedback">{{ errors.phone }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <label class="form-label" for="address">Morada</label>
                                    <Field type="text" class="form-control" :class="{'is-invalid': errors.address}" name="address" v-model="retrievedData.address" id="address"/>
                                    <span class="invalid-feedback">{{ errors.address }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <div class="form-check">
                                        <Field type="checkbox" class="form-check-input" name="is_active" id="is_active" :value="true" v-model="retrievedData.is_active"/>
                                        <label class="form-check-label" for="is_active">Ativo</label>
                                    </div>
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
