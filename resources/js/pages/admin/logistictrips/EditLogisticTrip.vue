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

let retrievedData =ref([]);
let loadingButtonSubmit =ref([false]);
let loadingDiv =ref([true]);
const toastr = useToastr();
const router = useRouter();
let loads =ref([]);
let self = this;
let destinations =ref([]);
let equipments =ref([]);
let drivers =ref([]);
let customers =ref([]);

let currentvalue = ref([]);
let tripstatuses = ref([])

const schema = yup.object({

    destination_id: yup.string().required(),
  driver_id: yup.string().required(),
  customer_id: yup.string().required(),

  equipment_id: yup.string().required(),
  start_date: yup.string().required(),
  end_date: yup.string().required(),

});







const getData = () => {
  axios.get(`/logistictrip/+${router.currentRoute.value.params.id}/edit`)
       .then((response)=>{

        loadingDiv.value=false;
        retrievedData.value = response.data.trip;
        loads.value = response.data.loadstatus;
        drivers.value = response.data.driver;
        customers.value = response.data.customers;

        destinations.value = response.data.destination;
        equipments.value = response.data.vehicle;
        tripstatuses.value =response.data.tripstatuses

       }).catch(()=>{

        loadingDiv.value=false;

       })
}

const editFunction = (values, actions) => {

  loadingButtonSubmit.value = true;
  axios.patch(`/logistictrip/${retrievedData.value.id}`,values).then((response)=>{

    // admins.value.unshift(response.data);
    // $('#createCategory').modal('hide');
    actions.resetForm();
    router.push({ path: '/admin/logistictrip' });
    toastr.success('Viagens editada com sucesso');

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




onMounted(()=>{
  
  getData();

})
</script>

<template>
    <div v-if="!loadingDiv">

        <h1 class="h3 mb-3">Viagens </h1>
        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Viagens: {{ retrievedData.departure }}</h5>

                                        <router-link to="/admin/logistictrip" class="btn btn-pill btn-primary mt-3"><vue-feather type="arrow-left"></vue-feather>Voltar</router-link> 

                                       
								    </div>
                                    
                                    <div class="card-body">
                                          
                                        <div class="row">
                                            <div class="col-xl-12 col-xxl-12 d-flex">
                                                <div class="w-100">
                                                    <Form @submit="editFunction" :validation-schema="schema" v-slot="{ errors }">
                                                    
                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="equipment_id">Veículo</label>
                                                                <Field as="select" class="form-control" readonly :class="{'is-invalid':errors.equipment_id}" v-model="retrievedData.equipment_id"  name="equipment_id" id="equipment_id" aria-describedby="equipment_id">
                                                                    <option value="" disabled>Selecionar</option>
                                                                    <option v-for="equipment in equipments" :key="equipment.id" :value="equipment.id">{{ equipment.name }}/{{ equipment.ref }}</option>
                                                                </Field>
                                                                <span class="invalid-feedback">{{ errors.equipment_id }}</span>
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="driver_id">Motorista</label>
                                                                <Field as="select" class="form-control" :class="{'is-invalid':errors.driver_id}" v-model="retrievedData.driver_id" name="driver_id" id="driver_id" aria-describedby="driver_id">
                                                                    <option value="" disabled>Selecionar</option>
                                                                    <option v-for="driver in drivers" :key="driver.id" :value="driver.id">{{ driver.name }}</option>
                                                                </Field>
                                                                <span class="invalid-feedback">{{ errors.driver_id }}</span>
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="customer_id">Cliente</label>
                                                                <Field as="select" class="form-control" :class="{'is-invalid':errors.customer_id}" v-model="retrievedData.customer_id" name="customer_id" id="customer_id" aria-describedby="customer_id">
                                                                    <option value="" disabled>Selecionar</option>
                                                                    <option v-for="customer in customers" :key="customer.id" :value="customer.id">{{ customer.customer_name }}</option>
                                                                </Field>
                                                                <span class="invalid-feedback">{{ errors.customer_id }}</span>
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="destination_id">Destino</label>
                                                                <Field as="select" class="form-control" :class="{'is-invalid':errors.destination_id}" v-model="retrievedData.destination_id" name="destination_id" id="destination_id" aria-describedby="destination_id">
                                                                    <option value="" disabled>Selecionar</option>
                                                                    <option v-for="destination in destinations" :key="destination.id" :value="destination.id">{{ destination.departure }} / {{ destination.destination }} - {{ destination.loadstatus.name }}</option>
                                                                </Field>
                                                                <span class="invalid-feedback">{{ errors.destination_id }}</span>
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="start_date">Inicio da Viagem</label>
                                                                <Field type="date" class="form-control" :class="{'is-invalid':errors.start_date}" v-model="retrievedData.start_date" name="start_date" id="start_date" placeholder="Descrição"/>
                                                                <span class="invalid-feedback">{{ errors.start_date }}</span>
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="end_date">Fim da Viagem</label>
                                                                <Field type="date" class="form-control" :class="{'is-invalid':errors.end_date}" v-model="retrievedData.end_date" name="end_date" id="end_date" placeholder="Descrição"/>
                                                                <span class="invalid-feedback">{{ errors.end_date }}</span>
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="trip_status_id">Estado da viagem</label>
                                                                <Field as="select" class="form-control" :class="{'is-invalid':errors.trip_status_id}" v-model="retrievedData.trip_status_id" name="trip_status_id" id="trip_status_id" aria-describedby="trip_status_id">
                                                                    <option value="" disabled>Selecionar</option>
                                                                    <option v-for="tripstatus in tripstatuses" :key="tripstatus.id" :value="tripstatus.id">{{ tripstatus.name }}</option>
                                                                </Field>
                                                                <span class="invalid-feedback">{{ errors.trip_status_id }}</span>
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