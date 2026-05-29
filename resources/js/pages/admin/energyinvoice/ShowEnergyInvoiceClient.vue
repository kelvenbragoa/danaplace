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
const loadingprint = ref(false);
const router = useRouter();
let self = this;
const image = ref();
const destination = ref();
const uploads = ref();
let openedbyuser = ref();
let closedbyuser = ref();
let jobtasks = ref();

let destinationuser = ref();
let requeststock = ref([]);
let requesttool = ref([]);
let requesttechnician = ref([]);
let resolutions = ref();
let preresolutions = ref();
let equipmentref = ref(0);
let jobcardnr = ref(0);
const toastr = useToastr();

function formatCurrency(value) {
  if (isNaN(value) || value === null || value === undefined) {
    return '0,00';
  }
  return new Intl.NumberFormat('pt-BR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(Number(value));
}

let { paperize } = usePaperizer("print-me", {
    styles: [
        "https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css",
    ],
    windowTitle: `REF: ${equipmentref.value} - ENERGY INVOICE Nº: #${router.currentRoute.value.params.id}`,
});

const downloadMcscr = () => {
    loadingprint.value = true;
    paperize();
    loadingprint.value = false;
};

const getData = () => {
    axios
        .get(`/energyinvoice/client/+${router.currentRoute.value.params.id}`)
        .then((response) => {
            loadingDiv.value = false;
            retrievedData.value = response.data.energyinvoiceitem;
            equipmentref.value = response.data.energyinvoiceitem.id;
            jobcardnr.value = response.data.energyinvoiceitem.id;

            if (destination.value.image != null) {
                image.value = "/storage/" + destination.value.image;
            } else {
                image.value = "/files/img/sys/companylogo.png";
            }
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
        <h1 class="h3 mb-3">Fatura Energia</h1>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body m-sm-3 m-md-5 border">
                        <div class="mb-4">
                            <a @click="$router.go(-1)" class="btn btn-pill btn-primary mt-3"><vue-feather type="arrow-left"></vue-feather>Voltar</a> 
                            
                        </div>
                        <div id="print-me">
                            <div class="row">
                                <!-- <div class="col">
                                                <div class="col-2">
                                                    <img src="/files/img/sys/companylogo.png" class="img-fluid" alt="M+D" width="450" height="450">
                                                </div>
                                            </div> -->
                                <br />
                                <div
                                    class="col text-left"
                                    style="text-align: left"
                                >
                                    <img
                                        src="/files/img/sys/companylogo.png"
                                        class="img-fluid"
                                        alt="image"
                                        width="150px"
                                        height="150px"
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
                                        NUIT: 401061711
                                        <br />
                                        Email: info@ieareiabranca.com
                                        <br />
                                        www.areiabranca.com
                                    </p>
                                </div>
                                <div class="col">
                                    <br />
                                </div>
                            </div>
                            
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th style="font-size: 10px;">
                                            DATE:
                                            {{
                                                moment(
                                                    retrievedData.created_at
                                                ).format("DD-MM-YYYY H:mm")
                                            }}
                                        </th>
                                        <th style="font-size: 10px;">
                                            FROM:
                                            {{
                                                moment(
                                                    retrievedData.energyinvoice.start_date_period
                                                ).format("DD-MM-YYYY H:mm")
                                            }}
                                        </th>
                                        <th style="font-size: 10px;">
                                            TO:
                                            {{
                                                moment(
                                                    retrievedData.energyinvoice.end_date_period
                                                ).format("DD-MM-YYYY H:mm")
                                            }}
                                        </th>
                                        <th style="font-size: 10px;">
                                            FATURA Nº: #{{ retrievedData.id }}
                                        </th>
                                    </tr>
                                </thead>
                                
                            </table>
                            <div class="row">
                                <div class="col-12">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th
                                                    colspan="3"
                                                    class="bg-secondary"
                                                    style="font-size: 10px;"
                                                >
                                                     INFORMATION
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td style="font-size: 10px;">Cliente</td>
                                                <td style="font-size: 10px;" colspan="2">
                                                    {{ retrievedData.destination.name }}
                                                </td>
                                                
                                            </tr>
                                             <tr>
                                                <td style="font-size: 10px;">Activo</td>
                                                <td style="font-size: 10px;" colspan="2">
                                                    {{ retrievedData.equipment.name }} - {{ retrievedData.equipment.ref }} 
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="font-size: 10px;">Consumo</td>
                                                <td style="font-size: 10px;" colspan="2">
                                                    {{ formatCurrency(retrievedData.apr_consumption) }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="font-size: 10px;">Total</td>
                                               
                                                <td style="font-size: 10px;" colspan="2">
                                                    {{ formatCurrency(retrievedData.total_to_invoice) }} MT
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                            
                             
                            </div>

                            <div class="row mt-5">
                                <strong style="font-size: 10px;">Bank Details:</strong>
                                <p style="font-size: 10px;"><strong>BCI:</strong> 22122363310001</p>
                                
                            </div>
                            <div class="row">
                                <div
                                    class="col text-left"
                                    style="text-align: left"
                                >
                                    <img
                                        src="/files/img/sys/carrimbo.png"
                                        class="img-fluid"
                                        alt="image"
                                        width="230px"
                                        height="230px"
                                        style="text-align: left; transform: rotate(-5deg);"
                                    />
                                </div>
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

