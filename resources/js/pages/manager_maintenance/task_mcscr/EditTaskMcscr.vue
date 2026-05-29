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


let retrievedData =ref([]);
let taskmcscrstatuses = ref([])
let requeststock = ref([])
let requesttechnician = ref([])
let loadingButtonSubmit =ref([false]);
let loadingDiv =ref([true]);
const toastr = useToastr();
const router = useRouter();
let self = this;
let currentvalue = ref([]);
let loggedUser = window.user;
let name = loggedUser.firstName+' '+loggedUser.lastName+' / '+loggedUser.email;

const schema = yup.object({

    task_mcscr_status_id: yup.string().required(),

});







const getData = () => {
  axios.get(`/taskmcscr/+${router.currentRoute.value.params.id}/edit`)
       .then((response)=>{

        loadingDiv.value=false;
        retrievedData.value = response.data.taskmcscr;
        taskmcscrstatuses = response.data.taskmcscrstatuses;
        requeststock.value =  response.data.requeststock;
        requesttechnician.value = response.data.requesttechnician;
        
       }).catch(()=>{

        loadingDiv.value=false;

       })
}

const editFunction = (values, actions) => {

  loadingButtonSubmit.value = true;
  axios.patch(`/taskmcscr/${retrievedData.value.id}`,values).then((response)=>{

    // admins.value.unshift(response.data);
    // $('#createCategory').modal('hide');
    actions.resetForm();
    router.push({ path: '/manager/maintenance/taskmcscr' });
    toastr.success('Atividade editada com sucesso');

  }).catch((error)=>{

    loadingButtonSubmit.value = false;
    toastr.error('Erro ao adicionar.'+error.response.data.message);
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

        <h1 class="h3 mb-3">Atividades</h1>
        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Formulário edição das Atividades de Inspeção do sistema.</h5>
                                        <!-- <h5 class="card-title">Ao submeter o MCSCR, o equipamento estará indisponível até o MCSCR for terminado.</h5> -->


                                        <router-link to="/manager/maintenance/taskmcscr" class="btn btn-pill btn-primary mt-3"><vue-feather type="arrow-left"></vue-feather>Voltar</router-link> 

                                       
								    </div>
                                    
                                    <div class="card-body">
                                        
                                            <Form @submit="editFunction" :validation-schema="schema" v-slot="{ errors }">

                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="equipment_id">Equipamento/Ativo:</label>
                                                        <span class="form-control">{{ retrievedData.equipment.name}}</span>
													</div>
												</div>

                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="equipment_id">Ref:</label>
                                                        <span class="form-control">{{ retrievedData.equipment.ref}}</span>
													</div>
												</div>

                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="area_id">Área:</label>
														<!-- <Field class="form-control" :class="{'is-invalid':errors.area_id}"  name="area_id" id="area_id" aria-describedby="area_id" v-model="retrievedData.area.name" readonly/> -->
                                                        <span class="form-control">{{ retrievedData.area.name}}</span>
													</div>
												</div>

                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="destination_id">Clientes:</label>
														<!-- <Field class="form-control" :class="{'is-invalid':errors.destination_id}"  name="destination_id" id="destination_id" aria-describedby="destination_id" v-model="retrievedData.destination.name" readonly/>
                                                        <span class="invalid-feedback">{{ errors.destination_id }}</span> -->
                                                        <span class="form-control">{{ retrievedData.destination.name}}</span>
													</div>
												</div>

                                              

                                              
                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" >Programado por / Horas:</label>
                                                        <span class="form-control">{{ retrievedData.schedule_by_user == null ? '------' :  retrievedData.schedule_by_user.firstName +' '+ retrievedData.schedule_by_user.lastName}} / {{ retrievedData.schedule_for == null ? '------' : moment(retrievedData.schedule_for).format('DD-MM-YYYY H:mm') }}</span>
													</div>
												</div>

                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" >Aberto por / Horas:</label>
                                                        <span class="form-control">{{ retrievedData.opened_by_user == null ? '------' :  retrievedData.opened_by_user.firstName +' '+ retrievedData.opened_by_user.lastName}} / {{ retrievedData.opened_at == null ? '------' : moment(retrievedData.opened_at).format('DD-MM-YYYY H:mm')  }}</span>
													</div>
												</div>

                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" >Fechado por / Horas:</label>
                                                        <span class="form-control">{{ retrievedData.closed_by_user == null ? '------' :  retrievedData.closed_by_user.firstName +' '+ retrievedData.closed_by_user.lastName}} / {{ retrievedData.closed_at == null ? '------' : moment(retrievedData.closed_at).format('DD-MM-YYYY H:mm')  }}</span>
													</div>
												</div>

                                                <!-- <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="opened_at">Aberto às:</label>
														<Field type="datetime-local" class="form-control" :class="{'is-invalid':errors.opened_at}" name="opened_at_" id="opened_at" placeholder="Aberto às" v-model="retrievedData.opened_at" readonly required/>
                                                        <span class="invalid-feedback">{{ errors.opened_at }}</span>
													</div>
												</div> -->

                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="task_plan_id">Plano:</label>
														<!-- <Field class="form-control" :class="{'is-invalid':errors.task_plan_id}"  name="task_plan_id" id="task_plan_id" aria-describedby="task_plan_id" v-model="retrievedData.task_plan.name" readonly/>
                                                        <span class="invalid-feedback">{{ errors.task_plan_id }}</span> -->
                                                        <span class="form-control">{{ retrievedData.task_plan.name}}</span>
													</div>
												</div>

                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="task_plan_task_id">Atividade:</label>
														<!-- <Field class="form-control" :class="{'is-invalid':errors.task_plan_task_id}"  name="task_plan_task_id" id="task_plan_task_id" aria-describedby="task_plan_task_id" v-model="retrievedData.task_plan_task.name" readonly/>
                                                        <span class="invalid-feedback">{{ errors.task_plan_task_id }}</span> -->
                                                        <span class="form-control">{{ retrievedData.task_plan_task.name}}</span>
													</div>
												</div>

                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="distance">Distância/Horas Atuais({{retrievedData.equipment.distance_control.name}}):</label>
														<Field type="number" class="form-control" :class="{'is-invalid':errors.distance}" name="distance" id="distance" placeholder="" v-model="retrievedData.distance" required/>
                                                        <span class="invalid-feedback">{{ errors.distance }}</span>
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
                                                <a class="btn btn-primary" data-toggle="collapse" href="#multiCollapseExample1" role="button" aria-expanded="false" aria-controls="multiCollapseExample1">Ver Requisição e Atividades <vue-feather type="eye"></vue-feather></a>

                                                <div class="row">
                                                    <div class="collapse multi-collapse" id="multiCollapseExample1">
                                                        <div class="card card-body">
                                                            <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <h1 class="h3 mb-3">Tarefas: {{ retrievedData.subtasks.length }}</h1>
                                                                <FieldArray class="form-control" name="subtasks">
                                                                    <fieldset class="InputGroup" v-for="(subtask, idx) in retrievedData.subtasks" :key="subtask.key">
                                                                        <div class="mb-3 col-md-12">
                                                                            <label class="form-label">#{{ idx+1 }}: {{subtask.subtask.name}}</label>
                                                                            <Field as="select" :name="`subtasks[${idx}].name`" class="form-control" v-model="subtask.answer" v-if="subtask.subtask.type_sub_task_id==1">
                                                                                <option value="1" >Sim</option>
                                                                                <option value="0" >Não</option>
                                                                            </Field> 

                                                                            <Field as="select" :name="`subtasks[${idx}].name`" class="form-control" v-model="subtask.answer" v-if="subtask.subtask.type_sub_task_id==2">
                                                                                <option value="2">Excelente</option>
                                                                                <option value="1">Bom</option>
                                                                                <option value="0">Mau</option>
                                                                            </Field>  

                                                                            <Field type="text" class="form-control" :name="`subtasks[${idx}].name`" v-model="subtask.answer" v-if="subtask.subtask.type_sub_task_id==3"/>
                                                                            <Field type="hidden" class="form-control" :name="`subtasks[${idx}].subtask_id`" v-model="subtask.id"/>
                                                                            <span class="invalid-feedback">{{ errors.subtasks }}</span>
                                                                        </div>
                                                                    </fieldset>
                                                                </FieldArray>
                                                                <!-- <div class="table-responsive">
                                                                    <table class="table table-striped">
                                                                        <thead>
                                                                            <tr>
                                                                                <th>#</th>
                                                                                <th>Tarefa</th>
                                                                                <th>Resposta</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            <tr v-for="(subtask, index) in retrievedData.subtasks" :key="subtask.id">
                                                                                <td>{{ index+1 }}</td>
                                                                                <td >{{ subtask.subtask.name }}</td>
                                                                                <td v-if="subtask.subtask.type_sub_task_id == 1"><span v-if="subtask.answer == 1">Sim</span> <span v-else>Não</span></td>
                                                                                <td v-if="subtask.subtask.type_sub_task_id == 2"><span v-if="subtask.answer == 0">Mau</span> <span v-if="subtask.answer == 1">Bom</span> <span v-if="subtask.answer == 2">Excelente</span></td>
                                                                                <td v-if="subtask.subtask.type_sub_task_id == 3">{{subtask.answer}}</td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </div> -->
                                                            </div>
                                                        </div>

                                                        <hr>

                                                

                                                        <h1 class="h3 mb-3">Requisição de Materiais: {{ requeststock.length }}</h1>
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
                                                        
                                                        <hr>
                                                        
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
                                                                </div>
                                                            </div>
                                                        </div>
                                                
                                               

                                                <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="task_mcscr_status_id">Estado:</label>
                                                                <Field as="select" class="form-control" :class="{'is-invalid':errors.task_mcscr_status_id}" v-model="retrievedData.task_mcscr_status_id"  name="task_mcscr_status_id" id="task_mcscr_status_id" aria-describedby="task_mcscr_status_id">
                                                                    <option value="" disabled>Selecionar</option>
                                                                    <option v-for="taskmcscrstatus in taskmcscrstatuses" :key="taskmcscrstatus.id" :value="taskmcscrstatus.id" >{{ taskmcscrstatus.name }}</option>
                                                                </Field>
                                                                <span class="invalid-feedback">{{ errors.task_mcscr_status_id }}</span>
                                                            </div>
                                                </div>


                                                <div class="mb-3 col-md-12">

														<label class="form-label" for="observation">Observação:</label>
														<Field as="textarea" class="form-control" :class="{'is-invalid':errors.observation}" v-model="retrievedData.observation" name="observation" id="observation" placeholder="Observação"/>
                                                        <span class="invalid-feedback">{{ errors.observation }}</span>
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