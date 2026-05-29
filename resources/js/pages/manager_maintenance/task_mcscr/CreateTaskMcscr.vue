<script setup>

import {onMounted, ref, reactive,watch} from 'vue';
import axios from 'axios';
import {useToastr} from '../../../toastr';
import {Form, Field,FieldArray} from 'vee-validate';
import * as yup from 'yup';
import { useRouter } from "vue-router";
import moment from 'moment'
import {debounce} from 'lodash';
import { Bootstrap4Pagination } from 'laravel-vue-pagination';
import VueFeather from 'vue-feather';

const loading = ref(false);
const toastr = useToastr();
const loadingDiv = ref(false);
const loadingTask = ref(false);
let currentvalue = ref([]);
let type_equipments =ref([]);
let reasons =ref([]);
let equipments =ref([]);
let taskplans =ref([]);
let subtasks = ref([]);
let materials = ref([]);
let departments = ref([]);
let malfunctions =ref([]);
let tasks =ref([]);
let distance_name = ref('N/A');
let loggedUser = window.user;
let name = loggedUser.firstName+' '+loggedUser.lastName+' / '+loggedUser.email;
const reasonInput = ref(null);
let initalstock = 0;
let selectedrow = 1;
let initaldepartment = 0;


let type_equipment_id_to_equipment = ref(0);
let equipment_id_to_task = ref(0);

let equipment_task_plan_id = ref(0);
const schema = yup.object({
    
  type_equipment_id: yup.string().required(),
  equipment_id: yup.string().required(),
  task_plan_task_id: yup.string().required(),
  schedule_for: yup.string().required(),
  //closed_at: yup.string().required(),
  observation: yup.string().required(),

  subtasks: yup.array().of(
    yup.object().shape({
        name: yup.string().required(),
      })
  ),

  materials: yup.array().of(
    yup.object().shape({
        id: yup.string().required(),
        quantity: yup.string().required(),
      })
  )

 
});
let self = this;
const router = useRouter();

const createRecordFunction = (values, actions) => {

 
    currentvalue.value = {values};
    loading.value = true;
    const arr = Array.from(values)
    axios.post('/taskmcscr',values).then((response)=>{

    // categories.value.unshift(response.data);
    // $('#createCategory').modal('hide');
    actions.resetForm();
    router.push({ path: '/manager/maintenance/taskmcscr' });
    toastr.success(response.data.message);
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
      materials.value = response.data.materials
      departments.value = response.data.departments
      
      loadingDiv.value=false;

     


     })
     .catch((error)=>{
      toastr.error(error);
      router.push({ path: '/manager/maintenance/taskmcscr' });
     })
}

const getEquipment = (typeequipment) => {

axios.get(`/auxiliar-create-mcscr/${typeequipment}`)
   .then((response)=>{

    equipments.value = response.data.equipments;
   
   })
   .catch((error)=>{
    toastr.error(error);
    router.push({ path: '/manager/maintenance/taskmcscr' });
   })


}

const getPlanTask = (equipment) => {


axios.get(`/auxiliar-create-mcscr-plantask/${equipment}`)
   .then((response)=>{
    taskplans.value = response.data.taskplantasks;
    distance_name.value = response.data.distance_control;
   })
   .catch((error)=>{
    toastr.error(error);
    router.push({ path: '/manager/maintenance/taskmcscr' });
   })
}

const getSubTask = (taskplanid)=>{
    loadingTask.value = true;
    axios.get(`/auxiliar-create-mcscr-subtask/${taskplanid}`)
   .then((response)=>{
    loadingTask.value = false;
    subtasks.value = response.data.subtasks;
    materials.value = response.data.materials;
    departments.value = response.data.departments
   })
   .catch((error)=>{
    loadingTask.value = false;
    toastr.error(error);
    router.push({ path: '/manager/maintenance/taskmcscr' });
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
        <h1 class="h3 mb-3">Atividades</h1>
        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Formulário criação das Atividades de Inspeção do sistema.</h5>
                                        <!-- <h5 class="card-title">Ao submeter o MCSCR, o equipamento estará indisponível até o MCSCR for terminado.</h5> -->


                                        <router-link to="/manager/maintenance/taskmcscr" class="btn btn-pill btn-primary mt-3"><vue-feather type="arrow-left"></vue-feather>Voltar</router-link> 

                                       
								    </div>
                                    
                                    <div class="card-body">
                                        
                                            <Form @submit="createRecordFunction" :validation-schema="schema" v-slot="{ errors }">

                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="type_equipment_id">Tipo de Equipamento/Ativos: {{ type_equipments.length }} Encontrados</label>
														<Field as="select" class="form-control" :class="{'is-invalid':errors.type_equipment_id}"  name="type_equipment_id" id="type_equipment_id" aria-describedby="type_equipment_id" @change="getEquipment(type_equipment_id_to_equipment)" v-model="type_equipment_id_to_equipment">
                                                            <option value="" disabled selected>Selecionar</option>
                                                            <option v-for="type_equipment in type_equipments" :key="type_equipment.id" :value="type_equipment.id">{{ type_equipment.name }}</option>
                                                        </Field>
                                                        <span class="invalid-feedback">{{ errors.type_equipment_id }}</span>
													</div>
												</div>


                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="equipment_id">Equipamentos/Ativos: {{ equipments.length }} Encontrados</label>
														<Field as="select" class="form-control" :class="{'is-invalid':errors.equipment_id}"  name="equipment_id" id="equipment_id" aria-describedby="equipment_id" @change="getPlanTask(equipment_id_to_task)" v-model="equipment_id_to_task">
                                                            <option value="" disabled selected>Selecionar</option>
                                                            <option v-for="equipment in equipments" :key="equipment.id" :value="equipment.id">{{ equipment.ref }} - {{ equipment.name }}</option>
                                                        </Field>
                                                        <span class="invalid-feedback">{{ errors.equipment_id }}</span>
													</div>
												</div>

                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="task_plan_task_id">Planos de Atividade para o equipamento: {{ taskplans.length }} Encontrados</label>
														<Field as="select" class="form-control" :class="{'is-invalid':errors.task_plan_task_id}"  name="task_plan_task_id" id="task_plan_task_id" aria-describedby="task_plan_task_id" @change="getSubTask(equipment_task_plan_id)" v-model="equipment_task_plan_id">
                                                            <option value="" disabled selected>Selecionar</option>
                                                            <option v-for="taskplan in taskplans" :key="taskplan.id" :value="taskplan.id">{{ taskplan.name }}</option>
                                                        </Field>
                                                        <span class="invalid-feedback">{{ errors.equipment_id }}</span>
													</div>
												</div>

                                                <div class="mt-2">
                                                    <div class="card-body">
                                                        <div>
                                                            <div class="row">
                                                                
                                                                <h3 class="text-center">
                                                                    <div v-if="loadingTask" class="spinner-border spinner-border-sm" role="status"></div>
                                                                    Escolhe o equipamento para visualizar as SubAtividades, Materiais e Técnicos
                                                                </h3>
                                                                
                                                            </div>

                                                            <FieldArray class="form-control" name="subtasks">
                                                                <fieldset class="InputGroup" v-for="(subtask, idx) in subtasks" :key="subtask.key">
                                                                    <div class="mb-3 col-md-12">
                                                                        <label class="form-label">#{{ idx+1 }}: {{subtask.name}}</label>
                                                                        <Field as="select" :name="`subtasks[${idx}].name`" class="form-control" v-if="subtask.type_sub_task_id==1" v-model="selectedrow">
                                                                            <option value="1">Sim</option>
                                                                            <option value="0" >Não</option>

                                                                        </Field> 

                                                                        <Field as="select" :name="`subtasks[${idx}].name`" class="form-control" v-if="subtask.type_sub_task_id==2" v-model="selectedrow">
                                                                            <option value="2">Excelente</option>
                                                                            <option value="1">Bom</option>
                                                                            <option value="0">Mau</option>
                                                                        </Field>  

                                                                        <Field type="text" class="form-control" :name="`subtasks[${idx}].name`" v-if="subtask.type_sub_task_id==3" v-model="selectedrow"/>
                                                                        <Field type="hidden" class="form-control" :name="`subtasks[${idx}].subtask_id`" v-model="subtask.id"/>
                                                                        <span class="invalid-feedback">{{ errors.subtasks }}</span>
                                                                    </div>
                                                                </fieldset>
                                                            </FieldArray>
                                                        </div>

                                                        <div class="row" >
                                                            <!-- <FieldArray class="form-control" name="materials">
                                                                <hr> -->
                                                                <table class="table table-striped">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>#</th>
                                                                            <th>Material</th>
                                                                            <th>Quantidade Esperada</th>
                                                                            <th>Quantidade A Requisitar</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <FieldArray class="form-control" name="materials">
                                                                            <tr v-for="(material, idx) in materials" :key="material.key">
                                                                                <td> {{ idx+1 }} </td>
                                                                                <td> {{material.product == null ? material.product_name : material.product.name}} </td>
                                                                                <td> {{ material.quantity }}</td>
                                                                                <td>
                                                                                    <fieldset>
                                                                                        <Field type="number" class="form-control" :name="`materials[${idx}].quantity`" :id="`materials[${idx}].quantity`" placeholder="Quantidade a Requisitar" v-model="initalstock" required/>
                                                                                        <Field type="hidden" class="form-control" :name="`materials[${idx}].id`" :id="`materials[${idx}].id`" v-model="material.id" placeholder="Contado"/>
                                                                                        <Field type="hidden" class="form-control" :name="`materials[${idx}].product_id`" :id="`materials[${idx}].product_id`" v-model="material.product.id" placeholder="Contado"/>
                                                                                    </fieldset>
                                                                                </td>
                                                                            </tr>
                                                                        </FieldArray>
                                                                    </tbody>
                                                                </table>
                                                        </div>

                                                        <div class="row" >
                                                            <!-- <FieldArray class="form-control" name="materials">
                                                                <hr> -->
                                                                <table class="table table-striped">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>#</th>
                                                                            <th>Departamento</th>
                                                                            <th>Quantidade Esperada</th>
                                                                            <th>Quantidade A Requisitar</th>
                                                                            
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <FieldArray class="form-control" name="departments">
                                                                            <tr v-for="(department, idx) in departments" :key="department.key">
                                                                                <td> {{ idx+1 }} </td>
                                                                                <td> {{department.department == null ? department.department_name : department.department.name}} </td>
                                                                                <td> {{ department.quantity }}</td>
                                                                                <td>
                                                                                    <fieldset>
                                                                                        <Field type="number" class="form-control" :name="`departments[${idx}].quantity`" :id="`departments[${idx}].quantity`" placeholder="Quantidade a Requisitar" v-model="initaldepartment" required/>
                                                                                        <Field type="hidden" class="form-control" :name="`departments[${idx}].id`" :id="`departments[${idx}].id`" v-model="department.id" placeholder="Contado"/>
                                                                                    </fieldset>
                                                                                </td>
                                                                            </tr>
                                                                        </FieldArray>
                                                                    </tbody>
                                                                </table>
                                                        </div>

                                                    </div>
                                                </div>
                                                <hr>

                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="opened_by_user_id">Criado/Aberto por:</label>
														<Field type="text" class="form-control"  v-model="name"  readonly name="user_system"  placeholder="Criado por"/>
                                                        <span class="invalid-feedback">{{ errors.opened_by_user_id }}</span>
													</div>
												</div>

                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="schedule_for">Programado para :</label>
														<Field type="datetime-local" class="form-control" :class="{'is-invalid':errors.schedule_for}" name="schedule_for" id="schedule_for" placeholder="Aberto às" required/>
                                                        <span class="invalid-feedback">{{ errors.schedule_for }}</span>
													</div>
												</div>




                                            <!--<div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="closed_at">Fechado às:</label>
														<Field type="datetime-local" class="form-control" :class="{'is-invalid':errors.closed_at}" name="closed_at" id="closed_at" placeholder="Previsão de saída" required/>
                                                        <span class="invalid-feedback">{{ errors.closed_at }}</span>
													</div>
												</div> -->

                                                <!-- <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="distance">Distância/Horas Atuais({{distance_name}}):</label>
														<Field type="number" class="form-control" :class="{'is-invalid':errors.distance}" name="distance" id="distance" placeholder="" required/>
                                                        <span class="invalid-feedback">{{ errors.distance }}</span>
													</div>
												</div> -->

                                               



                                                <!-- <div class="row">
                                                    <FieldArray class="form-control" name="materials" v-slot="{ fields, push, remove }">
                                                        <fieldset class="InputGroup" v-for="(field, idx) in fields" :key="field.key">
                                                                <div class="row">
                                                                    <div class="mb-3 col-md-3">
                                                                        <label class="form-label" for="date">Data:</label>
                                                                        <Field type="date" class="form-control" :class="{'is-invalid':errors.date}" :name="`materials[${idx}].date`" id="date" placeholder="Data"/>
                                                                        <span class="invalid-feedback">{{ errors.date }}</span>
                                                                    </div>
                                                                    <div class="mb-3 col-md-3">
                                                                        <label class="form-label" for="code">Código:</label>
                                                                        <Field type="text" class="form-control" :class="{'is-invalid':errors.code}" :name="`materials[${idx}].code`" id="code" placeholder="Código"/>
                                                                        <span class="invalid-feedback">{{ errors.code }}</span>
                                                                    </div>
                                                                    <div class="mb-3 col-md-3">
                                                                        <label class="form-label" for="descriptiion">Descrição do Material:</label>
                                                                        <Field type="text" class="form-control" :class="{'is-invalid':errors.descriptiion}" :name="`materials[${idx}].description`" id="descriptiion" placeholder="Descrição do material"/>
                                                                        <span class="invalid-feedback">{{ errors.descriptiion }}</span>
                                                                    </div>
                                                                    <div class="mb-3 col-md-3">
                                                                        <label class="form-label" for="quantity">Quantidade:</label>
                                                                        <Field type="number" class="form-control" :class="{'is-invalid':errors.quantity}" :name="`materials[${idx}].quantity`" id="quantity" placeholder="Quantidade"/>
                                                                        <span class="invalid-feedback">{{ errors.quantity }}</span>
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
                                                </div> -->
                                                




                                                <div class="mb-3 col-md-12">

														<label class="form-label" for="observation">Observação:</label>
														<Field as="textarea" class="form-control" :class="{'is-invalid':errors.observation}" name="observation" id="observation" placeholder="Observação"/>
                                                        <span class="invalid-feedback">{{ errors.observation }}</span>
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