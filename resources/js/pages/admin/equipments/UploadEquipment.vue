<script setup>

import axios from 'axios';
import { ref, onMounted, reactive, defineEmits, defineComponent,watch } from "vue";
import moment from 'moment'
import {useToastr} from '../../../toastr';
import {debounce} from 'lodash';
import {Form, Field, FieldArray} from 'vee-validate';
import { useRouter} from "vue-router";
import * as yup from 'yup';
import VueFeather from 'vue-feather';
import { Bootstrap4Pagination } from 'laravel-vue-pagination';
import FileUpload from 'primevue/fileupload';


let retrievedData =ref([]);
let loadingButtonSubmit =ref([false]);
let loadingDiv =ref([true]);
const toastr = useToastr();
const router = useRouter();
let self = this;
let currentvalue = ref([]);
let loggedUser = window.user;
const uploads = ref();
let image = ref();
const dataIdBeingDeleted = ref(0);
const loadingButtonDelete = ref(false);

const onFileUpload = (event) => {
    image.value = event.files[0];
    console.log(image.value);
};
const confirmDeletion = (data) => {

dataIdBeingDeleted.value = data.id;

$('#deleteModal').modal('show');
};
const deleteData = () =>{

loadingButtonDelete.value= true;

axios.delete(`/equipments/upload/${dataIdBeingDeleted.value}`)
.then(()=>{
 uploads.value = uploads.value.filter(data=>data.id !== dataIdBeingDeleted.value); 
 $('#deleteModal').modal('hide');

loadingButtonDelete.value= false;

 toastr.success('Registro apagada com sucesso');

}).catch((e)=>{
console.log(e)
 toastr.error('Erro ao apagar');
 loadingButtonDelete.value= false;
 $('#deleteModal').modal('hide');
}).finally(()=>{
 loadingButtonDelete.value= false;
});
}







const getData = () => {
  axios.get(`/equipments/${router.currentRoute.value.params.id}/upload`)
       .then((response)=>{

        loadingDiv.value=false;
        retrievedData.value = response.data.equipment;
        uploads.value = response.data.uploads;
       }).catch(()=>{

        loadingDiv.value=false;

       })
}

const editFunction = (values, actions) => {
  loadingButtonSubmit.value = true;
  values.image = image.value;
  values.equipment_id = retrievedData.value.id;
  axios.post(`/equipments/upload`,values,{
    headers: {
            'Content-Type': 'multipart/form-data'
          }
  }).then((response)=>{
    // admins.value.unshift(response.data);
    // $('#createCategory').modal('hide');
    retrievedData.value = response.data.equipment;
    uploads.value = response.data.uploads;
    actions.resetForm();
    router.push({ path: `/admin/equipments/${retrievedData.value.id}/upload` });

    toastr.success('Equipamentos editado com sucesso');


  }).catch((error)=>{

    loadingButtonSubmit.value = false;
    console.log(error);
    toastr.error('Erro ao adicionar.'+error.response.data.message);
    if(error.response.data.errors){
      actions.setErrors(error.response.data.errors);
    }
  }).finally(()=>{
    loadingButtonSubmit.value = false;
  })
};





onMounted(()=>{
  
  getData();

})
</script>

<template>
    <div v-if="!loadingDiv">

        <h1 class="h3 mb-3">Equipamentos </h1>
        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Equipamentos: #{{ retrievedData.id }}</h5>

                                        <a @click="$router.go(-1)" class="btn btn-pill btn-primary mt-3"><vue-feather type="arrow-left"></vue-feather>Voltar</a> 

								    </div>
                                    
                                    <div class="card-body">
                                          
                                        <div class="row">
                                            <div class="row">
                                                <!-- imagem -->
                                                <div class="col-sm-6 col-xl-3" v-for="upload in uploads" :key="upload.id">
                                                    <div class="card">
                                                       
                                                        <div class="card-body">
                                                            <div class="row">
                                                                <div class="col">
                                                                    <a href="#" @click.prevent="confirmDeletion(upload)"><vue-feather type="trash"></vue-feather></a>
                                                                </div>
                                                                
                                                            </div>
                                                            <div class="row">
                                                                <div class="col mt-0">
                                                                    <img :src='upload.file' alt="" class="w-100">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- imagem -->
                                            </div>


                                            <div class="col-xl-12 col-xxl-12 d-flex">
                                                <div class="w-100">

                                                    <Form @submit="editFunction" v-slot="{ errors }">
                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="mcscr_status_id">Ficheiro:</label>
                                                                <FileUpload mode="basic"  class="form-control" name="image" accept="image/*" auto :maxFileSize="1000000" customUpload @uploader="onFileUpload" />
                                                            </div>
                                                        </div>
                                                        <button type="submit" class="btn btn-primary" :disabled="loadingButtonSubmit == true">
                                                            <div v-if="loadingButtonSubmit == true" class="spinner-border spinner-border-sm" role="status"></div>
                                                            <span v-else>Submeter</span>
                                                        </button>
                                                    </Form>
                                                    
                                            
                                                
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


    <div class="modal" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">Deseja mesmo eliminar este item.</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Ao apagar este item, irá apagar todos os registros relacionados a ele.
                </div>
                <div class="modal-footer">
                
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                        <button @click.prevent="deleteData" type="button" class="btn btn-danger" :disabled="loadingButtonDelete">
                            <div v-if="loadingButtonDelete" class="spinner-border spinner-border-sm" role="status"></div>
                            <span v-else>Apagar registro</span>
                        </button>
                </div>
            </div>
        </div>
  </div>
</template>