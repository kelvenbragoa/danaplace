<script setup>

import {onMounted, ref, reactive, onUpdated} from 'vue';
import axios from 'axios';
import VueFeather from 'vue-feather';
import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import interactionPlugin from '@fullcalendar/interaction'
import timeGridPlugin from '@fullcalendar/timegrid'
import moment from 'moment'



const loadingDiv = ref(true);
const users = ref(0)
const areas = ref(0)
const destinations = ref(0)
const centercosts = ref(0)
const typeequipments = ref(0)
const malfunctions = ref(0)
const suppliers = ref(0)
const tasks = ref(0)
const equipments = ref(0)
const mcscrnumber = ref(0)

const loadingModal = ref(true);


let mcscr =ref([]);
let taskmcscr =ref([]);
let requesttechnician =ref([]);

const calendarOptions =  reactive({
        plugins: [ dayGridPlugin, interactionPlugin ,timeGridPlugin],
        headerToolbar: {
          left: 'prev,next today',
          center: 'title',
          right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events:"",
        initialView: 'dayGridMonth',
        eventClick: function(info) {
            openModal(info.event.id);
            // alert('Event: ' + info.event.title + info.event.id);
            // $('#deleteModal').modal('show');

            // change the border color just for fun
            info.el.style.borderColor = 'red';
        }
    
        
      })

const getEventList = async () => {
  axios.get('/calendars')
       .then((response)=>{
        calendarOptions.events = response.data;
        // loadingDiv.value=false;
       })
}

const closeModal = ()=>{
    $('#modalCalendar').modal('hide');
}

const openModal= async (data)=>{

loadingModal.value = true;
$('#modalCalendar').modal('show');



axios.get(`/detailcalendar/${data}`)
   .then((response)=>{
    taskmcscr.value = response.data.taskmcscr;
    mcscr.value = response.data.mcscr;
    requesttechnician.value = response.data.requesttechnician;

    loadingModal.value=false;
   }).catch(()=>{
    $('#modalCalendar').modal('hide');
    loadingModal.value=true;
   })

}



const getDashboardData = () =>{
    axios.get('/admins/dashboard/getdashboarddata')
    .then((response)=>{
        users.value = response.data.users
        areas.value = response.data.areas
        destinations.value = response.data.destinations
        centercosts.value = response.data.centercosts
        typeequipments.value = response.data.typeequipments
        malfunctions.value = response.data.malfunctions
        suppliers.value = response.data.suppliers
        tasks.value = response.data.tasks
        equipments.value = response.data.equipments
        mcscrnumber.value = response.data.mcscr
        loadingDiv.value=false;
    })
}


onMounted(()=>{
    getEventList();
    getDashboardData();

})


onUpdated(()=>{

})



</script>


<template>

    <div v-if="!loadingDiv">
        <h1 class="h3 mb-3">{{ $t('message.dashboard') }}</h1>
        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">{{ $t('message.dashboard') }}</h5>
                                    </div>
                                    <div class="card-body">

                                        <h5 class="card-title fw-semibold mb-4">{{ $t('message.calendar') }}</h5>
                                        <p class="mb-0">{{ $t('message.events') }}</p>
                                    
                                        <FullCalendar :options="calendarOptions"/>

                                        <div class="row">
                                            <div class="col-sm-6 col-xl-3">
                                                <div class="card">
                                                    <div class="card-body">
                                                        <div class="row">
                                                            <div class="col mt-0">
                                                                <h5 class="card-title">{{ $t('message.users') }}</h5>
                                                            </div>

                                                            <div class="col-auto">
                                                                <div class="stat text-primary">
                                                                    <vue-feather type="users"></vue-feather>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <h1 class="mt-1 mb-3">{{users}}</h1>
                                                        <div class="mb-0">
                                                             <router-link to="/admin/users"><vue-feather type="eye"></vue-feather></router-link>
                                                        </div>
                                                        
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-6 col-xl-3">
                                                <div class="card">
                                                    <div class="card-body">
                                                        <div class="row">
                                                            <div class="col mt-0">
                                                                <h5 class="card-title">{{ $t('message.areas') }}</h5>
                                                            </div>

                                                            <div class="col-auto">
                                                                <div class="stat text-primary">
                                                                    <vue-feather type="square"></vue-feather>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <h1 class="mt-1 mb-3">{{areas}}</h1>
                                                        <div class="mb-0">
                                                             <router-link to="/admin/areas"><vue-feather type="eye"></vue-feather></router-link>
                                                        </div>
                                                        
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-6 col-xl-3">
                                                <div class="card">
                                                    <div class="card-body">
                                                        <div class="row">
                                                            <div class="col mt-0">
                                                                <h5 class="card-title">{{ $t('message.application_destination') }}</h5>
                                                            </div>

                                                            <div class="col-auto">
                                                                <div class="stat text-primary">
                                                                    <vue-feather type="codepen"></vue-feather>
                                                           
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <h1 class="mt-1 mb-3">{{destinations}}</h1>
                                                        <div class="mb-0">
                                                             <router-link to="/admin/destinations"><vue-feather type="eye"></vue-feather></router-link>
                                                        </div>
                                                        
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-6 col-xl-3">
                                                <div class="card">
                                                    <div class="card-body">
                                                        <div class="row">
                                                            <div class="col mt-0">
                                                                <h5 class="card-title">{{ $t('message.center_cost') }}</h5>
                                                            </div>

                                                            <div class="col-auto">
                                                                <div class="stat text-primary">
                                                                    <vue-feather type="dollar-sign"></vue-feather>
                                                                    
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <h1 class="mt-1 mb-3">{{centercosts}}</h1>
                                                        <div class="mb-0">
                                                             <router-link to="/admin/centercost"><vue-feather type="eye"></vue-feather></router-link>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-sm-6 col-xl-3">
                                                <div class="card">
                                                    <div class="card-body">
                                                        <div class="row">
                                                            <div class="col mt-0">
                                                                <h5 class="card-title">{{ $t('message.type_equipments') }}</h5>
                                                            </div>

                                                            <div class="col-auto">
                                                                <div class="stat text-primary">
                                                                    <vue-feather type="list"></vue-feather>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <h1 class="mt-1 mb-3">{{typeequipments}}</h1>
                                                        <div class="mb-0">
                                                             <router-link to="/admin/type_equipments"><vue-feather type="eye"></vue-feather></router-link>
                                                        </div>
                                                        
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-6 col-xl-3">
                                                <div class="card">
                                                    <div class="card-body">
                                                        <div class="row">
                                                            <div class="col mt-0">
                                                                <h5 class="card-title">{{ $t('message.type_of_fault') }}</h5>
                                                            </div>

                                                            <div class="col-auto">
                                                                <div class="stat text-primary">
                                                                    <vue-feather type="alert-triangle"></vue-feather>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <h1 class="mt-1 mb-3">{{malfunctions}}</h1>
                                                        <div class="mb-0">
                                                             <router-link to="/admin/malfunctions"><vue-feather type="eye"></vue-feather></router-link>
                                                        </div>
                                                        
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-6 col-xl-3">
                                                <div class="card">
                                                    <div class="card-body">
                                                        <div class="row">
                                                            <div class="col mt-0">
                                                                <h5 class="card-title">{{ $t('message.suppliers') }}</h5>
                                                            </div>

                                                            <div class="col-auto">
                                                                <div class="stat text-primary">
                                                                    <vue-feather type="truck"></vue-feather>
                                                           
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <h1 class="mt-1 mb-3">{{suppliers}}</h1>
                                                        <div class="mb-0">
                                                             <router-link to="/admin/suppliers"><vue-feather type="eye"></vue-feather></router-link>
                                                        </div>
                                                        
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-6 col-xl-3">
                                                <div class="card">
                                                    <div class="card-body">
                                                        <div class="row">
                                                            <div class="col mt-0">
                                                                <h5 class="card-title">{{ $t('message.tasks') }}</h5>
                                                            </div>

                                                            <div class="col-auto">
                                                                <div class="stat text-primary">
                                                                    <vue-feather type="tablet"></vue-feather>
                                                                    
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <h1 class="mt-1 mb-3">{{tasks}}</h1>
                                                        <div class="mb-0">
                                                             <router-link to="/admin/tasks"><vue-feather type="eye"></vue-feather></router-link>
                                                        </div>
                                                        
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-6 col-xl-3">
                                                <div class="card">
                                                    <div class="card-body">
                                                        <div class="row">
                                                            <div class="col mt-0">
                                                                <h5 class="card-title">{{ $t('message.equipments') }}</h5>
                                                            </div>

                                                            <div class="col-auto">
                                                                <div class="stat text-primary">
                                                                    <vue-feather type="package"></vue-feather>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <h1 class="mt-1 mb-3">{{equipments}}</h1>
                                                        <div class="mb-0">
                                                             <router-link to="/admin/equipments"><vue-feather type="eye"></vue-feather></router-link>
                                                        </div>
                                                        
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-6 col-xl-3">
                                                <div class="card">
                                                    <div class="card-body">
                                                        <div class="row">
                                                            <div class="col mt-0">
                                                                <h5 class="card-title">{{ $t('message.job_card') }}</h5>
                                                            </div>

                                                            <div class="col-auto">
                                                                <div class="stat text-primary">
                                                                    <vue-feather type="database"></vue-feather>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <h1 class="mt-1 mb-3">{{mcscrnumber}}</h1>
                                                        <div class="mb-0">
                                                             <router-link to="/admin/mcscr"><vue-feather type="eye"></vue-feather></router-link>
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
                    {{ $t('message.loading') }}
                </div>
            </div> 
        </div>
    </div>
    <div class="modal" id="modalCalendar" tabindex="-1" role="dialog" aria-labelledby="modalCalendarTitle" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">{{ $t('message.details') }}</h5>
                    <button type="button" class="close" @click="closeModal" data-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">

                    <div v-if="!loadingModal">
                        <div class="card">
                            <div class="card-body">
                                <div v-if="mcscr != null">
                                    <div class="row">
                                        <div class="col">
                                            <h3 class="modal-title" id="exampleModalLongTitle">{{ $t('message.maintenance_corrective') }}</h3>
                                            <p><strong>{{ $t('message.application_destination') }}:</strong>{{ mcscr.destination.name }}</p>
                                            <p><strong>{{ $t('message.equipment') }}:</strong>{{ mcscr.equipment.name }}</p>
                                            <p><strong>{{ $t('message.ref') }}:</strong>{{ mcscr.equipment.ref }}</p>
                                            <p>
                                                                    <strong>{{ $t('message.status') }}: </strong>  
                                                                

                                                                    <span v-if="mcscr.mcscr_status_id == 1" class="badge bg-success">
                                                                        {{ mcscr.mcscr_status.name}}
                                                                    </span>
                                                                    <span v-if="mcscr.mcscr_status_id == 2" class="badge bg-warning">
                                                                        {{ mcscr.mcscr_status.name}}
                                                                    </span>
                                                                    <span v-if="mcscr.mcscr_status_id == 3" class="badge bg-danger">
                                                                        {{ mcscr.mcscr_status.name}}
                                                                    </span>
                                                                    <span v-if="mcscr.mcscr_status_id == 4" class="badge bg-info">
                                                                        {{ mcscr.mcscr_status.name}}
                                                                    </span>
                                                                    <span v-if="mcscr.mcscr_status_id == 5" class="badge bg-primary">
                                                                        {{ mcscr.mcscr_status.name}}
                                                                    </span>
                                            </p>
                                            <p><strong>{{ $t('message.reason') }}:</strong>  {{ mcscr.reason ?? 'N/A' }}</p>
                                            <p><strong>{{ $t('message.reason') }}:</strong>  {{ mcscr.reason_name == null ? 'N/A': mcscr.reason_name.name+' | Código:'+mcscr.reason_name.code }}</p>
                                            <p><strong>{{ $t('message.created_at') }}:</strong>  {{ moment(mcscr.opened_at).format('DD-MM-YYYY H:mm') }}</p>
                                            <p><strong>{{ $t('message.closed_at') }}:</strong>  {{ mcscr.closed_at == null ? 'N/A' : moment(mcscr.closed_at).format('DD-MM-YYYY H:mm') }}</p>
                                            <p class="text-danger"><strong>Tempo de Paralização:</strong>  
                                                                {{ mcscr.closed_at == null ? moment().diff(mcscr.opened_at,'hours')+' Horas ('+moment().diff(mcscr.opened_at,'minutes')+' Minutos)' :  
                                                                    
                                                                    moment(mcscr.closed_at).diff(mcscr.opened_at,'hours')+' Horas ('+moment(mcscr.closed_at).diff(mcscr.opened_at,'minutes')+' Minutos)'
                                                                
                                                                }}
                                            </p>
                                        </div>
                                        <div class="col">
                                            <!-- <h3>Requisições de Técnicos: {{ requesttechnician.length }}</h3> -->
                                            <table
                                                class="table table-bordered"
                                                v-for="(requesttech, idx) in requesttechnician"
                                                :key="requesttech.id"
                                            >
                                                <thead>
                                                    <tr>
                                                        <th class="bg-secondary" style="font-size: 10px;" colspan="4">
                                                            {{ $t('message.technical_requirements') }}
                                                        </th>
                                                    </tr>
                                                    <tr>
                                                        <th class="bg-secondary" style="font-size: 10px;" colspan="3">
                                                            {{ $t('message.status') }}
                                                        </th>
                                                        <th class="bg-secondary" style="font-size: 10px;" colspan="1">
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
                                                        </th>
                                                    </tr>
                                                    <tr>
                                                        <th class="bg-secondary" style="font-size: 10px;">#</th>
                                                        <th class="bg-secondary" style="font-size: 10px;">{{ $t('message.request_id') }}</th>
                                                        <th class="bg-secondary" style="font-size: 10px;">
                                                            {{ $t('message.department') }}
                                                        </th>
                                                        <th class="bg-secondary" style="font-size: 10px;">
                                                            {{ $t('message.required_technician') }}
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
                                        </div>
                                    </div>
                                   

                                </div>
                                <div v-if="taskmcscr != null">
                                    <div class="row">
                                        <div class="col">
                                            <h3 class="modal-title" id="exampleModalLongTitle">{{ $t('message.maintenance_preventive') }}</h3>
                                            <p><strong>{{ $t('message.application_destination') }}:</strong>{{ taskmcscr.destination.name }}</p>
                                            <p><strong>{{ $t('message.equipment') }}:</strong>{{ taskmcscr.equipment.name }}</p>
                                            <p><strong>{{ $t('message.ref') }}:</strong>{{ taskmcscr.equipment.ref }}</p>
                                            
                                            <p>
                                                                <strong>{{ $t('message.status') }}: </strong>  
                                                                
                                                                    <span v-if="taskmcscr.task_mcscr_status_id == 1" class="badge bg-warning">
                                                                        {{ taskmcscr.task_mcscr_status.name}}
                                                                    </span>
                                                                    <span v-if="taskmcscr.task_mcscr_status_id == 2" class="badge bg-danger">
                                                                        {{ taskmcscr.task_mcscr_status.name}}
                                                                    </span>
                                                                    <span v-if="taskmcscr.task_mcscr_status_id == 3" class="badge bg-success">
                                                                        {{ taskmcscr.task_mcscr_status.name}}
                                                                    </span>
                                                                    <span v-if="taskmcscr.task_mcscr_status_id == 4" class="badge bg-info">
                                                                        {{ taskmcscr.task_mcscr_status.name}} ({{moment(taskmcscr.schedule_for).format('DD-MM-YYYY H:mm')}})
                                                                    </span>
                                            </p>
                                            <p><strong>{{ $t('message.activity_plan') }}:</strong>{{ taskmcscr.task_plan.name }}</p>
                                            <p><strong>{{ $t('message.activity') }}:</strong>{{ taskmcscr.task_plan_task.name }}</p>
                                            <p><strong>{{ $t('message.scheduled_by') }}:</strong> {{ taskmcscr.schedule_by_user == null ? '------' :  taskmcscr.schedule_by_user.firstName +' '+ taskmcscr.schedule_by_user.lastName}} / {{ taskmcscr.schedule_for == null ? '------' : moment(taskmcscr.schedule_for).format('DD-MM-YYYY H:mm')  }}</p>
                                            <p class="text-danger"><strong>{{ $t('message.estimated_time') }}:</strong>  {{ taskmcscr.task_plan_task.estimated_time_days }} Dias : {{ taskmcscr.task_plan_task.estimated_time_hours }} Horas : {{ taskmcscr.task_plan_task.estimated_time_minutes }} Minutos</p>
                                            <p class="text-danger"><strong>{{ $t('message.time_unavailable') }}:</strong> {{ taskmcscr.task_plan_task.unavailable_equipment_time_days }} Dias : {{ taskmcscr.task_plan_task.unavailable_equipment_time_hours }} Horas : {{ taskmcscr.task_plan_task.unavailable_equipment_time_minutes }} Minutos</p>
                                            <p class="text-danger"><strong>{{ $t('message.stoppage_time') }}:</strong>  
                                                        {{ taskmcscr.closed_at == null ? moment().diff(taskmcscr.opened_at,'hours')+' Horas ('+moment().diff(taskmcscr.opened_at,'minutes')+' Minutos)' :  
                                                                    
                                                                    moment(taskmcscr.closed_at).diff(taskmcscr.opened_at,'hours')+' Horas ('+moment(taskmcscr.closed_at).diff(taskmcscr.opened_at,'minutes')+' Minutos)'
                                                                    }}
                                            </p>
                                            <p><strong>{{ $t('message.created_at') }}:</strong>  {{ taskmcscr.opened_at == null ? 'N/A' : moment(taskmcscr.opened_at).format('DD-MM-YYYY H:mm') }}</p>

                                            <p><strong>{{ $t('message.closed_at') }}:</strong>  {{ taskmcscr.closed_at == null ? 'N/A' : moment(taskmcscr.closed_at).format('DD-MM-YYYY H:mm') }}</p>
                                        </div>
                                        <div class="col">
                                            <h3>{{ $t('message.technical_requirements') }}: {{ requesttechnician.length }}</h3>
                                            <table
                                                class="table table-bordered"
                                                v-for="(requesttech, idx) in requesttechnician"
                                                :key="requesttech.id"
                                            >
                                                <thead>
                                                    <tr>
                                                        <th class="bg-secondary" style="font-size: 10px;" colspan="5">
                                                            {{ $t('message.technical_requirements') }}
                                                        </th>
                                                    </tr>
                                                    <tr>
                                                        <th class="bg-secondary" style="font-size: 10px;">#</th>
                                                        <th class="bg-secondary" style="font-size: 10px;">REQUEST ID</th>
                                                        <th class="bg-secondary" style="font-size: 10px;">
                                                            {{ $t('message.department') }}
                                                        </th>
                                                        <th class="bg-secondary" style="font-size: 10px;">
                                                            {{ $t('message.required_technician') }}
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
                                    {{ $t('message.loading') }}
                                </div>
                            </div> 
                        </div>
                    </div>
                </div>
                <!-- <div class="modal-footer">
                
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                        <button @click.prevent="deleteData" type="button" class="btn btn-danger" :disabled="loadingButtonDelete">
                            <div v-if="loadingButtonDelete" class="spinner-border spinner-border-sm" role="status"></div>
                            <span v-else>Apagar registro</span>
                        </button>
                </div> -->
            </div>
        </div>
  </div>
</template>