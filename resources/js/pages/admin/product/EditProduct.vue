<script setup>

import axios from 'axios';
import { ref, onMounted, reactive, defineEmits, defineComponent,watch } from "vue";
import moment from 'moment'
import {useToastr} from '../../../toastr';
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
const brands = ref([]);
const categories = ref([]);
const ivas = ref([]);

const schema = yup.object({
  name: yup.string().required(),
  code: yup.string().required(),
  quantity: yup.number().min(0).required(),
  stock_min:yup.number().min(0).required(),
  unity_price: yup.number().min(0).required(),
  unity_buy_price: yup.number().min(0).required(),
  product_brand_id: yup.number().required(),
  product_category_id: yup.number().required(),
});

const getData = () => {
  axios.get(`/products/+${router.currentRoute.value.params.id}/edit`)
       .then((response)=>{

        loadingDiv.value=false;
        retrievedData.value = response.data.product;
        brands.value = response.data.brands;
        categories.value = response.data.categories;
        ivas.value = response.data.ivas;

       }).catch(()=>{

        loadingDiv.value=false;

       })
}

const editFunction = (values, actions) => {

  loadingButtonSubmit.value = true;
  axios.patch(`/products/${retrievedData.value.id}`,values).then((response)=>{

    // admins.value.unshift(response.data);
    // $('#createCategory').modal('hide');
    actions.resetForm();
    router.push({ path: '/admin/products' });
    toastr.success('Produto editada com sucesso');

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

        <h1 class="h3 mb-3">Produto </h1>
        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Produto: {{ retrievedData.name }}</h5>

                                        <router-link to="/admin/products" class="btn btn-pill btn-primary mt-3"><vue-feather type="arrow-left"></vue-feather>Voltar</router-link> 

                                       
								    </div>
                                    
                                    <div class="card-body">
                                          
                                        <div class="row">
                                            <div class="col-xl-12 col-xxl-12 d-flex">
                                                <div class="w-100">
                                                    <Form @submit="editFunction" :validation-schema="schema" v-slot="{ errors }">
                                                    
                                                      
                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="code">Código</label>
                                                                <Field type="text" class="form-control" :class="{'is-invalid':errors.code}" name="code" id="code" v-model="retrievedData.code" placeholder="Código"/>
                                                                <span class="invalid-feedback">{{ errors.code }}</span>
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
                                                                <label class="form-label" for="quantity">Quantidade</label>
                                                                <Field type="text" class="form-control" :class="{'is-invalid':errors.quantity}" name="quantity"  v-model="retrievedData.quantity" id="quantity" readonly placeholder="Quantidade"/>
                                                                <span class="invalid-feedback">{{ errors.quantity }}</span>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="stock_min">Stock Mínimo</label>
                                                                <Field type="text" class="form-control" :class="{'is-invalid':errors.stock_min}" name="stock_min" id="stock_min" v-model="retrievedData.stock_min" placeholder="Stock Mínimo"/>
                                                                <span class="invalid-feedback">{{ errors.stock_min }}</span>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="unity_buy_price">Preço unitário de compra</label>
                                                                <Field type="text" class="form-control" :class="{'is-invalid':errors.unity_buy_price}" name="unity_buy_price" id="unity_buy_price" v-model="retrievedData.unity_buy_price" placeholder="Preço unitário de compra"/>
                                                                <span class="invalid-feedback">{{ errors.unity_buy_price }}</span>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="unity_price">Preço unitário de venda</label>
                                                                <Field type="text" class="form-control" :class="{'is-invalid':errors.unity_price}" name="unity_price" id="unity_price" v-model="retrievedData.unity_price" placeholder="Preço unitário de venda"/>
                                                                <span class="invalid-feedback">{{ errors.unity_price }}</span>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="product_category_id">Categoria do produto</label>
                                                                <Field as="select" class="form-control" :class="{'is-invalid':errors.product_category_id}"  name="product_category_id" id="product_category_id" v-model="retrievedData.product_category_id" aria-describedby="product_category_id">
                                                                    <option value="" disabled>Selecionar</option>
                                                                    <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
                                                                </Field>
                                                                <span class="invalid-feedback">{{ errors.product_category_id }}</span>
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="product_brand_id">Marca do produto</label>
                                                                <Field as="select" class="form-control" :class="{'is-invalid':errors.product_brand_id}"  name="product_brand_id" id="product_brand_id" v-model="retrievedData.product_brand_id" aria-describedby="product_brand_id">
                                                                    <option value="" disabled>Selecionar</option>
                                                                    <option v-for="brand in brands" :key="brand.id" :value="brand.id">{{ brand.name }}</option>
                                                                </Field>
                                                                <span class="invalid-feedback">{{ errors.product_brand_id }}</span>
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="mb-3 col-md-12">
                                                                <label class="form-label" for="tax_iva_id">IVA</label>
                                                                <Field as="select" class="form-control" :class="{'is-invalid':errors.tax_iva_id}" v-model="retrievedData.tax_iva_id"  name="tax_iva_id" id="tax_iva_id"  aria-describedby="tax_iva_id">
                                                                    <option value="" disabled>Selecionar</option>
                                                                    <option v-for="iva in ivas" :key="iva.id" :value="iva.id">{{ iva.name }}</option>
                                                                </Field>
                                                                <span class="invalid-feedback">{{ errors.tax_iva_id }}</span>
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