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
    
//   destination_id: yup.string().required(),
  expires_date:yup.string().required(),
 
});
let self = this;
const router = useRouter();

const createRecordFunction = (values, actions) => {

 
    currentvalue.value = {values};

    loading.value = true;

    const arr = Array.from(values)
    
    axios.post('/destination-quotation',values).then((response)=>{

    // categories.value.unshift(response.data);
    // $('#createCategory').modal('hide');
    actions.resetForm();
    router.push({ path: '/admin/destination/quotation' });
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
      router.push({ path: '/admin/destination/quotation' });
     })
}



onMounted(()=>{
    getAuxiliarData()
})



</script>

<template>
    <div v-if="!loadingDiv">
        <h1 class="h3 mb-3">Cotações</h1>
        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Formulário criação de cotações do sistema.</h5>

                                        <router-link to="/admin/destination/quotation" class="btn btn-pill btn-primary mt-3"><vue-feather type="arrow-left"></vue-feather>Voltar</router-link> 

                                       
								    </div>
                                    
                                    <div class="card-body">
                                            <Form @submit="createRecordFunction" :validation-schema="schema" v-slot="{ errors }">

                                                <!-- <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="destination_id">Cliente/Clientes:</label>
														<Field as="select" class="form-control" :class="{'is-invalid':errors.destination_id}" readonly  name="destination_id" id="destination_id" aria-describedby="destination_id">
                                                            <option value="" selected>Selecionar</option>
                                                            <option v-for="destination in destinations" :key="destination.id" :value="destination.id">{{ destination.name }}</option>
                                                        </Field>
                                                        <span class="invalid-feedback">{{ errors.destination_id }}</span>
													</div>
												</div> -->

                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="expires_date">Expira em:</label>
														<Field type="date" class="form-control" :class="{'is-invalid':errors.expires_date}" name="expires_date" id="expires_date" placeholder="Expira em" required/>
                                                        <span class="invalid-feedback">{{ errors.expires_date }}</span>
													</div>
												</div>

												<div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="obs">Obs</label>
														<Field type="text" class="form-control" :class="{'is-invalid':errors.obs}" required name="obs" id="obs" placeholder="Obs"/>
                                                        <span class="invalid-feedback">{{ errors.obs }}</span>
													</div>
												</div>
                                                <div class="row">
                                                    <FieldArray class="form-control" name="quotation" v-slot="{ fields, push, remove }">
                                                        <div class="card-body">
                                                            <button type="button" class="btn btn-pill btn-info mt-2" @click="push({ product_name: '' })">Adicionar Linha +</button>
                                                        </div>
                                                        <fieldset class="InputGroup" v-for="(field, idx) in fields" :key="field.key">
                                                                <div class="row">
                                                                    <div class="mb-3 col-md-3">
                                                                        <label class="form-label">Produto:</label>
                                                                        <Field type="text" class="form-control" :class="{'is-invalid':errors.product_name}" :name="`quotation[${idx}].product_name`" id="product_name" placeholder="Nome Produto"/>
                                                                        <span class="invalid-feedback">{{ errors.product_name }}</span>
                                                                    </div>
                                                                    <div class="mb-3 col-md-3">
                                                                        <label class="form-label">Quantidade:</label>
                                                                        <Field type="number" class="form-control" :class="{'is-invalid':errors.product_quantity}" :name="`quotation[${idx}].product_quantity`" id="product_quantity" placeholder="Quantidade Produto"/>
                                                                        <span class="invalid-feedback">{{ errors.product_quantity }}</span>
                                                                    </div>
                                                                    <!-- <div class="mb-3 col-md-3">
                                                                        <label class="form-label">Preço Unitário:</label>
                                                                        <Field type="number" class="form-control" :class="{'is-invalid':errors.unit_price}" :name="`quotation[${idx}].unit_price`" id="unit_price" placeholder="Preço Unitário"/>
                                                                        <span class="invalid-feedback">{{ errors.unit_price }}</span>
                                                                    </div>  -->
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