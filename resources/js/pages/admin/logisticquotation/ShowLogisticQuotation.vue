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
  axios.get(`/logisticquotation/+${router.currentRoute.value.params.id}?page=${page}`, 
  {
        params:{
          query: searchQuery.value
        }
      })
       .then((response)=>{
        loadingDiv.value=false;
        retrievedData.value = response.data.logisticquotation;
        // expenses.value = response.data.expenses;
        // total_expense.value = response.data.total_expense

     
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

        <h1 class="h3 mb-3">Quotações</h1>
        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Quotações: {{ retrievedData.id }}</h5>

                                        <router-link to="/admin/logisticquotation" class="btn btn-pill btn-primary mt-3"><vue-feather type="arrow-left"></vue-feather>Voltar</router-link> 

                                       
								    </div>
                                    
                                    <div class="card-body">
                                          
                                        <div class="row">
                                            <div class="col-xl-12 col-xxl-12 d-flex">
                                                <div class="w-100">
                                                    <p>Partida: {{ retrievedData.tripdestination == null ? retrievedData.departure : retrievedData.tripdestination.departure }}</p>
                                                    <p>Partida: {{ retrievedData.tripdestination == null ? retrievedData.destination : retrievedData.tripdestination.destination }}</p>
                                                    <p>Cliente: {{ retrievedData.customer == null ? retrievedData.customer_name : retrievedData.customer.customer_name}}</p>
                                                    
                                                    
                                                        
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