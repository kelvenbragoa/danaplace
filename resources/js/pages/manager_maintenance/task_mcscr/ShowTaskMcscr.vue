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
let taskmcscrstatuses = ref([]);
let requeststock = ref([]);
let requesttechnician = ref([]);
let requesttool = ref([]);
let loadingSubmit =ref([true]);
let loadingDiv =ref([true]);
const router = useRouter();
let self = this;







const getData = () => {
  axios.get(`/taskmcscr/+${router.currentRoute.value.params.id}`)
       .then((response)=>{
        loadingDiv.value=false;
        retrievedData.value = response.data.taskmcscr;
        taskmcscrstatuses = response.data.taskmcscrstatuses;
        requeststock.value =  response.data.requeststock;
        requesttechnician.value = response.data.requesttechnician;
        requesttool.value = response.data.requesttool;
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

        <h1 class="h3 mb-3">Atividades MCSCR</h1>
        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Atividades MCSCR: #{{ retrievedData.id }}</h5>

                                        <router-link to="/manager/maintenance/taskmcscr" class="btn btn-pill btn-primary mt-3"><vue-feather type="arrow-left"></vue-feather>Voltar</router-link> 

                                       
								    </div>
                                    
                                    <div class="card-body">
                                          
                                        <div class="row">
                                            <div class="col-xl-12 col-xxl-12 d-flex">
                                                <div class="w-100">
                                                    <h5 class="card-title">Informações da Tarefa MCSCR</h5>

                                                    <p><strong>ID da Tarefa MCSCR:</strong>  #{{ retrievedData.id }}</p>

                                                    <p><strong>Observação:</strong>  {{ retrievedData.observation ?? 'N/A' }}</p>

                                                    <p><strong>Distância/Horas:</strong>  {{ retrievedData.distance ?? 'N/A' }} {{ retrievedData.equipment.distance_control.name }}</p>

                                                    <p><strong>Tempo estimado:</strong> {{ retrievedData.task_plan_task.estimated_time_days }} Dias : {{ retrievedData.task_plan_task.estimated_time_hours }} Horas : {{ retrievedData.task_plan_task.estimated_time_minutes }} Minutos</p>
                                                    
                                                    <p><strong>Tempo que o equipamento estará indisponível:</strong> {{ retrievedData.task_plan_task.unavailable_equipment_time_days }} Dias : {{ retrievedData.task_plan_task.unavailable_equipment_time_hours }} Horas : {{ retrievedData.task_plan_task.unavailable_equipment_time_minutes }} Minutos </p>

                                                    <p><strong>Aberto em:</strong>  {{ retrievedData.opened_at == null ? 'N/A' : moment(retrievedData.opened_at).format('DD-MM-YYYY H:mm') }}</p>

                                                    <p><strong>Fechado em:</strong>  {{ retrievedData.closed_at == null ? 'N/A' : moment(retrievedData.closed_at).format('DD-MM-YYYY H:mm') }}</p>

                                                    <p class="text-danger"><strong>Tempo de Paralizado:</strong>  
                                                        <!-- {{ moment(retrievedData.closed_at).diff(retrievedData.opened_at,'hours')+' Horas ('+moment(retrievedData.closed_at).diff(retrievedData.opened_at,'minutes')+' Minutos)' }} -->
                                                        {{ retrievedData.closed_at == null ? moment().diff(retrievedData.opened_at,'hours')+' Horas ('+moment().diff(retrievedData.opened_at,'minutes')+' Minutos)' :  
                                                               
                                                               moment(retrievedData.closed_at).diff(retrievedData.opened_at,'hours')+' Horas ('+moment(retrievedData.closed_at).diff(retrievedData.opened_at,'minutes')+' Minutos)'
                                                            }}
                                                    </p>

                                                    <p>
                                                        <strong>Estado: </strong>  
                                                           
                                                            <span v-if="retrievedData.task_mcscr_status_id == 1" class="badge bg-warning">
                                                                {{ retrievedData.task_mcscr_status.name}}
                                                            </span>
                                                            <span v-if="retrievedData.task_mcscr_status_id == 2" class="badge bg-danger">
                                                                {{ retrievedData.task_mcscr_status.name}}
                                                            </span>
                                                            <span v-if="retrievedData.task_mcscr_status_id == 3" class="badge bg-success">
                                                                {{ retrievedData.task_mcscr_status.name}}
                                                            </span>
                                                    </p>
                                                    <p><strong>Programado por:</strong> {{ retrievedData.schedule_by_user == null ? '------' :  retrievedData.schedule_by_user.firstName +' '+ retrievedData.schedule_by_user.lastName}} / {{ retrievedData.schedule_for == null ? '------' : moment(retrievedData.schedule_for).format('DD-MM-YYYY H:mm')  }}</p>

                                                    <p><strong>Aberto por:</strong> {{ retrievedData.opened_by_user == null ? '------' :  retrievedData.opened_by_user.firstName +' '+ retrievedData.opened_by_user.lastName}} / {{ retrievedData.opened_at == null ? '------' : moment(retrievedData.opened_at).format('DD-MM-YYYY H:mm')  }}</p>

                                                    <p><strong>Fechado por:</strong> {{ retrievedData.closed_by_user == null ? '------' :  retrievedData.closed_by_user.firstName +' '+ retrievedData.closed_by_user.lastName}} / {{ retrievedData.closed_at == null ? '------' : moment(retrievedData.closed_at).format('DD-MM-YYYY H:mm')  }}</p>

                                                    <hr>

                                                    <h5 class="card-title">Informações do Equipamento</h5>

                                                    <p><strong>Nome:</strong>  {{ retrievedData.equipment.name ?? 'N/A' }}</p>
                                                    <p><strong>Referência:</strong>  {{ retrievedData.equipment.ref ?? 'N/A' }}</p>
                                                    <p><strong>Modelo:</strong>  {{ retrievedData.equipment.model ?? 'N/A' }}</p>
                                                    <p><strong>Marca:</strong>  {{ retrievedData.equipment.make ?? 'N/A' }}</p>
                                                    <p><strong>Serial:</strong>  {{ retrievedData.equipment.serial ?? 'N/A' }}</p>
                                                    <p><strong>Chassis:</strong>  {{ retrievedData.equipment.chassis ?? 'N/A' }}</p>
                                                    <p><strong>Referência:</strong>  {{ retrievedData.equipment.ref ?? 'N/A' }}</p>
                                                    <p><strong>Odômetro/Horímetro:</strong>  {{ retrievedData.distance ?? 'N/A' }}</p>
                                                    <p><strong>Clientes:</strong>  {{ retrievedData.destination.name ?? 'N/A' }}</p>
                                                    <p><strong>Área de Manutenção:</strong>  {{ retrievedData.area.name ?? 'N/A' }}</p>

                                                    <hr>

                                                    <!-- <h5 class="card-title">Atividades Realizadas</h5>

                                                    <div  v-for="(subtask,index) in retrievedData.subtasks" :key="subtask.id"> 
                                                        <p><strong>#{{index+1}} Tarefa:</strong> {{ subtask.subtask.name }}</p>
                                                        <p v-if="subtask.subtask.type_sub_task_id == 1"><strong>#{{index+1}} Resposta: </strong><span v-if="subtask.answer == 1">Sim</span> <span v-else>Não</span></p>
                                                        <p v-if="subtask.subtask.type_sub_task_id == 2"><strong>#{{index+1}} Resposta: </strong><span v-if="subtask.answer == 0">Mau</span> <span v-if="subtask.answer == 1">Bom</span> <span v-if="subtask.answer == 2">Excelente</span></p>
                                                        <p v-if="subtask.subtask.type_sub_task_id == 3"><strong>#{{index+1}} Resposta: </strong>{{subtask.answer}}</p>
                                                    </div>  -->
                                                    <div class="row">
                                                    <div class="mb-3 col-md-12">
                                                        <h1 class="h3 mb-3">Tarefas: {{ retrievedData.subtasks.length }}</h1>

                                                        <div class="table-responsive">
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
                                                        </div>
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
                                                
                                                <h1 class="h3 mb-3">Requisição de Técnicos:{{ requesttechnician.length }}</h1>
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
                                                
                                                <h1 class="h3 mb-3">Requisição de Ferramentaria:{{ requesttool.length }}</h1>
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