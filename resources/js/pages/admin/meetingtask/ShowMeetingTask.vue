<script setup>

import axios from 'axios';
import { ref, onMounted, reactive, defineEmits, defineComponent,watch } from "vue";
import moment from 'moment'
import {useToastr} from '../../../toastr';
import {debounce} from 'lodash';
import {Form, Field, FieldArray} from 'vee-validate';
import { useRouter} from "vue-router";
import * as yup from 'yup';
import VueFeather from 'vue-feather';
import { Bootstrap4Pagination } from 'laravel-vue-pagination';
import { usePaperizer } from "paperizer";


let retrievedData =ref([]);
let loadingSubmit =ref([true]);
let loadingDiv =ref([true]);
let dataBeingEdited = ref();
const router = useRouter();
const loadingprint = ref(false);
let self = this;
let searchQuery = ref(null)
let roles = ref([]);
let tasks = ref([]);
let participants = ref([]);
let currentvalue = ref([]);
let userparticipants = ref([]);
let user_source = ref(1)
let role_id_to_user = ref(0)
let dataIdBeingDeleted = ref(0);
const toastr = useToastr();
const loadingButtonDelete = ref(false);
const meeting_participant_id_edit = ref(0);
const statuses = ref();

const meeting_participant_id_form_edit = ref(0);
const date_form_edit = ref(0);
const what_form_edit = ref(0);
const status_id_form_edit = ref(0);


const schema = yup.object({
    obs: yup.string(),
    email: yup.string(),
    name: yup.string()


  });

  const schema2 = yup.object({
    participant_id: yup.string(),
    meeting_id: yup.string(),
    date: yup.string(),
    what: yup.string()

  });
  const schema3 = yup.object({
    obs: yup.string(),
    meeting_participant_id: yup.string(),
  });

  const schema4 = yup.object({
    meeting_participant_id: yup.string(),
    status_id: yup.string(),
  });

  const schema5 = yup.object({

  });

  const createRecordFunction2 = (values, actions) => {

 
currentvalue.value = {values};

loadingSubmit.value = true;

const arr = Array.from(values)

axios.post('/meetingtask',values).then((response)=>{

// categories.value.unshift(response.data);
// $('#createCategory').modal('hide');

tasks.value = response.data.tasks;
actions.resetField('participant_id');
actions.resetField('date');
actions.resetField('what');
loadingSubmit.value = false;

toastr.success('Tarefa adicionada com successo');
}).catch((error)=>{

loadingSubmit.value = false;
toastr.error('Erro ao associar. '+error.response.data.message);
if(error.response.data.errors){
   
    actions.setErrors(error.response.data.errors);
}
}).finally(()=>{
loadingSubmit.value = false;

})

};


const createRecordFunction = (values, actions) => {

 
currentvalue.value = {values};

loadingSubmit.value = true;

const arr = Array.from(values)

axios.post('/meetingparticipant',values).then((response)=>{

// categories.value.unshift(response.data);
// $('#createCategory').modal('hide');

participants.value = response.data.participants;
actions.resetField('role_id');
actions.resetField('name');
actions.resetField('email');
actions.resetField('obs');
loadingSubmit.value = false;

toastr.success('Participante adicionado com successo');
}).catch((error)=>{

loadingSubmit.value = false;
toastr.error('Erro ao associar. '+error.response.data.message);
if(error.response.data.errors){
   
    actions.setErrors(error.response.data.errors);
}
}).finally(()=>{
loadingSubmit.value = false;

})

};

const confirmCopyTask = (data) => {

meeting_participant_id_form_edit.value = data.meeting_participant_id;
date_form_edit.value = data.date;
what_form_edit.value = data.what;
status_id_form_edit.value = data.status_id;
dataBeingEdited.value = data.id;
$('#copyModalTask').modal('show');
};

const confirmEditTask = (data) => {

meeting_participant_id_form_edit.value = data.meeting_participant_id;
date_form_edit.value = data.date;
what_form_edit.value = data.what;
status_id_form_edit.value = data.status_id;
dataBeingEdited.value = data.id;
$('#editModalTask').modal('show');
};

const confirmEditParticipant = (data) => {
dataBeingEdited.value = data;
meeting_participant_id_edit.value = data.id;
$('#editModalParticipant').modal('show');
};

const confirmDeletionParticipant = (data) => {
dataIdBeingDeleted = data.id;
$('#deleteModalParticipant').modal('show');
};

const deleteDataParticipant = () =>{

loadingButtonDelete.value= true;

axios.delete(`/meetingparticipant/${dataIdBeingDeleted}`)
.then(()=>{
    participants.value = participants.value.filter(data=>data.id !== dataIdBeingDeleted); 
 $('#deleteModalParticipant').modal('hide');

 toastr.success('Registro apagada com sucesso');

}).catch(()=>{
 toastr.error('Erro ao apagar');
 loadingButtonDelete.value= false;
 $('#deleteModalTask').modal('hide');
}).finally(()=>{
 loadingButtonDelete.value= false;
});
}

const editDataTask = (values, actions) =>{

loadingButtonDelete.value= true;


axios.patch(`/meetingtask/${dataBeingEdited.value}`,values)
.then((response)=>{
    tasks.value = response.data.tasks;
     $('#editModalTask').modal('hide');

 toastr.success('Registro editado com sucesso');

}).catch((e)=>{
    console.log(e)
 toastr.error('Erro ao apagar');
 loadingButtonDelete.value= false;
 $('#editModalTask').modal('hide');
}).finally(()=>{
 loadingButtonDelete.value= false;
});
}

const copyDataTask = (values, actions) =>{

loadingButtonDelete.value= true;


axios.post(`/copymeetingtask`,values)
.then((response)=>{
    tasks.value = response.data.tasks;
     $('#copyModalTask').modal('hide');

 toastr.success('Registro editado com sucesso');

}).catch((e)=>{
    console.log(e)
 toastr.error('Erro ao apagar');
 loadingButtonDelete.value= false;
 $('#copyModalTask').modal('hide');
}).finally(()=>{
 loadingButtonDelete.value= false;
});
}

const editDataParticipant = (values, actions) =>{

loadingButtonDelete.value= true;


axios.patch(`/meetingparticipant/${meeting_participant_id_edit.value}`,values)
.then((response)=>{
    participants.value = response.data.participants;
     $('#editModalParticipant').modal('hide');

 toastr.success('Registro editado com sucesso');

}).catch((e)=>{
    console.log(e)
 toastr.error('Erro ao apagar');
 loadingButtonDelete.value= false;
 $('#editModalParticipant').modal('hide');
}).finally(()=>{
 loadingButtonDelete.value= false;
});
}

const confirmDeletionTask = (data) => {
dataIdBeingDeleted = data.id;
$('#deleteModalTask').modal('show');
};

const deleteDataTask = () =>{

loadingButtonDelete.value= true;

axios.delete(`/meetingtask/${dataIdBeingDeleted}`)
.then(()=>{
    tasks.value = tasks.value.filter(data=>data.id !== dataIdBeingDeleted); 
 $('#deleteModalTask').modal('hide');

 toastr.success('Registro apagada com sucesso');

}).catch(()=>{
 toastr.error('Erro ao apagar');
 loadingButtonDelete.value= false;
 $('#deleteModalTask').modal('hide');
}).finally(()=>{
 loadingButtonDelete.value= false;
});
}
const { paperize } = usePaperizer("print-me", {
    styles: [
        "https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css",
    ],
});
const downloadMcscr = () => {
    loadingprint.value = true;
    paperize();
    loadingprint.value = false;
};


const getData = (page=1) => {
  axios.get(`/meeting/+${router.currentRoute.value.params.id}?page=${page}`,
      {
        params:{
          query: searchQuery.value
        }
      })
       .then((response)=>{
        loadingDiv.value=false;
        retrievedData.value = response.data.meeting;
        roles.value = response.data.roles;
        participants.value = response.data.participants;
        tasks.value = response.data.tasks;
        statuses.value = response.data.statuses;

       }).catch(()=>{
        loadingDiv.value=false;
       })
}

const getUser = (roleid) => {

axios.get(`/auxiliar-create-meeting/${roleid}`)
   .then((response)=>{

    userparticipants.value = response.data.participants;
})
   .catch((error)=>{
    toastr.error(error);
    router.push({ path: '/admin/meeting' });
   })


}


watch(searchQuery,debounce(()=>{
    getData();
},300));

onMounted(()=>{
  
  getData();
})
</script>

<template>
    <div v-if="!loadingDiv">

        <h1 class="h3 mb-3">Reunião</h1>
        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Reunião: {{ retrievedData.subject }}</h5>

                                        <router-link to="/admin/meeting" class="btn btn-pill btn-primary mt-3"><vue-feather type="arrow-left"></vue-feather>Voltar</router-link> 

                                       
								    </div>
                                    
                                    <div class="card-body">
                                          
                                        <div class="row">
                                            <div class="col-xl-12 col-xxl-12 d-flex">
                                                <div class="w-100">
                                                    <p>Data : {{ moment(retrievedData.date).format('DD-MM-YYYY')}} </p>
                                                    <p>Inicio : {{ retrievedData.start_time }}</p>
                                                    <p>Fim : {{ retrievedData.end_time }}</p>
                                                    <p>Assunto : {{ retrievedData.subject }}</p>
                                                    <p>Tipo de Reunião: {{ retrievedData.typemeeting.name }}</p>
                                                    <p>Corpo: <router-link :to="'/admin/meeting/'+retrievedData.id+'/edit'"><vue-feather type="edit-2"></vue-feather> Editar</router-link> </p>
                                                    <div>
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
                                                    <a
                                                        
                                                        class="btn btn-primary ml-2"
                                                        data-toggle="collapse" href="#collapseDetalhes" role="button" aria-expanded="false" aria-controls="collapseDetalhes"
                                                    >
                                                        
                                                        <span>Ver</span>
                                                </a>
                                                </div>
                                                    
                                                <div class="collapse mt-3" id="collapseDetalhes">
                                                    <hr>

                                                    <div id="print-me">
                                                        <div class="row">
                                                            <br />
                                                            <div
                                                                class="col text-left"
                                                                style="text-align: left"
                                                            >
                                                                <img
                                                                    src="/files/img/sys/companylogo.png"
                                                                    class="img-fluid"
                                                                    alt="image"
                                                                    width="150"
                                                                    height="150"
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
                                                                    width="150"
                                                                    height="150"
                                                                    style="text-align: right"
                                                                /> -->
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col">
                                                                
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
                                                                    <th>
                                                                        ID:
                                                                        #{{
                                                                           
                                                                                retrievedData.id
                                                                            
                                                                        }}
                                                                    </th>
                                                                    <th>
                                                                        DATE:
                                                                        {{
                                                                            moment(
                                                                                retrievedData.date
                                                                            ).format("DD-MM-YYYY")
                                                                        }}
                                                                    </th>
                                                                    <th>
                                                                        TIME:
                                                                        {{retrievedData.start_time}} -  {{retrievedData.end_time}}
                                                                    </th>
                                                                    <th>
                                                                        TYPE:
                                                                        {{ retrievedData.typemeeting.name }}
                                                                    </th>
                                                                    
                                                                </tr>
                                                            </thead>
                                                        
                                                        </table>

                                                         
                                                    
                                                    
                                                    
                                                        <table class="table table-bordered">
                                                            <thead>
                                                                <tr align="center">
                                                                    <th class="bg-secondary" colspan="5" >
                                                                        SUBJECT
                                                                    </th>
                                                                </tr>
                                                                
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td colspan="5">
                                                                        {{ retrievedData.subject }}
                                                                    </td>
                                                                   
                                                                </tr>
                                                            </tbody>
                                                            
                                                            <thead>
                                                                <tr align="center">
                                                                    <th class="bg-secondary" colspan="5">
                                                                        PARTICIPANTS
                                                                    </th>
                                                                </tr>
                                                                
                                                            </thead>
                                                            <thead>
                                                                <tr>
                                                                    <th class="bg-secondary" colspan="2">
                                                                        NAME
                                                                    </th>
                                                                    <!-- <th class="bg-secondary">
                                                                        EMAIL
                                                                    </th>
                                                                    <th class="bg-secondary">
                                                                        ROLE
                                                                    </th> -->
                                                                    <th class="bg-secondary" colspan="2">
                                                                        OBS
                                                                    </th>
                                                                    <th class="bg-secondary" colspan="1">
                                                                        CONSENSUS
                                                                    </th>
                                                                </tr>
                                                                
                                                            </thead>
                                                            <tbody>
                                                                <tr  v-for="(actualData,index) in participants" :key="actualData.id">
                                                                    <td colspan="2">{{ actualData.name}}</td>
                                                                    <!-- <td>{{ actualData.email}}</td>
                                                                    <td>{{ actualData.role == null ? 'Convidado' : actualData.role.name}}</td> -->
                                                                    <td colspan="2">{{ actualData.obs}}</td>
                                                                    <td colspan="1">
                                                                        <span v-if="actualData.status == 1" class="badge bg-success">
                                                                            De acordo
                                                                        </span>
                                                                        <span v-if="actualData.status == 0" class="badge bg-danger">
                                                                            Discordância
                                                                        </span>
                                                                    </td>
                                                                    
                                                                </tr>
                                                            </tbody>
                                                            <thead>
                                                                <tr align="center">
                                                                    <th class="bg-secondary" colspan="5" >
                                                                        MEETING
                                                                    </th>
                                                                </tr>
                                                                
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td colspan="5">
                                                                        <div v-html="retrievedData.body"></div>
                                                                    </td>
                                                                   
                                                                </tr>
                                                            </tbody>
                                                            <thead>
                                                                <tr align="center">
                                                                    <th class="bg-secondary" colspan="5">
                                                                        TASKS
                                                                    </th>
                                                                </tr>
                                                                
                                                            </thead>
                                                            <thead>
                                                                <tr>
                                                                    
                                                                    <th class="bg-secondary">NAME</th>
                                                                    <th class="bg-secondary">EMAIL</th>
                                                                    <th class="bg-secondary">TASK</th>
                                                                    <th class="bg-secondary">DATE</th>
                                                                    <th class="bg-secondary">STATUS</th>
                                                                </tr>
                                                                
                                                            </thead>
                                                            <tbody>
                                                                <tr  v-for="(actualData,index) in tasks" :key="actualData.id">
                                                                    <td>{{ actualData.participant.name}}</td>
                                                                    <td>{{ actualData.participant.email}}</td>
                                                                    <td>{{ actualData.what}}</td>
                                                                    <td>{{ actualData.date}}</td>
                                                                    <td>
                                                                        <span v-if="actualData.status_id == 1" class="badge bg-success">
                                                                            {{actualData.status.name}}
                                                                        </span>
                                                                        <span v-if="actualData.status_id == 2" class="badge bg-danger">
                                                                            {{actualData.status.name}}
                                                                        </span>
                                                                        <span v-if="actualData.status_id == 4" class="badge bg-warning">
                                                                            {{actualData.status.name}}
                                                                        </span>
                                                                        <span v-if="actualData.status_id == 3" class="badge bg-info">
                                                                            {{actualData.status.name}}
                                                                        </span>
                                                                    </td>
                                                                    
                                                                </tr>
                                                            </tbody>
                                                        </table> 
                                                        <!-- <div style="page-break-before:always">&nbsp;</div>
                                                        <h3 class="text-center">Meeting</h3>
                                                        <span style="text-align: justify; text-justify: inter-word; line-height:1.8">
                                                            <div v-html="retrievedData.body"></div>
                                                        </span>                         -->

                                                    </div>
                                                    <hr>
                                                </div>


                                                    
                                                    <h5 class="card-title">Participantes: {{ participants.length }} registros encontrados.</h5>
                                                    <a class="btn btn-pill btn-primary mt-3" data-toggle="collapse" href="#collapseExample" role="button" aria-expanded="false" aria-controls="collapseExample">
                                                        <vue-feather type="plus"></vue-feather>Adicionar Participantes
                                                    </a>
                                                    <div class="collapse mt-3" id="collapseExample">
                                                            <div class="card card-body">
                                                                <Form @submit="createRecordFunction" :validation-schema="schema" v-slot="{ errors }">
                                                                    <div class="row">
                                                                        <div class="mb-3 col-md-12">
                                                                            <label class="form-label" for="user_source">Tipo de Usuário:</label>
                                                                            <Field as="select" class="form-control" :class="{'is-invalid':errors.user_source}" id="user_source" name="user_source" aria-describedby="user_source" v-model="user_source">
                                                                                <option value="1" selected>Usuário do sistema</option>
                                                                                <option value="2" selected>Convidado</option>
                                                                            </Field>
                                                                            <span class="invalid-feedback">{{ errors.user_source }}</span>
                                                                        </div>
                                                                    </div>
                                                                    <div v-if="user_source == 1">
                                                                        <div class="row">
                                                                            <div class="mb-3 col-md-12">
                                                                                <label class="form-label" for="role_id">Nível:</label>
                                                                                <Field as="select" class="form-control" :class="{'is-invalid':errors.role_id}" id="role_id" name="role_id" aria-describedby="role_id" @change="getUser(role_id_to_user)" v-model="role_id_to_user">
                                                                                    <option value="" selected>Selecionar</option>
                                                                                    <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.name }}</option>
                                                                                </Field>
                                                                                <span class="invalid-feedback">{{ errors.role_id }}</span>
                                                                            </div>
                                                                    </div>

                                                                    <div class="row">
                                                                        <div class="mb-3 col-md-12">
                                                                                <label class="form-label" for="equipment_id">Usuário:</label>
                                                                                <!-- <Field as="select" class="form-control" :class="{'is-invalid':errors.equipment_id}"  name="equipment_id" id="equipment_id" aria-describedby="equipment_id">
                                                                                    <option value="" selected>Selecionar</option>
                                                                                    <option v-for="equipment in equipments" :key="equipment.id" :value="equipment.id">{{ equipment.name }} / {{ equipment.ref }}</option>
                                                                                </Field> -->
                                                                                <!-- <div class="mb-2">
                                                                                    <input type="button" class="btn btn-secondary" @click="selectEquipment" value="Selecionar tudo"/> <input type="button" class="btn btn-secondary" @click="deselectEquipment" value="Deselecionar tudo"/> 
                                                                                </div> -->
                                                                                <FieldArray class="form-control" name="participants">
                                                                                    <div class="mb-2" v-for="(participant,idx) in userparticipants" :key="participant.id">
                                                                                        <Field class="form-check-input" type="checkbox" :value="participant.id" :id="`participants[${idx}].participant_id`" :name="`participants[${idx}].participant_id`"/>
                                                                                        <span class="form-check-label">
                                                                                        {{ participant.firstName +' '+participant.lastName  }} / {{ participant.email }}
                                                                                        </span> 
                                                                                    </div>
                                                                                </FieldArray>
                                                                            
                                                                                <span class="invalid-feedback">{{ errors.participant_id }}</span>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div v-else>
                                                                        <div class="row">
                                                                            <div class="mb-3 col-md-12">
                                                                                <label class="form-label" for="email">Email</label>
                                                                                <Field type="text" class="form-control" :class="{'is-invalid':errors.email}" name="email" required id="email" placeholder="Email"/>
                                                                                <span class="invalid-feedback">{{ errors.email }}</span>
                                                                            </div>
                                                                        </div>
                                                                        <div class="row">
                                                                            <div class="mb-3 col-md-12">
                                                                                <label class="form-label" for="name">Nome</label>
                                                                                <Field type="text" class="form-control" :class="{'is-invalid':errors.name}" name="name" required id="name" placeholder="Nome"/>
                                                                                <span class="invalid-feedback">{{ errors.name }}</span>
                                                                            </div>
                                                                        </div>

                                                                    </div>
                                                                    <div class="row">
                                                                        <div class="mb-3 col-md-12">
                                                                            <label class="form-label" for="obs">Observação</label>
                                                                            <Field type="text-area" class="form-control" :class="{'is-invalid':errors.obs}" required name="obs" id="obs" placeholder="Observação"/>
                                                                            <span class="invalid-feedback">{{ errors.obs }}</span>
                                                                        </div>
                                                                    </div>
                                                                
                                                                    <Field type="hidden" name="meeting_id" v-model="retrievedData.id"></Field>

                                                                    <button type="submit" class="btn btn-primary" :disabled="loadingSubmit == true">
                                                                        <div v-if="loadingSubmit == true" class="spinner-border spinner-border-sm" role="status"></div>
                                                                        <span v-else>Adicionar Participante</span>
                                                                    </button>

                                                                </Form>
                                                            </div>
                                                    </div>

                                                    <div class="table-responsive">
                                                        <table class="table table-striped">
                                                            <thead>
                                                                <tr>
                                                                    <th>#</th>
                                                                    <th>Nome</th>
                                                                    <th>Email</th>
                                                                    <th>Nível</th>
                                                                    <th>Obs</th>
                                                                    <th>Email</th>
                                                                    <th>Consenso</th>
                                                                    <th>Ações</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody v-if="participants.length > 0">
                                                                <tr  v-for="(actualData,index) in participants" :key="actualData.id">
                                                                    <td>#{{ index + 1 }}</td>
                                                                    <td>{{ actualData.name}}</td>
                                                                    <td>{{ actualData.email}}</td>
                                                                    <td>{{ actualData.role == null ? 'Convidado' : actualData.role.name}}</td>
                                                                    <td>{{ actualData.obs}}</td>
                                                                    <td>
                                                                        <span v-if="actualData.email_status == 1" class="badge bg-success">
                                                                            Enviado
                                                                        </span>
                                                                        <span v-if="actualData.email_status == 0" class="badge bg-danger">
                                                                            Pendente
                                                                        </span>
                                                                    </td>
                                                                    <td>
                                                                        <span v-if="actualData.status == 1" class="badge bg-success">
                                                                            De acordo
                                                                        </span>
                                                                        <span v-if="actualData.status == 0" class="badge bg-danger">
                                                                            Discordância
                                                                        </span>
                                                                    </td>
                                                                    <td>
                                                                        <a href="#" @click.prevent="confirmEditParticipant(actualData)"><vue-feather type="edit-2"></vue-feather></a>
                                                                        <a href="#"><vue-feather type="mail"></vue-feather></a>
                                                                        <a href="#" @click.prevent="confirmDeletionParticipant(actualData)"><vue-feather type="trash"></vue-feather></a>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                            <tbody v-else>
                                                                <tr>
                                                                    <td colspan="7" align="center">Nenhum resultado encontrado</td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>

                                                    <hr>
                                                    <h5 class="card-title">Tarefas: {{ tasks.length }} registros encontrados.</h5>
                                                    <a class="btn btn-pill btn-primary mt-3" data-toggle="collapse" href="#collapseTask" role="button" aria-expanded="false" aria-controls="collapseTask">
                                                        <vue-feather type="plus"></vue-feather>Adicionar Tarefas
                                                    </a>
                                                    <div class="collapse mt-3" id="collapseTask">
                                                            <div class="card card-body">
                                                                <Form @submit="createRecordFunction2" :validation-schema="schema2" v-slot="{ errors }">
                                                                    
                                                                    <div>
                                                                        <div class="row">
                                                                            <div class="mb-3 col-md-12">
                                                                                <label class="form-label" for="participant_id">Participante:</label>
                                                                                <Field as="select" class="form-control" :class="{'is-invalid':errors.participant_id}" id="participant_id" name="participant_id" aria-describedby="participant_id">
                                                                                    <option value="" selected>Selecionar</option>
                                                                                    <option v-for="participant in participants" :key="participant.id" :value="participant.id">{{ participant.name }} / {{ participant.email }}</option>
                                                                                </Field>
                                                                                <span class="invalid-feedback">{{ errors.participant_id }}</span>
                                                                            </div>
                                                                        </div>
                                                                        <div class="row">
                                                                            <div class="mb-3 col-md-12">
                                                                                <label class="form-label" for="what">Tarefa</label>
                                                                                <Field type="text" class="form-control" :class="{'is-invalid':errors.what}" name="what" required id="what" placeholder="Tarefa"/>
                                                                                <span class="invalid-feedback">{{ errors.what }}</span>
                                                                            </div>
                                                                        </div>
                                                                        <div class="row">
                                                                            <div class="mb-3 col-md-12">
                                                                                <label class="form-label" for="date">Prazo</label>
                                                                                <Field type="date" class="form-control" :class="{'is-invalid':errors.date}" name="date" required id="date" placeholder="Prazo"/>
                                                                                <span class="invalid-feedback">{{ errors.date }}</span>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <Field type="hidden" name="meeting_id" v-model="retrievedData.id"></Field>

                                                                    <button type="submit" class="btn btn-primary" :disabled="loadingSubmit == true">
                                                                        <div v-if="loadingSubmit == true" class="spinner-border spinner-border-sm" role="status"></div>
                                                                        <span v-else>Adicionar Tarefa</span>
                                                                    </button>

                                                                </Form>
                                                            </div>
                                                    </div>

                                                    <div class="table-responsive">
                                                        <table class="table table-striped">
                                                            <thead>
                                                                <tr>
                                                                    <th>#</th>
                                                                    <th>Nome</th>
                                                                    <th>Email</th>
                                                                    <th>Tarefa</th>
                                                                    <th>Prazo</th>
                                                                    <th>Estado</th>
                                                                    
                                                                    <th>Ações</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody v-if="tasks.length > 0">
                                                                <tr  v-for="(actualData,index) in tasks" :key="actualData.id">
                                                                    <td>#{{ index + 1 }}</td>
                                                                    <td>{{ actualData.participant.name}}</td>
                                                                    <td>{{ actualData.participant.email}}</td>
                                                                    <td>{{ actualData.what}}</td>
                                                                    <td>{{ actualData.date}}</td>
                                                                    <td>
                                                                        <span v-if="actualData.status_id == 1" class="badge bg-success">
                                                                            {{actualData.status.name}}
                                                                        </span>
                                                                        <span v-if="actualData.status_id == 2" class="badge bg-danger">
                                                                            {{actualData.status.name}}
                                                                        </span>
                                                                        <span v-if="actualData.status_id == 4" class="badge bg-warning">
                                                                            {{actualData.status.name}}
                                                                        </span>
                                                                        <span v-if="actualData.status_id == 3" class="badge bg-info">
                                                                            {{actualData.status.name}}
                                                                        </span>
                                                                    </td>
                                                                    <td>
                                                                        <a href="#" @click.prevent="confirmEditTask(actualData)"><vue-feather type="edit-2"></vue-feather></a>
                                                                        <a href="#" @click.prevent="confirmCopyTask(actualData)"><vue-feather type="copy"></vue-feather></a>
                                                                        <a href="#" @click.prevent="confirmDeletionTask(actualData)"><vue-feather type="trash"></vue-feather></a>
                                                                        
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                            <tbody v-else>
                                                                <tr>
                                                                <td colspan="7" align="center">Nenhum resultado encontrado</td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
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

    <!-- Modal delete task -->
   <div class="modal" id="deleteModalParticipant" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
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
                <button @click.prevent="deleteDataParticipant" type="button" class="btn btn-danger" :disabled="loadingButtonDelete">
                    <div v-if="loadingButtonDelete" class="spinner-border spinner-border-sm" role="status"></div>
                    <span v-else>Apagar registro</span>
                    </button>
        </div>
      </div>
    </div>
  </div>

      <!-- Modal delete participant -->
      <div class="modal" id="deleteModalTask" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
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
                <button @click.prevent="deleteDataTask" type="button" class="btn btn-danger" :disabled="loadingButtonDelete">
                    <div v-if="loadingButtonDelete" class="spinner-border spinner-border-sm" role="status"></div>
                    <span v-else>Apagar registro</span>
                    </button>
        </div>
      </div>
    </div>
  </div>

      <!-- Modal edit task -->
      <div class="modal" id="editModalParticipant" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLongTitle">Editar Participante.</h5>
          
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <Form @submit="editDataParticipant" :validation-schema="schema3" v-slot="{ errors }">
            <div class="modal-body">
                
            <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="name">Nome</label>
														<span type="text" class="form-control" >{{ dataBeingEdited == null ? '' : dataBeingEdited.name}} </span>
													</div>
												</div>
                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="name">Email</label>
														<span type="text" class="form-control" >{{ dataBeingEdited == null ? '' : dataBeingEdited.email }} </span>
													</div>
												</div>
                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="obs">Obs</label>
														<Field type="text" class="form-control"  name="obs" id="obs" placeholder="Observações"/>
                                                        <Field type="hidden" class="form-control" v-model="meeting_participant_id_edit"  name="meeting_participant_id" id="meeting_participant_id" placeholder="Observações"/>
													</div>
												</div>
                                           
                                                
        </div>
        <div class="modal-footer">
          
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                <button type="submit" class="btn btn-info" :disabled="loadingButtonDelete">
                    <div v-if="loadingButtonDelete" class="spinner-border spinner-border-sm" role="status"></div>
                    <span v-else>Submeter</span>
                    </button>
        </div>
    </Form>
      </div>
    </div>
  </div>

        <!-- Modal edit task -->
        <div class="modal" id="editModalTask" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLongTitle">Editar Tarefa.</h5>
          
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <Form @submit="editDataTask" :validation-schema="schema4" v-slot="{ errors }">
            <div class="modal-body">
                
            <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="meeting_participant_id">Participante</label>
                                                        <Field as="select" class="form-control" :class="{'is-invalid':errors.meeting_participant_id}" v-model="meeting_participant_id_form_edit"   name="meeting_participant_id" id="meeting_participant_id" aria-describedby="meeting_participant_id">
                                                                    <option value="" selected>Selecionar</option>
                                                                    <option v-for="participant in participants" :key="participant.id" :value="participant.id">{{ participant.name }}</option>
                                                        </Field>													
                                                    </div>
												</div>
                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="name">Prazo</label>
														<Field type="date" class="form-control" v-model="date_form_edit" name="date" id="date" placeholder="Date"/>
													</div>
												</div>
                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="obs">Tarefa</label>
														<Field type="text" class="form-control" v-model="what_form_edit" name="what" id="what" placeholder="Oque"/>
													</div>
												</div>
                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="obs">Estado</label>
                                                        <Field as="select" class="form-control" :class="{'is-invalid':errors.status_id}" v-model="status_id_form_edit"   name="status_id" id="status_id" aria-describedby="status_id">
                                                                    <option value="" selected>Selecionar</option>
                                                                    <option v-for="status in statuses" :key="status.id" :value="status.id">{{ status.name }}</option>
                                                        </Field>														</div>
												</div>
                                           
                                                
        </div>
        <div class="modal-footer">
          
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                <button type="submit" class="btn btn-info" :disabled="loadingButtonDelete">
                    <div v-if="loadingButtonDelete" class="spinner-border spinner-border-sm" role="status"></div>
                    <span v-else>Submeter</span>
                    </button>
        </div>
    </Form>
      </div>
    </div>
  </div>

  <!-- Modal edit task -->
  <div class="modal" id="copyModalTask" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLongTitle">Copiar Tarefa.</h5>
          
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <Form @submit="copyDataTask" :validation-schema="schema5" v-slot="{ errors }">
            <div class="modal-body">
                
            <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="meeting_participant_id">Participante</label>
                                                        <Field as="select" readonly class="form-control" :class="{'is-invalid':errors.meeting_participant_id}" v-model="meeting_participant_id_form_edit"   name="meeting_participant_id" id="meeting_participant_id" aria-describedby="meeting_participant_id">
                                                                    <option disabled v-for="participant in participants" :key="participant.id" :value="participant.id">{{ participant.name }}</option>
                                                        </Field>													
                                                    </div>
												</div>
                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="name">Prazo</label>
														<Field type="date" readonly disabled class="form-control" v-model="date_form_edit" name="date" id="date" placeholder="Date"/>
                                                        <Field type="hidden" class="form-control" v-model="dataBeingEdited" name="task_id" id="date" placeholder="Date"/>
													</div>
												</div>
                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="obs">Tarefa</label>
														<Field type="text" readonly disabled class="form-control" v-model="what_form_edit" name="what" id="what" placeholder="Oque"/>
													</div>
												</div>
                                                <div class="row">
                                                                        <div class="mb-3 col-md-12">
                                                                                <label class="form-label" for="equipment_id">Usuário:</label>
                                                                                <FieldArray class="form-control" name="participants">
                                                                                    <div class="mb-2" v-for="(participant,idx) in participants" :key="participant.id">
                                                                                        <Field class="form-check-input" type="checkbox" :value="participant.id" :id="`participants[${idx}].participant_id`" :name="`participants[${idx}].participant_id`"/>
                                                                                        <span class="form-check-label">
                                                                                        {{ participant.name}} / {{ participant.email }}
                                                                                        </span> 
                                                                                    </div>
                                                                                </FieldArray>
                                                                            
                                                                                <span class="invalid-feedback">{{ errors.participant_id }}</span>
                                                                            </div>
                                                                        </div>
                                                
                                           
                                                
        </div>
        <div class="modal-footer">
          
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                <button type="submit" class="btn btn-info" :disabled="loadingButtonDelete">
                    <div v-if="loadingButtonDelete" class="spinner-border spinner-border-sm" role="status"></div>
                    <span v-else>Submeter</span>
                    </button>
        </div>
    </Form>
      </div>
    </div>
  </div>
</template>