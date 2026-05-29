<script setup>

import {onMounted, ref, reactive,watch} from 'vue';
import axios from 'axios';
import {useToastr} from '../../../toastr';
import {Form, Field} from 'vee-validate';
import * as yup from 'yup';
import { useRouter } from "vue-router";
import moment from 'moment'
import {debounce} from 'lodash';
import { Bootstrap4Pagination } from 'laravel-vue-pagination';
import VueFeather from 'vue-feather';

const loading = ref(false);
const toastr = useToastr();
const loadingDiv = ref(false);
let currentvalue = ref([]);
const schema = yup.object({
    
  name: yup.string().required(),
 
});
let self = this;
const router = useRouter();

const createRecordFunction = (values, actions) => {

 
    currentvalue.value = {values};

    loading.value = true;

    const arr = Array.from(values)
    
    axios.post('/malfunctions',values).then((response)=>{

    // categories.value.unshift(response.data);
    // $('#createCategory').modal('hide');
    actions.resetForm();
    router.push({ path: '/admin/malfunctions' });
    toastr.success('Área criada com sucesso');
  }).catch((error)=>{
    
    loading.value = false;
    toastr.error('Erro ao adicionar. '+error.response.data.message);
    if(error.response.data.errors){
       
        actions.setErrors(error.response.data.errors);
    }
  }).finally(()=>{
    loading.value = false;
    
  })



};




</script>

<template>
    <div v-if="!loadingDiv">
        <h1 class="h3 mb-3">Tipo de avarias</h1>
        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Formulário criação dos tipos de avarias do sistema.</h5>

                                        <router-link to="/admin/malfunctions" class="btn btn-pill btn-primary mt-3"><vue-feather type="arrow-left"></vue-feather>Voltar</router-link> 

                                       
								    </div>
                                    
                                    <div class="card-body">
                                            <Form @submit="createRecordFunction" :validation-schema="schema" v-slot="{ errors }">
												<div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="name">Nome</label>
														<Field type="text" class="form-control" :class="{'is-invalid':errors.name}" name="name" id="name" placeholder="Nome"/>
                                                        <span class="invalid-feedback">{{ errors.name }}</span>
													</div>
												</div>
												

                                                

                                                
                                               
												<button type="submit" class="btn btn-primary" :disabled="loading">
                                                    <div v-if="loading" class="spinner-border spinner-border-sm" role="status"></div>
                                                    <span v-else>Submeter</span>
                                                </button>
											</Form>

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

<!-- 
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

axios.delete(`/mcscr/upload/${dataIdBeingDeleted.value}`)
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
  axios.get(`/mcscr/${router.currentRoute.value.params.id}/upload`)
       .then((response)=>{

        loadingDiv.value=false;
        retrievedData.value = response.data.mcscr;
        uploads.value = response.data.uploads;
       }).catch(()=>{

        loadingDiv.value=false;

       })
}

const editFunction = (values, actions) => {
  loadingButtonSubmit.value = true;
  values.image = image.value;
  axios.post(`/excel/upload`,values,{
    headers: {
            'Content-Type': 'multipart/form-data'
          }
  }).then((response)=>{
    // admins.value.unshift(response.data);
    // $('#createCategory').modal('hide');
    retrievedData.value = response.data.mcscr;
    uploads.value = response.data.uploads;
    actions.resetForm();
    toastr.success('MCSCR editado com sucesso');

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
  
//   getData();

})
</script>

<template>
    <div v-if="loadingDiv">

        <h1 class="h3 mb-3">MCSCR </h1>
        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">MCSCR: #{{ retrievedData.id }}</h5>


								    </div>
                                    
                                    <div class="card-body">
                                          
                                        <div class="row">
                                            


                                            <div class="col-xl-12 col-xxl-12 d-flex">
                                                <div class="w-100">

                                                    <Form @submit="editFunction" v-slot="{ errors }">
                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="mcscr_status_id">Ficheiro:</label>
                                                                <FileUpload mode="basic"  class="form-control" name="image"  auto  customUpload @uploader="onFileUpload" />
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
</template> -->