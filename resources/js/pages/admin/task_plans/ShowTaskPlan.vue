<script setup>

import axios from 'axios';
import { ref, onMounted, reactive, defineEmits, defineComponent,watch } from "vue";
import moment from 'moment'
import {useToastr} from '../../../toastr';
import {debounce} from 'lodash';
import {Form, Field, validate, FieldArray} from 'vee-validate';
import { useRouter} from "vue-router";
import * as yup from 'yup';
import VueFeather from 'vue-feather';
import { Bootstrap4Pagination } from 'laravel-vue-pagination';

let retrievedData =ref([]);
let loadingSubmit =ref([true]);
let loadingSubmitTask =ref([true]);
let loadingDiv =ref([true]);
const router = useRouter();
let self = this;
let criticals = ref([]);
let frequencies = ref([]);
let taskplantasks = ref([]);
let typetasks = ref([]);
let type_equipments =ref([]);
let destinations =ref([]);
let equipments =ref([]);
let taskplanequipments =ref([]);
let type_equipment_id_to_equipment = ref(0);

let currentvalue = ref([]);
let currentvaluetask = ref([]);
const toastr = useToastr();
const loadingButtonDeleteEquipment = ref(false);
const loadingButtonDeleteTask = ref(false);
const loadingButtonCopyTask = ref(false);
let dataIdBeingDeleted = ref(null);
let dataIdBeingCopied = ref(null);
let dataIdBeingDeletedTask = ref(null);



let destination_id_to_equipment = ref(0);

const schema = yup.object({
    // equipment_id: yup.string().required(),
    type_equipment_id: yup.string().required(),
    task_plan_id:yup.string().required(),
//     planequipments: yup.array().of(
//     yup.object().shape({
//         equipment_id: yup.string().required(),
//       })
//   )
  });

const schematask = yup.object({
    critical_id: yup.string().required(),
    frequency_id: yup.string().required(),
    do_every: yup.number().min(0).required(),
    name: yup.string().required(),
    type_task_id: yup.string().required(),
    task_plan_id:yup.string().required(),
    unavailable_equipment_time_days:yup.number().min(0).required(),
    unavailable_equipment_time_hours:yup.number().max(23).min(0).required(),
    unavailable_equipment_time_minutes:yup.number().max(59).min(0).required(),
    estimated_time_days:yup.number().min(0).required(),
    estimated_time_hours:yup.number().max(23).min(0).required(),
    estimated_time_minutes:yup.number().max(59).min(0).required(),
  });





const getData = () => {
  axios.get(`/taskplans/+${router.currentRoute.value.params.id}`)
       .then((response)=>{
        retrievedData.value = response.data.taskplan;
        type_equipments.value = response.data.type_equipments;
        taskplanequipments.value = response.data.taskplanequipments;
        criticals.value = response.data.criticals;
        frequencies.value = response.data.frequencies;
        typetasks.value = response.data.typetasks;
        taskplantasks.value = response.data.taskplantasks;
        destinations.value = response.data.destinations
        loadingDiv.value=false;




       }).catch(()=>{
        loadingDiv.value=false;
       })
}

const getEquipment = (typeequipment) => {

axios.get(`/auxiliar-create-mcscr/${typeequipment}`)
   .then((response)=>{

    equipments.value = response.data.equipments;
   })
   .catch((error)=>{
    toastr.error(error);
    router.push({ path: '/admin/taskplans' });
   })


}

const createRecordFunction = (values, actions) => {

 
currentvalue.value = {values};

loadingSubmit.value = true;

const arr = Array.from(values)

axios.post('/taskplanequipments',values).then((response)=>{

// categories.value.unshift(response.data);
// $('#createCategory').modal('hide');

retrievedData.value = response.data.taskplan;
type_equipments.value = response.data.type_equipments;
taskplanequipments.value = response.data.taskplanequipments;
criticals.value = response.data.criticals;
frequencies.value = response.data.frequencies;
typetasks.value = response.data.typetasks;
taskplantasks.value = response.data.taskplantasks;



actions.resetField('type_equipment_id');
actions.resetField('equipment_id');
loadingSubmit.value = false;

toastr.success('Equipamento/Ativo associado com successo');
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


const createRecordTaskFunction = (values, actions) => {

 
currentvaluetask.value = {values};

loadingSubmitTask.value = true;

const arr = Array.from(values)

axios.post('/taskplantasks',values).then((response)=>{

// categories.value.unshift(response.data);
// $('#createCategory').modal('hide');

retrievedData.value = response.data.taskplan;
type_equipments.value = response.data.type_equipments;
taskplanequipments.value = response.data.taskplanequipments;
criticals.value = response.data.criticals;
frequencies.value = response.data.frequencies;
typetasks.value = response.data.typetasks;
taskplantasks.value = response.data.taskplantasks;





actions.resetField('name');
actions.resetField('critical_id');
actions.resetField('type_task_id');
actions.resetField('frequency_id');
actions.resetField('do_every');
actions.resetField('estimated_time_hours');
actions.resetField('estimated_time_minutes');
actions.resetField('estimated_time_days');
actions.resetField('unavailable_equipment_time_days');
actions.resetField('unavailable_equipment_time_hours');
actions.resetField('unavailable_equipment_time_minutes');

loadingSubmitTask.value = false;

toastr.success('Atividade associado com successo');
}).catch((error)=>{

loadingSubmitTask.value = false;
toastr.error('Erro ao associar. '+error.response.data.message);
if(error.response.data.errors){
   
    actions.setErrors(error.response.data.errors);
}
}).finally(()=>{
loadingSubmitTask.value = false;

})

};

const confirmDeletionEquipment = (data) => {

dataIdBeingDeleted = data.id;

$('#deleteModal').modal('show');
// axios.post('/categories',values).then((response)=>{

//   categories.value.unshift(response.data);
//   $('#createCategory').modal('hide');
//   resetForm();
// })
};


const deleteDataEquipment = () =>{

loadingButtonDeleteEquipment.value= true;

axios.delete(`/taskplanequipments/${dataIdBeingDeleted}`)
.then(()=>{
    taskplanequipments.value.data = taskplanequipments.value.data.filter(data=>data.id !== dataIdBeingDeleted); 
 $('#deleteModal').modal('hide');

 toastr.success('Registro apagada com sucesso');

}).catch(()=>{
 toastr.error('Erro ao apagar');
 loadingButtonDeleteEquipment.value= false;
 $('#deleteModal').modal('hide');
}).finally(()=>{
 loadingButtonDeleteEquipment.value= false;
});
}

const confirmDeletionTask = (data) => {

dataIdBeingDeletedTask = data.id;

$('#deleteModalTask').modal('show');

};

const confirmCopyTask = (data) => {

    dataIdBeingCopied = data.id;

$('#copyModalTask').modal('show');

};

const getTypeEquipment = (destination_id_to_equipment) => {



axios.get(`/auxiliar-create-mcscr-type-equipment/${destination_id_to_equipment}`)
   .then((response)=>{

    type_equipments.value = response.data.type_equipments;
   })
   .catch((error)=>{
    toastr.error(error);
    router.push({ path: '/admin/mcscr' });
   })


}


const deleteDataTask = () =>{

loadingButtonDeleteTask.value= true;

axios.delete(`/taskplantasks/${dataIdBeingDeletedTask}`)
.then(()=>{
    taskplantasks.value.data = taskplantasks.value.data.filter(data=>data.id !== dataIdBeingDeletedTask); 
 $('#deleteModalTask').modal('hide');

 toastr.success('Registro apagada com sucesso');

}).catch(()=>{
 toastr.error('Erro ao apagar');
 loadingButtonDeleteTask.value= false;
 $('#deleteModalTask').modal('hide');
}).finally(()=>{
 loadingButtonDeleteTask.value= false;
});
}

const copyDataTask = () =>{

loadingButtonCopyTask.value= true;

axios.get(`/taskplantasks/${dataIdBeingCopied}/copy`)
.then((response)=>{
    
    taskplantasks.value = response.data.taskplantasks;
 $('#copyModalTask').modal('hide');

 toastr.success('Registro copiado com sucesso');

}).catch((e)=>{
    console.log(e)
 toastr.error('Erro ao copiar');
 loadingButtonCopyTask.value= false;
 $('#copyModalTask').modal('hide');
}).finally(()=>{
 loadingButtonCopyTask.value= false;
});
}



const selectEquipment = () =>{
    var ele = document.getElementsByName('equipments.equipment_id[]');
        console.log(ele.length); 
        for(var i=0; i < ele.length; i++){  
            if(ele[i].type=='checkbox')  
                ele[i].checked=true;  
        }  
}

const deselectEquipment = () =>{
    var ele = document.getElementsByName('equipment_id[]');
        console.log(ele.length); 
        for(var i=0; i < ele.length; i++){  
            if(ele[i].type=='checkbox')  
                ele[i].checked=false;  
        }  
}


onMounted(()=>{
  
  getData();
})
</script>

<template>
    <div v-if="!loadingDiv">

        <h1 class="h3 mb-3">Plano de atividade</h1>
        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Plano de atividade: {{ retrievedData.name }}</h5>

                                        <router-link to="/admin/taskplans" class="btn btn-pill btn-primary mt-3"><vue-feather type="arrow-left"></vue-feather>Voltar</router-link> 

                                       
								    </div>
                                    
                                    <div class="card-body">
                                          
                                        <div class="row">
                                            <div class="col-xl-12 col-xxl-12 d-flex">
                                                <div class="w-100">
                                                    <p>Nome Plano de atividade: {{ retrievedData.name }}</p>
                                                    <p>Equipamentos/Ativos associados: {{ taskplanequipments.total }}</p>
                                                    <p>Atividades associadas: {{ taskplantasks.total }}</p>

                                                    <hr>
                                                    <h5 class="card-title">Equipamentos/Ativos associados: {{ taskplanequipments.total }} registros encontrados.</h5>
                                                    <a class="btn btn-pill btn-primary mt-3" data-toggle="collapse" href="#collapseExample" role="button" aria-expanded="false" aria-controls="collapseExample">
                                                        <vue-feather type="plus"></vue-feather>Associar Equipamento/Ativo
                                                    </a>
                                                    <div class="collapse mt-3" id="collapseExample">
                                                            <div class="card card-body">
                                                                <Form @submit="createRecordFunction" :validation-schema="schema" v-slot="{ errors }">
                                                                    <div class="row">
                                                                        <div class="mb-3 col-md-12">
                                                                            <label class="form-label" for="destination_id">Clientes:</label>
                                                                            <Field as="select" class="form-control" :class="{'is-invalid':errors.destination_id}"  name="destination_id" id="destination_id" aria-describedby="destination_id" @change="getTypeEquipment(destination_id_to_equipment)" v-model="destination_id_to_equipment">
                                                                                <option value="" selected>Selecionar</option>
                                                                                <option v-for="destination in destinations" :key="destination.id" :value="destination.id">{{ destination.name }}</option>
                                                                            </Field>
                                                                            <span class="invalid-feedback">{{ errors.destination_id }}</span>
                                                                        </div>
                                                                    </div>

                                                                    <div class="row">
                                                                        <div class="mb-3 col-md-12">
                                                                            <label class="form-label" for="type_equipment_id">Tipo de Equipamento/Ativos:</label>
                                                                            <Field as="select" class="form-control" :class="{'is-invalid':errors.type_equipment_id}"  name="type_equipment_id" id="type_equipment_id" aria-describedby="type_equipment_id" @change="getEquipment(type_equipment_id_to_equipment)" v-model="type_equipment_id_to_equipment">
                                                                                <option value="" selected>Selecionar</option>
                                                                                <option v-for="(type_equipment,index) in type_equipments" :key="type_equipment.id" :value="type_equipment[0] ? type_equipment[0].type_equipment_id : 0">{{ index }}</option>
                                                                            </Field>
                                                                            <span class="invalid-feedback">{{ errors.type_equipment_id }}</span>
                                                                        </div>
                                                                    </div>
                                                                    <!-- <div class="row">
                                                                        <div class="mb-3 col-md-12">
                                                                            <label class="form-label" for="type_equipment_id">Tipo de Equipamento/Ativos:</label>
                                                                            <Field as="select" class="form-control" :class="{'is-invalid':errors.type_equipment_id}" id="type_equipment_id" name="type_equipment_id" aria-describedby="type_equipment_id" @change="getEquipment(type_equipment_id_to_equipment)" v-model="type_equipment_id_to_equipment">
                                                                                <option value="" selected>Selecionar</option>
                                                                                <option v-for="type_equipment in type_equipments" :key="type_equipment.id" :value="type_equipment.id">{{ type_equipment.name }}</option>
                                                                            </Field>
                                                                            <span class="invalid-feedback">{{ errors.type_equipment_id }}</span>
                                                                        </div>
                                                                    </div> -->

                                                                    <div class="row">
                                                                        <div class="mb-3 col-md-12">
                                                                            <label class="form-label" for="equipment_id">Equipamentos/Ativos:</label>
                                                                            <!-- <Field as="select" class="form-control" :class="{'is-invalid':errors.equipment_id}"  name="equipment_id" id="equipment_id" aria-describedby="equipment_id">
                                                                                <option value="" selected>Selecionar</option>
                                                                                <option v-for="equipment in equipments" :key="equipment.id" :value="equipment.id">{{ equipment.name }} / {{ equipment.ref }}</option>
                                                                            </Field> -->
                                                                            <div class="mb-2">
                                                                                <input type="button" class="btn btn-secondary" @click="selectEquipment" value="Selecionar tudo"/> <input type="button" class="btn btn-secondary" @click="deselectEquipment" value="Deselecionar tudo"/> 
                                                                            </div>
                                                                            <FieldArray class="form-control" name="planequipments">
                                                                                <div class="mb-2" v-for="(equipment,idx) in equipments" :key="equipment.id">
                                                                                    <Field class="form-check-input" type="checkbox" :value="equipment.id" :id="`planequipments[${idx}].equipment_id`" :name="`planequipments[${idx}].equipment_id`"/>
                                                                                    <span class="form-check-label">
                                                                                    {{ equipment.name }} / {{ equipment.ref }}
                                                                                    </span> 
                                                                                </div>
                                                                            </FieldArray>
                                                                           
                                                                            <span class="invalid-feedback">{{ errors.equipment_id }}</span>
                                                                        </div>
                                                                    </div>


                                                                    

                                                                    <Field type="hidden" name="task_plan_id" v-model="retrievedData.id"></Field>

                                                                    <button type="submit" class="btn btn-primary" :disabled="loadingSubmit == true">
                                                                        <div v-if="loadingSubmit == true" class="spinner-border spinner-border-sm" role="status"></div>
                                                                        <span v-else>Adicionar Equipamento/Ativo</span>
                                                                    </button>

                                                                </Form>
                                                            </div>
                                                    </div>

                                                    <div class="table-responsive">
                                                        <table class="table table-striped">
                                                            <thead>
                                                                <tr>
                                                                    <th>#</th>
                                                                    <th>Equipamento</th>
                                                                    <th>Referência</th>
                                                                    <th>Ações</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody v-if="taskplanequipments.data.length > 0">
                                                                <tr  v-for="(actualData,index) in taskplanequipments.data" :key="actualData.id">
                                                                    <td>#{{ index + 1 }}</td>
                                                                    <td>{{ actualData.equipment.name}}</td>
                                                                    <td>{{ actualData.equipment.ref}}</td>
                                                                    <td>
                                                                       
                                                                        <a href="#" @click.prevent="confirmDeletionEquipment(actualData)"><vue-feather type="trash"></vue-feather></a>
                                                                        
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                            <tbody v-else>
                                                                <tr>
                                                                <td colspan="4" align="center">Nenhum resultado encontrado</td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    <Bootstrap4Pagination :data="taskplanequipments" @pagination-change-page="getData"/>



                                                    <hr>
                                                    <h5 class="card-title">Atividades associados: {{ taskplantasks.total }} registros encontrados.</h5>
                                                    <a class="btn btn-pill btn-primary mt-3" data-toggle="collapse" href="#collapseTask" role="button" aria-expanded="false" aria-controls="collapseTask">
                                                        <vue-feather type="plus"></vue-feather>Adicionar tipo de Manutenção Preventiva
                                                    </a>
                                                    <div class="collapse mt-3" id="collapseTask">
                                                            <div class="card card-body">
                                                                <Form @submit="createRecordTaskFunction" :validation-schema="schematask" v-slot="{ errors }">

                                                                    <div class="row">
                                                                        <div class="mb-3 col-md-12">
                                                                            <label class="form-label" for="name">Nome da atividade</label>
                                                                            <Field type="text" class="form-control" :class="{'is-invalid':errors.name}" name="name" id="name" placeholder="Nome do plano"/>
                                                                            <span class="invalid-feedback">{{ errors.name }}</span>
                                                                        </div>
                                                                    </div>

                                                                    <div class="row">
                                                                        <div class="mb-3 col-md-12">
                                                                           <label class="form-label" for="type_task_id">Tipo de atividade</label>
                                                                            <Field as="select" class="form-control" :class="{'is-invalid':errors.type_task_id}"  name="type_task_id" id="type_task_id" aria-describedby="type_task_id">
                                                                                <option value="" disabled>Selecionar</option>
                                                                                <option v-for="typetask in typetasks" :key="typetask.id" :value="typetask.id">{{ typetask.name }}</option>
                                                                            </Field>
                                                                            <span class="invalid-feedback">{{ errors.type_task_id }}</span>
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    <div class="row">
                                                                        <div class="mb-3 col-md-12">
                                                                           <label class="form-label" for="critical_id">Criticidade</label>
                                                                            <Field as="select" class="form-control" :class="{'is-invalid':errors.critical_id}"  name="critical_id" id="critical_id" aria-describedby="critical_id">
                                                                                <option value="" disabled>Selecionar</option>
                                                                                <option v-for="critical in criticals" :key="critical.id" :value="critical.id">{{ critical.name }}</option>
                                                                            </Field>
                                                                            <span class="invalid-feedback">{{ errors.critical_id }}</span>
                                                                        </div>
                                                                    </div>

                                                                    <div class="row">
                                                                        <div class="mb-3 col-md-12">
                                                                           <label class="form-label" for="frequency_id">Frequencia de repetição</label>
                                                                            <Field as="select" class="form-control" :class="{'is-invalid':errors.frequency_id}"  name="frequency_id" id="frequency_id" aria-describedby="frequency_id">
                                                                                <option value="" disabled>Selecionar</option>
                                                                                <option v-for="frequency in frequencies" :key="frequency.id" :value="frequency.id">{{ frequency.name }}</option>
                                                                            </Field>
                                                                            <span class="invalid-feedback">{{ errors.frequency_id }}</span>
                                                                        </div>
                                                                    </div>

                                                                    <div class="row">
                                                                        <div class="mb-3 col-md-12">
                                                                            <label class="form-label" for="do_every">Fazer a cada</label>
                                                                            <Field type="number" class="form-control" :class="{'is-invalid':errors.do_every}" name="do_every" id="do_every" placeholder="Fazer a cada"/>
                                                                            <span class="invalid-feedback">{{ errors.do_every }}</span>
                                                                        </div>
                                                                    </div>

                                                                    

                                                                    <div class="row">
                                                                        <label class="form-label">Tempo estimado para a atividade</label>
                                                                        <div class="mb-3 col-md-1">
                                                                            <label class="form-label" for="estimated_time_days">Dias</label>
                                                                            <Field type="number" class="form-control" :class="{'is-invalid':errors.estimated_time_days}" name="estimated_time_days" id="estimated_time_days" placeholder="Dias"/>
                                                                            <span class="invalid-feedback">{{ errors.estimated_time_days }}</span>
                                                                        </div>

                                                                        <div class="mb-3 col-md-1">
                                                                            <label class="form-label" for="estimated_time_hours">Horas</label>
                                                                            <Field type="number" class="form-control" :class="{'is-invalid':errors.estimated_time_hours}" name="estimated_time_hours" id="estimated_time_hours" placeholder="Horas"/>
                                                                            <span class="invalid-feedback">{{ errors.estimated_time_hours }}</span>
                                                                        </div>
                                                                        
                                                                        <div class="mb-3 col-md-1">
                                                                            <label class="form-label" for="estimated_time_minutes">Minutos</label>
                                                                            <Field type="number"  class="form-control" :class="{'is-invalid':errors.estimated_time_minutes}" name="estimated_time_minutes" id="estimated_time_minutes" placeholder="Minutos"/>
                                                                            <span class="invalid-feedback">{{ errors.estimated_time_minutes }}</span>
                                                                        </div>
                                                                    </div>

                                                                    <div class="row">
                                                                        <label class="form-label">Tempo estimado que o equipamento/ativo ficará indisponível</label>
                                                                        <div class="mb-3 col-md-1">
                                                                            <label class="form-label" for="unavailable_equipment_time_days">Dias</label>
                                                                            <Field type="number" class="form-control" :class="{'is-invalid':errors.unavailable_equipment_time_days}" name="unavailable_equipment_time_days" id="unavailable_equipment_time_days" placeholder="Dias"/>
                                                                            <span class="invalid-feedback">{{ errors.unavailable_equipment_time_days }}</span>
                                                                        </div>

                                                                        <div class="mb-3 col-md-1">
                                                                            <label class="form-label" for="unavailable_equipment_time_hours">Horas</label>
                                                                            <Field type="number" class="form-control" :class="{'is-invalid':errors.unavailable_equipment_time_hours}" name="unavailable_equipment_time_hours" id="unavailable_equipment_time_hours" placeholder="Horas"/>
                                                                            <span class="invalid-feedback">{{ errors.unavailable_equipment_time_hours }}</span>
                                                                        </div>

                                                                        <div class="mb-3 col-md-1">
                                                                            <label class="form-label" for="unavailable_equipment_time_minutes">Minutos</label>
                                                                            <Field type="number" class="form-control" :class="{'is-invalid':errors.unavailable_equipment_time_minutes}" name="unavailable_equipment_time_minutes" id="unavailable_equipment_time_minutes" placeholder="Minutos"/>
                                                                            <span class="invalid-feedback">{{ errors.unavailable_equipment_time_minutes }}</span>
                                                                        </div>
                                                                    </div>

                                                                   

                                                                    <Field type="hidden" name="task_plan_id" v-model="retrievedData.id"></Field>

                                                                    <button type="submit" class="btn btn-primary" :disabled="loadingSubmitTask == true">
                                                                        <div v-if="loadingSubmitTask == true" class="spinner-border spinner-border-sm" role="status"></div>
                                                                        <span v-else>Associar atividade</span>
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
                                                                    <th>Tipo de atividade</th>
                                                                    <th>Criticidade</th>
                                                                    <th>Fazer a cada</th>
                                                                    <th>Frequência</th>
                                                                    <th>Tempo estimado</th>
                                                                    <th>Tempo que o equipamento estará indisponível</th>
                                                                    <th>Ações</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody v-if="taskplantasks.data.length > 0">
                                                                <tr  v-for="(actualData,index) in taskplantasks.data" :key="actualData.id">
                                                                    <td>#{{ index + 1 }}</td>
                                                                    <td>{{ actualData.name}}</td>
                                                                    <td>{{ actualData.typetask.name}}</td>
                                                                    <td>{{ actualData.critical.name}}</td>
                                                                    <td>{{ actualData.do_every}}</td>
                                                                    <td>{{ actualData.frequency.name}}</td>
                                                                    <td>{{ actualData.estimated_time_days}} Dias : {{ actualData.estimated_time_hours}} Horas : {{ actualData.estimated_time_minutes}} Minutos</td>
                                                                    <td>{{ actualData.unavailable_equipment_time_days}} Dias : {{ actualData.unavailable_equipment_time_hours}} Horas : {{ actualData.unavailable_equipment_time_minutes}} Minutos</td>
                                                                    <!-- <td>{{ moment(actualData.estimated_time,"HH:mm:ss").format('H')}} Horas e {{moment(actualData.estimated_time,"HH:mm:ss").format('mm')}} minutos</td>
                                                                    <td>{{ moment(actualData.unavailable_equipment_time,"HH:mm:ss").format('H')}} Horas e {{moment(actualData.unavailable_equipment_time,"HH:mm:ss").format('mm')}} minutos</td> -->

                                                                    
                                                                    <td>
                                                                        <router-link :to="'/admin/taskplans/tasks/'+actualData.id+'/edit'"><vue-feather type="edit-2"></vue-feather></router-link>
                                                                        <router-link :to="'/admin/taskplans/tasks/'+actualData.id"><vue-feather type="eye"></vue-feather></router-link> 
                                                                        <a href="#" @click.prevent="confirmCopyTask(actualData)"><vue-feather type="copy"></vue-feather></a>
                                                                        <a href="#" @click.prevent="confirmDeletionTask(actualData)"><vue-feather type="trash"></vue-feather></a>
                                                                        
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
                                                    <Bootstrap4Pagination :data="taskplantasks" @pagination-change-page="getData"/>



                                                   
                                            
                                                    <!-- <div class="row">
                                                        <div class="col-sm-3">
                                                            <div class="card">
                                                                <div class="card-body">
                                                                    <h5 class="card-title mb-4">Centro de custo</h5>
                                                                    <h1 class="mt-1 mb-3">{{retrievedData.name}}</h1>
                                                                    <div class="mb-1">
                                                                        <span class="text-muted">Código: {{retrievedData.code}}</span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-sm-3">
                                                            <div class="card">
                                                                <div class="card-body">
                                                                    <h5 class="card-title mb-4">Contas Associadas</h5>
                                                                    <h1 class="mt-1 mb-3">{{retrievedData.accounts.length}}</h1>
                                                                    <div class="mb-1">
                                                                        <span class="text-success"> <i class="mdi mdi-arrow-bottom-right"></i> </span>
                                                                        <span class="text-muted"></span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-sm-3">
                                                            <div class="card">
                                                                <div class="card-body">
                                                                    <h5 class="card-title mb-4">Equipamentos/Activos</h5>
                                                                    <h1 class="mt-1 mb-3">0</h1>
                                                                    <div class="mb-1">
                                                                        <span class="text-success"> <i class="mdi mdi-arrow-bottom-right"></i> </span>
                                                                        <span class="text-muted"></span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                    </div> -->
                                                        
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


    <!-- Modal delete equipemtn -->
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
                <button @click.prevent="deleteDataEquipment" type="button" class="btn btn-danger" :disabled="loadingButtonDeleteEquipment">
                    <div v-if="loadingButtonDeleteEquipment" class="spinner-border spinner-border-sm" role="status"></div>
                    <span v-else>Apagar registro</span>
                    </button>
        </div>
      </div>
    </div>
  </div>

   <!-- Modal delete task -->
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
                <button @click.prevent="deleteDataTask" type="button" class="btn btn-danger" :disabled="loadingButtonDeleteTask">
                    <div v-if="loadingButtonDeleteTask" class="spinner-border spinner-border-sm" role="status"></div>
                    <span v-else>Apagar registro</span>
                    </button>
        </div>
      </div>
    </div>
  </div>

   <!-- Modal delete task -->
   <div class="modal" id="copyModalTask" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLongTitle">Deseja mesmo copiar este plano.</h5>
          
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
            Ao confirmar será copiado todos os registros desta atividade as atividades.
        </div>
        <div class="modal-footer">

           
          
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                <button @click.prevent="copyDataTask" type="button" class="btn btn-info" :disabled="loadingButtonCopyTask">
                    <div v-if="loadingButtonCopyTask" class="spinner-border spinner-border-sm" role="status"></div>
                    <span v-else>Copiar registro</span>
                    </button>
        </div>
      </div>
    </div>
  </div>


  
</template>