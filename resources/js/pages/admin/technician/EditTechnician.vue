<script setup>

import axios from 'axios';
import { ref, onMounted, computed } from 'vue';
import { useToastr } from '../../../toastr';
import { Form, Field } from 'vee-validate';
import { useRouter } from 'vue-router';
import * as yup from 'yup';
import VueFeather from 'vue-feather';
import FileUpload from 'primevue/fileupload';

const retrievedData = ref({});
const loadingButtonSubmit = ref(false);
const loadingDiv = ref(true);
const toastr = useToastr();
const router = useRouter();
const departments = ref([]);
const areas = ref([]);
const contractTypes = ref([]);
const selectedContractTypeId = ref('');
const image = ref();

const schema = yup.object({
    name: yup.string().required(),
    salary: yup.number().required().min(0),
    contract_type_id: yup.string().nullable(),
});

const selectedContractType = computed(() => {
    return contractTypes.value.find(
        (type) => String(type.id) === String(selectedContractTypeId.value)
    );
});

const extraFields = computed(() => selectedContractType.value?.extra_fields || []);

const onFileUpload = (event) => {
    image.value = event.files[0];
};

const getExtraFieldValue = (key) => {
    return retrievedData.value.contract_extra_data?.[key] ?? '';
};

const validateExtraFields = (values) => {
    const errors = {};

    extraFields.value.forEach((field) => {
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

const getData = () => {
    axios.get(`/technicians/${router.currentRoute.value.params.id}/edit`)
        .then((response) => {
            loadingDiv.value = false;
            retrievedData.value = response.data.technician;
            departments.value = response.data.departments;
            areas.value = response.data.areas;
            contractTypes.value = response.data.contract_types || [];
            selectedContractTypeId.value = response.data.technician.contract_type_id || '';
        }).catch(() => {
            loadingDiv.value = false;
        });
};

const editFunction = (values, actions) => {
    const extraErrors = validateExtraFields(values);
    if (Object.keys(extraErrors).length > 0) {
        actions.setErrors(extraErrors);
        return;
    }

    loadingButtonSubmit.value = true;

    const payload = { ...values };
    payload.image = image.value;
    payload._method = 'PUT';
    payload.contract_extra_data = JSON.stringify(buildContractExtraData(values));

    extraFields.value.forEach((field) => {
        delete payload[`contract_extra_${field.key}`];
    });

    axios.post(`/technicians/${retrievedData.value.id}`, payload, {
        headers: {
            'Content-Type': 'multipart/form-data'
        }
    }).then(() => {
        actions.resetForm();
        router.push({ path: '/admin/technicians' });
        toastr.success('Técnico editado com sucesso');
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
        <h1 class="h3 mb-3">Técnico</h1>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Técnico: {{ retrievedData.name }}</h5>
                        <router-link to="/admin/technicians" class="btn btn-pill btn-primary mt-3">
                            <vue-feather type="arrow-left"></vue-feather>Voltar
                        </router-link>
                    </div>

                    <div class="card-body">
                        <Form @submit="editFunction" :validation-schema="schema" v-slot="{ errors }">
                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <label class="form-label" for="name">Nome</label>
                                    <Field type="text" class="form-control" :class="{'is-invalid': errors.name}" name="name" v-model="retrievedData.name" id="name" placeholder="Nome"/>
                                    <span class="invalid-feedback">{{ errors.name }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <label class="form-label" for="code">Código</label>
                                    <Field type="text" class="form-control" :class="{'is-invalid': errors.code}" name="code" v-model="retrievedData.code" id="code" placeholder="Código"/>
                                    <span class="invalid-feedback">{{ errors.code }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <label class="form-label" for="document">Documento</label>
                                    <Field type="text" class="form-control" :class="{'is-invalid': errors.document}" name="document" v-model="retrievedData.document" id="document" placeholder="Documento"/>
                                    <span class="invalid-feedback">{{ errors.document }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <label class="form-label" for="mobile_phone">Telefone Celular</label>
                                    <Field type="text" class="form-control" :class="{'is-invalid': errors.mobile_phone}" name="mobile_phone" v-model="retrievedData.mobile_phone" id="mobile_phone" placeholder="Telefone Celular"/>
                                    <span class="invalid-feedback">{{ errors.mobile_phone }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <label class="form-label" for="salary">Salário</label>
                                    <Field type="number" class="form-control" :class="{'is-invalid': errors.salary}" name="salary" v-model="retrievedData.salary" id="salary" placeholder="Salário"/>
                                    <span class="invalid-feedback">{{ errors.salary }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <label class="form-label" for="admission_date">Data de Admissão</label>
                                    <Field type="date" class="form-control" :class="{'is-invalid': errors.admission_date}" name="admission_date" v-model="retrievedData.admission_date" id="admission_date"/>
                                    <span class="invalid-feedback">{{ errors.admission_date }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <label class="form-label" for="department_id">Departamento</label>
                                    <Field as="select" class="form-control" :class="{'is-invalid': errors.department_id}" name="department_id" v-model="retrievedData.department_id" id="department_id">
                                        <option value="" disabled>Selecionar</option>
                                        <option v-for="department in departments" :key="department.id" :value="department.id">{{ department.name }}</option>
                                    </Field>
                                    <span class="invalid-feedback">{{ errors.department_id }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <label class="form-label" for="area_id">Área</label>
                                    <Field as="select" class="form-control" :class="{'is-invalid': errors.area_id}" name="area_id" v-model="retrievedData.area_id" id="area_id">
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
                                        :model-value="getExtraFieldValue(field.key)"
                                        rows="3"
                                    />
                                    <Field
                                        v-else
                                        :type="field.type"
                                        class="form-control"
                                        :class="{'is-invalid': errors[`contract_extra_${field.key}`]}"
                                        :name="`contract_extra_${field.key}`"
                                        :id="`contract_extra_${field.key}`"
                                        :model-value="getExtraFieldValue(field.key)"
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
