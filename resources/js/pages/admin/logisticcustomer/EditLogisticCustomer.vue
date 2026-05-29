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
    customer_name: yup.string().required(),
  customer_email: yup.string().required(),
  customer_mobile: yup.string().required(),
  customer_address: yup.string().required(),
  customer_nuit: yup.string().required(),
});







const getData = () => {
  axios.get(`/logisticcustomer/+${router.currentRoute.value.params.id}/edit`)
       .then((response)=>{

        loadingDiv.value=false;
        retrievedData.value = response.data.logisticcustomer;

       }).catch(()=>{

        loadingDiv.value=false;

       })
}

const editFunction = (values, actions) => {

  loadingButtonSubmit.value = true;
  axios.patch(`/logisticcustomer/${retrievedData.value.id}`,values).then((response)=>{

    // admins.value.unshift(response.data);
    // $('#createCategory').modal('hide');
    actions.resetForm();
    router.push({ path: '/admin/logisticcustomer' });
    toastr.success('Cliente editada com sucesso');

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
                                        <h5 class="card-title">Cliente: {{ retrievedData.name }}</h5>

                                        <router-link to="/admin/logisticcustomer" class="btn btn-pill btn-primary mt-3"><vue-feather type="arrow-left"></vue-feather>Voltar</router-link> 

                                       
								    </div>
                                    
                                    <div class="card-body">
                                          
                                        <div class="row">
                                            <div class="col-xl-12 col-xxl-12 d-flex">
                                                <div class="w-100">
                                                    <Form @submit="editFunction" :validation-schema="schema" v-slot="{ errors }">
                                                    
                                                        

                                                        <div class="row">
                                                                <div class="mb-3 col-md-12">
                                                                    <label class="form-label" for="customer_name">Nome</label>
                                                                    <Field type="text" class="form-control" :class="{'is-invalid':errors.customer_name}" v-model="retrievedData.customer_name" name="customer_name" id="customer_name" placeholder="Nome"/>
                                                                    <span class="invalid-feedback">{{ errors.customer_name }}</span>
                                                                </div>
                                                            </div>

                                                            <div class="row">
                                                                <div class="mb-3 col-md-12">
                                                                    <label class="form-label" for="customer_email">Email</label>
                                                                    <Field type="email" class="form-control" :class="{'is-invalid':errors.customer_email}" v-model="retrievedData.customer_email"  name="customer_email" id="customer_email" placeholder="Descrição"/>
                                                                    <span class="invalid-feedback">{{ errors.customer_email }}</span>
                                                                </div>
                                                            </div>

                                                            <div class="row">
                                                                <div class="mb-3 col-md-12">
                                                                    <label class="form-label" for="customer_mobile">Telefone</label>
                                                                    <Field type="text" class="form-control" :class="{'is-invalid':errors.customer_mobile}" v-model="retrievedData.customer_mobile"  name="customer_mobile" id="customer_mobile" placeholder="Telefone"/>
                                                                    <span class="invalid-feedback">{{ errors.customer_mobile }}</span>
                                                                </div>
                                                            </div>

                                                            <div class="row">
                                                                <div class="mb-3 col-md-12">
                                                                    <label class="form-label" for="customer_address">Endereço</label>
                                                                    <Field type="text" class="form-control" :class="{'is-invalid':errors.customer_address}" v-model="retrievedData.customer_address"  name="customer_address" id="customer_address" placeholder="Endereço"/>
                                                                    <span class="invalid-feedback">{{ errors.customer_address }}</span>
                                                                </div>
                                                            </div>

                                                            <div class="row">
                                                                <div class="mb-3 col-md-12">
                                                                    <label class="form-label" for="customer_nuit">NUIT</label>
                                                                    <Field type="text" class="form-control" :class="{'is-invalid':errors.customer_nuit}" v-model="retrievedData.customer_nuit"  name="customer_nuit" id="customer_nuit" placeholder="NUIT"/>
                                                                    <span class="invalid-feedback">{{ errors.customer_nuit }}</span>
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