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
import FileUpload from 'primevue/fileupload';

let retrievedData =ref([]);
let loadingButtonSubmit =ref([false]);
let loadingDiv =ref([true]);
const toastr = useToastr();
const router = useRouter();
let self = this;
let currentvalue = ref([]);
let departments = ref([])
let areas = ref([])
const schema = yup.object({
  name: yup.string().required(),
    salary: yup.number().required().min(0),

});

let image = ref();

const onFileUpload = (event) => {
    image.value = event.files[0];
    console.log(image.value);
};





const getData = () => {
  axios.get(`/technicians/+${router.currentRoute.value.params.id}/edit`)
       .then((response)=>{

        loadingDiv.value=false;
        retrievedData.value = response.data.technician;
        departments.value = response.data.departments;
        areas.value = response.data.areas;

       }).catch(()=>{

        loadingDiv.value=false;

       })
}

const editFunction = (values, actions) => {

  loadingButtonSubmit.value = true;
  values.image = image.value;
  values._method = 'PUT';
  axios.post(`/technicians/${retrievedData.value.id}`,values,{
    headers: {
            'Content-Type': 'multipart/form-data'
          }
  }).then((response)=>{

    // admins.value.unshift(response.data);
    // $('#createCategory').modal('hide');
    actions.resetForm();
    router.push({ path: '/admin/technicians' });
    toastr.success('Técnico editado com sucesso');

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

        <h1 class="h3 mb-3">Técnico </h1>
        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Técnico: {{ retrievedData.name }}</h5>

                                        <router-link to="/admin/technicians" class="btn btn-pill btn-primary mt-3"><vue-feather type="arrow-left"></vue-feather>Voltar</router-link> 

                                       
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
                                                                <label class="form-label" for="code">Código</label>
                                                                <Field type="text" class="form-control" :class="{'is-invalid':errors.code}" name="code" v-model="retrievedData.code" id="code" placeholder="Código"/>
                                                                <span class="invalid-feedback">{{ errors.code }}</span>
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="document">Documento</label>
                                                                <Field type="text" class="form-control" :class="{'is-invalid':errors.document}" name="document" v-model="retrievedData.document" id="document" placeholder="Documento"/>
                                                                <span class="invalid-feedback">{{ errors.document }}</span>
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="mobile_phone">Telefone Celular</label>
                                                                <Field type="text" class="form-control" :class="{'is-invalid':errors.mobile_phone}" name="mobile_phone" v-model="retrievedData.mobile_phone" id="mobile_phone" placeholder="Telefone Celular"/>
                                                                <span class="invalid-feedback">{{ errors.mobile_phone }}</span>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="document">Salário</label>
														<Field type="number" class="form-control" :class="{'is-invalid':errors.salary}" name="salary" v-model="retrievedData.salary" id="salary" placeholder="Salário"/>
                                                        <Field type="hidden" name="status" v-model="salary"/>
                                                        <span class="invalid-feedback">{{ errors.salary }}</span>
													</div>
												</div>

                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="admission_date">Data de Admissão</label>
                                                                <Field type="date" class="form-control" :class="{'is-invalid':errors.admission_date}" name="admission_date" v-model="retrievedData.admission_date" id="admission_date" placeholder="admission_dateo"/>
                                                                <span class="invalid-feedback">{{ errors.admission_date }}</span>
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="department_id">Departamento</label>
                                                                <Field as="select" class="form-control" :class="{'is-invalid':errors.department_id}"  name="department_id" v-model="retrievedData.department_id" id="department_id" aria-describedby="department_id">
                                                                    <option value="" disabled>Selecionar</option>
                                                                    <option v-for="department in departments" :key="department.id" :value="department.id">{{ department.name }}</option>
                                                                </Field>
                                                                <span class="invalid-feedback">{{ errors.department_id }}</span>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="area_id">Aréa</label>
                                                                <Field as="select" class="form-control" :class="{'is-invalid':errors.area_id}"  name="area_id" v-model="retrievedData.area_id" id="area_id" aria-describedby="area_id">
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