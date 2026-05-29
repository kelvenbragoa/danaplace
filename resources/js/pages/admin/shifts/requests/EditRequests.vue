<script setup>

import axios from 'axios';
import { ref, onMounted, reactive, defineEmits, defineComponent,watch } from "vue";
import moment from 'moment'
import {useToastr} from '../../../../toastr';
import {debounce} from 'lodash';
import {Form, Field} from 'vee-validate';
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
let shiftrequest= ref([])
let shift = ref([])
let users = ref([])
const schema = yup.object({
  
});







const getData = () => {
  axios.get(`/shiftequipmentrequestitem/${router.currentRoute.value.params.id}/edit`)
       .then((response)=>{

        loadingDiv.value=false;
        retrievedData.value = response.data.requestitem;
        shiftrequest.value =  response.data.shiftrequest;
        shift.value =  response.data.shift;
        users.value =  response.data.users;

       }).catch(()=>{

        loadingDiv.value=false;

       })
}

const editFunction = (values, actions) => {

  loadingButtonSubmit.value = true;
  axios.patch(`/shiftequipmentrequestitem/${retrievedData.value.id}`,values).then((response)=>{

    // admins.value.unshift(response.data);
    // $('#createCategory').modal('hide');
    actions.resetForm();
    router.push({ path: '/admin/shifts/request/'+retrievedData.value.shift_equipment_request_id });
    toastr.success('Registro editada com sucesso');

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

        <h1 class="h3 mb-3">Requisições Equipamento</h1>
        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Requisições Equipamento:{{ shift.name }}</h5>

                                        <router-link :to='"/admin/shifts/request/"+retrievedData.shift_equipment_request_id' class="btn btn-pill btn-primary mt-3"><vue-feather type="arrow-left"></vue-feather>Voltar</router-link> 

                                       
								    </div>
                                    
                                    <div class="card-body">
                                          
                                        <div class="row">
                                            <div class="col-xl-12 col-xxl-12 d-flex">
                                                <div class="w-100">
                                                    <Form @submit="editFunction" :validation-schema="schema" v-slot="{ errors }">
                                                    
                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="name">Planeamento</label>
                                                                <span class="form-control"> {{ shift.name }} </span>
                                                                <span class="invalid-feedback">{{ errors.name }}</span>
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="name">Frota</label>
                                                                <span class="form-control"> {{ shiftrequest.typeequipment.name }} </span>
                                                                <span class="invalid-feedback">{{ errors.name }}</span>
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="name">Equipamento</label>
                                                                <span class="form-control"> {{ retrievedData.equipment.ref }} </span>
                                                                <span class="invalid-feedback">{{ errors.name }}</span>
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="operator_user_id">Operador do Equipamento</label>
                                                                <Field as="select" class="form-control" :class="{'is-invalid':errors.operator_user_id}" name="operator_user_id" v-model="retrievedData.operator_user_id" id="operator_user_id" placeholder="Operador">
                                                                    <option>Selecionar</option>
                                                                    <option v-for="user in users" :key="user.id" :value="user.id">{{ user.user.firstName }} {{ user.user.lastName }}</option>
                                                                </Field>
                                                                <span class="invalid-feedback">{{ errors.operator_user_id }}</span>
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="petrol">Combustivel</label>
                                                                <Field type="text" class="form-control" :class="{'is-invalid':errors.petrol}" name="petrol" v-model="retrievedData.petrol" id="petrol" placeholder="Combustivel"/>
                                                                <span class="invalid-feedback">{{ errors.petrol }}</span>
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="moves">Moves</label>
                                                                <Field type="text" class="form-control" :class="{'is-invalid':errors.moves}" name="moves" v-model="retrievedData.moves" id="moves" placeholder="Moves"/>
                                                                <span class="invalid-feedback">{{ errors.moves }}</span>
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="ton">Toneladas</label>
                                                                <Field type="text" class="form-control" :class="{'is-invalid':errors.ton}" name="ton" v-model="retrievedData.ton" id="ton" placeholder="Toneladas"/>
                                                                <span class="invalid-feedback">{{ errors.ton }}</span>
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="distance">Distância percorida</label>
                                                                <Field type="text" class="form-control" :class="{'is-invalid':errors.distance}" name="distance" v-model="retrievedData.distance" id="distance" placeholder="Distância"/>
                                                                <span class="invalid-feedback">{{ errors.distance }}</span>
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="obs">Observação</label>
                                                                <Field type="text" class="form-control" :class="{'is-invalid':errors.obs}" name="obs" v-model="retrievedData.obs" id="obs" placeholder="Observação"/>
                                                                <span class="invalid-feedback">{{ errors.obs }}</span>
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
</template>