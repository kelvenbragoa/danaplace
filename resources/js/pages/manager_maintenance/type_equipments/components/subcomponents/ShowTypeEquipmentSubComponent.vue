<script setup>

import axios from 'axios';
import { ref, onMounted, reactive, defineEmits, defineComponent,watch } from "vue";
import moment from 'moment'
import {useToastr} from '../../../../../toastr';
import {debounce} from 'lodash';
import {Form, Field} from 'vee-validate';
import { useRouter} from "vue-router";
import * as yup from 'yup';
import VueFeather from 'vue-feather';
import { Bootstrap4Pagination } from 'laravel-vue-pagination';
import QrcodeVue from 'qrcode.vue'

let retrievedData =ref([]);
let accounts =ref([]);
let loadingSubmit =ref([true]);
let loadingDiv =ref([true]);
let qrcodevalue = ref('/subcomponent/0');
const router = useRouter();
let self = this;
let criticals = ref([]);
let subcomponents = ref([]);
let currentvalue = ref([]);

const toastr = useToastr();


const getData = (page = 1) => {
  axios.get(`/typeequipmentsubcomponent/+${router.currentRoute.value.params.id}?page=${page}`, )
       .then((response)=>{
        loadingDiv.value=false;
        retrievedData.value = response.data.subcomponent;
        criticals.value = response.data.criticals;
        qrcodevalue.value = "/subcomponent/"+retrievedData.value.id;
       
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

        <h1 class="h3 mb-3">SubComponente do Componente do Tipo de Equipamento</h1>
        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Nome: {{ retrievedData.name }}</h5>
                

                                        <router-link :to="'/admin/type_equipments/component/'+retrievedData.type_equipment_component_id" class="btn btn-pill btn-primary mt-3"><vue-feather type="arrow-left"></vue-feather>Voltar</router-link> 

                                       
								    </div>
                                    
                                    <div class="card-body">

                                        <div class="row">


                                            <div class="col-xl-12 col-xxl-12 d-flex">
                                                <div class="w-100">
                                                    <div class="row">
                                                        <p><strong>Nome:</strong> {{ retrievedData.name }}</p>
                                                        <p><strong>Modelo:</strong> {{ retrievedData.model }}</p>
                                                        <p><strong>Marca:</strong> {{ retrievedData.make }}</p>
                                                        <p><strong>Criticalidade:</strong> {{ retrievedData.criticality.name }}</p>
                                                        <p><strong>Percentagem:</strong> {{ retrievedData.percentage_weigth }}%</p>
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