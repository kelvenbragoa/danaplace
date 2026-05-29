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
let coins =ref([]);
let self = this;
let currentvalue = ref([]);

const schema = yup.object({
    departure: yup.string().required(),
    destination: yup.string().required(),
    total_distance: yup.string().required(),
    load_status_id: yup.string().required(),
    coin_id: yup.string().required(),
    amount: yup.string().required()

});







const getData = () => {
  axios.get(`/logisticdestination/+${router.currentRoute.value.params.id}/edit`)
       .then((response)=>{

        loadingDiv.value=false;
        retrievedData.value = response.data.destination;
        coins.value = response.data.coins;
        loads.value = response.data.loadstatus;

       }).catch(()=>{

        loadingDiv.value=false;

       })
}

const editFunction = (values, actions) => {

  loadingButtonSubmit.value = true;
  axios.patch(`/logisticdestination/${retrievedData.value.id}`,values).then((response)=>{

    // admins.value.unshift(response.data);
    // $('#createCategory').modal('hide');
    actions.resetForm();
    router.push({ path: '/admin/logisticdestination' });
    toastr.success('Destino de Logistica editada com sucesso');

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

        <h1 class="h3 mb-3">Destino de Logistica </h1>
        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Destino de Logistica: {{ retrievedData.departure }}</h5>

                                        <router-link to="/admin/logisticdestination" class="btn btn-pill btn-primary mt-3"><vue-feather type="arrow-left"></vue-feather>Voltar</router-link> 

                                       
								    </div>
                                    
                                    <div class="card-body">
                                          
                                        <div class="row">
                                            <div class="col-xl-12 col-xxl-12 d-flex">
                                                <div class="w-100">
                                                    <Form @submit="editFunction" :validation-schema="schema" v-slot="{ errors }">
                                                    
                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="departure">Partida</label>
                                                                <Field type="text" class="form-control" :class="{'is-invalid':errors.departure}" name="departure" v-model="retrievedData.departure" id="departure" placeholder="Partida"/>
                                                                <span class="invalid-feedback">{{ errors.departure }}</span>
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="destination">Destino</label>
                                                                <Field type="text" class="form-control" :class="{'is-invalid':errors.destination}" name="destination" v-model="retrievedData.destination" id="destination" placeholder="Carta de Conduçã"/>
                                                                <span class="invalid-feedback">{{ errors.destination }}</span>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="coin_id">Moeda</label>
                                                                <Field as="select" class="form-control" :class="{'is-invalid':errors.coin_id}"  v-model="retrievedData.coin_id" name="coin_id" id="coin_id" aria-describedby="coin_id">
                                                                    <option value="" disabled>Selecionar</option>
                                                                    <option v-for="coin in coins" :key="coin.id" :value="coin.id">{{ coin.name }}</option>
                                                                </Field>
                                                                <span class="invalid-feedback">{{ errors.coin_id }}</span>
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="amount">Valor Total</label>
                                                                <Field type="number" class="form-control" :class="{'is-invalid':errors.amount}" v-model="retrievedData.amount" name="amount" id="amount" placeholder="Distância Total"/>
                                                                <span class="invalid-feedback">{{ errors.amount }}</span>
                                                            </div>
                                                        </div>


                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="total_distance">Distancia Total</label>
                                                                <Field type="text" class="form-control" :class="{'is-invalid':errors.total_distance}" name="total_distance" v-model="retrievedData.total_distance" id="total_distance" placeholder="Carta de Conduçã"/>
                                                                <span class="invalid-feedback">{{ errors.total_distance }}</span>
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="load_status_id">Tipo de Carregamento</label>
                                                                <Field as="select" class="form-control" :class="{'is-invalid':errors.load_status_id}"  name="load_status_id" v-model="retrievedData.load_status_id" id="load_status_id" aria-describedby="load_status_id">
                                                                    <option value="" disabled>Selecionar</option>
                                                                    <option v-for="load in loads" :key="load.id" :value="load.id">{{ load.name }}</option>
                                                                </Field>
                                                                <span class="invalid-feedback">{{ errors.load_status_id }}</span>
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