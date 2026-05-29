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
    
  name: yup.string().required(),
  description: yup.string().required(),
  holder: yup.string().required(),
  emission_date: yup.string().required(),
  expires_date: yup.string().required(),
  type_document_id: yup.string().required(),
 
});
let self = this;
const router = useRouter();

const createRecordFunction = (values, actions) => {

 
    currentvalue.value = {values};

    loading.value = true;

    const arr = Array.from(values)
    
    axios.post('/documents',values).then((response)=>{

    // categories.value.unshift(response.data);
    // $('#createCategory').modal('hide');
    actions.resetForm();
    router.push({ path: '/admin/documents' });
    toastr.success('Documentos criada com sucesso');
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
      router.push({ path: '/admin/documents' });
     })
}

onMounted(()=>{
    getAuxiliarData()
})




</script>

<template>
    <div v-if="!loadingDiv">
        <h1 class="h3 mb-3">Documentos</h1>
        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Formulário criação das Documentos do sistema.</h5>

                                        <router-link to="/admin/documents" class="btn btn-pill btn-primary mt-3"><vue-feather type="arrow-left"></vue-feather>Voltar</router-link> 

                                       
								    </div>
                                    
                                    <div class="card-body">
                                            <Form @submit="createRecordFunction" :validation-schema="schema" v-slot="{ errors }">
												<div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="name">Nome</label>
														<Field type="text" class="form-control" :class="{'is-invalid':errors.name}" name="name" id="name" placeholder="Nome"/>
                                                        <span class="invalid-feedback">{{ errors.name }}</span>
													</div>
												</div>

                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="description">Descrição</label>
														<Field type="text" class="form-control" :class="{'is-invalid':errors.description}" name="description" id="description" placeholder="Descrição"/>
                                                        <span class="invalid-feedback">{{ errors.description }}</span>
													</div>
												</div>

                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="holder">Holder</label>
														<Field type="text" class="form-control" :class="{'is-invalid':errors.holder}" name="holder" id="holder" placeholder="Nome"/>
                                                        <span class="invalid-feedback">{{ errors.holder }}</span>
													</div>
												</div>
                                                
                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="type_document_id">Tipo de Documento</label>
														<Field as="select" class="form-control" :class="{'is-invalid':errors.type_document_id}"  name="type_document_id" id="type_document_id" aria-describedby="type_document_id">
                                                            <option value="" disabled>Selecionar</option>
                                                            <option v-for="typedocument in typedocuments" :key="typedocument.id" :value="typedocument.id">{{ typedocument.name }}</option>
                                                        </Field>
                                                        <span class="invalid-feedback">{{ errors.type_document_id }}</span>
													</div>
												</div>

                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="emission_date">Emissão</label>
														<Field type="date" class="form-control" :class="{'is-invalid':errors.emission_date}" name="emission_date" id="emission_date" placeholder="Data de Emissão"/>
                                                        <span class="invalid-feedback">{{ errors.emission_date }}</span>
													</div>
												</div>


                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="expires_date">Expira</label>
														<Field type="date" class="form-control" :class="{'is-invalid':errors.expires_date}" name="expires_date" id="expires_date" placeholder="Expira"/>
                                                        <span class="invalid-feedback">{{ errors.expires_date }}</span>
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