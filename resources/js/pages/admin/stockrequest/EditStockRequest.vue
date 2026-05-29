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

let retrievedData =ref([]);
let loadingButtonSubmit =ref([false]);
let loadingDiv =ref([true]);
const toastr = useToastr();
const router = useRouter();
let self = this;
let currentvalue = ref([]);
let materials = ref([])

const createdby = ref('')

const approvedby = ref('')

const deliveredby = ref('')



const schema = yup.object({
    request_status: yup.string().required(),

});



const getData = () => {
  axios.get(`/stockrequests/+${router.currentRoute.value.params.id}/edit`)
       .then((response)=>{

        loadingDiv.value=false;
        retrievedData.value = response.data.requeststock;
        materials.value = response.data.materials;

        createdby.value = response.data.requeststock.createdbyuser.firstName+' '+ response.data.requeststock.createdbyuser.lastName
        approvedby.value = response.data.requeststock.approvedbyuser.firstName+' '+ response.data.requeststock.approvedbyuser.lastName
        deliveredby.value = response.data.requeststock.approvedbyuser.firstName+' '+ response.data.requeststock.approvedbyuser.lastName

       }).catch(()=>{

        loadingDiv.value=false;

       })
}

const editFunction = (values, actions) => {

  loadingButtonSubmit.value = true;
  axios.patch(`/stockrequests/${retrievedData.value.id}`,values).then((response)=>{

    // admins.value.unshift(response.data);
    // $('#createCategory').modal('hide');
    actions.resetForm();
    router.push({ path: '/admin/stockrequests' });
    toastr.success('Requisição editada com sucesso');

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

        <h1 class="h3 mb-3">Requisição</h1>
        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Requisição:</h5>

                                        <router-link to="/admin/stockrequests" class="btn btn-pill btn-primary mt-3"><vue-feather type="arrow-left"></vue-feather>Voltar</router-link> 

                                       
								    </div>
                                    
                                    <div class="card-body">
                                          
                                        <div class="row">
                                            <div class="col-xl-12 col-xxl-12 d-flex">
                                                <div class="w-100">
                                                    <Form @submit="editFunction" :validation-schema="schema" v-slot="{ errors }">

                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="opened_by_user_id">Criado/Aberto por:</label>
                                                                <Field type="text" class="form-control" v-model="createdby"   readonly name="createdby"/>
                                                                
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="opened_by_user_id">Aprovado/Reprovado por:</label>
                                                                <Field type="text" class="form-control" v-model="approvedby"  readonly name="approbedby" />
                                                                
                                                            </div>
                                                        </div>

                                                        <div class="row"> 
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="opened_by_user_id">Entregue por:</label>
                                                                <Field type="text" class="form-control" v-model="deliveredby"  readonly name="deliveredby"/>
                                                               
                                                            </div>
                                                        </div>
                                                        <p><strong>Estado: </strong>
                                                            <span v-if="retrievedData.request_stock_status_id == 1" class="badge bg-warning">
                                                                {{ retrievedData.status.name}}
                                                            </span>
                                                            <span v-if="retrievedData.request_stock_status_id == 2" class="badge bg-success">
                                                                {{ retrievedData.status.name}}
                                                            </span>
                                                            <span v-if="retrievedData.request_stock_status_id == 3" class="badge bg-danger">
                                                                {{ retrievedData.status.name}}
                                                            </span>
                                                            <span v-if="retrievedData.request_stock_status_id == 4" class="badge bg-info">
                                                                {{ retrievedData.status.name}}
                                                            </span>    
                                                        </p>

                                                        <hr>
                                                        <h5 class="card-title">Materiais Requisitados</h5>
                                                        <div class="table-responsive">
                                                            <table class="table table-striped">
                                                                <thead>
                                                                    <tr>
                                                                        <th>#</th>
                                                                        <th>Material</th>
                                                                        <th>Stock Atual</th>
                                                                        <th>Quantidade Requisitada</th>
                                                                        <th>Quantidade Entregue</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody v-if="materials.length > 0">
                                                                    <FieldArray class="form-control" name="requeststockitens">
                                                                        <tr  v-for="(actualData,index) in materials" :key="actualData.id">
                                                                            <td>#{{ index + 1 }}</td>
                                                                            <td>{{ actualData.product.name }}</td>
                                                                            <td>{{ actualData.product.quantity }}</td>
                                                                            <td>{{ actualData.quantity }}</td>
                                                                            <td>
                                                                                <Field type="number" class="form-control" :name="`requeststockitens[${index}].quantity`" v-if="retrievedData.request_stock_status_id==2"/>
                                                                                <Field type="hidden" class="form-control" :name="`requeststockitens[${index}].item_id`" v-model="actualData.id"/>
                                                                            </td>
                                                                        </tr>
                                                                    </FieldArray>
                                                                </tbody>
                                                                <tbody v-else>
                                                                    <tr>
                                                                    <td colspan="8" align="center">Nenhum resultado encontrado</td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>


                                                        <div v-if="retrievedData.request_stock_status_id!=3">
                                                            <div class="row">
                                                                <div class="mb-3 col-md-12">
                                                                    <label class="form-label" for="request_status">Estado:</label>
                                                                    <Field as="select" class="form-control" :class="{'is-invalid':errors.request_status}"  name="request_status" id="request_status" aria-describedby="request_status">
                                                                        
                                                                        <option v-if="retrievedData.request_stock_status_id!=2" value="2">Aprovar</option>
                                                                        <option v-if="retrievedData.request_stock_status_id!=2" value="3">Rejeitar</option>
                                                                        <option v-if="retrievedData.request_stock_status_id==2" value="4">Entregue</option>
                                                                    </Field>
                                                                    <span class="invalid-feedback">{{ errors.request_status }}</span>
                                                                    
                                                                </div>
                                                            </div>

                                                            <button type="submit" class="btn btn-primary" :disabled="loadingButtonSubmit == true" v-if="retrievedData.request_stock_status_id!=3">
                                                                <div v-if="loadingButtonSubmit == true" class="spinner-border spinner-border-sm" role="status"></div>
                                                                <span v-else>Submeter</span>
                                                            </button>
                                                        </div>


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
</template>