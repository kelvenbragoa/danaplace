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
import { Bar } from 'vue-chartjs'
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js'

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale)

let retrievedData =ref([]);
let loadingSubmit =ref([true]);
let loadingDiv =ref([true]);
const router = useRouter();
let self = this;
const searchQuery = ref(null);
let mcscrs = ref([]);







const getData = (page = 1) => {
  axios.get(`/malfunctions/+${router.currentRoute.value.params.id}?page=${page}`, 
  {
        params:{
          query: searchQuery.value
        }
      })
       .then((response)=>{
        loadingDiv.value=false;
        retrievedData.value = response.data.malfunctions;
        mcscrs.value = response.data.mcscrs
        chartData.value.datasets[0].data = response.data.dataChartMalfunctions;
        chartData2.value.datasets[0].data = response.data.dataChartAverageMalfunctions;
       }).catch(()=>{
        loadingDiv.value=false;
       })
}

const chartData = ref({
          labels: [  'Janeiro',
                    'Fevereiro',
                    'Março',
                    'Abril',
                    'Maio',
                    'Junho',
                    'Julho',
                    'Agosto',
                    'Setembro',
                    'Outubro',
                    'Novembro',
                    'Dezembro' 
                ],
                datasets: [
                          {
                            label: 'Avaria por mês',
                            backgroundColor: '#50B3C7',
                            data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]
                          },
                        ]
        })
  const  chartOptions = {
          responsive: true, 
        }

        const chartData2 = ref({
            labels: [ 
                    'Janeiro',
                    'Fevereiro',
                    'Março',
                    'Abril',
                    'Maio',
                    'Junho',
                    'Julho',
                    'Agosto',
                    'Setembro',
                    'Outubro',
                    'Novembro',
                    'Dezembro' 
                ],
                datasets: [
                          {
                            label: 'Média de Horas de Reparação',
                            backgroundColor: '#50B3C7',
                            data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]
                          },
                        ]
        })
  const  chartOptions2 = {
          responsive: true, 
        }





onMounted(()=>{
  
  getData();
})
</script>

<template>
    <div v-if="!loadingDiv">

        <h1 class="h3 mb-3">Tipos de Avarias</h1>
        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Tipos de Avarias: {{ retrievedData.name }}</h5>

                                        <router-link to="/manager/maintenance/malfunctions" class="btn btn-pill btn-primary mt-3"><vue-feather type="arrow-left"></vue-feather>Voltar</router-link> 

                                       
								    </div>
                                    
                                    <div class="card-body">
                                          
                                        <div class="row">
                                            <div class="col-xl-12 col-xxl-12 d-flex">
                                                <div class="w-100">
                                                    <p><strong>Nome da avarias:</strong>  {{ retrievedData.name }}</p>
                                                    <p><strong>Obras:</strong>{{ retrievedData.mcscr.length }}</p>
                                                    <p><strong>Obras Concluídas:</strong>{{ retrievedData.mcscr_done.length }}</p>
                                                    <p><strong>Obras Em Execução:</strong>{{ retrievedData.mcscr_not_done.length }}</p>
                                                    <p><strong>Aguardando Aprovação:</strong>{{ retrievedData.mcscr_approval.length }}</p>

                                                    <hr>
                                                    <div class="row">

                                                        <div class="col-12 col-lg-6">
                                                            <div class="card">
                                                                <div class="card-header">
                                                                    <h5 class="card-title">Avarias {{ retrievedData.name }} por mês</h5>
                                                                    <h6 class="card-subtitle text-muted">Avarias  {{ retrievedData.name }} mês durante corrente ano.</h6>
                                                                </div>
                                                                <div class="card-body">
                                                                    <div class="chart">
                                                                        <Bar
                                                                            id="my-chart-id"
                                                                            :options="chartOptions"
                                                                            :data="chartData"
                                                                            />
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-12 col-lg-6">
                                                            <div class="card">
                                                                <div class="card-header">
                                                                    <h5 class="card-title">Média de Horas de Reparação de Avarias {{ retrievedData.name }} por mês</h5>
                                                                    <h6 class="card-subtitle text-muted">Média de Horas de Reparação de Avarias {{ retrievedData.name }} por mês durante corrente ano.</h6>
                                                                </div>
                                                                <div class="card-body">
                                                                    <div class="chart">
                                                                        <Bar
                                                                            id="my-chart-id"
                                                                            :options="chartOptions2"
                                                                            :data="chartData2"
                                                                            />
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        </div>


                                                    <hr>

                                                    <form class="d-none d-sm-inline-block mt-3">
                                                        <div class="input-group input-group-navbar">
                                                            <input type="text" class="form-control" v-model="searchQuery" placeholder="Procurar mcscr..." aria-label="Search">
                                                        </div>
                                                    </form>

                                                    <div class="table-responsive">
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Estado</th>
                                                        <th>Equipamento/Ativo</th>
                                                        <th>Aberto</th>
                                                        <th>Fechado</th>
                                                        <th>Tempo paralizado</th>
                                                        <th>Motivo</th>
                                                        <th>Previsão de Saída</th>
                                                        <th>Ações</th>
                                                    </tr>
                                                </thead>
                                                <tbody v-if="mcscrs.data.length > 0">
                                                    <tr  v-for="(actualData,index) in mcscrs.data" :key="actualData.id">
                                                        <td>#{{ index + 1 }}</td>
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
                                                        </td>
                                                        <td>{{ actualData.equipment.name}}</td>
                                                        <td>{{ moment(actualData.opened_at).format('DD-MM-YYYY H:mm') }} </td>
                                                        <td>{{ actualData.closed_at==null ? '-----' : moment(actualData.closed_at).format('DD-MM-YYYY H:mm')}}</td>
                                                        <td class="text-danger">
                                                            {{ actualData.closed_at == null ? moment().diff(actualData.opened_at,'hours')+' Horas ('+moment().diff(actualData.opened_at,'minutes')+' Minutos)' :  
                                                               
                                                               moment(actualData.closed_at).diff(actualData.opened_at,'hours')+' Horas ('+moment(actualData.closed_at).diff(actualData.opened_at,'minutes')+' Minutos)'
                                                            }}
                                                            <!-- {{ moment().diff(actualData.opened_at,'hours') }}Horas ({{ moment().diff(actualData.opened_at,'minutes') }} Minutos) -->
                                                        </td>
                                                        <td>{{ actualData.reason_id == null ? actualData.reason : actualData.reason_name.name}}</td>
                                                        <td>{{ moment(actualData.output_forecast).format('DD-MM-YYYY H:mm')}}</td>
                                                        <td>
                                                            <router-link :to="'/manager/maintenance/mcscr/'+actualData.id+'/edit'" v-if="actualData.mcscr_status_id != 1"><vue-feather type="edit-2"></vue-feather></router-link>
                                                            <!-- <router-link :to="'/manager/maintenance/mcscr/'+actualData.id+'/edit'" ><vue-feather type="edit-2"></vue-feather></router-link> -->
                                                            <router-link :to="'/manager/maintenance/mcscr/'+actualData.id"><vue-feather type="eye"></vue-feather></router-link> 
                                                            <!-- <a href="#" @click.prevent="confirmDeletion(actualData)"><vue-feather type="trash"></vue-feather></a> -->
                                                        </td>
                                                    </tr>
                                                </tbody>
                                                <tbody v-else>
                                                    <tr>
                                                    <td colspan="9" align="center">Nenhum resultado encontrado</td>
                                                    </tr>
                                                </tbody>
                                            </table>
								        </div>
                                        <Bootstrap4Pagination :data="mcscrs" @pagination-change-page="getData"/>

                                            
                                                    <!-- <div class="row">
                                                        <div class="col-sm-3">
                                                            <div class="card">
                                                                <div class="card-body">
                                                                    <h5 class="card-title mb-4">Centro de custo</h5>
                                                                    <h1 class="mt-1 mb-3">{{retrievedData.name}}</h1>
                                                                    <div class="mb-1">
                                                                        <span class="text-muted">Código: {{retrievedData.code}}</span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-sm-3">
                                                            <div class="card">
                                                                <div class="card-body">
                                                                    <h5 class="card-title mb-4">Contas Associadas</h5>
                                                                    <h1 class="mt-1 mb-3">{{retrievedData.accounts.length}}</h1>
                                                                    <div class="mb-1">
                                                                        <span class="text-success"> <i class="mdi mdi-arrow-bottom-right"></i> </span>
                                                                        <span class="text-muted"></span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-sm-3">
                                                            <div class="card">
                                                                <div class="card-body">
                                                                    <h5 class="card-title mb-4">Equipamentos/Activos</h5>
                                                                    <h1 class="mt-1 mb-3">0</h1>
                                                                    <div class="mb-1">
                                                                        <span class="text-success"> <i class="mdi mdi-arrow-bottom-right"></i> </span>
                                                                        <span class="text-muted"></span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                    </div> -->
                                                        
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