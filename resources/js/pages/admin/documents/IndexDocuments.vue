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



const retriviedData = ref({'data': []})

let dataIdBeingDeleted = ref(null);


 const getData = async (page = 1) => {
  axios.get(`/documents?page=${page}`,
      {
        params:{
          query: searchQuery.value
        }
      })
       .then((response)=>{
        retriviedData.value = response.data;
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

axios.delete(`/documents/${dataIdBeingDeleted}`)
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
        <h1 class="h3 mb-3">Documentos</h1>
        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Tabela dos Documentos do sistema. {{ retriviedData.total }} registros encontrados.</h5>
                                        <h6 class="card-subtitle text-muted">Para procurar, digite na caixa de procura</h6>

                                        <router-link to="/admin/documents/create" class="btn btn-pill btn-primary mt-3"><vue-feather type="plus"></vue-feather>Adicionar</router-link> 

                                        <br>

                                        <form class="d-none d-sm-inline-block mt-3">
                                            <div class="input-group input-group-navbar">
                                                <input type="text" class="form-control" v-model="searchQuery" placeholder="Procurar nome..." aria-label="Search">
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
                                                        <th>Tipo de Documento</th>
                                                        <th>Descrição</th>
                                                        <th>Holder</th>
                                                        <th>Emissão</th>
                                                        <th>Expira</th>
                                                        <th>Ações</th>
                                                    </tr>
                                                </thead>
                                                <tbody v-if="retriviedData.data.length > 0">
                                                    <tr  v-for="(actualData,index) in retriviedData.data" :key="actualData.id">
                                                        <td>#{{ index + 1 }}</td>
                                                        <td>{{ actualData.name}}</td>
                                                        <td>{{ actualData.typedocument.name}}</td>
                                                        <td>{{ actualData.description}}</td>
                                                        <td>{{ actualData.holder}}</td>
                                                        <td>{{ moment(actualData.emission_date).format('DD-MM-YYYY')}}</td>
                                                        <td>{{ moment(actualData.expires_date).format('DD-MM-YYYY')}}</td>

                                                        <td>
                                                            <router-link :to="'/admin/documents/'+actualData.id+'/edit'"><vue-feather type="edit-2"></vue-feather></router-link>
                                                            <router-link :to="'/admin/documents/'+actualData.id"><vue-feather type="eye"></vue-feather></router-link> 
                                                            <a href="#" @click.prevent="confirmDeletion(actualData)"><vue-feather type="trash"></vue-feather></a>
                                                            
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
                                <Bootstrap4Pagination :data="retriviedData" @pagination-change-page="getData"/>
                            </div>
                        </div>

    <div class="modal" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalTitle" aria-hidden="true">
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