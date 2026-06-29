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
import { usePaperizer } from "paperizer";

let retrievedData =ref([]);
const searchQuery = ref(null);
let equipments =ref([]);
let loadingSubmit =ref([true]);
let loadingDiv =ref([true]);
const router = useRouter();
let self = this;
let currentvalue = ref([]);
let expenses = ref([]);
let destination = ref([]);
const loadingprint = ref(false);

const { paperize } = usePaperizer("print-me", {
    styles: [
        "https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css",
    ],
});
let total_expense = ref(0)

const toastr = useToastr();

const schema = yup.object({
    
    expense_description: yup.string().required(),
    expense_amount: yup.string().required(),
     
  });

  const createRecordFunction = (values, actions) => {

 
currentvalue.value = {values};

loadingSubmit.value = true;

const arr = Array.from(values)

axios.post('/tripexpense',values).then((response)=>{

// categories.value.unshift(response.data);
// $('#createCategory').modal('hide');
expenses.value = response.data.expense;
total_expense.value = response.data.total_expense;


actions.resetField('expense_amount');
actions.resetField('expense_description');


toastr.success('Despesa criado com sucesso');
}).catch((error)=>{

loadingSubmit.value = false;
toastr.error('Erro ao adicionar. '+error.response.data.message);
if(error.response.data.errors){
   
    actions.setErrors(error.response.data.errors);
}
}).finally(()=>{
loadingSubmit.value = false;

})



};

const downloadMcscr = () => {
    // axios.get(`/download-mcscr/+${router.currentRoute.value.params.id}`)
    //    .then((response)=>{
    //     console.log(response.data)
    //    }).catch(()=>{

    //    })
    loadingprint.value = true;
    // window.print();
    // router.push('/admin/mcscr').catch(()=>{})
    // this.$htmlToPaper('printMe');
    paperize();
    loadingprint.value = false;

    // axios({
    //     url:`/download-mcscr/+${router.currentRoute.value.params.id}`,
    //     responseType:'blob'
    // }).then((response)=>{
    //     const url = window.URL.createObjectURL(new Blob([response.data]));
    //     const link = document.createElement('a');
    //     link.href = url;
    //     link.setAttribute('download', 'mcscr-nr-'+retrievedData.value.id+'.pdf');
    //     document.body.appendChild(link);
    //     link.click();
    //     loadingprint.value = false;
    //     toastr.success('Documento baixado com sucesso');
    // }).catch((error)=>{

    //     loadingprint.value = false;
    //     toastr.error('Ocorreu um erro ao tentar baixar o documento. '+error.response.data.message);

    // }).finally(()=>{
    //     loadingprint.value = false;
    // })
};





const getData = async (page = 1) => {
  axios.get(`/logistictrip/+${router.currentRoute.value.params.id}?page=${page}`, 
  {
        params:{
          query: searchQuery.value
        }
      })
       .then((response)=>{
        loadingDiv.value=false;
        retrievedData.value = response.data.trip;
        destination.value = response.data.destination;
        expenses.value = response.data.expense;
        total_expense.value = response.data.total_expense;
     
       }).catch(()=>{
        loadingDiv.value=false;
       })
}

watch(searchQuery,debounce(()=>{
    getData();
},300));


onMounted(()=>{
  getData();
})
</script>

<template>
    <div v-if="!loadingDiv">

        <h1 class="h3 mb-3">Viagens</h1>
        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Viagens: {{ retrievedData.name }}</h5>

                                        <router-link to="/admin/logistictrip" class="btn btn-pill btn-primary mt-3"><vue-feather type="arrow-left"></vue-feather>Voltar</router-link> 

                                       
								    </div>
                                    
                                    <div class="card-body">
                                          
                                        <div class="row">
                                            <div class="col-xl-12 col-xxl-12 d-flex">
                                                <div class="w-100">
                                                    <p>Partida: {{ retrievedData.destination.departure }}</p>
                                                    <p>Destino: {{ retrievedData.destination.destination }}</p>
                                                    <p>Distancia total: {{ retrievedData.destination.total_distance }} KM</p>
                                                    <p>Tipo de Carregamento: {{ retrievedData.destination.loadstatus.name }}</p>
                                                    <p>Estado: 
                                                        <span v-if="retrievedData.trip_status_id == 1" class="badge bg-warning">
                                                                {{ retrievedData.tripstatus.name}}
                                                            </span>
                                                            <span v-if="retrievedData.trip_status_id == 2" class="badge bg-primary">
                                                                {{ retrievedData.tripstatus.name}}
                                                            </span>
                                                            <span v-if="retrievedData.trip_status_id == 3" class="badge bg-success">
                                                                {{ retrievedData.tripstatus.name}}
                                                            </span>
                                                            <span v-if="retrievedData.trip_status_id == 4" class="badge bg-danger">
                                                                {{ retrievedData.tripstatus.name}}
                                                            </span>
                                                    </p>
                                                    <p>Despesa Total: {{ total_expense }} MT</p>

                                                    <hr>

                                                    <div class="card-header">
                                                        <h5 class="card-title">Despesas: {{expenses.length}} registros encontrados.</h5>
                                                        <div class="row">
                                                            <div class="col">
                                                                <a class="btn btn-pill btn-primary mt-3" data-toggle="collapse" href="#collapseExample" role="button" aria-expanded="false" aria-controls="collapseExample">
                                                                    <vue-feather type="plus"></vue-feather>Adicionar nova despesa
                                                                </a>
                                                            </div>
                                                            
                                                        </div>
                                                        
                                                        <div class="collapse mt-3" id="collapseExample">
                                                            <div class="card card-body">
                                                                
                                                                <Form @submit="createRecordFunction" :validation-schema="schema" v-slot="{ errors }">
                                                                    <div class="row">
                                                                        <div class="mb-3 col-md-6">
                                                                            <label class="form-label" for="expense_description">Nome</label>
                                                                            <Field type="text" class="form-control" :class="{'is-invalid':errors.expense_description}" name="expense_description" id="expense_description" placeholder="Nome"/>
                                                                            <span class="invalid-feedback">{{ errors.expense_description }}</span>
                                                                        </div>
                                                                    </div>
                                                                
                                                                    <div class="row">
                                                                        <div class="mb-3 col-md-6">
                                                                            <label class="form-label" for="expense_amount">Valor</label>
                                                                            <Field type="number" class="form-control" :class="{'is-invalid':errors.expense_amount}" name="expense_amount" id="expense_amount" placeholder="Valor"/>
                                                                            <span class="invalid-feedback">{{ errors.expense_amount }}</span>
                                                                            <Field type="hidden" class="form-control" :class="{'is-invalid':errors.trip_id}" v-model="retrievedData.id" name="trip_id" id="trip_id" placeholder="Valor"/>

                                                                        </div>
                                                                    </div>
                                                                   
                                                                    <button type="submit" class="btn btn-primary" :disabled="loadingSubmit == true">
                                                                        <div v-if="loadingSubmit == true" class="spinner-border spinner-border-sm" role="status"></div>
                                                                        <span v-else>Criar nova despesa</span>
                                                                    </button>
                                                                </Form>
                                                            </div>
                                                        </div>
                                                    </div>

                                        

                                                    <div class="table-responsive">
                                                        <table class="table table-striped">
                                                            <thead>
                                                                <tr>
                                                                    <th>#</th>
                                                                    <th>Nome</th>
                                                                    <th>Valor</th>
                                                                    <th>Data</th>
                                                                    <!-- <th>Ações</th> -->
                                                                </tr>
                                                            </thead>
                                                            <tbody v-if="expenses.length > 0">
                                                                <tr v-for="(actualData,index) in expenses" :key="actualData.id">
                                                                    <td>#{{ index + 1 }}</td>
                                                                    <td>{{ actualData.expense_description}}</td>
                                                                    <td>{{ actualData.expense_amount}} MT</td>
                                                                    <td>{{ moment(actualData.created_at).format('DD-MM-YYYY H:m')}}</td>
                                                                    <!-- <td>
                                                                        <router-link :to="'/admin/type_equipments/component/'+actualData.id+'/edit'"><vue-feather type="edit-2"></vue-feather></router-link>
                                                                        <router-link :to="'/admin/type_equipments/component/'+actualData.id"><vue-feather type="eye"></vue-feather></router-link> 
                                                                        <a href="#" @click.prevent="confirmDeletion(actualData)"><vue-feather type="trash"></vue-feather></a>
                                                                    </td> -->
                                                                </tr>
                                                            </tbody>
                                                            <tbody v-else>
                                                                <tr>
                                                                <td colspan="8" align="center">Nenhum resultado encontrado</td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>

                                                    <!-- <div class="row">
                                                        <div class="col-sm-3">
                                                            <div class="card">
                                                                <div class="card-body">
                                                                    <h5 class="card-title mb-4">Centro de custo</h5>
                                                                    <h1 class="mt-1 mb-3">{{retrievedData.name}}</h1>
                                                                    <div class="mb-1">
                                                                        <span class="text-muted">Código: {{retrievedData.code}}</span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-sm-3">
                                                            <div class="card">
                                                                <div class="card-body">
                                                                    <h5 class="card-title mb-4">Contas Associadas</h5>
                                                                    <h1 class="mt-1 mb-3">{{retrievedData.accounts.length}}</h1>
                                                                    <div class="mb-1">
                                                                        <span class="text-success"> <i class="mdi mdi-arrow-bottom-right"></i> </span>
                                                                        <span class="text-muted"></span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-sm-3">
                                                            <div class="card">
                                                                <div class="card-body">
                                                                    <h5 class="card-title mb-4">Equipamentos/Activos</h5>
                                                                    <h1 class="mt-1 mb-3">0</h1>
                                                                    <div class="mb-1">
                                                                        <span class="text-success"> <i class="mdi mdi-arrow-bottom-right"></i> </span>
                                                                        <span class="text-muted"></span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                    </div> -->
                                                        
                                                </div>
                                            </div>
                                        </div>

                                        <div id="print-me">

                                            <div class="row ">
                                                <div
                                                    class="col text-left"
                                                    style="text-align: left"
                                                >
                                                    <img
                                                        src="/files/img/sys/image.png"
                                                        class="img-fluid"
                                                        alt="image"
                                                        width="150"
                                                        height="150"
                                                        style="text-align: left"
                                                    />
                                                </div>
                                                <div class="col">
                                                    <br />
                                                </div>
                                                <div
                                                    class="col text-right"
                                                    style="text-align: right"
                                                >
                                                    <!-- <img
                                                        src="/files/img/sys/volvopenta1.png"
                                                        class="img-fluid"
                                                        alt="image"
                                                        width="150"
                                                        height="150"
                                                        style="text-align: right"
                                                    /> -->
                                                </div>
                                            </div>


                                            <div class="row">
                                                <div class="col text-left"
                                                style="text-align: left">
                                                    <!-- <div class="text-muted">Área de Manutenção</div> -->
                                                    <!-- <strong> {{ retrievedData.area.name ?? 'N/A' }} </strong> -->
                                                    <p style="font-size:10px">
                                                        Dana Place
                                                        <br />
                                                        Cimento a Ponta de Ouro
                                                        <br />
                                                        Matutuine, Moçambique
                                                        <br />
                                                        Tel: +258 87 914 1774
                                                        <br />
                                                        Email: info@ieareiabranca.com
                                                        <br />
                                                        www.areiabranca.com
                                                    </p>
                                                    <!-- <p>
                                                        Industrial Engines Lda
                                                        <br />
                                                        Av. Samora Machel, nº372F
                                                        <br />
                                                        Matola, Moçambique
                                                        <br />
                                                        Tel: +258 87 914 1774
                                                        <br />
                                                        Email: sales@ie.co.mz
                                                        <br />
                                                        www.ie.co.mz
                                                    </p> -->
                                                </div>
                                                <div class="col">
                                                    <br />
                                                </div>
                                                <div
                                                    class="col text-right"
                                                    style="text-align: right"
                                                >
                                                <p>
                                                        {{ retrievedData.customer == null ? "" : retrievedData.customer.customer_name}}
                                                        <br />
                                                        {{ retrievedData.customer == null ? "" : retrievedData.customer.customer_address}}
                                                        <br />
                                                        {{ retrievedData.customer == null ? "" : retrievedData.customer.customer_nuit}}
                                                        <br />
                                                        Tel: {{ retrievedData.customer == null ? "" : retrievedData.customer.customer_mobile}}
                                                        <br />
                                                        Email: {{ retrievedData.customer == null ? "" : retrievedData.customer.customer_email}}
                                                       
                                                    </p>
                                                </div>
                                               
                                            </div>
                                            <table class="table table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>
                                                            DATE:
                                                            {{
                                                                moment(
                                                                    retrievedData.created_at
                                                                ).format("DD-MM-YYYY H:mm")
                                                            }}
                                                        </th>
                                                        <th>
                                                            FLEET:
                                                            {{
                                                                retrievedData.equipment
                                                                    .type_equipment.name ??
                                                                "N/A"
                                                            }}
                                                        </th>
                                                        <th>
                                                            REF:
                                                            {{
                                                                retrievedData.equipment.ref ??
                                                                "N/A"
                                                            }}
                                                        </th>
                                                        <th>
                                                            TRIP Nº: #{{ retrievedData.id }}
                                                        </th>
                                                    </tr>
                                                </thead>
                                            </table>
                                            <table class="table table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th class="bg-secondary">START DATE:</th>
                                                        <th class="bg-secondary">
                                                            END DATE:
                                                        </th>
                                                        <th class="bg-secondary">
                                                            TRIP Nº:
                                                        </th>
                                                        <th class="bg-secondary">
                                                            TOTAL EXPENSE:
                                                        </th>
                                                        <th class="bg-secondary">
                                                            STATUS:
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td>
                                                            {{ retrievedData.start_date }}
                                                        </td>
                                                        <td>
                                                            {{ retrievedData.end_date }}
                                                        </td>
                                                        
                                                        <td>
                                                            {{ retrievedData.id }}
                                                        
                                                        </td>
                                                        <td>
                                                            {{ total_expense }} MT
                                                        </td>
                                                            
                                                        <td>
                                                            <span v-if="retrievedData.trip_status_id == 1" class="badge bg-warning">
                                                                {{ retrievedData.tripstatus.name}}
                                                            </span>
                                                            <span v-if="retrievedData.trip_status_id == 2" class="badge bg-primary">
                                                                {{ retrievedData.tripstatus.name}}
                                                            </span>
                                                            <span v-if="retrievedData.trip_status_id == 3" class="badge bg-success">
                                                                {{ retrievedData.tripstatus.name}}
                                                            </span>
                                                            <span v-if="retrievedData.trip_status_id == 4" class="badge bg-danger">
                                                                {{ retrievedData.tripstatus.name}}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>

                                            <table class="table table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th class="bg-secondary">DEPARTURE:</th>
                                                        <th class="bg-secondary">
                                                            DESTINATION:
                                                        </th>
                                                        <th class="bg-secondary">
                                                            TOTAL DISTANCE:
                                                        </th>
                                                        <th class="bg-secondary">
                                                            LOAD:
                                                        </th>
                                                        
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td>
                                                            {{ retrievedData.destination.departure }}
                                                        </td>
                                                        <td>
                                                            {{ retrievedData.destination.destination }}
                                                        </td>
                                                        
                                                        <td>
                                                            {{ retrievedData.destination.total_distance }} KM
                                                        
                                                        </td>
                                                        <td>
                                                            {{ retrievedData.destination.loadstatus.name }}
                                                        </td>
                                                            
                                                       
                                                    </tr>
                                                </tbody>
                                            </table>
                                            
                                            <div class="row">
                                                <div class="col-6">
                                                    <table class="table table-bordered">
                                                        <thead>
                                                            <tr>
                                                                <th
                                                                    colspan="2"
                                                                    class="bg-secondary"
                                                                >
                                                                    DRIVER INFORMATION
                                                                </th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <td>NAME</td>
                                                                <td>
                                                                    {{
                                                                        retrievedData.driver
                                                                            .name
                                                                             ??
                                                                        "N/A"
                                                                    }}
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>MOBILE</td>
                                                                <td>
                                                                    {{
                                                                        retrievedData.driver
                                                                            .mobile
                                                                             ??
                                                                        "N/A"
                                                                    }}
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>LICENSE</td>
                                                                <td>
                                                                    {{
                                                                        retrievedData.driver
                                                                            .license
                                                                             ??
                                                                        "N/A"
                                                                    }}
                                                                </td>
                                                            </tr>
                                                            
                                                             
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <div class="col-6">
                                                    <table class="table table-bordered">
                                                        <thead>
                                                            <tr>
                                                                <th
                                                                    colspan="2"
                                                                    class="bg-secondary"
                                                                >
                                                                    Equi INFORMATION
                                                                </th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <td>NAME</td>
                                                                <td>
                                                                    {{
                                                                        retrievedData.equipment
                                                                            .name ?? "N/A"
                                                                    }}
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>MODEL</td>
                                                                <td>
                                                                    {{
                                                                        retrievedData.equipment
                                                                            .model ?? "N/A"
                                                                    }}
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>REF</td>
                                                                <td>
                                                                    {{
                                                                        retrievedData.equipment
                                                                            .ref ?? "N/A"
                                                                    }}
                                                                </td>
                                                            </tr>
                                                           
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>



                                            
                                            <table class="table table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th colspan="4" class="bg-secondary">EXPENSES</th>
                                                    </tr>
                                                    <tr>
                                                        <th class="bg-secondary">ITEM</th>
                                                        <th class="bg-secondary">DESCRIPTION</th>
                                                        <th class="bg-secondary">AMOUNT</th>
                                                        <th class="bg-secondary">DATE</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr v-for="(actualData,index) in expenses" :key="actualData.id">
                                                                    <td>#{{ index + 1 }}</td>
                                                                    <td>{{ actualData.expense_description}}</td>
                                                                    <td>{{ actualData.expense_amount}} MT</td>
                                                                    <td>{{ moment(actualData.created_at).format('DD-MM-YYYY H:m')}}</td>
                                                                   
                                                                </tr>
                                                </tbody>
                                            </table>


                                  

                                            <table class="table table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th class="bg-secondary" colspan="2">
                                                            SIGNATURE
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td>TECHNICIAN NAME:</td>
                                                        <td>SIGNATURE:</td>
                                                    </tr>
                                                    <tr>
                                                        <td>DRIVER NAME:</td>
                                                        <td>SIGNATURE:</td>
                                                    </tr>
                                                </tbody>
                                            </table>

                                            <!-- <div style="page-break-before:always">&nbsp;</div> -->

                                       


                                     

















                                            </div>

                                    </div>
                                    <div class="text-center">
                        <button
                            @click="downloadMcscr"
                            class="btn btn-primary"
                            :disabled="loadingprint"
                        >
                            <div
                                v-if="loadingprint"
                                class="spinner-border spinner-border-sm"
                                role="status"
                            ></div>
                            <span v-else>Print</span>
                        </button>
                        <div
                            v-if="loadingprint"
                            class="d-flex justify-content-center"
                        >
                            Aguarde, Estamos gerando o seu documento...
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