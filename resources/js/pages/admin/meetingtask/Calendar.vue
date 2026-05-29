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
let task =ref([]);

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
  axios.get('/meeting-task-calendars')
       .then((response)=>{
        calendarOptions.events = response.data;
        loadingDiv.value=false;
       })
}



const openModal= async (data)=>{

    loadingModal.value = true;
    $('#modalCalendar').modal('show');



    axios.get(`/meeting-task-detailcalendar/${data}`)
       .then((response)=>{
        task.value = response.data.task;
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
        <h1 class="h3 mb-3">Calendário Tarefas</h1>
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
                                <div>
                                    <h3 class="modal-title" id="exampleModalLongTitle">Tarefa</h3>
                                    <p><strong>Tarefa:</strong>{{ task.what}}</p>
                                    <p><strong>Prazo:</strong>{{ task.date }}</p>
                                    <p>
                                                            <strong>Estado: </strong>  
                                                           

                                                            <span v-if="task.status_id == 1" class="badge bg-success">
                                                                {{ task.status.name}}
                                                            </span>
                                                            <span v-if="task.status_id == 2" class="badge bg-danger">
                                                                {{ task.status.name}}
                                                            </span>
                                    </p>
                                    <p><strong>Participante:</strong>  {{ task.participant.name }}</p>
                                    <p><strong>Reunião:</strong>  {{ task.meeting.subject}}</p>
                                   

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