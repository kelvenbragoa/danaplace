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
let departments = ref([]);
let searchQuery = ref(null)
let equipments = ref([]);
let requestitens = ref([])






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

                                        <router-link to="/admin/shiftequipmentrequest" class="btn btn-pill btn-primary mt-3"><vue-feather type="arrow-left"></vue-feather>Voltar</router-link> 

                                       
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