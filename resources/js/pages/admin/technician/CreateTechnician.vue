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
import FileUpload from 'primevue/fileupload';


const loading = ref(false);
const toastr = useToastr();
const loadingDiv = ref(true);
let currentvalue = ref([]);
let statusTechnician = 1;
let departments = ref([]);
let areas = ref([]);

let image = ref();

const onFileUpload = (event) => {
    image.value = event.files[0];
    console.log(image.value);
};

const schema = yup.object({
    
  name: yup.string().required(),
  date_of_birth: yup.date().required(),
  admission_date: yup.date().required(),
  code: yup.string().required(),
  document: yup.string().required(),
  department_id: yup.string().required(),
  area_id: yup.string().required(),
  contact: yup.string(),
  gender: yup.string(),
  address: yup.string(),
  province: yup.string(),
  city: yup.string(),
  civil_status: yup.string(),
  salary: yup.number().required().min(0),


});
let self = this;
const router = useRouter();

const createRecordFunction = (values, actions) => {

 
    currentvalue.value = {values};

    loading.value = true;

    const arr = Array.from(values)

    values.image = image.value;
    
    axios.post('/technicians',values,{
    headers: {
            'Content-Type': 'multipart/form-data'
          }
  }).then((response)=>{

    // technicians.value.unshift(response.data);
    // $('#createCategory').modal('hide');
    actions.resetForm();
    router.push({ path: '/admin/technicians' });
    toastr.success('Técnico criado com sucesso');
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

axios.get('/auxiliar-create-technicians')
     .then((response)=>{

      departments.value = response.data.departments;
      areas.value = response.data.areas;
      
      loadingDiv.value=false;

     })
     .catch((error)=>{
      toastr.error(error);
      router.push({ path: '/admin/departments' });
     })
}

onMounted(()=>{
    getAuxiliarData()
})




</script>

<template>
    <div v-if="!loadingDiv">
        <h1 class="h3 mb-3">Técnico</h1>
        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Formulário criação dos Técnicos do sistema.</h5>

                                        <router-link to="/admin/technicians" class="btn btn-pill btn-primary mt-3"><vue-feather type="arrow-left"></vue-feather>Voltar</router-link> 

                                       
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
														<label class="form-label" for="code">Código</label>
														<Field type="text" class="form-control" :class="{'is-invalid':errors.code}" name="code" id="code" placeholder="Código"/>
                                                        <span class="invalid-feedback">{{ errors.code }}</span>
													</div>
												</div>

                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="document">Documento</label>
														<Field type="text" class="form-control" :class="{'is-invalid':errors.document}" name="document" id="document" placeholder="Documento"/>
                                                        <Field type="hidden" name="status" v-model="statusTechnician"/>
                                                        <span class="invalid-feedback">{{ errors.document }}</span>
													</div>
												</div>
                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="document">Salário</label>
														<Field type="number" class="form-control" :class="{'is-invalid':errors.salary}" name="salary" id="salary" placeholder="Salário"/>
                                                        <Field type="hidden" name="status" v-model="salary"/>
                                                        <span class="invalid-feedback">{{ errors.salary }}</span>
													</div>
												</div>

                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="admission_date">Data de Admissão</label>
														<Field type="date" class="form-control" :class="{'is-invalid':errors.admission_date}" name="admission_date" id="admission_date" placeholder="admission_dateo"/>
                                                        <span class="invalid-feedback">{{ errors.admission_date }}</span>
													</div>
												</div>

                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="date_of_birth">Data de Nascimento</label>
														<Field type="date" class="form-control" :class="{'is-invalid':errors.date_of_birth}" name="date_of_birth" id="date_of_birth" placeholder="date_of_birtho"/>
                                                        <span class="invalid-feedback">{{ errors.date_of_birth }}</span>
													</div>
												</div>

                                                <div class="row">
                                                    <div class="mb-3 col-md-12">
														<label class="form-label" for="department_id">Departamento</label>
														<Field as="select" class="form-control" :class="{'is-invalid':errors.department_id}"  name="department_id" id="department_id" aria-describedby="department_id">
                                                            <option value="" disabled>Selecionar</option>
                                                            <option v-for="department in departments" :key="department.id" :value="department.id">{{ department.name }}</option>
                                                        </Field>
                                                        <span class="invalid-feedback">{{ errors.department_id }}</span>
													</div>
                                                </div>

                                                <div class="row">
                                                    <div class="mb-3 col-md-12">
														<label class="form-label" for="area_id">Área</label>
														<Field as="select" class="form-control" :class="{'is-invalid':errors.area_id}"  name="area_id" id="area_id" aria-describedby="area_id">
                                                            <option value="" disabled>Selecionar</option>
                                                            <option v-for="area in areas" :key="area.id" :value="area.id">{{ area.name }}</option>
                                                        </Field>
                                                        <span class="invalid-feedback">{{ errors.area_id }}</span>
													</div>
                                                </div>
                                                <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="image">Fotografia:</label>
                                                                <FileUpload mode="basic"  class="form-control" name="image" accept="image/*" auto :maxFileSize="1000000" customUpload @uploader="onFileUpload" />
                                                            </div>
                                                </div>

                                                <!-- <div class="row">
                                                    <div class="mb-3 col-md-12">
														<label class="form-label" for="area_id">Fotografia</label>
														<Field as="select" class="form-control" :class="{'is-invalid':errors.area_id}"  name="area_id" id="area_id" aria-describedby="area_id">
                                                            <option value="" disabled>Selecionar</option>
                                                            <option v-for="area in areas" :key="area.id" :value="area.id">{{ area.name }}</option>
                                                        </Field>
                                                        <span class="invalid-feedback">{{ errors.area_id }}</span>
													</div>
                                                </div> -->


												

                                                

                                                
                                               
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