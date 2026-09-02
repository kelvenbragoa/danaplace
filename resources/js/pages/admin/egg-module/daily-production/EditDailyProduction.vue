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
    total_eggs: 0,
    cracked_eggs: 0,
    dirty_eggs: 0,
    deformed_eggs: 0,
});

const schema = yup.object({
    flock_id: yup.string().required('Selecione o lote'),
    date: yup.string().required(),
    total_eggs: yup.number().required().min(0),
    cracked_eggs: yup.number().min(0).nullable(),
    dirty_eggs: yup.number().min(0).nullable(),
    deformed_eggs: yup.number().min(0).nullable(),
    normal_eggs: yup.number().min(0).nullable(),
    grande_eggs: yup.number().min(0).nullable(),
    jumbo_eggs: yup.number().min(0).nullable(),
    feed_consumption_kg: yup.number().min(0).nullable(),
    water_consumption_liters: yup.number().min(0).nullable(),
    light_hours: yup.number().min(0).nullable(),
    observations: yup.string().nullable(),
});

const cleanEggsPreview = computed(() => {
    return Math.max(
        0,
        Number(formValues.value.total_eggs || 0)
        - Number(formValues.value.cracked_eggs || 0)
        - Number(formValues.value.dirty_eggs || 0)
        - Number(formValues.value.deformed_eggs || 0)
    );
});

const getData = () => {
    Promise.all([
        axios.get(`/admin/daily-production/${router.currentRoute.value.params.id}`),
        axios.get('/admin/flocks-all'),
    ]).then(([productionResponse, flocksResponse]) => {
        loadingDiv.value = false;
        retrievedData.value = productionResponse.data;
        flocks.value = flocksResponse.data;
        formValues.value = {
            total_eggs: productionResponse.data.total_eggs || 0,
            cracked_eggs: productionResponse.data.cracked_eggs || 0,
            dirty_eggs: productionResponse.data.dirty_eggs || 0,
            deformed_eggs: productionResponse.data.deformed_eggs || 0,
        };
    }).catch(() => {
        loadingDiv.value = false;
    });
};

const updatePreview = (values) => {
    formValues.value = {
        total_eggs: values.total_eggs ?? retrievedData.value.total_eggs,
        cracked_eggs: values.cracked_eggs ?? retrievedData.value.cracked_eggs,
        dirty_eggs: values.dirty_eggs ?? retrievedData.value.dirty_eggs,
        deformed_eggs: values.deformed_eggs ?? retrievedData.value.deformed_eggs,
    };
};

const applyFlockDefaults = (flockId) => {
    const flock = flocks.value.find((item) => String(item.id) === String(flockId));
    if (!flock) return;

    retrievedData.value.feed_consumption_kg = Number(flock.daily_feed_consumption_kg || 0);
    retrievedData.value.water_consumption_liters = Number(flock.daily_water_consumption_liters || 0);
    retrievedData.value.light_hours = Number(flock.daily_light_hours || 0);
};

const editFunction = (values, actions) => {
    loadingButtonSubmit.value = true;

    const payload = {
        ...values,
        total_eggs: Number(values.total_eggs),
        cracked_eggs: Number(values.cracked_eggs || 0),
        dirty_eggs: Number(values.dirty_eggs || 0),
        deformed_eggs: Number(values.deformed_eggs || 0),
        normal_eggs: Number(values.normal_eggs || 0),
        grande_eggs: Number(values.grande_eggs || 0),
        jumbo_eggs: Number(values.jumbo_eggs || 0),
        feed_consumption_kg: Number(values.feed_consumption_kg || 0),
        water_consumption_liters: Number(values.water_consumption_liters || 0),
        light_hours: Number(values.light_hours || 0),
    };

    axios.patch(`/admin/daily-production/${retrievedData.value.id}`, payload).then(() => {
        router.push({ path: '/admin/producao-diaria' });
        toastr.success('Produção editada com sucesso');
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
        <h1 class="h3 mb-3">Produção Diária</h1>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Editar produção: {{ retrievedData.date }}</h5>
                        <router-link to="/admin/producao-diaria" class="btn btn-pill btn-primary mt-3">
                            <vue-feather type="arrow-left"></vue-feather>Voltar
                        </router-link>
                    </div>

                    <div class="card-body">
                        <Form @submit="editFunction" :validation-schema="schema" v-slot="{ errors, values }">
                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="flock_id">Lote</label>
                                    <Field as="select" class="form-control" :class="{'is-invalid': errors.flock_id}" name="flock_id" id="flock_id" v-model="retrievedData.flock_id" @change="applyFlockDefaults($event.target.value)">
                                        <option value="" disabled>Selecionar lote</option>
                                        <option v-for="flock in flocks" :key="flock.id" :value="flock.id">
                                            {{ flock.code }} - {{ flock.house?.name }}
                                        </option>
                                    </Field>
                                    <span class="invalid-feedback">{{ errors.flock_id }}</span>
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="date">Data</label>
                                    <Field type="date" class="form-control" :class="{'is-invalid': errors.date}" name="date" v-model="retrievedData.date" id="date"/>
                                    <span class="invalid-feedback">{{ errors.date }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-3">
                                    <label class="form-label" for="total_eggs">Total de Ovos</label>
                                    <Field type="number" class="form-control" :class="{'is-invalid': errors.total_eggs}" name="total_eggs" v-model="retrievedData.total_eggs" id="total_eggs" min="0" @input="updatePreview(values)"/>
                                    <span class="invalid-feedback">{{ errors.total_eggs }}</span>
                                </div>
                                <div class="mb-3 col-md-3">
                                    <label class="form-label" for="cracked_eggs">Rachados</label>
                                    <Field type="number" class="form-control" :class="{'is-invalid': errors.cracked_eggs}" name="cracked_eggs" v-model="retrievedData.cracked_eggs" id="cracked_eggs" min="0" @input="updatePreview(values)"/>
                                    <span class="invalid-feedback">{{ errors.cracked_eggs }}</span>
                                </div>
                                <div class="mb-3 col-md-3">
                                    <label class="form-label" for="dirty_eggs">Sujos</label>
                                    <Field type="number" class="form-control" :class="{'is-invalid': errors.dirty_eggs}" name="dirty_eggs" v-model="retrievedData.dirty_eggs" id="dirty_eggs" min="0" @input="updatePreview(values)"/>
                                    <span class="invalid-feedback">{{ errors.dirty_eggs }}</span>
                                </div>
                                <div class="mb-3 col-md-3">
                                    <label class="form-label" for="deformed_eggs">Deformados</label>
                                    <Field type="number" class="form-control" :class="{'is-invalid': errors.deformed_eggs}" name="deformed_eggs" v-model="retrievedData.deformed_eggs" id="deformed_eggs" min="0" @input="updatePreview(values)"/>
                                    <span class="invalid-feedback">{{ errors.deformed_eggs }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <p class="text-muted mb-2"><strong>Partidos por tamanho</strong> (Normal, Grande, Jumbo)</p>
                                </div>
                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="normal_eggs">Normal</label>
                                    <Field type="number" class="form-control" :class="{'is-invalid': errors.normal_eggs}" name="normal_eggs" v-model="retrievedData.normal_eggs" id="normal_eggs" min="0"/>
                                    <span class="invalid-feedback">{{ errors.normal_eggs }}</span>
                                </div>
                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="grande_eggs">Grande</label>
                                    <Field type="number" class="form-control" :class="{'is-invalid': errors.grande_eggs}" name="grande_eggs" v-model="retrievedData.grande_eggs" id="grande_eggs" min="0"/>
                                    <span class="invalid-feedback">{{ errors.grande_eggs }}</span>
                                </div>
                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="jumbo_eggs">Jumbo</label>
                                    <Field type="number" class="form-control" :class="{'is-invalid': errors.jumbo_eggs}" name="jumbo_eggs" v-model="retrievedData.jumbo_eggs" id="jumbo_eggs" min="0"/>
                                    <span class="invalid-feedback">{{ errors.jumbo_eggs }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <p class="text-muted mb-0">Ovos limpos (calculado): <strong>{{ cleanEggsPreview }}</strong></p>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <p class="text-muted mb-2">Ao alterar o lote, os consumos são actualizados com os valores diários do lote.</p>
                                </div>
                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="feed_consumption_kg">Ração (kg)</label>
                                    <Field type="number" step="0.01" class="form-control" :class="{'is-invalid': errors.feed_consumption_kg}" name="feed_consumption_kg" v-model="retrievedData.feed_consumption_kg" id="feed_consumption_kg" min="0"/>
                                    <span class="invalid-feedback">{{ errors.feed_consumption_kg }}</span>
                                </div>
                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="water_consumption_liters">Água (L)</label>
                                    <Field type="number" step="0.01" class="form-control" :class="{'is-invalid': errors.water_consumption_liters}" name="water_consumption_liters" v-model="retrievedData.water_consumption_liters" id="water_consumption_liters" min="0"/>
                                    <span class="invalid-feedback">{{ errors.water_consumption_liters }}</span>
                                </div>
                                <div class="mb-3 col-md-4">
                                    <label class="form-label" for="light_hours">Horas de Luz</label>
                                    <Field type="number" step="0.01" class="form-control" :class="{'is-invalid': errors.light_hours}" name="light_hours" v-model="retrievedData.light_hours" id="light_hours" min="0"/>
                                    <span class="invalid-feedback">{{ errors.light_hours }}</span>
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
