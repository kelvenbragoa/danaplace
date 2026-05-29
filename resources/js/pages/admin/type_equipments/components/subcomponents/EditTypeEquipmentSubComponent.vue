<script setup>

import axios from 'axios';
import { ref, onMounted, reactive, defineEmits, defineComponent,watch } from "vue";
import moment from 'moment'
import {useToastr} from '../../../../../toastr';
import {debounce} from 'lodash';
import {Form, Field} from 'vee-validate';
import { useRouter} from "vue-router";
import * as yup from 'yup';
import VueFeather from 'vue-feather';
import { Bootstrap4Pagination } from 'laravel-vue-pagination';

let retrievedData =ref([]);
let criticals =ref([]);
let equipmentstatuses =ref([]);
let loadingButtonSubmit =ref([false]);
let loadingDiv =ref([true]);
const toastr = useToastr();
const router = useRouter();
let self = this;
let currentvalue = ref([]);
const schema = yup.object({
    name: yup.string().required(),
    model: yup.string().required(),
    make: yup.string().required(),
    criticaly_id: yup.string().required(),
    type_equipment_component_id: yup.string().required(),
    percentage_weigth: yup.string().required(),
});







const getData = () => {
  axios.get(`/typeequipmentsubcomponent/+${router.currentRoute.value.params.id}/edit`)
       .then((response)=>{

        loadingDiv.value=false;
        retrievedData.value = response.data.subcomponent;
        equipmentstatuses.value = response.data.equipmentstatuses;
        criticals.value = response.data.criticals;

       }).catch(()=>{

        loadingDiv.value=false;

       })
}

const editFunction = (values, actions) => {

  loadingButtonSubmit.value = true;
  axios.patch(`/typeequipmentsubcomponent/${retrievedData.value.id}`,values).then((response)=>{

    // admins.value.unshift(response.data);
    // $('#createCategory').modal('hide');
    actions.resetForm();
    router.push({ path: '/admin/type_equipments/component/'+retrievedData.value.type_equipment_component_id });
    toastr.success('SubComponente do Tipo de Equipamento editado com sucesso');

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

        <h1 class="h3 mb-3">Componente do Tipo de Equipamento </h1>
        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Componente: {{ retrievedData.name }}</h5>
                                        <router-link :to="'/admin/type_equipments/component/'+retrievedData.type_equipment_component_id" class="btn btn-pill btn-primary mt-3"><vue-feather type="arrow-left"></vue-feather>Voltar</router-link> 
								    </div>
                                    
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-xl-12 col-xxl-12 d-flex">
                                                <div class="w-100">


                                                    <Form @submit="editFunction" :validation-schema="schema" v-slot="{ errors }">
                                                    
                                                                    <div class="row">
                                                                        <div class="mb-3 col-md-6">
                                                                            <label class="form-label" for="name">Nome</label>
                                                                            <Field type="text" class="form-control" :class="{'is-invalid':errors.name}" name="name" id="name" placeholder="Nome" v-model="retrievedData.name"/>
                                                                            <span class="invalid-feedback">{{ errors.name }}</span>
                                                                        </div>
                                                                    </div>
                                                                   
                                                                    <div class="row">
                                                                        <div class="mb-3 col-md-6">
                                                                            <label class="form-label" for="model">Modelo</label>
                                                                            <Field type="text" class="form-control" :class="{'is-invalid':errors.model}" name="model" id="model" placeholder="Modelo" v-model="retrievedData.model"/>
                                                                            <span class="invalid-feedback">{{ errors.model }}</span>
                                                                        </div>
                                                                    </div>

                                                                    <div class="row">
                                                                        <div class="mb-3 col-md-6">
                                                                            <label class="form-label" for="make">Marca</label>
                                                                            <Field type="text" class="form-control" :class="{'is-invalid':errors.make}" name="make" id="make" placeholder="Marca" v-model="retrievedData.make"/>
                                                                            <span class="invalid-feedback">{{ errors.make }}</span>
                                                                        </div>
                                                                    </div>

                                                                    <div class="row">
                                                                        <div class="mb-3 col-md-6">
                                                                            <label class="form-label" for="percentage_weigth">Percentagem do Componente no Equipamento %</label>
                                                                            <Field type="number" class="form-control" :class="{'is-invalid':errors.percentage_weigth}" name="percentage_weigth" id="percentage_weigth" placeholder="Percentagem%" v-model="retrievedData.percentage_weigth"/>
                                                                            <span class="invalid-feedback">{{ errors.percentage_weigth }}</span>
                                                                        </div>
                                                                    </div>

                                                                    <div class="row">
                                                                        <div class="mb-3 col-md-6">
                                                                            <label class="form-label" for="criticaly_id">Criticidade </label>
                                                                            <Field as="select" class="form-control" :class="{'is-invalid':errors.criticaly_id}"  name="criticaly_id" id="criticaly_id" aria-describedby="criticaly_id" v-model="retrievedData.criticaly_id">
                                                                                <option value="" disabled>Selecionar</option>
                                                                                <option v-for="critical in criticals" :key="critical.id" :value="critical.id">{{ critical.name }}</option>
                                                                            </Field>
                                                                            <Field type="hidden" class="form-control" :class="{'is-invalid':errors.type_equipment_component_id}" name="type_equipment_component_id" id="type_equipment_component_id" v-model="retrievedData.type_equipment_component_id"/>
                                                                            <span class="invalid-feedback">{{ errors.criticaly_id }}</span>
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