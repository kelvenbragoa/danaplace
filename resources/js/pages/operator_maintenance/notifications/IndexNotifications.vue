<script setup>

import {onMounted, ref, reactive,watch} from 'vue';
import axios from 'axios';
import {useToastr} from '../../../toastr';
import moment from 'moment'
import {debounce} from 'lodash';
import { Bootstrap4Pagination } from 'laravel-vue-pagination';
import VueFeather from 'vue-feather';

const toastr = useToastr();
const searchQuery = ref(null);
const loadingDiv = ref(true);
const loadingButtonDelete = ref(false);
let loggedUser = window.user;
let name = loggedUser.firstName+' '+loggedUser.lastName;


const retriviedData = ref({'data': []})

let dataIdBeingDeleted = ref(null);


 const getData = async (page = 1) => {
  axios.get(`/notifications`,
      {
        params:{
          query: searchQuery.value
        }
      })
       .then((response)=>{
        retriviedData.value = response.data;
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
        <h1 class="h3 mb-3">Notificações</h1>
        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    
                                    
                                    <div class="card-body">
                                        <div v-for="(notification, index) in retriviedData" :key="notification.id">
                                            <div class="d-flex align-items-start">
                                                <div class="rounded-circle mr-2">
                                                    <i class="far fa-fw fa-bell"></i>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <small class="float-right text-navy">Lida as: {{moment(notification.read_at).format('DD-MM-YYYY H:mm')}} </small>
                                                    <strong>#{{ index+1 }} {{name}}</strong>| {{notification.data.data}}<br />

                                                    <small class="text-muted">{{moment(notification.created_at).format('DD-MM-YYYY H:mm')}}</small>
                                                </div>
                                            </div>
                                            <hr>
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