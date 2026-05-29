<script setup>

import {onMounted, ref, reactive,watch} from 'vue';
import axios from 'axios';
import {useToastr} from '../../../toastr';
import moment from 'moment'
import {debounce} from 'lodash';
import { Bootstrap4Pagination } from 'laravel-vue-pagination';
import VueFeather from 'vue-feather';

const toastr = useToastr();
const searchQuery = ref(null);
const loadingDiv = ref(true);
const loadingButtonDelete = ref(false);
const destinations = ref([])
const destinationfilter = ref(null)



const retriviedData = ref({'data': []})

let dataIdBeingDeleted = ref(null);


 const getData = async (page = 1,destinationfilter) => {
  axios.get(`/users?page=${page}`,
      {
        params:{
          query: searchQuery.value,
          destination: destinationfilter

        }
      })
       .then((response)=>{
        retriviedData.value = response.data.users;
        destinations.value = response.data.destinations;
        loadingDiv.value=false;

        
       })

       
}

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

axios.delete(`/users/${dataIdBeingDeleted}`)
.then(()=>{
 retriviedData.value.data = retriviedData.value.data.filter(data=>data.id !== dataIdBeingDeleted); 
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

watch(searchQuery,debounce(()=>{
    getData();
},300));

onMounted(()=>{
    getData();
    
})
</script>

<template>
    <div v-if="!loadingDiv">
        <h1 class="h3 mb-3">Usuários</h1>
        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Tabela dos usuários do sistema. {{ retriviedData.total }} registros encontrados.</h5>
                                        <h6 class="card-subtitle text-muted">Para procurar, digite na caixa de procura</h6>

                                        <router-link to="/admin/users/create" class="btn btn-pill btn-primary mt-3"><vue-feather type="plus"></vue-feather>Adicionar</router-link> 

                                        <br>

                                        <form class="d-none d-sm-inline-block mt-3">
                                            <div class="input-group input-group-navbar">
                                                <input type="text" class="form-control" v-model="searchQuery" placeholder="Procurar nome, apelido ou email" aria-label="Search">
                                            </div>
                                        </form>
                                        <br>
                                        <form class="d-none d-sm-inline-block mt-3">
                                            <div class="input-group input-group-navbar">
                                                <select name="date_range" @change="getData(1,destinationfilter)" v-model="destinationfilter" class="form-control">
                                                        <option selected disabled>Select Destination</option>  
                                                        <option :value="destination.id" v-for="destination in destinations" :key="destination.id">{{ destination.name }}</option>                          
                                                </select>                       
                                            </div>
                                        </form>
								    </div>
                                    
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Nome</th>
                                                        <th>Apelido</th>
                                                        <th>Email</th>
                                                        <th>Telefone</th>
                                                        <th>Nível</th>
                                                        <th>Área</th>
                                                        <th>Destino</th>
                                                        <th>Estado Usuário</th>
                                                        <th>Ações</th>
                                                    </tr>
                                                </thead>
                                                <tbody v-if="retriviedData.data.length > 0">
                                                    <tr  v-for="(actualData,index) in retriviedData.data" :key="actualData.id">
                                                        <td>#{{ index + 1 }}</td>
                                                        <td>{{ actualData.firstName}}</td>
                                                        <td>{{ actualData.lastName}}</td>
                                                        <td>{{ actualData.email}}</td>
                                                        <td>{{ actualData.mobile}}</td>
                                                        <td>{{ actualData.role.name}}</td>
                                                        <td>{{ actualData.area == null ? '----' : actualData.area.name}}</td>
                                                        <td>{{ actualData.destination == null ? '----' : actualData.destination.name}}</td>
                                                        <td><span class="badge bg-success" v-if="actualData.user_status_id == 1">Ativo</span> <span class="badge bg-danger" v-else>Inativo</span></td>
                                                        <td>
                                                            <router-link :to="'/admin/users/'+actualData.id+'/edit'"><vue-feather type="edit-2"></vue-feather></router-link>
                                                            <router-link :to="'/admin/users/'+actualData.id"><vue-feather type="eye"></vue-feather></router-link> 
                                                            <a href="#" @click.prevent="confirmDeletion(actualData)"><vue-feather type="trash"></vue-feather></a>
                                                            
                                                        </td>
                                                    </tr>
                                                </tbody>
                                                <tbody v-else>
                                                    <tr>
                                                    <td colspan="10" align="center">Nenhum resultado encontrado</td>
                                                    </tr>
                                                </tbody>
                                            </table>
								        </div>
                                    </div>
                                   
								
                                </div>
                                <Bootstrap4Pagination :data="retriviedData" @pagination-change-page="getData"/>
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


</template>