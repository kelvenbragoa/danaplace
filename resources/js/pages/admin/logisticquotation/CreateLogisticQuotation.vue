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
let typeloads = ref([]);

const customer_id = ref(0);

const schema = yup.object({
    
departure: yup.string().required(),
  destination: yup.string().required(),
//   destination_id: yup.string().required(),
  customer_id: yup.string().required(),
  start_date: yup.string().required(),
  end_date: yup.string().required(),
//   customer_name: yup.string().required(),
//   customer_email: yup.string().required(),
//   customer_mobile: yup.string().required(),
//   customer_address: yup.string().required(),
//   customer_nuit: yup.string().required(),
  type_load_id: yup.string().required(),

 
});
let self = this;
const router = useRouter();

const createRecordFunction = (values, actions) => {

 
    currentvalue.value = {values};

    loading.value = true;

    const arr = Array.from(values)
    
    axios.post('/logisticquotation',values).then((response)=>{

    // categories.value.unshift(response.data);
    // $('#createCategory').modal('hide');
    actions.resetForm();
    router.push({ path: '/admin/logisticquotation' });
    toastr.success('Cotação criada com sucesso');
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

    typeloads.value = response.data.typeloads;

    destinations.value = response.data.logisticdestinations;
    // equipments.value = response.data.equipments
    islogistics.value =response.data.islogistic
     
      loadingDiv.value=false;

     


     })
     .catch((error)=>{
      toastr.error(error);
      router.push({ path: '/admin/logisticquotation' });
     })
}

onMounted(()=>{
    getAuxiliarData()
})




</script>

<template>
    <div v-if="!loadingDiv">
        <h1 class="h3 mb-3">Cotação</h1>
        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Formulário criação das Cotação do sistema.</h5>

                                        <router-link to="/admin/logisticquotation" class="btn btn-pill btn-primary mt-3"><vue-feather type="arrow-left"></vue-feather>Voltar</router-link> 

                                       
								    </div>
                                    
                                    <div class="card-body">
                                            <Form @submit="createRecordFunction" :validation-schema="schema" v-slot="{ errors }">

                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="customer_id">Cliente</label>
														<Field as="select" class="form-control" :class="{'is-invalid':errors.customer_id}" v-model="customer_id"  name="customer_id" id="customer_id"  aria-describedby="customer_id">
                                                            <option value="0" disabled>Selecionar</option>
                                                            <option v-for="customer in customers" :key="customer.id" :value="customer.id">{{ customer.customer_name }}</option>
                                                        </Field>
                                                        <span class="invalid-feedback">{{ errors.customer_id }}</span>
													</div>
												</div>

                                                <div v-if="customer_id == 0">

                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="customer_name">Nome</label>
														<Field type="text" class="form-control" :class="{'is-invalid':errors.customer_name}" name="customer_name" id="customer_name" placeholder="Nome"/>
                                                        <span class="invalid-feedback">{{ errors.customer_name }}</span>
													</div>
												</div>

                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="customer_email">Email</label>
														<Field type="email" class="form-control" :class="{'is-invalid':errors.customer_email}" name="customer_email" id="customer_email" placeholder="Descrição"/>
                                                        <span class="invalid-feedback">{{ errors.customer_email }}</span>
													</div>
												</div>

                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="customer_mobile">Telefone</label>
														<Field type="text" class="form-control" :class="{'is-invalid':errors.customer_mobile}" name="customer_mobile" id="customer_mobile" placeholder="Telefone"/>
                                                        <span class="invalid-feedback">{{ errors.customer_mobile }}</span>
													</div>
												</div>

                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="customer_address">Endereço</label>
														<Field type="text" class="form-control" :class="{'is-invalid':errors.customer_address}" name="customer_address" id="customer_address" placeholder="Endereço"/>
                                                        <span class="invalid-feedback">{{ errors.customer_address }}</span>
													</div>
												</div>

                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="customer_nuit">NUIT</label>
														<Field type="text" class="form-control" :class="{'is-invalid':errors.customer_nuit}" name="customer_nuit" id="customer_nuit" placeholder="NUIT"/>
                                                        <span class="invalid-feedback">{{ errors.customer_nuit }}</span>
													</div>
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
														<label class="form-label" for="departure">Partida</label>
														<Field type="text" class="form-control" :class="{'is-invalid':errors.departure}" name="departure" id="departure" placeholder="Nome"/>
                                                        <span class="invalid-feedback">{{ errors.departure }}</span>
													</div>
												</div>

                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="destination">Destino</label>
														<Field type="text" class="form-control" :class="{'is-invalid':errors.destination}" name="destination" id="destination" placeholder="Descrição"/>
                                                        <span class="invalid-feedback">{{ errors.destination }}</span>
													</div>
												</div>

                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="type_load_id">Tipo de Carga</label>
														<Field as="select" class="form-control" :class="{'is-invalid':errors.type_load_id}"  name="type_load_id" id="type_load_id" aria-describedby="type_load_id">
                                                            <option value="" disabled>Selecionar</option>
                                                            <option v-for="load in typeloads" :key="load.id" :value="load.id">{{ load.name }}</option>
                                                        </Field>
                                                        <span class="invalid-feedback">{{ errors.type_load_id }}</span>
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