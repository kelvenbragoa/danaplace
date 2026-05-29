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
let provinces =ref([]);
let self = this;
let method = "put";
let currentvalue = ref([]);
const schema = yup.object({
    name: yup.string().required(),
  company_name: yup.string().required(),
  company_address: yup.string().required(),
  company_nuit: yup.string().required(),
  province_id: yup.string().required(),
  company_mobile: yup.string().required(),
  company_email: yup.string().required(),
  is_logistic: yup.string().required(),
});







const getData = () => {
  axios.get(`/destinations/+${router.currentRoute.value.params.id}/edit`)
       .then((response)=>{

        loadingDiv.value=false;
        retrievedData.value = response.data.destination;
        provinces.value = response.data.provinces;

       }).catch(()=>{

        loadingDiv.value=false;

       })
}

const editFunction = (values, actions) => {

  loadingButtonSubmit.value = true;
  axios.post(`/destinations/${retrievedData.value.id}`,values,{
            headers: {
            'Content-Type': 'multipart/form-data'
          }
        }).then((response)=>{

    // admins.value.unshift(response.data);
    // $('#createCategory').modal('hide');
    actions.resetForm();
    router.push({ path: '/admin/destinations' });
    toastr.success('Área editada com sucesso');

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

        <h1 class="h3 mb-3">Cliente </h1>
        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Clientes: {{ retrievedData.name }}</h5>

                                        <a @click="$router.go(-1)" class="btn btn-pill btn-primary mt-3"><vue-feather type="arrow-left"></vue-feather>Voltar</a> 

                                       
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
                                                                <label class="form-label" for="company_name">Empresa</label>
                                                                <Field type="text" class="form-control" :class="{'is-invalid':errors.company_name}" name="company_name" v-model="retrievedData.company_name" id="company_name" placeholder="Empresa"/>
                                                                <span class="invalid-feedback">{{ errors.company_name }}</span>
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="company_address">Endereço Empresa</label>
                                                                <Field type="text" class="form-control" :class="{'is-invalid':errors.company_address}" name="company_address" v-model="retrievedData.company_address" id="company_address" placeholder="Endereço Empresa"/>
                                                                <span class="invalid-feedback">{{ errors.company_address }}</span>
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="company_nuit">NUIT Empresa</label>
                                                                <Field type="text" class="form-control" :class="{'is-invalid':errors.company_nuit}" name="company_nuit" v-model="retrievedData.company_nuit" id="company_nuit" placeholder="NUIT Empresa"/>
                                                                <span class="invalid-feedback">{{ errors.company_nuit }}</span>
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="province_id">Província</label>
                                                                <Field as="select" class="form-control" :class="{'is-invalid':errors.province_id}"  name="province_id" id="province_id" aria-describedby="province_id"  v-model="retrievedData.province_id">
                                                                    <option value="" disabled>Selecionar</option>
                                                                    <option v-for="province in provinces" :key="province.id" :value="province.id">{{ province.name }}</option>
                                                                </Field>
                                                                <span class="invalid-feedback">{{ errors.province_id }}</span>
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="company_mobile">Telefone Empresa</label>
                                                                <Field type="text" class="form-control" :class="{'is-invalid':errors.company_mobile}" name="company_mobile" v-model="retrievedData.company_mobile" id="company_mobile" placeholder="Telefone Empresa"/>
                                                                <span class="invalid-feedback">{{ errors.company_mobile }}</span>
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="company_email">Email Empresa</label>
                                                                <Field type="text" class="form-control" :class="{'is-invalid':errors.company_email}" name="company_email" v-model="retrievedData.company_email" id="company_email" placeholder="Email Empresa"/>
                                                                <span class="invalid-feedback">{{ errors.company_email }}</span>
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="is_logistic">Logistica</label>
                                                                <Field as="select" class="form-control" :class="{'is-invalid':errors.is_logistic}"  name="is_logistic" v-model="retrievedData.is_logistic"  id="is_logistic" aria-describedby="is_logistic">
                                                                    <option value="0" selected>Não</option>
                                                                    <option value="1">Sim</option>
                                                                </Field>
                                                                <span class="invalid-feedback">{{ errors.is_logistic }}</span>
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="image">Logo Empresa</label>
                                                                <Field type="file" accept="image/*" class="form-control" :class="{'is-invalid':errors.image}" name="image" id="image" placeholder="Imagem"/>
                                                                <Field type="hidden" class="form-control" :class="{'is-invalid':errors.image}" name="_method" v-model=method id="image" placeholder="Imagem"/>
                                                                <span class="invalid-feedback">{{ errors.image }}</span>
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