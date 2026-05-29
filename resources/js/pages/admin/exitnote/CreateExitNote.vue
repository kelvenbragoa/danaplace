<script setup>

import {onMounted, ref, reactive,watch} from 'vue';
import axios from 'axios';
import {useToastr} from '../../../toastr';
import {Form,Field,FieldArray} from 'vee-validate';
import * as yup from 'yup';
import { useRouter } from "vue-router";
import moment from 'moment'
import {debounce} from 'lodash';
import { Bootstrap4Pagination } from 'laravel-vue-pagination';
import VueFeather from 'vue-feather';

const loading = ref(false);
const loadingproduct = ref(false);
const toastr = useToastr();
const loadingDiv = ref(false);
let currentvalue = ref([]);
let stockcenters = ref([]);
const stockcenterproducts = ref([]);
let stock_center_id = ref(0);
const stocksuppliers = ref([]);
const schema = yup.object({
    
  ref: yup.string().required(),
  document_ref: yup.string().required(),
  serie: yup.string().required(),
  stock_supplier_id: yup.string().required(),
  stock_center_id: yup.string().required(),

  stockcenterproducts: yup.array().of(
    yup.object().shape({
        id: yup.string().required(),
        quantity: yup.string().required(),
        product_id: yup.string().required(),
      })
  )
 
});
let self = this;
const router = useRouter();

const createRecordFunction = (values, actions) => {

 
    currentvalue.value = {values};

    loading.value = true;

    const arr = Array.from(values)
    
    axios.post('/exitnotes',values).then((response)=>{

    // exitnotes.value.unshift(response.data);
    // $('#createCategory').modal('hide');
    actions.resetForm();
    router.push({ path: '/admin/exitnotes' });
    toastr.success('Nota de Saída criada com sucesso');
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

axios.get('/auxiliar-create-inventory')
     .then((response)=>{

      stockcenters.value = response.data.stockcenters;
      stocksuppliers.value = response.data.stocksuppliers;
      loadingDiv.value=false;

     })
     .catch((error)=>{
      toastr.error(error);
      router.push({ path: '/admin/exitnotes' });
     })
}

const getProducts = (stockcenter) => {
    loadingproduct.value=true;
    axios.get(`/auxiliar-create-inventory-product/${stockcenter}`)
   .then((response)=>{

    stockcenterproducts.value = response.data.stockcenterproducts;


      loadingproduct.value=false;
   })
   .catch((error)=>{
    toastr.error(error);
    router.push({ path: '/admin/exitnotes' });
    loadingproduct.value=false;
   })


}

onMounted(()=>{
    getAuxiliarData();
})




</script>

<template>
    <div v-if="!loadingDiv">
        <h1 class="h3 mb-3">Nota de Saída</h1>
        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Formulário criação dos Nota de Saída do sistema.</h5>
                                        <router-link to="/admin/exitnotes" class="btn btn-pill btn-primary mt-3"><vue-feather type="arrow-left"></vue-feather>Voltar</router-link>  
								    </div>
                                    <div class="card-body">
                                            <Form @submit="createRecordFunction" :validation-schema="schema" v-slot="{ errors }">

												<div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="ref">Referência</label>
														<Field type="text" class="form-control" :class="{'is-invalid':errors.ref}" name="ref" id="ref" placeholder="Referência"/>
                                                        <span class="invalid-feedback">{{ errors.ref }}</span>
													</div>
												</div>

                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="document_ref">Documento Referência</label>
														<Field type="text" class="form-control" :class="{'is-invalid':errors.document_ref}" name="document_ref" id="document_ref" placeholder="Documento Referência"/>
                                                        <span class="invalid-feedback">{{ errors.document_ref }}</span>
													</div>
												</div>

                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="serie">Serie</label>
														<Field type="text" class="form-control" :class="{'is-invalid':errors.serie}" name="serie" id="serie" placeholder="Serie"/>
                                                        <span class="invalid-feedback">{{ errors.serie }}</span>
													</div>
												</div>

                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="stock_supplier_id">Fornecedor de Stock</label>
                                                        <Field as="select" class="form-control" :class="{'is-invalid':errors.stock_supplier_id}" name="stock_supplier_id" id="stock_supplier_id" aria-describedby="center_cost_id">
                                                            <option value="" disabled>Selecionar</option>
                                                            <option v-for="stocksupplier in stocksuppliers" :key="stocksupplier.id" :value="stocksupplier.id">{{ stocksupplier.name }}</option>
                                                        </Field>
                                                        <span class="invalid-feedback">{{ errors.stocksupplier }}</span>
													</div>
												</div>

                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="name">Centro de Stock</label>
                                                        <Field as="select" class="form-control" :class="{'is-invalid':errors.stock_center_id}" name="stock_center_id" id="stock_center_id" aria-describedby="center_cost_id" @change="getProducts(stock_center_id)" v-model="stock_center_id">
                                                            <option value="" disabled>Selecionar</option>
                                                            <option v-for="stockcenter in stockcenters" :key="stockcenter.id" :value="stockcenter.id">{{ stockcenter.name }}</option>
                                                        </Field>
                                                        <span class="invalid-feedback">{{ errors.stock_center_id }}</span>
													</div>
												</div>

                                                

                                                <div class="row">
                                                    <div class="card-body">
                                                        <div class="row">
                                                            <div class="col">
                                                                <span><strong>Productos encontrados: {{ stockcenterproducts.length }}</strong></span> 
                                                            </div>
                                                            <div class="col">
                                                                <div  v-if="loadingproduct"> <div class="spinner-border spinner-border-sm" role="status"></div> <span>Por favor aguarde ...</span> </div>
                                                            </div>
                                                            
                                                        </div>
                                                        

                                                        <div class="table-responsive">
                                                            <table class="table table-striped">
                                                                <thead>
                                                                    <tr>
                                                                        <th>#</th>
                                                                        <th>Codigo</th>
                                                                        <th>Produto</th>
                                                                        <th>Stock</th>
                                                                        <th>Quantidade</th>
                                                                        <th>Preço</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody v-if="stockcenterproducts.length > 0">
                                                                    <FieldArray class="form-control" name="stockcenterproducts">
                                                                        
                                                                        <tr v-for="(stockcenterproduct, idx) in stockcenterproducts" :key="stockcenterproduct.key">
                                                                            <td>#{{ idx + 1 }}</td>
                                                                            <td>{{ stockcenterproduct.stockproduct.code }}</td>
                                                                            <td>{{ stockcenterproduct.stockproduct.name }}</td>
                                                                            <td>{{ stockcenterproduct.quantity }}</td>
                                                                            <td>
                                                                                <fieldset>
                                                                                    <Field type="text" class="form-control" :name="`stockcenterproducts[${idx}].quantity`" :id="`stockcenterproduct[${idx}].quantity`" placeholder="Contado" value="0"/>
                                                                                    <!-- <span class="invalid-feedback">{{ errors.stockcenterproducts[idx].quantity }}</span> -->
                                                                                    <Field type="hidden" class="form-control" :name="`stockcenterproducts[${idx}].id`" :id="`stockcenterproduct[${idx}].id`" v-model="stockcenterproduct.id" placeholder="Contado"/>
                                                                                    <Field type="hidden" class="form-control" :name="`stockcenterproducts[${idx}].product_id`" :id="`stockcenterproduct[${idx}].product_id`" v-model="stockcenterproduct.product_id" placeholder="Contado"/>
                                                                                    
                                                                                </fieldset>
                                                                                
                                                                            </td>
                                                                            <td>{{ stockcenterproduct.stockproduct.unit_sell_price }} MT</td>
                                                                        </tr>
                                                                    </FieldArray>
                                                                </tbody>
                                                                <tbody v-else>
                                                                    <tr>
                                                                    <td colspan="6" align="center">Nenhum resultado encontrado</td>
                                                                    </tr>
                                                                </tbody>
                                                               
                                                            </table>
                                                        </div>
                                                        <!-- <FieldArray class="form-control" name="stockcenterproducts">
                                                                <fieldset class="InputGroup" v-for="(stockcenterproduct, idx) in stockcenterproducts" :key="stockcenterproduct.key">
                                                                    <div class="row">
                                                                        <div class="mb-3 col-md-3">
                                                                            <label class="form-label">#{{ idx+1 }}: {{product.name}}</label>
                                                                            <Field as="select" :name="`products[${idx}].name`" class="form-control">
                                                                                <option value="1" selected>Sim</option>
                                                                                <option value="0" >Não</option>

                                                                            </Field> 
                                                                            <Field type="hidden" class="form-control" :name="`products[${idx}].product_id`" v-model="product.id"/>
                                                                            <span class="invalid-feedback">{{ errors.products }}</span>
                                                                        </div>
                                                                        <div class="mb-3 col-md-3">
                                                                            <label class="form-label">#{{ idx+1 }}: {{ stockcenterproduct.stockproduct.name }}</label>
                                                                           
                                                                            <span class="invalid-feedback">{{ errors.stockcenterproducts }}</span>
                                                                        </div>
                                                                    </div>
                                                                </fieldset>
                                                        </FieldArray> -->
                                                        
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