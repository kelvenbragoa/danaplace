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
import { usePaperizer } from "paperizer";


ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale,ArcElement)
const loadingprint = ref(false);
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

let { paperize } = usePaperizer("print-me", {
    styles: [
        "https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css",
    ],
    windowTitle: `FICHA TECNICA`,
});

const downloadMcscr = () => {
    loadingprint.value = true;
    paperize();
    loadingprint.value = false;
};

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

        <h1 class="h3 mb-3">Ficha Técnica do Equipamento/Ativo</h1>
        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Equipamentos: {{ retrievedData.name }}</h5>
                                        <a @click="$router.go(-1)" class="btn btn-pill btn-primary mt-3"><vue-feather type="arrow-left"></vue-feather>Voltar</a> 
								    </div>
                                    
                                    <div class="card-body">
                                        <div id="print-me">
                                            <div class="row m-4">
                                                <div class="col-xl-12 col-xxl-12 d-flex">
                                                    <div class="w-100">
                                                        <div class="row">
                                                            <!-- <div class="col">
                                                                            <div class="col-2">
                                                                                <img src="/files/img/sys/companylogo.png" class="img-fluid" alt="M+D" width="450" height="450">
                                                                            </div>
                                                                        </div> -->
                                                            <br />
                                                            <div
                                                                class="col text-left"
                                                                style="text-align: left"
                                                            >
                                                                <img
                                                                    src="/files/img/sys/companylogo.png"
                                                                    class="img-fluid"
                                                                    alt="image"
                                                                    width="150px"
                                                                    height="150px"
                                                                    style="text-align: left"
                                                                />
                                                            </div>
                                                            <div class="col">
                                                                <br />
                                                            </div>
                                                            <div
                                                                class="col text-right"
                                                                style="text-align: right"
                                                            >
                                                                <!-- <img
                                                                    src="/files/img/sys/volvopenta1.png"
                                                                    class="img-fluid mt-2"
                                                                    alt="image"
                                                                    width="250px"
                                                                    height="250px"
                                                                    style="text-align: right"
                                                                /> -->
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col">
                                                                <!-- <div class="text-muted">Área de Manutenção</div> -->
                                                                <!-- <strong> {{ retrievedData.area.name ?? 'N/A' }} </strong> -->
                                                                <p style="font-size:10px">
                        Dana Place
                        <br />
                        Cimento a Ponta de Ouro
                        <br />
                        Matutuine, Moçambique
                        <br />
                        Tel: +258 87 914 1774
                        <br />
                        Email: info@ieareiabranca.com
                        <br />
                        www.areiabranca.com
                    </p>
                                                            </div>
                                                            <div class="col">
                                                                <br />
                                                            </div>
                                                        </div>
                                                        <table class="table table-bordered">
                                                            <thead>
                                                                <tr>
                                                                    <th class="bg-secondary" style="font-size: 10px; text-align: center;" colspan="2">
                                                                        FICHA TÉCNICA
                                                                    </th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td style="font-size: 10px;">
                                                                        <img :src="profile_picture" height="150" width="150" alt="">
                                                                    </td>
                                                                    <td style="font-size: 10px;">
                                                                        <p><strong>Marca</strong>: {{ retrievedData.make }}</p>
                                                                        <p><strong>Ref</strong>: {{ retrievedData.ref }}</p>
                                                                        <p><strong>Chassis</strong>: {{ retrievedData.chassis }}</p>
                                                                        <p><strong>Serial</strong>: {{ retrievedData.serial }}</p>
                                                                        <p><strong>Capacidade</strong>: {{ retrievedData.load_max }}</p>
                                                                        <p><strong>Ano de Fabrico</strong>: {{ retrievedData.year }}</p>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                        <table class="table table-bordered">
                                                            <thead>
                                                                <tr>
                                                                    <th class="bg-secondary" style="font-size: 10px; text-align: center;" colspan="6">
                                                                        INFORMAÇÕES
                                                                    </th>
                                                                </tr>
                                                                <tr>
                                                                    <th class="bg-secondary" style="font-size: 10px;" colspan="1">
                                                                        ANO DE COMPRA
                                                                    </th>
                                                                    <th class="bg-secondary" style="font-size: 10px;" colspan="1">
                                                                        ÁREA
                                                                    </th>
                                                                    <th class="bg-secondary" style="font-size: 10px;" colspan="1">
                                                                       Clientes
                                                                    </th>
                                                                    <th class="bg-secondary" style="font-size: 10px;" colspan="1">
                                                                        FROTA
                                                                    </th>
                                                                    <th class="bg-secondary" style="font-size: 10px;" colspan="1">
                                                                        FABRICANTE
                                                                    </th>
                                                                    <th class="bg-secondary" style="font-size: 10px;" colspan="1">
                                                                        ODOMETRO/HORIMETRO
                                                                    </th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td style="font-size: 10px;">
                                                                        {{ retrievedData.buy_year }}
                                                                    </td>

                                                                    <td style="font-size: 10px;">
                                                                        {{ retrievedData.area.name }}
                                                                    </td>
                                                                    <td style="font-size: 10px;">
                                                                        {{ retrievedData.destination.name }}
                                                                    </td>
                                                                    <td style="font-size: 10px;">
                                                                        {{ retrievedData.type_equipment.name }}
                                                                    </td>
                                                                    <td style="font-size: 10px;">
                                                                        {{ retrievedData.supplier.name }}
                                                                    </td>
                                                                    <td style="font-size: 10px;">
                                                                        {{retrievedData.lastdistance == null ? '0' : retrievedData.lastdistance.value }}
                                                                    </td>
                                                                   
                                                                </tr>
                                                            </tbody>
                                                        </table>

                                                        

                                                    </div>
                                                </div>
                                            </div>
                                        </div>


                                        <div class="text-center">
                                            <button
                                                @click="downloadMcscr"
                                                class="btn btn-primary"
                                                :disabled="loadingprint"
                                            >
                                                <div
                                                    v-if="loadingprint"
                                                    class="spinner-border spinner-border-sm"
                                                    role="status"
                                                ></div>
                                                <span v-else>Print</span>
                                            </button>
                                            <div
                                                v-if="loadingprint"
                                                class="d-flex justify-content-center"
                                            >
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