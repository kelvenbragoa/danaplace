<script setup>

import {onMounted, ref, reactive,watch} from 'vue';
import axios from 'axios';
import {useToastr} from '../../../toastr';
import {Form, Field, FieldArray} from 'vee-validate';
import * as yup from 'yup';
import { useRouter } from "vue-router";
import moment from 'moment'
import {debounce} from 'lodash';
import { Bootstrap4Pagination } from 'laravel-vue-pagination';
import VueFeather from 'vue-feather';

const loading = ref(false);
const toastr = useToastr();
const loadingDiv = ref(false);
let currentvalue = ref([]);
const schema = yup.object({
    
  name: yup.string().required(),
  description: yup.string().required(),
 
});
let self = this;
const router = useRouter();

const createRecordFunction = (values, actions) => {

 
    currentvalue.value = {values};

    loading.value = true;

    const arr = Array.from(values)
    
    axios.post('/tirelayouts',values).then((response)=>{

    // categories.value.unshift(response.data);
    // $('#createCategory').modal('hide');
    actions.resetForm();
    router.push({ path: '/admin/tirelayouts' });
    toastr.success('Layout de Disposição dos Pneus criada com sucesso');
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




</script>

<template>
    <div v-if="!loadingDiv">
        <h1 class="h3 mb-3">Layout de Disposição dos Pneus</h1>
        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Formulário criação das Layout de Disposição dos Pneus do sistema.</h5>

                                        <router-link to="/admin/tirelayouts" class="btn btn-pill btn-primary mt-3"><vue-feather type="arrow-left"></vue-feather>Voltar</router-link> 

                                       
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
                                                    <FieldArray class="form-control" name="layout" v-slot="{ fields, push, remove }">
                                                        <div class="card-body">
                                                            <button type="button" class="btn btn-pill btn-info mt-2" @click="push({ })">Adicionar Nivel +</button>
                                                        </div>
                                                        <fieldset class="InputGroup" v-for="(field, idx) in fields" :key="field.key">
                                                                <div class="row">
                                                                    <p><strong>Nivel</strong>: {{ idx + 1 }}</p>
                                                                    <div class="mb-3 col-md-3">
                                                                        <label class="form-label">Nivel:</label>
                                                                        <span class="form-control">{{ idx + 1 }}</span>

                                                                    </div>
                                                                    <div class="mb-3 col-md-2">
                                                                        <label class="form-label">Quantidade de Pneus em cada lado:</label>
                                                                        <Field type="number" class="form-control" :class="{'is-invalid':errors.tire_quantity}" :name="`layout[${idx}].tire_quantity`" id="tire_quantity" placeholder="Quantidade Pneus em cada lado"/>
                                                                        <span class="invalid-feedback">{{ errors.tire_quantity }}</span>
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