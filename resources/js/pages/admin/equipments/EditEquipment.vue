<script setup>

import axios from 'axios';
import { ref, onMounted, reactive, defineEmits, defineComponent,watch } from "vue";
import moment from 'moment'
import {useToastr} from '../../../toastr';
import {debounce} from 'lodash';
import {Form, Field} from 'vee-validate';
import { useRouter} from "vue-router";
import * as yup from 'yup';
import VueFeather from 'vue-feather';
import { Bootstrap4Pagination } from 'laravel-vue-pagination';
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.min.css'

let retrievedData =ref([]);
let loadingButtonSubmit =ref([false]);
let loadingDiv =ref([true]);
const toastr = useToastr();
const router = useRouter();
let self = this;
let currentvalue = ref([]);

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
let categories =ref([]);
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
  distance_control_id: yup.string().required(),
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
  fuel: yup.string().required(),
  equipment_category_id: yup.string().required(),
  is_commissioned: yup.string().required(),
  is_building: yup.string().required(),
//   fee_ids: yup.array().min(1, 'Selecione pelo menos uma taxa').required('Taxas são obrigatórias'),
});







const getData = () => {
  axios.get(`/equipments/+${router.currentRoute.value.params.id}/edit`)
       .then((response)=>{

        loadingDiv.value=false;
        retrievedData.value = response.data.equipment;
        criticals.value = response.data.criticalies;
        type_equipments.value = response.data.type_equipments;
        equipmentstatuses.value = response.data.equipment_statuses;
        destinations.value = response.data.destinations;
        areas.value = response.data.areas;
        suppliers.value = response.data.suppliers;
        distance_controls.value = response.data.distance_controls;
        centercosts.value = response.data.center_costs;
        centercostaccounts.value = response.data.center_cost_accounts;
        acquisitions.value = response.data.acquisitions;
        categories.value = response.data.categories;
        fees.value = response.data.fees;
        
        // Mapear as taxas selecionadas para objetos completos
        const selectedFeeIds = response.data.equipment.fees.map(fee => fee.fee_id);
        selectedFees.value = response.data.fees.filter(fee => selectedFeeIds.includes(fee.id));

        load_unities.value = response.data.load_unities;
        coins.value = response.data.coins;

       }).catch(()=>{
        loadingDiv.value=false;
       })
}

const editFunction = (values, actions) => {
    values.fee_ids = selectedFees.value.map(fee => fee.id);

  loadingButtonSubmit.value = true;
  axios.patch(`/equipments/${retrievedData.value.id}`,values).then((response)=>{

    // admins.value.unshift(response.data);
    // $('#createCategory').modal('hide');
    actions.resetForm();
    router.push({ path: '/admin/equipments' });
    toastr.success('Equipamento editada com sucesso');

  }).catch((error)=>{

    loadingButtonSubmit.value = false;
    toastr.error('Erro ao adicionar');
    if(error.response.data.errors){
      actions.setErrors(error.response.data.errors);
    }
  }).finally(()=>{
    loadingButtonSubmit.value = false;
  })
};

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




onMounted(()=>{
  
  getData();

})
</script>

<template>
    <div v-if="!loadingDiv">

        <h1 class="h3 mb-3">Equipamento/Ativo </h1>
        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Equipamento: {{ retrievedData.name }}</h5>

                                        <a @click="$router.go(-1)" class="btn btn-pill btn-primary mt-3"><vue-feather type="arrow-left"></vue-feather>Voltar</a> 
                                        <router-link :to='"/admin/equipments/"+retrievedData.id+"/upload"' class="btn btn-pill btn-primary mt-3 ml-2"><vue-feather type="file"></vue-feather>Imagens </router-link> 
								    </div>
                                    
                                    <div class="card-body">
                                          
                                        <div class="row">
                                            <div class="col-xl-12 col-xxl-12 d-flex">
                                                <div class="w-100">
                                                    <Form @submit="editFunction" :validation-schema="schema" v-slot="{ errors }">
                                                    

                                                <div class="row">
													<div class="mb-3 col-md-6">
														<label class="form-label" for="name">Nome *</label>
														<Field type="text" class="form-control" :class="{'is-invalid':errors.name}" name="name" id="name" v-model="retrievedData.name" placeholder="Nome do equipamento/ativo"/>
                                                        <span class="invalid-feedback">{{ errors.name }}</span>
													</div>
													<div class="mb-3 col-md-6">
														<label class="form-label" for="ref">Referência *</label>
														<Field type="text" class="form-control" :class="{'is-invalid':errors.ref}" name="ref" id="ref" v-model="retrievedData.ref" placeholder="Referência"/>
                                                        <span class="invalid-feedback">{{ errors.ref }}</span>
													</div>
												</div>
                                                <div class="row">
													<div class="mb-3 col-md-6">
														<label class="form-label" for="make">Marca *</label>
														<Field type="text" class="form-control" :class="{'is-invalid':errors.make}" name="make" id="make" v-model="retrievedData.make" placeholder="Marca"/>
                                                        <span class="invalid-feedback">{{ errors.make }}</span>
													</div>
													<div class="mb-3 col-md-6">
														<label class="form-label" for="model">Modelo *</label>
														<Field type="text" class="form-control" :class="{'is-invalid':errors.model}" name="model" id="model" v-model="retrievedData.model" placeholder="Modelo"/>
                                                        <span class="invalid-feedback">{{ errors.model }}</span>
													</div>
												</div>
                                                <div class="row">
													<div class="mb-3 col-md-6">
														<label class="form-label" for="serial">Serial *</label>
														<Field type="text" class="form-control" :class="{'is-invalid':errors.serial}" name="serial" id="serial" v-model="retrievedData.serial" placeholder="Serial"/>
                                                        <span class="invalid-feedback">{{ errors.serial }}</span>
													</div>
													<div class="mb-3 col-md-6">
														<label class="form-label" for="chassis">Chassis *</label>
														<Field type="text" class="form-control" :class="{'is-invalid':errors.chassis}" name="chassis" id="chassis" v-model="retrievedData.chassis" placeholder="Chassis"/>
                                                        <span class="invalid-feedback">{{ errors.chassis }}</span>
													</div>
												</div>
                                                <div class="row">
													<div class="mb-3 col-md-6">
														<label class="form-label" for="year">Ano de fabrico *</label>
														<Field type="text" class="form-control" :class="{'is-invalid':errors.year}" name="year" id="year" v-model="retrievedData.year" placeholder="Ano de fabrico"/>
                                                        <span class="invalid-feedback">{{ errors.year }}</span>
													</div>
													<div class="mb-3 col-md-6">
														<label class="form-label" for="buy_year">Ano de compra *</label>
														<Field type="text" class="form-control" :class="{'is-invalid':errors.buy_year}" name="buy_year" id="buy_year" v-model="retrievedData.buy_year" placeholder="Ano de compra"/>
                                                        <span class="invalid-feedback">{{ errors.buy_year }}</span>
													</div>
												</div>
                                                <div class="row">
													<div class="mb-3 col-md-6">
														<label class="form-label" for="type_equipment_id">Tipo de equipamento *</label>
														<Field as="select" class="form-control" :class="{'is-invalid':errors.type_equipment_id}"  name="type_equipment_id" id="type_equipment_id" aria-describedby="type_equipment_id" v-model="retrievedData.type_equipment_id">
                                                            <option value="" disabled>Selecionar</option>
                                                            <option v-for="type_equipment in type_equipments" :key="type_equipment.id" :value="type_equipment.id">{{ type_equipment.name }}</option>
                                                        </Field>
                                                        <span class="invalid-feedback">{{ errors.type_equipment_id }}</span>
													</div>
													<div class="mb-3 col-md-6">
														<label class="form-label" for="destination_id">Clientes *</label>
														<Field as="select" class="form-control" :class="{'is-invalid':errors.destination_id}"  name="destination_id" id="destination_id" aria-describedby="destination_id" v-model="retrievedData.destination_id">
                                                            <option value="" disabled>Selecionar</option>
                                                            <option v-for="destination in destinations" :key="destination.id" :value="destination.id">{{ destination.name }}</option>
                                                        </Field>
                                                        <span class="invalid-feedback">{{ errors.destination_id }}</span>
													</div>
												</div>
                                                <div class="row">
													<div class="mb-3 col-md-6">
														<label class="form-label" for="area_id">Área *</label>
														<Field as="select" class="form-control" :class="{'is-invalid':errors.area_id}"  name="area_id" id="area_id" aria-describedby="area_id" v-model="retrievedData.area_id">
                                                            <option value="" disabled>Selecionar</option>
                                                            <option v-for="area in areas" :key="area.id" :value="area.id">{{ area.name }}</option>
                                                        </Field>
                                                        <span class="invalid-feedback">{{ errors.area_id }}</span>
													</div>
													<div class="mb-3 col-md-6">
														<label class="form-label" for="supplier_id">Fabricante *</label>
														<Field as="select" class="form-control" :class="{'is-invalid':errors.supplier_id}"  name="supplier_id" id="supplier_id" aria-describedby="supplier_id" v-model="retrievedData.supplier_id">
                                                            <option value="" disabled>Selecionar</option>
                                                            <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">{{ supplier.name }}</option>
                                                        </Field>
                                                        <span class="invalid-feedback">{{ errors.supplier_id }}</span>
													</div>
												</div>
                                                <div class="row">
													<div class="mb-3 col-md-6">
														<label class="form-label" for="center_cost_id">Centro de custo</label>
														<Field as="select" class="form-control" :class="{'is-invalid':errors.center_cost_id}"  name="center_cost_id" id="center_cost_id" aria-describedby="center_cost_id" @change="getAccount(retrievedData.center_cost_id)" v-model="retrievedData.center_cost_id">
                                                            <option value="" disabled>Selecionar</option>
                                                            <option v-for="centercost in centercosts" :key="centercost.id" :value="centercost.id">{{ centercost.name }}</option>
                                                        </Field>
                                                        <span class="invalid-feedback">{{ errors.center_cost_id }}</span>
													</div>
													<div class="mb-3 col-md-4">
														<label class="form-label" for="center_cost_account_id">Conta de centro de custo</label>
														<Field as="select" class="form-control" :class="{'is-invalid':errors.center_cost_account_id}"  name="center_cost_account_id" id="center_cost_account_id" aria-describedby="center_cost_account_id" v-model="retrievedData.center_cost_account_id">
                                                            <option value="" disabled>Selecionar</option>
                                                            <option v-for="centercostaccount in centercostaccounts" :key="centercostaccount.id" :value="centercostaccount.id">{{ centercostaccount.name }}</option>
                                                        </Field>
                                                        <span class="invalid-feedback">{{ errors.center_cost_account_id }}</span>
													</div>
												</div>
                                                <div class="row">
													<div class="mb-3 col-md-4">
														<label class="form-label" for="acquisition_id">Tipo de aquisição *</label>
														<Field as="select" class="form-control" :class="{'is-invalid':errors.acquisition_id}"  name="acquisition_id" id="acquisition_id" aria-describedby="acquisition_id" v-model="retrievedData.acquisition_id">
                                                            <option value="" disabled>Selecionar</option>
                                                            <option v-for="acquisition in acquisitions" :key="acquisition.id" :value="acquisition.id">{{ acquisition.name }}</option>
                                                        </Field>
                                                        <span class="invalid-feedback">{{ errors.acquisition_id }}</span>
													</div>
                                                    <div class="mb-3 col-md-2">
														<label class="form-label" for="equipment_category_id">Categoria de Equipamento</label>
														<Field as="select" class="form-control" :class="{'is-invalid':errors.equipment_category_id}"  name="equipment_category_id" id="equipment_category_id" aria-describedby="equipment_category_id" v-model="retrievedData.equipment_category_id">
                                                            <option value="" disabled>Selecionar</option>
                                                            <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
                                                        </Field>
                                                        <span class="invalid-feedback">{{ errors.equipment_category_id }}</span>
													</div>
                                                    <div class="mb-3 col-md-3">
														<label class="form-label" for="distance_control_id">Tipo de Medidor</label>
														<Field as="select" class="form-control" :class="{'is-invalid':errors.distance_control_id}"  name="distance_control_id" id="distance_control_id" aria-describedby="distance_control_id" v-model="retrievedData.distance_control_id">
                                                            <option value="" disabled>Selecionar</option>
                                                            <option v-for="distance_control in distance_controls" :key="distance_control.id" :value="distance_control.id">{{ distance_control.name }}</option>
                                                        </Field>
                                                        <span class="invalid-feedback">{{ errors.distance_control_id }}</span>
													</div>
													<div class="mb-3 col-md-3">
														<label class="form-label" for="gps_tracking_id">ID GPS</label>
														<Field type="text" class="form-control" :class="{'is-invalid':errors.gps_tracking_id}" name="gps_tracking_id" id="gps_tracking_id" v-model="retrievedData.gps_tracking_id" placeholder="GPS Tracking ID"/>
                                                        <span class="invalid-feedback">{{ errors.gps_tracking_id }}</span>
													</div>
												</div>

                                                <div class="row">
                                                    <div class="mb-3 col-md-3">
														<label class="form-label" for="amount">Valor de Compra</label>
														<Field type="text" class="form-control" :class="{'is-invalid':errors.amount}" name="amount" id="amount" v-model="retrievedData.amount" placeholder="Valor de Compra"/>
                                                        <span class="invalid-feedback">{{ errors.amount }}</span>
													</div>
                                                    <div class="mb-3 col-md-2">
														<label class="form-label" for="coin_id">Moeda</label>
														<Field as="select" class="form-control" :class="{'is-invalid':errors.coin_id}"  name="coin_id" id="coin_id" v-model="retrievedData.coin_id" aria-describedby="coin_id">
                                                            <option value="" disabled>Selecionar</option>
                                                            <option v-for="coin in coins" :key="coin.id" :value="coin.id">{{ coin.name }}</option>
                                                        </Field>
                                                        <span class="invalid-feedback">{{ errors.coin_id }}</span>
													</div>
                                                    <div class="mb-3 col-md-3">
														<label class="form-label" for="load_max">Capacidade Máxima</label>
														<Field type="text" class="form-control" :class="{'is-invalid':errors.load_max}" name="load_max" id="load_max" v-model="retrievedData.load_max" placeholder="Capacidade Máxima"/>
                                                        <span class="invalid-feedback">{{ errors.load_max }}</span>
													</div>
                                                    <div class="mb-3 col-md-2">
														<label class="form-label" for="load_unity_id">Unidade de Medida</label>
														<Field as="select" class="form-control" :class="{'is-invalid':errors.load_unity_id}"  name="load_unity_id" id="load_unity_id" v-model="retrievedData.load_unity_id" aria-describedby="load_unity_id">
                                                            <option value="" disabled>Selecionar</option>
                                                            <option v-for="load_unity in load_unities" :key="load_unity.id" :value="load_unity.id">{{ load_unity.name }}</option>
                                                        </Field>
                                                        <span class="invalid-feedback">{{ errors.load_unity_id }}</span>
													</div>
													<div class="mb-3 col-md-2">
														<label class="form-label" for="is_commissioned">Equipamento Comissionado</label>
														<Field as="select" class="form-control" :class="{'is-invalid':errors.is_commissioned}"  name="is_commissioned" id="is_commissioned" v-model="retrievedData.is_commissioned" aria-describedby="is_commissioned">
                                                            <option value="1" >Sim</option>
                                                            <option value="0" >Não</option>
                                                        </Field>
                                                        <span class="invalid-feedback">{{ errors.is_commissioned }}</span>
													</div>
                                                    <div class="mb-3 col-md-2">
														<label class="form-label" for="is_building">Edificio</label>
														<Field as="select" class="form-control" :class="{'is-invalid':errors.is_building}"  name="is_building" id="is_building" v-model="retrievedData.is_building" aria-describedby="is_building">
                                                            <option value="1" >Sim</option>
                                                            <option value="0" >Não</option>
                                                        </Field>
                                                        <span class="invalid-feedback">{{ errors.is_building }}</span>
													</div>
                                                    
													
												</div>

                                                <div class="row">
													<div class="mb-3 col-md-6">
														<label class="form-label" for="criticaly_id">Criticidade *</label>
														<Field as="select" class="form-control" :class="{'is-invalid':errors.criticaly_id}"  name="criticaly_id" id="criticaly_id" aria-describedby="criticaly_id" v-model="retrievedData.criticaly_id">
                                                            <option value="" disabled>Selecionar</option>
                                                            <option v-for="critical in criticals" :key="critical.id" :value="critical.id">{{ critical.name }}</option>
                                                        </Field>
                                                        <span class="invalid-feedback">{{ errors.criticaly_id }}</span>
													</div>
													
                                                    <div class="mb-3 col-md-3">
														<label class="form-label" for="equipment_status_id">Estado *</label>
														<Field as="select" class="form-control" :class="{'is-invalid':errors.equipment_status_id}"  name="equipment_status_id" id="equipment_status_id" aria-describedby="equipment_status_id"  v-model="retrievedData.equipment_status_id">
                                                            <option value="" disabled>Selecionar</option>
                                                            <option v-for="equipmentstatus in equipmentstatuses" :key="equipmentstatus.id" :value="equipmentstatus.id">{{ equipmentstatus.name }} / {{ equipmentstatus.mobilized }}</option>
                                                        </Field>
                                                        <span class="invalid-feedback">{{ errors.equipment_status_id }}</span>
													</div>

                                                    <div class="mb-3 col-md-3">
														<label class="form-label" for="fuel">Combustivel</label>
														<Field type="text" class="form-control" :class="{'is-invalid':errors.fuel}" name="fuel" id="fuel" v-model="retrievedData.fuel" placeholder="Capacidade Máxima"/>
                                                        <span class="invalid-feedback">{{ errors.fuel }}</span>
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
                                                          :class="{'is-invalid': errors.fee_ids}"
                                                          style="--multiselect-border-color: #b2b2b2; --multiselect-selected-bg: #007bff; --multiselect-selected-color: #fff; --multiselect-tag-bg: #007bff; --multiselect-tag-color: #fff; --multiselect-option-highlighted-bg: #007bff; --multiselect-option-highlighted-color: #fff;"
                                                        />
                                                        <span class="invalid-feedback">{{ errors.fee_ids }}</span>
                                                    </div>
                                                </div>
												
                                                        <button type="submit" class="btn btn-primary" :disabled="loadingButtonSubmit == true">
                                                            <div v-if="loadingButtonSubmit == true" class="spinner-border spinner-border-sm" role="status"></div>
                                                            <span v-else>Submeter</span>
                                                        </button>


                                                    </Form>
                                                    
                                            
                                                
                                                </div>
                                            </div>
                                        </div>
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