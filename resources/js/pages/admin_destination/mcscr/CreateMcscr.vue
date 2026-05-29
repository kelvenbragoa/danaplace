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
let reasons =ref([]);
let equipments =ref([]);
let components =ref([]);
let sub_components = ref([]);
let malfunctions =ref([]);
let tasks =ref([]);
let loggedUser = window.user;
let name = loggedUser.firstName+' '+loggedUser.lastName+' / '+loggedUser.email;
const reasonInput = ref(null);
var currentDate = new Date();


let type_equipment_id_to_equipment = ref(0);
let equipment_id_to_component = ref(0);
let component_id_to_subcomponent = ref(0);
const schema = yup.object({
    
  reason: yup.string().required(),
  reason_id: yup.string().required(),
  first_observation: yup.string().required(),
//   opened_at: yup.string().required(),
  output_forecast: yup.string().required(),
  type_equipment_id: yup.string().required(),
  equipment_id: yup.string().required(),
  equipment_component_id: yup.string().required(),
  type_malfunction_id: yup.string().required(),
  task_id: yup.string().required(),
  distance: yup.number().min(0).required()

 
});
let self = this;
const router = useRouter();

const createRecordFunction = (values, actions) => {

 
    currentvalue.value = {values};

    loading.value = true;

    const arr = Array.from(values)
    
    axios.post('/mcscr',values).then((response)=>{
    // categories.value.unshift(response.data);
    // $('#createCategory').modal('hide');
    actions.resetForm();
    router.push({ path: '/admin/mcscr' });
    toastr.success('MCSCR criado com sucesso');

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

      type_equipments.value = response.data.type_equipments
      malfunctions.value = response.data.malfunctions
      tasks.value = response.data.tasks
      reasons.value = response.data.reasons
      loadingDiv.value=false;

     


     })
     .catch((error)=>{
      toastr.error(error);
      router.push({ path: '/admin/mcscr' });
     })
}

const getEquipment = (typeequipment) => {

axios.get(`/auxiliar-create-mcscr/${typeequipment}`)
   .then((response)=>{

    equipments.value = response.data.equipments;
   })
   .catch((error)=>{
    toastr.error(error);
    router.push({ path: '/admin/mcscr' });
   })


}

const getComponent = (equipment) => {

axios.get(`/auxiliar-create-mcscr-components/${equipment}`)
   .then((response)=>{

    components.value = response.data.components;
   })
   .catch((error)=>{
    toastr.error(error);
    router.push({ path: '/admin/mcscr' });
   })


}

const getSubComponent = (equipment) => {

axios.get(`/auxiliar-create-mcscr-subcomponents/${equipment}`)
   .then((response)=>{

    sub_components.value = response.data.sub_components;
   })
   .catch((error)=>{
    toastr.error(error);
    router.push({ path: '/admin/mcscr' });
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
        <h1 class="h3 mb-3">MCSCR </h1>
        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Formulário criação dos MCSCR do sistema.</h5>
                                        <h5 class="card-title">Ao submeter o MCSCR, o equipamento estará indisponível até o MCSCR for terminado.</h5>


                                        <router-link to="/admin/mcscr" class="btn btn-pill btn-primary mt-3"><vue-feather type="arrow-left"></vue-feather>Voltar</router-link> 

                                       
								    </div>
                                    
                                    <div class="card-body">
                                        
                                            <Form @submit="createRecordFunction" :validation-schema="schema" v-slot="{ errors }">

												<div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="reason">Motivo:</label>
														<!-- <Field as="textarea" class="form-control" :class="{'is-invalid':errors.reason}" name="reason" id="reason" placeholder="Motivo"/> -->
                                                        <Field type="text" class="form-control" :class="{'is-invalid':errors.reason}" name="reason" id="reason" v-model="reasonInput" placeholder="Motivo"/>
                                                        <span class="invalid-feedback">{{ errors.reason }}</span>
													</div>
                                                    <p>{{ reasons.length }} Resultados</p>
                                                    <div class="mb-3 col-md-12">
														<!-- <Field as="textarea" class="form-control" :class="{'is-invalid':errors.reason}" name="reason" id="reason" placeholder="Motivo"/> -->
                                                        <Field as="select" class="form-control" :class="{'is-invalid':errors.reason_id}"  name="reason_id" id="reason_id" aria-describedby="reason_id">
                                                            <option value="" selected disabled>Selecionar</option>
                                                            <option value="0" selected>Nenhum item</option>
                                                            <option v-for="reason in reasons" :key="reason.id" :value="reason.id">{{ reason.name }}</option>
                                                        </Field>
                                                        <span class="invalid-feedback">{{ errors.reason_id }}</span>
                                                        
													</div>
												</div>


                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="opened_by_user_id">Criado/Aberto por:</label>
														<Field type="text" class="form-control"  v-model="name"  readonly name="user_system"  placeholder="Criado por"/>
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
														<label class="form-label" for="output_forecast">Previsão de saída:</label>
														<Field type="datetime-local" class="form-control" :class="{'is-invalid':errors.output_forecast}" name="output_forecast" id="output_forecast" placeholder="Previsão de saída" required/>
                                                        <span class="invalid-feedback">{{ errors.output_forecast }}</span>
													</div>
												</div>

                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="type_equipment_id">Tipo de Equipamento/Ativos:</label>
														<Field as="select" class="form-control" :class="{'is-invalid':errors.type_equipment_id}"  name="type_equipment_id" id="type_equipment_id" aria-describedby="type_equipment_id" @change="getEquipment(type_equipment_id_to_equipment)" v-model="type_equipment_id_to_equipment">
                                                            <option value="" selected>Selecionar</option>
                                                            <option v-for="type_equipment in type_equipments" :key="type_equipment.id" :value="type_equipment.id">{{ type_equipment.name }}</option>
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
														<label class="form-label" for="equipment_component_id">Componentes:</label>
                                                        <Field as="select" class="form-control" :class="{'is-invalid':errors.equipment_component_id}"  name="equipment_component_id" id="equipment_component_id"  @change="getSubComponent(component_id_to_subcomponent)" v-model="component_id_to_subcomponent">
                                                            <option value="" disabled selected>Selecionar</option>
                                                            <option value="0">Todo Equipamento/Ativo</option>
                                                            <option v-for="component in components" :key="component.id" :value="component.id">{{ component.name }}</option>
                                                        </Field>
                                                        <span class="invalid-feedback">{{ errors.equipment_component_id }}</span>
													</div>
												</div>

                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="equipment_sub_component_id">SubComponentes:</label>
                                                        <Field as="select" class="form-control" :class="{'is-invalid':errors.equipment_sub_component_id}"  name="equipment_sub_component_id" id="equipment_sub_component_id" aria-describedby="equipment_sub_component_id">
                                                            <option value="" disabled selected>Selecionar</option>
                                                            <option value="0">Todo Componente</option>
                                                            <option v-for="sub_component in sub_components" :key="sub_component.id" :value="sub_component.id">{{ sub_component.name }}</option>
                                                        </Field>
                                                        <span class="invalid-feedback">{{ errors.equipment_sub_component_id }}</span>
													</div>
												</div>

                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="type_malfunction_id">Tipo de avaria:</label>
                                                        <Field as="select" class="form-control" :class="{'is-invalid':errors.type_malfunction_id}"  name="type_malfunction_id" id="type_malfunction_id" aria-describedby="type_malfunction_id">
                                                            <option value="" disabled selected>Selecionar</option>
                                                            <option v-for="malfunction in malfunctions" :key="malfunction.id" :value="malfunction.id">{{ malfunction.name }}</option>
                                                        </Field>
                                                        <span class="invalid-feedback">{{ errors.type_malfunction_id }}</span>
													</div>
												</div>

                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="distance">Hodômetro/Horímetro:</label>
														<Field type="number" class="form-control" :class="{'is-invalid':errors.distance}" name="distance" id="distance" placeholder="" required/>
                                                        <span class="invalid-feedback">{{ errors.distance }}</span>
													</div>
												</div>

                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="task_id">Tipo de atividade:</label>
                                                        <Field as="select" class="form-control" :class="{'is-invalid':errors.task_id}"  name="task_id" id="task_id" aria-describedby="task_id">
                                                            <option value="" disabled selected>Selecionar</option>
                                                            <option v-for="task in tasks" :key="task.id" :value="task.id">{{ task.name }}</option>
                                                        </Field>
                                                        <span class="invalid-feedback">{{ errors.task_id }}</span>
													</div>
												</div>
                                                

                                                <div class="mb-3 col-md-12">
														<label class="form-label" for="first_observation">Primeira Observação:</label>
														<Field as="textarea" class="form-control" :class="{'is-invalid':errors.first_observation}" name="first_observation" id="first_observation" placeholder="Primeria Observação"/>
                                                        <span class="invalid-feedback">{{ errors.first_observation }}</span>
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