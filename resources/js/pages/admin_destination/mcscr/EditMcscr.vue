<script setup>

import axios from 'axios';
import { ref, onMounted, reactive, defineEmits, defineComponent,watch } from "vue";
import moment from 'moment'
import {useToastr} from '../../../toastr';
import {debounce} from 'lodash';
import {Form, Field, FieldArray} from 'vee-validate';
import { useRouter} from "vue-router";
import * as yup from 'yup';
import VueFeather from 'vue-feather';
import { Bootstrap4Pagination } from 'laravel-vue-pagination';


let retrievedData =ref([]);
let loadingButtonSubmit =ref([false]);
let loadingDiv =ref([true]);
const toastr = useToastr();
const router = useRouter();
let self = this;
let currentvalue = ref([]);
let loggedUser = window.user;
const reasonInput = ref(null);
const causeInput = ref(null);
const solutionInput = ref(null);
const consequenceInput = ref(null);
const recommendationInput = ref(null);
let products = ref([]);
let departments = ref([]);
let tools = ref([]);
let reasons =ref([]);
let solutions = ref([]);
let consequences = ref([]);
let recommendations = ref([]);
let causes = ref([]);
let waitingstatuss = ref([]);
let availabilitys = ref([]);
let mcscrstatuses = ref([]);
let malfunctions =ref([]);
let components =ref([]);
let sub_components =ref([]);
let requeststock = ref([])
let requesttechnician = ref([])
let requesttool = ref([])
let component = ref(0);
let sub_component = ref(0);



const schema = yup.object({

  reason: yup.string().required(),
  reason_id: yup.string().required(),

  cause: yup.string().required(),
  cause_id: yup.string().required(),

  solution: yup.string().required(),
  solution_id: yup.string().required(),

  consequence: yup.string().required(),
  consequence_id: yup.string().required(),

  recommendation: yup.string().required(),
  recommendation_id: yup.string().required(),

  opened_by_user_id: yup.string().required(),
  first_observation: yup.string().required(),
  opened_at: yup.string().required(),
  closed_at: yup.string().required(),
  output_forecast: yup.string().required(),
  waiting_status_id:yup.string().required(),
  type_malfunction_id: yup.string().required(),
  equipment_status_id: yup.string().required(),
  mcscr_status_id: yup.string().required(),
  equipment_component_id: yup.string().required(),
  material_cost: yup.number().required(),
  material_labor: yup.number().required(),
  is_rework:yup.string().required(),


  materials: yup.array().of(
    yup.object().shape({
        product_id: yup.string().required(),
        quantity: yup.string().required(),
        obs: yup.string().required(),
      
      })
  ),

  departments: yup.array().of(
    yup.object().shape({
        department_id: yup.string().required(),
        quantity: yup.string().required(),
        obs: yup.string().required(),
      
      })
  ),

  tools: yup.array().of(
    yup.object().shape({
        tool_id: yup.string().required(),
        obs: yup.string().required(),
      
      })
  )

});







const getData = () => {
  axios.get(`/mcscr/+${router.currentRoute.value.params.id}/edit`)
       .then((response)=>{

        loadingDiv.value=false;
        retrievedData.value = response.data.mcscr;
        reasons.value = response.data.reasons
        solutions.value = response.data.solutions
        consequences.value = response.data.consequences
        causes.value = response.data.causes
        recommendations.value = response.data.recommendations
        reasonInput.value = response.data.mcscr.reason;
        causeInput.value = response.data.mcscr.cause;
        solutionInput.value = response.data.mcscr.solution;
        recommendationInput.value = response.data.mcscr.recommendation;
        consequenceInput.value = response.data.mcscr.consequence;
        waitingstatuss.value = response.data.waiting
        availabilitys.value = response.data.availabilities
        mcscrstatuses.value = response.data.mcscrstatuses
        malfunctions.value = response.data.malfunctions
        components.value = response.data.components
        sub_components.value = response.data.sub_components
        products.value = response.data.products
        departments.value = response.data.departments
        tools.value = response.data.tools
        requeststock.value = response.data.requeststock
        requesttechnician.value = response.data.requesttechnician
        requesttool.value = response.data.requesttool

        if(retrievedData.value.equipment_component_id == null){
            component.value = 0
        }else{
            component.value = retrievedData.value.equipment_component_id
        }

        if(retrievedData.value.equipment_sub_component_id == null){
            sub_component.value = 0
        }else{
            sub_component.value = retrievedData.value.equipment_sub_component_id
        }

       

       }).catch(()=>{

        loadingDiv.value=false;

       })
}

const editFunction = (values, actions) => {

  loadingButtonSubmit.value = true;
  axios.patch(`/mcscr/${retrievedData.value.id}`,values).then((response)=>{

    // admins.value.unshift(response.data);
    // $('#createCategory').modal('hide');
    actions.resetForm();
    router.push({ path: '/admin/mcscr' });
    toastr.success('MCSCR editado com sucesso');

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

const getCause = () => {
axios.get(`/auxiliar-create-mcscr-cause`,
  {
    params:{
      query: causeInput.value
    }
  })
   .then((response)=>{
    causes.value = response.data;
    loadingDiv.value=false;
   })
}
watch(causeInput,debounce(()=>{
getCause();
},300));


const getSolution = () => {
axios.get(`/auxiliar-create-mcscr-solution`,
  {
    params:{
      query: solutionInput.value
    }
  })
   .then((response)=>{
    solutions.value = response.data;
    loadingDiv.value=false;
   })
}
watch(solutionInput,debounce(()=>{
getSolution();
},300));


const getConsequence = () => {
axios.get(`/auxiliar-create-mcscr-consequence`,
  {
    params:{
      query: consequenceInput.value
    }
  })
   .then((response)=>{
    consequences.value = response.data;
    loadingDiv.value=false;
   })
}
watch(consequenceInput,debounce(()=>{
getConsequence();
},300));

const getRecommendation = () => {
axios.get(`/auxiliar-create-mcscr-recommendation`,
  {
    params:{
      query: recommendationInput.value
    }
  })
   .then((response)=>{
    recommendations.value = response.data;
    loadingDiv.value=false;
   })
}
watch(recommendationInput,debounce(()=>{
getRecommendation();
},300));


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


onMounted(()=>{
  
  getData();

})
</script>

<template>
    <div v-if="!loadingDiv">

        <h1 class="h3 mb-3">MCSCR </h1>
        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">MCSCR: #{{ retrievedData.id }}</h5>

                                        <router-link to="/admin/mcscr" class="btn btn-pill btn-primary mt-3"><vue-feather type="arrow-left"></vue-feather>Voltar</router-link> 

                                       
								    </div>
                                    
                                    <div class="card-body">
                                          
                                        <div class="row">
                                            <div class="col-xl-12 col-xxl-12 d-flex">
                                                <div class="w-100">
                                                    <Form @submit="editFunction" :validation-schema="schema" v-slot="{ errors }">
                                                    
                                                        <!-- <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="name">Nome</label>
                                                                <Field type="text" class="form-control" :class="{'is-invalid':errors.name}" name="name" v-model="retrievedData.name" id="name" placeholder="Nome"/>
                                                                <span class="invalid-feedback">{{ errors.name }}</span>
                                                            </div>
                                                        </div> -->
                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="opened_by_user_id">Equipamento:</label>
                                                                <span class="form-control">{{retrievedData.equipment.name == null ? 'N/A': retrievedData.equipment.ref+'/'+retrievedData.equipment.name}}</span>
                                                                
                                                            </div>
                                                        </div>
                                                       
                                                        <div class="card-body mb-5" style="background-color: rgb(232, 232, 232);">
                                                            <div class="row">
                                                                <div class="mb-3 col-md-12">
                                                                    <label class="form-label" for="reason">Motivo:</label>
                                                                    <!-- <Field as="textarea" class="form-control" :class="{'is-invalid':errors.reason}" name="reason" id="reason" placeholder="Motivo"/> -->
                                                                    <Field type="text" class="form-control" :class="{'is-invalid':errors.reason}" name="reason"  v-model="reasonInput" id="reason" placeholder="Motivo"/>
                                                                    <span class="invalid-feedback">{{ errors.reason }}</span>
                                                                </div>
                                                                <p>{{ reasons.length }} Resultados</p>
                                                                <div class="mb-3 col-md-12">
                                                                    <!-- <Field as="textarea" class="form-control" :class="{'is-invalid':errors.reason}" name="reason" id="reason" placeholder="Motivo"/> -->
                                                                    <Field as="select" class="form-control" :class="{'is-invalid':errors.reason_id}"  name="reason_id" v-model="retrievedData.reason_id" id="reason_id" aria-describedby="reason_id">
                                                                        <option value="" selected disabled>Selecionar</option>
                                                                        <option value="0" selected>Nenhum item</option>
                                                                        <option v-for="reason in reasons" :key="reason.id" :value="reason.id">{{ reason.name }}</option>
                                                                    </Field>
                                                                    <span class="invalid-feedback">{{ errors.reason_id }}</span>
                                                                    
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="card-body mb-5" style="background-color: rgb(232, 232, 232);">
                                                            <div class="row">
                                                                <div class="mb-3 col-md-12">
                                                                    <label class="form-label" for="cause">Causa:</label>
                                                                    
                                                                    <Field type="text" class="form-control" :class="{'is-invalid':errors.cause}" name="cause"  v-model="causeInput" id="cause" placeholder="Causa"/>
                                                                    <span class="invalid-feedback">{{ errors.cause }}</span>
                                                                </div>
                                                                <p>{{ causes.length }} Resultados</p>
                                                                <div class="mb-3 col-md-12">
                                                                    
                                                                    <Field as="select" class="form-control" :class="{'is-invalid':errors.cause_id}"  name="cause_id" v-model="retrievedData.cause_id" id="cause_id" aria-describedby="cause_id">
                                                                        <option value="" selected disabled>Selecionar</option>
                                                                        <option value="0" selected>Nenhum item</option>
                                                                        <option v-for="cause in causes" :key="cause.id" :value="cause.id">{{ cause.name }}</option>
                                                                    </Field>
                                                                    <span class="invalid-feedback">{{ errors.cause_id }}</span>
                                                                    
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="card-body mb-5" style="background-color: rgb(232, 232, 232);">
                                                            <div class="row">
                                                                <div class="mb-3 col-md-12">
                                                                    <label class="form-label" for="solution">Solução:</label>
                                                                    
                                                                    <Field type="text" class="form-control" :class="{'is-invalid':errors.solution}" name="solution"  v-model="solutionInput" id="solution" placeholder="Solução"/>
                                                                    <span class="invalid-feedback">{{ errors.solution }}</span>
                                                                </div>
                                                                <p>{{ solutions.length }} Resultados</p>
                                                                <div class="mb-3 col-md-12">
                                                                    
                                                                    <Field as="select" class="form-control" :class="{'is-invalid':errors.solution_id}"  name="solution_id" v-model="retrievedData.solution_id" id="solution_id" aria-describedby="solution_id">
                                                                        <option value="" selected disabled>Selecionar</option>
                                                                        <option value="0" selected>Nenhum item</option>
                                                                        <option v-for="solution in solutions" :key="solution.id" :value="solution.id">{{ solution.name }}</option>
                                                                    </Field>
                                                                    <span class="invalid-feedback">{{ errors.solution_id }}</span>
                                                                    
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="card-body mb-5" style="background-color: rgb(232, 232, 232);">
                                                            <div class="row">
                                                                <div class="mb-3 col-md-12">
                                                                    <label class="form-label" for="consequence">Consequência:</label>
                                                                    
                                                                    <Field type="text" class="form-control" :class="{'is-invalid':errors.consequence}" name="consequence"  v-model="consequenceInput" id="consequence" placeholder="Consequência"/>
                                                                    <span class="invalid-feedback">{{ errors.consequence }}</span>
                                                                </div>
                                                                <p>{{ consequences.length }} Resultados</p>
                                                                <div class="mb-3 col-md-12">
                                                                    
                                                                    <Field as="select" class="form-control" :class="{'is-invalid':errors.consequence_id}"  name="consequence_id" v-model="retrievedData.consequence_id" id="consequence_id" aria-describedby="consequence_id">
                                                                        <option value="" selected disabled>Selecionar</option>
                                                                        <option value="0" selected>Nenhum item</option>
                                                                        <option v-for="consequence in consequences" :key="consequence.id" :value="consequence.id">{{ consequence.name }}</option>
                                                                    </Field>
                                                                    <span class="invalid-feedback">{{ errors.consequence_id }}</span>
                                                                    
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="card-body mb-5" style="background-color: rgb(232, 232, 232);">
                                                            <div class="row">
                                                                <div class="mb-3 col-md-12">
                                                                    <label class="form-label" for="recommendation">Recomendações:</label>
                                                                    
                                                                    <Field type="text" class="form-control" :class="{'is-invalid':errors.recommendation}" name="recommendation"  v-model="recommendationInput" id="recommendation" placeholder="Recomendação"/>
                                                                    <span class="invalid-feedback">{{ errors.recommendation }}</span>
                                                                </div>
                                                                <p>{{ recommendations.length }} Resultados</p>
                                                                <div class="mb-3 col-md-12">
                                                                    
                                                                    <Field as="select" class="form-control" :class="{'is-invalid':errors.recommendation_id}"  name="recommendation_id" v-model="retrievedData.recommendation_id" id="recommendation_id" aria-describedby="recommendation_id">
                                                                        <option value="" selected disabled>Selecionar</option>
                                                                        <option value="0" selected>Nenhum item</option>
                                                                        <option v-for="recommendation in recommendations" :key="recommendation.id" :value="recommendation.id">{{ recommendation.name }}</option>
                                                                    </Field>
                                                                    <span class="invalid-feedback">{{ errors.recommendation_id }}</span>
                                                                    
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="mb-3 col-md-12">
                                                            <label class="form-label" for="first_observation">Primeira Observação:</label>
                                                            <Field as="textarea" class="form-control" :class="{'is-invalid':errors.first_observation}" v-model="retrievedData.first_observation" name="first_observation" id="first_observation" placeholder="Primeria Observação"/>
                                                            <span class="invalid-feedback">{{ errors.first_observation }}</span>
												        </div>

                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label">Criado/Aberto por:</label>
                                                                <span class="form-control">{{retrievedData.opened_by_user == null ? 'N/A': retrievedData.opened_by_user.firstName+' '+retrievedData.opened_by_user.lastName}}</span>
                                                                
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label">Terminado por:</label>
                                                                <span class="form-control">{{retrievedData.closed_by_user == null ? 'N/A': retrievedData.closed_by_user.name}}</span>
                                                                <!-- <Field type="text" class="form-control"  v-model="{{ retrievedData.closed_by_user == null ? 'N/A': retrievedData.closed_by_user.name}}"  readonly name="user_system"  placeholder="Criado por"/> -->
                                                               
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="opened_at">Aberto às:</label>
                                                                <Field type="datetime-local" class="form-control" :class="{'is-invalid':errors.opened_at}" v-model="retrievedData.opened_at" name="opened_at" id="opened_at" placeholder="Aberto às" readonly required/>
                                                                <span class="invalid-feedback">{{ errors.opened_at }}</span>
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="closed_at">Fechado às:</label>
                                                                <Field type="datetime-local" class="form-control" :class="{'is-invalid':errors.closed_at}" v-model="retrievedData.closed_at" name="closed_at" id="closed_at" placeholder="Aberto às" required/>
                                                                <span class="invalid-feedback">{{ errors.closed_at }}</span>
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="output_forecast">Previsão de saída:</label>
                                                                <Field type="datetime-local" class="form-control" :class="{'is-invalid':errors.output_forecast}" v-model="retrievedData.output_forecast" name="output_forecast" id="output_forecast" placeholder="Previsão de saída" required/>
                                                                <span class="invalid-feedback">{{ errors.output_forecast }}</span>
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="waiting_status_id">Motivo de Espera (Paralização) :</label>
                                                                <Field as="select" class="form-control" :class="{'is-invalid':errors.waiting_status_id}" v-model="retrievedData.waiting_status_id" name="waiting_status_id" id="waiting_status_id" aria-describedby="waiting_status_id">
                                                                    <option value="" disabled selected>Selecionar</option>
                                                                    <option v-for="waitingstatus in waitingstatuss" :key="waitingstatus.id" :value="waitingstatus.id">{{ waitingstatus.name }}</option>
                                                                </Field>
                                                                <span class="invalid-feedback">{{ errors.waiting_status_id }}</span>
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="type_malfunction_id">Tipo de avaria:</label>
                                                                <Field as="select" class="form-control" :class="{'is-invalid':errors.type_malfunction_id}" v-model="retrievedData.type_malfunction_id" name="type_malfunction_id" id="type_malfunction_id" aria-describedby="type_malfunction_id">
                                                                    <option value="" disabled selected>Selecionar</option>
                                                                    <option v-for="malfunction in malfunctions" :key="malfunction.id" :value="malfunction.id">{{ malfunction.name }}</option>
                                                                </Field>
                                                                <span class="invalid-feedback">{{ errors.type_malfunction_id }}</span>
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="distance">Hodômetro/Horímetro:</label>
                                                                <Field type="number" class="form-control" :class="{'is-invalid':errors.distance}" name="distance" id="distance" v-model="retrievedData.distance" placeholder="" required/>
                                                                <span class="invalid-feedback">{{ errors.distance }}</span>
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="is_rework">Retrabalho:</label>
                                                                <Field as="select" class="form-control" :class="{'is-invalid':errors.is_rework}" v-model="retrievedData.is_rework" name="is_rework" id="is_rework" aria-describedby="is_rework">
                                                                    <option value="" disabled selected>Selecionar</option>
                                                                    <option value="0" >Não</option>
                                                                    <option value="1" >Sim</option>
                                                                    
                                                                </Field>
                                                                <span class="invalid-feedback">{{ errors.is_rework }}</span>
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="equipment_component_id">Componentes:</label>
                                                                <Field as="select" class="form-control" :class="{'is-invalid':errors.equipment_component_id}" v-model="component" name="equipment_component_id" id="equipment_component_id" aria-describedby="equipment_component_id" @change="getSubComponent(component)">
                                                                    <option value="" disabled selected>Selecionar</option>
                                                                    <option value="0" >Todo Equipamento/Ativo</option>
                                                                    <option v-for="component in components" :key="component.id" :value="component.id">{{ component.name }}</option>
                                                                </Field>
                                                                <span class="invalid-feedback">{{ errors.equipment_component_id }}</span>
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="equipment_sub_component_id">SubComponentes:</label>
                                                                <Field as="select" class="form-control" :class="{'is-invalid':errors.equipment_sub_component_id}" v-model="sub_component" name="equipment_sub_component_id" id="equipment_sub_component_id" aria-describedby="equipment_sub_component_id">
                                                                    <option value="" disabled selected>Selecionar</option>
                                                                    <option value="0" >Todo Componente</option>
                                                                    <option v-for="sub_component in sub_components" :key="sub_component.id" :value="sub_component.id">{{ sub_component.name }}</option>
                                                                </Field>
                                                                <span class="invalid-feedback">{{ errors.equipment_sub_component_id }}</span>
                                                            </div>
                                                        </div>



                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="equipment_status_id">Disponibilidade do Equipamento:</label>
                                                                <Field as="select" class="form-control" :class="{'is-invalid':errors.equipment_status_id}" v-model="retrievedData.equipment.equipment_status_id"  name="equipment_status_id" id="equipment_status_id" aria-describedby="equipment_status_id">
                                                                    <option value="" disabled selected>Selecionar</option>
                                                                    <option disabled v-for="availability in availabilitys" :key="availability.id" :value="availability.id">{{ availability.name }} / {{ availability.mobilized }}</option>
                                                                </Field>
                                                                <span class="invalid-feedback">{{ errors.equipment_status_id }}</span>
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="material_labor">Custo de Mão de Obra:</label>
                                                                <Field type="number" class="form-control" :class="{'is-invalid':errors.material_labor}" v-model="retrievedData.material_labor" name="material_labor" id="material_labor" required/>
                                                                <span class="invalid-feedback">{{ errors.material_labor }}</span>
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="material_cost">Custo de Material:</label>
                                                                <Field type="number" class="form-control" :class="{'is-invalid':errors.material_cost}" v-model="retrievedData.material_cost" name="material_cost" id="material_cost" required/>
                                                                <span class="invalid-feedback">{{ errors.material_cost }}</span>
                                                            </div>
                                                        </div>

                                                        <hr>
                                                        <div class="row">
                                                            <FieldArray class="form-control" name="materials" v-slot="{ fields, push, remove }">
                                                                <fieldset class="InputGroup" v-for="(field, idx) in fields" :key="field.key">
                                                                        <div class="row">
                                                                            <div class="mb-3 col-md-3">
                                                                                <label class="form-label">Produto:</label>
                                                                                <Field as="select" class="form-control" :class="{'is-invalid':errors.product_id}"  :name="`materials[${idx}].product_id`" id="product_id" aria-describedby="product_id">
                                                                                    <option value="" disabled>Selecionar</option>
                                                                                    <option v-for="product in products" :key="product.id" :value="product.id">Produto:{{ product.name }} | Stock Atual: {{ product.quantity }} {{ product.unity.alias }}</option>
                                                                                </Field>
                                                                            
                                                                                <span class="invalid-feedback">{{ errors.date }}</span>
                                                                            </div>
                                                                            <div class="mb-3 col-md-3">
                                                                                <label class="form-label">Quantidade:</label>
                                                                                <Field type="number" class="form-control" :class="{'is-invalid':errors.quantity}" :name="`materials[${idx}].quantity`" id="quantity" placeholder="Quantidade"/>
                                                                                <span class="invalid-feedback">{{ errors.quantity }}</span>
                                                                            </div>
                                                                            <div class="mb-3 col-md-3">
                                                                                <label class="form-label">Observação:</label>
                                                                                <Field type="text" class="form-control" :class="{'is-invalid':errors.obs}" :name="`materials[${idx}].obs`" id="obs" placeholder="Observação"/>
                                                                                <span class="invalid-feedback">{{ errors.obs }}</span>
                                                                            </div>
                                                                            <div class="col-sm-3">
                                                                                <button type="button" class="btn btn-danger mt-4" @click="remove(idx>1 ? idx : 0 )">X</button>
                                                                            </div>
                                                                        </div>
                                                                </fieldset>

                                                                
                                                                <div class="card-body">
                                                                    <button type="button" class="btn btn-info mt-2" @click="push({ name: '' })">Adicionar Material +</button>
                                                                </div>
                                                            </FieldArray>
                                                        </div>
                                                        <h1 class="h3 mb-3">Requisição de Materiais:{{ requeststock.length }}</h1>
                                                            <div class="row" v-for="(request, idx) in requeststock" :key="request.id" >
                                                                <p>#{{ idx+1 }}</p>
                                                                <p><strong>ID Da Requisição:</strong> {{ request.id }}</p>
                                                                <p><strong>Data De Criação:</strong> {{ moment(request.created_at).format('DD-MM-YYYY H:mm')}}</p>
                                                                <p><strong>Criado por:</strong> {{ request.createdbyuser.firstName+' ' +request.createdbyuser.lastName }}</p>
                                                                <p><strong>Aprovado/Reprovado por:</strong> {{ request.approvedbyuser == null ? '-----' : request.approvedbyuser.firstName+' '+request.approvedbyuser.lastName +'('+moment(request.approved_date).format('DD-MM-YYYY H:mm')+')'}}</p>
                                                                <p><strong>Entregue por:</strong> {{ request.deliveredbyuser == null ? '-----' : request.deliveredbyuser.firstName+' '+request.deliveredbyuser.lastName +'('+moment(request.delivered_date).format('DD-MM-YYYY H:mm')+')'}}</p>
                                                                
                                                                <p><strong>Estado:</strong> 
                                                                        <span v-if="request.request_stock_status_id == 1" class="badge bg-warning">
                                                                            {{ request.status.name}}
                                                                        </span>
                                                                        <span v-if="request.request_stock_status_id == 2" class="badge bg-success">
                                                                            {{ request.status.name}}
                                                                        </span>
                                                                        <span v-if="request.request_stock_status_id == 3" class="badge bg-danger">
                                                                            {{ request.status.name}}
                                                                        </span>
                                                                        <span v-if="request.request_stock_status_id == 4" class="badge bg-info">
                                                                            {{ request.status.name}}
                                                                        </span>      
                                                                </p>
                                                                <div class="mb-3 col-md-12">
                                                                    <label class="form-label" for="distance">Materiais Requisitados na Requisição #{{ request.id }}:</label>
                                                                    <div class="table-responsive">
                                                                        <table class="table table-striped">
                                                                            <thead>
                                                                                <tr>
                                                                                    <th>#</th>
                                                                                    <th>ID da Requisição</th>
                                                                                    <th>Material</th>
                                                                                    <th>Quantidade Requisitada</th>
                                                                                    <th>Quantidade Entregue</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                <tr v-for="(material, index) in request.requestitens" :key="material.id">
                                                                                    <td>{{ index+1 }}</td>
                                                                                    <td>{{ request.id }}</td>
                                                                                    <td>{{ material.product.name }}</td>
                                                                                    <td>{{ material.quantity }}</td>
                                                                                    <td>{{ material.delivered_quantity }}</td>

                                                                                    
                                                                                </tr>
                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                                <hr>
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
                                                                            <div class="mb-3 col-md-3">
                                                                                <label class="form-label">Quantidade:</label>
                                                                                <Field type="number" class="form-control" :class="{'is-invalid':errors.quantity}" :name="`departments[${idx}].quantity`" id="quantity" placeholder="Quantidade"/>
                                                                                <span class="invalid-feedback">{{ errors.quantity }}</span>
                                                                            </div>
                                                                        
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
                                                
                                                
                                                <h1 class="h3 mb-3">Requisição de Técnicos: {{ requesttechnician.length }}</h1>
                                                <div class="row" v-for="(requesttech, idx) in requesttechnician" :key="requesttech.id" >
                                                    <p>#{{ idx+1 }}</p>
                                                    <p><strong>ID Da Requisição:</strong> {{ requesttech.id }}</p>
                                                    <p><strong>Data De Criação:</strong> {{ moment(requesttech.created_at).format('DD-MM-YYYY H:mm')}}</p>
                                                    <p><strong>Criado por:</strong> {{ requesttech.createdbyuser.firstName+' ' +requesttech.createdbyuser.lastName }}</p>
                                                    <p><strong>Aprovado/Reprovado por:</strong> {{ requesttech.approvedbyuser == null ? '-----' : requesttech.approvedbyuser.firstName+' '+requesttech.approvedbyuser.lastName +'('+moment(requesttech.approved_date).format('DD-MM-YYYY H:mm')+')'}}</p>
                                                    <p><strong>Entregue por:</strong> {{ requesttech.deliveredbyuser == null ? '-----' : requesttech.deliveredbyuser.firstName+' '+requesttech.deliveredbyuser.lastName +'('+moment(requesttech.delivered_date).format('DD-MM-YYYY H:mm')+')'}}</p>
                                                    
                                                    <p><strong>Estado:</strong> 
                                                            <span v-if="requesttech.request_technician_status_id == 1" class="badge bg-warning">
                                                                {{ requesttech.status.name}}
                                                            </span>
                                                            <span v-if="requesttech.request_technician_status_id == 2" class="badge bg-success">
                                                                {{ requesttech.status.name}}
                                                            </span>
                                                            <span v-if="requesttech.request_technician_status_id == 3" class="badge bg-danger">
                                                                {{ requesttech.status.name}}
                                                            </span>
                                                            <span v-if="requesttech.request_technician_status_id == 4" class="badge bg-info">
                                                                {{ requesttech.status.name}}
                                                            </span>      
                                                    </p>
                                                    <div class="mb-3 col-md-12">
                                                        <label class="form-label" for="distance">Departamentos Requisitados: #{{ requesttech.id }}</label>
                                                        <div class="table-responsive">
                                                            <table class="table table-striped">
                                                                <thead>
                                                                    <tr>
                                                                        <th>#</th>
                                                                        <th>ID da Requisição</th>
                                                                        <th>Departamento</th>
                                                                        <th>Técnicos Requisitados</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <tr v-for="(tech, index) in requesttech.requestitens" :key="tech.id">
                                                                        <td>{{ index+1 }}</td>
                                                                        <td>{{ requesttech.id }}</td>
                                                                        <td>{{ tech.department.name }}</td>
                                                                        <td>{{ tech.technician == null ? '------' : tech.technician.name }}</td>
                                                                     
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                                <hr>
                                                <div class="row">
                                                    <FieldArray class="form-control" name="tools" v-slot="{ fields, push, remove }">
                                                        <fieldset class="InputGroup" v-for="(field, idx) in fields" :key="field.key">
                                                                <div class="row">
                                                                    <div class="mb-3 col-md-3">
                                                                        <label class="form-label">Ferramenta:</label>
                                                                        <Field as="select" class="form-control" :class="{'is-invalid':errors.tool_id}"  :name="`tools[${idx}].tool_id`" id="tool_id" aria-describedby="tool_id">
                                                                            <option value="" disabled>Selecionar</option>
                                                                            <option v-for="tool in tools" :key="tool.id" :value="tool.id">{{ tool.name }}</option>
                                                                        </Field>
                                                                       
                                                                        <span class="invalid-feedback">{{ errors.date }}</span>
                                                                    </div>
                                                                    
                                                                    <div class="mb-3 col-md-3">
                                                                        <label class="form-label">Observação:</label>
                                                                        <Field type="text" class="form-control" :class="{'is-invalid':errors.obs}" :name="`tools[${idx}].obs`" id="obs" placeholder="Observação"/>
                                                                        <span class="invalid-feedback">{{ errors.obs }}</span>
                                                                    </div>
                                                                    <div class="col-sm-3">
                                                                        <button type="button" class="btn btn-danger mt-4" @click="remove(idx>1 ? idx : 0 )">X</button>
                                                                    </div>
                                                                </div>
                                                        </fieldset>

                                                        
                                                        <div class="card-body">
                                                            <button type="button" class="btn btn-info mt-2" @click="push({ name: '' })">Adicionar Ferramentaria +</button>
                                                        </div>
                                                    </FieldArray>
                                                </div>

                                                <h1 class="h3 mb-3">Requisição de Ferramentaria: {{ requesttool.length }}</h1>
                                                <div class="row" v-for="(requesttoolshop, idx) in requesttool" :key="requesttoolshop.id" >
                                                    <p>#{{ idx+1 }}</p>
                                                    <p><strong>ID Da Requisição:</strong> {{ requesttoolshop.id }}</p>
                                                    <p><strong>Data De Criação:</strong> {{ moment(requesttoolshop.created_at).format('DD-MM-YYYY H:mm')}}</p>
                                                    <p><strong>Criado por:</strong> {{ requesttoolshop.createdbyuser.firstName+' ' +requesttoolshop.createdbyuser.lastName }}</p>
                                                    <p><strong>Aprovado/Reprovado por:</strong> {{ requesttoolshop.approvedbyuser == null ? '-----' : requesttoolshop.approvedbyuser.firstName+' '+requesttoolshop.approvedbyuser.lastName +'('+moment(requesttoolshop.approved_date).format('DD-MM-YYYY H:mm')+')'}}</p>
                                                    <p><strong>Entregue por:</strong> {{ requesttoolshop.deliveredbyuser == null ? '-----' : requesttoolshop.deliveredbyuser.firstName+' '+requesttoolshop.deliveredbyuser.lastName +'('+moment(requesttoolshop.delivered_date).format('DD-MM-YYYY H:mm')+')'}}</p>
                                                    
                                                    <p><strong>Estado:</strong> 
                                                            <span v-if="requesttoolshop.request_tool_status_id == 1" class="badge bg-warning">
                                                                {{ requesttoolshop.status.name}}
                                                            </span>
                                                            <span v-if="requesttoolshop.request_tool_status_id == 2" class="badge bg-success">
                                                                {{ requesttoolshop.status.name}}
                                                            </span>
                                                            <span v-if="requesttoolshop.request_tool_status_id == 3" class="badge bg-danger">
                                                                {{ requesttoolshop.status.name}}
                                                            </span>
                                                            <span v-if="requesttoolshop.request_tool_status_id == 4" class="badge bg-info">
                                                                {{ requesttoolshop.status.name}}
                                                            </span>      
                                                    </p>
                                                    <div class="mb-3 col-md-12">
                                                        <label class="form-label" for="distance">Ferramentarias Requisitadas: #{{ requesttoolshop.id }}</label>
                                                        <div class="table-responsive">
                                                            <table class="table table-striped">
                                                                <thead>
                                                                    <tr>
                                                                        <th>#</th>
                                                                        <th>ID da Requisição</th>
                                                                        <th>Ferramentaria</th>
                                                                        <th>Código</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <tr v-for="(toolshop, index) in requesttoolshop.requestitens" :key="toolshop.id">
                                                                        <td>{{ index+1 }}</td>
                                                                        <td>{{ requesttoolshop.id }}</td>
                                                                        <td>{{ toolshop.tool.name }}</td>
                                                                        <td>{{ toolshop.tool.code}}</td>
                                                                     
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>




                                               
                                                <hr>
                                                <!-- fim -->

                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="mcscr_status_id">Estado do MCSCR:</label>
                                                                <Field as="select" class="form-control" :class="{'is-invalid':errors.mcscr_status_id}" v-model="retrievedData.mcscr_status_id"  name="mcscr_status_id" id="mcscr_status_id" aria-describedby="mcscr_status_id">
                                                                    <option value="" disabled selected>Selecionar</option>
                                                                    <option v-for="mcscrstatus in mcscrstatuses" :key="mcscrstatus.id" :value="mcscrstatus.id">{{ mcscrstatus.name }}</option>
                                                                </Field>
                                                                <span class="invalid-feedback">{{ errors.mcscr_status_id }}</span>
                                                            </div>
                                                        </div>

                                                        <Field type="hidden" class="form-control" :class="{'is-invalid':errors.opened_by_user_id}" name="opened_by_user_id" id="opened_by_user_id" v-model="retrievedData.opened_by_user_id" />
                                                        <Field type="hidden" class="form-control" :class="{'is-invalid':errors.closed_by_user_id}" name="closed_by_user_id" id="closed_by_user_id" v-model="loggedUser.id" />
												

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