<script setup>

import {onMounted, ref, reactive,watch} from 'vue';
import axios from 'axios';
import {useToastr} from '../../../toastr';
import moment from 'moment'
import {debounce} from 'lodash';
import { Bootstrap4Pagination } from 'laravel-vue-pagination';
import VueFeather from 'vue-feather';
import {Form, Field,FieldArray  } from 'vee-validate';
import FileUpload from 'primevue/fileupload';


const toastr = useToastr();
const searchQuery = ref(null);
const loadingDiv = ref(true);
const loadingButtonDelete = ref(false);
let loadingButtonSubmit =ref([false]);
let image = ref();
let signature = ref();

const options = ref({
    penColor: "rgb(0, 0, 0)",
    backgroundColor: "rgb(255,255,255)"
})
const undo = () => {
  signature.value.undo();
}
const clear = () => {
  signature.value.clear()
}
const save = async (values)  => {
//   console.log(signature.value.save("image/jpeg"))
  loadingButtonSubmit.value = true;
  values.pad="pad"

    const base64Data = signature.value.save("image/jpeg");
    const base64 = await fetch(base64Data);
    const base64Response = await fetch(`${base64Data}`);
    const blob = await base64Response.blob();




  values.image = blob;
  values.user_id = retriviedData.value.id;
  axios.post(`/profile/upload`,values,{
    headers: {
            'Content-Type': 'multipart/form-data'
          }
  }).then((response)=>{
    // admins.value.unshift(response.data);
    // $('#createCategory').modal('hide');
    retriviedData.value = response.data.user;
    toastr.success('Assinatura carregada com sucesso');

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
}

const onFileUpload = (event) => {
    image.value = event.files[0]; 
    console.log(image.value);
};

const editFunction = (values, actions) => {
  loadingButtonSubmit.value = true;
  values.image = image.value;
  values.user_id = retriviedData.value.id;
  axios.post(`/profile/upload`,values,{
    headers: {
            'Content-Type': 'multipart/form-data'
          }
  }).then((response)=>{
    // admins.value.unshift(response.data);
    // $('#createCategory').modal('hide');
    retriviedData.value = response.data.user;
    toastr.success('Assinatura carregada com sucesso');

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

const retriviedData = ref({'data': []})

let dataIdBeingDeleted = ref(null);


 const getData = async (page = 1) => {
  axios.get(`/profile`,
      {
        params:{
          query: searchQuery.value
        }
      })
       .then((response)=>{
        retriviedData.value = response.data.user;
        loadingDiv.value=false;

        
       })

       
}

const confirmDeletion = (data) => {

dataIdBeingDeleted = data.id;

$('#deleteModal').modal('show');
// axios.post('/categories',values).then((response)=>{

//   categories.value.unshift(response.data);
//   $('#createCategory').modal('hide');
//   resetForm();
// })
};

const deleteData = () =>{

loadingButtonDelete.value= true;

axios.delete(`/suppliers/${dataIdBeingDeleted}`)
.then(()=>{
 retriviedData.value.data = retriviedData.value.data.filter(data=>data.id !== dataIdBeingDeleted); 
 $('#deleteModal').modal('hide');

 toastr.success('Registro apagada com sucesso');

}).catch(()=>{
 toastr.error('Erro ao apagar');
 loadingButtonDelete.value= false;
 $('#deleteModal').modal('hide');
}).finally(()=>{
 loadingButtonDelete.value= false;
});
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
        <h1 class="h3 mb-3">Perfil</h1>
        
                        <div class="row">
                            <div class="col-md-3 col-xl-2">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Perfil</h5>
                                    </div>

                                    <div class="list-group list-group-flush" role="tablist">
                                        <a class="list-group-item list-group-item-action active" data-toggle="list" href="#account" role="tab">
                                           Conta
                                        </a>  
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-9 col-xl-10">
                                <div class="card">
                                    <div class="card-header">
                                        <div class="card-header">

                                        <h5 class="card-title mb-0">Informações</h5>
                                        </div>
								    </div>
                                    
                                    <div class="card-body">
                                        <Form>
												<div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="subject">Nome</label>
														<span class="form-control" readonly>{{ retriviedData.firstName }}</span>
													</div>
												</div>

                                                

                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="date">Apelido</label>
														<span class="form-control" readonly>{{ retriviedData.lastName }}</span>
													</div>
												</div>
                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="start_time">Telefone</label>
														<span class="form-control" readonly>{{ retriviedData.mobile }}</span>
													</div>
												</div>
                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="end_time">Nível</label>
														<span class="form-control" readonly>{{ retriviedData.role.name }}</span>
													</div>
												</div>

                                                <hr>

                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="end_time">Assinatura</label>
                                                        <div v-if="retriviedData.signature == null">
                                                            <small>Ainda não foi configurada uma assinatura</small>
                                                        </div>
                                                        <div v-else>
                                                            <div class="col">
                                                                <img :src='retriviedData.signature' alt="" class="w-100">
                                                            </div>
                                                        </div>
													</div>
												</div>
											</Form>
                                            <div class="card-body">
                                                <Form @submit="editFunction" v-slot="{ errors }">
                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="mcscr_status_id">Carregar Imagem da Assinatura:</label>
                                                                <FileUpload mode="basic"  class="form-control" name="image" accept="image/*" auto :maxFileSize="1000000" customUpload @uploader="onFileUpload" />
                                                            </div>
                                                        </div>
                                                        <button type="submit" class="btn btn-primary" :disabled="loadingButtonSubmit == true">
                                                            <div v-if="loadingButtonSubmit == true" class="spinner-border spinner-border-sm" role="status"></div>
                                                            <span v-else>Submeter</span>
                                                        </button>
                                                </Form>
                                            </div>
                                            

                                            <hr>
                                            <div class="card-body" style="background-color: antiquewhite;">
                                                <h5>Assine aqui:</h5>
                                                <Vue3Signature ref="signature" :sigOption="options" :w="'800px'" :h="'400px'"></Vue3Signature>
                                                <button @click="save" class="btn btn-primary" :disabled="loadingButtonSubmit == true">
                                                                <div v-if="loadingButtonSubmit == true" class="spinner-border spinner-border-sm" role="status"></div>
                                                                <span v-else>Submeter</span>
                                                </button>
                                                <button class="btn btn-primary ml-2" @click="undo" >
                                                                <span>Desfazer</span>
                                                </button>
                                                <button class="btn btn-primary ml-2" @click="clear">
                                                                <span>Limpar</span>
                                                </button>
                                            </div>
                                            
                                          
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