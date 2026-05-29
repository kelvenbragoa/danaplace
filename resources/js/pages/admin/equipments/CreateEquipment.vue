<script setup>

import {onMounted, ref, reactive,watch} from 'vue';
import axios from 'axios';
import {useToastr} from '../../../toastr';
import {Form, Field} from 'vee-validate';
import * as yup from 'yup';
import { useRouter } from "vue-router";
import moment from 'moment'
import {debounce} from 'lodash';
import { Bootstrap4Pagination } from 'laravel-vue-pagination';
import VueFeather from 'vue-feather';
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.min.css'

const loading = ref(false);
const toastr = useToastr();
const loadingDiv = ref(false);
let currentvalue = ref([]);
let fuel = 0;
let criticals =ref([]);
let type_equipments =ref([]);
let equipmentstatuses =ref([]);
let destinations =ref([]);
let areas =ref([]);
let suppliers =ref([]);
let centercosts =ref([]);
let centercostaccounts =ref([]);
let acquisitions =ref([]);
let distance_controls = ref([]);
let load_unities = ref([]);
let coins = ref([]);
let center_cost_id_to_city = ref(0);
let categories = ref([]);
let fees = ref([]);
const selectedFees = ref([]);


const schema = yup.object({

  name: yup.string().required(),
  ref: yup.string().required(),
  make: yup.string().required(),
  model: yup.string().required(),
  serial: yup.string().required(),
  chassis: yup.string().required(),
  year: yup.string().required(),
  buy_year: yup.string().required(),
  type_equipment_id: yup.string().required(),
  destination_id: yup.string().required(),
  area_id: yup.string().required(),
  supplier_id: yup.string().required(),
  acquisition_id: yup.string().required(),
  equipment_category_id: yup.string().required(),

  center_cost_id: yup.string(),
  center_cost_account_id: yup.string(),
  gps_tracking_id: yup.string(),
  criticaly_id: yup.string().required(),
  equipment_status_id: yup.string().required(),
  distance_control_id: yup.string().required(),
  coin_id: yup.string().required(),
  load_unity_id: yup.string().required(),
  load_max: yup.string().required(),
  amount: yup.string().required(),
  is_commissioned: yup.string().required(),
  is_building: yup.string().required(),
//   fee_ids: yup.array().min(1, 'Selecione pelo menos uma taxa').required('Taxas são obrigatórias'),

  
 
});
let self = this;
const router = useRouter();

const getAuxiliarData = () => {

axios.get('/auxiliar-create-equipments')
     .then((response)=>{

      criticals.value = response.data.criticalies;
      type_equipments.value = response.data.type_equipments;
      equipmentstatuses.value = response.data.equipment_statuses;
      destinations.value = response.data.destinations;
      areas.value = response.data.areas;
      suppliers.value = response.data.suppliers;
      centercosts.value = response.data.center_costs;
    //centercostaccounts.value = response.data.center_cost_accounts;
      acquisitions.value = response.data.acquisitions;
      categories.value = response.data.categories
      distance_controls.value = response.data.distance_controls;
      load_unities.value = response.data.load_unities;
      coins.value = response.data.coins;
      fees.value = response.data.fees;
      loadingDiv.value=false;

     })
     .catch((error)=>{
      toastr.error(error);
      router.push({ path: '/admin/equipments' });
     })
}

const getAccount = (centercost) => {

  axios.get(`/auxiliar-create-equipments/${centercost}`)
     .then((response)=>{

        centercostaccounts.value = response.data.accounts;
     })
     .catch((error)=>{
      toastr.error(error);
      router.push({ path: '/admin/equipments' });
     })


}

const createRecordFunction = (values, actions) => {
    values.fee_ids = selectedFees.value.map(fee => fee.id);
    currentvalue.value = {values};

    loading.value = true;

    const arr = Array.from(values)
    
    axios.post('/equipments',values).then((response)=>{

    // categories.value.unshift(response.data);
    // $('#createCategory').modal('hide');
    actions.resetForm();
    router.push({ path: '/admin/equipments' });
    toastr.success('Equipamento/Ativo criado com sucesso');
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


onMounted(()=>{
    getAuxiliarData()
})




</script>

<template>
    <div v-if="!loadingDiv">
        <h1 class="h3 mb-3">Equipamentos/Ativos</h1>
        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Formulário criação das equipamentos do sistema.</h5>
                                        <h5 class="card-title">Campos marcados com * são obrigatórios.</h5>

                                        <router-link to="/admin/equipments" class="btn btn-pill btn-primary mt-3"><vue-feather type="arrow-left"></vue-feather>Voltar</router-link> 

                                       
								    </div>
                                    
                                    <div class="card-body">
                                            <Form @submit="createRecordFunction" :validation-schema="schema" v-slot="{ errors }">
												<div class="row">
													<div class="mb-3 col-md-6">
														<label class="form-label" for="name">Nome *</label>
														<Field type="text" class="form-control" :class="{'is-invalid':errors.name}" name="name" id="name" placeholder="Nome do equipamento/ativo"/>
                                                        <span class="invalid-feedback">{{ errors.name }}</span>
													</div>
													<div class="mb-3 col-md-6">
														<label class="form-label" for="ref">Referência *</label>
														<Field type="text" class="form-control" :class="{'is-invalid':errors.ref}" name="ref" id="ref" placeholder="Referência"/>
                                                        <span class="invalid-feedback">{{ errors.ref }}</span>
													</div>
												</div>
                                                <div class="row">
													<div class="mb-3 col-md-6">
														<label class="form-label" for="make">Marca *</label>
														<Field type="text" class="form-control" :class="{'is-invalid':errors.make}" name="make" id="make" placeholder="Marca"/>
                                                        <span class="invalid-feedback">{{ errors.make }}</span>
													</div>
													<div class="mb-3 col-md-6">
														<label class="form-label" for="model">Modelo *</label>
														<Field type="text" class="form-control" :class="{'is-invalid':errors.model}" name="model" id="model" placeholder="Modelo"/>
                                                        <span class="invalid-feedback">{{ errors.model }}</span>
													</div>
												</div>
                                                <div class="row">
													<div class="mb-3 col-md-6">
														<label class="form-label" for="serial">Serial *</label>
														<Field type="text" class="form-control" :class="{'is-invalid':errors.serial}" name="serial" id="serial" placeholder="Serial"/>
                                                        <span class="invalid-feedback">{{ errors.serial }}</span>
													</div>
													<div class="mb-3 col-md-6">
														<label class="form-label" for="chassis">Chassis *</label>
														<Field type="text" class="form-control" :class="{'is-invalid':errors.chassis}" name="chassis" id="chassis" placeholder="Chassis"/>
                                                        <span class="invalid-feedback">{{ errors.chassis }}</span>
													</div>
												</div>
                                                <div class="row">
													<div class="mb-3 col-md-6">
														<label class="form-label" for="year">Ano de fabrico *</label>
														<Field type="text" class="form-control" :class="{'is-invalid':errors.year}" name="year" id="year" placeholder="Ano de fabrico"/>
                                                        <span class="invalid-feedback">{{ errors.year }}</span>
													</div>
													<div class="mb-3 col-md-6">
														<label class="form-label" for="buy_year">Ano de compra *</label>
														<Field type="text" class="form-control" :class="{'is-invalid':errors.buy_year}" name="buy_year" id="buy_year" placeholder="Ano de compra"/>
                                                        <span class="invalid-feedback">{{ errors.buy_year }}</span>
													</div>
												</div>
                                                <div class="row">
													<div class="mb-3 col-md-6">
														<label class="form-label" for="type_equipment_id">Tipo de equipamento *</label>
														<Field as="select" class="form-control" :class="{'is-invalid':errors.type_equipment_id}"  name="type_equipment_id" id="type_equipment_id" aria-describedby="type_equipment_id">
                                                            <option value="" disabled>Selecionar</option>
                                                            <option v-for="type_equipment in type_equipments" :key="type_equipment.id" :value="type_equipment.id">{{ type_equipment.name }}</option>
                                                        </Field>
                                                        <span class="invalid-feedback">{{ errors.type_equipment_id }}</span>
													</div>
													<div class="mb-3 col-md-6">
														<label class="form-label" for="destination_id">Clientes *</label>
														<Field as="select" class="form-control" :class="{'is-invalid':errors.destination_id}"  name="destination_id" id="destination_id" aria-describedby="destination_id">
                                                            <option value="" disabled>Selecionar</option>
                                                            <option v-for="destination in destinations" :key="destination.id" :value="destination.id">{{ destination.name }}</option>
                                                        </Field>
                                                        <span class="invalid-feedback">{{ errors.destination_id }}</span>
													</div>
												</div>
                                                <div class="row">
													<div class="mb-3 col-md-6">
														<label class="form-label" for="area_id">Área *</label>
														<Field as="select" class="form-control" :class="{'is-invalid':errors.area_id}"  name="area_id" id="area_id" aria-describedby="area_id">
                                                            <option value="" disabled>Selecionar</option>
                                                            <option v-for="area in areas" :key="area.id" :value="area.id">{{ area.name }}</option>
                                                        </Field>
                                                        <span class="invalid-feedback">{{ errors.area_id }}</span>
													</div>
													<div class="mb-3 col-md-6">
														<label class="form-label" for="supplier_id">Fabricante *</label>
														<Field as="select" class="form-control" :class="{'is-invalid':errors.supplier_id}"  name="supplier_id" id="supplier_id" aria-describedby="supplier_id">
                                                            <option value="" disabled>Selecionar</option>
                                                            <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">{{ supplier.name }}</option>
                                                        </Field>
                                                        <span class="invalid-feedback">{{ errors.supplier_id }}</span>
													</div>
												</div>
                                                <div class="row">
													<div class="mb-3 col-md-6">
														<label class="form-label" for="center_cost_id">Centro de custo</label>
														<Field as="select" class="form-control" :class="{'is-invalid':errors.center_cost_id}"  name="center_cost_id" id="center_cost_id" aria-describedby="center_cost_id" @change="getAccount(center_cost_id_to_city)" v-model="center_cost_id_to_city">
                                                            <option value="" disabled>Selecionar</option>
                                                            <option v-for="centercost in centercosts" :key="centercost.id" :value="centercost.id">{{ centercost.name }}</option>
                                                        </Field>
                                                        <span class="invalid-feedback">{{ errors.center_cost_id }}</span>
													</div>
													<div class="mb-3 col-md-4">
														<label class="form-label" for="center_cost_account_id">Conta de centro de custo</label>
														<Field as="select" class="form-control" :class="{'is-invalid':errors.center_cost_account_id}"  name="center_cost_account_id" id="center_cost_account_id" aria-describedby="cityId">
                                                            <option value="" disabled>Selecionar</option>
                                                            <option v-for="centercostaccount in centercostaccounts" :key="centercostaccount.id" :value="centercostaccount.id">{{ centercostaccount.name }}</option>
                                                        </Field>
                                                        <span class="invalid-feedback">{{ errors.center_cost_account_id }}</span>
													</div>
												</div>
                                                <div class="row">
													<div class="mb-3 col-md-4">
														<label class="form-label" for="acquisition_id">Tipo de aquisição *</label>
														<Field as="select" class="form-control" :class="{'is-invalid':errors.acquisition_id}"  name="acquisition_id" id="acquisition_id" aria-describedby="acquisition_id">
                                                            <option value="" disabled>Selecionar</option>
                                                            <option v-for="acquisition in acquisitions" :key="acquisition.id" :value="acquisition.id">{{ acquisition.name }}</option>
                                                        </Field>
                                                        <span class="invalid-feedback">{{ errors.acquisition_id }}</span>
													</div>
                                                    <div class="mb-3 col-md-2">
														<label class="form-label" for="equipment_category_id">Categoria do Equipamento</label>
														<Field as="select" class="form-control" :class="{'is-invalid':errors.equipment_category_id}"  name="equipment_category_id" id="equipment_category_id" aria-describedby="equipment_category_id">
                                                            <option value="" disabled>Selecionar</option>
                                                            <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
                                                        </Field>
                                                        <span class="invalid-feedback">{{ errors.equipment_category_id }}</span>
													</div>
                                                    <div class="mb-3 col-md-3">
														<label class="form-label" for="distance_control_id">Tipo de Medidor</label>
														<Field as="select" class="form-control" :class="{'is-invalid':errors.distance_control_id}"  name="distance_control_id" id="distance_control_id" aria-describedby="distance_control_id">
                                                            <option value="" disabled>Selecionar</option>
                                                            <option v-for="distance_control in distance_controls" :key="distance_control.id" :value="distance_control.id">{{ distance_control.name }}</option>
                                                        </Field>
                                                        <span class="invalid-feedback">{{ errors.distance_control_id }}</span>
													</div>
													<div class="mb-3 col-md-3">
														<label class="form-label" for="gps_tracking_id">ID GPS</label>
														<Field type="text" class="form-control" :class="{'is-invalid':errors.gps_tracking_id}" name="gps_tracking_id" id="gps_tracking_id" placeholder="GPS Tracking ID"/>
                                                        <span class="invalid-feedback">{{ errors.gps_tracking_id }}</span>
													</div>
												</div>
                                                <div class="row">
                                                    <div class="mb-3 col-md-3">
														<label class="form-label" for="amount">Valor de Compra</label>
														<Field type="text" class="form-control" :class="{'is-invalid':errors.amount}" name="amount" id="amount" placeholder="Valor de Compra"/>
                                                        <span class="invalid-feedback">{{ errors.amount }}</span>
													</div>
                                                    <div class="mb-3 col-md-2">
														<label class="form-label" for="coin_id">Moeda</label>
														<Field as="select" class="form-control" :class="{'is-invalid':errors.coin_id}"  name="coin_id" id="coin_id" aria-describedby="coin_id">
                                                            <option value="" disabled>Selecionar</option>
                                                            <option v-for="coin in coins" :key="coin.id" :value="coin.id">{{ coin.name }}</option>
                                                        </Field>
                                                        <span class="invalid-feedback">{{ errors.coin_id }}</span>
													</div>
                                                    <div class="mb-3 col-md-3">
														<label class="form-label" for="load_max">Capacidade Máxima</label>
														<Field type="text" class="form-control" :class="{'is-invalid':errors.load_max}" name="load_max" id="load_max" placeholder="Capacidade Máxima"/>
                                                        <span class="invalid-feedback">{{ errors.load_max }}</span>
													</div>
                                                    <div class="mb-3 col-md-2">
														<label class="form-label" for="load_unity_id">Unidade de Medida</label>
														<Field as="select" class="form-control" :class="{'is-invalid':errors.load_unity_id}"  name="load_unity_id" id="load_unity_id" aria-describedby="load_unity_id">
                                                            <option value="" disabled>Selecionar</option>
                                                            <option v-for="load_unity in load_unities" :key="load_unity.id" :value="load_unity.id">{{ load_unity.name }}</option>
                                                        </Field>
                                                        <span class="invalid-feedback">{{ errors.load_unity_id }}</span>
													</div>
													<div class="mb-3 col-md-2">
														<label class="form-label" for="is_commissioned">Equipamento Comissionado</label>
														<Field as="select" class="form-control" :class="{'is-invalid':errors.is_commissioned}"  name="is_commissioned" id="is_commissioned" aria-describedby="is_commissioned">
                                                            <option value="1" selected>Sim</option>
                                                            <option value="0" >Não</option>
                                                        </Field>
                                                        <span class="invalid-feedback">{{ errors.is_commissioned }}</span>
													</div>
                                                    <div class="mb-3 col-md-2">
														<label class="form-label" for="is_building">Edificio</label>
														<Field as="select" class="form-control" :class="{'is-invalid':errors.is_building}"  name="is_building" id="is_building" aria-describedby="is_building">
                                                            <option value="1" selected>Sim</option>
                                                            <option value="0" >Não</option>
                                                        </Field>
                                                        <span class="invalid-feedback">{{ errors.is_building }}</span>
													</div>
                                                    
													
												</div>

                                                <div class="row">
													<div class="mb-3 col-md-6">
														<label class="form-label" for="criticaly_id">Criticidade *</label>
														<Field as="select" class="form-control" :class="{'is-invalid':errors.criticaly_id}"  name="criticaly_id" id="criticaly_id" aria-describedby="criticaly_id">
                                                            <option value="" disabled>Selecionar</option>
                                                            <option v-for="critical in criticals" :key="critical.id" :value="critical.id">{{ critical.name }}</option>
                                                        </Field>
                                                        <span class="invalid-feedback">{{ errors.criticaly_id }}</span>
													</div>
													
                                                    <div class="mb-3 col-md-3">
														<label class="form-label" for="equipment_status_id">Estado *</label>
														<Field as="select" class="form-control" :class="{'is-invalid':errors.equipment_status_id}"  name="equipment_status_id" id="equipment_status_id" aria-describedby="equipment_status_id">
                                                            <option value="" disabled>Selecionar</option>
                                                            <option v-for="equipmentstatus in equipmentstatuses" :key="equipmentstatus.id" :value="equipmentstatus.id">{{ equipmentstatus.name }} / {{ equipmentstatus.mobilized }}</option>
                                                        </Field>
                                                        <span class="invalid-feedback">{{ errors.equipment_status_id }}</span>
													</div>
												</div>

                                                <div class="row mb-3">
                                                    <div class="mb-12 col-md-6">
                                                        <label class="form-label" for="fee_ids">Taxas Associadas *</label>
                                                        <Multiselect
                                                          v-model="selectedFees"
                                                          :options="fees"
                                                          :multiple="true"
                                                          :close-on-select="false"
                                                          :clear-on-select="false"
                                                          :preserve-search="true"
                                                          placeholder="Selecione as taxas"
                                                          label="name"
                                                          track-by="id"
                                                          :class="['form-control', {'is-invalid': errors.fee_ids}]"
                                                          style="--multiselect-border-color: #b2b2b2; --multiselect-selected-bg: #007bff; --multiselect-selected-color: #fff; --multiselect-tag-bg: #007bff; --multiselect-tag-color: #fff; --multiselect-option-highlighted-bg: #007bff; --multiselect-option-highlighted-color: #fff;"
                                                        />
                                                        <span class="invalid-feedback">{{ errors.fee_ids }}</span>
                                                    </div>
                                                </div>

                                                <Field type="hidden" class="form-control" name="fuel" v-model="fuel" placeholder="Capacidade Máxima"/>
												
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