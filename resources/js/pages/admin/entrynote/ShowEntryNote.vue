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
let searchQuery = ref(null)
let equipments = ref([]);







const getData = (page=1) => {
  axios.get(`/entrynotes/+${router.currentRoute.value.params.id}?page=${page}`,
      {
        params:{
          query: searchQuery.value
        }
      })
       .then((response)=>{
        loadingDiv.value=false;
        retrievedData.value = response.data.entrynote;
    
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

        <h1 class="h3 mb-3">Nota de Entrada</h1>
        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Nota de Entrada: {{ retrievedData.ref }}</h5>

                                        <router-link to="/admin/entrynotes" class="btn btn-pill btn-primary mt-3"><vue-feather type="arrow-left"></vue-feather>Voltar</router-link> 

                                       
								    </div>
                                    
                                    <div class="card-body">
                                          
                                        <div class="row">
                                            <div class="col-xl-12 col-xxl-12 d-flex">
                                                <div class="w-100">
                                                    <p>Referência: {{ retrievedData.ref }}</p>
                                                    <p>Documento Referência: {{ retrievedData.document_ref }}</p>
                                                    <p>Serie: {{ retrievedData.serie }}</p>
                                                    <p>Centro de Stock: {{ retrievedData.stockcenter.name }}</p>
                                                    <p>Fornecedor Stock: {{ retrievedData.supplier.name }}</p>
                                                    <p>Criado por: {{ retrievedData.user.name }}</p>
                                                    <p>Data: {{ moment(retrievedData.created_at).format('DD-MM-YYYY H:mm') }}</p>
                                                    <p>Observação: {{ retrievedData.obs }}</p>

                                                    <div class="table-responsive">
                                                        <table class="table table-striped">
                                                            <thead>
                                                                <tr>
                                                                    <th>#</th>
                                                                    <th>Nome produto</th>
                                                                    <th>Centro de Stock</th>
                                                                    <th>Quantidade Anterior</th>
                                                                    <th>Quantidade Entrada</th>
                                                                    <th>Quantidade Atual</th>
                                                                   
                                                                
                                                                </tr>
                                                            </thead>
                                                            <tbody v-if="retrievedData.itens.length > 0">
                                                                <tr  v-for="(actualData,index) in retrievedData.itens" :key="actualData.id">
                                                                    <td>#{{ index + 1 }}</td>
                                                                    <td>{{ actualData.product.name}}</td>
                                                                    <td>{{ retrievedData.stockcenter.name}}</td>
                                                                    <td>{{ actualData.last_quantity}}</td>
                                                                    <td>{{ actualData.quantity}}</td>
                                                                    <td>{{  actualData.quantity + actualData.last_quantity }}</td>
                                                                  
                                                                </tr>
                                                            </tbody>
                                                            <tbody v-else>
                                                                <tr>
                                                                <td colspan="8" align="center">Nenhum resultado encontrado</td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    
                                                    
                                          
                                            
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