<script setup>

import { onMounted, ref, computed } from 'vue';
import axios from 'axios';
import { useToastr } from '../../../toastr';
import { Form, Field } from 'vee-validate';
import * as yup from 'yup';
import { useRouter } from 'vue-router';
import VueFeather from 'vue-feather';
import FileUpload from 'primevue/fileupload';

const loading = ref(false);
const toastr = useToastr();
const loadingDiv = ref(true);
let statusTechnician = 1;
let departments = ref([]);
let areas = ref([]);
let contractTypes = ref([]);
let selectedContractTypeId = ref('');
let image = ref();

const onFileUpload = (event) => {
    image.value = event.files[0];
};

const selectedContractType = computed(() => {
    return contractTypes.value.find(
        (type) => String(type.id) === String(selectedContractTypeId.value)
    );
});

const extraFields = computed(() => selectedContractType.value?.extra_fields || []);

const schema = yup.object({
    name: yup.string().required(),
    date_of_birth: yup.date().required(),
    admission_date: yup.date().required(),
    code: yup.string().required(),
    document: yup.string().required(),
    department_id: yup.string().required(),
    area_id: yup.string().required(),
    contact: yup.string(),
    gender: yup.string(),
    address: yup.string(),
    province: yup.string(),
    city: yup.string(),
    civil_status: yup.string(),
    salary: yup.number().required().min(0),
    contract_type_id: yup.string().nullable(),
});

const router = useRouter();

const validateExtraFields = (values) => {
    const errors = {};
    const fields = extraFields.value;

    fields.forEach((field) => {
        const value = values[`contract_extra_${field.key}`];
        if (field.required && (value === undefined || value === null || value === '')) {
            errors[`contract_extra_${field.key}`] = `${field.label} é obrigatório`;
        }
    });

    return errors;
};

const buildContractExtraData = (values) => {
    const data = {};

    extraFields.value.forEach((field) => {
        const value = values[`contract_extra_${field.key}`];
        if (value !== undefined && value !== null && value !== '') {
            data[field.key] = value;
        }
    });

    return data;
};

const createRecordFunction = (values, actions) => {
    const extraErrors = validateExtraFields(values);
    if (Object.keys(extraErrors).length > 0) {
        actions.setErrors(extraErrors);
        return;
    }

    loading.value = true;

    const payload = { ...values };
    payload.image = image.value;
    payload.contract_extra_data = JSON.stringify(buildContractExtraData(values));

    extraFields.value.forEach((field) => {
        delete payload[`contract_extra_${field.key}`];
    });

    axios.post('/technicians', payload, {
        headers: {
            'Content-Type': 'multipart/form-data'
        }
    }).then(() => {
        actions.resetForm();
        selectedContractTypeId.value = '';
        router.push({ path: '/admin/technicians' });
        toastr.success('Técnico criado com sucesso');
    }).catch((error) => {
        toastr.error('Erro ao adicionar. ' + error.response.data.message);
        if (error.response.data.errors) {
            actions.setErrors(error.response.data.errors);
        }
    }).finally(() => {
        loading.value = false;
    });
};

const getAuxiliarData = () => {
    axios.get('/auxiliar-create-technicians')
        .then((response) => {
            departments.value = response.data.departments;
            areas.value = response.data.areas;
            contractTypes.value = response.data.contract_types || [];
            loadingDiv.value = false;
        })
        .catch((error) => {
            toastr.error(error);
            router.push({ path: '/admin/departments' });
        });
};

onMounted(() => {
    getAuxiliarData();
});

</script>

<template>
    <div v-if="!loadingDiv">
        <h1 class="h3 mb-3">Técnico</h1>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Formulário criação dos Técnicos do sistema.</h5>
                        <router-link to="/admin/technicians" class="btn btn-pill btn-primary mt-3">
                            <vue-feather type="arrow-left"></vue-feather>Voltar
                        </router-link>
                    </div>

                    <div class="card-body">
                        <Form @submit="createRecordFunction" :validation-schema="schema" v-slot="{ errors }">
                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <label class="form-label" for="name">Nome</label>
                                    <Field type="text" class="form-control" :class="{'is-invalid': errors.name}" name="name" id="name" placeholder="Nome"/>
                                    <span class="invalid-feedback">{{ errors.name }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <label class="form-label" for="code">Código</label>
                                    <Field type="text" class="form-control" :class="{'is-invalid': errors.code}" name="code" id="code" placeholder="Código"/>
                                    <span class="invalid-feedback">{{ errors.code }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <label class="form-label" for="document">Documento</label>
                                    <Field type="text" class="form-control" :class="{'is-invalid': errors.document}" name="document" id="document" placeholder="Documento"/>
                                    <Field type="hidden" name="status" v-model="statusTechnician"/>
                                    <span class="invalid-feedback">{{ errors.document }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <label class="form-label" for="mobile_phone">Telefone Celular</label>
                                    <Field type="text" class="form-control" :class="{'is-invalid': errors.mobile_phone}" name="mobile_phone" id="mobile_phone" placeholder="Telefone Celular"/>
                                    <span class="invalid-feedback">{{ errors.mobile_phone }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <label class="form-label" for="salary">Salário</label>
                                    <Field type="number" class="form-control" :class="{'is-invalid': errors.salary}" name="salary" id="salary" placeholder="Salário"/>
                                    <span class="invalid-feedback">{{ errors.salary }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <label class="form-label" for="admission_date">Data de Admissão</label>
                                    <Field type="date" class="form-control" :class="{'is-invalid': errors.admission_date}" name="admission_date" id="admission_date"/>
                                    <span class="invalid-feedback">{{ errors.admission_date }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <label class="form-label" for="date_of_birth">Data de Nascimento</label>
                                    <Field type="date" class="form-control" :class="{'is-invalid': errors.date_of_birth}" name="date_of_birth" id="date_of_birth"/>
                                    <span class="invalid-feedback">{{ errors.date_of_birth }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <label class="form-label" for="department_id">Departamento</label>
                                    <Field as="select" class="form-control" :class="{'is-invalid': errors.department_id}" name="department_id" id="department_id">
                                        <option value="" disabled>Selecionar</option>
                                        <option v-for="department in departments" :key="department.id" :value="department.id">{{ department.name }}</option>
                                    </Field>
                                    <span class="invalid-feedback">{{ errors.department_id }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <label class="form-label" for="area_id">Área</label>
                                    <Field as="select" class="form-control" :class="{'is-invalid': errors.area_id}" name="area_id" id="area_id">
                                        <option value="" disabled>Selecionar</option>
                                        <option v-for="area in areas" :key="area.id" :value="area.id">{{ area.name }}</option>
                                    </Field>
                                    <span class="invalid-feedback">{{ errors.area_id }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <label class="form-label" for="contract_type_id">Tipo de Contrato</label>
                                    <Field as="select" class="form-control" :class="{'is-invalid': errors.contract_type_id}" name="contract_type_id" id="contract_type_id" v-model="selectedContractTypeId">
                                        <option value="">Selecionar (opcional)</option>
                                        <option v-for="contractType in contractTypes" :key="contractType.id" :value="contractType.id">
                                            {{ contractType.name }}
                                        </option>
                                    </Field>
                                    <span class="invalid-feedback">{{ errors.contract_type_id }}</span>
                                    <small class="text-muted">
                                        <router-link to="/admin/contract-types/create">Adicionar novo tipo de contrato</router-link>
                                    </small>
                                </div>
                            </div>

                            <div v-if="extraFields.length" class="row">
                                <div class="col-12">
                                    <h6 class="mb-3">Dados do contrato ({{ selectedContractType?.name }})</h6>
                                </div>
                                <div v-for="field in extraFields" :key="field.key" class="mb-3 col-md-12">
                                    <label class="form-label" :for="`contract_extra_${field.key}`">
                                        {{ field.label }}
                                        <span v-if="field.required" class="text-danger">*</span>
                                    </label>
                                    <Field
                                        v-if="field.type === 'textarea'"
                                        as="textarea"
                                        class="form-control"
                                        :class="{'is-invalid': errors[`contract_extra_${field.key}`]}"
                                        :name="`contract_extra_${field.key}`"
                                        :id="`contract_extra_${field.key}`"
                                        rows="3"
                                    />
                                    <Field
                                        v-else
                                        :type="field.type"
                                        class="form-control"
                                        :class="{'is-invalid': errors[`contract_extra_${field.key}`]}"
                                        :name="`contract_extra_${field.key}`"
                                        :id="`contract_extra_${field.key}`"
                                    />
                                    <span class="invalid-feedback">{{ errors[`contract_extra_${field.key}`] }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <label class="form-label" for="image">Fotografia:</label>
                                    <FileUpload mode="basic" class="form-control" name="image" accept="image/*" auto :maxFileSize="1000000" customUpload @uploader="onFileUpload" />
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
