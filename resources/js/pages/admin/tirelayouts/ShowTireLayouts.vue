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
let tirelayoutlevels =ref([]);
// let tirelayoutlevelposition = ref([]);








const getData = (page=1) => {
  axios.get(`/tirelayouts/+${router.currentRoute.value.params.id}?page=${page}`,
      {
        params:{
          query: searchQuery.value
        }
      })
       .then((response)=>{
        loadingDiv.value=false;
        retrievedData.value = response.data.tirelayout;
        tirelayoutlevels.value = response.data.tirelayoutlevels;
        // tirelayoutlevelposition.value = response.data.tirelayoutlevelposition;


    
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

        <h1 class="h3 mb-3">Layout</h1>
        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Layout: {{ retrievedData.name }}</h5>

                                        <router-link to="/admin/tirelayouts" class="btn btn-pill btn-primary mt-3"><vue-feather type="arrow-left"></vue-feather>Voltar</router-link> 

                                       
								    </div>
                                    
                                    <div class="card-body">
                                          
                                        <div class="row">
                                            <div class="col-xl-12 col-xxl-12 d-flex">
                                                <div class="w-100">
                                                    <p>Nome do layout: {{ retrievedData.name }}</p>    
                                                    <p>Nome do descrição: {{ retrievedData.description }}</p>    

                                                </div>
                                            </div>
                                        </div>

                                        <div class="row" v-for="(actualData,index) in tirelayoutlevels" :key="actualData.id">
                                            <div class="col text-right">
                                                <span v-for="(actualTireLeft,index) in actualData.tirelayoutlevelpositions.filter(item => item.position === 'Left')" :key="actualData.id">
                                                   {{ actualTireLeft.name }} <img src="/files/img/sys/tire2.png" alt="">
                                                </span>
                                            </div>
                                            <div class="col text-center">
                                                <img src="/files/img/sys/diff2.png" alt="">
                                            </div>
                                            <div class="col">
                                                <span v-for="(actualTireRight,index) in actualData.tirelayoutlevelpositions.filter(item => item.position === 'Right')" :key="actualData.id">
                                                    <img src="/files/img/sys/tire2.png" alt="" >  {{ actualTireRight.name }}
                                                </span>
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