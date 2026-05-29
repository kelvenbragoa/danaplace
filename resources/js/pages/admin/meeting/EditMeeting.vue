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
import { QuillEditor } from '@vueup/vue-quill'
import '@vueup/vue-quill/dist/vue-quill.snow.css';

let retrievedData =ref([]);
let loadingButtonSubmit =ref([false]);
let loadingDiv =ref([true]);
const toastr = useToastr();
const router = useRouter();
let self = this;
let typemeetings = ref([]);
let body = ref([]);
let currentvalue = ref([]);
const schema = yup.object({
    subject: yup.string().required(),
    date: yup.string().required(),
    start_time: yup.string().required(),
    end_time: yup.string().required(),
    type_meeting_id: yup.string().required(),
});







const getData = () => {
  axios.get(`/meeting/+${router.currentRoute.value.params.id}/edit`)
       .then((response)=>{

        loadingDiv.value=false;
        retrievedData.value = response.data.meeting;
        body.value = response.data.meeting.body;
        typemeetings.value = response.data.typemeetings

       }).catch(()=>{

        loadingDiv.value=false;

       })
}

const editFunction = (values, actions) => {
    values.body = body.value;

  loadingButtonSubmit.value = true;
  axios.patch(`/meeting/${retrievedData.value.id}`,values).then((response)=>{

    // admins.value.unshift(response.data);
    // $('#createCategory').modal('hide');
    actions.resetForm();
    router.push({ path: '/admin/meeting' });
    toastr.success('Reunião editada com sucesso');

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

        <h1 class="h3 mb-3">Reunião</h1>
        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Reunião: {{ retrievedData.name }}</h5>

                                        <router-link to="/admin/meeting" class="btn btn-pill btn-primary mt-3"><vue-feather type="arrow-left"></vue-feather>Voltar</router-link> 

                                       
								    </div>
                                    
                                    <div class="card-body">
                                          
                                        <div class="row">
                                            <div class="col-xl-12 col-xxl-12 d-flex">
                                                <div class="w-100">
                                                    <Form @submit="editFunction" :validation-schema="schema" v-slot="{ errors }">
                                                    
                                                        
                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="subject">Assunto</label>
                                                                <Field type="text" class="form-control" :class="{'is-invalid':errors.subject}" name="subject" v-model="retrievedData.subject"  id="subject" placeholder="Assunto"/>
                                                                <span class="invalid-feedback">{{ errors.subject }}</span>
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="type_meeting_id">Tipo de Reunião</label>
                                                                <Field as="select" class="form-control" :class="{'is-invalid':errors.type_meeting_id}" v-model="retrievedData.type_meeting_id"   name="type_meeting_id" id="type_meeting_id" aria-describedby="type_meeting_id">
                                                                    <option value="" selected>Selecionar</option>
                                                                    <option v-for="typemeeting in typemeetings" :key="typemeeting.id" :value="typemeeting.id">{{ typemeeting.name }}</option>
                                                                </Field>
                                                                <span class="invalid-feedback">{{ errors.type_meeting_id }}</span>
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="date">Data</label>
                                                                <Field type="date" class="form-control" :class="{'is-invalid':errors.date}" name="date" v-model="retrievedData.date"  id="date" placeholder="Data"/>
                                                                <span class="invalid-feedback">{{ errors.date }}</span>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="start_time">Inicio</label>
                                                                <Field type="time" class="form-control" :class="{'is-invalid':errors.start_time}" name="start_time" v-model="retrievedData.start_time"  id="start_time" placeholder="Inicio"/>
                                                                <span class="invalid-feedback">{{ errors.start_time }}</span>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="end_time">Fim</label>
                                                                <Field type="time" class="form-control" :class="{'is-invalid':errors.end_time}" name="end_time" v-model="retrievedData.end_time"  id="end_time" placeholder="Data"/>
                                                                <span class="invalid-feedback">{{ errors.end_time }}</span>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="row">
                                                            <div class="mb-5 col-md-12">
                                                                <label class="form-label" for="body">Corpo</label>
                                                                <quill-editor v-model:content="body" contentType="html" theme="snow"></quill-editor>
                                                            </div>
                                                        </div>
												
                                                        <button type="submit" class="btn btn-primary mt-5" :disabled="loadingButtonSubmit == true">
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