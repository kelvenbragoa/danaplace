<script setup>

import {onMounted, ref, reactive,watch} from 'vue';
import axios from 'axios';
import {useToastr} from '../../../toastr';
import moment from 'moment'
import {debounce} from 'lodash';
import { Bootstrap4Pagination } from 'laravel-vue-pagination';
import VueFeather from 'vue-feather';

const toastr = useToastr();
const searchQuery = ref(null);
const searchQueryMcscr = ref(null);
const loadingDiv = ref(true);
const loadingButtonDelete = ref(false);

const total = ref(0)
const terminado = ref(0)
const pendente = ref(0)
const programado = ref(0)
const aprovacao = ref(0)
const diagnostico = ref(0)
const execucao = ref(0)
const destinations = ref([])
const destinationfilter = ref(null)
const destinationfilterjobs = ref(null)

const mcscrStatus = {'terminado':1,'aguardaaprovacao':2,'emexecucao':3,'pendente':4,'emdiagnostico':5,'programado':6}

const retriviedData = ref({'data': []})

const retriviedDataMcscr = ref({'data': []})

let dataIdBeingDeleted = ref(null);


 const getData = async (page = 1,destinationfilterjobs) => {
  axios.get(`/jobtasks?page=${page}`,
      {
        params:{
          query: searchQuery.value,
          destination: destinationfilterjobs

        }
      })
       .then((response)=>{
        retriviedData.value = response.data.jobs;
        destinations.value = response.data.destinations;

        loadingDiv.value=false;

        
       })   
}

const getDataMcscr = async (page = 1,status,destinationfilter) => {

    
axios.get(`/mcscrjobtask?page=${page}`,
    {
      params:{
        query: searchQueryMcscr.value,
        status:status,
        destination: destinationfilter
      }
    })
     .then((response)=>{
      
      retriviedDataMcscr.value = response.data.mcscr;
      terminado.value = response.data.terminado;
      pendente.value = response.data.pendente;
      programado.value = response.data.programado;

      aprovacao.value = response.data.aprovacao;
      diagnostico.value = response.data.diagnostico;
      execucao.value = response.data.execucao;
      total.value = response.data.total;
      destinations.value = response.data.destinations;

      loadingDiv.value=false;

      
     })

     
}

const confirmDone = (data) => {
dataIdBeingDeleted = data.id;
$('#doneModal').modal('show');
};

const confirmNotDone = (data) => {
dataIdBeingDeleted = data.id;
$('#doneModal').modal('show');
};

const confirmData = () =>{

loadingButtonDelete.value= true;

axios.patch(`/jobtasks/${dataIdBeingDeleted}`)
.then(()=>{
//     retriviedData.value.data = retriviedData.value.data.map(data => {
//     if (data.id === dataIdBeingDeleted) {
//         return { ...data, status_id: data.status_id === 1 ? 2 : 1 }; // Create a new object with the updated status
//     }
//     return data; // Keep the rest of the items unchanged
// });
retriviedData.value.data = retriviedData.value.data.map(data => {
            if (data.id === dataIdBeingDeleted) {
                // Determine the new status name based on the updated status_id
                const newStatusId = data.status_id === 1 ? 2 : 1;
                const newStatusName = newStatusId === 1 ? 'Executado' : 'Não Executado';
                
                return { 
                    ...data, 
                    status_id: newStatusId, 
                    status: { 
                        ...data.status, 
                        name: newStatusName 
                    } 
                };
            }
            return data;
        });
 $('#doneModal').modal('hide');

 toastr.success('Registro confirmado com sucesso');

}).catch(()=>{
 toastr.error('Erro ao apagar');
 loadingButtonDelete.value= false;
 $('#doneModal').modal('hide');
}).finally(()=>{
 loadingButtonDelete.value= false;
});
}

watch(searchQuery,debounce(()=>{
    getData();
},300));

watch(searchQueryMcscr,debounce(()=>{
    getDataMcscr();
},300));

onMounted(()=>{
    getData();
    getDataMcscr();
    
})
</script>

<template>
    <div v-if="!loadingDiv">
        <h1 class="h3 mb-3">Tarefas</h1>
        <div class="row">
						<div class="col-12 col-lg-12">
							<div class="tab">
								<ul class="nav nav-tabs" role="tablist">
									<li class="nav-item" role="presentation"><a class="nav-link active" href="#tab-1" data-bs-toggle="tab" role="tab" aria-selected="true">Tarefas</a></li>
									<li class="nav-item" role="presentation"><a class="nav-link" href="#tab-2" data-bs-toggle="tab" role="tab" aria-selected="false" tabindex="-1">JobCards Recomendações</a></li>
								</ul>
								<div class="tab-content">
									<div class="tab-pane active show" id="tab-1" role="tabpanel">
										<div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Tabela das Tarefas Recomendações do sistema. {{ retriviedData.total }} registros encontrados.</h5>
                                        <h6 class="card-subtitle text-muted">Para procurar, digite na caixa de procura</h6>

                                        <!-- <router-link to="/admin/jobtasks/create" class="btn btn-pill btn-primary mt-3"><vue-feather type="plus"></vue-feather>Adicionar</router-link>  -->

                                        <br>

                                        <form class="d-none d-sm-inline-block mt-3">
                                            <div class="input-group input-group-navbar">
                                                <input type="text" class="form-control" v-model="searchQuery" placeholder="Procurar..." aria-label="Search">
                                            </div>
                                        </form>
                                        <br>
                                        <form class="d-none d-sm-inline-block mt-3">
                                            <div class="input-group input-group-navbar">
                                                <select name="date_range" @change="getData(1,destinationfilterjobs)" v-model="destinationfilterjobs" class="form-control">
                                                        <option selected disabled>Select Destination</option>  
                                                        <option :value="destination.id" v-for="destination in destinations" :key="destination.id">{{ destination.name }}</option>                          
                                                </select>                       
                                            </div>
                                        </form>
								    </div>
                                    
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Recomendação</th>
                                                        <th>ID Job Card</th>
                                                        <th>Clientes</th>
                                                        <th>Área</th>
                                                        <th>Equipamento/Ref</th>
                                                        <th>Frota</th>
                                                        <th>Estado</th>
                                                        <th>Ações</th>
                                                    </tr>
                                                </thead>
                                                <tbody v-if="retriviedData.data.length > 0">
                                                    <tr  v-for="(actualData,index) in retriviedData.data" :key="actualData.id">
                                                        <td>#{{ index + 1 }}</td>
                                                        <td>{{ actualData.task}}</td>
                                                        <td>{{ actualData.mcscr_id}}</td>
                                                        <td>{{ actualData.destination.name}}</td>
                                                        <td>{{ actualData.area.name}}</td>
                                                        <td>{{ actualData.equipment.name}} / {{ actualData.equipment.ref}}</td>
                                                        <td>{{ actualData.type_equipment.name}}</td>
                                                        <td>
                                                            <span v-if="actualData.status_id == 1" class="badge bg-success">
                                                                {{actualData.status.name}}
                                                            </span>
                                                            <span v-if="actualData.status_id == 2" class="badge bg-danger">
                                                                {{actualData.status.name}} 
                                                            </span>
                                                            <span v-if="actualData.status_id == 5" class="badge bg-warning">
                                                                {{actualData.status.name}} 
                                                            </span>
                                                        </td>
                                                        <td>

                                                            <router-link v-if="actualData.status_id == 5" :to="'/admin/mcscr/'+actualData.generated_mcscr_id+'/edit'"><vue-feather type="edit-2"></vue-feather></router-link>
                                                            <router-link v-if="actualData.status_id == 5" :to="'/admin/mcscr/'+actualData.generated_mcscr_id"><vue-feather type="eye"></vue-feather></router-link> 
                                                            
                                                            <router-link v-if="actualData.status_id == 2" :to="'/admin/jobtasks/create/'+actualData.id"><vue-feather type="edit-2"></vue-feather></router-link>
                                                            <router-link v-if="actualData.status_id == 2" :to="'/admin/mcscr/'+actualData.mcscr_id"><vue-feather type="eye"></vue-feather></router-link> 
                                                            <!-- <a v-if="actualData.status_id == 2" href="#" @click.prevent="confirmDone(actualData)"><vue-feather type="check"></vue-feather></a>
                                                            <a v-if="actualData.status_id == 1" href="#" @click.prevent="confirmNotDone(actualData)"><vue-feather type="x"></vue-feather></a> -->
                                                        </td>
                                                    </tr>
                                                </tbody>
                                                <tbody v-else>
                                                    <tr>
                                                    <td colspan="10" align="center">Nenhum resultado encontrado</td>
                                                    </tr>
                                                </tbody>
                                            </table>
								        </div>
                                    </div>
                                   
								
                                </div>
                                <Bootstrap4Pagination :data="retriviedData" @pagination-change-page="getData"/>
                            </div>
                        </div>
									</div>
									<div class="tab-pane" id="tab-2" role="tabpanel">
										<div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Tabela dos JobCards das Recomendações do sistema. {{ retriviedDataMcscr.total }} registros encontrados.</h5>
                                        <h6 class="card-subtitle text-muted">Para procurar, digite na caixa de procura</h6>


                                        <!-- <router-link to="/admin/calendars" class="btn btn-pill btn-primary mt-3 ml-3"><vue-feather type="calendar"></vue-feather>Calendário</router-link>  -->

                                        <br>

                                        <form class="d-none d-sm-inline-block mt-3">
                                            <div class="input-group input-group-navbar">
                                                <input type="text" class="form-control" v-model="searchQueryMcscr" placeholder="Procurar nome..." aria-label="Search">
                                            </div>
                                        </form>
                                        <div class="btn-group ml-5 flex-wrap" role="group" aria-label="Basic example">
                                            <button @click="getDataMcscr(1)" class="btn btn-secondary">
                                                <span class="mr-1">Todos</span>
                                                <span  class="badge bg-ligth">{{ total }} </span>
                                            </button>
                                            <button @click="getDataMcscr(1,mcscrStatus.terminado)" class="btn btn-ligth">
                                                <span class="mr-1">Terminado</span>
                                                <span  class="badge bg-success">{{terminado}}</span>
                                            </button>
                                            <button @click="getDataMcscr(1,mcscrStatus.aguardaaprovacao)" class="btn btn-light">
                                                <span class="mr-1">Aguardando Aprovação</span>
                                                <span  class="badge bg-warning">{{aprovacao}}</span>
                                            </button>
                                            <button @click="getDataMcscr(1,mcscrStatus.emexecucao)" class="btn btn-light">
                                                <span class="mr-1">Em execução</span>
                                                <span  class="badge bg-danger">{{execucao}}</span>
                                            </button>
                                            <button @click="getDataMcscr(1,mcscrStatus.emdiagnostico)" class="btn btn-light">
                                                <span class="mr-1">Em Diagnóstico</span>
                                                <span  class="badge bg-primary">{{diagnostico}}</span>
                                            </button>
                                            <button @click="getDataMcscr(1,mcscrStatus.pendente)" class="btn btn-light">
                                                <span class="mr-1">Pendente</span>
                                                <span  class="badge bg-info">{{pendente}}</span>
                                            </button>
                                            <button @click="getDataMcscr(1,mcscrStatus.programado)" class="btn btn-light">
                                                <span class="mr-1">Programado</span>
                                                <span  class="badge bg-info">{{programado}}</span>
                                            </button>
                                        </div>
                                        <br>
                                        <form class="d-none d-sm-inline-block mt-3">
                                            <div class="input-group input-group-navbar">
                                                <select name="date_range" @change="getDataMcscr(1,null,destinationfilter)" v-model="destinationfilter" class="form-control">
                                                        <option selected disabled>Select Destination</option>  
                                                        <option :value="destination.id" v-for="destination in destinations" :key="destination.id">{{ destination.name }}</option>                          
                                                </select>                       
                                            </div>
                                        </form>
								    </div>
                                    
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>ID</th>
                                                        <th>Estado</th>
                                                        <th>Equipamento/Ativo</th>
                                                        <th>Clientes</th>
                                                        <th>Aberto</th>
                                                        <th>Fechado</th>
                                                        <th>Tempo paralizado</th>
                                                        <th>Motivo</th>
                                                        <th>Previsão de Saída</th>
                                                        <th>Ações</th>
                                                    </tr>
                                                </thead>
                                                <tbody v-if="retriviedDataMcscr.data.length > 0">
                                                    <tr  v-for="(actualData,index) in retriviedDataMcscr.data" :key="actualData.id">
                                                        <td>#{{ index + 1 }}</td>
                                                        <td>{{ actualData.id }}</td>
                                                        <td>
                                                            <span v-if="actualData.mcscr_status_id == 1" class="badge bg-success">
                                                                {{ actualData.mcscr_status.name}}
                                                            </span>
                                                            <span v-if="actualData.mcscr_status_id == 2" class="badge bg-warning">
                                                                {{ actualData.mcscr_status.name}}
                                                            </span>
                                                            <span v-if="actualData.mcscr_status_id == 3" class="badge bg-danger">
                                                                {{ actualData.mcscr_status.name}}
                                                            </span>
                                                            <span v-if="actualData.mcscr_status_id == 4" class="badge bg-info">
                                                                {{ actualData.mcscr_status.name}}
                                                            </span>
                                                            <span v-if="actualData.mcscr_status_id == 5" class="badge bg-primary">
                                                                {{ actualData.mcscr_status.name}}
                                                            </span>
                                                            <span v-if="actualData.mcscr_status_id == 6" class="badge bg-info">
                                                                {{ actualData.mcscr_status.name}}
                                                            </span>
                                                        </td>
                                                        <td>{{ actualData.equipment.name}} / {{ actualData.equipment.ref}}</td>
                                                        <td>{{ actualData.destination.name}}</td>
                                                        <td>{{ moment(actualData.opened_at).format('DD-MM-YYYY H:mm') }} </td>
                                                        <td>{{ actualData.closed_at==null ? '-----' : moment(actualData.closed_at).format('DD-MM-YYYY H:mm')}}</td>
                                                        <td class="text-danger">
                                                            {{ actualData.closed_at == null ? moment().diff(actualData.opened_at,'hours')+' Horas ('+moment().diff(actualData.opened_at,'minutes')+' Minutos)' :  
                                                               
                                                               moment(actualData.closed_at).diff(actualData.opened_at,'hours')+' Horas ('+moment(actualData.closed_at).diff(actualData.opened_at,'minutes')+' Minutos)'
                                                            }}
                                                            <!-- {{ moment().diff(actualData.opened_at,'hours') }}Horas ({{ moment().diff(actualData.opened_at,'minutes') }} Minutos) -->
                                                        </td>
                                                        <td>{{ actualData.reason }}</td>
                                                        <td>{{ moment(actualData.output_forecast).format('DD-MM-YYYY H:mm')}}</td>
                                                        <td>
                                                            <router-link :to="'/admin/mcscr/'+actualData.id+'/edit'"><vue-feather type="edit-2"></vue-feather></router-link>
                                                            <!-- <router-link :to="'/admin/mcscr/'+actualData.id+'/edit'" ><vue-feather type="edit-2"></vue-feather></router-link> -->
                                                            <router-link :to="'/admin/mcscr/'+actualData.id"><vue-feather type="eye"></vue-feather></router-link> 
                                                            <!-- <a href="#" @click.prevent="confirmDeletion(actualData)"><vue-feather type="trash"></vue-feather></a> -->
                                                        </td>
                                                    </tr>
                                                </tbody>
                                                <tbody v-else>
                                                    <tr>
                                                    <td colspan="10" align="center">Nenhum resultado encontrado</td>
                                                    </tr>
                                                </tbody>
                                            </table>
								        </div>
                                    </div>

                                </div>
                                <Bootstrap4Pagination :data="retriviedDataMcscr" @pagination-change-page="getDataMcscr"/>
                            </div>
                        </div>
									</div>
								</div>
							</div>
						</div>
					</div>
                        

    <div class="modal" id="doneModal" tabindex="-1" role="dialog" aria-labelledby="doneModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLongTitle">Deseja mesmo realizar esta ação?</h5>
          
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
            Ao confirmar, irá marcar como executada/não executada esta tarefa.
        </div>
        <div class="modal-footer">
          
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                <button @click.prevent="confirmData" type="button" class="btn btn-primary" :disabled="loadingButtonDelete">
                    <div v-if="loadingButtonDelete" class="spinner-border spinner-border-sm" role="status"></div>
                    <span v-else>Confirmar</span>
                </button>
         
          
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