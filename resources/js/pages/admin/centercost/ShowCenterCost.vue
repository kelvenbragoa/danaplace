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
let accounts =ref([]);
let loadingSubmit =ref([true]);
let loadingDiv =ref([true]);
const loadingButtonDelete = ref(false);
const router = useRouter();
let self = this;

let dataIdBeingDeleted = ref(null);

const toastr = useToastr();

const searchQuery = ref(null);

let currentvalue = ref([]);

const schema = yup.object({
    
    name: yup.string().required(),
    code: yup.string().required(),
   
});

const createRecordFunction = (values, actions) => {

 
currentvalue.value = {values};

loadingSubmit.value = true;

const arr = Array.from(values)

axios.post('/centercostaccount',values).then((response)=>{

// categories.value.unshift(response.data);
// $('#createCategory').modal('hide');
retrievedData.value = response.data.centercost;
accounts.value = response.data.accounts;

actions.resetField('name');
actions.resetField('code');
toastr.success('Centro de custo criado com sucesso');
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

const getData = (page = 1) => {
  axios.get(`/centercost/+${router.currentRoute.value.params.id}?page=${page}`,
        {
        params:{
          query: searchQuery.value
        }
      }
        )
       .then((response)=>{
        loadingDiv.value=false;
        retrievedData.value = response.data.centercost;
        accounts.value = response.data.accounts;
       }).catch(()=>{
        loadingDiv.value=false;
       })
}

watch(searchQuery,debounce(()=>{
    getData();
},300));

const confirmDeletion = (data) => {

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

axios.delete(`/centercostaccount/${dataIdBeingDeleted}`)
.then(()=>{
    accounts.value.data = accounts.value.data.filter(data=>data.id !== dataIdBeingDeleted); 
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


onMounted(()=>{
  
  getData();
})
</script>

<template>
    <div v-if="!loadingDiv">

        <h1 class="h3 mb-3">Centro de custo</h1>
        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Centro de custo: {{ retrievedData.name }}</h5>

                                        <router-link to="/admin/centercost" class="btn btn-pill btn-primary mt-3"><vue-feather type="arrow-left"></vue-feather>Voltar</router-link> 

                                       
								    </div>
                                    
                                    <div class="card-body">
                                          
                                        <div class="row">
                                            <div class="col-xl-12 col-xxl-12 d-flex">
                                                <div class="w-100">
                                                    <div class="row">
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
                                                                    <h1 class="mt-1 mb-3">{{retrievedData.equipments.length}}</h1>
                                                                    <div class="mb-1">
                                                                        <span class="text-success"> <i class="mdi mdi-arrow-bottom-right"></i> </span>
                                                                        <span class="text-muted"></span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                    </div>
                                                        
                                                </div>
                                            </div>
                                        </div>

                                        <hr>

                                        <div class="card-header">
                                            <h5 class="card-title">Contas do centro de custo: {{ accounts.total}} registros encontrados.</h5>
                                            <a class="btn btn-pill btn-primary mt-3" data-toggle="collapse" href="#collapseExample" role="button" aria-expanded="false" aria-controls="collapseExample">
                                                <vue-feather type="plus"></vue-feather>Adicionar novo conta
                                            </a>
                                            <div class="collapse mt-2" id="collapseExample">
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
                                                                <label class="form-label" for="code">Código</label>
                                                                <Field type="text" class="form-control" :class="{'is-invalid':errors.code}" name="code" id="code" placeholder="Código"/>
                                                                <span class="invalid-feedback">{{ errors.code }}</span>
                                                                <Field type="hidden" class="form-control" :class="{'is-invalid':errors.code}" name="center_cost_id" id="center_cost_id" v-model="retrievedData.id"/>
                                                            </div>
                                                        </div>
                                                        <button type="submit" class="btn btn-primary" :disabled="loadingSubmit == true">
                                                            <div v-if="loadingSubmit == true" class="spinner-border spinner-border-sm" role="status"></div>
                                                            <span v-else>Criar nova conta</span>
                                                        </button>
											        </Form>
                                                </div>
                                            </div>

                                            
								        </div>



                                        <form class="d-none d-sm-inline-block mt-3">
                                            <div class="input-group input-group-navbar">
                                                <input type="text" class="form-control" v-model="searchQuery" placeholder="Procurar conta..." aria-label="Search">
                                            </div>
                                        </form>

                                        <div class="table-responsive">
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Nome da conta</th>
                                                        <th>Código</th>
                                                        <th>Centro de custo</th>
                                                        <th>Equipamentos/Ativos</th>
                                                        <th>Ações</th>
                                                    </tr>
                                                </thead>
                                                <tbody v-if="accounts.data.length > 0">
                                                    <tr  v-for="(actualData,index) in accounts.data" :key="actualData.id">
                                                        <td>#{{ index + 1 }}</td>
                                                        <td>{{ actualData.name}}</td>
                                                        <td>{{ actualData.code}}</td>
                                                        <td>{{ actualData.center_cost.name}} / {{ actualData.center_cost.code}}</td>
                                                        <td>{{ actualData.equipments.length}}</td>
                                                        <td>
                                                            <router-link :to="'/admin/centercost/account/'+actualData.id+'/edit'"><vue-feather type="edit-2"></vue-feather></router-link>
                                                            <router-link :to="'/admin/centercost/account/'+actualData.id"><vue-feather type="eye"></vue-feather></router-link> 
                                                            <a href="#" @click.prevent="confirmDeletion(actualData)"><vue-feather type="trash"></vue-feather></a>
                                                            
                                                        </td>
                                                    </tr>
                                                </tbody>
                                                <tbody v-else>
                                                    <tr>
                                                    <td colspan="6" align="center">Nenhum resultado encontrado</td>
                                                    </tr>
                                                </tbody>
                                            </table>
								        </div>
                                        <Bootstrap4Pagination :data="accounts" @pagination-change-page="getData"/>



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