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







const getData = () => {
  axios.get(`/users/+${router.currentRoute.value.params.id}`)
       .then((response)=>{
        loadingDiv.value=false;
        retrievedData.value = response.data;
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

        <h1 class="h3 mb-3">Usuário</h1>
        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Usuário</h5>

                                        <router-link to="/admin/users" class="btn btn-pill btn-primary mt-3"><vue-feather type="arrow-left"></vue-feather>Voltar</router-link> 
								    </div>
                                    
                                    <div class="card-body">
                                          
                                        <div class="row">
                                            <div class="col-xl-12 col-xxl-12 d-flex">
                                                <div class="w-100">
                                                    <p>Nome: {{ retrievedData.firstName }} {{ retrievedData.lastName }}</p>
                                                    <p>Email: {{ retrievedData.email }}</p>
                                                    <p>Endereço: {{ retrievedData.address }}</p>
                                                    <p>Código: {{ retrievedData.code }}</p>
                                                    <p>BI: {{ retrievedData.bi }}</p>
                                                    <p>Telefone: {{ retrievedData.mobile }}</p>
                                                    <p>Celular: {{ retrievedData.cellphone }}</p>
                                                    <p>Area: {{ retrievedData.area == null ? '----' : retrievedData.area.name }}</p>
                                                    <p>Destino: {{ retrievedData.destination == null ? '----' : retrievedData.destination.name }}</p>
                                                    <p>País: {{ retrievedData.country.name }}</p>
                                                    <p>Província: {{ retrievedData.province.name }}</p>
                                                    <p>Cidade: {{ retrievedData.city.name }}</p>
                                                    <p>Estado do usuário: {{ retrievedData.user_status.name }}</p>
                                                    <p>Estado da conta: {{ retrievedData.account_status.name }}</p>
                                                    <p>Nível: {{ retrievedData.role.name }}</p>

                                            
                                                  
                                                        
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