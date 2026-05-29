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

const loading = ref(false);
const toastr = useToastr();
const loadingDiv = ref(true);
let currentvalue = ref([]);
let loads =ref([]);
let destinations =ref([]);
let equipments =ref([]);
let islogistics =ref([]);
let drivers =ref([]);
let customers =ref([]);

let destination_id_to_equipment = ref(0);

const schema = yup.object({
    
  destination_id: yup.string().required(),
  driver_id: yup.string().required(),
  customer_id: yup.string().required(),
  equipment_id: yup.string().required(),
  start_date: yup.string().required(),
  end_date: yup.string().required(),


 
});
let self = this;
const router = useRouter();

const getEquipment = (typeequipment) => {


axios.get(`/auxiliar-create-mcscr-logistic/${typeequipment}`)
   .then((response)=>{

    equipments.value = response.data.equipments;
   })
   .catch((error)=>{
    toastr.error(error);
    router.push({ path: '/admin/logistictrip' });
   })


}

const createRecordFunction = (values, actions) => {

 
    currentvalue.value = {values};

    loading.value = true;

    const arr = Array.from(values)
    
    axios.post('/logistictrip',values).then((response)=>{

    // categories.value.unshift(response.data);
    // $('#createCategory').modal('hide');
    actions.resetForm();
    router.push({ path: '/admin/logistictrip' });
    toastr.success('Viagens criada com sucesso');
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

axios.get('/auxiliar-create-users')
     .then((response)=>{

    
    loads.value = response.data.loads;
    drivers.value = response.data.drivers;
    customers.value = response.data.customers;

    destinations.value = response.data.logisticdestinations;
    // equipments.value = response.data.equipments
    islogistics.value =response.data.islogistic
     
      loadingDiv.value=false;

     


     })
     .catch((error)=>{
      toastr.error(error);
      router.push({ path: '/admin/logistictrip' });
     })
}

onMounted(()=>{
    getAuxiliarData()
})




</script>

<template>
    <div v-if="!loadingDiv">
        <h1 class="h3 mb-3">Viagens</h1>
        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Formulário criação das Viagens do sistema.</h5>

                                        <router-link to="/admin/logistictrip" class="btn btn-pill btn-primary mt-3"><vue-feather type="arrow-left"></vue-feather>Voltar</router-link> 

                                       
								    </div>
                                    
                                    <div class="card-body">
                                            <Form @submit="createRecordFunction" :validation-schema="schema" v-slot="{ errors }">

                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="destinations_id">Clientes:</label>
														<Field as="select" class="form-control" :class="{'is-invalid':errors.destinations_id}"  name="destinations_id" id="destinations_id" aria-describedby="destinations_id" @change="getEquipment(destination_id_to_equipment)" v-model="destination_id_to_equipment">
                                                            <option value="" selected>Selecionar</option>
                                                            <option v-for="islogistic in islogistics" :key="islogistic.id" :value="islogistic.id">{{ islogistic.name }}</option>
                                                        </Field>
                                                        <span class="invalid-feedback">{{ errors.destinations_id }}</span>
													</div>
												</div>
												
                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="equipment_id">Veículo</label>
														<Field as="select" class="form-control" :class="{'is-invalid':errors.equipment_id}"  name="equipment_id" id="equipment_id" aria-describedby="equipment_id">
                                                            <option value="" disabled>Selecionar</option>
                                                            <option v-for="equipment in equipments" :key="equipment.id" :value="equipment.id">{{ equipment.name }}/{{ equipment.ref }}</option>
                                                        </Field>
                                                        <span class="invalid-feedback">{{ errors.equipment_id }}</span>
													</div>
												</div>

                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="driver_id">Motorista</label>
														<Field as="select" class="form-control" :class="{'is-invalid':errors.driver_id}"  name="driver_id" id="driver_id" aria-describedby="driver_id">
                                                            <option value="" disabled>Selecionar</option>
                                                            <option v-for="driver in drivers" :key="driver.id" :value="driver.id">{{ driver.name }}</option>
                                                        </Field>
                                                        <span class="invalid-feedback">{{ errors.driver_id }}</span>
													</div>
												</div>
                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="customer_id">Cliente</label>
														<Field as="select" class="form-control" :class="{'is-invalid':errors.customer_id}"  name="customer_id" id="customer_id" aria-describedby="customer_id">
                                                            <option value="" disabled>Selecionar</option>
                                                            <option v-for="customer in customers" :key="customer.id" :value="customer.id">{{ customer.customer_name }}</option>
                                                        </Field>
                                                        <span class="invalid-feedback">{{ errors.customer_id }}</span>
													</div>
												</div>

                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="destination_id">Destino</label>
														<Field as="select" class="form-control" :class="{'is-invalid':errors.destination_id}"  name="destination_id" id="destination_id" aria-describedby="destination_id">
                                                            <option value="" disabled>Selecionar</option>
                                                            <option v-for="destination in destinations" :key="destination.id" :value="destination.id">{{ destination.departure }} / {{ destination.destination }} - {{ destination.loadstatus.name }}</option>
                                                        </Field>
                                                        <span class="invalid-feedback">{{ errors.destination_id }}</span>
													</div>
												</div>

                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="start_date">Inicio da Viagem</label>
														<Field type="date" class="form-control" :class="{'is-invalid':errors.start_date}" name="start_date" id="start_date" placeholder="Descrição"/>
                                                        <span class="invalid-feedback">{{ errors.start_date }}</span>
													</div>
												</div>

                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="end_date">Fim da Viagem</label>
														<Field type="date" class="form-control" :class="{'is-invalid':errors.end_date}" name="end_date" id="end_date" placeholder="Descrição"/>
                                                        <span class="invalid-feedback">{{ errors.end_date }}</span>
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