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
let openedbyuser = ref();
let closedbyuser = ref();

let destinationuser = ref();


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
        destinationuser.value = response.data.destinationuser
            openedbyuser.value = response.data.openedby;
            closedbyuser.value = response.data.closedby;

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

    loadingprint.value = true;

        paperize()
    loadingprint.value = false;

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
                        <div class="mb-4">
                            <a @click="$router.go(-1)" class="btn btn-pill btn-primary mt-3"><vue-feather type="arrow-left"></vue-feather>Voltar</a> 
                            
                        </div>
                        <div id="print-me">
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
                                        <th style="font-size: 10px;">
                                            DATE:
                                            {{
                                                moment(
                                                    retrievedData.created_at
                                                ).format("DD-MM-YYYY H:mm")
                                            }}
                                        </th>
                                        <th style="font-size: 10px;">
                                            FLEET:
                                            {{
                                                retrievedData.equipment
                                                    .type_equipment.name ??
                                                "N/A"
                                            }}
                                        </th>
                                        <th style="font-size: 10px;">
                                            REF:
                                            {{
                                                retrievedData.equipment.ref ??
                                                "N/A"
                                            }}
                                        </th>
                                        <th style="font-size: 10px;">
                                            JOB CARD Nº: #{{ retrievedData.id }}
                                        </th>
                                    </tr>
                                </thead>
                                
                            </table>
                            <div class="row">
                                <div class="col-6">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th
                                                    colspan="2"
                                                    class="bg-secondary"
                                                    style="font-size: 10px;"
                                                >
                                                    CUSTOMER INFORMATION
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td style="font-size: 10px;">COMPANY NAME</td>
                                                <td style="font-size: 10px;">
                                                    {{
                                                        retrievedData
                                                            .destination
                                                            .company_name ??
                                                        "N/A"
                                                    }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="font-size: 10px;">NAME OF RESPONSIBLE</td>
                                                <td style="font-size: 10px;">
                                                    {{
                                                        retrievedData
                                                            .destination.name ??
                                                        "N/A"
                                                    }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="font-size: 10px;">ADDRESS</td>
                                                <td style="font-size: 10px;">
                                                    {{
                                                        retrievedData
                                                            .destination
                                                            .company_address ??
                                                        "N/A"
                                                    }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="font-size: 10px;">CITY</td>
                                                <td style="font-size: 10px;">
                                                    {{
                                                        retrievedData
                                                            .destination
                                                            .province.name ??
                                                        "N/A"
                                                    }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="font-size: 10px;">MOBILE</td>
                                                <td style="font-size: 10px;">
                                                    {{
                                                        retrievedData
                                                            .destination
                                                            .company_mobile ??
                                                        "N/A"
                                                    }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="col-6">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th
                                                    colspan="2"
                                                    class="bg-secondary"
                                                    style="font-size: 10px;"
                                                >
                                                    ENGINE INFORMATION
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td style="font-size: 10px;">Nº OF CHASSI</td>
                                                <td style="font-size: 10px;">
                                                    {{
                                                        retrievedData.equipment
                                                            .chassis ?? "N/A"
                                                    }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="font-size: 10px;">MODEL</td>
                                                <td style="font-size: 10px;">
                                                    {{
                                                        retrievedData.equipment
                                                            .model ?? "N/A"
                                                    }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="font-size: 10px;">Nº SERIE</td>
                                                <td style="font-size: 10px;">
                                                    {{
                                                        retrievedData.equipment
                                                            .serial ?? "N/A"
                                                    }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="font-size: 10px;">YEAR</td>
                                                <td style="font-size: 10px;">
                                                    {{
                                                        retrievedData.equipment
                                                            .buy_year ?? "N/A"
                                                    }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="font-size: 10px;">HOUR METER</td>
                                                <td style="font-size: 10px;">
                                                    {{
                                                        retrievedData.distance ??
                                                        "N/A"
                                                    }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                         
                           
                           
                         
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th class="bg-secondary" style="font-size: 10px;">COMMENTS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="font-size: 10px;">
                                            {{
                                                retrievedData.observation ??
                                                "N/A"
                                            }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            

                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th class="bg-secondary" style="font-size: 10px;">OPENED AT:</th>
                                        <th class="bg-secondary" style="font-size: 10px;">
                                            SCHEDULED FOR
                                        </th>
                                        <th class="bg-secondary" style="font-size: 10px;">
                                            EXECUTION AT:
                                        </th>
                                       
                                        <th class="bg-secondary" style="font-size: 10px;">CLOSED AT:</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="font-size: 10px;">
                                            {{
                                                moment(
                                                    retrievedData.created_at
                                                ).format("DD-MM-YYYY H:mm")
                                            }}
                                        </td>
                                        <td style="font-size: 10px;">
                                            {{ retrievedData.schedule_for == null ? 'N/A' : moment(retrievedData.schedule_for).format('DD-MM-YYYY H:mm') }}
                                        </td>
                                        <td style="font-size: 10px;">
                                            {{ retrievedData.opened_at == null ? 'N/A' : moment(retrievedData.opened_at).format('DD-MM-YYYY H:mm') }}
                                        </td>
                                       
                                        <td style="font-size: 10px;">
                                            <strong>{{ retrievedData.closed_at == null ? 'N/A' : moment(retrievedData.closed_at).format('DD-MM-YYYY H:mm') }}</strong>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <table class="table table-bordered">
                                <thead>
                                    
                                    <tr>
                                        <th class="bg-secondary" style="font-size: 10px;">
                                            STATUS
                                        </th>
                                        <th class="bg-secondary" style="font-size: 10px;">
                                            TASK PLAN
                                        </th>
                                        <th class="bg-secondary" style="font-size: 10px;">
                                            ACTIVITY
                                        </th>
                                        <th class="bg-secondary" style="font-size: 10px;">ESTIMATED TIME</th>
                                        <th class="bg-secondary" style="font-size: 10px;">
                                            TIME UNAVAILABLE
                                        </th>
                                        <th class="bg-secondary" style="font-size: 10px;">
                                            STOPPED TIME
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="font-size: 10px;">
                                            <span v-if="retrievedData.task_mcscr_status_id == 1" class="badge bg-warning">
                                                                {{ retrievedData.task_mcscr_status.name}}
                                                            </span>
                                                            <span v-if="retrievedData.task_mcscr_status_id == 2" class="badge bg-success">
                                                                {{ retrievedData.task_mcscr_status.name}}
                                                            </span>
                                                            <span v-if="retrievedData.task_mcscr_status_id == 3" class="badge bg-danger">
                                                                {{ retrievedData.task_mcscr_status.name}}
                                                            </span>
                                                            <span v-if="retrievedData.task_mcscr_status_id == 4" class="badge bg-primary">
                                                                {{ retrievedData.task_mcscr_status.name}}
                                                            </span>
                                        </td>
                                        <td style="font-size: 10px;">
                                            {{ retrievedData.task_plan.name }}
                                        </td>
                                        <td style="font-size: 10px;">
                                            {{ retrievedData.task_plan_task.name }}
                                        </td>
                                        <td style="font-size: 10px;">
                                            {{ retrievedData.task_plan_task.estimated_time_days }} Dias : {{ retrievedData.task_plan_task.estimated_time_hours }} Horas : {{ retrievedData.task_plan_task.estimated_time_minutes }} Minutos
                                        </td>
                                        <td style="font-size: 10px;">
                                            {{ retrievedData.task_plan_task.unavailable_equipment_time_days }} Dias : {{ retrievedData.task_plan_task.unavailable_equipment_time_hours }} Horas : {{ retrievedData.task_plan_task.unavailable_equipment_time_minutes }} Minutos
                                        </td>
                                        <td style="font-size: 10px;">
                                            {{ retrievedData.closed_at == null ? moment().diff(retrievedData.opened_at,'hours')+' Horas ('+moment().diff(retrievedData.opened_at,'minutes')+' Minutos)' :  
                                                               
                                                               moment(retrievedData.closed_at).diff(retrievedData.opened_at,'hours')+' Horas ('+moment(retrievedData.closed_at).diff(retrievedData.opened_at,'minutes')+' Minutos)'
                                                            }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th class="bg-secondary" style="font-size: 10px;" colspan="5">
                                            COSTS INVOLVED
                                        </th>
                                    </tr>
                                    <tr>
                                        <th class="bg-secondary" style="font-size: 10px;">LABOR</th>
                                        <th class="bg-secondary" style="font-size: 10px;">MATERIAL</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="font-size: 10px;">
                                            {{
                                                retrievedData.material_labor ??
                                                "0"
                                            }}
                                            MT
                                        </td>
                                        <td style="font-size: 10px;">
                                            {{
                                                retrievedData.material_cost ??
                                                "0"
                                            }}
                                            MT
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <table
                                class="table table-bordered"
                                v-for="(request, idx) in requeststock"
                                :key="request.id"
                            >
                                <thead>
                                    <tr>
                                        <th class="bg-secondary" style="font-size: 10px;" colspan="5">
                                            MATERIALS REQUIREMENT
                                        </th>
                                    </tr>
                                    <tr>
                                        <th class="bg-secondary" style="font-size: 10px;">#</th>
                                        <th class="bg-secondary" style="font-size: 10px;">REQUEST ID</th>
                                        <th class="bg-secondary" style="font-size: 10px;">MATERIAL</th>
                                        <th class="bg-secondary" style="font-size: 10px;">
                                            QUANTITY REQUIRED
                                        </th>
                                        <th class="bg-secondary" style="font-size: 10px;">
                                            QUANTITY DELIVERED
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="(
                                            material, index
                                        ) in request.requestitens"
                                        :key="material.id"
                                    >
                                        <td style="font-size: 10px;">#{{ index + 1 }}</td>
                                        <td style="font-size: 10px;">{{ request.id }}</td>
                                        <td style="font-size: 10px;">{{ material.product.name }}</td>
                                        <td style="font-size: 10px;">{{ material.quantity }}</td>
                                        <td style="font-size: 10px;">
                                            {{ material.delivered_quantity }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <table
                                class="table table-bordered"
                                v-for="(requesttech, idx) in requesttechnician"
                                :key="requesttech.id"
                            >
                                <thead>
                                    <tr>
                                        <th class="bg-secondary" style="font-size: 10px;" colspan="5">
                                            TECHNICAL REQUIREMENTS
                                        </th>
                                    </tr>
                                    <tr>
                                        <th class="bg-secondary" style="font-size: 10px;">#</th>
                                        <th class="bg-secondary" style="font-size: 10px;">REQUEST ID</th>
                                        <th class="bg-secondary" style="font-size: 10px;">
                                            DEPARTAMENT
                                        </th>
                                        <th class="bg-secondary" style="font-size: 10px;">
                                            REQUIRED TECHNICIAN
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="(
                                            tech, index
                                        ) in requesttech.requestitens"
                                        :key="tech.id"
                                    >
                                        <td style="font-size: 10px;">#{{ index + 1 }}</td>
                                        <td style="font-size: 10px;">{{ requesttech.id }}</td>
                                        <td style="font-size: 10px;">{{ tech.department ? tech.department.name : 'N/A' }}</td>
                                        <td style="font-size: 10px;">
                                            {{
                                                tech.technician == null
                                                    ? "------"
                                                    : tech.technician.name
                                            }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <table
                                class="table table-bordered"
                                v-for="(requesttoolshop, idx) in requesttool"
                                :key="requesttoolshop.id"
                            >
                                <thead>
                                    <tr>
                                        <th class="bg-secondary" style="font-size: 10px;" colspan="5">
                                            REQUEST TOOLS
                                        </th>
                                    </tr>
                                    <tr>
                                        <th class="bg-secondary" style="font-size: 10px;">#</th>
                                        <th class="bg-secondary" style="font-size: 10px;">REQUEST ID</th>
                                        <th class="bg-secondary" style="font-size: 10px;">TOOL</th>
                                        <th class="bg-secondary" style="font-size: 10px;">CODE</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="(
                                            toolshop, index
                                        ) in requesttoolshop.requestitens"
                                        :key="toolshop.id"
                                    >
                                        <td style="font-size: 10px;">{{ index + 1 }}</td>
                                        <td style="font-size: 10px;">{{ requesttoolshop.id }}</td>
                                        <td style="font-size: 10px;">{{ toolshop.tool.name }}</td>
                                        <td style="font-size: 10px;">{{ toolshop.tool.code }}</td>
                                    </tr>
                                </tbody>
                            </table>

                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th class="bg-secondary" style="font-size: 10px;" colspan="2">
                                            SIGNATURE
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="font-size: 10px;">TECHNICIAN NAME: {{ openedbyuser ? openedbyuser.firstName : '' }} {{ openedbyuser ? openedbyuser.lastName : '' }}</td>
                                        <td style="font-size: 10px;">CLIENT NAME: {{ retrievedData.destination.company_name }} </td>
                                        
                                    </tr>
                                    <tr>
                                        <td style="font-size: 10px;">SIGNATURE: 
                                            <div v-if="openedbyuser">
                                                <img :src="openedbyuser.signature" 
                                                    style="height: auto; 
                                                    width: auto; 
                                                    max-width: 140px; 
                                                    max-height: 140px;">
                                            </div>
                                        </td>
                                        
                                        <td style="font-size: 10px;">SIGNATURE:
                                            <div v-if="destinationuser">
                                                <img v-if="destinationuser.signature != null" :src="destinationuser.signature"  
                                                    style="height: auto; 
                                                    width: auto; 
                                                    max-width: 140px; 
                                                    max-height: 140px;">
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <div style="page-break-before:always">&nbsp;</div>

                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th class="bg-secondary" style="font-size: 10px;" colspan="5">
                                            TASKS
                                        </th>
                                    </tr>
                                    <tr>
                                        <th class="bg-secondary" style="font-size: 10px;">#</th>
                                        <th class="bg-secondary" style="font-size: 10px;">TASKS</th>
                                        <th class="bg-secondary" style="font-size: 10px;">TYPE</th>
                                        <th class="bg-secondary" style="font-size: 10px;">ANSWER</th>
                                        <th class="bg-secondary" style="font-size: 10px;">ANSWER</th>

                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(subtask, index) in retrievedData.subtasks" :key="subtask.id">

                                        <td style="font-size: 10px;">
                                            {{ index+1 }}
                                        </td>
                                        <td style="font-size: 10px;">
                                            {{ subtask.subtask.name }}
                                        </td>
                                        <td style="font-size: 10px;">
                                            {{ subtask.subtask.typesubtask.name }}
                                        </td>

                                        <td v-if="subtask.subtask.type_sub_task_id == 1" style="font-size: 10px;" ><span v-if="subtask.answer == 1">Sim</span> <span v-else>Não</span></td>
                                        <td v-if="subtask.subtask.type_sub_task_id == 2" style="font-size: 10px;"><span v-if="subtask.answer == 0">Mau</span> <span v-if="subtask.answer == 1">Bom</span> <span v-if="subtask.answer == 2">Excelente</span></td>
                                        <td v-if="subtask.subtask.type_sub_task_id == 3" style="font-size: 10px;"> 
                                            <div v-if="retrievedData.task_mcscr_status_id == 2">{{subtask.answer}}</div>
                                        </td>
                                       
                                    </tr>
                                </tbody>
                            </table>



                            
                           
                        
                        
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
                <br />
                <div class="d-flex justify-content-center">
                    Carregando Dados...
                </div>
            </div>
        </div>
    </div>
</template>
