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
const schema = yup.object({
    
    // name: yup.string().required(),
    
   
  });





const getData = (page=1) => {
  axios.get(`/groupshift/+${router.currentRoute.value.params.id}?page=${page}`,
      {
        params:{
          query: searchQuery.value
        }
      })
       .then((response)=>{
        loadingDiv.value=false;
        retrievedData.value = response.data.group;
        operators.value = response.data.operators;
        operatorsUser.value = response.data.operatorsUser;
        
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

axios.post('/groupshiftoperator',values).then((response)=>{

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

axios.delete(`/groupshiftoperator/${dataIdBeingDeleted}`)
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

        <h1 class="h3 mb-3">Grupo</h1>
        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Grupo: {{ retrievedData.name }}</h5>

                                        <router-link :to="'/admin/shifts/'+ retrievedData.shift_id " class="btn btn-pill btn-primary mt-3"><vue-feather type="arrow-left"></vue-feather>Voltar</router-link> 

                                       
								    </div>
                                    
                                    <div class="card-body">
                                          
                                        <div class="row">
                                            <div class="col-xl-12 col-xxl-12 d-flex">
                                                <div class="w-100">
                                                    <p><strong>Grupo</strong> : {{ retrievedData.name }}</p>
                                                    <p><strong>Nº Operadores</strong> : {{ retrievedData.groupshiftoperators.length }}</p>
                                                    <p><strong>Planeamento</strong> : {{ retrievedData.shift.name }}</p>
                                                    <p><strong>Data</strong>: {{ moment(retrievedData.shift.date).format('DD-MM-YYYY') }}</p>
                                                    <p><strong>Criado por</strong>:{{ retrievedData.shift.user.firstName }} {{ retrievedData.shift.user.lastName }} </p>

                                                    <hr>

                                                    <h5 class="card-title">Usuários associados: 0 registros encontrados.</h5>
                                                    <a class="btn btn-pill btn-primary mt-3" data-toggle="collapse" href="#collapseExample" role="button" aria-expanded="false" aria-controls="collapseExample">
                                                        <vue-feather type="plus"></vue-feather>Associar Usuários
                                                    </a>
                                                    <div class="collapse mt-3" id="collapseExample">
                                                                <div class="card card-body">
                                                                    <Form @submit="createRecordGroupShiftOperatorsFunction" :validation-schema="schema" v-slot="{ errors }">
                                                                        <div class="row">
                                                                        <div class="mb-3 col-md-12">
                                                                            <label class="form-label" for="equipment_id">Operadores:</label>
                                                                           
                                                                            <div class="mb-2">
                                                                                <input type="button" class="btn btn-secondary" @click="selectOperators" value="Selecionar tudo"/> <input type="button" class="btn btn-secondary" @click="deselectOperators" value="Deselecionar tudo"/> 
                                                                            </div>
                                                                            <FieldArray class="form-control" name="users">
                                                                                <div class="mb-2" v-for="(operator,idx) in operatorsUser" :key="operator.id">
                                                                                    <Field class="form-check-input" type="checkbox" :value="operator.id" :id="`users[${idx}].operator_id`" :name="`users[${idx}].operator_id`"/>
                                                                                    <span class="form-check-label">
                                                                                    {{ operator.firstName }} {{ operator.lastName }}
                                                                                    </span> 
                                                                                </div>
                                                                            </FieldArray>
                                                                           
                                                                            <span class="invalid-feedback">{{ errors.operator_id }}</span>
                                                                        </div>
                                                                    </div>

                                                                        <Field type="hidden" name="group_shift_id" v-model="retrievedData.id"></Field>
                                                                        <Field type="hidden" name="shift_id" v-model="retrievedData.shift_id"></Field>

                                                                        <button type="submit" class="btn btn-primary" :disabled="loadingSubmit == true">
                                                                            <div v-if="loadingSubmit == true" class="spinner-border spinner-border-sm" role="status"></div>
                                                                            <span v-else>Associar Operador</span>
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
                                                                    <th>Telefone</th>
                                                                    <th>Ações</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody v-if="operators.length > 0">
                                                                <tr  v-for="(actualData,index) in operators" :key="actualData.id">
                                                                    <td>#{{ index + 1 }}</td>
                                                                    <td>{{ actualData.user.firstName}} {{ actualData.user.lastName}}</td> 
                                                                    <td>{{ actualData.user.email}}</td>
                                                                    <td>{{ actualData.user.mobile}}</td>
                                                                    <td>
                                                                        <!-- <router-link :to="'/admin/groupshift/'+actualData.id"><vue-feather type="eye"></vue-feather></router-link>  -->
                                                                        <a href="#" @click.prevent="confirmDeletionGroup(actualData)"><vue-feather type="trash"></vue-feather></a>
                                                                        
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                            <tbody v-else>
                                                                <tr>
                                                                <td colspan="5" align="center">Nenhum resultado encontrado</td>
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