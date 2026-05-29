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
import QrcodeVue from 'qrcode.vue'

let retrievedData =ref([]);
let subtasks = ref([]);
let typesubtasks = ref([]);
let accounts =ref([]);
let loadingSubmit =ref([true]);
let loadingSubmitMaterial =ref([true]);
let loadingDiv =ref([true]);
let qrcodevalue = ref('/equipment/0');
const router = useRouter();
let self = this;
let currentvalue = ref([]);
let currentvaluematerial = ref([]);
let currentvaluedepartment = ref([]);
const loadingButtonDelete = ref(false);
let dataIdBeingDeleted = ref(null);
const products = ref([]);
const departments = ref([]);
const taskproducts = ref([]);
const taskdepartments = ref([]);


const toastr = useToastr();

const schema = yup.object({
    name: yup.string().required(),
    type_sub_task_id: yup.string().required(),
    task_plan_task_id:yup.string().required(),
  });

  const schema2 = yup.object({
    
    quantity: yup.string().required(),
    task_plan_task_id:yup.string().required(),
    // product_id: yup.string().required(),
    product_name: yup.string().required(),
    
  });

  const schema3= yup.object({
    
    quantity_department: yup.string().required(),
    task_plan_task_id:yup.string().required(),
  });



const getData = (page = 1) => {
  axios.get(`/taskplantasks/+${router.currentRoute.value.params.id}?page=${page}`, )
       .then((response)=>{
        loadingDiv.value=false;
        retrievedData.value = response.data.taskplantasks;
        subtasks.value = response.data.subtasks;
        typesubtasks.value = response.data.typesubtasks;
        products.value = response.data.products;
        departments.value = response.data.departments;
        taskproducts.value = response.data.taskproducts;
        taskdepartments.value = response.data.taskdepartments;
        
       
       }).catch(()=>{
        loadingDiv.value=false;
       })
}


const createRecordFunction = (values, actions) => {

currentvalue.value = {values};
loadingSubmit.value = true;
const arr = Array.from(values)
axios.post('/subtasks',values).then((response)=>{

// categories.value.unshift(response.data);
// $('#createCategory').modal('hide');

retrievedData.value = response.data.taskplantasks;
subtasks.value = response.data.subtasks;
typesubtasks.value = response.data.typesubtasks;
products.value = response.data.products;
taskproducts.value = response.data.taskproducts;



actions.resetField('name');
actions.resetField('type_sub_task_id');
loadingSubmit.value = false;

toastr.success('Atividade adicionada com successo');
}).catch((error)=>{

loadingSubmit.value = false;
toastr.error('Erro ao associar. '+error.response.data.message);
if(error.response.data.errors){
   
    actions.setErrors(error.response.data.errors);
}
}).finally(()=>{
loadingSubmit.value = false;

})

};

const createRecordMaterialFunction = (values, actions) => {

 
currentvaluematerial.value = {values};

loadingSubmitMaterial.value = true;

const arr = Array.from(values)

axios.post('/taskmaterials',values).then((response)=>{

// categories.value.unshift(response.data);
// $('#createCategory').modal('hide');

retrievedData.value = response.data.taskplantasks;
subtasks.value = response.data.subtasks;
typesubtasks.value = response.data.typesubtasks;
products.value = response.data.products;
taskproducts.value = response.data.taskproducts;
taskdepartments.value = response.data.taskdepartments;


actions.resetField('product_id');
actions.resetField('product_name');
actions.resetField('quantity');
loadingSubmitMaterial.value = false;

toastr.success('Material adicionado com successo');
}).catch((error)=>{

loadingSubmitMaterial.value = false;
toastr.error('Erro ao associar. '+error.response.data.message);
if(error.response.data.errors){
   
    actions.setErrors(error.response.data.errors);
}
}).finally(()=>{
loadingSubmitMaterial.value = false;

})

};

const createRecordDepartmentFunction = (values, actions) => {

 
currentvaluedepartment.value = {values};

loadingSubmitMaterial.value = true;

const arr = Array.from(values)

axios.post('/taskdepartments',values).then((response)=>{

// categories.value.unshift(response.data);
// $('#createCategory').modal('hide');

retrievedData.value = response.data.taskplantasks;
subtasks.value = response.data.subtasks;
typesubtasks.value = response.data.typesubtasks;
products.value = response.data.products;
taskproducts.value = response.data.taskproducts;
taskdepartments.value = response.data.taskdepartments;


actions.resetField('department_id');
actions.resetField('department_name');
actions.resetField('quantity_department');
loadingSubmitMaterial.value = false;

toastr.success('Departamento de técnicos adicionado com successo');
}).catch((error)=>{

loadingSubmitMaterial.value = false;
toastr.error('Erro ao associar. '+error.response.data.message);
if(error.response.data.errors){
   
    actions.setErrors(error.response.data.errors);
}
}).finally(()=>{
loadingSubmitMaterial.value = false;

})

};

const confirmDeletion = (data) => {

dataIdBeingDeleted = data.id;

$('#deleteModal').modal('show');

};

const confirmDeletion2 = (data) => {

dataIdBeingDeleted = data.id;

$('#deleteModal2').modal('show');

};

const confirmDeletion3 = (data) => {

dataIdBeingDeleted = data.id;

$('#deleteModal3').modal('show');

};


const deleteData = () =>{

loadingButtonDelete.value= true;

axios.delete(`/subtasks/${dataIdBeingDeleted}`)
.then(()=>{
    subtasks.value.data = subtasks.value.data.filter(data=>data.id !== dataIdBeingDeleted); 
 $('#deleteModal').modal('hide');

 toastr.success('Registro apagada com sucesso');

}).catch(()=>{
 toastr.error('Erro ao apagar');
 loadingButtonDelete.value= false;
 $('#deleteModal').modal('hide');
}).finally(()=>{
 loadingButtonDelete.value= false;
});
}

const deleteData2 = () =>{

loadingButtonDelete.value= true;

axios.delete(`/taskmaterials/${dataIdBeingDeleted}`)
.then(()=>{
    taskproducts.value = taskproducts.value.filter(data=>data.id !== dataIdBeingDeleted); 
 $('#deleteModal2').modal('hide');

 toastr.success('Registro apagado com sucesso');

}).catch((e)=>{
    console.log(e)
 toastr.error('Erro ao apagar');
 loadingButtonDelete.value= false;
 $('#deleteModal2').modal('hide');
}).finally(()=>{
 loadingButtonDelete.value= false;
});
}

const deleteData3 = () =>{

loadingButtonDelete.value= true;

axios.delete(`/taskdepartments/${dataIdBeingDeleted}`)
.then(()=>{
    taskdepartments.value = taskdepartments.value.filter(data=>data.id !== dataIdBeingDeleted); 
 $('#deleteModal3').modal('hide');

 toastr.success('Registro apagado com sucesso');

}).catch((e)=>{
    console.log(e)
 toastr.error('Erro ao apagar');
 loadingButtonDelete.value= false;
 $('#deleteModal3').modal('hide');
}).finally(()=>{
 loadingButtonDelete.value= false;
});
}



onMounted(()=>{
  getData();
})
</script>

<template>
    <div v-if="!loadingDiv">

        <h1 class="h3 mb-3">Atividade</h1>
        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Nome: {{ retrievedData.name }}</h5>
                                        <router-link :to="'/admin/taskplans/'+retrievedData.task_plan_id" class="btn btn-pill btn-primary mt-3"><vue-feather type="arrow-left"></vue-feather>Voltar</router-link> 
								    </div>
                                    
                                    <div class="card-body">
                                          
                                        <div class="row">
                                            <div class="col-xl-12 col-xxl-12 d-flex">
                                                <div class="w-100">
                                                   
                                                        <p><strong>Nome:</strong> {{ retrievedData.name }}</p>
                                                        <p><strong>Tipo de Atividade:</strong> {{ retrievedData.typetask.name }}</p>
                                                        <p><strong>Frequência:</strong> {{ retrievedData.do_every }}  {{ retrievedData.frequency.name }}</p>
                                                        <p><strong>Criticidade:</strong> {{ retrievedData.critical.name }}</p>
                                                        <p><strong>Tempo estimado:</strong> {{ retrievedData.estimated_time_days}} Dias : {{ retrievedData.estimated_time_hours}} Horas : {{ retrievedData.estimated_time_minutes}} Minutos</p>
                                                        <p><strong>Tempo que o equipamento estará indisponível:</strong> {{ retrievedData.unavailable_equipment_time_days}} Dias : {{ retrievedData.unavailable_equipment_time_hours}} Horas : {{ retrievedData.unavailable_equipment_time_minutes}} Minutos</p>
                                                        <p><strong>Atividade:</strong> {{ subtasks.total }}</p>

                                                        <hr>
                                                        <h5 class="card-title">Atividade: {{ subtasks.total }} registros encontrados.</h5>
                                                        <a class="btn btn-pill btn-primary mt-3" data-toggle="collapse" href="#collapseExample" role="button" aria-expanded="false" aria-controls="collapseExample">
                                                            <vue-feather type="plus"></vue-feather>Adicionar Atividade
                                                        </a>
                                                        <div class="collapse mt-3" id="collapseExample">
                                                                <div class="card card-body">
                                                                    <Form @submit="createRecordFunction" :validation-schema="schema" v-slot="{ errors }">
                                                                        <div class="row">
                                                                            <div class="row">
                                                                                <div class="mb-3 col-md-12">
                                                                                    <label class="form-label" for="name">Atividade</label>
                                                                                    <Field type="text" class="form-control" :class="{'is-invalid':errors.name}" name="name" id="name" placeholder="Nome da atividade"/>
                                                                                    <span class="invalid-feedback">{{ errors.name }}</span>
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                        <div class="row">
                                                                            <div class="mb-3 col-md-12">
                                                                                <label class="form-label" for="type_sub_task_id">Tipo de resposta:</label>
                                                                                <Field as="select" class="form-control" :class="{'is-invalid':errors.type_sub_task_id}"  name="type_sub_task_id" id="type_sub_task_id" aria-describedby="type_sub_task_id">
                                                                                    <option value="" selected>Selecionar</option>
                                                                                    <option v-for="typesubtask in typesubtasks" :key="typesubtask.id" :value="typesubtask.id">{{ typesubtask.name }}</option>
                                                                                </Field>
                                                                                <span class="invalid-feedback">{{ errors.type_sub_task_id }}</span>
                                                                            </div>
                                                                        </div>

                                                                        <Field type="hidden" name="task_plan_task_id" v-model="retrievedData.id"></Field>

                                                                        <button type="submit" class="btn btn-primary" :disabled="loadingSubmit == true">
                                                                            <div v-if="loadingSubmit == true" class="spinner-border spinner-border-sm" role="status"></div>
                                                                            <span v-else>Adicionar Atividade</span>
                                                                        </button>

                                                                    </Form>
                                                                </div>
                                                        </div>
                                                        <div class="table-responsive">
                                                        <table class="table table-striped">
                                                            <thead>
                                                                <tr>
                                                                    <th>#</th>
                                                                    <th>Atividade</th>
                                                                    <th>Tipo de Atividade</th>
                                                                    <th>Ações</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody v-if="subtasks.data.length > 0">
                                                                <tr  v-for="(actualData,index) in subtasks.data" :key="actualData.id">
                                                                    <td>#{{ index + 1 }}</td>
                                                                    <td>{{ actualData.name}}</td>
                                                                    <td>{{ actualData.typesubtask.name}}</td>
                                                                    <td>
                                                                       
                                                                        <a href="#" @click.prevent="confirmDeletion(actualData)"><vue-feather type="trash"></vue-feather></a>
                                                                        
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                            <tbody v-else>
                                                                <tr>
                                                                <td colspan="4" align="center">Nenhum resultado encontrado</td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    <Bootstrap4Pagination :data="subtasks" @pagination-change-page="getData"/>

                                                    <hr>
                                                    <a class="btn btn-pill btn-primary mt-3" data-toggle="collapse" href="#collapseExampleMaterial" role="button" aria-expanded="false" aria-controls="collapseExampleMaterial">
                                                            <vue-feather type="plus"></vue-feather>Adicionar Materiais
                                                        </a>
                                                        <div class="collapse mt-3" id="collapseExampleMaterial">
                                                                <div class="card card-body">
                                                                    <Form @submit="createRecordMaterialFunction" :validation-schema="schema2" v-slot="{ errors }">

                                                                        <div class="row">
                                                                            <div class="row">
                                                                                <div class="mb-3 col-md-12">
                                                                                    <label class="form-label" for="product_name">Descrição do Produto</label>
                                                                                    <Field type="text" class="form-control" :class="{'is-invalid':errors.product_name}" name="product_name" id="product_name" placeholder="Descricao de produto"/>
                                                                                    <span class="invalid-feedback">{{ errors.product_name }}</span>
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                        <div class="row">
                                                                            <div class="row">
                                                                                <div class="mb-3 col-md-12">
                                                                                    <label class="form-label" for="product_id">Produto</label>
                                                                                    <Field as="select" class="form-control" :class="{'is-invalid':errors.product_id}"  name="product_id" id="product_id" aria-describedby="product_id">
                                                                                        <option value="" selected>Selecionar</option>
                                                                                        <option v-for="product in products" :key="product.id" :value="product.id">{{ product.name }}</option>
                                                                                    </Field>
                                                                                    <span class="invalid-feedback">{{ errors.product_id }}</span>
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                        <div class="row">
                                                                            <div class="row">
                                                                                <div class="mb-3 col-md-12">
                                                                                    <label class="form-label" for="quantity">Quantidade</label>
                                                                                    <Field type="number" class="form-control" :class="{'is-invalid':errors.quantity}" name="quantity" id="quantity" placeholder="Quantidade"/>
                                                                                    <span class="invalid-feedback">{{ errors.quantity }}</span>
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                        <Field type="hidden" name="task_plan_task_id" v-model="retrievedData.id"></Field>
                                                                        <button type="submit" class="btn btn-primary" :disabled="loadingSubmitMaterial == true">
                                                                            <div v-if="loadingSubmitMaterial == true" class="spinner-border spinner-border-sm" role="status"></div>
                                                                            <span v-else>Associar Material</span>
                                                                        </button>

                                                                    </Form>
                                                                </div>
                                                        </div>  
                                                        
                                                        <div class="table-responsive">
                                                        <table class="table table-striped">
                                                            <thead>
                                                                <tr>
                                                                    <th>#</th>
                                                                    <th>Produto/Material</th>
                                                                    <th>Produto/Material Stock</th>
                                                                    <th>Quantidade</th>
                                                                    <th>Ações</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody v-if="taskproducts.length > 0">
                                                                <tr  v-for="(actualData,index) in taskproducts" :key="actualData.id">
                                                                    <td>#{{ index + 1 }}</td>
                                                                    <td>{{ actualData.product_name ?? 'N/A'}}</td>
                                                                    <td>{{ actualData.product == null ?  'N/A' : actualData.product.name}}</td>
                                                                    <td>{{ actualData.quantity}}</td>
                                                                    <td>
                                                                       
                                                                        <a href="#" @click.prevent="confirmDeletion2(actualData)"><vue-feather type="trash"></vue-feather></a>
                                                                        
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                            <tbody v-else>
                                                                <tr>
                                                                <td colspan="5" align="center">Nenhum resultado encontrado</td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>


                                                    <hr>
                                                    <a class="btn btn-pill btn-primary mt-3" data-toggle="collapse" href="#collapseExampleTechnician" role="button" aria-expanded="false" aria-controls="collapseExampleTechnician">
                                                            <vue-feather type="plus"></vue-feather>Adicionar Técnico
                                                        </a>
                                                        <div class="collapse mt-3" id="collapseExampleTechnician">
                                                                <div class="card card-body">
                                                                    <Form @submit="createRecordDepartmentFunction" :validation-schema="schema3" v-slot="{ errors }">

                                                                        <div class="row">
                                                                            <div class="row">
                                                                                <div class="mb-3 col-md-12">
                                                                                    <label class="form-label" for="department_id">Departamento</label>
                                                                                    <Field as="select" class="form-control" :class="{'is-invalid':errors.department_id}"  name="department_id" id="department_id" aria-describedby="department_id">
                                                                                        <option value="" selected>Selecionar</option>
                                                                                        <option v-for="department in departments" :key="department.id" :value="department.id">{{ department.name }}</option>
                                                                                    </Field>
                                                                                    <span class="invalid-feedback">{{ errors.department_id }}</span>
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                        <div class="row">
                                                                            <div class="row">
                                                                                <div class="mb-3 col-md-12">
                                                                                    <label class="form-label" for="department_name">Descrição Departamento</label>
                                                                                    <Field type="text" class="form-control" :class="{'is-invalid':errors.department_name}" name="department_name" id="department_name" placeholder="Departamento"/>
                                                                                    <span class="invalid-feedback">{{ errors.department_name }}</span>
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                        <div class="row">
                                                                            <div class="row">
                                                                                <div class="mb-3 col-md-12">
                                                                                    <label class="form-label" for="quantity_department">Quantidade de técnicos</label>
                                                                                    <Field type="number" class="form-control" :class="{'is-invalid':errors.quantity_department}" name="quantity_department" id="quantity_department" placeholder="Quantidade"/>
                                                                                    <span class="invalid-feedback">{{ errors.quantity_department }}</span>
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                        <Field type="hidden" name="task_plan_task_id" v-model="retrievedData.id"></Field>
                                                                        <button type="submit" class="btn btn-primary" :disabled="loadingSubmitMaterial == true">
                                                                            <div v-if="loadingSubmitMaterial == true" class="spinner-border spinner-border-sm" role="status"></div>
                                                                            <span v-else>Associar Departamento Técnicos</span>
                                                                        </button>

                                                                    </Form>
                                                                </div>
                                                        </div> 
                                                        <div class="table-responsive">
                                                        <table class="table table-striped">
                                                            <thead>
                                                                <tr>
                                                                    <th>#</th>
                                                                    <th>Departamento</th>
                                                                    <th>Descrição Departamento</th>
                                                                    <th>Quantidade</th>
                                                                    <th>Ações</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody v-if="taskdepartments.length > 0">
                                                                <tr  v-for="(actualData,index) in taskdepartments" :key="actualData.id">
                                                                    <td>#{{ index + 1 }}</td>
                                                                    <td>{{ actualData.department == null ?  'N/A' : actualData.department.name}}</td>
                                                                    <td>{{ actualData.department_name ?? 'N/A'}}</td>
                                                                    <td>{{ actualData.quantity}}</td>
                                                                    <td>
                                                                       
                                                                        <a href="#" @click.prevent="confirmDeletion3(actualData)"><vue-feather type="trash"></vue-feather></a>
                                                                        
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                            <tbody v-else>
                                                                <tr>
                                                                <td colspan="5" align="center">Nenhum resultado encontrado</td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div> 
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

    <div class="modal" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLongTitle">Deseja mesmo eliminar este item.</h5>
          
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
            Ao apagar este item, irá apagar todos os registros relacionados a ele.
        </div>
        <div class="modal-footer">
          
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                <button @click.prevent="deleteData" type="button" class="btn btn-danger" :disabled="loadingButtonDelete">
                    <div v-if="loadingButtonDelete" class="spinner-border spinner-border-sm" role="status"></div>
                    <span v-else>Apagar registro</span>
                    </button>
        </div>
      </div>
    </div>
  </div>


  <div class="modal" id="deleteModal2" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLongTitle">Deseja mesmo eliminar este item.</h5>
          
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
            Ao apagar este item, irá apagar todos os registros relacionados a ele.
        </div>
        <div class="modal-footer">
          
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                <button @click.prevent="deleteData2" type="button" class="btn btn-danger" :disabled="loadingButtonDelete">
                    <div v-if="loadingButtonDelete" class="spinner-border spinner-border-sm" role="status"></div>
                    <span v-else>Apagar registro</span>
                    </button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal" id="deleteModal3" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLongTitle">Deseja mesmo eliminar este item.</h5>
          
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
            Ao apagar este item, irá apagar todos os registros relacionados a ele.
        </div>
        <div class="modal-footer">
          
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                <button @click.prevent="deleteData3" type="button" class="btn btn-danger" :disabled="loadingButtonDelete">
                    <div v-if="loadingButtonDelete" class="spinner-border spinner-border-sm" role="status"></div>
                    <span v-else>Apagar registro</span>
                    </button>
        </div>
      </div>
    </div>
  </div>
</template>