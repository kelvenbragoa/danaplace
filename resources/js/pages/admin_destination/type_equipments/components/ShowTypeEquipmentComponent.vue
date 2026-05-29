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
import QrcodeVue from 'qrcode.vue'

let retrievedData =ref([]);
let accounts =ref([]);
let loadingSubmit =ref([true]);
let loadingDiv =ref([true]);
let qrcodevalue = ref('/equipment/0');
const router = useRouter();
let self = this;
let criticals = ref([]);
let subcomponents = ref([]);
let currentvalue = ref([]);

const toastr = useToastr();


const schema = yup.object({
    
    name: yup.string().required(),
    model: yup.string().required(),
    make: yup.string().required(),
    criticaly_id: yup.string().required(),
    type_equipment_component_id: yup.string().required(),
    percentage_weigth: yup.string().required(),
    
   
  });

  const createRecordFunction = (values, actions) => {

 
currentvalue.value = {values};

loadingSubmit.value = true;

const arr = Array.from(values)

axios.post('/typeequipmentsubcomponent',values).then((response)=>{

// categories.value.unshift(response.data);
// $('#createCategory').modal('hide');
retrievedData.value = response.data.component;
subcomponents.value = response.data.subcomponents;
criticals.value = response.data.criticals;

actions.resetField('name');
actions.resetField('model');
actions.resetField('make');
actions.resetField('criticaly_id');
actions.resetField('percentage_weigth');

toastr.success('SubComponente criado com sucesso');
}).catch((error)=>{

loadingSubmit.value = false;
toastr.error('Erro ao adicionar. '+error.response.data.message);
if(error.response.data.errors){
    actions.setErrors(error.response.data.errors);
}
}).finally(()=>{
loadingSubmit.value = false;

})



};

const getData = (page = 1) => {
  axios.get(`/typeequipmentcomponent/+${router.currentRoute.value.params.id}?page=${page}`, )
       .then((response)=>{
        loadingDiv.value=false;
        retrievedData.value = response.data.component;
        subcomponents.value = response.data.subcomponents;
        criticals.value = response.data.criticals;
        qrcodevalue.value = "/subcomponent/"+retrievedData.value.id;
       
       }).catch(()=>{
        loadingDiv.value=false;
       })
}




onMounted(()=>{
  
  getData();
})
</script>

<template>
    <div v-if="!loadingDiv">

        <h1 class="h3 mb-3">Componente do Tipo de Equipamento</h1>
        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Nome: {{ retrievedData.name }}</h5>
                

                                        <router-link :to="'/admin/type_equipments/'+retrievedData.type_equipment_id" class="btn btn-pill btn-primary mt-3"><vue-feather type="arrow-left"></vue-feather>Voltar</router-link> 

                                       
								    </div>
                                    
                                    <div class="card-body">

                                        <div class="row">


                                            <div class="col-xl-12 col-xxl-12 d-flex">
                                                <div class="w-100">
                                                    <div class="row">
                                                        <p><strong>Nome:</strong> {{ retrievedData.name }}</p>
                                                        <p><strong>Modelo:</strong> {{ retrievedData.model }}</p>
                                                        <p><strong>Marca:</strong> {{ retrievedData.make }}</p>
                                                        <p><strong>Criticalidade:</strong> {{ retrievedData.criticality.name }}</p>
                                                        <p><strong>Percentagem:</strong> {{ retrievedData.percentage_weigth }}%</p>
                                                        <p><strong>SubComponentes:</strong> {{ subcomponents.total  }}</p>
                                                        <hr>
                                                        <div class="card-header">
                                                        <h5 class="card-title">SubComponentes do Tipo de Equipamento: subcomponents {{ subcomponents.total }} registros encontrados.</h5>
                                                        <a class="btn btn-pill btn-primary mt-3" data-toggle="collapse" href="#collapseExample" role="button" aria-expanded="false" aria-controls="collapseExample">
                                                            <vue-feather type="plus"></vue-feather>Adicionar novo subcomponente
                                                        </a>
                                                        <div class="collapse mt-3" id="collapseExample">
                                                            <div class="card card-body">
                                                                
                                                                <Form @submit="createRecordFunction" :validation-schema="schema" v-slot="{ errors }">
                                                                    <div class="row">
                                                                        <div class="mb-3 col-md-6">
                                                                            <label class="form-label" for="name">Nome</label>
                                                                            <Field type="text" class="form-control" :class="{'is-invalid':errors.name}" name="name" id="name" placeholder="Nome"/>
                                                                            <span class="invalid-feedback">{{ errors.name }}</span>
                                                                        </div>
                                                                    </div>
                                                                   
                                                                    <div class="row">
                                                                        <div class="mb-3 col-md-6">
                                                                            <label class="form-label" for="model">Modelo</label>
                                                                            <Field type="text" class="form-control" :class="{'is-invalid':errors.model}" name="model" id="model" placeholder="Modelo"/>
                                                                            <span class="invalid-feedback">{{ errors.model }}</span>
                                                                        </div>
                                                                    </div>
                                                                    <div class="row">
                                                                        <div class="mb-3 col-md-6">
                                                                            <label class="form-label" for="make">Marca</label>
                                                                            <Field type="text" class="form-control" :class="{'is-invalid':errors.make}" name="make" id="make" placeholder="Marca"/>
                                                                            <span class="invalid-feedback">{{ errors.make }}</span>
                                                                        </div>
                                                                    </div>
                                                                   
                                                                   
                                                                   
                                                                    <div class="row">
                                                                        <div class="mb-3 col-md-6">
                                                                            <label class="form-label" for="percentage_weigth">Percentagem do Componente no Equipamento %</label>
                                                                            <Field type="number" class="form-control" :class="{'is-invalid':errors.percentage_weigth}" name="percentage_weigth" id="percentage_weigth" placeholder="Percentagem%"/>
                                                                            <span class="invalid-feedback">{{ errors.percentage_weigth }}</span>
                                                                        </div>
                                                                    </div>
                                                                    <div class="row">
                                                                        <div class="mb-3 col-md-6">
                                                                            <label class="form-label" for="criticaly_id">Criticidade </label>
                                                                            <Field as="select" class="form-control" :class="{'is-invalid':errors.criticaly_id}"  name="criticaly_id" id="criticaly_id" aria-describedby="criticaly_id">
                                                                                <option value="" disabled>Selecionar</option>
                                                                                <option v-for="critical in criticals" :key="critical.id" :value="critical.id">{{ critical.name }}</option>
                                                                            </Field>
                                                                            <span class="invalid-feedback">{{ errors.criticaly_id }}</span>
                                                                            <Field type="hidden" class="form-control" :class="{'is-invalid':errors.type_equipment_component_id}" name="type_equipment_component_id" id="type_equipment_component_id" v-model="retrievedData.id"/>
                                                                        </div>
                                                                    </div>
                                                                  

                                                                   
                                                                    <button type="submit" class="btn btn-primary" :disabled="loadingSubmit == true">
                                                                        <div v-if="loadingSubmit == true" class="spinner-border spinner-border-sm" role="status"></div>
                                                                        <span v-else>Criar novo componente</span>
                                                                    </button>
                                                                </Form>
                                                            </div>
                                                        </div>
                                                    </div>

                                                   

                                                    <div class="table-responsive">
                                                        <table class="table table-striped">
                                                            <thead>
                                                                <tr>
                                                                    <th>#</th>
                                                                    <th>Nome</th>
                                                                    <th>Marca</th>
                                                                    <th>Modelo</th>
                                                                    <th>Criticidade</th>
                                                                    <th>Percentagem</th>
                                                                    <th>Ações</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody v-if="subcomponents.data.length > 0">
                                                                <tr v-for="(actualData,index) in subcomponents.data" :key="actualData.id">
                                                                    <td>#{{ index + 1 }}</td>
                                                                    <td>{{ actualData.name}}</td>
                                                                    <td>{{ actualData.make}}</td>
                                                                    <td>{{ actualData.model}}</td>
                                                                    <td>{{ actualData.criticality.name}}</td>
                                                                    <td>{{ actualData.percentage_weigth}}%</td>
                                                                    <td>
                                                                        <router-link :to="'/admin/type_equipments/subcomponent/'+actualData.id+'/edit'"><vue-feather type="edit-2"></vue-feather></router-link>
                                                                        <router-link :to="'/admin/type_equipments/subcomponent/'+actualData.id"><vue-feather type="eye"></vue-feather></router-link> 
                                                                        <a href="#" @click.prevent="confirmDeletion(actualData)"><vue-feather type="trash"></vue-feather></a>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                            <tbody v-else>
                                                                <tr>
                                                                <td colspan="8" align="center">Nenhum resultado encontrado</td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    <Bootstrap4Pagination :data="subcomponents" @pagination-change-page="getData"/>
                                                    </div>  
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