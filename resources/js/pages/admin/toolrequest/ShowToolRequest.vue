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
const router = useRouter();
let self = this;
let tools = ref([]);
let searchQuery = ref(null)
let equipments = ref([]);



const getData = (page=1) => {
  axios.get(`/toolrequests/+${router.currentRoute.value.params.id}?page=${page}`,
      {
        params:{
          query: searchQuery.value
        }
      })
       .then((response)=>{
        loadingDiv.value=false;
        retrievedData.value = response.data.requesttools;
        tools.value = response.data.tools;
       }).catch(()=>{
        loadingDiv.value=false;
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

        <h1 class="h3 mb-3">Requisições Ferramentaria</h1>
        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Requisições Ferramentaria:</h5>

                                        <router-link to="/admin/toolrequests" class="btn btn-pill btn-primary mt-3"><vue-feather type="arrow-left"></vue-feather>Voltar</router-link> 

                                       
								    </div>
                                    
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-xl-12 col-xxl-12 d-flex">
                                                <div class="w-100">
                                                    <h5 class="card-title">Informações da Requisição do Ferramentaria</h5>
                                                    <p><strong>ID:</strong> {{ retrievedData.id }}</p>
                                                    <p><strong>Tipo de Requisição:</strong> {{  retrievedData.task_mcscr_id != null ? 'Atividades Planeadas' : ''}} {{ retrievedData.mcscr_id != null ? 'MCSCR' : '' }}</p>
                                                    <p><strong>Criado por:</strong> {{ retrievedData.createdbyuser.firstName +' ' +retrievedData.createdbyuser.lastName }}</p>
                                                    <p><strong>Aprovado/Reprovado por:</strong> {{ retrievedData.approvedbyuser == null ? '-----' : retrievedData.approvedbyuser.firstName+' '+retrievedData.approvedbyuser.lastName +'('+moment(retrievedData.approved_date).format('DD-MM-YYYY H:mm')+')' }}</p>
                                                    <p><strong>Entregue por:</strong> {{ retrievedData.deliveredbyuser == null ? '-----' : retrievedData.deliveredbyuser.firstName+' '+retrievedData.deliveredbyuser.lastName+'('+moment(retrievedData.delivereddate).format('DD-MM-YYYY H:mm')+')' }}</p>
                                                    <p><strong>Data Criação:</strong> {{moment(retrievedData.created_at).format('DD-MM-YYYY H:mm') }}</p>
                                                    <p><strong>Estado:</strong>
                                                            <span v-if="retrievedData.request_tool_status_id == 1" class="badge bg-warning">
                                                                {{ retrievedData.status.name}}
                                                            </span>
                                                            <span v-if="retrievedData.request_tool_status_id == 2" class="badge bg-success">
                                                                {{ retrievedData.status.name}}
                                                            </span>
                                                            <span v-if="retrievedData.request_tool_status_id == 3" class="badge bg-danger">
                                                                {{ retrievedData.status.name}}
                                                            </span>
                                                            <span v-if="retrievedData.request_tool_status_id == 4" class="badge bg-info">
                                                                {{ retrievedData.status.name}}
                                                            </span>    
                                                    </p>

                                                    <hr>

                                                    <h5 class="card-title">Ferramentaria Requisitadas</h5>
                                                    <div class="table-responsive">
                                                        <table class="table table-striped">
                                                            <thead>
                                                                <tr>
                                                                    <th>#</th>
                                                                    <th>Ferramenta</th>
                                                                    <th>Codigo</th>
                                                                   
                                                                </tr>
                                                            </thead>
                                                            <tbody v-if="tools.length > 0">
                                                                <tr  v-for="(actualData,index) in tools" :key="actualData.id">
                                                                    <td>#{{ index + 1 }}</td>
                                                                    <td>{{ actualData.tool.name }}</td>
                                                                    <td>{{ actualData.tool.code}}</td>
                                                                    
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
</template>