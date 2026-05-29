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
const searchQuery = ref(null);
let equipments =ref([]);
let loadingSubmit =ref([true]);
let loadingDiv =ref([true]);
const router = useRouter();
let self = this;







const getData = async (page = 1) => {
  axios.get(`/areas/+${router.currentRoute.value.params.id}?page=${page}`, 
  {
        params:{
          query: searchQuery.value
        }
      })
       .then((response)=>{
        loadingDiv.value=false;
        retrievedData.value = response.data.area;
        equipments.value = response.data.equipments;
     
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

        <h1 class="h3 mb-3">Área</h1>
        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Área: {{ retrievedData.name }}</h5>

                                        <router-link to="/manager/maintenance/areas" class="btn btn-pill btn-primary mt-3"><vue-feather type="arrow-left"></vue-feather>Voltar</router-link> 

                                       
								    </div>
                                    
                                    <div class="card-body">
                                          
                                        <div class="row">
                                            <div class="col-xl-12 col-xxl-12 d-flex">
                                                <div class="w-100">
                                                    <p>Nome da área: {{ retrievedData.name }}</p>
                                                    <p>Empresa: {{ retrievedData.company_name }}</p>
                                                    <p>Endereço: {{ retrievedData.company_address }}</p>
                                                    <p>Província: {{ retrievedData.province.name }}</p>
                                                    <p>NUIT: {{ retrievedData.company_nuit }}</p>
                                                    <p>Telefone: {{ retrievedData.company_mobile }}</p>
                                                    <p>Email: {{ retrievedData.company_email }}</p>
                                                    <hr>
                                                    <h5 class="card-title">Equipamentos/Ativos</h5>
                                                    <p>Disponibilidade Geral: 
                                                        {{ 
                                                                retrievedData.available_equipments.length + retrievedData.unavailable_equipments.length != 0 ?
                                                                Math.round((100 * retrievedData.available_equipments.length) / (retrievedData.available_equipments.length + retrievedData.unavailable_equipments.length)) : 0
                                                            }}%
                                                    </p>
                                                    <p>Disponíveis: {{ retrievedData.available_equipments.length}}</p>
                                                    <p>Indisponíveis: {{ retrievedData.unavailable_equipments.length}}</p>
                                                    <p>Não operacionais: {{ retrievedData.imobilized_equipments.length}}</p>

                                                    <hr>
                                                    <p>Atividades planeadas: {{ retrievedData.task_mcscr.length}}</p>
                                                    <p>Atividades não planeadas: {{ retrievedData.mcscr.length}}</p>

                                                    <hr>

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
                                                                    <th>Ref</th>
                                                                    <th>Tipo de Equipamento</th>
                                                                    <th>Área</th>
                                                                    <th>Destino</th>
                                                                    <th>Modelo</th>
                                                                    <th>Ano de Compra</th>
                                                                    <th>Estado</th>
                                                                    <th>Operação</th>
                                                                    <th>Ações</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody v-if="equipments.data.length > 0">
                                                                <tr  v-for="(actualData,index) in equipments.data" :key="actualData.id">
                                                                    <td>#{{ index + 1 }}</td>
                                                                    <td>{{ actualData.name}}</td>
                                                                    <td>{{ actualData.ref}}</td>
                                                                    <td>{{ actualData.type_equipment.name}}</td>
                                                                    <td>{{ actualData.area.name}}</td>
                                                                    <td>{{ actualData.destination.name}}</td>
                                                                    <td>{{ actualData.model}}</td>
                                                                    <td>{{ actualData.buy_year}}</td>
                                                                    <td>
                                                                        <span class="badge bg-success" v-if="actualData.equipment_status.id == 1">
                                                                            {{ actualData.equipment_status.name}}
                                                                        </span> 
                                                                        <span class="badge bg-danger" v-if="actualData.equipment_status.id == 2">
                                                                            {{ actualData.equipment_status.name}}
                                                                        </span>
                                                                        <span class="badge bg-danger" v-if="actualData.equipment_status.id == 3">
                                                                            {{ actualData.equipment_status.name}}
                                                                        </span>
                                                                    </td>
                                                                    <td>
                                                                        <span class="badge bg-success" v-if="actualData.equipment_status.id == 1">
                                                                            {{ actualData.equipment_status.mobilized}}
                                                                        </span> 
                                                                        <span class="badge bg-success" v-if="actualData.equipment_status.id == 2">
                                                                            {{ actualData.equipment_status.mobilized}}
                                                                        </span>
                                                                        <span class="badge bg-danger" v-if="actualData.equipment_status.id == 3">
                                                                            {{ actualData.equipment_status.mobilized}}
                                                                        </span>
                                                                    </td>
                                                                    <td>
                                                                        <router-link :to="'/manager/maintenance/equipments/'+actualData.id+'/edit'"><vue-feather type="edit-2"></vue-feather></router-link>
                                                                        <router-link :to="'/manager/maintenance/equipments/'+actualData.id"><vue-feather type="eye"></vue-feather></router-link> 
                                                                        
                                                                        
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                            <tbody v-else>
                                                                <tr>
                                                                <td colspan="11" align="center">Nenhum resultado encontrado</td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    <Bootstrap4Pagination :data="equipments" @pagination-change-page="getData"/>



                                            
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