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
let groups =ref([]);
let loadingSubmit =ref([true]);
let type_equipments =ref([]);
let shiftequipmentrequests =ref([]);
let loadingDiv =ref([true]);
const router = useRouter();
let self = this;
let searchQuery = ref(null)
let equipments = ref([]);
let currentvalue = ref([]);
const loadingButtonDelete = ref(false);
const toastr = useToastr();
let dataIdBeingDeleted = ref(null);
const schema = yup.object({
    
    name: yup.string().required(),
    
   
  });

  const schema2 = yup.object({
    
    obs: yup.string().required(),
    type_equipment_id: yup.string().required(),
    request_quantity: yup.string().required(),
    
   
  });





const getData = (page=1) => {
  axios.get(`/shifts/+${router.currentRoute.value.params.id}?page=${page}`,
      {
        params:{
          query: searchQuery.value
        }
      })
       .then((response)=>{
        loadingDiv.value=false;
        retrievedData.value = response.data.shift;
        groups.value = response.data.groups;
        type_equipments.value = response.data.type_equipments;
        shiftequipmentrequests.value = response.data.requests;
       }).catch(()=>{
        loadingDiv.value=false;
       })
}


watch(searchQuery,debounce(()=>{
    getData();
},300));

const createRecordGroupFunction = (values, actions) => {

 
currentvalue.value = {values};

loadingSubmit.value = true;

const arr = Array.from(values)

axios.post('/groupshift',values).then((response)=>{

// categories.value.unshift(response.data);
// $('#createCategory').modal('hide');

retrievedData.value = response.data.shift;
groups.value = response.data.groups;
type_equipments.value = response.data.type_equipments;



actions.resetField('name');
loadingSubmit.value = false;

toastr.success('Grupo associado com successo');
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


const createRecordRequestFunction = (values, actions) => {

 
currentvalue.value = {values};

loadingSubmit.value = true;

const arr = Array.from(values)

axios.post('/shiftequipmentrequest',values).then((response)=>{

// categories.value.unshift(response.data);
// $('#createCategory').modal('hide');

retrievedData.value = response.data.shift;
groups.value = response.data.groups;
type_equipments.value = response.data.type_equipments;
shiftequipmentrequests.value = response.data.requests;



actions.resetField('type_equipment_id');
actions.resetField('obs');
actions.resetField('request_quantity');
loadingSubmit.value = false;

toastr.success('Grupo associado com successo');
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

const confirmDeletionGroup = (data) => {

dataIdBeingDeleted = data.id;

$('#deleteModal').modal('show');

};

const deleteDataGroup = () =>{

loadingButtonDelete.value= true;

axios.delete(`/groupshift/${dataIdBeingDeleted}`)
.then((e)=>{
    groups.value = groups.value.filter(data=>data.id !== dataIdBeingDeleted); 
    
 $('#deleteModal').modal('hide');

 toastr.success('Registro apagada com sucesso');

}).catch((e)=>{
 toastr.error('Erro ao apagar');
 console.log(e)
 loadingButtonDelete.value= false;
 $('#deleteModal').modal('hide');
}).finally(()=>{
 loadingButtonDelete.value= false;
});
}

const confirmDeletionRequest = (data) => {

dataIdBeingDeleted = data.id;

$('#deleteRequestModal').modal('show');

};

const deleteDataRequest = () =>{

loadingButtonDelete.value= true;

axios.delete(`/shiftequipmentrequest/${dataIdBeingDeleted}`)
.then((e)=>{
    shiftequipmentrequests.value = shiftequipmentrequests.value.filter(data=>data.id !== dataIdBeingDeleted); 
    
 $('#deleteRequestModal').modal('hide');

 toastr.success('Registro apagada com sucesso');

}).catch((e)=>{
 toastr.error('Erro ao apagar');
 console.log(e)
 loadingButtonDelete.value= false;
 $('#deleteRequestModal').modal('hide');
}).finally(()=>{
 loadingButtonDelete.value= false;
});
}


onMounted(()=>{
  getData();
})
</script>

<template>
    <div v-if="!loadingDiv">

        <h1 class="h3 mb-3">Planeamento</h1>
        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Planeamento: {{ retrievedData.name }}</h5>

                                        <router-link to="/admin/shifts" class="btn btn-pill btn-primary mt-3"><vue-feather type="arrow-left"></vue-feather>Voltar</router-link> 

                                       
								    </div>
                                    
                                    <div class="card-body">
                                          
                                        <div class="row">
                                            <div class="col-xl-12 col-xxl-12 d-flex">
                                                <div class="w-100">
                                                    <p><strong>Planeamento</strong> : {{ retrievedData.name }}</p>
                                                    <p><strong>Data</strong>: {{ moment(retrievedData.date).format('DD-MM-YYYY') }}</p>
                                                    <p><strong>Criado por</strong>:{{ retrievedData.user.firstName }} {{ retrievedData.user.lastName }} </p>

                                                    <hr>

                                                    <h5 class="card-title">Grupos associados: {{groups.length}} registros encontrados.</h5>
                                                    <a class="btn btn-pill btn-primary mt-3" data-toggle="collapse" href="#collapseExample" role="button" aria-expanded="false" aria-controls="collapseExample">
                                                        <vue-feather type="plus"></vue-feather>Associar Grupos
                                                    </a>
                                                    <div class="collapse mt-3" id="collapseExample">
                                                                <div class="card card-body">
                                                                    <Form @submit="createRecordGroupFunction" :validation-schema="schema" v-slot="{ errors }">
                                                                        <div class="row">
                                                                            <div class="mb-3 col-md-12">
                                                                                <label class="form-label" for="name">Nome</label>
                                                                                <Field type="text" class="form-control" :class="{'is-invalid':errors.name}" name="name" id="name" placeholder="Nome"/>
                                                                                <span class="invalid-feedback">{{ errors.name }}</span>
                                                                            </div>
                                                                        </div>

                                                                        <Field type="hidden" name="shift_id" v-model="retrievedData.id"></Field>

                                                                        <button type="submit" class="btn btn-primary" :disabled="loadingSubmit == true">
                                                                            <div v-if="loadingSubmit == true" class="spinner-border spinner-border-sm" role="status"></div>
                                                                            <span v-else>Associar Grupo</span>
                                                                        </button>

                                                                    </Form>
                                                                </div>
                                                    </div>

                                                    <div class="table-responsive">
                                                        <table class="table table-striped">
                                                            <thead>
                                                                <tr>
                                                                    <th>#</th>
                                                                    <th>Grupo</th>
                                                                    <th>Planeamento</th>
                                                                    <th>Nrº Operadores</th>
                                                                    <th>Ações</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody v-if="groups.length > 0">
                                                                <tr  v-for="(actualData,index) in groups" :key="actualData.id">
                                                                    <td>#{{ index + 1 }}</td>
                                                                    <td>{{ actualData.name}}</td>
                                                                    <td>{{ retrievedData.name}}</td>
                                                                    <td>{{ actualData.groupshiftoperators.length}}</td>
                                                                    <td>
                                                                        <router-link :to="'/admin/shifts/groupshift/'+actualData.id"><vue-feather type="eye"></vue-feather></router-link> 
                                                                        <a href="#" @click.prevent="confirmDeletionGroup(actualData)"><vue-feather type="trash"></vue-feather></a>
                                                                        
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

                                                    <hr>

                                                    <h5 class="card-title">Requisições : {{shiftequipmentrequests.length}} registros encontrados.</h5>
                                                    <a class="btn btn-pill btn-primary mt-3" data-toggle="collapse" href="#collapseExample2" role="button" aria-expanded="false" aria-controls="collapseExample2">
                                                        <vue-feather type="plus"></vue-feather>Requisições
                                                    </a>
                                                    <div class="collapse mt-3" id="collapseExample2">
                                                                <div class="card card-body">
                                                                    <Form @submit="createRecordRequestFunction" :validation-schema="schema2" v-slot="{ errors }">
                                                                        <div class="row">
                                                                            <div class="mb-3 col-md-12">
                                                                                <label class="form-label" for="type_equipment_id">Tipo de Equipamento/Ativos:</label>
                                                                                <Field as="select" class="form-control" :class="{'is-invalid':errors.type_equipment_id}" id="type_equipment_id" name="type_equipment_id" aria-describedby="type_equipment_id">
                                                                                    <option value="" selected>Selecionar</option>
                                                                                    <option v-for="type_equipment in type_equipments" :key="type_equipment.id" :value="type_equipment.id">{{ type_equipment.name }}</option>
                                                                                </Field>
                                                                                <span class="invalid-feedback">{{ errors.type_equipment_id }}</span>
                                                                            </div>
                                                                        </div>

                                                                        <div class="row">
                                                                            <div class="mb-3 col-md-12">
                                                                                <label class="form-label" for="request_quantity">Nº Equipamentos</label>
                                                                                <Field type="text" class="form-control" :class="{'is-invalid':errors.request_quantity}" name="request_quantity" id="request_quantity" placeholder="Quantidade"/>
                                                                                <span class="invalid-feedback">{{ errors.request_quantity }}</span>
                                                                            </div>
                                                                        </div>

                                                                        <div class="row">
                                                                            <div class="mb-3 col-md-12">
                                                                                <label class="form-label" for="obs">Observação</label>
                                                                                <Field type="text" class="form-control" :class="{'is-invalid':errors.obs}" name="obs" id="obs" placeholder="Observação"/>
                                                                                <span class="invalid-feedback">{{ errors.obs }}</span>
                                                                            </div>
                                                                        </div>


                                                                        <Field type="hidden" name="shift_id" v-model="retrievedData.id"></Field>

                                                                        <button type="submit" class="btn btn-primary" :disabled="loadingSubmit == true">
                                                                            <div v-if="loadingSubmit == true" class="spinner-border spinner-border-sm" role="status"></div>
                                                                            <span v-else>Submeter</span>
                                                                        </button>

                                                                    </Form>
                                                                </div>
                                                    </div>

                                                    <div class="table-responsive">
                                                        <table class="table table-striped">
                                                            <thead>
                                                                <tr>
                                                                    <th>#</th>
                                                                    <th>ID</th>
                                                                    <th>Equipamento</th>
                                                                    <th>Quantidade</th>
                                                                    <th>Quantidade Respondida</th>
                                                                    <th>Percentagem</th>
                                                                    <th>Estado</th>
                                                                    <th>Ações</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody v-if="shiftequipmentrequests.length > 0">
                                                                <tr  v-for="(actualData,index) in shiftequipmentrequests" :key="actualData.id">
                                                                    <td>#{{ index + 1 }}</td>
                                                                    <td>{{ actualData.id}}</td>
                                                                    <td>{{ actualData.typeequipment.name}}</td>
                                                                    <td>{{ actualData.request_quantity}}</td>
                                                                    <td>{{ actualData.delivered_quantity}}</td>
                                                                    <td>
                                                                        <span v-if="actualData.request_quantity == 0">
                                                                            0%
                                                                        </span>
                                                                        <span v-else>
                                                                            {{(100*actualData.delivered_quantity)/ actualData.request_quantity }}%
                                                                        </span>
                                                                    </td>
                                                                    <td>
                                                                        <span v-if="actualData.status == 1" class="badge bg-success">
                                                                            Respondido
                                                                        </span>
                                                                        <span v-if="actualData.status == 0" class="badge bg-danger">
                                                                            Pendente
                                                                        </span>
                                                                    </td>
                                                                    <td>
                                                                        <router-link :to="'/admin/shifts/request/'+actualData.id"><vue-feather type="eye"></vue-feather></router-link> 
                                                                        <a v-if="actualData.status == 0" href="#" @click.prevent="confirmDeletionRequest(actualData)"><vue-feather type="trash"></vue-feather></a>
                                                                        
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
                        <button @click.prevent="deleteDataGroup" type="button" class="btn btn-danger" :disabled="loadingButtonDelete">
                            <div v-if="loadingButtonDelete" class="spinner-border spinner-border-sm" role="status"></div>
                            <span v-else>Apagar registro</span>
                            </button>
                </div>
            </div>
            </div>
        </div>

        <!-- Modal delete equipemtn -->
       <div class="modal" id="deleteRequestModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
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
                        <button @click.prevent="deleteDataRequest" type="button" class="btn btn-danger" :disabled="loadingButtonDelete">
                            <div v-if="loadingButtonDelete" class="spinner-border spinner-border-sm" role="status"></div>
                            <span v-else>Apagar registro</span>
                        </button>
                </div>
            </div>
            </div>
        </div>
</template>