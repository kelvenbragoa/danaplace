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
let currentvalue = ref([]);

const schema = yup.object({
    departure: yup.string().required(),
    destination: yup.string().required(),
    total_distance: yup.string().required(),
    load_status_id: yup.string().required(),

});







const getData = () => {
  axios.get(`/logisticquotation/+${router.currentRoute.value.params.id}/edit`)
       .then((response)=>{

        loadingDiv.value=false;
        retrievedData.value = response.data.quotation;
        loads.value = response.data.loadstatus;

       }).catch(()=>{

        loadingDiv.value=false;

       })
}

const editFunction = (values, actions) => {

  loadingButtonSubmit.value = true;
  axios.patch(`/logisticquotation/${retrievedData.value.id}`,values).then((response)=>{

    // admins.value.unshift(response.data);
    // $('#createCategory').modal('hide');
    actions.resetForm();
    router.push({ path: '/admin/logisticquotation' });
    toastr.success('Quotações editada com sucesso');

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

        <h1 class="h3 mb-3">Quotações </h1>
        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Quotações: {{ retrievedData.departure }}</h5>

                                        <router-link to="/admin/logisticquotation" class="btn btn-pill btn-primary mt-3"><vue-feather type="arrow-left"></vue-feather>Voltar</router-link> 

                                       
								    </div>
                                    
                                    <div class="card-body">
                                          
                                        <div class="row">
                                            <div class="col-xl-12 col-xxl-12 d-flex">
                                                <div class="w-100">
                                                    <Form @submit="editFunction" :validation-schema="schema" v-slot="{ errors }">
                                                    
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