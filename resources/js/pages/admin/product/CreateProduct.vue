<script setup>

import { onMounted, ref } from 'vue';
import axios from 'axios';
import { useToastr } from '../../../toastr';
import { Form, Field } from 'vee-validate';
import * as yup from 'yup';
import { useRouter } from 'vue-router';
import VueFeather from 'vue-feather';

const loading = ref(false);
const toastr = useToastr();
const loadingDiv = ref(true);
const initialvalue = ref(0);
const selectedIva = ref(1);
const generatedCode = ref('');
const brands = ref([]);
const categories = ref([]);
const ivas = ref([]);
const unities = ref([]);

const schema = yup.object({
    name: yup.string().required(),
    code: yup.string().required(),
    quantity: yup.number().min(0).required(),
    stock_min: yup.number().min(0).required(),
    unity_price: yup.number().min(0).required(),
    unit_id: yup.number().min(0).required(),
    unity_buy_price: yup.number().min(0).required(),
    product_brand_id: yup.number().required(),
    product_category_id: yup.number().required(),
});

const router = useRouter();

const createRecordFunction = (values, actions) => {
    loading.value = true;

    const payload = {
        ...values,
        code: generatedCode.value || values.code,
    };

    axios.post('/products', payload).then(() => {
        actions.resetForm();
        router.push({ path: '/admin/products' });
        toastr.success('Produto criado com sucesso');
    }).catch((error) => {
        toastr.error('Erro ao adicionar. ' + (error.response?.data?.message || ''));
        if (error.response?.data?.errors) {
            actions.setErrors(error.response.data.errors);
        }
    }).finally(() => {
        loading.value = false;
    });
};

const getAuxiliarData = () => {
    axios.get('/auxiliar-create-products')
        .then((response) => {
            brands.value = response.data.brands;
            categories.value = response.data.categories;
            ivas.value = response.data.ivas;
            unities.value = response.data.unities;
            generatedCode.value = response.data.next_code || '';
            loadingDiv.value = false;
        })
        .catch(() => {
            toastr.error('Erro ao carregar dados');
            router.push({ path: '/admin/products' });
        });
};

onMounted(() => {
    getAuxiliarData();
});

</script>

<template>
    <div v-if="!loadingDiv">
        <h1 class="h3 mb-3">Produtos</h1>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Formulário criação dos produtos do sistema.</h5>
                        <router-link to="/admin/products" class="btn btn-pill btn-primary mt-3">
                            <vue-feather type="arrow-left"></vue-feather>Voltar
                        </router-link>
                    </div>

                    <div class="card-body">
                        <Form
                            @submit="createRecordFunction"
                            :validation-schema="schema"
                            v-slot="{ errors }"
                            :initial-values="{ code: generatedCode, quantity: 0, tax_iva_id: selectedIva }"
                        >
                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <label class="form-label" for="code">Código</label>
                                    <Field
                                        type="text"
                                        class="form-control"
                                        :class="{'is-invalid': errors.code}"
                                        name="code"
                                        id="code"
                                        v-model="generatedCode"
                                        readonly
                                    />
                                    <span class="invalid-feedback">{{ errors.code }}</span>
                                    <small class="text-muted">Gerado automaticamente</small>
                                </div>
                            </div>
                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <label class="form-label" for="name">Nome</label>
                                    <Field type="text" class="form-control" :class="{'is-invalid': errors.name}" name="name" id="name" placeholder="Nome"/>
                                    <span class="invalid-feedback">{{ errors.name }}</span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <label class="form-label" for="quantity">Quantidade</label>
                                    <Field type="text" class="form-control" :class="{'is-invalid': errors.quantity}" name="quantity" v-model="initialvalue" id="quantity" readonly placeholder="Quantidade"/>
                                    <span class="invalid-feedback">{{ errors.quantity }}</span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <label class="form-label" for="stock_min">Stock Minimo</label>
                                    <Field type="number" class="form-control" :class="{'is-invalid': errors.stock_min}" name="stock_min" id="stock_min" placeholder="Stock Mínimo"/>
                                    <span class="invalid-feedback">{{ errors.stock_min }}</span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <label class="form-label" for="unity_buy_price">Preço unitário de compra</label>
                                    <Field type="number" class="form-control" :class="{'is-invalid': errors.unity_buy_price}" name="unity_buy_price" id="unity_buy_price" placeholder="Preço unitário de compra"/>
                                    <span class="invalid-feedback">{{ errors.unity_buy_price }}</span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <label class="form-label" for="unity_price">Preço unitário de venda</label>
                                    <Field type="number" class="form-control" :class="{'is-invalid': errors.unity_price}" name="unity_price" id="unity_price" placeholder="Preço unitário de venda"/>
                                    <span class="invalid-feedback">{{ errors.unity_price }}</span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <label class="form-label" for="product_category_id">Categoria do produto</label>
                                    <Field as="select" class="form-control" :class="{'is-invalid': errors.product_category_id}" name="product_category_id" id="product_category_id">
                                        <option value="" disabled>Selecionar</option>
                                        <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
                                    </Field>
                                    <span class="invalid-feedback">{{ errors.product_category_id }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <label class="form-label" for="product_brand_id">Marca do produto</label>
                                    <Field as="select" class="form-control" :class="{'is-invalid': errors.product_brand_id}" name="product_brand_id" id="product_brand_id">
                                        <option value="" disabled>Selecionar</option>
                                        <option v-for="brand in brands" :key="brand.id" :value="brand.id">{{ brand.name }}</option>
                                    </Field>
                                    <span class="invalid-feedback">{{ errors.product_brand_id }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <label class="form-label" for="unit_id">Unidade</label>
                                    <Field as="select" class="form-control" :class="{'is-invalid': errors.unit_id}" name="unit_id" id="unit_id">
                                        <option value="" disabled>Selecionar</option>
                                        <option v-for="unit in unities" :key="unit.id" :value="unit.id">{{ unit.name }}</option>
                                    </Field>
                                    <span class="invalid-feedback">{{ errors.unit_id }}</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3 col-md-12">
                                    <label class="form-label" for="tax_iva_id">IVA</label>
                                    <Field as="select" class="form-control" :class="{'is-invalid': errors.tax_iva_id}" v-model="selectedIva" name="tax_iva_id" id="tax_iva_id">
                                        <option value="" disabled>Selecionar</option>
                                        <option v-for="iva in ivas" :key="iva.id" :value="iva.id">{{ iva.name }}</option>
                                    </Field>
                                    <span class="invalid-feedback">{{ errors.tax_iva_id }}</span>
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
