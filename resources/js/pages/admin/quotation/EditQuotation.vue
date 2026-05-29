<script setup>

import axios from 'axios';
import { ref, onMounted, reactive, defineEmits, defineComponent,watch } from "vue";
import moment from 'moment'
import {useToastr} from '../../../toastr';
import {debounce} from 'lodash';
import {Form, Field,FieldArray} from 'vee-validate';
import { useRouter} from "vue-router";
import * as yup from 'yup';
import VueFeather from 'vue-feather';
import { Bootstrap4Pagination } from 'laravel-vue-pagination';

let retrievedData =ref([]);
let quotationitems =ref([]);
let coins =ref([]);
let statuses =ref([]);
let loadingButtonSubmit =ref([false]);
let loadingDiv =ref([true]);
const toastr = useToastr();
const router = useRouter();
let self = this;
let currentvalue = ref([]);
const schema = yup.object({
//   value: yup.string().required(),
});







const getData = () => {
  axios.get(`/quotation/+${router.currentRoute.value.params.id}/edit`)
       .then((response)=>{
        retrievedData.value = response.data.quotation;
        // quotationitem.value = response.data.quotationitem;
        coins.value = response.data.coins;
        statuses.value = response.data.status;

        loadingDiv.value=false;
        




       }).catch(()=>{

        loadingDiv.value=false;

       })
}

const editFunction = (values, actions) => {

  loadingButtonSubmit.value = true;
  axios.patch(`/quotation/${retrievedData.value.id}`,values).then((response)=>{

    // admins.value.unshift(response.data);
    // $('#createCategory').modal('hide');
    actions.resetForm();
    router.push({ path: '/admin/quotation' });
    toastr.success('Registo editado com sucesso');

  }).catch((error)=>{

    loadingButtonSubmit.value = false;
    toastr.error('Erro ao adicionar. '+error.response.data.message);
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

        <h1 class="h3 mb-3">Cotação</h1>
        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Cotação: {{ retrievedData.value }}</h5>

                                        <router-link to="/admin/quotation" class="btn btn-pill btn-primary mt-3"><vue-feather type="arrow-left"></vue-feather>Voltar</router-link> 

                                       
								    </div>
                                    
                                    <div class="card-body">
                                          
                                        <div class="row">
                                            <div class="col-xl-12 col-xxl-12 d-flex">
                                                <div class="w-100">
                                                    <Form @submit="editFunction" :validation-schema="schema" v-slot="{ errors }">

                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="equipment_id">Empresa:</label>
                                                                <span class="form-control">{{ retrievedData.destination_id != 0 ? retrievedData.destination.company_name : retrievedData.company_name }}</span>
                                                            </div>
                                                        </div>
                                                        <div class="row" v-if="retrievedData.destination_id != 0">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="equipment_id">Clientes:</label>
                                                                <span class="form-control">{{ retrievedData.destination.name }}</span>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="equipment_id">Nuit:</label>
                                                                <span class="form-control">{{ retrievedData.destination_id != 0 ? retrievedData.destination.company_nuit : retrievedData.company_nuit }}</span>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="equipment_id">Email:</label>
                                                                <span class="form-control">{{ retrievedData.destination_id != 0 ? retrievedData.destination.company_email : retrievedData.company_email }}</span>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="equipment_id">Nome Representante:</label>
                                                                <span class="form-control">{{ retrievedData.destination_id != 0 ? retrievedData.destination.name : retrievedData.representative_name }}</span>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="equipment_id">Telefone Representante:</label>
                                                                <span class="form-control">{{ retrievedData.destination_id != 0 ? retrievedData.destination.mobile : retrievedData.representative_mobile }}</span>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="date">Expira em:</label>
                                                                <Field type="date" class="form-control" :class="{'is-invalid':errors.expires_date}" v-model="retrievedData.expires_date" name="expires_date" id="expires_date" placeholder="Expira em" required/>                                                                <span class="invalid-feedback">{{ errors.date }}</span>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="type_of_transport">Tipo de Transporte</label>
                                                                <Field type="text" class="form-control" :class="{'is-invalid':errors.type_of_transport}" name="type_of_transport" id="type_of_transport" v-model="retrievedData.type_of_transport" placeholder="Tipo de Transporte"/>
                                                                <span class="invalid-feedback">{{ errors.type_of_transport }}</span>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="coin_id">Moeda:</label>
                                                                <Field as="select" class="form-control" :class="{'is-invalid':errors.coin_id}"  name="coin_id" id="coin_id" v-model="retrievedData.coin_id" aria-describedby="coin_id">
                                                                    <option value="" selected>Selecionar</option>
                                                                    <option v-for="coin in coins" :key="coin.id" :value="coin.id">{{ coin.name }}</option>
                                                                </Field>
                                                                <span class="invalid-feedback">{{ errors.coin_id }}</span>
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="payment_method">Método de Pagamento</label>
                                                                <Field type="text" class="form-control" :class="{'is-invalid':errors.payment_method}" name="payment_method" v-model="retrievedData.payment_method" id="payment_method" placeholder="Metodo de Pagamento"/>
                                                                <span class="invalid-feedback">{{ errors.payment_method }}</span>
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="warranty">Periodo de Garantia</label>
                                                                <Field type="date" class="form-control" :class="{'is-invalid':errors.warranty}" name="warranty" id="warranty" v-model="retrievedData.warranty" placeholder="Periodo de Garantia"/>
                                                                <span class="invalid-feedback">{{ errors.warranty }}</span>
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="delivery_date">Data de Entrega</label>
                                                                <Field type="date" class="form-control" :class="{'is-invalid':errors.delivery_date}" name="delivery_date" id="delivery_date" v-model="retrievedData.delivery_date" placeholder="Periodo de Garantia"/>
                                                                <span class="invalid-feedback">{{ errors.delivery_date }}</span>
                                                            </div>
                                                        </div>


                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="obs">Obs</label>
                                                                <Field type="text" class="form-control" :class="{'is-invalid':errors.obs}" name="obs" v-model="retrievedData.obs" id="obs" placeholder="Obs"/>
                                                                <span class="invalid-feedback">{{ errors.obs }}</span>
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="status_quotation_id">Status:</label>
                                                                <Field as="select" class="form-control" :class="{'is-invalid':errors.status_quotation_id}"  name="status_quotation_id" id="status_quotation_id" v-model="retrievedData.status_quotation_id" aria-describedby="status_quotation_id">
                                                                    <option value="" selected>Selecionar</option>
                                                                    <option v-for="status in statuses" :key="status.id" :value="status.id">{{ status.name }}</option>
                                                                </Field>
                                                                <span class="invalid-feedback">{{ errors.status_quotation_id }}</span>
                                                            </div>
                                                        </div>


                                                        <FieldArray class="form-control" name="quotation">
                                                            <fieldset class="InputGroup" v-for="(item, idx) in retrievedData.itens" :key="item.id">
                                                                <div class="row">
                                                                    <div class="mb-3 col-md-3">
                                                                        <label class="form-label">Produto:</label>
                                                                        <Field type="text" class="form-control" :class="{'is-invalid':errors.product_name}" :name="`quotation[${idx}].product_name`" v-model="item.product_name" id="product_name" placeholder="Nome Produto"/>
                                                                        <span class="invalid-feedback">{{ errors.product_name }}</span>
                                                                    </div>
                                                                    <div class="mb-3 col-md-3">
                                                                        <label class="form-label">Quantidade:</label>
                                                                        <Field type="number" class="form-control" :class="{'is-invalid':errors.product_quantity}" :name="`quotation[${idx}].product_quantity`" v-model="item.quantity" id="product_quantity" placeholder="Quantidade Produto"/>
                                                                        <span class="invalid-feedback">{{ errors.product_quantity }}</span>
                                                                    </div>
                                                                    <div class="mb-3 col-md-3">
                                                                        <label class="form-label">Preço Unitário:</label>
                                                                        <Field type="number" class="form-control" :class="{'is-invalid':errors.unit_price}" :name="`quotation[${idx}].unit_price`" id="unit_price" v-model="item.unit_price" placeholder="Preço Unitário"/>
                                                                        <span class="invalid-feedback">{{ errors.unit_price }}</span>
                                                                    </div> 
                                                                    <div class="mb-3 col-md-2">
                                                                        <label class="form-label">Desconto:</label>
                                                                        <Field type="number" class="form-control" :class="{'is-invalid':errors.discount}" :name="`quotation[${idx}].discount`" id="discount" v-model="item.discount" placeholder="Disconto"/>
                                                                        <span class="invalid-feedback">{{ errors.discount }}</span>
                                                                    </div> 
                                                                    <Field type="hidden" class="form-control" :name="`quotation[${idx}].id`" readonly v-model="item.id"/>

                                                                    
                                                                </div>
                                                            </fieldset>
                                                        </FieldArray>

                                                       

                                                        <!-- <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="date">Data:</label>
                                                                <Field type="date" class="form-control" :class="{'is-invalid':errors.date}" v-model="retrievedData.date" name="date" id="date" placeholder="Data" readonly required/>
                                                                <span class="invalid-feedback">{{ errors.date }}</span>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="value">Valor atual</label>
                                                                <Field type="number" class="form-control" :class="{'is-invalid':errors.value}" v-model="retrievedData.value" name="value" id="value" placeholder="Valor atual"/>
                                                                <span class="invalid-feedback">{{ errors.value }}</span>
                                                            </div>
                                                        </div>
                                                     -->
                                                       
												
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