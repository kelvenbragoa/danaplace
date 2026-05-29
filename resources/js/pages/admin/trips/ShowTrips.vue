<script setup>
import axios from "axios";
import {
    ref,
    onMounted,
    reactive,
    defineEmits,
    defineComponent,
    watch,
} from "vue";
import moment from "moment";
import { useToastr } from "../../../toastr";
import { debounce } from "lodash";
import { Form, Field } from "vee-validate";
import { useRouter } from "vue-router";
import * as yup from "yup";
import VueFeather from "vue-feather";
import { Bootstrap4Pagination } from "laravel-vue-pagination";
import VueHtmlToPaper from "vue-html-to-paper";
import { usePaperizer } from "paperizer";

let retrievedData = ref([]);
let loadingSubmit = ref([true]);
let loadingDiv = ref([true]);
let expenses = ref([]);
const loadingprint = ref(false);
const router = useRouter();
let self = this;
let requeststock = ref([]);
let requesttool = ref([]);
let requesttechnician = ref([]);
const toastr = useToastr();
const { paperize } = usePaperizer("print-me", {
    styles: [
        "https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css",
    ],
});

const downloadMcscr = () => {

    loadingprint.value = true;
    paperize();
    loadingprint.value = false;

};

const conditionStatus = (status) => {
    switch (status) {
        case 1:
            return "New";
        case 2:
            return "Very Good Condition";
        case 3:
            return "Good";
        case 4:
            return "Works with Disabilities";
        case 5:
            return "Works With Great Disabilities";
        default:
            return "Other";
    }
};
const conditionOperation = (status) => {
    switch (status) {
        case 1:
            return "Monitor and Follow Maintenance Plans";
        case 2:
            return "Monitor and Follow Maintenance Plans";
        case 3:
            return "Needs Minor Repairs (Tightenin)";
        case 4:
            return "Requires Localized Repairs";
        case 5:
            return "Needs Complete Repair";
        default:
            return "Other";
    }
};

const getData = () => {
    axios
        .get(`/trips/+${router.currentRoute.value.params.id}`)
        .then((response) => {
            loadingDiv.value = false;
            retrievedData.value = response.data.trips;
            expenses.value = response.data.expenses
            // requeststock.value = response.data.requeststock
            // requesttool.value = response.data.requesttool
            // requesttechnician.value = response.data.requesttechnician
        })
        .catch(() => {
            loadingDiv.value = false;
        });
};

onMounted(() => {
    getData();
});
</script>

<template>
    <div v-if="!loadingDiv">
        <h1 class="h3 mb-3">Viagens</h1>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body m-sm-3 m-md-5 border">
                        <div class="mb-4">
                            <router-link
                                to="/admin/trips"
                                class="btn btn-pill btn-primary mt-3"
                                ><vue-feather type="arrow-left"></vue-feather
                                >Voltar</router-link
                            >
                        </div>
                        <div id="print-me">

                            <div class="row ">
                                <div
                                    class="col text-left"
                                    style="text-align: left"
                                >
                                    <img
                                        src="/files/img/sys/companylogo.png"
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
                                        class="img-fluid mt-2"
                                        alt="image"
                                        width="250px"
                                        height="250px"
                                        style="text-align: right"
                                    /> -->
                                </div>
                            </div>

                            
                            <div class="row">
                                <div class="col">
                                    <!-- <div class="text-muted">Área de Manutenção</div> -->
                                    <!-- <strong> {{ retrievedData.area.name ?? 'N/A' }} </strong> -->
                                    <p style="font-size:10px">
                                        Condominio Areia Branca Lda
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
                                </div>
                                <div class="col">
                                    <br />
                                </div>
                                <!-- <div class="col text-right" style="text-align: right;">
                                                <div class="text-muted" style="text-align: right;">Clientes</div>
                                                <strong style="text-align: right;"> {{ retrievedData.destination.name ?? 'N/A' }} </strong>
                                                <p style="text-align: right;"> {{ retrievedData.destination.company_name ?? 'N/A' }} 
                                                    <br> 
                                                    {{ retrievedData.destination.company_address ?? 'N/A' }}
                                                    <br>
                                                    {{ retrievedData.destination.company_nuit ?? 'N/A' }}
                                                    <br>
                                                    {{ retrievedData.destination.province.name ?? 'N/A' }}
                                                    <br>
                                                    {{ retrievedData.destination.company_mobile ?? 'N/A' }}
                                                    <br>
                                                    {{ retrievedData.destination.company_email ?? 'N/A' }}
                                                </p>
                                            </div> -->
                            </div>
                            
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th class="bg-secondary">TRIP Nº:</th>
                                        <th class="bg-secondary">
                                            NAME:
                                        </th>
                                        <th class="bg-secondary">
                                            DESTINATION:
                                        </th>
                                        <th class="bg-secondary">
                                            DEPARTURE DATE:
                                        </th>
                                        <th class="bg-secondary">
                                            RETURN DATE:
                                        </th>
                                        <th class="bg-secondary">
                                            TOTAL EXPENSE:
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            #{{
                                                retrievedData.id
                                            }}
                                           
                                        </td>
                                        <td>
                                            {{
                                                retrievedData.name
                                            }}
                                           
                                        </td>
                                        <td>
                                            {{
                                                retrievedData.destination
                                            }}
                                           
                                        </td>
                                        <td>
                                            {{
                                                
                                                moment(
                                                    retrievedData.departure_date
                                                ).format("DD-MM-YYYY H:mm")
                                            }}
                                        </td>

                                        <td>
                                            {{
                                               moment(
                                                    retrievedData.return_date
                                                ).format("DD-MM-YYYY H:mm")
                                            }}
                                        </td>
                                        
                                        <td>
                                            {{
                                                retrievedData.total_expenses
                                            }}
                                           
                                        </td>
                                      
                                    </tr>
                                </tbody>
                            </table>
                          
                            <table class="table table-bordered">
                                <thead>
                                   
                                    <tr>
                                        <th class="bg-secondary">
                                            NAME
                                        </th>
                                        <th class="bg-secondary">
                                            DESCRIPTION
                                        </th>
                                        <th class="bg-secondary">AMOUNT</th>
                                    </tr>
                                </thead>
                                <tbody v-if="expenses.length > 0">
                                    <tr  v-for="(actualData,index) in expenses" :key="actualData.id">
                                        <td>{{ actualData.name }}</td>
                                        <td>{{ actualData.description }}</td>
                                        <td>{{ actualData.amount }} MT</td>
                                    </tr>

                                    
                                </tbody>
                                <tbody v-else>
                                        <tr>
                                            <td colspan="4" align="center">Nenhum resultado encontrado</td>
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
                                        <td>NAME:</td>
                                        <td>SIGNATURE:</td>
                                    </tr>
                                    <tr>
                                        <td>NAME:</td>
                                        <td>SIGNATURE:</td>
                                    </tr>
                                </tbody>
                            </table>
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
                <br />
                <div class="d-flex justify-content-center">
                    Carregando Dados...
                </div>
            </div>
        </div>
    </div>
</template>
