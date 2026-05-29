<script setup>

import {onMounted, ref, reactive,watch} from 'vue';
import axios from 'axios';
import {useToastr} from '../../../toastr';
import {Form, Field, FieldArray} from 'vee-validate';
import * as yup from 'yup';
import { useRouter } from "vue-router";
import moment from 'moment'
import {debounce} from 'lodash';
import { Bootstrap4Pagination } from 'laravel-vue-pagination';
import VueFeather from 'vue-feather';

const loading = ref(false);
const toastr = useToastr();
const loadingDiv = ref(true);
let currentvalue = ref([]);
let type_equipments =ref([]);
let type_equipment_id_to_equipment = ref(0);
let equipments =ref([]);

const invoicetotal = ref(0);
const iva = ref(0);


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
//   iva: yup.number().required("IVA é obrigatório"),
//   invoicetotal: yup.number().required("Total da fatura é obrigatório"),

 
});
let self = this;
const router = useRouter();

const createRecordFunction = (values, actions) => {

 
    currentvalue.value = {values};

    loading.value = true;

    const arr = Array.from(values)
    
    axios.post('/energyinvoice',values).then((response)=>{

    // categories.value.unshift(response.data);
    // $('#createCategory').modal('hide');
    actions.resetForm();
    router.push({ path: '/admin/energyinvoice' });
    toastr.success('Registro criada com sucesso');
  }).catch((error)=>{
    
    loading.value = false;
    toastr.error('Erro ao adicionar. '+error.response.data.message);
    if(error.response.data.errors){
       
        actions.setErrors(error.response.data.errors);
    }
  }).finally(()=>{
    loading.value = false;
    
  })



};

const getAuxiliarData = () => {

axios.get('/auxiliar-create-invoice')
     .then((response)=>{

      equipments.value = response.data.equipments;
      loadingDiv.value=false;

     


     })
     .catch((error)=>{
      toastr.error(error);
      router.push({ path: '/admin/fuel' });
     })
}

const getEquipment = (typeequipment) => {

axios.get(`/auxiliar-create-mcscr/${typeequipment}`)
   .then((response)=>{

    equipments.value = response.data.equipments;
   })
   .catch((error)=>{
    toastr.error(error);
    router.push({ path: '/admin/fuel' });
   })


}

onMounted(()=>{
    getAuxiliarData()
})



</script>

<template>
    <div v-if="!loadingDiv">
        <h1 class="h3 mb-3">Fatura EDM</h1>
        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Formulário criação de Fatura EDM do sistema.</h5>

                                        <router-link to="/admin/energyinvoice" class="btn btn-pill btn-primary mt-3"><vue-feather type="arrow-left"></vue-feather>Voltar</router-link> 

                                       
								    </div>
                                    
                                    <div class="card-body">
                                            <Form @submit="createRecordFunction" :validation-schema="schema" v-slot="{ errors }">

												<div class="row">
													<div class="mb-3 col-md-6">
														<label class="form-label" for="start_date_period">Inicio Periodo de Faturação</label>
														<Field type="date" class="form-control" :class="{'is-invalid':errors.start_date_period}" name="start_date_period" id="start_date_period"/>
                                                        <span class="invalid-feedback">{{ errors.start_date_period }}</span>
													</div>
                                                    <div class="mb-3 col-md-6">
														<label class="form-label" for="end_date_period">Fim Periodo de Faturação</label>
														<Field type="date" class="form-control" :class="{'is-invalid':errors.end_date_period}" name="end_date_period" id="end_date_period"/>
                                                        <span class="invalid-feedback">{{ errors.end_date_period }}</span>
													</div>
												</div>

                                                <div class="row">
													<div class="mb-3 col-md-6">
														<label class="form-label" for="active_energy_consumption">Consumo de Energia Ativa</label>
														<Field type="number" class="form-control" :class="{'is-invalid':errors.active_energy_consumption}" name="active_energy_consumption" id="active_energy_consumption"/>
                                                        <span class="invalid-feedback">{{ errors.active_energy_consumption }}</span>
													</div>
                                                    <div class="mb-3 col-md-6">
														<label class="form-label" for="active_energy_consumption_cost">Valor Consumo de Energia Ativa</label>
														<Field type="number" class="form-control" :class="{'is-invalid':errors.active_energy_consumption_cost}" name="active_energy_consumption_cost" id="active_energy_consumption_cost"/>
                                                        <span class="invalid-feedback">{{ errors.active_energy_consumption_cost }}</span>
													</div>
												</div>

                                                <div class="row">
													<div class="mb-3 col-md-6">
														<label class="form-label" for="reactive_energy_consumption">Consumo de Energia Reativa</label>
														<Field type="number" class="form-control" :class="{'is-invalid':errors.reactive_energy_consumption}" name="reactive_energy_consumption" id="reactive_energy_consumption"/>
                                                        <span class="invalid-feedback">{{ errors.reactive_energy_consumption }}</span>
													</div>
                                                    <div class="mb-3 col-md-6">
														<label class="form-label" for="reactive_energy_consumption_cost">Valor Consumo de Energia Reativa</label>
														<Field type="number" class="form-control" :class="{'is-invalid':errors.reactive_energy_consumption_cost}" name="reactive_energy_consumption_cost" id="reactive_energy_consumption_cost"/>
                                                        <span class="invalid-feedback">{{ errors.reactive_energy_consumption_cost }}</span>
													</div>
												</div>

                                                <div class="row">
													<div class="mb-3 col-md-6">
														<label class="form-label" for="loss">Perda</label>
														<Field type="number" class="form-control" :class="{'is-invalid':errors.loss}" name="loss" id="loss"/>
                                                        <span class="invalid-feedback">{{ errors.loss }}</span>
													</div>
                                                    <div class="mb-3 col-md-6">
														<label class="form-label" for="loss_cost">Valor Perda</label>
														<Field type="number" class="form-control" :class="{'is-invalid':errors.loss_cost}" name="loss_cost" id="loss_cost"/>
                                                        <span class="invalid-feedback">{{ errors.loss_cost }}</span>
													</div>
												</div>

                                                <div class="row">
													<div class="mb-3 col-md-6">
														<label class="form-label" for="ponta">Ponta</label>
														<Field type="number" class="form-control" :class="{'is-invalid':errors.ponta}" name="ponta" id="ponta"/>
                                                        <span class="invalid-feedback">{{ errors.ponta }}</span>
													</div>
                                                    <div class="mb-3 col-md-6">
														<label class="form-label" for="ponta_cost">Valor Ponta</label>
														<Field type="number" class="form-control" :class="{'is-invalid':errors.ponta_cost}" name="ponta_cost" id="ponta_cost"/>
                                                        <span class="invalid-feedback">{{ errors.ponta_cost }}</span>
													</div>
												</div>

                                                <div class="row">
													<div class="mb-3 col-md-6">
														<label class="form-label" for="fix_rate">Taxa Fixa</label>
														<Field type="number" class="form-control" :class="{'is-invalid':errors.fix_rate}" name="fix_rate" id="fix_rate"/>
                                                        <span class="invalid-feedback">{{ errors.fix_rate }}</span>
													</div>
                                                    <div class="mb-3 col-md-6">
														<label class="form-label" for="fix_rate_cost">Valor Taxa Fixa</label>
														<Field type="number" class="form-control" :class="{'is-invalid':errors.fix_rate_cost}" name="fix_rate_cost" id="fix_rate_cost"/>
                                                        <span class="invalid-feedback">{{ errors.fix_rate_cost }}</span>
													</div>
												</div>

                                                <div class="row">
													<div class="mb-3 col-md-6">
														<label class="form-label" for="iva">IVA(16% de 62%)</label>
														<input type="number" class="form-control" :value="iva" readonly/>
                                                        <span class="invalid-feedback">{{ errors.iva }}</span>
													</div>
                                                    <div class="mb-3 col-md-6">
														<label class="form-label" for="invoicetotal">Total Fatura</label>
														<input type="number" class="form-control" :value="invoicetotal" readonly/>
                                                        <span class="invalid-feedback">{{ errors.invoicetotal }}</span>
													</div>
												</div>
                                                <div class="row">
                                                    <FieldArray class="form-control" name="quotation" v-slot="{ fields, push, remove }">
                                                        <div class="card-body">
                                                            <button type="button" class="btn btn-pill btn-info mt-2" @click="push({ product_name: '' })">Adicionar Linha +</button>
                                                        </div>
                                                        <fieldset class="InputGroup" v-for="(field, idx) in fields" :key="field.key">
                                                                <div class="row">
                                                                    <div class="mb-3 col-md-3">
                                                                        <label class="form-label">Ativo:</label>
                                                                        <Field as="select" class="form-control" :class="{'is-invalid':errors.equipment_id}"  :name="`quotation[${idx}].equipment_id`" id="equipment_id" aria-describedby="equipment_id">
                                                                            <option value="" selected>Selecionar</option>
                                                                            <option v-for="equipment in equipments" :key="equipment.id" :value="equipment.id">{{ equipment.name }} - {{ equipment.ref }} / {{ equipment.destination.name }}</option>

                                                                        </Field>                                                                        <span class="invalid-feedback">{{ errors.product_name }}</span>
                                                                    </div>
                                                                    <div class="mb-3 col-md-2">
                                                                        <label class="form-label">Consumo:</label>
                                                                        <Field type="number" class="form-control" :class="{'is-invalid':errors.apr_consumption}" :name="`quotation[${idx}].apr_consumption`" id="apr_consumption" placeholder="Consumo"/>
                                                                        <span class="invalid-feedback">{{ errors.apr_consumption }}</span>
                                                                    </div>
                                                                    <!-- <div class="mb-3 col-md-2">
                                                                        <label class="form-label">Custo:</label>
                                                                        <Field type="number" class="form-control" :class="{'is-invalid':errors.cost}" :name="`quotation[${idx}].cost`" id="cost" placeholder="Preço Unitário"/>
                                                                        <span class="invalid-feedback">{{ errors.cost }}</span>
                                                                    </div>  -->
                                                        
                                                                    <div class="col-sm-3">
                                                                        <button type="button" class="btn btn-pill btn-danger mt-4" @click="remove(idx>1 ? idx : 0 )">X</button>
                                                                    </div>
                                                                </div>
                                                        </fieldset>  
                                                    </FieldArray>
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