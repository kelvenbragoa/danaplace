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
let typemeetings = ref([]);

const schema = yup.object({
    
  subject: yup.string().required(),
  date: yup.string().required(),
  start_time: yup.string().required(),
  end_time: yup.string().required(),
  type_meeting_id: yup.string().required(),


 
});
let self = this;
const router = useRouter();

const createRecordFunction = (values, actions) => {

 
    currentvalue.value = {values};

    loading.value = true;

    const arr = Array.from(values)
    
    axios.post('/meeting',values).then((response)=>{

    // categories.value.unshift(response.data);
    // $('#createCategory').modal('hide');
    actions.resetForm();
    router.push({ path: '/admin/meeting' });
    toastr.success('Reunião criada com sucesso');
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

      typemeetings.value = response.data.typemeetings
      loadingDiv.value=false;

     


     })
     .catch((error)=>{
      toastr.error(error);
      router.push({ path: '/admin/meeting' });
     })
}

onMounted(()=>{
    getAuxiliarData()
})


</script>

<template>
    <div v-if="!loadingDiv">
        <h1 class="h3 mb-3">Reunião</h1>
        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Formulário criação das Reunião do sistema.</h5>

                                        <router-link to="/admin/meeting" class="btn btn-pill btn-primary mt-3"><vue-feather type="arrow-left"></vue-feather>Voltar</router-link> 

                                       
								    </div>
                                    
                                    <div class="card-body">
                                            <Form @submit="createRecordFunction" :validation-schema="schema" v-slot="{ errors }">
												<div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="subject">Assunto</label>
														<Field type="text" class="form-control" :class="{'is-invalid':errors.subject}" name="subject" id="subject" placeholder="Assunto"/>
                                                        <span class="invalid-feedback">{{ errors.subject }}</span>
													</div>
												</div>

                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="type_meeting_id">Tipo de Reunião</label>
														<Field as="select" class="form-control" :class="{'is-invalid':errors.type_meeting_id}"  name="type_meeting_id" id="type_meeting_id" aria-describedby="type_meeting_id">
                                                            <option value="" selected>Selecionar</option>
                                                            <option v-for="typemeeting in typemeetings" :key="typemeeting.id" :value="typemeeting.id">{{ typemeeting.name }}</option>
                                                        </Field>
                                                        <span class="invalid-feedback">{{ errors.type_meeting_id }}</span>
													</div>
												</div>

                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="date">Data</label>
														<Field type="date" class="form-control" :class="{'is-invalid':errors.date}" name="date" id="date" placeholder="Data"/>
                                                        <span class="invalid-feedback">{{ errors.date }}</span>
													</div>
												</div>
                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="start_time">Inicio</label>
														<Field type="time" class="form-control" :class="{'is-invalid':errors.start_time}" name="start_time" id="start_time" placeholder="Inicio"/>
                                                        <span class="invalid-feedback">{{ errors.start_time }}</span>
													</div>
												</div>
                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="end_time">Fim</label>
														<Field type="time" class="form-control" :class="{'is-invalid':errors.end_time}" name="end_time" id="end_time" placeholder="Data"/>
                                                        <span class="invalid-feedback">{{ errors.end_time }}</span>
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