<script setup>
import axios from "axios";
import { ref, onMounted, watch } from "vue";
import moment from "moment";
import { useToastr } from "../../../toastr";
import { debounce } from "lodash";
import { Form, Field, FieldArray, useForm } from "vee-validate";
import { useRouter } from "vue-router";
import * as yup from "yup";
import VueFeather from "vue-feather";
import { Bootstrap4Pagination } from "laravel-vue-pagination";

const loading = ref(false);
const loadingDiv = ref(true);
const toastr = useToastr();
const router = useRouter();

let type_equipments = ref([]);
let type_equipment_id_to_equipment = ref(0);
let equipments = ref([]);

const invoicetotal = ref(0);
const iva = ref(0);
const retrievedData = ref({});
const loadingButtonSubmit = ref(false);

function formatOneDecimal(value) {
    if (value === null || value === undefined || value === '') return '';
    return Number(value).toFixed(1);
}

const schema = yup.object({
    start_date_period: yup.date().required("Início do período é obrigatório"),
    end_date_period: yup.date().required("Fim do período é obrigatório"),
    active_energy_consumption: yup.number().required("Obrigatório"),
    active_energy_consumption_cost: yup.number().required("Obrigatório"),
    reactive_energy_consumption: yup.number().required("Obrigatório"),
    reactive_energy_consumption_cost: yup.number().required("Obrigatório"),
    loss: yup.number().required("Obrigatório"),
    loss_cost: yup.number().required("Obrigatório"),
    ponta: yup.number().required("Obrigatório"),
    ponta_cost: yup.number().required("Obrigatório"),
    fix_rate: yup.number().required("Obrigatório"),
    fix_rate_cost: yup.number().required("Obrigatório"),
});

const { setValues, values } = useForm({ validationSchema: schema });

const getData = () => {
  axios
    .get(`/energyinvoice/${router.currentRoute.value.params.id}/edit`)
    .then((response) => {
      loadingDiv.value = false;
      const data = response.data.invoice; // Aqui está o objeto correto
      retrievedData.value = data;

      setValues({
        ...data,
        quotation: (data.energy_invoice_items || []).map(item => ({
          id: item.id, // importante para update no backend
          equipment_id: item.equipment_id,
          apr_consumption: item.apr_consumption,
          // Adicione mais campos aqui se precisar editar outros!
        }))
      });
    })
    .catch(() => {
      loadingDiv.value = false;
    });
};

const editFunction = (formValues, actions) => {
    loadingButtonSubmit.value = true;
    axios
        .patch(`/energyinvoice/${retrievedData.value.id}`, formValues)
        .then(() => {
            actions.resetForm();
            router.push({ path: "/admin/energyinvoice" });
            toastr.success("Registo editado com sucesso");
        })
        .catch((error) => {
            toastr.error("Erro ao editar");
            if (error.response?.data?.errors) {
                actions.setErrors(error.response.data.errors);
            }
        })
        .finally(() => {
            loadingButtonSubmit.value = false;
        });
};

const getAuxiliarData = () => {
    axios
        .get("/auxiliar-create-invoice")
        .then((response) => {
            equipments.value = response.data.equipments;
            loadingDiv.value = false;
        })
        .catch((error) => {
            toastr.error(error);
            router.push({ path: "/admin/fuel" });
        });
};
// Calcula IVA e Total da fatura sempre que valores mudam
watch(
    [
        () => values.active_energy_consumption_cost,
        () => values.reactive_energy_consumption_cost,
        () => values.loss_cost,
        () => values.ponta_cost,
        () => values.fix_rate_cost,
    ],
    () => {
        const subtotal =
            Number(values.active_energy_consumption_cost || 0) +
            Number(values.reactive_energy_consumption_cost || 0) +
            Number(values.loss_cost || 0) +
            Number(values.ponta_cost || 0) +
            Number(values.fix_rate_cost || 0);

        iva.value = (subtotal * 0.62 * 0.16).toFixed(2); // 16% de 62%
        invoicetotal.value = (subtotal + Number(iva.value)).toFixed(2);
    },
    { deep: true }
);

onMounted(() => {
    getAuxiliarData();
    getData();
});
</script>

<template>
    <div v-if="!loadingDiv">
        <h1 class="h3 mb-3">Fatura EDM</h1>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">
                            Formulário de edição de Fatura EDM
                        </h5>
                        <router-link
                            to="/admin/energyinvoice"
                            class="btn btn-pill btn-primary mt-3"
                        >
                            <vue-feather type="arrow-left"></vue-feather> Voltar
                        </router-link>
                    </div>

                    <div class="card-body">
                        <Form
                            @submit="editFunction"
                            :validation-schema="schema"
                            v-slot="{ errors }"
                        >
                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label
                                        class="form-label"
                                        for="start_date_period"
                                        >Inicio Periodo de Faturação</label
                                    >
                                    <Field
                                        type="date"
                                        class="form-control"
                                        :class="{
                                            'is-invalid':
                                                errors.start_date_period,
                                        }"
                                        v-model="
                                            retrievedData.start_date_period
                                        "
                                        name="start_date_period"
                                        id="start_date_period"
                                    />
                                    <span class="invalid-feedback">{{
                                        errors.start_date_period
                                    }}</span>
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label
                                        class="form-label"
                                        for="end_date_period"
                                        >Fim Periodo de Faturação</label
                                    >
                                    <Field
                                        type="date"
                                        class="form-control"
                                        :class="{
                                            'is-invalid':
                                                errors.end_date_period,
                                        }"
                                        v-model="retrievedData.end_date_period"
                                        name="end_date_period"
                                        id="end_date_period"
                                    />
                                    <span class="invalid-feedback">{{
                                        errors.end_date_period
                                    }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label
                                        class="form-label"
                                        for="active_energy_consumption"
                                        >Consumo de Energia Ativa</label
                                    >
                                    <Field
                                        type="number"
                                        class="form-control"
                                        :class="{
                                            'is-invalid':
                                                errors.active_energy_consumption,
                                        }"
                                        v-model="retrievedData.active_energy_consumption"
                                        name="active_energy_consumption"
                                        id="active_energy_consumption"
                                        step="0.1"
                                        @blur="retrievedData.ponta = formatOneDecimal(retrievedData.ponta)"
                                    />
                                    <span class="invalid-feedback">{{
                                        errors.active_energy_consumption
                                    }}</span>
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label
                                        class="form-label"
                                        for="active_energy_consumption_cost"
                                        >Valor Consumo de Energia Ativa</label
                                    >
                                    <Field
                                        type="number"
                                        class="form-control"
                                        :class="{
                                            'is-invalid':
                                                errors.active_energy_consumption_cost,
                                        }"
                                        v-model="
                                            retrievedData.active_energy_consumption_cost
                                        "
                                        name="active_energy_consumption_cost"
                                        id="active_energy_consumption_cost"
                                    />
                                    <span class="invalid-feedback">{{
                                        errors.active_energy_consumption_cost
                                    }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label
                                        class="form-label"
                                        for="reactive_energy_consumption"
                                        >Consumo de Energia Reativa</label
                                    >
                                    <Field
                                        type="number"
                                        class="form-control"
                                        :class="{
                                            'is-invalid':
                                                errors.reactive_energy_consumption,
                                        }"
                                        v-model="
                                            retrievedData.reactive_energy_consumption
                                        "
                                        name="reactive_energy_consumption"
                                        id="reactive_energy_consumption"
                                    />
                                    <span class="invalid-feedback">{{
                                        errors.reactive_energy_consumption
                                    }}</span>
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label
                                        class="form-label"
                                        for="reactive_energy_consumption_cost"
                                        >Valor Consumo de Energia Reativa</label
                                    >
                                    <Field
                                        type="number"
                                        class="form-control"
                                        :class="{
                                            'is-invalid':
                                                errors.reactive_energy_consumption_cost,
                                        }"
                                        v-model="
                                            retrievedData.reactive_energy_consumption_cost
                                        "
                                        name="reactive_energy_consumption_cost"
                                        id="reactive_energy_consumption_cost"
                                    />
                                    <span class="invalid-feedback">{{
                                        errors.reactive_energy_consumption_cost
                                    }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="loss"
                                        >Perda</label
                                    >
                                    <Field
                                        type="number"
                                        class="form-control"
                                        :class="{ 'is-invalid': errors.loss }"
                                        v-model="retrievedData.loss"
                                        name="loss"
                                        id="loss"
                                    />
                                    <span class="invalid-feedback">{{
                                        errors.loss
                                    }}</span>
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="loss_cost"
                                        >Valor Perda</label
                                    >
                                    <Field
                                        type="number"
                                        class="form-control"
                                        :class="{
                                            'is-invalid': errors.loss_cost,
                                        }"
                                        v-model="retrievedData.loss_cost"
                                        name="loss_cost"
                                        id="loss_cost"
                                    />
                                    <span class="invalid-feedback">{{
                                        errors.loss_cost
                                    }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="ponta"
                                        >Ponta</label
                                    >
                                    <Field
                                        type="number"
                                        class="form-control"
                                        :class="{ 'is-invalid': errors.ponta }"
                                        v-model="retrievedData.ponta"
                                        name="ponta"
                                        id="ponta"
                                    />
                                    <span class="invalid-feedback">{{
                                        errors.ponta
                                    }}</span>
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="ponta_cost"
                                        >Valor Ponta</label
                                    >
                                    <Field
                                        type="number"
                                        class="form-control"
                                        :class="{
                                            'is-invalid': errors.ponta_cost,
                                        }"
                                        v-model="retrievedData.ponta_cost"
                                        name="ponta_cost"
                                        id="ponta_cost"
                                    />
                                    <span class="invalid-feedback">{{
                                        errors.ponta_cost
                                    }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="fix_rate"
                                        >Taxa Fixa</label
                                    >
                                    <Field
                                        type="number"
                                        class="form-control"
                                        :class="{
                                            'is-invalid': errors.fix_rate,
                                        }"
                                        v-model="retrievedData.fix_rate"
                                        name="fix_rate"
                                        id="fix_rate"
                                    />
                                    <span class="invalid-feedback">{{
                                        errors.fix_rate
                                    }}</span>
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label
                                        class="form-label"
                                        for="fix_rate_cost"
                                        >Valor Taxa Fixa</label
                                    >
                                    <Field
                                        type="number"
                                        class="form-control"
                                        :class="{
                                            'is-invalid': errors.fix_rate_cost,
                                        }"
                                        v-model="retrievedData.fix_rate_cost"
                                        name="fix_rate_cost"
                                        id="fix_rate_cost"
                                    />
                                    <span class="invalid-feedback">{{
                                        errors.fix_rate_cost
                                    }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="iva"
                                        >IVA(16% de 62%)</label
                                    >
                                    <input
                                        type="number"
                                        class="form-control"
                                        :value="iva"
                                        readonly
                                    />
                                    <span class="invalid-feedback">{{
                                        errors.iva
                                    }}</span>
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label class="form-label" for="invoicetotal"
                                        >Total Fatura</label
                                    >
                                    <input
                                        type="number"
                                        class="form-control"
                                        :value="invoicetotal"
                                        readonly
                                    />
                                    <span class="invalid-feedback">{{
                                        errors.invoicetotal
                                    }}</span>
                                </div>
                            </div>
                          
                            <div class="row">
                              

                                    <FieldArray class="form-control" name="quotation">
                                                            <fieldset class="InputGroup" v-for="(item, idx) in retrievedData.energy_invoice_items" :key="item.id">
                                                                <div class="row">
                                                                    <div class="mb-3 col-md-3">
                                                                        <label class="form-label">Ativo:</label>
                                                                        <span class="form-control">{{ item.equipment.name }}</span>
                                                                    </div>
                                                                    <div class="mb-3 col-md-3">
                                                                        <label class="form-label">Consumo:</label>
                                                                        <Field type="number" class="form-control" :class="{'is-invalid':errors.apr_consumption}" :name="`quotation[${idx}].apr_consumption`" v-model="item.apr_consumption" id="apr_consumption" placeholder="Quantidade Produto"/>
                                                                        <span class="invalid-feedback">{{ errors.apr_consumption }}</span>
                                                                    </div>
                                                                   
                                                                    <Field type="hidden" class="form-control" :name="`quotation[${idx}].equipment_id`" readonly v-model="item.equipment_id"/>

                                                                    
                                                                </div>
                                                            </fieldset>
                                                        </FieldArray>
                                  
                             
                            </div>

                            <button
                                type="submit"
                                class="btn btn-primary"
                                :disabled="loading"
                            >
                                <div
                                    v-if="loading"
                                    class="spinner-border spinner-border-sm"
                                    role="status"
                                ></div>
                                <span v-else>Submeter</span>
                            </button>
                        </Form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading -->
    <div v-else>
        <div class="card">
            <div class="card-body text-center">
                <div class="spinner-border" role="status"></div>
                <br /><br />
                Carregando Dados...
            </div>
        </div>
    </div>
</template>
