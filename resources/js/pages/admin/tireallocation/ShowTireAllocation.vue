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
import QrcodeVue from 'qrcode.vue'
import { Bar,Pie } from 'vue-chartjs'
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale,ArcElement } from 'chart.js'
import VueBarcode from 'vue-barcode';
import { BarcodeGeneratorPlugin } from "@syncfusion/ej2-vue-barcode-generator";
import UploadMcscr from '../mcscr/UploadMcscr.vue';

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale,ArcElement)

let criticals =ref([]);
let mcscrData = ref([]);
let equipmentstatuses =ref([]);
let retrievedData =ref([]);
let loadingSubmit =ref([true]);
let loadingDiv =ref([true]);
const router = useRouter();
let self = this;
let qrcodevalue = ref('/equipment/0');
const loadingButtonDelete = ref(false);
let components =ref([]);
const searchQuery = ref(null);
let currentvalue = ref([]);
let dataIdBeingDeleted = ref(null);
let loadingReconciliation = ref([false]);
const profile_picture = ref();
const uploads = ref();


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

const opened_at_to_closed_at = ref(0);


const schema = yup.object({
    
    name: yup.string().required(),
    ref: yup.string().required(),
    model: yup.string().required(),
    make: yup.string().required(),
    serial: yup.string().required(),
    criticaly_id: yup.string().required(),
    equipment_id: yup.string().required(),
    equipment_status_id: yup.string().required(),
    percentage_weigth: yup.string().required(),
    
   
  });

const createRecordFunction = (values, actions) => {

 
currentvalue.value = {values};

loadingSubmit.value = true;

const arr = Array.from(values)

axios.post('/equipmentcomponent',values).then((response)=>{

// categories.value.unshift(response.data);
// $('#createCategory').modal('hide');
retrievedData.value = response.data.equipment;
components.value = response.data.components;
equipmentstatuses.value = response.data.equipmentstatuses;
criticals.value = response.data.criticals;
qrcodevalue.value = "/equipment/"+retrievedData.value.id;

actions.resetField('name');
actions.resetField('ref');
actions.resetField('model');
actions.resetField('make');
actions.resetField('serial');
actions.resetField('criticaly_id');
actions.resetField('equipment_status_id');
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







const getData = (page=1) => {
  axios.get(`/equipments/+${router.currentRoute.value.params.id}?page=${page}`,
        {
        params:{
          query: searchQuery.value
        }
        }
        )
       .then((response)=>{
        loadingDiv.value=false;
        retrievedData.value = response.data.equipment;
        profile_picture.value = response.data.profile_picture;
        uploads.value = response.data.uploads
        components.value = response.data.components;
        equipmentstatuses.value = response.data.equipmentstatuses;
        criticals.value = response.data.criticals;
        qrcodevalue.value = "/equipment/"+retrievedData.value.id;
        totalMcscrCount.value = response.data.totalMcscrCount
        totalTaskCount.value = response.data.totalTaskCount;
        totalFuelCount.value = response.data.totalFuelCount;
        totalHourCount.value = response.data.totalHourCount;
        totalDistanceCount.value = response.data.totalDistanceCount;
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
        // chartData7.value.datasets[0].data = response.data.dataChartHourMonth;
        chartData7.value.datasets[0].data = response.data.dataChartDistanceMonth;
        chartData8.value.datasets[0].data = response.data.dataChartDistanceDay;

        chartData9.value.datasets[1].data = response.data.dataChartTaskMcscrDone;
        chartData9.value.datasets[0].data = response.data.dataChartTaskMcscrScheduled;

        chartData10.value.datasets[0].data = response.data.pieChartDuration;

        opened_at_to_closed_at.value = response.data.opened_at_to_closed_at;

        mcscrData.value = response.data.mcscrData;
        
       }).catch(()=>{
        loadingDiv.value=false;
       })
}

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

axios.delete(`/equipmentcomponent/${dataIdBeingDeleted}`)
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

const confirmReconciliation = ()=>{

   
loadingReconciliation.value=true;
axios.get(`/equipments/reconciliation/${retrievedData.value.id}`)
   .then((response)=>{

    loadingReconciliation.value=false;
    retrievedData.value = response.data.equipment;
    components.value = response.data.components;
    equipmentstatuses.value = response.data.equipmentstatuses;
    criticals.value = response.data.criticals;
    toastr.success('Reconciliação feita com sucesso.');
    
   }).catch(()=>{
    loadingReconciliation.value=false;
    toastr.error('Erro ao reconciliar');
   })

}

const getMcscrCount = ()=>{

axios.get(`/equipments/+${router.currentRoute.value.params.id}/mcscrcount`,{
  params: {
    date_range: selectedMcscrStatus.value
  }
})
.then((response)=>{
  totalMcscrCount.value = response.data.totalMcscrCount;
})
}

const getTaskCount = ()=>{

axios.get(`/equipments/+${router.currentRoute.value.params.id}/taskcount`,{
params: {
date_range: selectedTaskStatus.value
}
})
.then((response)=>{
totalTaskCount.value = response.data.totalTaskCount;
})
}

const getFuelCount = ()=>{

axios.get(`/equipments/+${router.currentRoute.value.params.id}/fuelcount`,{
params: {
date_range: selectedFuelStatus.value
}
})
.then((response)=>{
totalFuelCount.value = response.data.totalFuelCount;
})
}


const getHourDistanceCount = ()=>{

axios.get(`/equipments/+${router.currentRoute.value.params.id}/hourdistancecount`,{
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
                            label: 'Operação por mês',
                            backgroundColor: '#50B3C7',
                            data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]
                          },
                        ]
        })
  const  chartOptions7 = {
          responsive: true, 
        }

        const chartData8 = ref({
          labels: [  '1','2','3','4','5','6','7','8','9','10','11','12','13','14','15','16','17','18','19','20','21','22','23','24','25','26','27','28','29','30','31'
                ],
                datasets: [
                          {
                            label: 'Horas/Distancia por dia',
                            backgroundColor: '#50B3C7',
                            data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0,0, 0, 0, 0, 0, 0, 0, 0, 0, 0,0, 0, 0, 0, 0, 0, 0, 0, 0, 0,0]
                          },
                        ]
        })
        
  const  chartOptions8 = {
          responsive: true, }

          const chartData9 = ref({
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
                          },
                        ]
        })
  const  chartOptions9 = {
          responsive: true, 
        }

        const chartData10 = ref({
            labels: ['Pendente - Diagnóstico','Diagnóstico - Execução', 'Execução - Aprovação', 'Aprovação - Conclusão'],
                datasets: [
                          
                          {
                            
                            backgroundColor: ['#d64a6e','#4aa0b5', '#4a7ed6', '#f6c344'],
                            data: [0, 0, 0, 0]
                          },
                        ]
        })
  const  chartOptions10 = {
          responsive: true, 
        }

onMounted(()=>{
  
  getData();
})
</script>

<template>
    <div v-if="!loadingDiv">

        <h1 class="h3 mb-3">Equipamentos/Ativos</h1>
        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Equipamentos: {{ retrievedData.name }}</h5>

                                        <a @click="$router.go(-1)" class="btn btn-pill btn-primary mt-3"><vue-feather type="arrow-left"></vue-feather>Voltar</a> 
                                       
								    </div>
                                    
                                    <div class="card-body">
                                          
                                        <div class="row">
                                            <div class="col-xl-12 col-xxl-12 d-flex">
                                                <div class="w-100">
                                                    <div class="card" style="border-radius: 100px;">
                                                        <div class="card-header" style="background-color: #848484;border-radius: 30px 30px 0px 0px;">
                                                            <h1 class="text-white text-center"><strong>{{ retrievedData.name }}</strong></h1>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="row">
                                                                <div class="col text-center">
                                                                    <img :src="profile_picture" height="150" width="150" alt="">
                                                                </div>
                                                                <div class="col">
                                                                    <h3>Equipment Information</h3>
                                                                    <p><strong>ID</strong>: {{ retrievedData.id }}</p>
                                                                    <p><strong>REF</strong>: {{ retrievedData.ref }}</p>
                                                                    <p><strong>YEAR</strong>: {{ retrievedData.year }}</p>
                                                                    <p><strong>MAKE</strong>: {{ retrievedData.make }}</p>
                                                                    <p><strong>MODEL</strong>: {{ retrievedData.model }}</p>
                                                                    <p><strong>SERIAL</strong>: {{ retrievedData.serial }}</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="card-footer" style="background-color: #848484; border-radius:0px 0px 30px 30px ;">
                                                            <!-- <qrcode-vue :value="qrcodevalue" :size="120" level="H" /> -->
                                                            <h1 class="text-white text-center"><strong>{{ retrievedData.name }}</strong></h1>
                                                        </div>
                                                    </div>
                                                    <div class="card-header">
                                                        <div class="row">
                                                                <div class="col">
                                                                    <a class="btn btn-pill btn-primary mt-3" data-toggle="collapse" href="#collapseDetalhes" role="button" aria-expanded="false" aria-controls="collapseDetalhes">
                                                                        <vue-feather type="eye"></vue-feather>Ver detalhes do Equipamento
                                                                    </a>
                                                                </div>
                                                                
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <p><strong>Nome</strong>: {{ retrievedData.name }}</p>
                                                    </div>
                                                    <div class="collapse mt-3" id="collapseDetalhes">
                                                        <div class="row">
                                                            <div class="mb-3 col-md-6">
                                                                
                                                                <p><strong>Referência</strong>: {{ retrievedData.ref }}</p>
                                                                <p><strong>Marca</strong>: {{ retrievedData.make }}</p>
                                                                <p><strong>Modelo</strong>: {{ retrievedData.model }}</p>
                                                                <p><strong>Serial</strong>: {{ retrievedData.serial }}</p>
                                                                <p><strong>Chassis</strong>: {{ retrievedData.chassis }}</p>
                                                                <p><strong>Ano de Fabrico</strong>: {{ retrievedData.year }}</p>
                                                                <p><strong>Ano de Compra</strong>: {{ retrievedData.buy_year }}</p>
                                                                <p><strong>Tipo de Equipamento</strong>: {{ retrievedData.type_equipment.name }}</p>
                                                                <p><strong>Clientes</strong>: {{ retrievedData.destination.name }}</p>
                                                                <p><strong>Área</strong>: {{ retrievedData.area.name }}</p>
                                                                <p><strong>Fabricante</strong>: {{ retrievedData.supplier.name }}</p>
                                                            </div>
                                                            <div class="mb-3 col-md-6">
                                                                <p><strong>Aquisição</strong>: {{ retrievedData.acquisition.name }}</p>
                                                                <p><strong>Valor de Compra</strong>: {{ retrievedData.amount }} {{ retrievedData.coin.name }}</p>
                                                                <p><strong>Capacidade Máxima</strong>: {{ retrievedData.load_max }} {{ retrievedData.load_unity.name }}</p>
                                                                <p><strong>Comissionado</strong>: <span v-if="retrievedData.is_commissioned == 0">Não</span> <span v-else>Sim</span> </p>
                                                                <p><strong>Criticidade</strong>: {{ retrievedData.criticaly.name }}</p>
                                                                <p><strong>Centro de Custo</strong>: {{ retrievedData.center_cost == null ? 'N/A' :  retrievedData.center_cost.name}}</p>
                                                                <p><strong>Medidor de distância</strong>: {{ retrievedData.distance_control.name }}</p>
                                                                <p><strong>Distancia/Horas de Operação Total</strong>: {{retrievedData.lastdistance == null ? '0' : retrievedData.lastdistance.value }}</p>
                                                                <p><strong>Combustivel</strong>: {{ retrievedData.fuel }}</p>
                                                                <p><strong>Conta de Centro de Custo</strong>: {{ retrievedData.center_cost_account==null ? 'N/A' : retrievedData.center_cost_account.name }}</p>
                                                                <p><strong>Estado do Equipamento</strong>: 
                                                                        <span class="badge bg-success" v-if="retrievedData.equipment_status.id == 1">
                                                                            {{ retrievedData.equipment_status.name}}
                                                                        </span> 
                                                                        <span class="badge bg-danger" v-if="retrievedData.equipment_status.id == 2">
                                                                            {{ retrievedData.equipment_status.name}}
                                                                        </span>
                                                                        <span class="badge bg-danger" v-if="retrievedData.equipment_status.id == 3">
                                                                            {{ retrievedData.equipment_status.name}}
                                                                        </span>
                                                                </p>
                                                                <p><strong>Operacional</strong>: 
                                                                        <span class="badge bg-success" v-if="retrievedData.equipment_status.id == 1">
                                                                            {{ retrievedData.equipment_status.mobilized}}
                                                                        </span> 
                                                                        <span class="badge bg-success" v-if="retrievedData.equipment_status.id == 2">
                                                                            {{ retrievedData.equipment_status.mobilized}}
                                                                        </span>
                                                                        <span class="badge bg-danger" v-if="retrievedData.equipment_status.id == 3">
                                                                            {{ retrievedData.equipment_status.mobilized}}
                                                                        </span>
                                                                </p>
                                                            </div>
                                                        </div>
                                                        <p><strong>GPS ID</strong>: {{ retrievedData.gps_tracking_id }}</p>
                                                    
                                                        <qrcode-vue :value="qrcodevalue" :size="120" level="H" />

                                                        <div class="row">
                                                <!-- imagem -->
                                                <div class="col-sm-6 col-xl-3" v-for="upload in uploads" :key="upload.id">
                                                    <div class="card">
                                                       
                                                        <div class="card-body">
                                                            
                                                            <div class="row">
                                                                <div class="col mt-0">
                                                                    <img :src='upload.file' alt="" class="w-100">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- imagem -->
                                            </div>

                                               

                                                    
                                                    
                                                   
                                                    

                                                    <hr>

                                                    <div class="card-header">
                                                        <h5 class="card-title">Componentes do Equipamento: {{ components.total }} registros encontrados.</h5>
                                                        <div class="row">
                                                            <div class="col">
                                                                <a class="btn btn-pill btn-primary mt-3" @click.prevent="confirmReconciliation()">
                                                                    <vue-feather type="refresh-ccw" ></vue-feather>Reconciliar Componentes/SubComponentes
                                                                </a>

                                                                <div v-if="loadingReconciliation == true">
                                                                    <div class="d-flex justify-content-center">
                                                                        <div class="spinner-border" role="status">
                                                                            <span class="sr-only"></span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <form class="d-none d-sm-inline-block mt-3">
                                                        <div class="input-group input-group-navbar">
                                                            <input type="text" class="form-control" v-model="searchQuery" placeholder="Procurar componente..." aria-label="Search">
                                                        </div>
                                                    </form>

                                                    <div class="table-responsive">
                                                        <table class="table table-striped">
                                                            <thead>
                                                                <tr>
                                                                    <th>#</th>
                                                                    <th>Nome</th>
                                                                    <th>Ref</th>
                                                                    <th>Serial</th>
                                                                    <th>Marca</th>
                                                                    <th>Modelo</th>
                                                                    <th>Criticidade</th>
                                                                    <th>Percentagem</th>
                                                                    <th>SubComponentes</th>
                                                                    <th>Estado</th>
                                                                    <th>Ações</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody v-if="components.data.length > 0">
                                                                <tr  v-for="(actualData,index) in components.data" :key="actualData.id">
                                                                    <td>#{{ index + 1 }}</td>
                                                                    <td>{{ actualData.name}}</td>
                                                                    <td>{{ actualData.ref}}</td>
                                                                    <td>{{ actualData.serial}}</td>
                                                                    <td>{{ actualData.make}}</td>
                                                                    <td>{{ actualData.model}}</td>
                                                                    <td>{{ actualData.criticality.name}}</td>
                                                                    <td>{{ actualData.percentage_weigth}}%</td>
                                                                    <td>{{ actualData.subcomponents.length}}</td>
                                                                    <td>
                                                                        <span class="badge bg-success" v-if="actualData.equipmentstatus.id == 1">
                                                                            {{ actualData.equipmentstatus.name}}
                                                                        </span> 
                                                                        <span class="badge bg-danger" v-if="actualData.equipmentstatus.id == 2">
                                                                            {{ actualData.equipmentstatus.name}}
                                                                        </span>
                                                                        <span class="badge bg-danger" v-if="actualData.equipmentstatus.id == 3">
                                                                            {{ actualData.equipmentstatus.name}}
                                                                        </span>
                                                                        
                                                                    </td>
                                                                    
                                                                    <td>
                                                                        <router-link :to="'/admin/equipments/component/'+actualData.id+'/edit'"><vue-feather type="edit-2"></vue-feather></router-link>
                                                                        <router-link :to="'/admin/equipments/component/'+actualData.id"><vue-feather type="eye"></vue-feather></router-link> 
                                                                        <!-- <a href="#" @click.prevent="confirmDeletion(actualData)"><vue-feather type="trash"></vue-feather></a> -->
                                                                        
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
                                                    <Bootstrap4Pagination :data="components" @pagination-change-page="getData"/>
                                                    </div>
                                                    
                                                 

                                            
                                                   
                                                    <hr>
                                                    <div class="row">
                                                        <div class="col-sm-6 col-xl-2">
                                                            <div class="card">
                                                                <div class="card-body">
                                                                    <div class="row">
                                                                        <div class="col mt-0">
                                                                            <h5 class="card-title">Manutenção Corretiva</h5>
                                                                            <!-- MCSCR -->
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

                                                        <div class="col-sm-6 col-xl-2">
                                                            <div class="card">
                                                                <div class="card-body">
                                                                    <div class="row">
                                                                        <div class="col mt-0">
                                                                            <h5 class="card-title">Manutenção preventiva</h5>
                                                                            <!-- Atividades -->
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
                                                

                                                        <div class="col-sm-6 col-xl-2">
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

                                                        <div class="col-sm-6 col-xl-3">
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

                                                    <hr>
                                                    <div class="row">

                                                        <div class="col-12 col-lg-6">
                                                            <div class="card">
                                                                <div class="card-header">
                                                                    <!-- <h5 class="card-title">Disponibilidade por mês %</h5> -->
                                                                    <h5 class="card-title">Disponibilidade por mês durante corrente ano.</h5>
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
                                                                    <!-- <h5 class="card-title">MCSCR e Atividades Planeadas</h5> -->
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
                                                    

                                                    <hr>

                                                    <div class="row">
                                                        <div class="col-12 col-lg-6">
                                                            <div class="card">
                                                                <div class="card-header">
                                                                    <h5 class="card-title">Manutenções Preventivas Programado x Realizado</h5>
                                                                    <!-- <h6 class="card-subtitle text-muted">Atividades Planeadas Programado x Realizado por mês durante corrente ano.</h6> -->
                                                                </div>
                                                                <div class="card-body">
                                                                    <div class="chart">
                                                                        <Bar
                                                                            id="my-chart-id"
                                                                            :options="chartOptions9"
                                                                            :data="chartData9"
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
                                                                                :options="chartOptions10"
                                                                                :data="chartData10"
                                                                                />
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                    </div>

                                                    <div class="row">

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
                                                                            :options="chartOptions3"
                                                                            :data="chartData3"
                                                                            />
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-12 col-lg-6">
                                                            <div class="card">
                                                                <div class="card-header">
                                                                    <h5 class="card-title">Custo de Material para Manutenção Corretiva e Preventiva</h5>
                                                                    <h6 class="card-subtitle text-muted">Custo de Material para MCSCR e Atividades Planeadas por mês durante corrente ano.</h6>
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

                                                        </div>

                                                        <div class="row">

<div class="col-12 col-lg-6">
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">Consumo de Combustivel do Equipamento</h5>
            <h6 class="card-subtitle text-muted">Consumo de Combustivel por mês durante corrente ano.</h6>
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
            <h5 class="card-title">Consumo de Combustivel do Equipamento</h5>
            <h6 class="card-subtitle text-muted">Consumo de Combustivel por dia durante corrente mês do ano.</h6>
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


<div class="row">
    <div class="col-12 col-lg-6">
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">Horas / Distância Operação ({{ retrievedData.distance_control.name }})</h5>
            <h6 class="card-subtitle text-muted">Horas / Distância Operação por mês durante corrente ano.({{ retrievedData.distance_control.name }})</h6>
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

<div class="col-12 col-lg-6">
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">Horas / Distância Operação ({{ retrievedData.distance_control.name }})</h5>
            <h6 class="card-subtitle text-muted">Horas / Distância Operação por dia durante corrente mês. ({{ retrievedData.distance_control.name }})</h6>
        </div>
        <div class="card-body">
            <div class="chart">
                <Bar
                    id="my-chart-id"
                    :options="chartOptions8"
                    :data="chartData8"
                    />
            </div>
        </div>
    </div>
</div>
</div>

</div>
<hr>

                                                    <div class="row">
                                                        <div class="col-12">
                                                            <div class="card">
                                                                
                                                                
                                                                <div class="card-body">
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
                                                                            <tbody v-if="mcscrData.length > 0">
                                                                                <tr  v-for="(actualDataMcscr,index) in mcscrData" :key="actualDataMcscr.id">
                                                                                    <td>#{{ index + 1 }}</td>
                                                                                    <td>
                                                                                        <span v-if="actualDataMcscr.mcscr_status_id == 1" class="badge bg-success">
                                                                                            {{ actualDataMcscr.mcscr_status.name}}
                                                                                        </span>
                                                                                        <span v-if="actualDataMcscr.mcscr_status_id == 2" class="badge bg-warning">
                                                                                            {{ actualDataMcscr.mcscr_status.name}}
                                                                                        </span>
                                                                                        <span v-if="actualDataMcscr.mcscr_status_id == 3" class="badge bg-danger">
                                                                                            {{ actualDataMcscr.mcscr_status.name}}
                                                                                        </span>
                                                                                        <span v-if="actualDataMcscr.mcscr_status_id == 4" class="badge bg-info">
                                                                                            {{ actualDataMcscr.mcscr_status.name}}
                                                                                        </span>
                                                                                        <span v-if="actualDataMcscr.mcscr_status_id == 5" class="badge bg-primary">
                                                                                            {{ actualDataMcscr.mcscr_status.name}}
                                                                                        </span>
                                                                                    </td>
                                                                                    <td>{{ actualDataMcscr.equipment.name}} / {{ actualDataMcscr.equipment.ref}}</td>
                                                                                    <td>{{ moment(actualDataMcscr.opened_at).format('DD-MM-YYYY H:mm') }} </td>
                                                                                    <td>{{ actualDataMcscr.closed_at==null ? '-----' : moment(actualDataMcscr.closed_at).format('DD-MM-YYYY H:mm')}}</td>
                                                                                    <td class="text-danger">
                                                                                        {{ actualDataMcscr.closed_at == null ? moment().diff(actualDataMcscr.opened_at,'hours')+' Horas ('+moment().diff(actualDataMcscr.opened_at,'minutes')+' Minutos)' :  
                                                                                        
                                                                                        moment(actualDataMcscr.closed_at).diff(actualDataMcscr.opened_at,'hours')+' Horas ('+moment(actualDataMcscr.closed_at).diff(actualDataMcscr.opened_at,'minutes')+' Minutos)'
                                                                                        }}
                                                                                        <!-- {{ moment().diff(actualDataMcscr.opened_at,'hours') }}Horas ({{ moment().diff(actualDataMcscr.opened_at,'minutes') }} Minutos) -->
                                                                                    </td>
                                                                                    <td>{{ actualDataMcscr.reason_id == null ? actualDataMcscr.reason : actualDataMcscr.reason_name.name}}</td>
                                                                                    <td>{{ moment(actualDataMcscr.output_forecast).format('DD-MM-YYYY H:mm')}}</td>
                                                                                    <td>
                                                                                        <!-- <router-link :to="'/admin/mcscr/'+actualDataMcscr.id+'/edit'" v-if="actualDataMcscr.mcscr_status_id != 1"><vue-feather type="edit-2"></vue-feather></router-link> -->
                                                                                        <!-- <router-link :to="'/admin/mcscr/'+actualDataMcscr.id+'/edit'" ><vue-feather type="edit-2"></vue-feather></router-link> -->
                                                                                        <router-link :to="'/admin/mcscr/'+actualDataMcscr.id"><vue-feather type="eye"></vue-feather></router-link> 
                                                                                        <!-- <a href="#" @click.prevent="confirmDeletion(actualDataMcscr)"><vue-feather type="trash"></vue-feather></a> -->
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
                                                                </div>

                                                            </div>
                                                            <!-- <Bootstrap4Pagination :data="mcscrData" @pagination-change-page="getData"/> -->
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