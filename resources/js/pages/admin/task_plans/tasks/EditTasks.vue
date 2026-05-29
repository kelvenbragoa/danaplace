<script setup>

import axios from 'axios';
import { ref, onMounted, reactive, defineEmits, defineComponent,watch } from "vue";
import moment from 'moment'
import {useToastr} from '../../../../toastr';
import {debounce} from 'lodash';
import {Form, Field} from 'vee-validate';
import { useRouter} from "vue-router";
import * as yup from 'yup';
import VueFeather from 'vue-feather';
import { Bootstrap4Pagination } from 'laravel-vue-pagination';

let retrievedData =ref([]);
let criticals =ref([]);
let frequencies = ref([]);
let typetasks = ref([]);
let loadingButtonSubmit =ref([false]);
let loadingDiv =ref([true]);
const toastr = useToastr();
const router = useRouter();
let self = this;
let currentvalue = ref([]);

const schema = yup.object({
    name: yup.string().required(),
    task_plan_id: yup.string().required(),
    type_task_id: yup.string().required(),
    critical_id: yup.string().required(),
    unavailable_equipment_time_days:yup.number().min(0).required(),
    unavailable_equipment_time_hours:yup.number().max(23).min(0).required(),
    unavailable_equipment_time_minutes:yup.number().max(59).min(0).required(),
    estimated_time_days:yup.number().min(0).required(),
    estimated_time_hours:yup.number().max(23).min(0).required(),
    estimated_time_minutes:yup.number().max(59).min(0).required(),
    do_every: yup.number().min(0).required(),
    frequency_id: yup.string().required(),

});







const getData = () => {
  axios.get(`/taskplantasks/+${router.currentRoute.value.params.id}/edit`)
       .then((response)=>{

        loadingDiv.value=false;
        retrievedData.value = response.data.taskplantasks;
        frequencies.value = response.data.frequencies;
        criticals.value = response.data.criticals;
        typetasks.value = response.data.typetasks;

       }).catch(()=>{

        loadingDiv.value=false;

       })
}

const editFunction = (values, actions) => {

  loadingButtonSubmit.value = true;
  axios.patch(`/taskplantasks/${retrievedData.value.id}`,values).then((response)=>{

    // admins.value.unshift(response.data);
    // $('#createCategory').modal('hide');
    actions.resetForm();
    router.push({ path: '/admin/taskplans/'+retrievedData.value.task_plan_id });
    toastr.success('Atividade editada com sucesso');

  }).catch((error)=>{

    loadingButtonSubmit.value = false;
    toastr.error('Erro ao adicionar. '+error.response.data.message);
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

        <h1 class="h3 mb-3">Atividade </h1>
        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Atividade: {{ retrievedData.name }}</h5>

                                        <router-link :to="'/admin/taskplans/'+retrievedData.task_plan_id" class="btn btn-pill btn-primary mt-3"><vue-feather type="arrow-left"></vue-feather>Voltar</router-link> 

                                       
								    </div>
                                    
                                    <div class="card-body">
                                          
                                        <div class="row">
                                            <div class="col-xl-12 col-xxl-12 d-flex">
                                                <div class="w-100">
                                                    <Form @submit="editFunction" :validation-schema="schema" v-slot="{ errors }">
                                                    
                                                                    <div class="row">
                                                                        <div class="mb-3 col-md-12">
                                                                            <label class="form-label" for="name">Nome</label>
                                                                            <Field type="text" class="form-control" :class="{'is-invalid':errors.name}" name="name" id="name" placeholder="Nome" v-model="retrievedData.name"/>
                                                                            <span class="invalid-feedback">{{ errors.name }}</span>
                                                                        </div>
                                                                    </div>

                                                                    <div class="row">
                                                                        <div class="mb-3 col-md-12">
                                                                           <label class="form-label" for="type_task_id">Tipo de Atividade</label>
                                                                            <Field as="select" class="form-control" :class="{'is-invalid':errors.type_task_id}"  name="type_task_id" id="type_task_id"  v-model="retrievedData.type_task_id" aria-describedby="type_task_id">
                                                                                <option value="" disabled>Selecionar</option>
                                                                                <option v-for="typetask in typetasks" :key="typetask.id" :value="typetask.id">{{ typetask.name }}</option>
                                                                            </Field>
                                                                            <span class="invalid-feedback">{{ errors.type_task_id }}</span>
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    <div class="row">
                                                                        <div class="mb-3 col-md-12">
                                                                           <label class="form-label" for="critical_id">Criticidade</label>
                                                                            <Field as="select" class="form-control" :class="{'is-invalid':errors.critical_id}"  name="critical_id" id="critical_id" v-model="retrievedData.critical_id" aria-describedby="critical_id">
                                                                                <option value="" disabled>Selecionar</option>
                                                                                <option v-for="critical in criticals" :key="critical.id" :value="critical.id">{{ critical.name }}</option>
                                                                            </Field>
                                                                            <span class="invalid-feedback">{{ errors.critical_id }}</span>
                                                                        </div>
                                                                    </div>

                                                                    <div class="row">
                                                                        <div class="mb-3 col-md-12">
                                                                            <label class="form-label" for="do_every">Fazer a cada</label>
                                                                            <Field type="number" class="form-control" :class="{'is-invalid':errors.do_every}" name="do_every" id="do_every" v-model="retrievedData.do_every" placeholder="Fazer a cada"/>
                                                                            <span class="invalid-feedback">{{ errors.do_every }}</span>
                                                                        </div>
                                                                    </div>

                                                                    <div class="row">
                                                                        <div class="mb-3 col-md-12">
                                                                           <label class="form-label" for="frequency_id">Frequencia de repetição</label>
                                                                            <Field as="select" class="form-control" :class="{'is-invalid':errors.frequency_id}"  name="frequency_id" id="frequency_id" v-model="retrievedData.frequency_id" aria-describedby="frequency_id">
                                                                                <option value="" disabled>Selecionar</option>
                                                                                <option v-for="frequency in frequencies" :key="frequency.id" :value="frequency.id">{{ frequency.name }}</option>
                                                                            </Field>
                                                                            <span class="invalid-feedback">{{ errors.frequency_id }}</span>
                                                                        </div>
                                                                    </div>

                                                                    <div class="row">
                                                                        <label class="form-label">Tempo estimado para a atividade</label>
                                                                        <div class="mb-3 col-md-3">
                                                                            <label class="form-label" for="estimated_time_days">Dias</label>
                                                                            <Field type="number" class="form-control" :class="{'is-invalid':errors.estimated_time_days}" name="estimated_time_days" id="estimated_time_days" v-model="retrievedData.estimated_time_days" placeholder="Dias"/>
                                                                            <span class="invalid-feedback">{{ errors.estimated_time_days }}</span>
                                                                        </div>

                                                                        <div class="mb-3 col-md-3">
                                                                            <label class="form-label" for="estimated_time_hours">Horas</label>
                                                                            <Field type="number" class="form-control" :class="{'is-invalid':errors.estimated_time_hours}" name="estimated_time_hours" id="estimated_time_hours" v-model="retrievedData.estimated_time_hours" placeholder="Horas"/>
                                                                            <span class="invalid-feedback">{{ errors.estimated_time_hours }}</span>
                                                                        </div>
                                                                        
                                                                        <div class="mb-3 col-md-3">
                                                                            <label class="form-label" for="estimated_time_minutes">Minutos</label>
                                                                            <Field type="number" class="form-control" :class="{'is-invalid':errors.estimated_time_minutes}" name="estimated_time_minutes" id="estimated_time_minutes" v-model="retrievedData.estimated_time_minutes" placeholder="Minutos"/>
                                                                            <span class="invalid-feedback">{{ errors.estimated_time_minutes }}</span>
                                                                        </div>
                                                                    </div>

                                                                    <div class="row">
                                                                        <label class="form-label">Tempo estimado que o equipamento/ativo ficará indisponível</label>
                                                                        <div class="mb-3 col-md-3">
                                                                            <label class="form-label" for="unavailable_equipment_time_days">Dias</label>
                                                                            <Field type="number" class="form-control" :class="{'is-invalid':errors.unavailable_equipment_time_days}" name="unavailable_equipment_time_days" id="unavailable_equipment_time_days" v-model="retrievedData.unavailable_equipment_time_days" placeholder="Dias"/>
                                                                            <span class="invalid-feedback">{{ errors.unavailable_equipment_time_days }}</span>
                                                                        </div>

                                                                        <div class="mb-3 col-md-3">
                                                                            <label class="form-label" for="unavailable_equipment_time_hours">Horas</label>
                                                                            <Field type="number" class="form-control" :class="{'is-invalid':errors.unavailable_equipment_time_hours}" name="unavailable_equipment_time_hours" id="unavailable_equipment_time_hours" v-model="retrievedData.unavailable_equipment_time_hours" placeholder="Horas"/>
                                                                            <span class="invalid-feedback">{{ errors.unavailable_equipment_time_hours }}</span>
                                                                        </div>

                                                                        <div class="mb-3 col-md-3">
                                                                            <label class="form-label" for="unavailable_equipment_time_minutes">Minutos</label>
                                                                            <Field type="number" class="form-control" :class="{'is-invalid':errors.unavailable_equipment_time_minutes}" name="unavailable_equipment_time_minutes" id="unavailable_equipment_time_minutes" v-model="retrievedData.unavailable_equipment_time_minutes" placeholder="Minutos"/>
                                                                            <span class="invalid-feedback">{{ errors.unavailable_equipment_time_minutes }}</span>
                                                                        </div>
                                                                    </div>

                                                                    

                                                                   

                                                                    <Field type="hidden" name="task_plan_id" v-model="retrievedData.task_plan_id"></Field>

												
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