<script setup>

import axios from 'axios';
import { ref, onMounted, reactive, defineEmits, defineComponent,watch } from "vue";
import moment from 'moment'
import {useToastr} from '../../../../toastr';
import {debounce} from 'lodash';
import {Form, Field, FieldArray} from 'vee-validate';
import { useRouter} from "vue-router";
import * as yup from 'yup';
import VueFeather from 'vue-feather';
import { Bootstrap4Pagination } from 'laravel-vue-pagination';

let retrievedData =ref([]);
let groups =ref([]);
let loadingSubmit =ref([true]);
let loadingDiv =ref([true]);
const router = useRouter();
let self = this;
let searchQuery = ref(null)
let operatorsUser = ref([]);
let operators = ref([]);
let currentvalue = ref([]);
const loadingButtonDelete = ref(false);
const toastr = useToastr();
let dataIdBeingDeleted = ref(null);
let requestitens = ref([])
const schema = yup.object({
    
    // name: yup.string().required(),
    
   
  });





const getData = (page=1) => {
  axios.get(`/shiftequipmentrequest/+${router.currentRoute.value.params.id}?page=${page}`,
      {
        params:{
          query: searchQuery.value
        }
      })
       .then((response)=>{
        loadingDiv.value=false;
        retrievedData.value = response.data.request;
        requestitens.value = response.data.requestitens;
        
       }).catch(()=>{
        loadingDiv.value=false;
       })
}


watch(searchQuery,debounce(()=>{
    getData();
},300));

const createRecordGroupShiftOperatorsFunction = (values, actions) => {

 
currentvalue.value = {values};

loadingSubmit.value = true;

const arr = Array.from(values)

axios.post('/shiftequipmentrequest',values).then((response)=>{

// categories.value.unshift(response.data);
// $('#createCategory').modal('hide');

retrievedData.value = response.data.group;
operators.value = response.data.operators;
operatorsUser.value = response.data.operatorsUser;


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

axios.delete(`/shiftequipmentrequest/${dataIdBeingDeleted}`)
.then((e)=>{
    operators.value = operators.value.filter(data=>data.id !== dataIdBeingDeleted); 
    
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

const selectOperators = () =>{
    var ele = document.getElementsByName('equipments.equipment_id[]');
        console.log(ele.length); 
        for(var i=0; i < ele.length; i++){  
            if(ele[i].type=='checkbox')  
                ele[i].checked=true;  
        }  
}

const deselectOperators = () =>{
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

<h1 class="h3 mb-3">Requisições Equipamento</h1>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Requisições Equipamento:</h5>

                                <router-link :to="'/admin/shifts/'+ retrievedData.shift_id " class="btn btn-pill btn-primary mt-3"><vue-feather type="arrow-left"></vue-feather>Voltar</router-link> 

                               
                            </div>
                            
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-xl-12 col-xxl-12 d-flex">
                                        <div class="w-100">
                                            <h5 class="card-title">Informações da Requisição do Equipamento</h5>
                                            <p><strong>ID:</strong> {{ retrievedData.id }}</p>
                                            <p><strong>Criado por:</strong> {{ retrievedData.createdbyuser.firstName +' ' +retrievedData.createdbyuser.lastName }}</p>
                                            <p><strong>Respondido por:</strong> {{ retrievedData.answeredbyuser == null ? '-----' : retrievedData.answeredbyuser.firstName+' '+retrievedData.answeredbyuser.lastName+'('+moment(retrievedData.delivereddate).format('DD-MM-YYYY H:mm')+')' }}</p>
                                            <p><strong>Data Criação:</strong> {{moment(retrievedData.created_at).format('DD-MM-YYYY H:mm') }}</p>
                                            <p><strong>Estado:</strong>
                                                    <span v-if="retrievedData.status == 0" class="badge bg-danger">
                                                        Pendente
                                                    </span>
                                                    <span v-if="retrievedData.status == 1" class="badge bg-success">
                                                        Respondido
                                                    </span>
                                                  
                                            </p>

                                            <hr>

                                            <h5 class="card-title">Frota Requisitada</h5>
                                            <div class="table-responsive">
                                                <table class="table table-striped">
                                                    <thead>
                                                        <tr>
                                                            <th>#</th>
                                                            <th>Frota</th>
                                                            <th>Equipamento</th>
                                                            <th>Operador</th>
                                                            <th>Moves</th>
                                                            <th>Toneladas</th>
                                                            <th>Combústivel</th>
                                                            <th>Acidente</th>
                                                            <th>Hodometro</th>
                                                            <th>Obs</th>
                                                            <th>Ações</th>
                                                           
                                                        </tr>
                                                    </thead>
                                                    <tbody v-if="requestitens.length > 0">
                                                        <tr  v-for="(actualData,index) in requestitens" :key="actualData.id">
                                                            <td>#{{ index + 1 }}</td>
                                                            <td>{{ retrievedData.typeequipment.name}}</td>
                                                            <td>{{ actualData.equipment.ref }}</td>
                                                            <td>{{ actualData.useroperator == null ? '-----' : actualData.useroperator.firstName+' '+actualData.useroperator.lastName }}</td>
                                                            <td>{{ actualData.moves }}</td>
                                                            <td>{{ actualData.ton }}</td>
                                                            <td>{{ actualData.petrol }}</td>
                                                            <td>{{ actualData.accident }}</td>
                                                            <td>{{ actualData.distance }}</td>
                                                            <td>{{ actualData.obs }}</td>
                                                            <td>
                                                                <router-link :to="'/admin/shifts/requestitem/'+actualData.id+'/edit'"><vue-feather type="edit-2"></vue-feather></router-link>
                                                                
                                                                <!-- <a href="#" @click.prevent="confirmDeletion(actualData)"><vue-feather type="trash"></vue-feather></a> -->
                                                            </td>
                                                            
                                                        </tr>
                                                    </tbody>
                                                    <tbody v-else>
                                                        <tr>
                                                        <td colspan="8" align="center">Nenhum resultado encontrado</td>
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
</template>