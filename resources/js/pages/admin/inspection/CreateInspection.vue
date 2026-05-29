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
const loadingDiv = ref(false);
let currentvalue = ref([]);
let type_equipments =ref([]);
let destinations =ref([]);
let reasons =ref([]);
let equipments =ref([]);
let components =ref([]);
let sub_components = ref([]);
let malfunctions =ref([]);
let tasks =ref([]);
let inspection_status_id = ref(1);
let loggedUser = window.user;
let name = loggedUser.firstName+' '+loggedUser.lastName+' / '+loggedUser.email;
const reasonInput = ref(null);
var currentDate = new Date();

let destination_id_to_equipment = ref(0);
let type_equipment_id_to_equipment = ref(0);
let equipment_id_to_component = ref(0);
let component_id_to_subcomponent = ref(0);
const schema = yup.object({
    
  opened_at: yup.string().required(),
  type_equipment_id: yup.string().required(),
  equipment_id: yup.string().required(),
 
});
let self = this;
const router = useRouter();

const createRecordFunction = (values, actions) => {

 
    currentvalue.value = {values};

    loading.value = true;

    const arr = Array.from(values)
    
    axios.post('/inspections',values).then((response)=>{
    // categories.value.unshift(response.data);
    // $('#createCategory').modal('hide');
    actions.resetForm();
    router.push({ path: '/admin/inspections' });
    toastr.success('Inspeção criado com sucesso');

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

    //   type_equipments.value = response.data.type_equipments
      destinations.value = response.data.destinations
      malfunctions.value = response.data.malfunctions
      tasks.value = response.data.tasks
      reasons.value = response.data.reasons
      loadingDiv.value=false;

     


     })
     .catch((error)=>{
      toastr.error(error);
      router.push({ path: '/admin/inspections' });
     })
}

const getTypeEquipment = (destination_id_to_equipment) => {



axios.get(`/auxiliar-create-mcscr-type-equipment/${destination_id_to_equipment}`)
   .then((response)=>{

    type_equipments.value = response.data.type_equipments;
   })
   .catch((error)=>{
    toastr.error(error);
    router.push({ path: '/admin/inspections' });
   })


}

const getEquipment = (typeequipment) => {


axios.get(`/auxiliar-create-mcscr/${typeequipment}/${destination_id_to_equipment.value}`)
   .then((response)=>{

    equipments.value = response.data.equipments;
   })
   .catch((error)=>{
    toastr.error(error);
    router.push({ path: '/admin/inspections' });
   })


}

const getComponent = (equipment) => {

axios.get(`/auxiliar-create-mcscr-components/${equipment}`)
   .then((response)=>{

    components.value = response.data.components;
   })
   .catch((error)=>{
    toastr.error(error);
    router.push({ path: '/admin/inspections' });
   })


}

const getSubComponent = (equipment) => {

axios.get(`/auxiliar-create-mcscr-subcomponents/${equipment}`)
   .then((response)=>{

    sub_components.value = response.data.sub_components;
   })
   .catch((error)=>{
    toastr.error(error);
    router.push({ path: '/admin/inspections' });
   })


}

const getReason = () => {

    axios.get(`/auxiliar-create-mcscr-reason`,
      {
        params:{
          query: reasonInput.value
        }
      })
       .then((response)=>{
        reasons.value = response.data;
        loadingDiv.value=false;

        
       })
}

watch(reasonInput,debounce(()=>{
    getReason();
},300));

onMounted(()=>{
    getAuxiliarData()
})




</script>

<template>
    <div v-if="!loadingDiv">
        <h1 class="h3 mb-3">Inspeção </h1>
        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Formulário criação das Inspeções do sistema.</h5>
                                        <h5 class="card-title">Ao submeter o Inspeção, o equipamento estará indisponível até o Inspeção for terminado.</h5>

                                        <router-link to="/admin/inspections" class="btn btn-pill btn-primary mt-3"><vue-feather type="arrow-left"></vue-feather>Voltar</router-link> 
                                       
								    </div>
                                    
                                    <div class="card-body">
                                        
                                            <Form @submit="createRecordFunction" :validation-schema="schema" v-slot="{ errors }">

												


                                                


                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="destination_id">Clientes:</label>
														<Field as="select" class="form-control" :class="{'is-invalid':errors.destination_id}"  name="destination_id" id="destination_id" aria-describedby="destination_id" @change="getTypeEquipment(destination_id_to_equipment)" v-model="destination_id_to_equipment">
                                                            <option value="" selected>Selecionar</option>
                                                            <option v-for="destination in destinations" :key="destination.id" :value="destination.id">{{ destination.name }}</option>
                                                        </Field>
                                                        <span class="invalid-feedback">{{ errors.destination_id }}</span>
													</div>
												</div>

                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="type_equipment_id">Tipo de Equipamento/Ativos:</label>
														<Field as="select" class="form-control" :class="{'is-invalid':errors.type_equipment_id}"  name="type_equipment_id" id="type_equipment_id" aria-describedby="type_equipment_id" @change="getEquipment(type_equipment_id_to_equipment)" v-model="type_equipment_id_to_equipment">
                                                            <option value="" selected>Selecionar</option>
                                                            <option v-for="(type_equipment,index) in type_equipments" :key="type_equipment.id" :value="type_equipment[0].type_equipment_id">{{ index }}</option>
                                                        </Field>
                                                        <span class="invalid-feedback">{{ errors.type_equipment_id }}</span>
													</div>
												</div>

                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="equipment_id">Equipamentos/Ativos:</label>
														<Field as="select" class="form-control" :class="{'is-invalid':errors.equipment_id}"  name="equipment_id" id="equipment_id" aria-describedby="equipment_id" @change="getComponent(equipment_id_to_component)" v-model="equipment_id_to_component">
                                                            <option value="" selected>Selecionar</option>
                                                            <option v-for="equipment in equipments" :key="equipment.id" :value="equipment.id">{{ equipment.ref }} - {{ equipment.name }}</option>
                                                        </Field>
                                                        <span class="invalid-feedback">{{ errors.equipment_id }}</span>
													</div>
												</div>

   

                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="opened_by_user_id">Criado/Aberto por:</label>
                                                        <span class="form-control">{{ name }}</span>
														<!-- <Field type="text" class="form-control"  v-model="name"  readonly  placeholder="Criado por"/> -->
                                                        <span class="invalid-feedback">{{ errors.opened_by_user_id }}</span>
													</div>
												</div>

                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="opened_at">Aberto às:</label>
														<Field type="datetime-local" class="form-control" :class="{'is-invalid':errors.opened_at}" name="opened_at" id="opened_at" placeholder="Aberto às" required/>
                                                        <!-- <span class="form-control">{{ moment(currentDate).format('DD-MM-YYYY H:mm') }}</span> -->
                                                        <span class="invalid-feedback">{{ errors.opened_at }}</span>
													</div>
												</div>
                                                
                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="inspection_status_id">Estado da Inspeção:</label>
                                                        <Field as="select" class="form-control" :class="{'is-invalid':errors.inspection_status_id}" required v-model="inspection_status_id"  name="inspection_status_id" id="inspection_status_id" aria-describedby="inspection_status_id">
                                                            <option value="" selected disabled>Selecionar</option>
                                                            <option value="1" selected>Programar</option>
                                                            <option value="2" selected>Executar</option>
                                                        </Field>
                                                        <span class="invalid-feedback">{{ errors.inspection_status_id }}</span>
													</div>
												</div>

                                                <div v-if="inspection_status_id == 2">
                                                    <div class="row">
                                                        <div class="mb-3 col-md-12">
                                                            <label class="form-label" for="total_hours">Hodômetro/Horímetro:</label>
                                                            <Field type="number" class="form-control" :class="{'is-invalid':errors.total_hours}" name="total_hours" id="total_hours" placeholder=""/>
                                                            <span class="invalid-feedback">{{ errors.total_hours }}</span>
                                                        </div>
												    </div>

                                                    <div class="row">
                                                        <div class="mb-3 col-md-12">
                                                            <label class="form-label" for="is_operational">Operacional:</label>
                                                            <Field as="select" class="form-control" :class="{'is-invalid':errors.is_operational}" name="is_operational" id="is_operational" aria-describedby="is_operational">
                                                                <option value="" selected disabled>Selecionar</option>
                                                                <option value="1" >Sim</option>
                                                                <option value="0" >Não</option>
                                                            </Field>
                                                        </div>
												    </div>

                                                    <div class="row">
                                                        <div class="mb-3 col-md-4">
                                                            <label class="form-label" for="engine_condition">Engine Condition:</label>
                                                            <Field as="select" class="form-control" :class="{'is-invalid':errors.engine_condition}" name="engine_condition" id="engine_condition" aria-describedby="engine_condition">
                                                                <option value="" selected disabled>Selecionar</option>
                                                                <option value="1">New</option>
                                                                <option value="2" >Very Good Condition</option>
                                                                <option value="3" >Good</option>
                                                                <option value="4" >Works with Disabilities</option>
                                                                <option value="5" >Works With Great Disabilities</option>

                                                            </Field>
                                                        </div>
                                                        <div class="mb-3 col-md-8">
                                                            <label class="form-label" for="engine_description">Engine Description:</label>
                                                            <Field type="text" class="form-control" :class="{'is-invalid':errors.engine_description}" name="engine_description" id="engine_description" placeholder=""/>

                                                        </div>
												    </div>

                                                    <div class="row">
                                                        <div class="mb-3 col-md-4">
                                                            <label class="form-label" for="eletrical_system_condition">Eletrical System Condition:</label>
                                                            <Field as="select" class="form-control" :class="{'is-invalid':errors.eletrical_system_condition}" name="eletrical_system_condition" id="eletrical_system_condition" aria-describedby="eletrical_system_condition">
                                                                <option value="" selected disabled>Selecionar</option>
                                                                <option value="1">New</option>
                                                                <option value="2" >Very Good Condition</option>
                                                                <option value="3" >Good</option>
                                                                <option value="4" >Works with Disabilities</option>
                                                                <option value="5" >Works With Great Disabilities</option>

                                                            </Field>
                                                        </div>
                                                        <div class="mb-3 col-md-8">
                                                            <label class="form-label" for="eletrical_system_description">Eletrical System Description:</label>
                                                            <Field type="text" class="form-control" :class="{'is-invalid':errors.eletrical_system_description}" name="eletrical_system_description" id="eletrical_system_description" placeholder=""/>

                                                        </div>
												    </div>

                                                    <div class="row">
                                                        <div class="mb-3 col-md-4">
                                                            <label class="form-label" for="transmission_condition">Transmission Condition:</label>
                                                            <Field as="select" class="form-control" :class="{'is-invalid':errors.transmission_condition}" name="transmission_condition" id="transmission_condition" aria-describedby="transmission_condition">
                                                                <option value="" selected disabled>Selecionar</option>
                                                                <option value="1">New</option>
                                                                <option value="2" >Very Good Condition</option>
                                                                <option value="3" >Good</option>
                                                                <option value="4" >Works with Disabilities</option>
                                                                <option value="5" >Works With Great Disabilities</option>

                                                            </Field>
                                                        </div>
                                                        <div class="mb-3 col-md-8">
                                                            <label class="form-label" for="transmission_description">Transmission Description:</label>
                                                            <Field type="text" class="form-control" :class="{'is-invalid':errors.transmission_description}" name="transmission_description" id="transmission_description" placeholder=""/>

                                                        </div>
												    </div>

                                                    <div class="row">
                                                        <div class="mb-3 col-md-4">
                                                            <label class="form-label" for="control_system_condition">Control System Condition:</label>
                                                            <Field as="select" class="form-control" :class="{'is-invalid':errors.control_system_condition}" name="control_system_condition" id="control_system_condition" aria-describedby="control_system_condition">
                                                                <option value="" selected disabled>Selecionar</option>
                                                                <option value="1">New</option>
                                                                <option value="2" >Very Good Condition</option>
                                                                <option value="3" >Good</option>
                                                                <option value="4" >Works with Disabilities</option>
                                                                <option value="5" >Works With Great Disabilities</option>

                                                            </Field>
                                                        </div>
                                                        <div class="mb-3 col-md-8">
                                                            <label class="form-label" for="control_system_description">Control System Description:</label>
                                                            <Field type="text" class="form-control" :class="{'is-invalid':errors.control_system_description}" name="control_system_description" id="control_system_description" placeholder=""/>

                                                        </div>
												    </div>

                                                    <div class="row">
                                                        <div class="mb-3 col-md-4">
                                                            <label class="form-label" for="structure_condition">Structure Condition:</label>
                                                            <Field as="select" class="form-control" :class="{'is-invalid':errors.structure_condition}"  name="structure_condition" id="structure_condition" aria-describedby="structure_condition">
                                                                <option value="" selected disabled>Selecionar</option>
                                                                <option value="1">New</option>
                                                                <option value="2" >Very Good Condition</option>
                                                                <option value="3" >Good</option>
                                                                <option value="4" >Works with Disabilities</option>
                                                                <option value="5" >Works With Great Disabilities</option>

                                                            </Field>
                                                        </div>
                                                        <div class="mb-3 col-md-8">
                                                            <label class="form-label" for="structure_description">Structure Description:</label>
                                                            <Field type="text" class="form-control" :class="{'is-invalid':errors.structure_description}" name="structure_description" id="structure_description" placeholder=""/>

                                                        </div>
												    </div>

                                                    <div class="row">
                                                        <div class="mb-3 col-md-4">
                                                            <label class="form-label" for="hydraulic_system_condition">Hydraulic System Condition:</label>
                                                            <Field as="select" class="form-control" :class="{'is-invalid':errors.hydraulic_system_condition}"  name="hydraulic_system_condition" id="hydraulic_system_condition" aria-describedby="hydraulic_system_condition">
                                                                <option value="" selected disabled>Selecionar</option>
                                                                <option value="1">New</option>
                                                                <option value="2" >Very Good Condition</option>
                                                                <option value="3" >Good</option>
                                                                <option value="4" >Works with Disabilities</option>
                                                                <option value="5" >Works With Great Disabilities</option>

                                                            </Field>
                                                        </div>
                                                        <div class="mb-3 col-md-8">
                                                            <label class="form-label" for="hydraulic_system_description">Hydraulic System Description:</label>
                                                            <Field type="text" class="form-control" :class="{'is-invalid':errors.hydraulic_system_description}" name="hydraulic_system_description" id="hydraulic_system_description" placeholder=""/>

                                                        </div>
												    </div>

                                                    <div class="row">
                                                        <div class="mb-3 col-md-4">
                                                            <label class="form-label" for="pneumatic_system_condition">Pneumatic System Condition:</label>
                                                            <Field as="select" class="form-control" :class="{'is-invalid':errors.pneumatic_system_condition}"  name="pneumatic_system_condition" id="pneumatic_system_condition" aria-describedby="pneumatic_system_condition">
                                                                <option value="" selected disabled>Selecionar</option>
                                                                <option value="1">New</option>
                                                                <option value="2" >Very Good Condition</option>
                                                                <option value="3" >Good</option>
                                                                <option value="4" >Works with Disabilities</option>
                                                                <option value="5" >Works With Great Disabilities</option>

                                                            </Field>
                                                        </div>
                                                        <div class="mb-3 col-md-8">
                                                            <label class="form-label" for="pneumatic_system_description">Pneumatic System Description:</label>
                                                            <Field type="text" class="form-control" :class="{'is-invalid':errors.pneumatic_system_description}" name="pneumatic_system_description" id="pneumatic_system_description" placeholder=""/>

                                                        </div>
												    </div>

                                                    <div class="row">
                                                        <div class="mb-3 col-md-4">
                                                            <label class="form-label" for="suspension_condition">Suspension Condition:</label>
                                                            <Field as="select" class="form-control" :class="{'is-invalid':errors.suspension_condition}" required name="suspension_condition" id="suspension_condition" aria-describedby="suspension_condition">
                                                                <option value="" selected disabled>Selecionar</option>
                                                                <option value="1">New</option>
                                                                <option value="2" >Very Good Condition</option>
                                                                <option value="3" >Good</option>
                                                                <option value="4" >Works with Disabilities</option>
                                                                <option value="5" >Works With Great Disabilities</option>

                                                            </Field>
                                                        </div>
                                                        <div class="mb-3 col-md-8">
                                                            <label class="form-label" for="suspension_description">Suspension Description:</label>
                                                            <Field type="text" class="form-control" :class="{'is-invalid':errors.suspension_description}" name="suspension_description" id="suspension_description" placeholder=""/>

                                                        </div>
												    </div>

                                                    <div class="row">
                                                        <div class="mb-3 col-md-4">
                                                            <label class="form-label" for="tyres_condition">Tyres Condition:</label>
                                                            <Field as="select" class="form-control" :class="{'is-invalid':errors.tyres_condition}" name="tyres_condition" id="tyres_condition" aria-describedby="tyres_condition">
                                                                <option value="" selected disabled>Selecionar</option>
                                                                <option value="1">New</option>
                                                                <option value="2" >Very Good Condition</option>
                                                                <option value="3" >Good</option>
                                                                <option value="4" >Works with Disabilities</option>
                                                                <option value="5" >Works With Great Disabilities</option>

                                                            </Field>
                                                        </div>
                                                        <div class="mb-3 col-md-8">
                                                            <label class="form-label" for="tyres_description">Tyres Description:</label>
                                                            <Field type="text" class="form-control" :class="{'is-invalid':errors.tyres_description}" name="tyres_description" id="tyres_description" placeholder=""/>

                                                        </div>
												    </div>

                                                    <div class="row">
                                                        <div class="mb-3 col-md-4">
                                                            <label class="form-label" for="blades_condition">Blades Condition:</label>
                                                            <Field as="select" class="form-control" :class="{'is-invalid':errors.blades_condition}" name="blades_condition" id="blades_condition" aria-describedby="blades_condition">
                                                                <option value="" selected disabled>Selecionar</option>
                                                                <option value="1">New</option>
                                                                <option value="2" >Very Good Condition</option>
                                                                <option value="3" >Good</option>
                                                                <option value="4" >Works with Disabilities</option>
                                                                <option value="5" >Works With Great Disabilities</option>

                                                            </Field>
                                                        </div>
                                                        <div class="mb-3 col-md-8">
                                                            <label class="form-label" for="blades_description">Blades Description:</label>
                                                            <Field type="text" class="form-control" :class="{'is-invalid':errors.blades_description}" name="blades_description" id="blades_description" placeholder=""/>

                                                        </div>
												    </div>

                                                    <div class="row">
                                                        <div class="mb-3 col-md-4">
                                                            <label class="form-label" for="cabin_condition">Cabin Condition:</label>
                                                            <Field as="select" class="form-control" :class="{'is-invalid':errors.cabin_condition}" name="cabin_condition" id="cabin_condition" aria-describedby="cabin_condition">
                                                                <option value="" selected disabled>Selecionar</option>
                                                                <option value="1">New</option>
                                                                <option value="2" >Very Good Condition</option>
                                                                <option value="3" >Good</option>
                                                                <option value="4" >Works with Disabilities</option>
                                                                <option value="5" >Works With Great Disabilities</option>

                                                            </Field>
                                                        </div>
                                                        <div class="mb-3 col-md-8">
                                                            <label class="form-label" for="cabin_description">Cabin Description:</label>
                                                            <Field type="text" class="form-control" :class="{'is-invalid':errors.cabin_description}" name="cabin_description" id="cabin_description" placeholder=""/>

                                                        </div>
												    </div>

                                                    <div class="row">
                                                        <div class="mb-3 col-md-4">
                                                            <label class="form-label" for="others_condition">Others Condition:</label>
                                                            <Field as="select" class="form-control" :class="{'is-invalid':errors.others_condition}" name="others_condition" id="others_condition" aria-describedby="others_condition">
                                                                <option value="" selected disabled>Selecionar</option>
                                                                <option value="1">New</option>
                                                                <option value="2" >Very Good Condition</option>
                                                                <option value="3" >Good</option>
                                                                <option value="4" >Works with Disabilities</option>
                                                                <option value="5" >Works With Great Disabilities</option>

                                                            </Field>
                                                        </div>
                                                        <div class="mb-3 col-md-8">
                                                            <label class="form-label" for="others_description">Others Description:</label>
                                                            <Field type="text" class="form-control" :class="{'is-invalid':errors.others_description}" name="others_description" id="others_description" placeholder=""/>

                                                        </div>
												    </div>

                                                    <div class="row">
                                                        <div class="mb-3 col-md-12">
                                                            <label class="form-label" for="rating_unit_condition">Unit Condition:</label>
                                                            <Field as="select" class="form-control" :class="{'is-invalid':errors.rating_unit_condition}"  name="rating_unit_condition" id="rating_unit_condition" aria-describedby="rating_unit_condition">
                                                                <option value="" selected disabled>Selecionar</option>
                                                                <option value="1">New</option>
                                                                <option value="2" >Very Good Condition</option>
                                                                <option value="3" >Good</option>
                                                                <option value="4" >Works with Disabilities</option>
                                                                <option value="5" >Works With Great Disabilities</option>

                                                            </Field>
                                                        </div>
												    </div>

                                                    <div class="row">
                                                        <div class="mb-3 col-md-12">
                                                            <label class="form-label" for="rating_in_operation">In Operation:</label>
                                                            <Field as="select" class="form-control" :class="{'is-invalid':errors.rating_in_operation}"  name="rating_in_operation" id="rating_in_operation" aria-describedby="rating_in_operation">
                                                                <option value="" selected disabled>Selecionar</option>
                                                                <option value="1">Monitor and Follow Maintenance Plans</option>
                                                                <option value="2" >Monitor and Follow Maintenance Plans</option>
                                                                <option value="3" >Needs Minor Repairs (Tightenin)</option>
                                                                <option value="4" >Requires Localized Repairs</option>
                                                                <option value="5" >Needs Complete Repair</option>

                                                            </Field>
                                                        </div>
												    </div>

                                                    <div class="row">
                                                        <div class="mb-3 col-md-12">
                                                            <label class="form-label" for="comments">Comments:</label>
                                                            <Field type="textarea" class="form-control" :class="{'is-invalid':errors.comments}" name="comments" id="comments" placeholder=""/>

                                                        </div>
												    </div>

                                                    <div class="row">
                                                        <div class="mb-3 col-md-12">
                                                            <label class="form-label" for="recommendation_1">Recommendation 1:</label>
                                                            <Field type="textarea" class="form-control" :class="{'is-invalid':errors.recommendation_1}" name="recommendation_1" id="recommendation_1" placeholder=""/>

                                                        </div>
												    </div>

                                                    <div class="row">
                                                        <div class="mb-3 col-md-12">
                                                            <label class="form-label" for="recommendation_2">Recommendation 2:</label>
                                                            <Field type="textarea" class="form-control" :class="{'is-invalid':errors.recommendation_2}" name="recommendation_2" id="recommendation_2" placeholder=""/>

                                                        </div>
												    </div>

                                                    <div class="row">
                                                        <div class="mb-3 col-md-12">
                                                            <label class="form-label" for="recommendation_3">Recommendation 3:</label>
                                                            <Field type="textarea" class="form-control" :class="{'is-invalid':errors.recommendation_3}" name="recommendation_3" id="recommendation_3" placeholder=""/>

                                                        </div>
												    </div>

                                                    <div class="row">
                                                        <div class="mb-3 col-md-12">
                                                            <label class="form-label" for="recommendation_4">Recommendation 4:</label>
                                                            <Field type="textarea" class="form-control" :class="{'is-invalid':errors.recommendation_4}" name="recommendation_4" id="recommendation_4" placeholder=""/>

                                                        </div>
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