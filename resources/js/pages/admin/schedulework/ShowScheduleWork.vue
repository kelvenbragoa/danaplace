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

let retrievedData =ref([]);
let loadingSubmit =ref([true]);
let loadingDiv =ref([true]);
let loadingButtonDeleteSchedule = ref(false);
const router = useRouter();
let self = this;
let searchQuery = ref(null)
let equipments = ref([]);
let typeequipments = ref([]);
let departments = ref([]);
let scheduleworkitens = ref([]);
let technicians = ref([]);
let type_equipment_id_to_equipment = ref(0);
let department_id_to_technician = ref(0);
let currentvalue = ref([]);
let dataIdBeingDeleted = ref(0);
const toastr = useToastr();



const schema = yup.object({
    type_equipment_id: yup.string().required(),

  });

const createRecordFunction = (values, actions) => {

 
currentvalue.value = {values};

loadingSubmit.value = true;

const arr = Array.from(values)

axios.post('/scheduleworkitem',values).then((response)=>{

// categories.value.unshift(response.data);
// $('#createCategory').modal('hide');

retrievedData.value = response.data.schedulework;
typeequipments.value = response.data.typeequipments;
departments.value = response.data.departments;
scheduleworkitens.value = response.data.scheduleworkitens;



actions.resetField('type_equipment_id');
actions.resetField('equipment_id');
actions.resetField('department_id');
actions.resetField('technician_id');
actions.resetField('start_time');
actions.resetField('end_time');

loadingSubmit.value = false;

toastr.success('Escala adicionada com sucesso');
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


const getData = (page=1) => {
  axios.get(`/schedulework/+${router.currentRoute.value.params.id}?page=${page}`,
      {
        params:{
          query: searchQuery.value
        }
      })
       .then((response)=>{
        loadingDiv.value=false;
        retrievedData.value = response.data.schedulework;
        typeequipments.value = response.data.typeequipments;
        departments.value = response.data.departments;
        scheduleworkitens.value = response.data.scheduleworkitens;

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
    router.push({ path: '/admin/schedulework' });
   })


}

const getTechnician = (department) => {

axios.get(`/auxiliar-create-schedule/${department}`)
   .then((response)=>{

    technicians.value = response.data.technicians;
   })
   .catch((error)=>{
    toastr.error(error);
    router.push({ path: '/admin/schedulework' });
   })


}

const confirmDeletionSchedule = (data) => {

dataIdBeingDeleted = data.id;

$('#deleteModal').modal('show');
// axios.post('/categories',values).then((response)=>{

//   categories.value.unshift(response.data);
//   $('#createCategory').modal('hide');
//   resetForm();
// })
};


const deleteDataSchedule = () =>{

loadingButtonDeleteSchedule.value= true;

axios.delete(`/scheduleworkitem/${dataIdBeingDeleted}`)
.then(()=>{
    scheduleworkitens.value.data = scheduleworkitens.value.data.filter(data=>data.id !== dataIdBeingDeleted); 
 $('#deleteModal').modal('hide');

 toastr.success('Registro apagada com sucesso');

}).catch(()=>{
 toastr.error('Erro ao apagar');
 loadingButtonDeleteSchedule.value= false;
 $('#deleteModal').modal('hide');
}).finally(()=>{
 loadingButtonDeleteSchedule.value= false;
});
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

        <h1 class="h3 mb-3">Escala de Trabalho</h1>
        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Escala de Trabalho: {{ retrievedData.name }}</h5>

                                        <router-link to="/admin/schedulework" class="btn btn-pill btn-primary mt-3"><vue-feather type="arrow-left"></vue-feather>Voltar</router-link> 

                                       
								    </div>
                                    
                                    <div class="card-body">
                                          
                                        <div class="row">
                                            <div class="col-xl-12 col-xxl-12 d-flex">
                                                <div class="w-100">
                                                    <p>Escala de Trabahlo: {{ retrievedData.name }}</p>
                                                    <p>Data: {{ moment(retrievedData.date).format('DD-MM-YYYY') }}</p>
                                                    <p>Responsável: {{ retrievedData.responsible }}</p>
                                                    <p>Observação: {{ retrievedData.obs }}</p>

                                                    <hr>

                                                    <h5 class="card-title">Horarios e Equipamentos: {{ scheduleworkitens.total }} registros encontrados.</h5>
                                                    <a class="btn btn-pill btn-primary mt-3" data-toggle="collapse" href="#collapseExample" role="button" aria-expanded="false" aria-controls="collapseExample">
                                                        <vue-feather type="plus"></vue-feather>Associar
                                                    </a>
                                                    <div class="collapse mt-3" id="collapseExample">
                                                            <div class="card card-body">
                                                                <Form @submit="createRecordFunction" :validation-schema="schema" v-slot="{ errors }">
                                                                    <div class="row">
                                                                        <div class="mb-3 col-md-12">
                                                                            <label class="form-label" for="type_equipment_id">Tipo de Equipamento/Ativos:</label>
                                                                            <Field as="select" class="form-control" :class="{'is-invalid':errors.type_equipment_id}" id="type_equipment_id" name="type_equipment_id" aria-describedby="type_equipment_id" @change="getEquipment(type_equipment_id_to_equipment)" v-model="type_equipment_id_to_equipment" required>
                                                                                <option value="" selected>Selecionar</option>
                                                                                <option v-for="type_equipment in typeequipments" :key="type_equipment.id" :value="type_equipment.id">{{ type_equipment.name }}</option>
                                                                            </Field>
                                                                            <span class="invalid-feedback">{{ errors.type_equipment_id }}</span>
                                                                        </div>
                                                                    </div>

                                                                    <div class="row">
                                                                        <div class="mb-3 col-md-12">
                                                                            <label class="form-label" for="equipment_id">Equipamentos/Ativos:</label>
                                                                            <Field as="select" class="form-control" :class="{'is-invalid':errors.equipment_id}" id="equipment_id" name="equipment_id" aria-describedby="equipment_id" required>
                                                                                <option value="" selected>Selecionar</option>
                                                                                <option v-for="equipment in equipments" :key="equipment.id" :value="equipment.id">{{ equipment.name }} / {{ equipment.ref }}</option>
                                                                            </Field>
                                                                            <span class="invalid-feedback">{{ errors.equipment_id }}</span>
                                                                        </div>
                                                                    </div>

                                                                    <div class="row">
                                                                        <div class="mb-3 col-md-12">
                                                                            <label class="form-label" for="department_id">Departamentos:</label>
                                                                            <Field as="select" class="form-control" :class="{'is-invalid':errors.department_id}" id="department_id" name="department_id" aria-describedby="department_id" @change="getTechnician(department_id_to_technician)" v-model="department_id_to_technician" required>
                                                                                <option value="" selected>Selecionar</option>
                                                                                <option v-for="department in departments" :key="department.id" :value="department.id">{{ department.name }}</option>
                                                                            </Field>
                                                                            <span class="invalid-feedback">{{ errors.department_id }}</span>
                                                                        </div>
                                                                    </div>

                                                                    <div class="row">
                                                                        <div class="mb-3 col-md-12">
                                                                            <label class="form-label" for="equipment_id">Tecnicos:</label>
                                                                            <Field as="select" class="form-control" :class="{'is-invalid':errors.technician_id}" id="technician_id" name="technician_id" aria-describedby="technician_id" required>
                                                                                <option value="" selected>Selecionar</option>
                                                                                <option v-for="technician in technicians" :key="technician.id" :value="technician.id">{{ technician.name }}</option>
                                                                            </Field>
                                                                           
                                                                            <span class="invalid-feedback">{{ errors.equipment_id }}</span>
                                                                        </div>
                                                                    </div>

                                                                    <div class="row">
                                                                        <div class="mb-3 col-md-12">
                                                                            <label class="form-label" for="start_time">Inicio</label>
                                                                            <Field type="time" class="form-control" :class="{'is-invalid':errors.start_time}" name="start_time" id="start_time" placeholder="Horas de inicio" required/>
                                                                            <span class="invalid-feedback">{{ errors.start_time }}</span>
                                                                        </div>
                                                                    </div>

                                                                    <div class="row">
                                                                        <div class="mb-3 col-md-12">
                                                                            <label class="form-label" for="end_time">Fim</label>
                                                                            <Field type="time" class="form-control" :class="{'is-invalid':errors.end_time}" name="end_time" id="end_time" placeholder="Horas de Fim" required/>
                                                                            <span class="invalid-feedback">{{ errors.end_time }}</span>
                                                                        </div>
                                                                    </div>


                                                                    

                                                                    <Field type="hidden" name="schedule_work_id" v-model="retrievedData.id"></Field>

                                                                    <button type="submit" class="btn btn-primary" :disabled="loadingSubmit == true">
                                                                        <div v-if="loadingSubmit == true" class="spinner-border spinner-border-sm" role="status"></div>
                                                                        <span v-else>Associar</span>
                                                                    </button>

                                                                </Form>
                                                            </div>
                                                    </div>
                                                    <hr>
                                                    <div class="table-responsive">
                                                        <table class="table table-striped">
                                                            <thead>
                                                                <tr>
                                                                    <th>#</th>
                                                                    <th>Técnico</th>
                                                                    <th>Equipamento</th>
                                                                    <th>Inicio</th>
                                                                    <th>Fim</th>
                                                                    <th>Ações</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody v-if="scheduleworkitens.data.length > 0">
                                                                <tr  v-for="(actualData,index) in scheduleworkitens.data" :key="actualData.id">
                                                                    <td>#{{ index + 1 }}</td>
                                                                    <td>{{ actualData.technician.name}}</td>
                                                                    <td>{{ actualData.equipment.name +' '+ actualData.equipment.ref}}</td>
                                                                    <td>{{ actualData.start_time}}</td>
                                                                    <td>{{ actualData.start_time}}</td>
                                                                    
                                                                    <td>
                                                                        <!-- <router-link :to="'/admin/schedulework/'+actualData.id+'/edit'"><vue-feather type="edit-2"></vue-feather></router-link>
                                                                        <router-link :to="'/admin/schedulework/'+actualData.id"><vue-feather type="eye"></vue-feather></router-link>  -->
                                                                        <a href="#" @click.prevent="confirmDeletionSchedule(actualData)"><vue-feather type="trash"></vue-feather></a>
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
                <button @click.prevent="deleteDataSchedule" type="button" class="btn btn-danger" :disabled="loadingButtonDeleteSchedule">
                    <div v-if="loadingButtonDeleteSchedule" class="spinner-border spinner-border-sm" role="status"></div>
                    <span v-else>Apagar registro</span>
                    </button>
        </div>
      </div>
    </div>
  </div>
</template>