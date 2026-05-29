<script setup>

import {onMounted, ref, reactive,watch} from 'vue';
import axios from 'axios';
import {useToastr} from '../../../toastr';
import {Form, Field,FieldArray  } from 'vee-validate';
import * as yup from 'yup';
import { useRouter } from "vue-router";
import moment from 'moment'
import {debounce} from 'lodash';
import { Bootstrap4Pagination } from 'laravel-vue-pagination';
import VueFeather from 'vue-feather';

let retrievedData =ref([]);
const loading = ref(false);
let loadingButtonSubmit =ref([false]);
let loadingDiv =ref([true]);
const toastr = useToastr();
const router = useRouter();
let expenses =ref([]);
let self = this;
let currentvalue = ref([]);

const loadingButtonDelete = ref(false);

let dataIdBeingDeleted = ref(null);

const schema = yup.object({
    destination: yup.string().required(),
    departure_date:yup.string().required(),
    return_date:yup.string().required(),
    name:yup.string().required(),
});





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

axios.delete(`/tripexpenses/${dataIdBeingDeleted}`)
.then(()=>{
 expenses.value = expenses.value.filter(data=>data.id !== dataIdBeingDeleted); 
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

const getData = () => {
  axios.get(`/trips/+${router.currentRoute.value.params.id}/edit`)
       .then((response)=>{

        loadingDiv.value=false;
        retrievedData.value = response.data.trips;
        expenses.value = response.data.expenses;

       }).catch(()=>{

        loadingDiv.value=false;

       })
}

const editFunction = (values, actions) => {

  loadingButtonSubmit.value = true;
  axios.patch(`/trips/${retrievedData.value.id}`,values).then((response)=>{

    // admins.value.unshift(response.data);
    // $('#createCategory').modal('hide');
    actions.resetForm();
    router.push({ path: '/admin/trips' });
    toastr.success('Viagem editada com sucesso');

  }).catch((error)=>{

    loadingButtonSubmit.value = false;
    toastr.error('Erro ao adicionar');
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

        <h1 class="h3 mb-3">Viagem </h1>
        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Viagem: {{ retrievedData.name }}</h5>

                                        <router-link to="/admin/trips" class="btn btn-pill btn-primary mt-3"><vue-feather type="arrow-left"></vue-feather>Voltar</router-link> 

                                       
								    </div>
                                    
                                    <div class="card-body">
                                          
                                        <div class="row">
                                            <div class="col-xl-12 col-xxl-12 d-flex">
                                                <div class="w-100">
                                               

                                                    <Form @submit="editFunction" :validation-schema="schema" v-slot="{ errors }">

                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="destination">Destino</label>
                                                                <Field type="text" class="form-control" :class="{'is-invalid':errors.destination}" name="destination" id="destination" v-model="retrievedData.destination" placeholder="Destino"/>
                                                                <span class="invalid-feedback">{{ errors.destination }}</span>
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="name">Nome</label>
                                                                <Field type="text" class="form-control" :class="{'is-invalid':errors.name}" name="name" id="name" v-model="retrievedData.name" placeholder="Nome"/>
                                                                <span class="invalid-feedback">{{ errors.name }}</span>
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="departure_date">Data de partida:</label>
                                                                <Field type="date" class="form-control" :class="{'is-invalid':errors.departure_date}" name="departure_date" v-model="retrievedData.departure_date"  id="departure_date" placeholder="Data de partida" required/>
                                                                <span class="invalid-feedback">{{ errors.departure_date }}</span>
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="return_date">Data de Retorno:</label>
                                                                <Field type="date" class="form-control" :class="{'is-invalid':errors.return_date}" name="return_date" v-model="retrievedData.return_date"  id="return_date" placeholder="Data de partida" required/>
                                                                <span class="invalid-feedback">{{ errors.return_date }}</span>
                                                            </div>
                                                        </div>


                                                        <div class="row">
                                                            <FieldArray class="form-control" name="trip" v-slot="{ fields, push, remove }">
                                                                <div class="card-body">
                                                                    <button type="button" class="btn btn-pill btn-info mt-2" @click="push({ })">Adicionar Linha +</button>
                                                                </div>
                                                                <fieldset class="InputGroup" v-for="(field, idx) in fields" :key="field.key">
                                                                        <div class="row">
                                                                            <div class="mb-3 col-md-3">
                                                                                <label class="form-label">Name:</label>
                                                                                <Field type="text" class="form-control" :class="{'is-invalid':errors.expense_name}" :name="`trip[${idx}].expense_name`" id="expense_name" placeholder="Nome"/>
                                                                                <span class="invalid-feedback">{{ errors.expense_name }}</span>
                                                                            </div>
                                                                            <div class="mb-3 col-md-3">
                                                                                <label class="form-label">Descrição:</label>
                                                                                <Field type="text" class="form-control" :class="{'is-invalid':errors.expense_description}" :name="`trip[${idx}].expense_description`" id="expense_description" placeholder="Descrição"/>
                                                                                <span class="invalid-feedback">{{ errors.expense_description }}</span>
                                                                            </div>
                                                                            <div class="mb-3 col-md-3">
                                                                                <label class="form-label">Preço:</label>
                                                                                <Field type="number" class="form-control" :class="{'is-invalid':errors.amount}" :name="`trip[${idx}].amount`" id="amount" placeholder="Valor"/>
                                                                                <span class="invalid-feedback">{{ errors.amount }}</span>
                                                                            </div> 
                                                                            <div class="col-sm-3">
                                                                                <button type="button" class="btn btn-pill btn-danger mt-4" @click="remove(idx>1 ? idx : 0 )">X</button>
                                                                            </div>
                                                                        </div>
                                                                </fieldset>  
                                                            </FieldArray>
                                                        </div>

                                                        <div class="table-responsive">
                                                            <table class="table table-striped">
                                                                <thead>
                                                                    <tr>
                                                                        <th>#</th>
                                                                        <th>Nome</th>
                                                                        <th>Descrição</th>
                                                                        <th>Valor</th>
                                                                        <th>Ações</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody v-if="expenses.length > 0">
                                                                    <tr  v-for="(actualData,index) in expenses" :key="actualData.id">
                                                                        <td>#{{ index + 1 }}</td>
                                                                        <td>{{ actualData.name}}</td>
                                                                        <td>{{ actualData.description}}</td>
                                                                        <td>{{ actualData.amount}} MT</td>
                                                                        <td>
                                                                            <a href="#" @click.prevent="confirmDeletion(actualData)"><vue-feather type="trash"></vue-feather></a>   
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