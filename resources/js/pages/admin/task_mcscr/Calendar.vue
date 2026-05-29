<script setup>
import axios from 'axios';
import { ref, onMounted, reactive, defineEmits, defineComponent } from "vue";
import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import interactionPlugin from '@fullcalendar/interaction'
import timeGridPlugin from '@fullcalendar/timegrid'
import VueFeather from 'vue-feather';
import moment from 'moment'


const loadingDiv = ref(true);
const loadingModal = ref(true);
let mcscr =ref([]);
let taskmcscr =ref([]);

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
        loadingDiv.value=false;
       })
}



const openModal= async (data)=>{

    loadingModal.value = true;
    $('#modalCalendar').modal('show');



    axios.get(`/detailcalendar/${data}`)
       .then((response)=>{
        taskmcscr.value = response.data.taskmcscr;
        mcscr.value = response.data.mcscr;
        loadingModal.value=false;
       }).catch(()=>{
        $('#modalCalendar').modal('hide');
        loadingModal.value=true;
       })

    

}

onMounted(()=>{
     getEventList();
})

</script>

<template>
    <div v-if="!loadingDiv">
        <h1 class="h3 mb-3">Calendário das Manutenções</h1>
        <div class="card">
            <div class="card-header">
                <a @click="$router.go(-1)" class="btn btn-pill btn-primary mt-3"><vue-feather type="arrow-left"></vue-feather>Voltar</a> 
            </div>
            <div class="card-body">
                <h5 class="card-title fw-semibold mb-4">Calendário</h5>
                <p class="mb-0">Eventos</p>
            
                <FullCalendar :options="calendarOptions"/>

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

    <div class="modal" id="modalCalendar" tabindex="-1" role="dialog" aria-labelledby="modalCalendarTitle" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">Detalhes</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">

                    <div v-if="!loadingModal">
                        <div class="card">
                            <div class="card-body">
                                <div v-if="mcscr != null">
                                    <h3 class="modal-title" id="exampleModalLongTitle">Manutenção Corretiva</h3>
                                    <p><strong>Equipamento:</strong>{{ mcscr.equipment.name }}</p>
                                    <p><strong>Ref:</strong>{{ mcscr.equipment.ref }}</p>
                                    <p>
                                                            <strong>Estado: </strong>  
                                                           

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
                                    <p><strong>Motivo Descrito:</strong>  {{ mcscr.reason ?? 'N/A' }}</p>
                                    <p><strong>Motivo Registrado:</strong>  {{ mcscr.reason_name == null ? 'N/A': mcscr.reason_name.name+' | Código:'+mcscr.reason_name.code }}</p>
                                    <p><strong>Aberto em:</strong>  {{ moment(mcscr.opened_at).format('DD-MM-YYYY H:mm') }}</p>
                                    <p><strong>Fechado em:</strong>  {{ mcscr.closed_at == null ? 'N/A' : moment(mcscr.closed_at).format('DD-MM-YYYY H:mm') }}</p>
                                    <p class="text-danger"><strong>Tempo de Paralização:</strong>  
                                                        {{ mcscr.closed_at == null ? moment().diff(mcscr.opened_at,'hours')+' Horas ('+moment().diff(mcscr.opened_at,'minutes')+' Minutos)' :  
                                                               
                                                            moment(mcscr.closed_at).diff(mcscr.opened_at,'hours')+' Horas ('+moment(mcscr.closed_at).diff(mcscr.opened_at,'minutes')+' Minutos)'
                                                        
                                                        }}
                                    </p>

                                </div>
                                <div v-if="taskmcscr != null">
                                    <h3 class="modal-title" id="exampleModalLongTitle">Manutenção Preventiva</h3>
                                    <p><strong>Equipamento:</strong>{{ taskmcscr.equipment.name }}</p>
                                    <p><strong>Ref:</strong>{{ taskmcscr.equipment.ref }}</p>
                                    
                                    <p>
                                                        <strong>Estado: </strong>  
                                                           
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
                                    <p><strong>Plano de Actividade:</strong>{{ taskmcscr.task_plan.name }}</p>
                                    <p><strong>Actividade:</strong>{{ taskmcscr.task_plan_task.name }}</p>
                                    <p><strong>Programado por:</strong> {{ taskmcscr.schedule_by_user == null ? '------' :  taskmcscr.schedule_by_user.firstName +' '+ taskmcscr.schedule_by_user.lastName}} / {{ taskmcscr.schedule_for == null ? '------' : moment(taskmcscr.schedule_for).format('DD-MM-YYYY H:mm')  }}</p>
                                    <p class="text-danger"><strong>Tempo estimado:</strong>  {{ taskmcscr.task_plan_task.estimated_time_days }} Dias : {{ taskmcscr.task_plan_task.estimated_time_hours }} Horas : {{ taskmcscr.task_plan_task.estimated_time_minutes }} Minutos</p>
                                    <p class="text-danger"><strong>Tempo que o equipamento estará indisponível:</strong> {{ taskmcscr.task_plan_task.unavailable_equipment_time_days }} Dias : {{ taskmcscr.task_plan_task.unavailable_equipment_time_hours }} Horas : {{ taskmcscr.task_plan_task.unavailable_equipment_time_minutes }} Minutos</p>
                                    <p class="text-danger"><strong>Tempo paralizado:</strong>  
                                                {{ taskmcscr.closed_at == null ? moment().diff(taskmcscr.opened_at,'hours')+' Horas ('+moment().diff(taskmcscr.opened_at,'minutes')+' Minutos)' :  
                                                               
                                                               moment(taskmcscr.closed_at).diff(taskmcscr.opened_at,'hours')+' Horas ('+moment(taskmcscr.closed_at).diff(taskmcscr.opened_at,'minutes')+' Minutos)'
                                                            }}
                                    </p>
                                    <p><strong>Aberto em:</strong>  {{ taskmcscr.opened_at == null ? 'N/A' : moment(taskmcscr.opened_at).format('DD-MM-YYYY H:mm') }}</p>

                                    <p><strong>Fechado em:</strong>  {{ taskmcscr.closed_at == null ? 'N/A' : moment(taskmcscr.closed_at).format('DD-MM-YYYY H:mm') }}</p>
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