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
let typedocuments =ref([]);
const schema = yup.object({
    
  customer_name: yup.string().required(),
  customer_email: yup.string().required(),
  customer_mobile: yup.string().required(),
  customer_address: yup.string().required(),
  customer_nuit: yup.string().required(),

 
});
let self = this;
const router = useRouter();

const createRecordFunction = (values, actions) => {

 
    currentvalue.value = {values};

    loading.value = true;

    const arr = Array.from(values)
    
    axios.post('/logisticcustomer',values).then((response)=>{

    // categories.value.unshift(response.data);
    // $('#createCategory').modal('hide');
    actions.resetForm();
    router.push({ path: '/admin/logisticcustomer' });
    toastr.success('Clientes criada com sucesso');
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

    
      typedocuments.value = response.data.typedocuments;
     
      loadingDiv.value=false;

     


     })
     .catch((error)=>{
      toastr.error(error);
      router.push({ path: '/admin/logisticcustomer' });
     })
}

onMounted(()=>{
    // getAuxiliarData()
})




</script>

<template>
    <div v-if="loadingDiv">
        <h1 class="h3 mb-3">Clientes</h1>
        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Formulário criação das Clientes do sistema.</h5>

                                        <router-link to="/admin/logisticcustomer" class="btn btn-pill btn-primary mt-3"><vue-feather type="arrow-left"></vue-feather>Voltar</router-link> 

                                       
								    </div>
                                    
                                    <div class="card-body">
                                            <Form @submit="createRecordFunction" :validation-schema="schema" v-slot="{ errors }">
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