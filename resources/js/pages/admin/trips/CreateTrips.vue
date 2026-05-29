<script setup>

import {onMounted, ref, reactive,watch} from 'vue';
import axios from 'axios';
import {useToastr} from '../../../toastr';
import {Form, Field,FieldArray  } from 'vee-validate';
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
let destinations =ref([]);
let destination_id_to_equipment = ref(0);
let equipments =ref([]);
const schema = yup.object({
    
  destination: yup.string().required(),
  departure_date:yup.string().required(),
  return_date:yup.string().required(),
  name:yup.string().required(),
 
});
let self = this;
const router = useRouter();

const createRecordFunction = (values, actions) => {

 
    currentvalue.value = {values};

    loading.value = true;

    const arr = Array.from(values)
    
    axios.post('/trips',values).then((response)=>{

    // categories.value.unshift(response.data);
    // $('#createCategory').modal('hide');
    actions.resetForm();
    router.push({ path: '/admin/trips' });
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

axios.get('/auxiliar-create-mcscr')
     .then((response)=>{

      destinations.value = response.data.destinations
      loadingDiv.value=false;

     


     })
     .catch((error)=>{
      toastr.error(error);
      router.push({ path: '/admin/trips' });
     })
}



onMounted(()=>{
    // getAuxiliarData()
})



</script>

<template>
    <div v-if="loadingDiv">
        <h1 class="h3 mb-3">Viagens</h1>
        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Formulário criação de viagens do sistema.</h5>

                                        <router-link to="/admin/trips" class="btn btn-pill btn-primary mt-3"><vue-feather type="arrow-left"></vue-feather>Voltar</router-link> 

                                       
								    </div>
                                    
                                    <div class="card-body">
                                            <Form @submit="createRecordFunction" :validation-schema="schema" v-slot="{ errors }">

                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="destination">Destino</label>
														<Field type="text" class="form-control" :class="{'is-invalid':errors.destination}" name="destination" id="destination" placeholder="Destino"/>
                                                        <span class="invalid-feedback">{{ errors.destination }}</span>
													</div>
												</div>

                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="name">Nome</label>
														<Field type="text" class="form-control" :class="{'is-invalid':errors.name}" name="name" id="name" placeholder="Nome"/>
                                                        <span class="invalid-feedback">{{ errors.name }}</span>
													</div>
												</div>

                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="departure_date">Data de partida:</label>
														<Field type="date" class="form-control" :class="{'is-invalid':errors.departure_date}" name="departure_date" id="departure_date" placeholder="Data de partida" required/>
                                                        <span class="invalid-feedback">{{ errors.departure_date }}</span>
													</div>
												</div>

                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="return_date">Data de Retorno:</label>
														<Field type="date" class="form-control" :class="{'is-invalid':errors.return_date}" name="return_date" id="return_date" placeholder="Data de partida" required/>
                                                        <span class="invalid-feedback">{{ errors.return_date }}</span>
													</div>
												</div>

												
                                                <div class="row">
                                                    <FieldArray class="form-control" name="trip" v-slot="{ fields, push, remove }">
                                                        <div class="card-body">
                                                            <button type="button" class="btn btn-pill btn-info mt-2" @click="push({ })">Adicionar Linha +</button>
                                                        </div>
                                                        <fieldset class="InputGroup" v-for="(field, idx) in fields" :key="field.key">
                                                                <div class="row">
                                                                    <div class="mb-3 col-md-3">
                                                                        <label class="form-label">Name:</label>
                                                                        <Field type="text" class="form-control" :class="{'is-invalid':errors.expense_name}" :name="`trip[${idx}].expense_name`" id="expense_name" placeholder="Nome"/>
                                                                        <span class="invalid-feedback">{{ errors.expense_name }}</span>
                                                                    </div>
                                                                    <div class="mb-3 col-md-3">
                                                                        <label class="form-label">Descrição:</label>
                                                                        <Field type="text" class="form-control" :class="{'is-invalid':errors.expense_description}" :name="`trip[${idx}].expense_description`" id="expense_description" placeholder="Descrição"/>
                                                                        <span class="invalid-feedback">{{ errors.expense_description }}</span>
                                                                    </div>
                                                                    <div class="mb-3 col-md-3">
                                                                        <label class="form-label">Preço:</label>
                                                                        <Field type="number" class="form-control" :class="{'is-invalid':errors.amount}" :name="`trip[${idx}].amount`" id="amount" placeholder="Valor"/>
                                                                        <span class="invalid-feedback">{{ errors.amount }}</span>
                                                                    </div> 
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