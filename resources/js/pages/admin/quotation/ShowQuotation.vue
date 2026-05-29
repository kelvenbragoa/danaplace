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
import { Bar } from 'vue-chartjs'
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js'
import VueHtmlToPaper from "vue-html-to-paper";
import { usePaperizer } from 'paperizer'
ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale)

let retrievedData =ref([]);
let loadingSubmit =ref([true]);
let loadingDiv =ref([true]);
const router = useRouter();
let self = this;
let searchQuery = ref(null)
let equipments = ref([]);
const loadingprint = ref(false);

function formatCurrency(value) {
  if (isNaN(value) || value === null || value === undefined) {
    return '0,00';
  }
  return new Intl.NumberFormat('pt-BR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(Number(value));
}


const { paperize } = usePaperizer('print-me',{
    styles: [
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css'
  ]
})


const downloadMcscr = () =>{
    // axios.get(`/download-mcscr/+${router.currentRoute.value.params.id}`)
    //    .then((response)=>{
    //     console.log(response.data)
    //    }).catch(()=>{
        
    //    })
    loadingprint.value = true;
        // window.print();
        // router.push('/admin/mcscr').catch(()=>{})
        // this.$htmlToPaper('printMe');
        paperize()
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
}
const getData = (page=1) => {
  axios.get(`/quotation/+${router.currentRoute.value.params.id}`,
      {
        params:{
          query: searchQuery.value
        }
      })
       .then((response)=>{
        loadingDiv.value=false;
        retrievedData.value = response.data;
       }).catch(()=>{
        loadingDiv.value=false;
       })
}



onMounted(()=>{
  getData();
})
</script>

<template>
    <div v-if="!loadingDiv">

        <h1 class="h3 mb-3">Quotação</h1>
        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Quotação: #{{ retrievedData.id }}</h5>

                                        <router-link to="/admin/quotation" class="btn btn-pill btn-primary mt-3"><vue-feather type="arrow-left"></vue-feather>Voltar</router-link> 

                                       
								    </div>
                                        
                                        <div class="card-body m-sm-3 m-md-5 border">
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
                                                <div class="row ">
                                                    <div
                                                        class="col text-left"
                                                        style="text-align: left"
                                                    >
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
                                                    <div
                                                        class="col text-right"
                                                        style="text-align: right"
                                                    >
                                                    <strong style="font-size:10px"> COTAÇÃO </strong>
                                                    <p style="font-size:10px"> SQ - #{{ retrievedData.id }}
                                                    </p>
                                                    </div>
                                                </div>
                                            

                                            <hr class="" />


                                            <div class="row">
                                                <div class="col">
                                                    
                                                    
                                                    <p style="font-size:10px">Cliente: <strong>{{ retrievedData.destination_id != 0 ? retrievedData.destination.company_name : retrievedData.company_name }}</strong> 
                                                        <br> 
                                                        Endereço do cliente: <strong>{{ retrievedData.destination_id != 0 ? retrievedData.destination.address : retrievedData.company_address}}, {{ retrievedData.destination_id != 0 ? retrievedData.destination.province.name :  retrievedData.province}} </strong>
                                                        <br>
                                                        Cliente NUIT: <strong>{{ retrievedData.destination_id != 0 ? retrievedData.destination.company_nuit : retrievedData.company_nuit}}</strong> 
                                                        <br>
                                                        Ref: <strong>{{ retrievedData.id }}</strong>
                                                       
                                                    </p>
                                                </div>
                                                <div class="col">
                                                    <br>
                                                </div>
                                                <div class="col text-right">
                                                    
                                                    <p style="font-size:10px">Cotação Data: <strong>{{ moment(retrievedData.created_at).format('DD/MM/YYYY') }}</strong> 
                                                        <br> 
                                                         Data Validade: <strong>{{ moment(retrievedData.expires_at).format('DD/MM/YYYY') }}</strong> 
                                                       
                                                       
                                                    </p>
                                                </div>
                                            </div>

                                            <table class="table table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th class="bg-secondary" style="font-size:10px">#</th>
                                                        <th class="bg-secondary" style="font-size:10px">Descrição</th>
                                                        <th class="bg-secondary" style="font-size:10px">Quantidade</th>
                                                        <th class="bg-secondary" style="font-size:10px">Preço</th>
                                                        <th class="bg-secondary" style="font-size:10px">Disconto</th>
                                                        <th class="bg-secondary" style="font-size:10px">Total</th>
                                                        
                                                    </tr>
                                                </thead>
                                                <tbody v-if="retrievedData.itens.length > 0">
                                                    <tr  v-for="(actualData,index) in retrievedData.itens" :key="actualData.id">
                                                        
                                                        <td style="font-size:10px">#{{ actualData.quotation_id}}</td>
                                                        <td style="font-size:10px">{{ actualData.product_name}}</td>
                                                        <td style="font-size:10px">{{ actualData.quantity}}</td>
                                                        <td style="font-size:10px">{{ formatCurrency(actualData.unit_price)}} {{ retrievedData.coin.name}}</td>
                                                        <td style="font-size:10px">{{ formatCurrency(actualData.discount) }} {{ retrievedData.coin.name}}</td>
                                                        <td style="font-size:10px">{{ formatCurrency(actualData.total - actualData.discount)}} {{ retrievedData.coin.name}}</td>
                                                    </tr>
                                                </tbody>
                                                <tbody v-else>
                                                    <tr>
                                                    <td colspan="7" align="center" style="font-size:10px">Nenhum resultado encontrado</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            <p class="text-right" style="font-size:10px">
                                                <strong>SUBTOTAL</strong>: {{ formatCurrency(retrievedData.total_amount - retrievedData.total_discount) }} {{ retrievedData.coin.name}}
                                            </p>
                                            <p class="text-right" style="font-size:10px">
                                                <strong>IVA 16%</strong>: {{ formatCurrency((retrievedData.total_amount - retrievedData.total_discount) * 0.16) }} {{ retrievedData.coin.name}}
                                            </p>
                                            <p class="text-right" style="font-size:10px">
                                                <strong>TOTAL</strong>: {{ formatCurrency((retrievedData.total_amount - retrievedData.total_discount) + (retrievedData.total_amount - retrievedData.total_discount) * 0.16) }} {{ retrievedData.coin.name}}
                                            </p>

                                            <p style="font-size:10px">
                                                Entregar para: <strong> {{ retrievedData.destination.address }}</strong> <br>
                                                Termos de pagamento: <strong> {{ retrievedData.payment_method }}</strong> <br>
                                                Prazo de entrega: <strong> {{ retrievedData.delivery_date }}</strong> <br>
                                                Meio de Transporte: <strong> {{ retrievedData.type_of_transport }}</strong> <br>
                                            </p>

                                            <p style="font-size:10px">
                                               <strong>Detalhes bancários:</strong>  <br>
                                                    Nome da conta: Condominio Areia Branca Lda NUIT: 400634319 <br> <br>
                                                    <strong>Access Bank</strong>  <br>
                                                    MZN Conta No.: 00002841450113 <br>
                                                    MZN NIB: 006600100284145011320 <br>
                                                    USD Conta No.:00002841450215 <br>
                                                    SWIFT: ABNGMZMA
                                                    <br>
                                                    <br>
                                                    <strong>Standard Bank Mozambique</strong> <br>
                                                    MZN Conta No.: 2018205361007 <br>
                                                    MZN NIB: 000302010820536100796
                                            </p>



                                        </div>
                                        <div class="text-center">
                                    <button @click="downloadMcscr" class="btn btn-primary" :disabled="loadingprint">
                                                <div v-if="loadingprint" class="spinner-border spinner-border-sm" role="status"></div>
                                                <span v-else>Print</span>
                                            </button>
                                            <div v-if="loadingprint" class="d-flex justify-content-center">
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
                <br>
                <div class="d-flex justify-content-center">
                    Carregando Dados...
                </div>
            </div> 
        </div>
    </div>
</template>