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
import FileUpload from 'primevue/fileupload';


let retrievedData =ref([]);
let criticals =ref([]);
let equipmentstatuses =ref([]);
let loadingButtonSubmit =ref([false]);
let loadingDiv =ref([true]);
const toastr = useToastr();
const router = useRouter();
let self = this;
let currentvalue = ref([]);
const schema = yup.object({
    name: yup.string().required(),
    ref: yup.string().required(),
    model: yup.string().required(),
    make: yup.string().required(),
    serial: yup.string().required(),
    criticaly_id: yup.string().required(),
    equipment_id: yup.string().required(),
    equipment_status_id: yup.string().required(),
    percentage_weigth: yup.string().required(),
});

let image = ref();

const onFileUpload = (event) => {
    image.value = event.files[0];
    console.log(image.value);
};






const getData = () => {
  axios.get(`/equipmentcomponent/+${router.currentRoute.value.params.id}/edit`)
       .then((response)=>{

        loadingDiv.value=false;
        retrievedData.value = response.data.component;
        equipmentstatuses.value = response.data.equipmentstatuses;
        criticals.value = response.data.criticals;

       }).catch(()=>{

        loadingDiv.value=false;

       })
}

const editFunction = (values, actions) => {
// values.image = image.value;

  loadingButtonSubmit.value = true;
  axios.patch(`/equipmentcomponent/${retrievedData.value.id}`,values).then((response)=>{

    // admins.value.unshift(response.data);
    // $('#createCategory').modal('hide');
    actions.resetForm();
    router.push({ path: '/admin/equipments/'+retrievedData.value.equipment_id });
    toastr.success('Componente do Equipamento editado com sucesso');

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

        <h1 class="h3 mb-3">Componente do Equipamento </h1>
        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Componente: {{ retrievedData.name }}</h5>

                                        <router-link :to="'/admin/equipments/'+retrievedData.equipment_id" class="btn btn-pill btn-primary mt-3"><vue-feather type="arrow-left"></vue-feather>Voltar</router-link> 

                                       
								    </div>
                                    
                                    <div class="card-body">
                                          
                                        <div class="row">
                                            <div class="col-xl-12 col-xxl-12 d-flex">
                                                <div class="w-100">
                                                    <Form @submit="editFunction" :validation-schema="schema" v-slot="{ errors }">
                                                    
                                                                    <div class="row">
                                                                        <div class="mb-3 col-md-6">
                                                                            <label class="form-label" for="name">Nome</label>
                                                                            <Field type="text" class="form-control" :class="{'is-invalid':errors.name}" name="name" id="name" placeholder="Nome" v-model="retrievedData.name" readonly/>
                                                                            <span class="invalid-feedback">{{ errors.name }}</span>
                                                                        </div>
                                                                    </div>

                                                                    <div class="row">
                                                                        <div class="mb-3 col-md-6">
                                                                            <label class="form-label" for="ref">Referência</label>
                                                                            <Field type="text" class="form-control" :class="{'is-invalid':errors.ref}" name="ref" id="ref" placeholder="Referência" v-model="retrievedData.ref"/>
                                                                            <span class="invalid-feedback">{{ errors.ref }}</span>
                                                                        </div>
                                                                    </div>

                                                                    <div class="row">
                                                                        <div class="mb-3 col-md-6">
                                                                            <label class="form-label" for="serial">Serial</label>
                                                                            <Field type="text" class="form-control" :class="{'is-invalid':errors.serial}" name="serial" id="serial" placeholder="Serial" v-model="retrievedData.serial"/>
                                                                            <span class="invalid-feedback">{{ errors.serial }}</span>
                                                                        </div>
                                                                    </div>

                                                                    <div class="row">
                                                                        <div class="mb-3 col-md-6">
                                                                            <label class="form-label" for="model">Modelo</label>
                                                                            <Field type="text" class="form-control" :class="{'is-invalid':errors.model}" name="model" id="model" placeholder="Modelo" v-model="retrievedData.model" readonly/>
                                                                            <span class="invalid-feedback">{{ errors.model }}</span>
                                                                        </div>
                                                                    </div>

                                                                    <div class="row">
                                                                        <div class="mb-3 col-md-6">
                                                                            <label class="form-label" for="make">Marca</label>
                                                                            <Field type="text" class="form-control" :class="{'is-invalid':errors.make}" name="make" id="make" placeholder="Marca" v-model="retrievedData.make" readonly/>
                                                                            <span class="invalid-feedback">{{ errors.make }}</span>
                                                                        </div>
                                                                    </div>
                                                                   
                                                                   
                                                                    <div class="row">
                                                                        <div class="mb-3 col-md-6">
                                                                            <label class="form-label" for="percentage_weigth">Percentagem do Componente no Equipamento %</label>
                                                                            <Field type="number" class="form-control" :class="{'is-invalid':errors.percentage_weigth}" name="percentage_weigth" id="percentage_weigth" placeholder="Percentagem%" v-model="retrievedData.percentage_weigth" readonly/>
                                                                            <span class="invalid-feedback">{{ errors.percentage_weigth }}</span>
                                                                        </div>
                                                                    </div>

                                                                    <div class="row">
                                                                        <div class="mb-3 col-md-6">
                                                                            <label class="form-label" for="criticaly_id">Criticidade </label>
                                                                            <Field as="select" class="form-control" :class="{'is-invalid':errors.criticaly_id}"  name="criticaly_id" id="criticaly_id" aria-describedby="criticaly_id" v-model="retrievedData.criticaly_id" readonly>
                                                                                
                                                                                <option v-for="critical in criticals" :key="critical.id" :value="critical.id" disabled>{{ critical.name }}</option>
                                                                            </Field>
                                                                            <span class="invalid-feedback">{{ errors.criticaly_id }}</span>
                                                                        </div>
                                                                    </div>

                                                                    <div class="row">
                                                                        <div class="mb-3 col-md-6">
                                                                            <label class="form-label" for="equipment_status_id">Estado</label>
                                                                            <Field as="select" class="form-control" :class="{'is-invalid':errors.equipment_status_id}"  name="equipment_status_id" id="equipment_status_id" aria-describedby="equipment_status_id" v-model="retrievedData.equipment_status_id">
                                                                                <option value="" disabled>Selecionar</option>
                                                                                <option v-for="equipmentstatus in equipmentstatuses" :key="equipmentstatus.id" :value="equipmentstatus.id">{{ equipmentstatus.name }} / {{ equipmentstatus.mobilized }}</option>
                                                                            </Field>
                                                                            <span class="invalid-feedback">{{ errors.equipment_status_id }}</span>
                                                                            <Field type="hidden" class="form-control" :class="{'is-invalid':errors.equipment_id}" name="equipment_id" id="equipment_id" v-model="retrievedData.equipment_id"/>
                                                                        </div>
                                                                    </div>

                                                                    <div class="mb-3 col-md-6">
                                                                        <label class="form-label" for="mcscr_status_id">Ficheiro:</label>
                                                                        <FileUpload mode="basic"  class="form-control" name="image" accept="image/*" auto :maxFileSize="1000000" customUpload @uploader="onFileUpload" />
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