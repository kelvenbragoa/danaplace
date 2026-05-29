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
import VueHtmlToPaper from "vue-html-to-paper";
import { usePaperizer } from 'paperizer'


let retrievedData =ref([]);
let loadingSubmit =ref([true]);
let loadingDiv =ref([true]);
const loadingprint = ref(false);
const router = useRouter();
let self = this;
let requeststock = ref([])
let requesttool = ref([]);
let requesttechnician = ref([])
const toastr = useToastr();
const { paperize } = usePaperizer('print-me',{
    styles: [
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css'
  ]
})


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

    // axios({
    //     url:`/download-mcscr/+${router.currentRoute.value.params.id}`,
    //     responseType:'blob'
    // }).then((response)=>{
    //     const url = window.URL.createObjectURL(new Blob([response.data]));
    //     const link = document.createElement('a');
    //     link.href = url;
    //     link.setAttribute('download', 'mcscr-nr-'+retrievedData.value.id+'.pdf');
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




const getData = () => {
  axios.get(`/mcscr/+${router.currentRoute.value.params.id}`)
       .then((response)=>{
        loadingDiv.value=false;
        retrievedData.value = response.data.mcscr;
        requeststock.value = response.data.requeststock
        requesttool.value = response.data.requesttool
        requesttechnician.value = response.data.requesttechnician
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

        <h1 class="h3 mb-3">MCSCR</h1>
        
        <div class="row">
						<div class="col-12">
							<div class="card" id="print-me">
								<div class="card-body m-sm-3 m-md-5 border">
									<!-- <div class="mb-4">
										Hello <strong>Charles Hall</strong>,
										<br /> This is the receipt for a payment of <strong>$268.00</strong> (USD) you made to AdminKit Demo.
									</div> -->
                                    <div class="mb-4">
                                        <router-link to="/admin/mcscr" class="btn btn-pill btn-primary mt-3"><vue-feather type="arrow-left"></vue-feather>Voltar</router-link>
                                    </div> 

                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <div class="col-2">
												<img src="/files/img/sys/companylogo.png" class="img-fluid w-100" alt="M+D" width="450" height="450">
											</div>
                                        </div>
                                    </div>

                                    <div class="row mb-4">
										<div class="col-md-6">
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
										<div class="col-md-6 text-md-right">
											<div class="text-muted">Clientes</div>
											<strong> {{ retrievedData.destination.name ?? 'N/A' }} </strong>
											<p> {{ retrievedData.destination.company_name ?? 'N/A' }} 
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
                                        <p><strong>MCSCR Nº: </strong>#{{retrievedData.id}}  </p>
                                    </div>
                                    <hr class="my-4" />


                                        <div class="text-muted mb-4">Detalhes Equipamento</div>

                                            <div class="row mb-5 border">
                                                <div class="col-md-2 border-right">
                                                    <div class="text-muted">Equipamento/Ativo</div>
                                                    <strong>{{ retrievedData.equipment.name ?? 'N/A' }}</strong>
                                                </div>
                                                <div class="col-md-2 border-right">
                                                    <div class="text-muted">Tipo/Frota</div>
                                                    <strong>{{ retrievedData.equipment.type_equipment.name ?? 'N/A' }}</strong>
                                                </div>
                                                <div class="col-md-2 border-right">
                                                    <div class="text-muted">Ref</div>
                                                    <strong>{{ retrievedData.equipment.ref ?? 'N/A' }}</strong>
                                                </div>
                                                <div class="col-md-2 border-right">
                                                    <div class="text-muted">Marca</div>
                                                    <strong>{{ retrievedData.equipment.make ?? 'N/A' }}</strong>
                                                </div>
                                                <div class="col-md-2 border-right">
                                                    <div class="text-muted">Modelo</div>
                                                    <strong>{{ retrievedData.equipment.model ?? 'N/A' }}</strong>
                                                </div>
                                                <div class="col-md-2 border-right">
                                                    <div class="text-muted">Odômetro/Horímetro</div>
                                                    <strong>{{ retrievedData.distance ?? 'N/A' }}</strong>
                                                </div>
                                            </div>

                                    <hr class="my-4" />
                                    <div class="text-muted mb-4">Detalhes MCSCR</div>

                                        <p>
                                                            <strong>Estado: </strong>  
                                                           

                                                            <span v-if="retrievedData.mcscr_status_id == 1" class="badge bg-success">
                                                                {{ retrievedData.mcscr_status.name}}
                                                            </span>
                                                            <span v-if="retrievedData.mcscr_status_id == 2" class="badge bg-warning">
                                                                {{ retrievedData.mcscr_status.name}}
                                                            </span>
                                                            <span v-if="retrievedData.mcscr_status_id == 3" class="badge bg-danger">
                                                                {{ retrievedData.mcscr_status.name}}
                                                            </span>
                                                            <span v-if="retrievedData.mcscr_status_id == 4" class="badge bg-info">
                                                                {{ retrievedData.mcscr_status.name}}
                                                            </span>
                                                            <span v-if="retrievedData.mcscr_status_id == 5" class="badge bg-primary">
                                                                {{ retrievedData.mcscr_status.name}}
                                                            </span>
                                            </p>
                                            <p class="text-danger"><strong>Tempo paralizado:</strong>  
                                                        {{ retrievedData.closed_at == null ? moment().diff(retrievedData.opened_at,'hours')+' Horas ('+moment().diff(retrievedData.opened_at,'minutes')+' Minutos)' :  
                                                               
                                                            moment(retrievedData.closed_at).diff(retrievedData.opened_at,'hours')+' Horas ('+moment(retrievedData.closed_at).diff(retrievedData.opened_at,'minutes')+' Minutos)'
                                                        
                                                        }}
                                            </p>
                                            <p><strong>Previsão de Saída:</strong>  {{ moment(retrievedData.output_forecast).format('DD-MM-YYYY H:mm') }}</p>
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
                                                <div class="col-md-2 border-right">
                                                    <div class="text-muted">Solução</div>
                                                    <p><strong>Descrita:</strong>  {{ retrievedData.solution ?? 'N/A' }}</p>
                                                    <p><strong>Registrada:</strong>  {{ retrievedData.solution_name == null ? 'N/A': retrievedData.solution_name.name }}</p>
                                                    <p><strong>Código:</strong>  {{ retrievedData.solution_name == null ? 'N/A': retrievedData.solution_name.code }}</p>
                                                </div>
                                                <div class="col-md-2 border-right">
                                                    <div class="text-muted">Consequência</div>
                                                    <p><strong>Descrita:</strong>  {{ retrievedData.consequence ?? 'N/A' }}</p>
                                                    <p><strong>Registrada:</strong>  {{ retrievedData.consequence_name == null ? 'N/A': retrievedData.consequence_name.name}}</p>
                                                    <p><strong>Código:</strong>  {{ retrievedData.consequence_name == null ? 'N/A':retrievedData.consequence_name.code }}</p>
                                                </div>
                                                <div class="col-md-2 border-right">
                                                    <div class="text-muted">Recomendação</div>
                                                    <p><strong>Descrita:</strong>  {{ retrievedData.recommendation ?? 'N/A' }}</p>
                                                    <p><strong>Registrada:</strong>  {{ retrievedData.recommendation_name == null ? 'N/A': retrievedData.recommendation_name.name }}</p>
                                                    <p><strong>Código:</strong>  {{ retrievedData.recommendation_name == null ? 'N/A': retrievedData.recommendation_name.code }}</p>
                                                </div>

                                            </div>

                                            <div class="row mb-5 border">
                                                <div class="col-md-2 border-right">
                                                    <div class="text-muted">MCSCR nº</div>
                                                    <strong>#{{retrievedData.id}}</strong>
                                                </div>
                                                <div class="col-md-2 text-md-right border-right">
                                                    <div class="text-muted">Aberto em:</div>
                                                    <strong>{{ moment(retrievedData.opened_at).format('DD-MM-YYYY H:mm') }}</strong>
                                                </div>
                                                <div class="col-md-2 text-md-right border-right">
                                                    <div class="text-muted">Diagnóstico em:</div>
                                                    <strong>{{ retrievedData.diagnosis_start_at == null ? 'N/A' : moment(retrievedData.diagnosis_start_at).format('DD-MM-YYYY H:mm') }} |

                                                        {{ retrievedData.diagnosis_start_at == null ? 'N/A' :  
                                                               moment(retrievedData.diagnosis_start_at).diff(retrievedData.opened_at,'hours')+' H ('+moment(retrievedData.diagnosis_start_at).diff(retrievedData.opened_at,'minutes')+' Min)'
                                                        }}
                                                    </strong>
                                                </div>
                                                <div class="col-md-2 text-md-right border-right">
                                                    <div class="text-muted">Execução em:</div>
                                                    <strong>{{ retrievedData.execution_start_at == null ? 'N/A' : moment(retrievedData.execution_start_at).format('DD-MM-YYYY H:mm') }} |
                                                        {{ retrievedData.execution_start_at == null ? 'N/A' :  
                                                               moment(retrievedData.execution_start_at).diff(retrievedData.diagnosis_start_at,'hours')+' H ('+moment(retrievedData.execution_start_at).diff(retrievedData.diagnosis_start_at,'minutes')+' Min)'
                                                        }}
                                                    </strong>
                                                </div>
                                                <div class="col-md-2 text-md-right border-right">
                                                    <div class="text-muted">Aprovação em:</div>
                                                    <strong>{{ retrievedData.awaiting_approval_start_at == null ? 'N/A' : moment(retrievedData.awaiting_approval_start_at).format('DD-MM-YYYY H:mm') }} |
                                                        {{ retrievedData.awaiting_approval_start_at == null ? 'N/A' :  
                                                               moment(retrievedData.awaiting_approval_start_at).diff(retrievedData.execution_start_at,'hours')+' H ('+moment(retrievedData.awaiting_approval_start_at).diff(retrievedData.execution_start_at,'minutes')+' Min)'
                                                        }}
                                                    </strong>
                                                </div>
                                                <div class="col-md-2 text-md-right border-right">
                                                    <div class="text-muted">Fechado em:</div>
                                                    <strong>{{ retrievedData.closed_at == null ? 'N/A' : moment(retrievedData.closed_at).format('DD-MM-YYYY H:mm') }} |
                                                        {{ retrievedData.closed_at == null ? 'N/A' :  
                                                               moment(retrievedData.closed_at).diff(retrievedData.awaiting_approval_start_at,'hours')+' H ('+moment(retrievedData.closed_at).diff(retrievedData.awaiting_approval_start_at,'minutes')+' Min)'
                                                        }}
                                                    </strong>
                                                </div>
                                            </div>

                                    <hr class="my-4" />

                                        <div class="text-muted mb-4">Detalhes da Avaria</div>

                                        <div class="row mb-5 border">
                                            <div class="col-md-3 border-right">
                                                <div class="text-muted">Tipo de Avaria:</div>
                                                <strong>{{ retrievedData.type_malfunction.name ?? 'N/A' }}</strong>
                                            </div>
                                            <div class="col-md-2 border-right">
                                                <div class="text-muted">Componente:</div>
                                                <strong>{{ retrievedData.component == null ? 'N/A' : retrievedData.component.name}}</strong>
                                            </div>
                                            <div class="col-md-2 border-right">
                                                <div class="text-muted">SubComponente:</div>
                                                <strong> {{ retrievedData.subcomponent == null ? 'N/A' : retrievedData.subcomponent.name}}</strong>
                                            </div>
                                            <div class="col-md-2 border-right">
                                                <div class="text-muted">Retrabalho</div>
                                                <strong>{{ retrievedData.is_rework == 1 ? 'Sim' : 'Não' }}</strong>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="text-muted"> Motivo de Espera (Paralização):</div>
                                                <strong>{{ retrievedData.waiting_status.name }}</strong>
                                            </div>
                                        </div>



									

                                    <hr class="my-4" />

                                    <div class="text-muted mb-4">Custos Envolvidos</div>

                                        <div class="row mb-5 border">
                                            <div class="col-md-2 border-right">
                                                <div class="text-muted">Mão de Obra</div>
                                                <strong>{{ retrievedData.material_labor ?? '0'}} MT</strong>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="text-muted">Material</div>
                                                <strong>{{ retrievedData.material_cost ?? '0'}} MT</strong>
                                            </div>
                                            
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
                                            <table class="table table-sm">
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
                                            <table class="table table-sm">
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
                                                <table class="table table-sm">
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
                                            <div class="col-md-6 border-right">
                                                <div class="text-muted">Aberto por:</div>
                                                <br>
                                                <strong>{{ retrievedData.opened_by_user == null ? 'N/A' : retrievedData.opened_by_user.firstName+' '+retrievedData.opened_by_user.lastName+' / '+retrievedData.opened_by_user.email }}</strong> 
                                                <br>
                                                <br>
                                                <hr>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="text-muted">Fechado por: </div>
                                                <br>
                                                <strong>{{ retrievedData.closed_by_user == null ? 'N/A' : retrievedData.closed_by_user.firstName+' '+retrievedData.closed_by_user.lastName+' / '+retrievedData.closed_by_user.email }}</strong> 
                                                <br>
                                                <br>
                                                <hr>
                                            </div>
                                            
                                        </div>

                                    <hr class="my-4" />


									<div class="text-center">
										<p class="text-sm">
											<strong>Observação:</strong> {{ retrievedData.first_observation ?? 'N/A' }}.
										</p>

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