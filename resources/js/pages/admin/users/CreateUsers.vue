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
let roles =ref([]);
let areas =ref([]);
let destinations =ref([]);
let currentvalue = ref([]);
let provinces =ref([]);
let cities =ref([]);
let user_statuses =ref([]);
let account_statuses =ref([]);
const loadingDiv = ref(true);
const province_id_to_city = ref(0);
const user_role = ref(0);

const schema = yup.object({
    
  firstName: yup.string().required(),
  lastName: yup.string().required(),
  email: yup.string().email().required(),
  password: yup.string().required().min(8),
  address: yup.string().required(),
  code: yup.string().required(),
  bi: yup.string().required(),
  mobile: yup.string().required(),
  cellphone: yup.string().required(),
  province_id: yup.string().required(),
  city_id: yup.string().required(),
  role_id: yup.string().required(),
  user_status_id: yup.string().required(),
  account_status_id: yup.string().required(),
//   signature: yup.string().required(),
//   area_id: yup.string().required(),  
//   destination_id: yup.string().required(), 
  
});
let self = this;
const router = useRouter();

const createRecordFunction = (values, actions) => {


    currentvalue.value = {values};

    loading.value = true;

    const arr = Array.from(values)

    console.log(arr)
    console.log(values)
    
    axios.post('/users',values).then((response)=>{

    // categories.value.unshift(response.data);
    // $('#createCategory').modal('hide');
    actions.resetForm();
    router.push({ path: '/admin/users' });
    toastr.success('Usuário criado com sucesso');
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



const getAuxiliarData = () => {

  axios.get('/auxiliar-create-users')
       .then((response)=>{

        roles.value = response.data.roles;
        areas.value = response.data.areas;
        destinations.value = response.data.destinations;
        provinces.value = response.data.provinces;
        user_statuses.value = response.data.user_statuses;
        account_statuses.value = response.data.account_statuses;
        loadingDiv.value=false;

       


       })
       .catch((error)=>{
        toastr.error(error);
        router.push({ path: '/admin/users' });
       })
}

const getCity = (city) => {

    axios.get(`/auxiliar-create-users/${city}`)
       .then((response)=>{

        cities.value = response.data.cities;
       })
       .catch((error)=>{
        toastr.error(error);
        router.push({ path: '/admin/users' });
       })
}

// const formobjective = reactive([
//     {id: ''}
// ]);
// const addRow = () =>{
//     formobjective.push({id:''})
// }
// const removeRow = (index) =>{
//     if(formobjective.length>1){
//         formobjective.splice(index,1)
//     }
    
// }

onMounted(()=>{
    getAuxiliarData()
})
</script>

<template>
    <div v-if="!loadingDiv">
        <h1 class="h3 mb-3">Usuários</h1>
        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Formulário criação dos usuários do sistema.</h5>

                                        <router-link to="/admin/users" class="btn btn-pill btn-primary mt-3"><vue-feather type="arrow-left"></vue-feather>Voltar</router-link> 

                                       
								    </div>
                                    
                                    <div class="card-body">
                                            <Form @submit="createRecordFunction" :validation-schema="schema" v-slot="{ errors }">
												<div class="row">
													<div class="mb-3 col-md-6">
														<label class="form-label" for="firstName">Primeiro Nome</label>
														<Field type="text" class="form-control" :class="{'is-invalid':errors.firstName}" name="firstName" id="firstName" placeholder="Primeiro Nome"/>
                                                        <span class="invalid-feedback">{{ errors.firstName }}</span>
													</div>
													<div class="mb-3 col-md-6">
														<label class="form-label" for="lastName">Apelido</label>
														<Field type="text" class="form-control" :class="{'is-invalid':errors.lastName}" name="lastName" id="lastName" placeholder="Apelido"/>
                                                        <span class="invalid-feedback">{{ errors.lastName }}</span>
													</div>
												</div>
												<div class="mb-3">
													<label class="form-label" for="email">Email</label>
													<Field type="email" class="form-control"  :class="{'is-invalid':errors.email}" name="email" id="email" placeholder="Email"/>
                                                    <span class="invalid-feedback">{{ errors.email }}</span>
												</div>
                                                <div class="mb-3">
													<label class="form-label" for="password">Palavra passe</label>
													<Field type="password" class="form-control"  :class="{'is-invalid':errors.password}" name="password" id="password" placeholder="Palavra passe"/>
                                                    <span class="invalid-feedback">{{ errors.password }}</span>
												</div>
												<div class="mb-3">
													<label class="form-label" for="address">Endereço</label>
													<Field type="text" class="form-control" id="address" :class="{'is-invalid':errors.address}" name="address"  placeholder="Endereço"/>
                                                    <span class="invalid-feedback">{{ errors.address }}</span>
												</div>
												<div class="mb-3">
													<label class="form-label" for="code">Código</label>
													<Field type="text" class="form-control" :class="{'is-invalid':errors.code}" name="code" id="code" placeholder="Código do Usuário"/>
                                                    <span class="invalid-feedback">{{ errors.code }}</span>
												</div>
                                                <div class="mb-3">
													<label class="form-label" for="bi">BI</label>
													<Field type="text" class="form-control" :class="{'is-invalid':errors.bi}" name="bi" id="bi" placeholder="BI"/>
                                                    <span class="invalid-feedback">{{ errors.bi }}</span>
												</div>
                                                <div class="mb-3">
													<label class="form-label" for="mobile">Telefone</label>
													<Field type="text" class="form-control" :class="{'is-invalid':errors.mobile}" name="mobile" id="mobile" placeholder="Telefone"/>
                                                    <span class="invalid-feedback">{{ errors.mobile }}</span>
												</div>
                                                <div class="mb-3">
													<label class="form-label" for="cellphone">Celular</label>
													<Field type="text" class="form-control" :class="{'is-invalid':errors.cellphone}" name="cellphone" id="cellphone" placeholder="Celular"/>
                                                    <span class="invalid-feedback">{{ errors.cellphone }}</span>
												</div>
												<div class="row">
													<div class="mb-3 col-md-6">
														<label class="form-label" for="province_id">Província</label>
														<Field as="select" class="form-control" :class="{'is-invalid':errors.province_id}"  name="province_id" id="province_id" aria-describedby="province_id" @change="getCity(province_id_to_city)" v-model="province_id_to_city">
                                                            <option value="" disabled>Selecionar</option>
                                                            <option v-for="province in provinces" :key="province.id" :value="province.id">{{ province.name }}</option>
                                                        </Field>
                                                        <span class="invalid-feedback">{{ errors.province_id }}</span>

													</div>
													<div class="mb-3 col-md-4">
														<label class="form-label" for="city_id">Cidade</label>
														<Field as="select" class="form-control" :class="{'is-invalid':errors.city_id}"  name="city_id" id="city_id" aria-describedby="cityId">
                                                            <option value="" disabled>Selecionar</option>
                                                            <option v-for="city in cities" :key="city.id" :value="city.id">{{ city.name }}</option>
                                                        </Field>
                                                        <span class="invalid-feedback">{{ errors.city_id }}</span>
													</div>
													<div class="mb-3 col-md-4">
														<label class="form-label" for="role_id">Nível</label>
														<Field as="select" class="form-control" :class="{'is-invalid':errors.role_id}"  name="role_id" id="role_id" aria-describedby="role_id" v-model="user_role">
                                                            <option value="" disabled>Selecionar</option>
                                                            <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.name }}</option>
                                                        </Field>
                                                        <span class="invalid-feedback">{{ errors.role_id }}</span>
													</div>

                                                    <div class="mb-3 col-md-4">
														<label class="form-label" for="user_status_id">Estado do usuário</label>
														<Field as="select" class="form-control" :class="{'is-invalid':errors.user_status_id}"  name="user_status_id" id="user_status_id" aria-describedby="user_status_id">
                                                            <option value="" disabled>Selecionar</option>
                                                            <option v-for="user_status in user_statuses" :key="user_status.id" :value="user_status.id">{{ user_status.name }}</option>
                                                        </Field>
                                                        <span class="invalid-feedback">{{ errors.user_status_id }}</span>
													</div>

                                                    <div class="mb-3 col-md-4">
														<label class="form-label" for="account_status_id">Estado da conta do usuário</label>
														<Field as="select" class="form-control" :class="{'is-invalid':errors.account_status_id}"  name="account_status_id" id="account_status_id" aria-describedby="account_status_id">
                                                            <option value="" disabled>Selecionar</option>
                                                            <option v-for="account_status in account_statuses" :key="account_status.id" :value="account_status.id">{{ account_status.name }}</option>
                                                        </Field>
                                                        <span class="invalid-feedback">{{ errors.account_status_id }}</span>
													</div>
												</div>
                                                <!-- <div class="mb-3">
													<label class="form-label" for="signature">Assinatura</label>
													<Field type="text" class="form-control" :class="{'is-invalid':errors.signature}" name="signature" id="signature" placeholder="Assinatura"/>
                                                    <span class="invalid-feedback">{{ errors.signature }}</span>
												</div> -->

                                                <div class="mb-3" v-if="user_role == 5 || user_role == 6 || user_role == 7">
													<label class="form-label" for="area_id">Area</label>
													<Field as="select" class="form-control" :class="{'is-invalid':errors.area_id}"  name="area_id" id="area_id" aria-describedby="area_id">
                                                        <option value="" disabled>Selecionar</option>
                                                        <option v-for="area in areas" :key="area.id" :value="area.id">{{ area.name }}</option>
                                                    </Field>
                                                    <span class="invalid-feedback">{{ errors.area_id }}</span>
												</div>

                                                <div class="mb-3" v-if="user_role == 8 || user_role == 9 || user_role == 10">
													<label class="form-label" for="destination_id">Destino</label>
													<Field as="select" class="form-control" :class="{'is-invalid':errors.destination_id}"  name="destination_id" id="destination_id" aria-describedby="destination_id">
                                                        <option value="" disabled>Selecionar</option>
                                                        <option v-for="destination in destinations" :key="destination.id" :value="destination.id">{{ destination.name }}</option>
                                                    </Field>
                                                    <span class="invalid-feedback">{{ errors.destination_id }}</span>
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