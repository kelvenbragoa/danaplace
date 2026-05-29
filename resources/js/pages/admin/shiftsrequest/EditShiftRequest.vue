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
let equipments = ref([])

const createdby = ref('')

const approvedby = ref('')

const deliveredby = ref('')



const schema = yup.object({
    delivered_quantity: yup.string().required(),

});



const getData = () => {
  axios.get(`/shiftequipmentrequest/+${router.currentRoute.value.params.id}/edit`)
       .then((response)=>{

        loadingDiv.value=false;
        retrievedData.value = response.data.request;
        equipments.value = response.data.equipments;
       

       }).catch(()=>{

        loadingDiv.value=false;

       })
}

const editFunction = (values, actions) => {

  loadingButtonSubmit.value = true;
  axios.patch(`/shiftequipmentrequest/${retrievedData.value.id}`,values).then((response)=>{

    // admins.value.unshift(response.data);
    // $('#createCategory').modal('hide');
    actions.resetForm();
    router.push({ path: '/admin/shiftequipmentrequest' });
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
                                        <h5 class="card-title">Requisição:#{{ retrievedData.id }}</h5>

                                        <router-link to="/admin/shiftequipmentrequest" class="btn btn-pill btn-primary mt-3"><vue-feather type="arrow-left"></vue-feather>Voltar</router-link> 

                                       
								    </div>
                                    
                                    <div class="card-body">
                                          
                                        <div class="row">
                                            <div class="col-xl-12 col-xxl-12 d-flex">
                                                <div class="w-100">
                                                    <Form @submit="editFunction" :validation-schema="schema" v-slot="{ errors }">

                                                        <p><strong>Estado: </strong>
                                                            <span v-if="retrievedData.status == 0" class="badge bg-danger">
                                                                Pendente
                                                            </span>
                                                            <span v-if="retrievedData.status == 1" class="badge bg-success">
                                                                Respondido
                                                            </span>
                                                           
                                                        </p>


                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="opened_by_user_id">Planeamento Turno:</label>
                                                                <span class="form-control">{{ retrievedData.shift.name }}</span>
                                                                
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="opened_by_user_id">Frota:</label>
                                                                <span class="form-control">{{ retrievedData.typeequipment.name }}</span>
                                                                
                                                            </div>
                                                        </div>
                                                       
                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="opened_by_user_id">Data:</label>
                                                                <span class="form-control">{{ moment(retrievedData.created_at).format('DD-MM-YYYY H:mm') }}</span>
                                                                
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="opened_by_user_id">Criado por:</label>
                                                                <span class="form-control">{{ retrievedData.createdbyuser.firstName }} {{ retrievedData.createdbyuser.lastName }}</span>
                                                                
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="opened_by_user_id">Quantidade Requisita:</label>
                                                                <span class="form-control">{{ retrievedData.request_quantity }}</span>
                                                                
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="delivered_quantity">Quantidade Entregue:</label>
                                                                <Field type="number" class="form-control" :class="{'is-invalid':errors.delivered_quantity}" name="delivered_quantity" id="delivered_quantity" placeholder="Quantidade Entregue"/>
                                                                <span class="invalid-feedback">{{ errors.delivered_quantity }}</span>
                                                            </div>
                                                        </div>

                                                       
                                                        
                                                        <hr>
                                                        <h5 class="card-title">Departamento Requisitados</h5>
                                                        <div class="table-responsive">
                                                            <table class="table table-striped">
                                                                <thead>
                                                                    <tr>
                                                                        <th>#</th>
                                                                        <th>Frota</th>
                                                                        <th>Equipamento</th>
                                                                       
                                                                    </tr>
                                                                </thead>
                                                                <tbody v-if="retrievedData.request_quantity > 0">
                                                                    <FieldArray class="form-control" name="requestequipmentitem">
                                                                        <tr  v-for="(actualData,index) in retrievedData.request_quantity" :key="actualData.id">
                                                                            <td>#{{ index + 1 }}</td>
                                                                            <td>{{ retrievedData.typeequipment.name }}</td>
                                                                            
                                                                            <td>
                                                                                
                                                                                <Field as="select" class="form-control" :name="`requestequipmentitem[${index}].equipment_id`" aria-describedby="equipment_id" >
                                                                        
                                                                                    <option v-for="equipment in  equipments" :key="equipment.id" :value="equipment.id">{{ equipment.ref }}</option>
                                                                                   
                                                                                </Field>
                                                                                
                                                                            </td>
                                                                        </tr>
                                                                    </FieldArray>
                                                                </tbody>
                                                                <tbody v-else>
                                                                    <tr>
                                                                    <td colspan="3" align="center">Nenhum resultado encontrado</td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>


                                   
                                                        <Field type="hidden" class="form-control" name="request_equipment_id" v-model="retrievedData.id"/>
                                                        <button type="submit" class="btn btn-primary" :disabled="loadingButtonSubmit == true" >
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
</template>