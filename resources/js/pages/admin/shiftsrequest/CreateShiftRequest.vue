<script setup>

import {onMounted, ref, reactive,watch} from 'vue';
import axios from 'axios';
import {useToastr} from '../../../toastr';
import {Form, Field,FieldArray} from 'vee-validate';
import * as yup from 'yup';
import { useRouter } from "vue-router";
import moment from 'moment'
import {debounce} from 'lodash';
import { Bootstrap4Pagination } from 'laravel-vue-pagination';
import VueFeather from 'vue-feather';

const loading = ref(false);
const toastr = useToastr();
const loadingDiv = ref(true);
let departments = ref([]);
let requests = ref([])
let currentvalue = ref([]);
let loggedUser = window.user;
let name = loggedUser.firstName+' '+loggedUser.lastName+' / '+loggedUser.email;
let typetaskid = 0;
const schema = yup.object({
    
    first_observation: yup.string().required(),

    materials: yup.array().of(
    yup.object().shape({
        product_id: yup.string().required(),
        quantity: yup.string().required(),
        obs: yup.string().required(),
      
      })
  )
 
});
let self = this;
const router = useRouter();

const createRecordFunction = (values, actions) => {

 
    currentvalue.value = {values};

    loading.value = true;

    const arr = Array.from(values)
    
    axios.post('/shiftequipmentrequest',values).then((response)=>{

    // categories.value.unshift(response.data);
    // $('#createCategory').modal('hide');
    actions.resetForm();
    router.push({ path: '/admin/shiftequipmentrequest' });
    toastr.success('Requisição criada com sucesso');
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

axios.get('/auxiliar-create-technicianrequest')
     .then((response)=>{

      departments.value = response.data.departments
    
      loadingDiv.value=false;

     


     })
     .catch((error)=>{
      toastr.error(error);
      router.push({ path: '/admin/shiftequipmentrequest' });
     })
}


const getType = (typerequest) => {

axios.get(`/auxiliar-create-requeststock/${typerequest}`)
   .then((response)=>{

    requests.value = response.data.requests;
   
   })
   .catch((error)=>{
    toastr.error(error);
    router.push({ path: '/admin/shiftequipmentrequest' });
   })


}


onMounted(()=>{
    getAuxiliarData()
})


</script>

<template>
    <div v-if="!loadingDiv">
        <h1 class="h3 mb-3">Requisição de Técnicos</h1>
        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Formulário criação requisição de Técnicos do sistema.</h5>

                                        <router-link to="/admin/shiftequipmentrequest" class="btn btn-pill btn-primary mt-3"><vue-feather type="arrow-left"></vue-feather>Voltar</router-link> 

                                       
								    </div>
                                    
                                    <div class="card-body">
                                            <Form @submit="createRecordFunction" :validation-schema="schema" v-slot="{ errors }">

                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="opened_by_user_id">Criado/Aberto por:</label>
														<Field type="text" class="form-control"  v-model="name"  readonly name="user_system"  placeholder="Criado por"/>
                                                        <span class="invalid-feedback">{{ errors.opened_by_user_id }}</span>
													</div>
												</div>

                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="type_task">Tipo de Requisição:</label>
														<Field as="select" class="form-control" :class="{'is-invalid':errors.type_task}"  name="type_task" id="type_task" aria-describedby="type_task" @change="getType(typetaskid)" v-model="typetaskid">
                                                            <option value="" disabled selected>Selecionar</option>
                                                            <option value="1">MCSCR</option>
                                                            <option value="2">Atividade Planeadas</option>
                                                        </Field>
                                                        <span class="invalid-feedback">{{ errors.type_task }}</span>
													</div>
												</div>

                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="request_id">ID: {{ requests.length }} Encontrados</label>
														<Field as="select" class="form-control" :class="{'is-invalid':errors.request_id}"  name="request_id" id="request_id" aria-describedby="request_id" >
                                                            <option value="" disabled selected>Selecionar</option>
                                                            <option v-for="request in requests" :key="request.id" :value="request.id">#{{ request.id }}</option>
                                                        </Field>
                                                        <span class="invalid-feedback">{{ errors.request_id }}</span>
													</div>
												</div>


                                                <div class="row">
                                                    <FieldArray class="form-control" name="departments" v-slot="{ fields, push, remove }">
                                                        <fieldset class="InputGroup" v-for="(field, idx) in fields" :key="field.key">
                                                                <div class="row">
                                                                    <div class="mb-3 col-md-3">
                                                                        <label class="form-label">Departamento:</label>
                                                                        <Field as="select" class="form-control" :class="{'is-invalid':errors.department_id}"  :name="`departments[${idx}].department_id`" id="department_id" aria-describedby="department_id">
                                                                            <option value="" disabled>Selecionar</option>
                                                                            <option v-for="department in departments" :key="department.id" :value="department.id">Departamento:{{ department.name }}</option>
                                                                        </Field>
                                                                       
                                                                        <span class="invalid-feedback">{{ errors.date }}</span>
                                                                    </div>
                                                                    <!-- <div class="mb-3 col-md-3">
                                                                        <label class="form-label">Quantidade:</label>
                                                                        <Field type="number" class="form-control" :class="{'is-invalid':errors.quantity}" :name="`departments[${idx}].quantity`" id="quantity" placeholder="Quantidade"/>
                                                                        <span class="invalid-feedback">{{ errors.quantity }}</span>
                                                                    </div> -->
                                                                    <div class="mb-3 col-md-3">
                                                                        <label class="form-label">Observação:</label>
                                                                        <Field type="text" class="form-control" :class="{'is-invalid':errors.obs}" :name="`departments[${idx}].obs`" id="obs" placeholder="Observação"/>
                                                                        <span class="invalid-feedback">{{ errors.obs }}</span>
                                                                    </div>
                                                                    <div class="col-sm-3">
                                                                        <button type="button" class="btn btn-danger mt-4" @click="remove(idx>1 ? idx : 0 )">X</button>
                                                                    </div>
                                                                </div>
                                                        </fieldset>

                                                        
                                                        <div class="card-body">
                                                            <button type="button" class="btn btn-info mt-2" @click="push({ name: '' })">Adicionar Departamento +</button>
                                                        </div>
                                                    </FieldArray>
                                                </div>
												<div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="first_observation">Observação</label>
														<Field type="text" class="form-control" :class="{'is-invalid':errors.first_observation}" name="first_observation" id="first_observation" placeholder="Observação"/>
                                                        <span class="invalid-feedback">{{ errors.first_observation }}</span>
													</div>
												</div>

                                                <!-- <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="code">Código</label>
														<Field type="text" class="form-control" :class="{'is-invalid':errors.code}" name="code" id="code" placeholder="Código"/>
                                                        <span class="invalid-feedback">{{ errors.code }}</span>
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