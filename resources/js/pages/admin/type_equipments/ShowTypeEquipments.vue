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
let criticals = ref([]);
let loadingSubmit =ref([true]);
let loadingReconciliation = ref([false]);
let loadingDiv =ref([true]);
const router = useRouter();
let components =ref([]);
const searchQuery = ref(null);
let currentvalue = ref([]);
const loadingButtonDelete = ref(false);
let self = this;
let dataIdBeingDeleted = ref(null);

let dataIdBeingCopied = ref(null);
const loadingButtonCopyTask = ref(false);

const toastr = useToastr();


const schema = yup.object({
    
    name: yup.string().required(),
    model: yup.string().required(),
    make: yup.string().required(),
    criticaly_id: yup.string().required(),
    type_equipment_id: yup.string().required(),
    percentage_weigth: yup.string().required(),
    
   
  });

const createRecordFunction = (values, actions) => {

 
currentvalue.value = {values};

loadingSubmit.value = true;

const arr = Array.from(values)

axios.post('/typeequipmentcomponent',values).then((response)=>{

// categories.value.unshift(response.data);
// $('#createCategory').modal('hide');
retrievedData.value = response.data.type_equipment;
components.value = response.data.components;
criticals.value = response.data.criticals;

actions.resetField('name');
actions.resetField('model');
actions.resetField('make');
actions.resetField('criticaly_id');
actions.resetField('percentage_weigth');

toastr.success('Componente criado com sucesso');
}).catch((error)=>{

loadingSubmit.value = false;
toastr.error('Erro ao adicionar. '+error.response.data.message);
if(error.response.data.errors){
   
    actions.setErrors(error.response.data.errors);
}
}).finally(()=>{
loadingSubmit.value = false;

})



};

watch(searchQuery,debounce(()=>{
    getData();
},300));


const confirmDeletion = (data) => {

console.log(data)

dataIdBeingDeleted = data.id;

$('#deleteModal').modal('show');
// axios.post('/categories',values).then((response)=>{

//   categories.value.unshift(response.data);
//   $('#createCategory').modal('hide');
//   resetForm();
// })
};




const deleteData = () =>{

loadingButtonDelete.value= true;

axios.delete(`/typeequipmentcomponent/${dataIdBeingDeleted}`)
.then(()=>{
    components.value.data = components.value.data.filter(data=>data.id !== dataIdBeingDeleted); 
 $('#deleteModal').modal('hide');

 toastr.success('Registro apagada com sucesso');

}).catch(()=>{
 toastr.error('Erro ao apagar');
 loadingButtonDelete.value= false;
 $('#deleteModal').modal('hide');
}).finally(()=>{
 loadingButtonDelete.value= false;
});
}




const getData = (page=1) => {
  axios.get(`/type_equipments/+${router.currentRoute.value.params.id}?page=${page}`)
       .then((response)=>{
        loadingDiv.value=false;
        retrievedData.value = response.data.type_equipment;
        criticals.value = response.data.criticals;
        components.value = response.data.components;
       }).catch(()=>{
        loadingDiv.value=false;
       })
}

const confirmCopyTask = (data) => {

dataIdBeingCopied = data.id;
$('#copyModalTask').modal('show');
};

const copyDataTask = () =>{

loadingButtonCopyTask.value= true;

axios.get(`/type_equipments_component/${dataIdBeingCopied}/copy`)
.then((response)=>{
    
    // retriviedData.value = response.data;
 $('#copyModalTask').modal('hide');
 getData();
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


onMounted(()=>{
  
  getData();
})
</script>

<template>
    <div v-if="!loadingDiv">

        <h1 class="h3 mb-3">Tipo de equipamentos</h1>
        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Tipo de equipamentos: {{ retrievedData.name }}</h5>

                                        <router-link to="/admin/type_equipments" class="btn btn-pill btn-primary mt-3"><vue-feather type="arrow-left"></vue-feather>Voltar</router-link> 
                                        <router-link :to="'/admin/fleet/'+retrievedData.id" class="btn btn-pill btn-primary mt-3"><vue-feather type="truck"> </vue-feather>Frota</router-link> 

                                       
								    </div>
                                    
                                    <div class="card-body">
                                          
                                        <div class="row">
                                            <div class="col-xl-12 col-xxl-12 d-flex">
                                                <div class="w-100">
                                                    <p>Nome do tipo de equipamento: {{ retrievedData.name }}</p>
                                                    <h5 class="card-title">Equipamentos/Ativos</h5>
                                                    <p>Disponibilidade Geral: 
                                                        {{ 
                                                                retrievedData.available_equipments.length + retrievedData.unavailable_equipments.length != 0 ?
                                                                Math.round((100 * retrievedData.available_equipments.length) / (retrievedData.available_equipments.length + retrievedData.unavailable_equipments.length)) : 0
                                                            }}%
                                                    </p>
                                                    <p style="color:green">Disponíveis: {{ retrievedData.available_equipments.length}}</p>
                                                    <p style="color:red">Indisponíveis: {{ retrievedData.unavailable_equipments.length}}</p>
                                                    <p style="color:red">Não operacionais: {{ retrievedData.imobilized_equipments.length}}</p>
                                            
                                                    <hr>

                                                    <div class="card-header">
                                                        <h5 class="card-title">Componentes do Tipo de Equipamento: {{components.total}} registros encontrados.</h5>
                                                        <div class="row">
                                                            <div class="col">
                                                                <a class="btn btn-pill btn-primary mt-3" data-toggle="collapse" href="#collapseExample" role="button" aria-expanded="false" aria-controls="collapseExample">
                                                                    <vue-feather type="plus"></vue-feather>Adicionar novo componente
                                                                </a>
                                                            </div>
                                                            
                                                        </div>
                                                        
                                                        <div class="collapse mt-3" id="collapseExample">
                                                            <div class="card card-body">
                                                                
                                                                <Form @submit="createRecordFunction" :validation-schema="schema" v-slot="{ errors }">
                                                                    <div class="row">
                                                                        <div class="mb-3 col-md-6">
                                                                            <label class="form-label" for="name">Nome</label>
                                                                            <Field type="text" class="form-control" :class="{'is-invalid':errors.name}" name="name" id="name" placeholder="Nome"/>
                                                                            <span class="invalid-feedback">{{ errors.name }}</span>
                                                                        </div>
                                                                    </div>
                                                                   
                                                                    <div class="row">
                                                                        <div class="mb-3 col-md-6">
                                                                            <label class="form-label" for="model">Modelo</label>
                                                                            <Field type="text" class="form-control" :class="{'is-invalid':errors.model}" name="model" id="model" placeholder="Modelo"/>
                                                                            <span class="invalid-feedback">{{ errors.model }}</span>
                                                                        </div>
                                                                    </div>
                                                                    <div class="row">
                                                                        <div class="mb-3 col-md-6">
                                                                            <label class="form-label" for="make">Marca</label>
                                                                            <Field type="text" class="form-control" :class="{'is-invalid':errors.make}" name="make" id="make" placeholder="Marca"/>
                                                                            <span class="invalid-feedback">{{ errors.make }}</span>
                                                                        </div>
                                                                    </div>
                                                                   
                                                                   
                                                                   
                                                                    <div class="row">
                                                                        <div class="mb-3 col-md-6">
                                                                            <label class="form-label" for="percentage_weigth">Percentagem do Componente no Equipamento %</label>
                                                                            <Field type="number" class="form-control" :class="{'is-invalid':errors.percentage_weigth}" name="percentage_weigth" id="percentage_weigth" placeholder="Percentagem%"/>
                                                                            <span class="invalid-feedback">{{ errors.percentage_weigth }}</span>
                                                                        </div>
                                                                    </div>
                                                                    <div class="row">
                                                                        <div class="mb-3 col-md-6">
                                                                            <label class="form-label" for="criticaly_id">Criticidade </label>
                                                                            <Field as="select" class="form-control" :class="{'is-invalid':errors.criticaly_id}"  name="criticaly_id" id="criticaly_id" aria-describedby="criticaly_id">
                                                                                <option value="" disabled>Selecionar</option>
                                                                                <option v-for="critical in criticals" :key="critical.id" :value="critical.id">{{ critical.name }}</option>
                                                                            </Field>
                                                                            <span class="invalid-feedback">{{ errors.criticaly_id }}</span>
                                                                            <Field type="hidden" class="form-control" :class="{'is-invalid':errors.type_equipment_id}" name="type_equipment_id" id="type_equipment_id" v-model="retrievedData.id"/>
                                                                        </div>
                                                                    </div>
                                                                  

                                                                   
                                                                    <button type="submit" class="btn btn-primary" :disabled="loadingSubmit == true">
                                                                        <div v-if="loadingSubmit == true" class="spinner-border spinner-border-sm" role="status"></div>
                                                                        <span v-else>Criar novo componente</span>
                                                                    </button>
                                                                </Form>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <form class="d-none d-sm-inline-block mt-3">
                                                        <div class="input-group input-group-navbar">
                                                            <input type="text" class="form-control" v-model="searchQuery" placeholder="Procurar componente..." aria-label="Search">
                                                        </div>
                                                    </form>

                                                    <div class="table-responsive">
                                                        <table class="table table-striped">
                                                            <thead>
                                                                <tr>
                                                                    <th>#</th>
                                                                    <th>Nome</th>
                                                                    <th>Marca</th>
                                                                    <th>Modelo</th>
                                                                    <th>Criticidade</th>
                                                                    <th>Percentagem</th>
                                                                    <th>SubComponentes</th>
                                                                    <th>Ações</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody v-if="components.data.length > 0">
                                                                <tr v-for="(actualData,index) in components.data" :key="actualData.id">
                                                                    <td>#{{ index + 1 }}</td>
                                                                    <td>{{ actualData.name}}</td>
                                                                    <td>{{ actualData.make}}</td>
                                                                    <td>{{ actualData.model}}</td>
                                                                    <td>{{ actualData.criticality.name}}</td>
                                                                    <td>{{ actualData.percentage_weigth}}%</td>
                                                                    <td>{{ actualData.subcomponents.length }}</td>
                                                                    <td>
                                                                        <router-link :to="'/admin/type_equipments/component/'+actualData.id+'/edit'"><vue-feather type="edit-2"></vue-feather></router-link>
                                                                        <router-link :to="'/admin/type_equipments/component/'+actualData.id"><vue-feather type="eye"></vue-feather></router-link> 
                                                                        <a href="#" @click.prevent="confirmCopyTask(actualData)"><vue-feather type="copy"></vue-feather></a>
                                                                        <a href="#" @click.prevent="confirmDeletion(actualData)"><vue-feather type="trash"></vue-feather></a>
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
                                                    <Bootstrap4Pagination :data="components" @pagination-change-page="getData"/>

                                                   
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
                                   <!-- Modal delete task -->
<div class="modal" id="copyModalTask" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLongTitle">Deseja mesmo copiar este tipo de equipamento.</h5>
          
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
            Ao confirmar será copiado todos os registros deste tipo de equipamento.
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
                <button @click.prevent="deleteData" type="button" class="btn btn-danger" :disabled="loadingButtonDelete">
                    <div v-if="loadingButtonDelete" class="spinner-border spinner-border-sm" role="status"></div>
                    <span v-else>Apagar registro</span>
                    </button>
         
          
        </div>
      </div>
    </div>
  </div>
</template>