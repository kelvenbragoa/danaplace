<script setup>

import {onMounted, ref, onUpdated,onBeforeMount ,reactive,watch} from 'vue';
import axios from 'axios';
import {useToastr} from '../../../toastr';
import moment from 'moment'
import {debounce} from 'lodash';
import { Bootstrap4Pagination } from 'laravel-vue-pagination';
import VueFeather from 'vue-feather';

const toastr = useToastr();
const searchQuery = ref(null);
const loadingDiv = ref(true);
const loadingtest = ref(true);
const loadingButtonDelete = ref(false);
const hello = ref('')
let startTime = moment();
const taskmcscrStatus = {'terminado':2,'execucao':3,'programado':4}

const total = ref(0)
const terminado = ref(0)
const programado = ref(0)
const execucao = ref(0)


const retriviedData = ref({'data': []})

let dataIdBeingDeleted = ref(null);


 const getData = async (page = 1,status) => {
  axios.get(`/destination-taskmcscr?page=${page}`,
      {
        params:{
          query: searchQuery.value,
          status:status
        }
      })
       .then((response)=>{
        retriviedData.value = response.data.taskmcscr;
        terminado.value = response.data.terminado;
        programado.value = response.data.programado;
        execucao.value = response.data.execucao;
        total.value = response.data.total;
        loadingDiv.value=false;

        
       })

       
}

const confirmDeletion = (data) => {

dataIdBeingDeleted = data.id;

$('#deleteModal').modal('show');
// axios.post('/categories',values).then((response)=>{

//   categories.value.unshift(response.data);
//   $('#createCategory').modal('hide');
//   resetForm();
// })
};

const deleteData = () =>{

loadingButtonDelete.value= true;

axios.delete(`/taskmcscr/${dataIdBeingDeleted}`)
.then(()=>{
 retriviedData.value.data = retriviedData.value.data.filter(data=>data.id !== dataIdBeingDeleted); 
 $('#deleteModal').modal('hide');

 toastr.success('Registro apagada com sucesso');

}).catch(()=>{
 toastr.error('Erro ao apagar');
 loadingButtonDelete.value= false;
 $('#deleteModal').modal('hide');
}).finally(()=>{
 loadingButtonDelete.value= false;
});
}

watch(searchQuery,debounce(()=>{
    getData();
},300));

// const changeText = () =>{
//     hello.value = 'MCSHELLO'
//     loadingtest.value = false
// }

onMounted(()=>{
    getData();
    
})

// onUpdated(()=>{
//     setTimeout(() => {
//           changeText()
//         }, 2000);
    
// })
</script>

<template>
    <div v-if="!loadingDiv">
        <h1 class="h3 mb-3">Actividades Agendadas</h1>

                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Tabela dos Actividades Agendadas do sistema. {{ retriviedData.total }} registros encontrados.</h5>
                                        <h6 class="card-subtitle text-muted">Para procurar, digite na caixa de procura</h6>

                                        <!-- <router-link to="/admin/taskmcscr/create" class="btn btn-pill btn-primary mt-3"><vue-feather type="plus"></vue-feather>Adicionar</router-link>  -->

                                        <router-link to="/admin/destination/calendars" class="btn btn-pill btn-primary mt-3 ml-3"><vue-feather type="calendar"></vue-feather>Calendário</router-link> 



                                        <br>

                                        <form class="d-none d-sm-inline-block mt-3">
                                            <div class="input-group input-group-navbar">
                                                <input type="text" class="form-control" v-model="searchQuery" placeholder="Procurar nome..." aria-label="Search">
                                            </div>
                                        </form>

                                        <div class="btn-group ml-5 flex-wrap" role="group" aria-label="Basic example">
                                            <button @click="getData(1)" class="btn btn-secondary">
                                                <span class="mr-1">Todos</span>
                                                <span  class="badge bg-ligth">{{ total }} </span>
                                            </button>
                                            <button @click="getData(1,taskmcscrStatus.terminado)" class="btn btn-ligth">
                                                <span class="mr-1">Terminado</span>
                                                <span  class="badge bg-success">{{terminado}}</span>
                                            </button>
                                            
                                            <button @click="getData(1,taskmcscrStatus.execucao)" class="btn btn-light">
                                                <span class="mr-1">Em execução</span>
                                                <span  class="badge bg-danger">{{execucao}}</span>
                                            </button>
                                            <button @click="getData(1,taskmcscrStatus.programado)" class="btn btn-light">
                                                <span class="mr-1">Programado</span>
                                                <span  class="badge bg-info">{{programado}}</span>
                                            </button>
                                           
                                        </div>
								    </div>
                                    
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Estado</th>
                                                        <th>Equipamento/Activo</th>
                                                        <th>Plano de Actividade</th>
                                                        <th>Actividade</th>
                                                        <th>Aberto</th>
                                                        <th>Fechado</th>
                                                        <th>Duração</th>
                                                        <th>Programado</th>
                                                        <th>Ações</th>
                                                    </tr>
                                                </thead>
                                                <tbody v-if="retriviedData.data.length > 0">
                                                    <tr  v-for="(actualData,index) in retriviedData.data" :key="actualData.id">
                                                        <td>#{{ index + 1 }}</td>
                                                        <td>
                                                            <span v-if="actualData.task_mcscr_status_id == 1" class="badge bg-warning">
                                                                {{ actualData.task_mcscr_status.name}}
                                                            </span>
                                                            <span v-if="actualData.task_mcscr_status_id == 3" class="badge bg-danger">
                                                                {{ actualData.task_mcscr_status.name}}
                                                            </span>
                                                            <span v-if="actualData.task_mcscr_status_id == 2" class="badge bg-success">
                                                                {{ actualData.task_mcscr_status.name}}
                                                            </span>
                                                            <span v-if="actualData.task_mcscr_status_id == 4" class="badge bg-info">
                                                                {{ actualData.task_mcscr_status.name}} ({{moment(actualData.schedule_for).format('DD-MM-YYYY H:mm')}})
                                                            </span>
                                                        </td>
                                                        <td>{{ actualData.equipment.name}}/{{ actualData.equipment.ref}}</td>
                                                        <td>{{ actualData.task_plan.name}}</td>
                                                        <td>{{ actualData.task_plan_task.name}}</td>
                                                        <td>{{ actualData.opened_at!=null ? moment(actualData.opened_at).format('DD-MM-YYYY H:mm'):'------' }} </td>
                                                        <td>{{ actualData.closed_at!=null ?moment(actualData.closed_at).format('DD-MM-YYYY H:mm') : '------' }} </td>
                                                        <td class="text-danger">
                                                            <!-- {{ moment(actualData.closed_at).diff(actualData.opened_at,'hours')+' Horas ('+moment(actualData.closed_at).diff(actualData.opened_at,'minutes')+' Minutos)' }} -->
                                                            {{  actualData.opened_at!=null ? (actualData.closed_at == null ? moment().diff(actualData.opened_at,'hours')+' Horas ('+moment().diff(actualData.opened_at,'minutes')+' Minutos)' :  
                                                               
                                                               moment(actualData.closed_at).diff(actualData.opened_at,'hours')+' Horas ('+moment(actualData.closed_at).diff(actualData.opened_at,'minutes')+' Minutos)'):'------'
                                                            }}
                                                        </td>
                                                        <td>{{ actualData.schedule_by_user.firstName}} {{ actualData.schedule_by_user.lastName}}</td>
                                                        

                                                        <td>
                                                            <!-- <router-link :to="'/admin/taskmcscr/'+actualData.id+'/edit'" v-if="actualData.task_mcscr_status_id != 2"><vue-feather type="edit-2"></vue-feather></router-link> -->
                                                            <router-link :to="'/admin/destination/taskmcscr/'+actualData.id"><vue-feather type="eye"></vue-feather></router-link> 
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
                                <Bootstrap4Pagination :data="retriviedData" @pagination-change-page="getData"/>
                            </div>
                        </div>

    <div class="modal" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">Deseja mesmo eliminar este item.</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Ao apagar este item, irá apagar todos os registros relacionados a ele.
                </div>
                <div class="modal-footer">
                
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                        <button @click.prevent="deleteData" type="button" class="btn btn-danger" :disabled="loadingButtonDelete">
                            <div v-if="loadingButtonDelete" class="spinner-border spinner-border-sm" role="status"></div>
                            <span v-else>Apagar registro</span>
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