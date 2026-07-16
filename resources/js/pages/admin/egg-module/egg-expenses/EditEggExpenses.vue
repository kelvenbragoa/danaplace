<script setup>

import { computed, onMounted, ref, watch } from 'vue';
import axios from 'axios';
import { useToastr } from '../../../../toastr';
import { Form, Field } from 'vee-validate';
import * as yup from 'yup';
import { useRouter } from 'vue-router';
import VueFeather from 'vue-feather';
import moment from 'moment';

const retrievedData = ref({});
const loadingButtonSubmit = ref(false);
const loadingDiv = ref(true);
const toastr = useToastr();
const router = useRouter();

const categories = ref({});
const paymentMethods = ref({});
const farms = ref([]);
const houses = ref([]);
const flocks = ref([]);
const selectedFarmId = ref('');
const selectedHouseId = ref('');

const schema = yup.object({
    title: yup.string().required('Título é obrigatório'),
    description: yup.string().nullable(),
    amount: yup.number().required('Valor é obrigatório').min(0.01, 'Valor mínimo 0.01'),
    expense_date: yup.string().required('Data é obrigatória'),
    category: yup.string().required('Categoria é obrigatória'),
    farm_id: yup.mixed().nullable(),
    house_id: yup.mixed().nullable(),
    flock_id: yup.mixed().nullable(),
    vendor_name: yup.string().nullable(),
    invoice_number: yup.string().nullable(),
    payment_method: yup.string().nullable(),
    notes: yup.string().nullable(),
});

const filteredHouses = computed(() => {
    if (!selectedFarmId.value) return houses.value;
    return houses.value.filter(h => String(h.farm_id) === String(selectedFarmId.value));
});

const filteredFlocks = computed(() => {
    if (selectedHouseId.value) {
        return flocks.value.filter(f => String(f.house_id) === String(selectedHouseId.value));
    }
    if (selectedFarmId.value) {
        return flocks.value.filter(f => String(f.house?.farm_id) === String(selectedFarmId.value)
            || String(f.house?.farm?.id) === String(selectedFarmId.value));
    }
    return flocks.value;
});

const toNullableId = (value) => {
    if (value === '' || value === null || value === undefined) return null;
    return Number(value);
};

const formatDateInput = (value) => {
    if (!value) return '';
    return moment(value).format('YYYY-MM-DD');
};

const getData = async () => {
    try {
        const [metaRes, farmsRes, housesRes, flocksRes, expenseRes] = await Promise.all([
            axios.get('/admin/egg-expenses/meta'),
            axios.get('/admin/farms-all'),
            axios.get('/admin/houses-all'),
            axios.get('/admin/flocks-active'),
            axios.get(`/admin/egg-expenses/${router.currentRoute.value.params.id}`),
        ]);

        categories.value = metaRes.data.categories || {};
        paymentMethods.value = metaRes.data.payment_methods || {};
        farms.value = farmsRes.data;
        houses.value = housesRes.data;
        flocks.value = flocksRes.data;

        const expense = expenseRes.data;
        expense.expense_date = formatDateInput(expense.expense_date);
        expense.farm_id = expense.farm_id ?? '';
        expense.house_id = expense.house_id ?? '';
        expense.flock_id = expense.flock_id ?? '';
        expense.payment_method = expense.payment_method ?? '';
        expense.description = expense.description ?? '';
        expense.vendor_name = expense.vendor_name ?? '';
        expense.invoice_number = expense.invoice_number ?? '';
        expense.notes = expense.notes ?? '';

        retrievedData.value = expense;
        selectedFarmId.value = expense.farm_id || '';
        selectedHouseId.value = expense.house_id || '';
        loadingDiv.value = false;
    } catch (e) {
        loadingDiv.value = false;
        toastr.error('Erro ao carregar despesa');
        router.push({ path: '/admin/despesas-ovos' });
    }
};

const editFunction = (values, actions) => {
    loadingButtonSubmit.value = true;

    const payload = {
        ...values,
        amount: Number(values.amount),
        farm_id: toNullableId(selectedFarmId.value),
        house_id: toNullableId(selectedHouseId.value),
        flock_id: toNullableId(values.flock_id),
        payment_method: values.payment_method || null,
    };

    axios.patch(`/admin/egg-expenses/${retrievedData.value.id}`, payload).then(() => {
        router.push({ path: '/admin/despesas-ovos' });
        toastr.success('Despesa editada com sucesso');
    }).catch((error) => {
        toastr.error('Erro ao editar');
        if (error.response?.data?.errors) {
            actions.setErrors(error.response.data.errors);
        }
    }).finally(() => {
        loadingButtonSubmit.value = false;
    });
};

watch(selectedFarmId, (newVal, oldVal) => {
    if (oldVal !== undefined && String(newVal) !== String(oldVal) && loadingDiv.value === false) {
        // only clear house when user changes farm after initial load
        if (String(retrievedData.value.farm_id) !== String(newVal)) {
            selectedHouseId.value = '';
            retrievedData.value.flock_id = '';
        }
    }
});

onMounted(() => {
    getData();
});

</script>

<template>
    <div v-if="!loadingDiv">
        <h1 class="h3 mb-3">Despesa de Ovos</h1>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Despesa: {{ retrievedData.title }}</h5>
                        <router-link to="/admin/despesas-ovos" class="btn btn-pill btn-primary mt-3">
                            <vue-feather type="arrow-left"></vue-feather>Voltar
                        </router-link>
                    </div>

                    <div class="card-body">
                        <Form @submit="editFunction" :validation-schema="schema" v-slot="{ errors }" :initial-values="retrievedData">
                            <div class="row">
                                <div class="mb-3 col-md-8">
                                    <label class="form-label" for="title">Título</label>
                                    <Field type="text" class="form-control" :class="{'is-invalid': errors.title}" name="title" v-model="retrievedData.title" id="title"/>
                                    <span class="invalid-feedback">{{ errors.title }}</span>
                                </div>
                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="expense_date">Data</label>
                                    <Field type="date" class="form-control" :class="{'is-invalid': errors.expense_date}" name="expense_date" v-model="retrievedData.expense_date" id="expense_date"/>
                                    <span class="invalid-feedback">{{ errors.expense_date }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="category">Categoria</label>
                                    <Field as="select" class="form-control" :class="{'is-invalid': errors.category}" name="category" v-model="retrievedData.category" id="category">
                                        <option v-for="(label, key) in categories" :key="key" :value="key">{{ label }}</option>
                                    </Field>
                                    <span class="invalid-feedback">{{ errors.category }}</span>
                                </div>
                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="amount">Valor</label>
                                    <Field type="number" step="0.01" min="0.01" class="form-control" :class="{'is-invalid': errors.amount}" name="amount" v-model="retrievedData.amount" id="amount"/>
                                    <span class="invalid-feedback">{{ errors.amount }}</span>
                                </div>
                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="payment_method">Método de pagamento</label>
                                    <Field as="select" class="form-control" :class="{'is-invalid': errors.payment_method}" name="payment_method" v-model="retrievedData.payment_method" id="payment_method">
                                        <option value="">—</option>
                                        <option v-for="(label, key) in paymentMethods" :key="key" :value="key">{{ label }}</option>
                                    </Field>
                                    <span class="invalid-feedback">{{ errors.payment_method }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="farm_id">Granja</label>
                                    <select class="form-control" id="farm_id" v-model="selectedFarmId">
                                        <option value="">— Opcional —</option>
                                        <option v-for="farm in farms" :key="farm.id" :value="farm.id">{{ farm.name }}</option>
                                    </select>
                                </div>
                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="house_id">Galpão</label>
                                    <select class="form-control" id="house_id" v-model="selectedHouseId">
                                        <option value="">— Opcional —</option>
                                        <option v-for="house in filteredHouses" :key="house.id" :value="house.id">{{ house.name }}</option>
                                    </select>
                                </div>
                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="flock_id">Lote</label>
                                    <Field as="select" class="form-control" :class="{'is-invalid': errors.flock_id}" name="flock_id" v-model="retrievedData.flock_id" id="flock_id">
                                        <option value="">— Opcional —</option>
                                        <option v-for="flock in filteredFlocks" :key="flock.id" :value="flock.id">
                                            {{ flock.code }} — {{ flock.house?.name || '' }}
                                        </option>
                                    </Field>
                                    <span class="invalid-feedback">{{ errors.flock_id }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="vendor_name">Fornecedor</label>
                                    <Field type="text" class="form-control" :class="{'is-invalid': errors.vendor_name}" name="vendor_name" v-model="retrievedData.vendor_name" id="vendor_name"/>
                                    <span class="invalid-feedback">{{ errors.vendor_name }}</span>
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="invoice_number">Nº Fatura / Referência</label>
                                    <Field type="text" class="form-control" :class="{'is-invalid': errors.invoice_number}" name="invoice_number" v-model="retrievedData.invoice_number" id="invoice_number"/>
                                    <span class="invalid-feedback">{{ errors.invoice_number }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <label class="form-label" for="description">Descrição</label>
                                    <Field as="textarea" class="form-control" :class="{'is-invalid': errors.description}" name="description" v-model="retrievedData.description" id="description" rows="2"/>
                                    <span class="invalid-feedback">{{ errors.description }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <label class="form-label" for="notes">Notas</label>
                                    <Field as="textarea" class="form-control" :class="{'is-invalid': errors.notes}" name="notes" v-model="retrievedData.notes" id="notes" rows="2"/>
                                    <span class="invalid-feedback">{{ errors.notes }}</span>
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
