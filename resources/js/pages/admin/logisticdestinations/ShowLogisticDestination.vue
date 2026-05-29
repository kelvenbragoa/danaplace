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
let expenses =ref([]);
let loadingSubmit =ref([true]);
let loadingDiv =ref([true]);
const router = useRouter();
let self = this;
let currentvalue = ref([]);
let total_expense = ref(0);

const toastr = useToastr();



const schema = yup.object({
    
    expense_description: yup.string().required(),
    expense_amount: yup.string().required(),
     
  });

const createRecordFunction = (values, actions) => {

 
currentvalue.value = {values};

loadingSubmit.value = true;

const arr = Array.from(values)

axios.post('/destinationexpense',values).then((response)=>{

// categories.value.unshift(response.data);
// $('#createCategory').modal('hide');
expenses.value = response.data.expenses;
total_expense.value = response.data.total_expense


actions.resetField('expense_amount');
actions.resetField('expense_description');


toastr.success('Despesa criado com sucesso');
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



const getData = async (page = 1) => {
  axios.get(`/logisticdestination/+${router.currentRoute.value.params.id}?page=${page}`, 
  {
        params:{
          query: searchQuery.value
        }
      })
       .then((response)=>{
        loadingDiv.value=false;
        retrievedData.value = response.data.destination;
        expenses.value = response.data.expenses;
        total_expense.value = response.data.total_expense

     
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

        <h1 class="h3 mb-3">Destino de Logistica</h1>
        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Destino de Logistica: {{ retrievedData.name }}</h5>

                                        <router-link to="/admin/logisticdestination" class="btn btn-pill btn-primary mt-3"><vue-feather type="arrow-left"></vue-feather>Voltar</router-link> 

                                       
								    </div>
                                    
                                    <div class="card-body">
                                          
                                        <div class="row">
                                            <div class="col-xl-12 col-xxl-12 d-flex">
                                                <div class="w-100">
                                                    <p>Partida: {{ retrievedData.departure }}</p>
                                                    <p>Destino: {{ retrievedData.destination }}</p>
                                                    <p>Distancia total: {{ retrievedData.total_distance }} KM</p>
                                                    <p>Tipo de Carregamento: {{ retrievedData.loadstatus.name }}</p>
                                                    <p>Valor de Carregamento: {{ retrievedData.amount }} {{ retrievedData.coin.name }}</p>
                                                    <p>Total Despesas: {{ total_expense }} {{ retrievedData.coin.name }}</p>
                                                    <hr>

                                                    <div class="card-header">
                                                        <h5 class="card-title">Despesas: {{expenses.length}} registros encontrados.</h5>
                                                        <div class="row">
                                                            <div class="col">
                                                                <a class="btn btn-pill btn-primary mt-3" data-toggle="collapse" href="#collapseExample" role="button" aria-expanded="false" aria-controls="collapseExample">
                                                                    <vue-feather type="plus"></vue-feather>Adicionar nova despesa
                                                                </a>
                                                            </div>
                                                            
                                                        </div>
                                                        
                                                        <div class="collapse mt-3" id="collapseExample">
                                                            <div class="card card-body">
                                                                
                                                                <Form @submit="createRecordFunction" :validation-schema="schema" v-slot="{ errors }">
                                                                    <div class="row">
                                                                        <div class="mb-3 col-md-6">
                                                                            <label class="form-label" for="expense_description">Nome</label>
                                                                            <Field type="text" class="form-control" :class="{'is-invalid':errors.expense_description}" name="expense_description" id="expense_description" placeholder="Nome"/>
                                                                            <span class="invalid-feedback">{{ errors.expense_description }}</span>
                                                                        </div>
                                                                    </div>
                                                                
                                                                    <div class="row">
                                                                        <div class="mb-3 col-md-6">
                                                                            <label class="form-label" for="expense_amount">Valor</label>
                                                                            <Field type="number" class="form-control" :class="{'is-invalid':errors.expense_amount}" name="expense_amount" id="expense_amount" placeholder="Valor"/>
                                                                            <span class="invalid-feedback">{{ errors.expense_amount }}</span>
                                                                            <Field type="hidden" class="form-control" :class="{'is-invalid':errors.destination_id}" v-model="retrievedData.id" name="destination_id" id="destination_id" placeholder="Valor"/>

                                                                        </div>
                                                                    </div>
                                                                   
                                                                    <button type="submit" class="btn btn-primary" :disabled="loadingSubmit == true">
                                                                        <div v-if="loadingSubmit == true" class="spinner-border spinner-border-sm" role="status"></div>
                                                                        <span v-else>Criar nova despesa</span>
                                                                    </button>
                                                                </Form>
                                                            </div>
                                                        </div>
                                                    </div>

                                        

                                                    <div class="table-responsive">
                                                        <table class="table table-striped">
                                                            <thead>
                                                                <tr>
                                                                    <th>#</th>
                                                                    <th>Nome</th>
                                                                    <th>Valor</th>
                                                                    <!-- <th>Ações</th> -->
                                                                </tr>
                                                            </thead>
                                                            <tbody v-if="expenses.length > 0">
                                                                <tr v-for="(actualData,index) in expenses" :key="actualData.id">
                                                                    <td>#{{ index + 1 }}</td>
                                                                    <td>{{ actualData.expense_description}}</td>
                                                                    <td>{{ actualData.expense_amount}} MT</td>
                                                                    <!-- <td>
                                                                        <router-link :to="'/admin/type_equipments/component/'+actualData.id+'/edit'"><vue-feather type="edit-2"></vue-feather></router-link>
                                                                        <router-link :to="'/admin/type_equipments/component/'+actualData.id"><vue-feather type="eye"></vue-feather></router-link> 
                                                                        <a href="#" @click.prevent="confirmDeletion(actualData)"><vue-feather type="trash"></vue-feather></a>
                                                                    </td> -->
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