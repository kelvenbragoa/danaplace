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
let provinces =ref([]);
const schema = yup.object({
    
  name: yup.string().required(),
//   company_name: yup.string().required(),
//   company_address: yup.string().required(),
//   company_nuit: yup.string().required(),
//   province_id: yup.string().required(),
//   company_mobile: yup.string().required(),
//   company_email: yup.string().required(),
 
});
let self = this;
const router = useRouter();

const createRecordFunction = (values, actions) => {

 
    currentvalue.value = {values};

    loading.value = true;

    const arr = Array.from(values)
    
    axios.post('/typedocuments',values).then((response)=>{

    // categories.value.unshift(response.data);
    // $('#createCategory').modal('hide');
    actions.resetForm();
    router.push({ path: '/admin/typedocuments' });
    toastr.success('Tipo de Documento criada com sucesso');
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

    
      provinces.value = response.data.provinces;
     
      loadingDiv.value=false;

     


     })
     .catch((error)=>{
      toastr.error(error);
      router.push({ path: '/admin/typedocuments' });
     })
}

onMounted(()=>{
    // getAuxiliarData()
})




</script>

<template>
    <div v-if="loadingDiv">
        <h1 class="h3 mb-3">Tipo de Documento</h1>
        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Formulário criação dos tipos de documento do sistema.</h5>

                                        <router-link to="/admin/typedocuments" class="btn btn-pill btn-primary mt-3"><vue-feather type="arrow-left"></vue-feather>Voltar</router-link> 

                                       
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
                                                <!-- <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="company_name">Nome Empresa</label>
														<Field type="text" class="form-control" :class="{'is-invalid':errors.company_name}" name="company_name" id="company_name" placeholder="Empresa"/>
                                                        <span class="invalid-feedback">{{ errors.company_name }}</span>
													</div>
												</div>
                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="company_address">Endereço da Empresa</label>
														<Field type="text" class="form-control" :class="{'is-invalid':errors.company_address}" name="company_address" id="company_address" placeholder="Endereço"/>
                                                        <span class="invalid-feedback">{{ errors.company_address }}</span>
													</div>
												</div>
                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="company_nuit">NUIT da Empresa</label>
														<Field type="text" class="form-control" :class="{'is-invalid':errors.company_nuit}" name="company_nuit" id="company_nuit" placeholder="NUIT"/>
                                                        <span class="invalid-feedback">{{ errors.company_nuit }}</span>
													</div>
												</div>
                                                
                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="province_id">Província</label>
														<Field as="select" class="form-control" :class="{'is-invalid':errors.province_id}"  name="province_id" id="province_id" aria-describedby="province_id">
                                                            <option value="" disabled>Selecionar</option>
                                                            <option v-for="province in provinces" :key="province.id" :value="province.id">{{ province.name }}</option>
                                                        </Field>
                                                        <span class="invalid-feedback">{{ errors.province_id }}</span>
													</div>
												</div>

                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="company_mobile">Telefone da Empresa</label>
														<Field type="text" class="form-control" :class="{'is-invalid':errors.company_mobile}" name="company_mobile" id="company_mobile" placeholder="Telefone"/>
                                                        <span class="invalid-feedback">{{ errors.company_mobile }}</span>
													</div>
												</div>

                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="company_email">Email da Empresa</label>
														<Field type="text" class="form-control" :class="{'is-invalid':errors.company_email}" name="company_email" id="company_email" placeholder="Email"/>
                                                        <span class="invalid-feedback">{{ errors.company_email }}</span>
													</div>
												</div>
												 -->

                                                

                                                
                                               
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