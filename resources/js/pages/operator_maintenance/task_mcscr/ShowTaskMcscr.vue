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
import { usePaperizer } from 'paperizer'

let retrievedData =ref([]);
let taskmcscrstatuses = ref([]);
let requeststock = ref([]);
let requesttechnician = ref([]);
let requesttool = ref([]);
let loadingSubmit =ref([true]);
let loadingDiv =ref([true]);
const router = useRouter();
let self = this;
let image = ref();
const destination = ref();


const loadingprint = ref(false);
const toastr = useToastr();

const { paperize } = usePaperizer('print-me',{
    styles: [
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css'
  ]
})





const getData = () => {
  axios.get(`/taskmcscr/+${router.currentRoute.value.params.id}`)
       .then((response)=>{
        loadingDiv.value=false;
        retrievedData.value = response.data.taskmcscr;
        taskmcscrstatuses = response.data.taskmcscrstatuses;
        requeststock.value =  response.data.requeststock;
        requesttechnician.value = response.data.requesttechnician;
        requesttool.value = response.data.requesttool;
        destination.value = response.data.destination;
        image.value = "/files/img/sys/volvopenta1.png"

        // if(destination.value.image != null){
        //         image.value = "/storage/"+destination.value.image
        //     }else{
        //         image.value = "/files/img/sys/companylogo.png"
        //     }
       }).catch(()=>{
        loadingDiv.value=false;
       })
}

const downloadMcscr = () =>{
    // axios.get(`/download-mcscr/+${router.currentRoute.value.params.id}`)
    //    .then((response)=>{
    //     console.log(response.data)
    //    }).catch(()=>{
        
    //    })

    loadingprint.value = true;
        // window.print();
        // router.push('/admin/mcscr').catch(()=>{})
        // this.$htmlToPaper('printMe');
        paperize()
    loadingprint.value = false;

    // loadingprint.value = true;
    // axios({
    //     url:`/download-taskmcscr/+${router.currentRoute.value.params.id}`,
    //     responseType:'blob'
    // }).then((response)=>{
    //     const url = window.URL.createObjectURL(new Blob([response.data]));
    //     const link = document.createElement('a');
    //     link.href = url;
    //     link.setAttribute('download', 'task-nr-'+retrievedData.value.id+'.pdf');
    //     document.body.appendChild(link);
    //     link.click();
    //     loadingprint.value = false;
    //     toastr.success('Documento baixado com sucesso');
    // }).catch((error)=>{
    
    //     loadingprint.value = false;
    //     toastr.error('Ocorreu um erro ao tentar baixar o documento. '+error.response.data.message);
       
    // }).finally(()=>{
    //     loadingprint.value = false;
    // })
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
								<div class="card-body m-sm-3 m-md-5 border">
									<!-- <div class="mb-4">
										Hello <strong>Charles Hall</strong>,
										<br /> This is the receipt for a payment of <strong>$268.00</strong> (USD) you made to AdminKit Demo.
									</div> -->
                                    <div class="mb-4">
                                        <router-link to="/admin/taskmcscr" class="btn btn-pill btn-primary mt-3"><vue-feather type="arrow-left"></vue-feather>Voltar</router-link>
                                    </div> 
                                    <div id="print-me">
                                    <div class="row mb-4">
                                        <div class="row">
                                            <div class="col">
												<img src="/files/img/sys/companylogo.png" class="img-fluid" width="150" height="150">
											</div>
                                            <div class="col">
                                                <br>
                                            </div>
                                            <div class="col text-right">
												<img :src=image class="img-fluid" :alt=image width="150" height="150">
											</div>
                                        </div>
                                    </div>

                                    <div class="row mb-4">
										<div class="col">
                                            <div class="text-muted">Área de Manutenção</div>
											<strong> {{ retrievedData.area.name ?? 'N/A' }} </strong>
											<p> {{ retrievedData.area.company_name ?? 'N/A' }} 
                                                <br> 
                                                {{ retrievedData.area.company_address ?? 'N/A' }}
                                                <br>
                                                {{ retrievedData.area.company_nuit ?? 'N/A' }}
                                                <br>
                                                {{ retrievedData.area.province.name ?? 'N/A' }}
                                                <br>
                                                {{ retrievedData.area.company_mobile ?? 'N/A' }}
                                                <br>
                                                {{ retrievedData.area.company_email ?? 'N/A' }}
                                            </p>
										</div>
                                        <div class="col">
                                            <br>
                                        </div>
										<div class="col text-md-right" style="text-align: right;">
											<div class="text-muted" style="text-align: right;">Clientes</div>
											<strong style="text-align: right;"> {{ retrievedData.destination.name ?? 'N/A' }} </strong>
											<p style="text-align: right;"> {{ retrievedData.destination.company_name ?? 'N/A' }} 
                                                <br> 
                                                {{ retrievedData.destination.company_address ?? 'N/A' }}
                                                <br>
                                                {{ retrievedData.destination.company_nuit ?? 'N/A' }}
                                                <br>
                                                {{ retrievedData.destination.province.name ?? 'N/A' }}
                                                <br>
                                                {{ retrievedData.destination.company_mobile ?? 'N/A' }}
                                                <br>
                                                {{ retrievedData.destination.company_email ?? 'N/A' }}
                                            </p>
										</div>
									</div>
                                    <div class="row mb-4">
                                        <p><strong>Atividade Nº: </strong>#{{retrievedData.id}}  </p>
                                    </div>
                                    <hr class="my-4" />


                                        <div class="text-muted mb-4">Detalhes Equipamento</div>

                                            <div class="row mb-5 border">
                                                <div class="col border-right">
                                                    <div class="text-muted">Equipamento/Ativo</div>
                                                    <strong>{{ retrievedData.equipment.name ?? 'N/A' }}</strong>
                                                </div>
                                                <div class="col border-right">
                                                    <div class="text-muted">Tipo/Frota</div>
                                                    <strong>{{ retrievedData.equipment.type_equipment.name ?? 'N/A' }}</strong>
                                                </div>
                                                <div class="col border-right">
                                                    <div class="text-muted">Ref</div>
                                                    <strong>{{ retrievedData.equipment.ref ?? 'N/A' }}</strong>
                                                </div>
                                                <div class="col border-right">
                                                    <div class="text-muted">Marca</div>
                                                    <strong>{{ retrievedData.equipment.make ?? 'N/A' }}</strong>
                                                </div>
                                                <div class="col border-right">
                                                    <div class="text-muted">Modelo</div>
                                                    <strong>{{ retrievedData.equipment.model ?? 'N/A' }}</strong>
                                                </div>
                                                <div class="col border-right">
                                                    <div class="text-muted">{{ retrievedData.equipment.distance_control.name }}</div>
                                                    <strong>{{ retrievedData.distance ?? 'N/A' }}</strong>
                                                </div>
                                            </div>

                                    <hr class="my-4" />
                                    <div class="text-muted mb-4">Detalhes Atividade</div>

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
                                            <p><strong>Plano de Actividade:</strong>{{ retrievedData.task_plan.name }}</p>
                                            <p><strong>Actividade:</strong>{{ retrievedData.task_plan_task.name }}</p>
                                            <p class="text-danger"><strong>Tempo estimado:</strong>  {{ retrievedData.task_plan_task.estimated_time_days }} Dias : {{ retrievedData.task_plan_task.estimated_time_hours }} Horas : {{ retrievedData.task_plan_task.estimated_time_minutes }} Minutos</p>
                                            <p class="text-danger"><strong>Tempo que o equipamento estará indisponível:</strong> {{ retrievedData.task_plan_task.unavailable_equipment_time_days }} Dias : {{ retrievedData.task_plan_task.unavailable_equipment_time_hours }} Horas : {{ retrievedData.task_plan_task.unavailable_equipment_time_minutes }} Minutos</p>
                                            <p class="text-danger"><strong>Tempo paralizado:</strong>  
                                                {{ retrievedData.closed_at == null ? moment().diff(retrievedData.opened_at,'hours')+' Horas ('+moment().diff(retrievedData.opened_at,'minutes')+' Minutos)' :  
                                                               
                                                               moment(retrievedData.closed_at).diff(retrievedData.opened_at,'hours')+' Horas ('+moment(retrievedData.closed_at).diff(retrievedData.opened_at,'minutes')+' Minutos)'
                                                            }}
                                            </p>
                                            <!-- <p><strong>Previsão de Saída:</strong>  {{ moment(retrievedData.output_forecast).format('DD-MM-YYYY H:mm') }}</p>
                                            <div class="row mb-5 border">

                                                <div class="col-md-3 border-right">
                                                    <div class="text-muted">Motivo</div>
                                                    <p><strong>Descrito: </strong>{{ retrievedData.reason ?? 'N/A' }}</p>
                                                    <p><strong>Registrado: </strong>{{ retrievedData.reason_name == null ? 'N/A': retrievedData.reason_name.name }}</p>
                                                    <p><strong>Código: </strong>{{ retrievedData.reason_name == null ? 'N/A': retrievedData.reason_name.code }}</p>
                                                    
                                                </div>
                                                <div class="col-md-3 border-right">
                                                    <div class="text-muted">Causa</div>
                                                    <p><strong>Descrita:</strong>  {{ retrievedData.cause ?? 'N/A' }}</p>
                                                    <p><strong>Registrada:</strong>  {{ retrievedData.cause_name == null ? 'N/A': retrievedData.cause_name.name }}</p>
                                                    <p><strong>Código:</strong>  {{ retrievedData.cause_name == null ? 'N/A': retrievedData.cause_name.code }}</p>
                                                </div>
                                                <div class="col border-right">
                                                    <div class="text-muted">Solução</div>
                                                    <p><strong>Descrita:</strong>  {{ retrievedData.solution ?? 'N/A' }}</p>
                                                    <p><strong>Registrada:</strong>  {{ retrievedData.solution_name == null ? 'N/A': retrievedData.solution_name.name }}</p>
                                                    <p><strong>Código:</strong>  {{ retrievedData.solution_name == null ? 'N/A': retrievedData.solution_name.code }}</p>
                                                </div>
                                                <div class="col border-right">
                                                    <div class="text-muted">Consequência</div>
                                                    <p><strong>Descrita:</strong>  {{ retrievedData.consequence ?? 'N/A' }}</p>
                                                    <p><strong>Registrada:</strong>  {{ retrievedData.consequence_name == null ? 'N/A': retrievedData.consequence_name.name}}</p>
                                                    <p><strong>Código:</strong>  {{ retrievedData.consequence_name == null ? 'N/A':retrievedData.consequence_name.code }}</p>
                                                </div>
                                                <div class="col border-right">
                                                    <div class="text-muted">Recomendação</div>
                                                    <p><strong>Descrita:</strong>  {{ retrievedData.recommendation ?? 'N/A' }}</p>
                                                    <p><strong>Registrada:</strong>  {{ retrievedData.recommendation_name == null ? 'N/A': retrievedData.recommendation_name.name }}</p>
                                                    <p><strong>Código:</strong>  {{ retrievedData.recommendation_name == null ? 'N/A': retrievedData.recommendation_name.code }}</p>
                                                </div>

                                            </div> -->

                                            <div class="row mb-5 border">
                                                <!-- <div class="col border-right">
                                                    <div class="text-muted">MCSCR nº</div>
                                                    <strong>#{{retrievedData.id}}</strong>
                                                </div> -->
                                                <div class="col-md-3  border-right">
                                                    <div class="text-muted">Criado em:</div>
                                                    <strong>{{ moment(retrievedData.created_at).format('DD-MM-YYYY H:mm') }}</strong>
                                                </div>
                                                <div class="col-md-3  border-right">
                                                    <div class="text-muted">Programado para:</div>
                                                    <strong>{{ retrievedData.schedule_for == null ? 'N/A' : moment(retrievedData.schedule_for).format('DD-MM-YYYY H:mm') }}

                                                       
                                                    </strong>
                                                </div>
                                                <div class="col-md-3  border-right">
                                                    <div class="text-muted">Iniciado em:</div>
                                                    <strong>{{ retrievedData.opened_at == null ? 'N/A' : moment(retrievedData.opened_at).format('DD-MM-YYYY H:mm') }}
                                                       
                                                    </strong>
                                                </div>
                                                <div class="col-md-3  border-right">
                                                    <div class="text-muted">Terminado em:</div>
                                                    <strong>{{ retrievedData.closed_at == null ? 'N/A' : moment(retrievedData.closed_at).format('DD-MM-YYYY H:mm') }}
                                                    </strong>
                                                </div>
                                               
                                            </div>

                                    <!-- <hr class="my-4" />

                                        <div class="text-muted mb-4">Detalhes da Avaria</div>

                                        <div class="row mb-5 border">
                                            <div class="col-md-3 border-right">
                                                <div class="text-muted">Tipo de Avaria:</div>
                                                <strong>----</strong>
                                            </div>
                                            <div class="col border-right">
                                                <div class="text-muted">Componente:</div>
                                                <strong>{{ retrievedData.component == null ? 'N/A' : retrievedData.component.name}}</strong>
                                            </div>
                                            <div class="col border-right">
                                                <div class="text-muted">SubComponente:</div>
                                                <strong> {{ retrievedData.subcomponent == null ? 'N/A' : retrievedData.subcomponent.name}}</strong>
                                            </div>
                                            <div class="col border-right">
                                                <div class="text-muted">Retrabalho</div>
                                                <strong>{{ retrievedData.is_rework == 1 ? 'Sim' : 'Não' }}</strong>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="text-muted"> Motivo de Espera (Paralização):</div>
                                                <strong>----</strong>
                                            </div>
                                        </div> -->



									

                                    <hr class="my-4" />

                                    <div class="text-muted mb-4">Custos Envolvidos</div>

                                        <div class="row mb-5 border">
                                            <div class="col border-right">
                                                <div class="text-muted">Mão de Obra</div>
                                                <strong>{{ retrievedData.material_labor ?? '0'}} MT</strong>
                                            </div>
                                            <div class="col">
                                                <div class="text-muted">Material</div>
                                                <strong>{{ retrievedData.material_cost ?? '0'}} MT</strong>
                                            </div>
                                            
                                        </div>
                                    <hr class="my-4" />

                                    <div class="text-muted mb-4">Tarefas: {{ retrievedData.subtasks.length }}</div>
                                    <div class="table-responsive">
                                            <table class="table">
                                                <thead>
                                                                    <tr>
                                                                        <th>#</th>
                                                                        <th>Tarefa</th>
                                                                        <th>Tipo</th>
                                                                        <th>Resposta</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <tr v-for="(subtask, index) in retrievedData.subtasks" :key="subtask.id">
                                                                        <td>{{ index+1 }}</td>
                                                                        <td >{{ subtask.subtask.name }}</td>
                                                                        <td >{{ subtask.subtask.typesubtask.name }}</td>
                                                                        <td v-if="subtask.subtask.type_sub_task_id == 1"><span v-if="subtask.answer == 1">Sim</span> <span v-else>Não</span></td>
                                                                        <td v-if="subtask.subtask.type_sub_task_id == 2"><span v-if="subtask.answer == 0">Mau</span> <span v-if="subtask.answer == 1">Bom</span> <span v-if="subtask.answer == 2">Excelente</span></td>
                                                                        <td v-if="subtask.subtask.type_sub_task_id == 3">{{subtask.answer}}</td>
                                                                    </tr>
                                                                </tbody>
                                            </table>
                                        </div>

                                    <hr class="my-4" />

                                    <div class="text-muted mb-4">Requisição Materiais: {{ requeststock.length }} registros</div>

                                        <div class="row mb-5 border" v-for="(request, idx) in requeststock" :key="request.id">
                                            <p>
                                                <span><strong>Registro:</strong>#{{ idx+1 }}</span> <br>
                                                <span><strong>ID da Requisição:</strong>{{ request.id }}</span> <br>
                                                <span><strong>Data De Criação:</strong>{{ moment(request.created_at).format('DD-MM-YYYY H:mm')}}</span> <br>
                                                <span><strong>Criado por:</strong>{{ request.createdbyuser.firstName+' ' +request.createdbyuser.lastName }}</span> <br>
                                                <span><strong>Aprovado/Reprovado por:</strong>{{ request.approvedbyuser == null ? '-----' : request.approvedbyuser.firstName+' '+request.approvedbyuser.lastName +'('+moment(request.approved_date).format('DD-MM-YYYY H:mm')+')'}}</span> <br>
                                                <span><strong>Entregue por:</strong>{{ request.deliveredbyuser == null ? '-----' : request.deliveredbyuser.firstName+' '+request.deliveredbyuser.lastName +'('+moment(request.delivered_date).format('DD-MM-YYYY H:mm')+')'}}</span> <br>
                                                <span><strong>Estado:</strong>
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
                                                </span>
                                            </p>
                                            <table class="table">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>ID da Requisição</th>
                                                        <th>Material</th>
                                                        <th>Quantidade Requisitado</th>
                                                        <th>Quantidade Entregue</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr v-for="(material, index) in request.requestitens" :key="material.id">
                                                        <td>#{{index+1}}</td>
                                                        <td>{{request.id}}</td>
                                                        <td>{{ material.product.name }}</td>
                                                        <td>{{ material.quantity }}</td>
                                                        <td>{{ material.delivered_quantity }}</td>
                                                        
                                                    </tr>
                                                </tbody>
                                            </table>

                                           
                                            
                                            
                                        </div>
                                    <hr class="my-4" />


                                    <div class="text-muted mb-4">Requisição Técnicos: {{ requesttechnician.length }} registros</div>

                                    <div class="row mb-5 border" v-for="(requesttech, idx) in requesttechnician" :key="requesttech.id">
                                            <p>
                                                <span><strong>Registro:</strong>#{{ idx+1 }}</span> <br>
                                                <span><strong>ID da Requisição:</strong>{{ requesttech.id }}</span> <br>
                                                <span><strong>Data De Criação:</strong>{{ moment(requesttech.created_at).format('DD-MM-YYYY H:mm')}}</span> <br>
                                                <span><strong>Criado por:</strong>{{ requesttech.createdbyuser.firstName+' ' +requesttech.createdbyuser.lastName }}</span> <br>
                                                <span><strong>Aprovado/Reprovado por:</strong>{{ requesttech.approvedbyuser == null ? '-----' : requesttech.approvedbyuser.firstName+' '+requesttech.approvedbyuser.lastName +'('+moment(requesttech.approved_date).format('DD-MM-YYYY H:mm')+')'}}</span> <br>
                                                <span><strong>Entregue por:</strong>{{ requesttech.deliveredbyuser == null ? '-----' : requesttech.deliveredbyuser.firstName+' '+requesttech.deliveredbyuser.lastName +'('+moment(requesttech.delivered_date).format('DD-MM-YYYY H:mm')+')'}}</span> <br>
                                                <span><strong>Estado:</strong>
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
                                                </span>
                                            </p>
                                            <table class="table">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>ID da Requisição</th>
                                                        <th>Departamento</th>
                                                        <th>Nome Técnicos Requisitados</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr v-for="(tech, index) in requesttech.requestitens" :key="tech.id">
                                                        <td>#{{index+1}}</td>
                                                        <td>{{ requesttech.id }}</td>
                                                        <td>{{ tech.department.name }}</td>
                                                        <td>{{ tech.technician == null ? '------' : tech.technician.name }}</td>
                                                        
                                                    </tr>
                                                </tbody>
                                            </table>

                                           
                                            
                                            
                                        </div>

                                    <hr class="my-4" />

                                    <div class="text-muted mb-4">Requisição Ferramentaria: {{ requesttool.length }} registros</div>

                                        <div class="row mb-5 border" v-for="(requesttoolshop, idx) in requesttool" :key="requesttoolshop.id">
                                                <p>
                                                    <span><strong>Registro:</strong>#{{ idx+1 }}</span> <br>
                                                    <span><strong>ID da Requisição:</strong>{{ requesttoolshop.id }}</span> <br>
                                                    <span><strong>Data De Criação:</strong>{{ moment(requesttoolshop.created_at).format('DD-MM-YYYY H:mm')}}</span> <br>
                                                    <span><strong>Criado por:</strong>{{ requesttoolshop.createdbyuser.firstName+' ' +requesttoolshop.createdbyuser.lastName }}</span> <br>
                                                    <span><strong>Aprovado/Reprovado por:</strong>{{ requesttoolshop.approvedbyuser == null ? '-----' : requesttoolshop.approvedbyuser.firstName+' '+requesttoolshop.approvedbyuser.lastName +'('+moment(requesttoolshop.approved_date).format('DD-MM-YYYY H:mm')+')'}}</span> <br>
                                                    <span><strong>Entregue por:</strong>{{ requesttoolshop.deliveredbyuser == null ? '-----' : requesttoolshop.deliveredbyuser.firstName+' '+requesttoolshop.deliveredbyuser.lastName +'('+moment(requesttoolshop.delivered_date).format('DD-MM-YYYY H:mm')+')'}}</span> <br>
                                                    <span><strong>Estado:</strong>
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
                                                    </span>
                                                </p>
                                                <table class="table">
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

                                    <hr class="my-4" />
                                    <div class="text-muted mb-4">Envolvidos</div>

                                        <div class="row mb-5 border">
                                            <div class="col border-right">
                                                <div class="text-muted">Aberto por:</div>
                                                <br>
                                                <strong>{{ retrievedData.opened_by_user == null ? 'N/A' : retrievedData.opened_by_user.firstName+' '+retrievedData.opened_by_user.lastName+' / '+retrievedData.opened_by_user.email }}</strong> 
                                                <br>
                                                <br>
                                                <hr>
                                            </div>
                                            <div class="col">
                                                <div class="text-muted">Fechado por: </div>
                                                <br>
                                                <strong>{{ retrievedData.closed_by_user == null ? 'N/A' : retrievedData.closed_by_user.firstName+' '+retrievedData.closed_by_user.lastName+' / '+retrievedData.closed_by_user.email }}</strong> 
                                                <br>
                                                <br>
                                                <hr>
                                            </div>
                                            
                                        </div>

                                    <hr class="my-4" />


									<div class="text-left">
										<p class="text-sm">
											<strong>Observação:</strong> 
										</p>
                                        <pre style="text-align: left;">
                                            {{ retrievedData.observation ?? 'N/A' }}
                                        </pre>

									


                                        
									</div>
                                </div>
								</div>
                                <div class="text-center">
										
										<button @click="downloadMcscr" class="btn btn-primary" :disabled="loadingprint">
                                            <div v-if="loadingprint" class="spinner-border spinner-border-sm" role="status"></div>
                                            <span v-else>Print</span>
                                        </button>
                                        <div v-if="loadingprint" class="d-flex justify-content-center">
                                            Aguarde, Estamos gerando o seu documento...
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