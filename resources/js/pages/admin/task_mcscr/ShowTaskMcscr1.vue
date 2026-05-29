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
                                        Condominio Areia Branca Lda
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
                                        <th class="bg-secondary" style="font-size: 10px;">
                                            SUBJECT OF THE OCCURRENCE
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="font-size: 10px;">
                                            {{
                                                retrievedData.reason ?? ""
                                            }}
                                            /
                                            {{
                                                retrievedData.reason_name ==
                                                null
                                                    ? ""
                                                    : retrievedData.reason_name
                                                          .name
                                            }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th class="bg-secondary" style="font-size: 10px;">
                                            CAUSE OF FAILURE
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="font-size: 10px;">
                                            {{ retrievedData.cause ?? "" }} /
                                            {{
                                                retrievedData.cause_name == null
                                                    ? ""
                                                    : retrievedData.cause_name
                                                          .name
                                            }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            
                           
                         
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
                                                retrievedData.first_observation ??
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
                                            <strong>{{ retrievedData.closed_at == null ? 'N/A' : moment(retrievedData.closed_at).format('DD-MM-YYYY H:mm') }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th class="bg-secondary" style="font-size: 10px;" colspan="5">
                                            STATUS
                                        </th>
                                    </tr>
                                    <tr>
                                        <th class="bg-secondary" style="font-size: 10px;">
                                            TASK PLAN
                                        </th>
                                        <th class="bg-secondary" style="font-size: 10px;">COMPONENT</th>
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
                                                            <span v-if="retrievedData.task_mcscr_status_id == 2" class="badge bg-danger">
                                                                {{ retrievedData.task_mcscr_status.name}}
                                                            </span>
                                                            <span v-if="retrievedData.task_mcscr_status_id == 3" class="badge bg-success">
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
                                        <td style="font-size: 10px;">{{ tech.department.name }}</td>
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
                                        <td style="font-size: 10px;">TECHNICIAN NAME: {{ openedbyuser.firstName }} {{ openedbyuser.lastName }}</td>
                                        <td style="font-size: 10px;">CLIENT NAME: {{ retrievedData.destination.company_name }} </td>
                                        
                                    </tr>
                                    <tr>
                                        <td style="font-size: 10px;">SIGNATURE: 
                                            <div v-if="openedbyuser.signature != null">
                                                <img :src="openedbyuser.signature" 
                                                    style="height: auto; 
                                                    width: auto; 
                                                    max-width: 140px; 
                                                    max-height: 140px;">
                                            </div>
                                        </td>
                                        
                                        <td style="font-size: 10px;">SIGNATURE:
                                            <div v-if="destinationuser != null">
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


<!--                           
                            <div style="page-break-before:always">&nbsp;</div>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th class="bg-secondary" style="font-size: 10px;" colspan="2">
                                            ATTACHMENTS
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <td style="font-size: 10px;">

                                    
                                    <div class="row">
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
                                    </div>
                                </td>
                                </tbody>
                            </table> -->

                            
                           
                        
                        
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
                                           
                                            

                                            <div class="row mb-5 border">
                                                
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