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
let typedocuments =ref([]);
let self = this;
let currentvalue = ref([]);

const schema = yup.object({
    name: yup.string().required(),
    description: yup.string().required(),
    holder: yup.string().required(),
    emission_date: yup.string().required(),
    expires_date: yup.string().required(),
    type_document_id: yup.string().required(),
});







const getData = () => {
  axios.get(`/documents/+${router.currentRoute.value.params.id}/edit`)
       .then((response)=>{

        loadingDiv.value=false;
        retrievedData.value = response.data.document;
        typedocuments.value = response.data.typedocument;

       }).catch(()=>{

        loadingDiv.value=false;

       })
}

const editFunction = (values, actions) => {

  loadingButtonSubmit.value = true;
  axios.patch(`/documents/${retrievedData.value.id}`,values).then((response)=>{

    // admins.value.unshift(response.data);
    // $('#createCategory').modal('hide');
    actions.resetForm();
    router.push({ path: '/admin/documents' });
    toastr.success('Documento editada com sucesso');

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

        <h1 class="h3 mb-3">Documento </h1>
        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Documento: {{ retrievedData.name }}</h5>

                                        <router-link to="/admin/documents" class="btn btn-pill btn-primary mt-3"><vue-feather type="arrow-left"></vue-feather>Voltar</router-link> 

                                       
								    </div>
                                    
                                    <div class="card-body">
                                          
                                        <div class="row">
                                            <div class="col-xl-12 col-xxl-12 d-flex">
                                                <div class="w-100">
                                                    <Form @submit="editFunction" :validation-schema="schema" v-slot="{ errors }">
                                                    
                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="name">Nome</label>
                                                                <Field type="text" class="form-control" :class="{'is-invalid':errors.name}" name="name" v-model="retrievedData.name" id="name" placeholder="Nome"/>
                                                                <span class="invalid-feedback">{{ errors.name }}</span>
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="description">Descrição</label>
                                                                <Field type="text" class="form-control" :class="{'is-invalid':errors.description}" name="description" v-model="retrievedData.description" id="description" placeholder="Descrição"/>
                                                                <span class="invalid-feedback">{{ errors.description }}</span>
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="holder">Holder</label>
                                                                <Field type="text" class="form-control" :class="{'is-invalid':errors.holder}" name="holder" v-model="retrievedData.holder" id="holder" placeholder="Holder"/>
                                                                <span class="invalid-feedback">{{ errors.holder }}</span>
                                                            </div>
                                                        </div>

                                                       

                                                      
                                                      

                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="type_document_id">Tipo de Documento</label>
                                                                <Field as="select" class="form-control" :class="{'is-invalid':errors.type_document_id}"  name="type_document_id" id="type_document_id" aria-describedby="type_document_id"  v-model="retrievedData.type_document_id">
                                                                    <option value="" disabled>Selecionar</option>
                                                                    <option v-for="typedocument in typedocuments" :key="typedocument.id" :value="typedocument.id">{{ typedocument.name }}</option>
                                                                </Field>
                                                                <span class="invalid-feedback">{{ errors.type_document_id }}</span>
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="emission_date">Emissão</label>
                                                                <Field type="date" class="form-control" :class="{'is-invalid':errors.emission_date}" name="emission_date" v-model="retrievedData.emission_date" id="emission_date" placeholder="Data de Emissão"/>
                                                                <span class="invalid-feedback">{{ errors.emission_date }}</span>
                                                            </div>
                                                        </div>


                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="expires_date">Expira</label>
                                                                <Field type="date" class="form-control" :class="{'is-invalid':errors.expires_date}" name="expires_date" v-model="retrievedData.expires_date" id="expires_date" placeholder="Expira"/>
                                                                <span class="invalid-feedback">{{ errors.expires_date }}</span>
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