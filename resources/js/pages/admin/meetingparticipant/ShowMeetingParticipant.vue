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
let tasks = ref([]);

let totaltasks = ref([]);
let totaldonetasks = ref([]);
let totalnotdonetasks = ref([]);
let meetings = ref([]);







const getData = () => {
  axios.get(`/meetingparticipant/+${router.currentRoute.value.params.id}`)
       .then((response)=>{
        loadingDiv.value=false;
        retrievedData.value = response.data.user;
        tasks.value = response.data.tasks;
        totaltasks.value = response.data.totaltasks;
        totaldonetasks.value = response.data.totaldonetasks;
        totalnotdonetasks.value = response.data.totalnotdonetasks;
        meetings.value = response.data.meetings
       }).catch(()=>{
        loadingDiv.value=false;
       })
}

onMounted(()=>{
  
  getData();
})
</script>

<template>
    <div v-if="!loadingDiv">

        <h1 class="h3 mb-3">Funcionário</h1>
        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Funcionário</h5>

                                        <a @click="router.back()" class="btn btn-pill btn-primary mt-3"><vue-feather type="arrow-left"></vue-feather>Voltar</a> 
								    </div>
                                    
                                    <div class="card-body">
                                          
                                        <div class="row">
                                            <div class="col-xl-12 col-xxl-12 d-flex">
                                                <div class="w-100">
                                                    <p>Nome: {{ retrievedData.firstName }} {{ retrievedData.lastName }}</p>
                                                    <p>Email: {{ retrievedData.email }}</p>
                                                    <hr>
                                                    <div class="row">

                                                        <div class="col-sm-6 col-xl-3">
                                                            <div class="card">
                                                                <div class="card-body">
                                                                    <div class="row">
                                                                        <div class="col mt-0">
                                                                            <h5 class="card-title">Total Reuniões</h5>
                                                                        </div>
                                                                    </div>
                                                                    <!-- <h1 class="mt-1 mb-3">{{ totalMcscrCount }}</h1> -->
                                                                    <h1 class="mt-1 mb-3">{{meetings}}</h1>

                                                                    <div class="mb-0">
                                                                        <!-- <select name="date_range" @change="getMcscrCount()" v-model="selectedMcscrStatus" class="form-control"> -->
                                                                        <select name="date_range" class="form-control">
                                                                            <option selected value="total" >Total</option>
                                                                            <option value="today" >Hoje</option>
                                                                            <option value="30" >30 dias</option>
                                                                            <option value="60" >60 dias</option>
                                                                            <option value="360" >360 dias</option>
                                                                            <option value="monthtodate" >Inicio Mês até Hoje</option>
                                                                            <option value="yeartodate" >Inicio Ano até Hoje</option>
                                                                        </select>
                                                                    </div>
                                                                    
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-sm-6 col-xl-3">
                                                            <div class="card">
                                                                <div class="card-body">
                                                                    <div class="row">
                                                                        <div class="col mt-0">
                                                                            <h5 class="card-title">Total Tarefas</h5>
                                                                        </div>
                                                                    </div>
                                                                    <!-- <h1 class="mt-1 mb-3">{{ totalMcscrCount }}</h1> -->
                                                                    <h1 class="mt-1 mb-3">{{ totaltasks }}</h1>

                                                                    <div class="mb-0">
                                                                        <!-- <select name="date_range" @change="getMcscrCount()" v-model="selectedMcscrStatus" class="form-control"> -->
                                                                        <select name="date_range" class="form-control">
                                                                            <option selected value="total" >Total</option>
                                                                            <option value="today" >Hoje</option>
                                                                            <option value="30" >30 dias</option>
                                                                            <option value="60" >60 dias</option>
                                                                            <option value="360" >360 dias</option>
                                                                            <option value="monthtodate" >Inicio Mês até Hoje</option>
                                                                            <option value="yeartodate" >Inicio Ano até Hoje</option>
                                                                        </select>
                                                                    </div>
                                                                    
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-sm-6 col-xl-3">
                                                            <div class="card">
                                                                <div class="card-body">
                                                                    <div class="row">
                                                                        <div class="col mt-0">
                                                                            <h5 class="card-title">Total Tarefas Executadas</h5>
                                                                        </div>
                                                                    </div>
                                                                    <!-- <h1 class="mt-1 mb-3">{{ totalMcscrCount }}</h1> -->
                                                                    <h1 class="mt-1 mb-3">{{ totaldonetasks }}</h1>

                                                                    <div class="mb-0">
                                                                        <!-- <select name="date_range" @change="getMcscrCount()" v-model="selectedMcscrStatus" class="form-control"> -->
                                                                        <select name="date_range" class="form-control">
                                                                            <option selected value="total" >Total</option>
                                                                            <option value="today" >Hoje</option>
                                                                            <option value="30" >30 dias</option>
                                                                            <option value="60" >60 dias</option>
                                                                            <option value="360" >360 dias</option>
                                                                            <option value="monthtodate" >Inicio Mês até Hoje</option>
                                                                            <option value="yeartodate" >Inicio Ano até Hoje</option>
                                                                        </select>
                                                                    </div>
                                                                    
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-sm-6 col-xl-3">
                                                            <div class="card">
                                                                <div class="card-body">
                                                                    <div class="row">
                                                                        <div class="col mt-0">
                                                                            <h5 class="card-title">Total Tarefas Não Executadas</h5>
                                                                        </div>
                                                                    </div>
                                                                    <!-- <h1 class="mt-1 mb-3">{{ totalMcscrCount }}</h1> -->
                                                                    <h1 class="mt-1 mb-3">{{ totalnotdonetasks }}</h1>

                                                                    <div class="mb-0">
                                                                        <!-- <select name="date_range" @change="getMcscrCount()" v-model="selectedMcscrStatus" class="form-control"> -->
                                                                        <select name="date_range" class="form-control">
                                                                            <option selected value="total" >Total</option>
                                                                            <option value="today" >Hoje</option>
                                                                            <option value="30" >30 dias</option>
                                                                            <option value="60" >60 dias</option>
                                                                            <option value="360" >360 dias</option>
                                                                            <option value="monthtodate" >Inicio Mês até Hoje</option>
                                                                            <option value="yeartodate" >Inicio Ano até Hoje</option>
                                                                        </select>
                                                                    </div>
                                                                    
                                                                </div>
                                                            </div>
                                                        </div>


                                                    </div>
                                                    <div class="table-responsive">
                                                        <table class="table table-striped">
                                                            <thead>
                                                                <tr>
                                                                    <th>#</th>
                                                                    <th>Participante</th>
                                                                    <th>Email</th>
                                                                    <th>Reunião</th>
                                                                    <th>Tarefa</th>
                                                                    <th>Prazo</th>
                                                                    <th>Estado</th>
                                                                    <th>Ações</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody v-if="tasks.length > 0">
                                                                <tr  v-for="(actualData,index) in tasks" :key="actualData.id">
                                                                    <td>#{{ index + 1 }}</td>
                                                                    <td>{{ actualData.participant.name}}</td>
                                                                    <td>{{ actualData.participant.email}}</td>
                                                                    <td>{{ actualData.meeting.subject}}</td>
                                                                    <td>{{ actualData.what}}</td>
                                                                    <td>{{ moment(actualData.date).format('DD-MM-YYYY') }}</td>
                                                                    <td>
                                                                                    <span v-if="actualData.status_id == 1" class="badge bg-success">
                                                                                        {{actualData.status.name}}
                                                                                    </span>
                                                                                    <span v-if="actualData.status_id == 2" class="badge bg-danger">
                                                                                        {{actualData.status.name}} 
                                                                                        <!-- ({{ moment().diff(actualData.date,'days dias') }}) -->
                                                                                    </span>
                                                                                    <span v-if="actualData.status_id == 4" class="badge bg-warning">
                                                                                        {{actualData.status.name}}
                                                                                    </span>
                                                                                    <span v-if="actualData.status_id == 3" class="badge bg-info">
                                                                                        {{actualData.status.name}}
                                                                                    </span>
                                                                    </td>
                                                                    <td>
                                                                        <!-- <router-link :to="'/admin/meeting/'+actualData.id+'/edit'"><vue-feather type="edit-2"></vue-feather></router-link> -->
                                                                        <router-link :to="'/admin/meeting/'+actualData.meeting_id"><vue-feather type="eye"></vue-feather></router-link> 
                                                                        <!-- <a href="#" @click.prevent="confirmDeletion(actualData)"><vue-feather type="trash"></vue-feather></a> -->
                                                                        
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