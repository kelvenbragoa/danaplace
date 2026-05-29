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
let coins =ref([]);

const schema = yup.object({
    
  departure: yup.string().required(),
  destination: yup.string().required(),
  load_status_id: yup.string().required(),
  total_distance: yup.string().required(),
  coin_id: yup.string().required(),
  amount: yup.string().required(),


 
});
let self = this;
const router = useRouter();

const createRecordFunction = (values, actions) => {

 
    currentvalue.value = {values};

    loading.value = true;

    const arr = Array.from(values)
    
    axios.post('/logisticdestination',values).then((response)=>{

    // categories.value.unshift(response.data);
    // $('#createCategory').modal('hide');
    actions.resetForm();
    router.push({ path: '/admin/logisticdestination' });
    toastr.success('Destino de Logistica criada com sucesso');
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
        coins.value = response.data.coins;
     
      loadingDiv.value=false;

     


     })
     .catch((error)=>{
      toastr.error(error);
      router.push({ path: '/admin/logisticdestination' });
     })
}

onMounted(()=>{
    getAuxiliarData()
})




</script>

<template>
    <div v-if="!loadingDiv">
        <h1 class="h3 mb-3">Destino de Logistica</h1>
        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Formulário criação das Destino de Logistica do sistema.</h5>

                                        <router-link to="/admin/logisticdestination" class="btn btn-pill btn-primary mt-3"><vue-feather type="arrow-left"></vue-feather>Voltar</router-link> 

                                       
								    </div>
                                    
                                    <div class="card-body">
                                            <Form @submit="createRecordFunction" :validation-schema="schema" v-slot="{ errors }">
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
														<label class="form-label" for="load_status_id">Tipo de Carregamento</label>
														<Field as="select" class="form-control" :class="{'is-invalid':errors.load_status_id}"  name="load_status_id" id="load_status_id" aria-describedby="load_status_id">
                                                            <option value="" disabled>Selecionar</option>
                                                            <option v-for="load in loads" :key="load.id" :value="load.id">{{ load.name }}</option>
                                                        </Field>
                                                        <span class="invalid-feedback">{{ errors.load_status_id }}</span>
													</div>
												</div>

                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="coin_id">Moeda</label>
														<Field as="select" class="form-control" :class="{'is-invalid':errors.coin_id}"  name="coin_id" id="coin_id" aria-describedby="coin_id">
                                                            <option value="" disabled>Selecionar</option>
                                                            <option v-for="coin in coins" :key="coin.id" :value="coin.id">{{ coin.name }}</option>
                                                        </Field>
                                                        <span class="invalid-feedback">{{ errors.coin_id }}</span>
													</div>
												</div>

                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="amount">Valor Total</label>
														<Field type="number" class="form-control" :class="{'is-invalid':errors.amount}" name="amount" id="amount" placeholder="Valor Total"/>
                                                        <span class="invalid-feedback">{{ errors.amount }}</span>
													</div>
												</div>

                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="total_distance">Distância Total</label>
														<Field type="number" class="form-control" :class="{'is-invalid':errors.total_distance}" name="total_distance" id="total_distance" placeholder="Distância Total"/>
                                                        <span class="invalid-feedback">{{ errors.total_distance }}</span>
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