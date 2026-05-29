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
let searchQuery = ref(null)
let equipments = ref([]);







const getData = (page=1) => {
  axios.get(`/hourdistances/+${router.currentRoute.value.params.id}?page=${page}`,
      {
        params:{
          query: searchQuery.value
        }
      })
       .then((response)=>{
        loadingDiv.value=false;
        retrievedData.value = response.data.hourdistance;
        chartData.value.datasets[0].data = response.data.dataChartHourDistance;
       }).catch(()=>{
        loadingDiv.value=false;
       })
}


watch(searchQuery,debounce(()=>{
    getData();
},300));

const chartData = ref({
          labels: [  '1','2','3','4','5','6','7','8','9','10','11','12','13','14','15','16','17','18','19','20','21','22','23','24','25','26','27','28','29','30','31'
                ],
                datasets: [
                          {
                            label: 'Avaria por mês',
                            backgroundColor: '#50B3C7',
                            data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0,0, 0, 0, 0, 0, 0, 0, 0, 0, 0,0, 0, 0, 0, 0, 0, 0, 0, 0, 0,0]
                          },
                        ]
        })
  const  chartOptions = {
          responsive: true, 
        }
onMounted(()=>{
  
  getData();
})
</script>

<template>
    <div v-if="!loadingDiv">

        <h1 class="h3 mb-3">Horas / Distancia de Operação</h1>
        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Horas / Distancia de Operação: {{ retrievedData.value }}</h5>

                                        <router-link to="/manager/maintenance/hourdistances" class="btn btn-pill btn-primary mt-3"><vue-feather type="arrow-left"></vue-feather>Voltar</router-link> 

                                       
								    </div>
                                    
                                    <div class="card-body">
                                          
                                        <div class="row">
                                            <div class="col-xl-12 col-xxl-12 d-flex">
                                                <div class="w-100">
                                                    <p>Equipamento: {{ retrievedData.equipment.name }}</p>
                                                    <p>Ref: {{ retrievedData.equipment.ref }}</p>
                                                    <p>Destino: {{ retrievedData.destination.name }}</p>
                                                    <p>Area: {{ retrievedData.area.name }}</p>

                                                    <p>Medidor: {{ retrievedData.distance_control.name }}</p>
                                                    <p>Valor: {{ retrievedData.value }}</p>

                                                    <hr>
                                                    <p>Historico de Horas/Distância do Equipamento</p>
                                                    <div class="row">

                                                        <div class="col-12 col-lg-12">
                                                            <div class="card">
                                                                <div class="card-header">
                                                                    <h5 class="card-title">Historico Horas/Distância do Equipamento</h5>
                                                                    <h6 class="card-subtitle text-muted">Historico Horas/Distância do Equipamento por dia durante corrente mês.</h6>
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