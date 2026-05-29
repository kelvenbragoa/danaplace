<script setup>

import axios from 'axios';
import { ref, onMounted, reactive, defineEmits, defineComponent,watch } from "vue";
import moment from 'moment'
import {useToastr} from '../../../../toastr';
import {debounce} from 'lodash';
import {Form, Field} from 'vee-validate';
import { useRouter} from "vue-router";
import * as yup from 'yup';
import VueFeather from 'vue-feather';
import { Bootstrap4Pagination } from 'laravel-vue-pagination';
import { Bar , Pie} from 'vue-chartjs'
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, ArcElement } from 'chart.js'

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale , ArcElement)

let retrievedData =ref([]);
let criticals = ref([]);
let loadingSubmit =ref([true]);
let loadingReconciliation = ref([false]);
let loadingDiv =ref([true]);
const router = useRouter();
let equipments =ref([]);
const searchQuery = ref(null);
let currentvalue = ref([]);
const loadingButtonDelete = ref(false);
let self = this;
let dataIdBeingDeleted = ref(null);
let destination = ref();
const toastr = useToastr();

const selectedMcscrStatus = ref('total');
const totalMcscrCount = ref(0);

const selectedTaskStatus = ref('total');
const totalTaskCount = ref(0);

const selectedFuelStatus = ref('total');
const totalFuelCount = ref(0);

const selectedHourDistanceStatus = ref('total');
const totalHourCount = ref(0);
const totalDistanceCount = ref(0);

const total = ref(0)
const terminado = ref(0)
const pendente = ref(0)
const aprovacao = ref(0)
const diagnostico = ref(0)
const execucao = ref(0)
const peca = ref(0)
const tecnico = ref(0)
const acidente = ref(0)
const previsao = ref(0);

const opened_at_to_closed_at = ref(0);

let unavailable_equipments =ref([]);
let available_equipments =ref([]);
let imobilized_equipments =ref([]);


const isVisibleManutencao = ref(false);
const isVisibleEquipamentos = ref(false);
const isVisibleOperacao = ref(false);
//const isVisibleOperacao = ref(false);



const schema = yup.object({
    
    name: yup.string().required(),
    model: yup.string().required(),
    make: yup.string().required(),
    criticaly_id: yup.string().required(),
    type_equipment_id: yup.string().required(),
    percentage_weigth: yup.string().required(),
  });

const createRecordFunction = (values, actions) => {

 
currentvalue.value = {values};

loadingSubmit.value = true;

const arr = Array.from(values)

axios.post('/typeequipmentcomponent',values).then((response)=>{

// categories.value.unshift(response.data);
// $('#createCategory').modal('hide');
retrievedData.value = response.data.type_equipment;
equipments.value = response.data.equipments;
criticals.value = response.data.criticals;

actions.resetField('name');
actions.resetField('model');
actions.resetField('make');
actions.resetField('criticaly_id');
actions.resetField('percentage_weigth');

toastr.success('Componente criado com sucesso');
}).catch((error)=>{

loadingSubmit.value = false;
toastr.error('Erro ao adicionar. '+error.response.data.message);
if(error.response.data.errors){
   
    actions.setErrors(error.response.data.errors);
}
}).finally(()=>{
loadingSubmit.value = false;

})

};

watch(searchQuery,debounce(()=>{
    getData();
},300));


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

axios.delete(`/typeequipmentcomponent/${dataIdBeingDeleted}`)
.then(()=>{
    components.value.data = components.value.data.filter(data=>data.id !== dataIdBeingDeleted); 
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




const getData = (page=1) => {
  axios.get(`/destinationsfleet/${router.currentRoute.value.params.fleet_id}/destination/${router.currentRoute.value.params.destination_id}?page=${page}`)
       .then((response)=>{
        loadingDiv.value=false;
        retrievedData.value = response.data.type_equipment;
        criticals.value = response.data.criticals;
        equipments.value = response.data.equipments;
        destination.value = response.data.destination;
        totalMcscrCount.value = response.data.totalMcscrCount
        totalTaskCount.value = response.data.totalTaskCount;
        totalFuelCount.value = response.data.totalFuelCount;
        totalHourCount.value = response.data.totalHourCount;
        totalDistanceCount.value = response.data.totalDistanceCount;

        terminado.value = response.data.terminado;
        pendente.value = response.data.pendente;
        aprovacao.value = response.data.aprovacao;
        diagnostico.value = response.data.diagnostico;
        execucao.value = response.data.execucao;

        peca.value = response.data.peca;
        tecnico.value = response.data.tecnico;
        acidente.value = response.data.acidente;

        unavailable_equipments.value = response.data.unavailable_equipments;
        available_equipments.value = response.data.available_equipments;
        imobilized_equipments.value = response.data.imobilized_equipments;

        previsao.value = response.data.previsao;

        opened_at_to_closed_at.value = response.data.opened_at_to_closed_at

        chartData.value.datasets[1].data = response.data.dataChartAvailable;
        chartData.value.datasets[0].data = response.data.dataChartUnAvailable;

        chartData2.value.datasets[0].data = response.data.dataChartMCSCR;
        chartData2.value.datasets[1].data = response.data.dataChartTaskMcscr;

        chartData3.value.datasets[0].data = response.data.dataChartMaterialMCSCR;
        chartData3.value.datasets[1].data = response.data.dataChartMaterialTaskMcscr;

        chartData4.value.datasets[0].data = response.data.dataChartLaborMCSCR;
        chartData4.value.datasets[1].data = response.data.dataChartLaborTaskMcscr;

        chartData5.value.datasets[0].data = response.data.dataChartFuelMonth;
        chartData6.value.datasets[0].data = response.data.dataChartFuelDay;

        chartData7.value.datasets[1].data = response.data.dataChartTaskMcscrDone;
        chartData7.value.datasets[0].data = response.data.dataChartTaskMcscrScheduled;

        chartData8.value.datasets[0].data = response.data.pieChartDuration;
       }).catch(()=>{
        loadingDiv.value=false;
       })
}


const getMcscrCount = ()=>{

    axios.get(`/fleets/+${router.currentRoute.value.params.id}/mcscrcount`,{
      params: {
        date_range: selectedMcscrStatus.value
      }
    })
    .then((response)=>{
      totalMcscrCount.value = response.data.totalMcscrCount;
    })
}

const getTaskCount = ()=>{

axios.get(`/fleets/+${router.currentRoute.value.params.id}/taskcount`,{
  params: {
    date_range: selectedTaskStatus.value
  }
})
.then((response)=>{
  totalTaskCount.value = response.data.totalTaskCount;
})
}

const getFuelCount = ()=>{

axios.get(`/fleets/+${router.currentRoute.value.params.id}/fuelcount`,{
  params: {
    date_range: selectedFuelStatus.value
  }
})
.then((response)=>{
  totalFuelCount.value = response.data.totalFuelCount;
})
}


const getHourDistanceCount = ()=>{

axios.get(`/fleets/+${router.currentRoute.value.params.id}/hourdistancecount`,{
  params: {
    date_range: selectedHourDistanceStatus.value
  }
})
.then((response)=>{
  totalHourCount.value = response.data.totalHourCount;
  totalDistanceCount.value = response.data.totalDistanceCount;
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
                            label: 'Disponibilidade - Ano Passado',
                            backgroundColor: '#f87979',
                            data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]
                          },
                          {
                            label: 'Disponibilidade - Este Ano',
                            backgroundColor: '#50B3C7',
                            data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]
                          }
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
                            label: 'Manutenção Corretiva',
                            // backgroundColor: '#50B3C7',
                            backgroundColor: '#f87979',

                            data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]
                          },
                          {
                            label: 'Manutenção Preventiva',
                            // backgroundColor: '#f87979',
                            backgroundColor: '#50B3C7',
                            data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]
                          }
                        ]
        })
  const  chartOptions2 = {
          responsive: true, 
        }

        const chartData3 = ref({
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
                            label: 'Manutenção Corretiva',
                            // backgroundColor: '#50B3C7',
                            backgroundColor: '#f87979',
                            data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]
                          },
                          {
                            label: 'Manutenção Preventiva',
                            // backgroundColor: '#f87979',
                            backgroundColor: '#50B3C7',
                            data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]
                          }
                        ]
        })
  const  chartOptions3 = {
          responsive: true, 
        }

        const chartData4 = ref({
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
                            label: 'Manutenção Corretiva',
                            // backgroundColor: '#50B3C7',
                            backgroundColor: '#f87979',
                            data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]
                          },
                          {
                            label: 'Manutenção Preventiva',
                            // backgroundColor: '#f87979',
                            backgroundColor: '#50B3C7',
                            data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]
                          }
                        ]
        })
  const  chartOptions4 = {
          responsive: true, 
        }


        const chartData5 = ref({
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
                            label: 'Consumo de Combustivel por mês',
                            backgroundColor: '#50B3C7',
                            data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]
                          },
                         
                        ]
        })
  const  chartOptions5 = {
          responsive: true, 
        }


        const chartData6 = ref({
          labels: [  '1','2','3','4','5','6','7','8','9','10','11','12','13','14','15','16','17','18','19','20','21','22','23','24','25','26','27','28','29','30','31'
                ],
                datasets: [
                          {
                            label: 'Consumo de  Combustivel por dia',
                            backgroundColor: '#50B3C7',
                            data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0,0, 0, 0, 0, 0, 0, 0, 0, 0, 0,0, 0, 0, 0, 0, 0, 0, 0, 0, 0,0]
                          },
                        ]
        })
        
  const  chartOptions6 = {
          responsive: true, 
        }

        const chartData7 = ref({
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
                            label: 'Programado',
                            backgroundColor: '#f87979',
                            data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]
                          },
                          {
                            label: 'Realizado',
                            backgroundColor: '#50B3C7',
                            data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]
                          }
                        ]
        })
  const  chartOptions7 = {
          responsive: true, 
        }

        const chartData8 = ref({
            labels: ['Pendente - Diagnóstico','Diagnóstico - Execução', 'Execução - Aprovação', 'Aprovação - Conclusão'],
                datasets: [
                          
                          {
                            
                            backgroundColor: ['#d64a6e','#4aa0b5', '#4a7ed6', '#f6c344'],
                            data: [0, 0, 0, 0]
                          },
                        ]
        })
  const  chartOptions8 = {
          responsive: true, 
          maintainAspectRatio:true
        }


        function goBackUsingBack() {
    if (router) {
        router.back();
    }
}




onMounted(()=>{
  
  getData();
})
</script>

<template>
    <div v-if="!loadingDiv">

        <h1 class="h3 mb-3">Frota</h1>
        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Frota: {{ retrievedData.name }}</h5>
                                        <h5 class="card-title">Clientes: {{ destination.name }}</h5>

                                        <Button class="btn btn-pill btn-primary mt-3" @click="goBackUsingBack()"><vue-feather type="arrow-left"></vue-feather>Voltar</Button>                                         

                                       
								    </div>
                                    
                                    <div class="card-body">

                                        <div class="row">
                                            <div class="col-xl-12 col-xxl-12 d-flex">
                                                <div class="w-100">
                                                    <div class="row">
                                                        <!-- Card Manutenção -->
                                                        <div class="col-sm-6 col-xl-4">
                                                        <div class="card custom-card" @click="isVisibleManutencao = !isVisibleManutencao">
                                                            <div class="card-body text-center">
                                                            <i class="fas fa-tools icon"></i>
                                                            <h4>Manutenção</h4>
                                                            <i :class="isVisibleManutencao ? 'fas fa-eye eye-icon' : 'fas fa-eye-slash eye-icon'"></i>
                                                            </div>
                                                        </div>
                                                        </div>

                                                        <!-- Card Equipamentos -->
                                                        <div class="col-sm-6 col-xl-4">
                                                        <div class="card custom-card" @click="isVisibleEquipamentos = !isVisibleEquipamentos">
                                                            <div class="card-body text-center">
                                                            <i class="fas fa-cogs icon"></i>
                                                            <h4>Operação</h4>
                                                            <i :class="isVisibleEquipamentos ? 'fas fa-eye eye-icon' : 'fas fa-eye-slash eye-icon'"></i>
                                                            </div>
                                                        </div>
                                                        </div>

                                                        <!-- Card Combustível -->
                                                        <div class="col-sm-6 col-xl-4">
                                                        <div class="card custom-card" @click="isVisibleOperacao = !isVisibleOperacao">
                                                            <div class="card-body text-center">
                                                            <i class="fas fa-dollar-sign icon"></i>
                                                            <h4>Custos</h4>
                                                            <i :class="isVisibleOperacao ? 'fas fa-eye eye-icon' : 'fas fa-eye-slash eye-icon'"></i>
                                                            </div>
                                                        </div>
                                                        </div>

                                                        <!-- Card Horas de Operação -->
                                                        <!-- <div class="col-sm-6 col-xl-3">
                                                        <div class="card custom-card" @click="isVisibleOperacao = !isVisibleOperacao">
                                                            <div class="card-body text-center">
                                                            <i class="fas fa-clock icon"></i>
                                                            <h4>Horas de Operação</h4>
                                                            <i :class="isVisibleOperacao ? 'fas fa-eye eye-icon' : 'fas fa-eye-slash eye-icon'"></i>
                                                            </div>
                                                        </div>
                                                        </div> -->
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                                             
                                          
                                        <div class="row">
                                            <div class="col-xl-12 col-xxl-12 d-flex">
                                                <div class="w-100">
                                                    <div class="row" v-show="isVisibleManutencao">
                                                        <div class="col-sm-6 col-xl-2">
                                                            <div class="card">
                                                                <div class="card-body">
                                                                    <div class="row">
                                                                        <div class="col mt-0">
                                                                            <h5 class="card-title">Disponibilidade</h5>
                                                                            <h5 class="card-title">Geral</h5>
                                                                        </div>

                                                                        <div class="col-auto">
                                                                            <div class="stat text-primary">
                                                                                <vue-feather type="activity"></vue-feather>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <h1 class="mt-1 mb-3">
                                                                        {{ 
                                                                        available_equipments.length + unavailable_equipments.length != 0 ?
                                                                        Math.round((100 * available_equipments.length) / (available_equipments.length + unavailable_equipments.length)) : 0
                                                                        }}%
                                                                    </h1> 
                                                                </div>
                                                            </div>
                                                         </div>

                                                         <div class="col-sm-6 col-xl-2">
                                                            <div class="card">
                                                                <div class="card-body">
                                                                    <div class="row">
                                                                        <div class="col mt-0">
                                                                            <h5 class="card-title">MCSCR</h5>
                                                                            <h5 class="card-title">Previsões do dia</h5>
                                                                        </div>

                                                                        <div class="col-auto">
                                                                            <div class="stat text-primary">
                                                                                <vue-feather type="calendar"></vue-feather>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <h1 class="mt-1 mb-3">{{ previsao }}</h1> 
                                                                </div>
                                                            </div>
                                                         </div>

                                                         <div class="col-sm-6 col-xl-2">
                                                            <div class="card">
                                                                <div class="card-body">
                                                                    <div class="row">
                                                                        <div class="col mt-0">
                                                                            <h5 class="card-title">MCSCR</h5>
                                                                            <h5 class="card-title">Aguarda Peças</h5>
                                                                        </div>

                                                                        <div class="col-auto">
                                                                            <div class="stat text-primary">
                                                                                <vue-feather type="pause"></vue-feather>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <h1 class="mt-1 mb-3">{{ peca }}</h1> 
                                                                </div>
                                                            </div>
                                                         </div>

                                                         <div class="col-sm-6 col-xl-2">
                                                            <div class="card">
                                                                <div class="card-body">
                                                                    <div class="row">
                                                                        <div class="col mt-0">
                                                                            <h5 class="card-title">MCSCR</h5>
                                                                            <h5 class="card-title">Aguarda Técnicos</h5>
                                                                        </div>

                                                                        <div class="col-auto">
                                                                            <div class="stat text-primary">
                                                                                <vue-feather type="pause"></vue-feather>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <h1 class="mt-1 mb-3">{{ tecnico }}</h1> 
                                                                </div>
                                                            </div>
                                                         </div>

                                                         <div class="col-sm-6 col-xl-2">
                                                            <div class="card">
                                                                <div class="card-body">
                                                                    <div class="row">
                                                                        <div class="col mt-0">
                                                                            <h5 class="card-title">MCSCR</h5>
                                                                            <h5 class="card-title">Acidente</h5>
                                                                        </div>

                                                                        <div class="col-auto">
                                                                            <div class="stat text-primary">
                                                                                <vue-feather type="pause"></vue-feather>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <h1 class="mt-1 mb-3">{{ acidente }}</h1> 
                                                                </div>
                                                            </div>
                                                         </div>

                                                    </div>
                                                    <div class="row" v-show="isVisibleManutencao">
                                                        <div class="col-sm-6 col-xl-2">
                                                            <div class="card">
                                                                <div class="card-body">
                                                                    <div class="row">
                                                                        <div class="col mt-0">
                                                                            <h5 class="card-title">MCSCR</h5>
                                                                            <h5 class="card-title">Terminados</h5>
                                                                        </div>

                                                                        <div class="col-auto">
                                                                            <div class="stat text-primary">
                                                                                <vue-feather type="settings"></vue-feather>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <h1 class="mt-1 mb-3">{{ terminado }}</h1> 
                                                                </div>
                                                            </div>
                                                         </div>

                                                         <div class="col-sm-6 col-xl-2">
                                                            <div class="card">
                                                                <div class="card-body">
                                                                    <div class="row">
                                                                        <div class="col mt-0">
                                                                            <h5 class="card-title">MCSCR</h5>
                                                                            <h5 class="card-title">Aguardando Aprovação</h5>
                                                                        </div>

                                                                        <div class="col-auto">
                                                                            <div class="stat text-primary">
                                                                                <vue-feather type="settings"></vue-feather>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <h1 class="mt-1 mb-3">{{ aprovacao }}</h1> 
                                                                </div>
                                                            </div>
                                                         </div>

                                                         <div class="col-sm-6 col-xl-2">
                                                            <div class="card">
                                                                <div class="card-body">
                                                                    <div class="row">
                                                                        <div class="col mt-0">
                                                                            <h5 class="card-title">MCSCR</h5>
                                                                            <h5 class="card-title">Em Execução</h5>
                                                                        </div>

                                                                        <div class="col-auto">
                                                                            <div class="stat text-primary">
                                                                                <vue-feather type="settings"></vue-feather>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <h1 class="mt-1 mb-3">{{ execucao }}</h1> 
                                                                </div>
                                                            </div>
                                                         </div>

                                                         <div class="col-sm-6 col-xl-2">
                                                            <div class="card">
                                                                <div class="card-body">
                                                                    <div class="row">
                                                                        <div class="col mt-0">
                                                                            <h5 class="card-title">MCSCR</h5>
                                                                            <h5 class="card-title">Em Diagnóstico</h5>
                                                                        </div>

                                                                        <div class="col-auto">
                                                                            <div class="stat text-primary">
                                                                                <vue-feather type="settings"></vue-feather>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <h1 class="mt-1 mb-3">{{ diagnostico }}</h1> 
                                                                </div>
                                                            </div>
                                                         </div>

                                                         <div class="col-sm-6 col-xl-2">
                                                            <div class="card">
                                                                <div class="card-body">
                                                                    <div class="row">
                                                                        <div class="col mt-0">
                                                                            <h5 class="card-title">MCSCR</h5>
                                                                            <h5 class="card-title">Pendente</h5>
                                                                        </div>

                                                                        <div class="col-auto">
                                                                            <div class="stat text-primary">
                                                                                <vue-feather type="settings"></vue-feather>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <h1 class="mt-1 mb-3">{{ pendente }}</h1> 
                                                                </div>
                                                            </div>
                                                         </div>
                                                    </div>
                                                    <!-- <div class="row">
                                                        <div class="col">
                                                            <p>Frota: {{ retrievedData.name }}</p>
                                                            <p style="color:green">Equipamentos Disponíveis: {{ retrievedData.available_equipments.length}}</p>
                                                            <p style="color:red">Equipamentos Indisponíveis: {{ retrievedData.unavailable_equipments.length}}</p>
                                                            <p style="color:red">Equipamentos Não operacionais: {{ retrievedData.imobilized_equipments.length}}</p>
                                                        </div>
                                                        <div class="col">
                                                            <p>Disponibilidade Geral:</p>
                                                            <h2> 
                                                                {{ 
                                                                        retrievedData.available_equipments.length + retrievedData.unavailable_equipments.length != 0 ?
                                                                        Math.round((100 * retrievedData.available_equipments.length) / (retrievedData.available_equipments.length + retrievedData.unavailable_equipments.length)) : 0
                                                                    }}%
                                                            </h2>
                                                        </div>
                                                    </div> -->

                                                    
                                            
                                                    

                                                    <div class="row">
                                                        <div class="col-sm-6 col-xl-2" v-show="isVisibleManutencao">
                                                            <div class="card">
                                                                <div class="card-body">
                                                                    <div class="row">
                                                                        <div class="col mt-0">
                                                                            <h5 class="card-title">Manutenção Corretiva</h5>
                                                                        </div>
                                                                    </div>
                                                                    <h1 class="mt-1 mb-3">{{ totalMcscrCount }}</h1>
                                                                    <div class="mb-0">
                                                                        <select name="date_range" @change="getMcscrCount()" v-model="selectedMcscrStatus" class="form-control">
                                                                            <option selected value="total" >Total</option>
                                                                            <option value="today" >Hoje</option>
                                                                            <option value="30" >30 dias</option>
                                                                            <option value="60" >60 dias</option>
                                                                            <option value="360" >360 dias</option>
                                                                            <option value="monthtodate" >Inicio Mês até Hoje</option>
                                                                            <option value="yeartodate" >Inicio Ano até Hoje</option>
                                                                        </select>
                                                                    </div>
                                                                    
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-sm-6 col-xl-2" v-show="isVisibleManutencao">
                                                            <div class="card">
                                                                <div class="card-body">
                                                                    <div class="row">
                                                                        <div class="col mt-0">
                                                                            <h5 class="card-title">Manutenção Preventiva</h5>
                                                                        </div>
                                                                    </div>
                                                                    <h1 class="mt-1 mb-3">{{ totalTaskCount }}</h1>
                                                                    <div class="mb-0">
                                                                        <select name="date_range" @change="getTaskCount()" v-model="selectedTaskStatus" class="form-control">
                                                                            <option selected value="total" >Total</option>
                                                                            <option value="today" >Hoje</option>
                                                                            <option value="30" >30 dias</option>
                                                                            <option value="60" >60 dias</option>
                                                                            <option value="360" >360 dias</option>
                                                                            <option value="monthtodate" >Inicio Mês até Hoje</option>
                                                                            <option value="yeartodate" >Inicio Ano até Hoje</option>
                                                                        </select>
                                                                    </div>
                                                                    
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-sm-6 col-xl-2" v-show="isVisibleOperacao">
                                                            <div class="card">
                                                                <div class="card-body">
                                                                    <div class="row">
                                                                        <div class="col mt-0">
                                                                            <h5 class="card-title">Combustivel</h5>
                                                                        </div>
                                                                    </div>
                                                                    <h1 class="mt-1 mb-3">{{ totalFuelCount }} L</h1>
                                                                    <div class="mb-0">
                                                                        <select name="date_range" @change="getFuelCount()" v-model="selectedFuelStatus" class="form-control">
                                                                            <option selected value="total" >Total</option>
                                                                            <option value="today" >Hoje</option>
                                                                            <option value="30" >30 dias</option>
                                                                            <option value="60" >60 dias</option>
                                                                            <option value="360" >360 dias</option>
                                                                            <option value="monthtodate" >Inicio Mês até Hoje</option>
                                                                            <option value="yeartodate" >Inicio Ano até Hoje</option>
                                                                        </select>
                                                                    </div>
                                                                    
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-sm-6 col-xl-3" v-show="isVisibleOperacao">
                                                            <div class="card">
                                                                <div class="card-body">
                                                                    <div class="row">
                                                                        <div class="col mt-0">
                                                                            <h5 class="card-title">Horas/Distancia Operação</h5>
                                                                        </div>
                                                                    </div>
                                                                    <div class="row">
                                                                        <div class="col">
                                                                            <small>Hodômetro</small>
                                                                            <h1 class="mt-1 mb-3">{{ totalDistanceCount }}</h1>
                                                                            
                                                                        </div>
                                                                        <div class="col">
                                                                            <small>Horímetro</small>
                                                                            <h1 class="mt-1 mb-3">{{ totalHourCount }}</h1>
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    <div class="mb-0">
                                                                        <select name="date_range" @change="getHourDistanceCount()" v-model="selectedHourDistanceStatus" class="form-control">
                                                                            <option selected value="total" >Total</option>
                                                                            <option value="today" >Hoje</option>
                                                                            <option value="30" >30 dias</option>
                                                                            <option value="60" >60 dias</option>
                                                                            <option value="360" >360 dias</option>
                                                                            <option value="monthtodate" >Inicio Mês até Hoje</option>
                                                                            <option value="yeartodate" >Inicio Ano até Hoje</option>
                                                                        </select>
                                                                    </div>
                                                                                                            
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    

                                                    <div class="row" v-show="isVisibleManutencao">

                                                        <div class="col-12 col-lg-6">
                                                            <div class="card">
                                                                <div class="card-header">
                                                                    <h5 class="card-title">Disponibilidade por mês % durante corrente ano.</h5>
                                                                    <!-- <h6 class="card-subtitle text-muted">Disponibilidade por mês durante corrente ano.</h6> -->
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
                                                                    <h5 class="card-title">Manutenção Corretiva e Preventiva por mês durante corrente ano.</h5>
                                                                    <!-- <h6 class="card-subtitle text-muted">MCSCR e Atividades Planeadas por mês durante corrente ano.</h6> -->
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

                                                    <div class="row" v-show="isVisibleManutencao">
                                                        <div class="col-6 col-lg-6">
                                                            <div class="card">
                                                                <div class="card-header">
                                                                    <h5 class="card-title">Manutenção Preventiva Programado x Realizado por mês durante corrente ano.</h5>
                                                                    <!-- <h6 class="card-subtitle text-muted">Atividades Planeadas Programado x Realizado  por mês durante corrente ano.</h6> -->
                                                                </div>
                                                                <div class="card-body">
                                                                    <div class="chart">
                                                                        <Bar
                                                                            id="my-chart-id"
                                                                            :options="chartOptions7"
                                                                            :data="chartData7"
                                                                            />
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-6 col-lg-6">
                                                                <div class="card">
                                                                    <div class="card-header">
                                                                        <h5 class="card-title">Tempo de Duração dos Estados do MCSCR (em minutos)</h5>
                                                                        <h6 class="card-subtitle text-muted">Tempo Médio de Abertura até Conclusão : {{ opened_at_to_closed_at }} Minutos</h6>
                                                                    </div>
                                                                    <div class="card-body">
                                                                        <div class="chart">
                                                                            <Pie
                                                                                id="my-chart-id-pie"
                                                                                :options="chartOptions8"
                                                                                :data="chartData8"
                                                                                />
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                    </div>

                                                    <div class="row" v-show="isVisibleManutencao">

                                                        <div class="col-12 col-lg-6">
                                                            <div class="card">
                                                                <div class="card-header">
                                                                    <h5 class="card-title">Custo Mão de Obra por mês para Manutenção Corretiva e Preventiva</h5>
                                                                    <!-- <h6 class="card-subtitle text-muted">Custo Mão de Obra por mês durante corrente ano.</h6> -->
                                                                </div>
                                                                <div class="card-body">
                                                                    <div class="chart">
                                                                        <Bar
                                                                            id="my-chart-id"
                                                                            :options="chartOptions4"
                                                                            :data="chartData4"
                                                                            />
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-12 col-lg-6">
                                                            <div class="card">
                                                                <div class="card-header">
                                                                    <h5 class="card-title">Custo de Material para Manutenção Corretiva e Preventiva por mês durante corrente ano.</h5>
                                                                    <!-- <h6 class="card-subtitle text-muted">Custo de Material para MCSCR e Atividades Planeadas por mês durante corrente ano.</h6> -->
                                                                </div>
                                                                <div class="card-body">
                                                                    <div class="chart">
                                                                        <Bar
                                                                            id="my-chart-id"
                                                                            :options="chartOptions3"
                                                                            :data="chartData3"
                                                                            />
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row" v-show="isVisibleOperacao">
                                                    <div class="col-12 col-lg-6">
                                                        <div class="card">
                                                            <div class="card-header">
                                                                <h5 class="card-title">Consumo de Combustivel da Frota por mês durante corrente ano.</h5>
                                                                <!-- <h6 class="card-subtitle text-muted">Consumo de Combustivel por mês durante corrente ano.</h6> -->
                                                            </div>
                                                            <div class="card-body">
                                                                <div class="chart">
                                                                    <Bar
                                                                        id="my-chart-id"
                                                                        :options="chartOptions5"
                                                                        :data="chartData5"
                                                                        />
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-12 col-lg-6">
                                                        <div class="card">
                                                            <div class="card-header">
                                                                <h5 class="card-title">Consumo de Combustivel da Frota por dia durante corrente mês do ano.</h5>
                                                                <!-- <h6 class="card-subtitle text-muted">Consumo de Combustivel por dia durante corrente mês do ano.</h6> -->
                                                            </div>
                                                            <div class="card-body">
                                                                <div class="chart">
                                                                    <Bar
                                                                        id="my-chart-id"
                                                                        :options="chartOptions6"
                                                                        :data="chartData6"
                                                                        />
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                        </div>
                                                        <!-- 

                                                        <div class="row">
                                                            <div class="col-12 col-lg-6">
                                                                <div class="card">
                                                                    <div class="card-header">
                                                                        <h5 class="card-title">Tempo de Duração dos Estados do MCSCR (em minutos)</h5>
                                                                       
                                                                    </div>
                                                                    <div class="card-body">
                                                                        <div class="chart">
                                                                            <Pie
                                                                                id="my-chart-id-pie"
                                                                                :options="chartOptions8"
                                                                                :data="chartData8"
                                                                                />
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div> -->

                                                
                                                    <div class="table-responsive" v-show="isVisibleEquipamentos">
                                                        <table class="table table-striped">
                                                            <thead>
                                                                <tr>
                                                                    <th>#</th>
                                                                    <th>Nome</th>
                                                                    <th>Ref</th>
                                                                    <th>Marca</th>
                                                                    <th>Modelo</th>
                                                                    <th>Área</th>
                                                                    <th>Destino</th>
                                                                    <th>Estado Último MCSCR(Corretiva)</th>
                                                                    <th>Nº MCSCR Mês(Corretiva)</th>
                                                                    <th>Nº Preventivas Mês</th>
                                                                    <th>Estado</th>
                                                                    <th>Operação</th>
                                                                    <th>Ações</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody v-if="equipments.data.length > 0">
                                                                <tr  v-for="(actualData,index) in equipments.data" :key="actualData.id">
                                                                    <td>#{{ index + 1 }}</td>
                                                                    <td>{{ actualData.name}}</td>
                                                                    <td>{{ actualData.ref}}</td>
                                                                    <td>{{ actualData.make}}</td>
                                                                    <td>{{ actualData.model}}</td>
                                                                    <td>{{ actualData.area.name}}</td>
                                                                    <td>{{ actualData.destination.name}}</td>

                                                                    <!-- start mcscr condition existence -->
                                                                    <td >
                                                                        <span v-if="actualData.lastmcscr !=null">
                                                                            


                                                                            <span v-if="actualData.lastmcscr.mcscr_status_id == 1" class="badge bg-success">
                                                                                {{ actualData.lastmcscr.mcscr_status.name}}
                                                                            </span>
                                                                            <span v-if="actualData.lastmcscr.mcscr_status_id == 2" class="badge bg-warning">
                                                                                {{ actualData.lastmcscr.mcscr_status.name}}
                                                                            </span>
                                                                            <span v-if="actualData.lastmcscr.mcscr_status_id == 3" class="badge bg-danger">
                                                                                {{ actualData.lastmcscr.mcscr_status.name}}
                                                                            </span>
                                                                            <span v-if="actualData.lastmcscr.mcscr_status_id == 4" class="badge bg-info">
                                                                                {{ actualData.lastmcscr.mcscr_status.name}}
                                                                            </span>
                                                                            <span v-if="actualData.lastmcscr.mcscr_status_id == 5" class="badge bg-primary">
                                                                                {{ actualData.lastmcscr.mcscr_status.name}}
                                                                            </span>
                                                                            (Aberto:{{ moment(actualData.lastmcscr.opened_at).format('DD-MM-YYYY H:mm') }})
                                                                            <p>Motivo:{{actualData.lastmcscr.reason}}</p>
                                                                        </span>
                                                                        <span v-else>
                                                                            -----
                                                                        </span>
                                                                       
                                                                    </td>
                                                                    
                                                                    <!-- end mcscr condition exists -->

                                                                    <td>{{ actualData.mcscrmonth.length }}</td>
                                                                    <td>{{ actualData.taskmcscrmonth.length }}</td>

                                                                    <td>
                                                                        <span class="badge bg-success" v-if="actualData.equipment_status.id == 1">
                                                                            {{ actualData.equipment_status.name}}
                                                                        </span> 
                                                                        <span class="badge bg-danger" v-if="actualData.equipment_status.id == 2">
                                                                            {{ actualData.equipment_status.name}}
                                                                        </span>
                                                                        <span class="badge bg-danger" v-if="actualData.equipment_status.id == 3">
                                                                            {{ actualData.equipment_status.name}}
                                                                        </span>
                                                                    </td>
                                                                    <td>
                                                                        <span class="badge bg-success" v-if="actualData.equipment_status.id == 1">
                                                                            {{ actualData.equipment_status.mobilized}}
                                                                        </span> 
                                                                        <span class="badge bg-success" v-if="actualData.equipment_status.id == 2">
                                                                            {{ actualData.equipment_status.mobilized}}
                                                                        </span>
                                                                        <span class="badge bg-danger" v-if="actualData.equipment_status.id == 3">
                                                                            {{ actualData.equipment_status.mobilized}}
                                                                        </span>
                                                                    </td>
                                                                    <td>
                                                                        <router-link :to="'/admin/equipments/'+actualData.id+'/edit'"><vue-feather type="edit-2"></vue-feather></router-link>
                                                                        <router-link :to="'/admin/equipments/'+actualData.id"><vue-feather type="eye"></vue-feather></router-link> 
                                                                        <a href="#" @click.prevent="confirmDeletion(actualData)"><vue-feather type="trash"></vue-feather></a>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                            <tbody v-else>
                                                                <tr>
                                                                <td colspan="11" align="center">Nenhum resultado encontrado</td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    <Bootstrap4Pagination :data="equipments" @pagination-change-page="getData"/>
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

    <div class="modal" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
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
</template>