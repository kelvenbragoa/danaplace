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

const loading = ref(false);
const toastr = useToastr();
const loadingDiv = ref(true);
let currentvalue = ref([]);
let destinations =ref([]);
let destination_id_to_equipment = ref(0);
let equipments =ref([]);
const destination_id_company = ref(0);
let type_equipment_id_to_equipment = ref(0);
let type_equipments =ref([]);
let coins =ref([]);



const schema = yup.object({
    
  destination_id: yup.string().required(),
  expires_date:yup.string().required(),
 
});
let self = this;
const router = useRouter();

const getTypeEquipment = (destination_id_company) => {

axios.get(`/auxiliar-create-mcscr-type-equipment/${destination_id_company}`)
   .then((response)=>{

    type_equipments.value = response.data.type_equipments;
   })
   .catch((error)=>{
    toastr.error(error);
    router.push({ path: '/admin/quotation' });
   })
}

const getEquipment = (typeequipment) => {
axios.get(`/auxiliar-create-mcscr/${typeequipment}/${destination_id_company.value}`)
   .then((response)=>{

    equipments.value = response.data.equipments;
   })
   .catch((error)=>{
    toastr.error(error);
    router.push({ path: '/admin/quotation' });
   })
}

const createRecordFunction = (values, actions) => {

 
    currentvalue.value = {values};

    loading.value = true;

    const arr = Array.from(values)
    
    axios.post('/quotation',values).then((response)=>{

    // categories.value.unshift(response.data);
    // $('#createCategory').modal('hide');
    actions.resetForm();
    router.push({ path: '/admin/quotation' });
    toastr.success('Registro criada com sucesso');
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

axios.get('/auxiliar-create-mcscr')
     .then((response)=>{

      destinations.value = response.data.destinations
      coins.value = response.data.coins
      loadingDiv.value=false;

     


     })
     .catch((error)=>{
      toastr.error(error);
      router.push({ path: '/admin/quotation' });
     })
}



onMounted(()=>{
    getAuxiliarData()
})



</script>

<template>
    <div v-if="!loadingDiv">
        <h1 class="h3 mb-3">Cotações</h1>
        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Formulário criação de cotações do sistema.</h5>

                                        <router-link to="/admin/quotation" class="btn btn-pill btn-primary mt-3"><vue-feather type="arrow-left"></vue-feather>Voltar</router-link> 

                                       
								    </div>
                                    
                                    <div class="card-body">
                                            <Form @submit="createRecordFunction" :validation-schema="schema" v-slot="{ errors }">

                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="destination_id">Cliente/Clientes:</label>
														<Field as="select" class="form-control" :class="{'is-invalid':errors.destination_id}"  name="destination_id" id="destination_id" v-model="destination_id_company" @change="getTypeEquipment(destination_id_company)" aria-describedby="destination_id">
                                                            <option value="0">Selecionar</option>
                                                            <option v-for="destination in destinations" :key="destination.id" :value="destination.id">{{ destination.name }}</option>
                                                        </Field>
                                                        <span class="invalid-feedback">{{ errors.destination_id }}</span>
													</div>
												</div>

                                                <div v-if="destination_id_company != 0">
													<div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="type_equipment_id">Tipo de Equipamento/Ativos:</label>
														<Field as="select" class="form-control" :class="{'is-invalid':errors.type_equipment_id}"  name="type_equipment_id" id="type_equipment_id" aria-describedby="type_equipment_id" @change="getEquipment(type_equipment_id_to_equipment)" v-model="type_equipment_id_to_equipment">
                                                            <option value="" selected>Selecionar</option>
                                                            <option v-for="(type_equipment,index) in type_equipments" :key="type_equipment.id" :value="type_equipment[0].type_equipment_id">{{ index }}</option>
                                                        </Field>
                                                        <span class="invalid-feedback">{{ errors.type_equipment_id }}</span>
													</div>
												</div>


                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="equipment_id">Equipamentos/Ativos:</label>
														<Field as="select" class="form-control" :class="{'is-invalid':errors.equipment_id}"  name="equipment_id" id="equipment_id" aria-describedby="equipment_id">
                                                            <option value="" selected>Selecionar</option>
                                                            <option v-for="equipment in equipments" :key="equipment.id" :value="equipment.id">{{ equipment.ref }} - {{ equipment.name }}</option>
                                                        </Field>
                                                        <span class="invalid-feedback">{{ errors.equipment_id }}</span>
													</div>
												</div>
												</div>

                                                <hr>
                                                <div v-if="destination_id_company == 0">
                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="representative_name">Nome do Representante</label>
														<Field type="text" class="form-control" :class="{'is-invalid':errors.representative_name}" name="representative_name" id="representative_name" placeholder="Nome Representante"/>
                                                        <span class="invalid-feedback">{{ errors.representative_name }}</span>
													</div>
												</div>
                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="representative_mobile">Telefone do Representante</label>
														<Field type="text" class="form-control" :class="{'is-invalid':errors.representative_mobile}" name="representative_mobile" id="representative_mobile" placeholder="Telefone Representante"/>
                                                        <span class="invalid-feedback">{{ errors.representative_mobile }}</span>
													</div>
												</div>
                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="company_name">Nome Empresa</label>
														<Field type="text" class="form-control" :class="{'is-invalid':errors.company_name}" name="company_name" id="company_name" placeholder="Empresa"/>
                                                        <span class="invalid-feedback">{{ errors.company_name }}</span>
													</div>
												</div>
                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="company_address">Endereço da Empresa</label>
														<Field type="text" class="form-control" :class="{'is-invalid':errors.company_address}" name="company_address" id="company_address" placeholder="Endereço"/>
                                                        <span class="invalid-feedback">{{ errors.company_address }}</span>
													</div>
												</div>
                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="company_nuit">NUIT da Empresa</label>
														<Field type="text" class="form-control" :class="{'is-invalid':errors.company_nuit}" name="company_nuit" id="company_nuit" placeholder="NUIT"/>
                                                        <span class="invalid-feedback">{{ errors.company_nuit }}</span>
													</div>
												</div>
                                                
                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="province">Província</label>
                                                        <Field type="text" class="form-control" :class="{'is-invalid':errors.province}" name="province" id="province" placeholder="Provincia"/>

                                                        <span class="invalid-feedback">{{ errors.province }}</span>
													</div>
												</div>

                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="company_mobile">Telefone da Empresa</label>
														<Field type="text" class="form-control" :class="{'is-invalid':errors.company_mobile}" name="company_mobile" id="company_mobile" placeholder="Telefone"/>
                                                        <span class="invalid-feedback">{{ errors.company_mobile }}</span>
													</div>
												</div>

                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="company_email">Email da Empresa</label>
														<Field type="text" class="form-control" :class="{'is-invalid':errors.company_email}" name="company_email" id="company_email" placeholder="Email"/>
                                                        <span class="invalid-feedback">{{ errors.company_email }}</span>
													</div>
												</div>
                                            </div>



                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="expires_date">Expira em:</label>
														<Field type="date" class="form-control" :class="{'is-invalid':errors.expires_date}" name="expires_date" id="expires_date" placeholder="Expira em" required/>
                                                        <span class="invalid-feedback">{{ errors.expires_date }}</span>
													</div>
												</div>

                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="type_of_transport">Tipo de Transporte</label>
														<Field type="text" class="form-control" :class="{'is-invalid':errors.type_of_transport}" name="type_of_transport" id="type_of_transport" placeholder="Tipo de Transporte"/>
                                                        <span class="invalid-feedback">{{ errors.type_of_transport }}</span>
													</div>
												</div>

                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="coin_id">Moeda:</label>
														<Field as="select" class="form-control" :class="{'is-invalid':errors.coin_id}"  name="coin_id" id="coin_id" aria-describedby="coin_id">
                                                            <option value="" selected>Selecionar</option>
                                                            <option v-for="coin in coins" :key="coin.id" :value="coin.id">{{ coin.name }}</option>
                                                        </Field>
                                                        <span class="invalid-feedback">{{ errors.coin_id }}</span>
													</div>
												</div>

                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="payment_method">Método de Pagamento</label>
														<Field type="text" class="form-control" :class="{'is-invalid':errors.payment_method}" name="payment_method" id="payment_method" placeholder="Metodo de Pagamento"/>
                                                        <span class="invalid-feedback">{{ errors.payment_method }}</span>
													</div>
												</div>

                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="warranty">Periodo de Garantia</label>
														<Field type="date" class="form-control" :class="{'is-invalid':errors.warranty}" name="warranty" id="warranty" placeholder="Periodo de Garantia"/>
                                                        <span class="invalid-feedback">{{ errors.warranty }}</span>
													</div>
												</div>

                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="delivery_date">Data de Entrega</label>
														<Field type="date" class="form-control" :class="{'is-invalid':errors.delivery_date}" name="delivery_date" id="delivery_date" placeholder="Periodo de Garantia"/>
                                                        <span class="invalid-feedback">{{ errors.delivery_date }}</span>
													</div>
												</div>

												<div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="obs">Obs</label>
														<Field type="text" class="form-control" :class="{'is-invalid':errors.obs}" name="obs" id="obs" placeholder="Obs"/>
                                                        <span class="invalid-feedback">{{ errors.obs }}</span>
													</div>
												</div>
                                                <div class="row">
                                                    <FieldArray class="form-control" name="quotation" v-slot="{ fields, push, remove }">
                                                        <div class="card-body">
                                                            <button type="button" class="btn btn-pill btn-info mt-2" @click="push({ product_name: '' })">Adicionar Linha +</button>
                                                        </div>
                                                        <fieldset class="InputGroup" v-for="(field, idx) in fields" :key="field.key">
                                                                <div class="row">
                                                                    <div class="mb-3 col-md-3">
                                                                        <label class="form-label">Produto:</label>
                                                                        <Field type="text" class="form-control" :class="{'is-invalid':errors.product_name}" :name="`quotation[${idx}].product_name`" id="product_name" placeholder="Nome Produto"/>
                                                                        <span class="invalid-feedback">{{ errors.product_name }}</span>
                                                                    </div>
                                                                    <div class="mb-3 col-md-2">
                                                                        <label class="form-label">Quantidade:</label>
                                                                        <Field type="number" class="form-control" :class="{'is-invalid':errors.product_quantity}" :name="`quotation[${idx}].product_quantity`" id="product_quantity" placeholder="Quantidade Produto"/>
                                                                        <span class="invalid-feedback">{{ errors.product_quantity }}</span>
                                                                    </div>
                                                                    <div class="mb-3 col-md-2">
                                                                        <label class="form-label">Preço Unitário:</label>
                                                                        <Field type="number" class="form-control" :class="{'is-invalid':errors.unit_price}" :name="`quotation[${idx}].unit_price`" id="unit_price" placeholder="Preço Unitário"/>
                                                                        <span class="invalid-feedback">{{ errors.unit_price }}</span>
                                                                    </div> 
                                                                    <div class="mb-3 col-md-2">
                                                                        <label class="form-label">Desconto:</label>
                                                                        <Field type="number" class="form-control" :class="{'is-invalid':errors.discount}" :name="`quotation[${idx}].discount`" id="discount" placeholder="Disconto"/>
                                                                        <span class="invalid-feedback">{{ errors.discount }}</span>
                                                                    </div> 
                                                                    <div class="col-sm-3">
                                                                        <button type="button" class="btn btn-pill btn-danger mt-4" @click="remove(idx>1 ? idx : 0 )">X</button>
                                                                    </div>
                                                                </div>
                                                        </fieldset>  
                                                    </FieldArray>
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