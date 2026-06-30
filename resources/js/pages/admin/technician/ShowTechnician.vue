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
let loadingSubmit =ref([true]);
let loadingDiv =ref([true]);
const router = useRouter();
let self = this;
let searchQuery = ref(null)
let equipments = ref([]);
const loadingPrint = ref(false);

// Paperizer configurations
let { paperize: paperizeEmployeeFile } = usePaperizer("print-employee-file", {
    styles: [
        "https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css",
    ],
    windowTitle: `Ficha do Funcionário`,
});

let { paperize: paperizeEmployeeCard } = usePaperizer("print-employee-card", {
    styles: [
        "https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css",
    ],
    windowTitle: `Cartão do Funcionário`,
});







const getData = (page=1) => {
  axios.get(`/technicians/+${router.currentRoute.value.params.id}?page=${page}`,
      {
        params:{
          query: searchQuery.value
        }
      })
       .then((response)=>{
        loadingDiv.value=false;
        retrievedData.value = response.data.technician;
    
       }).catch(()=>{
        loadingDiv.value=false;
       })
}


watch(searchQuery,debounce(()=>{
    getData();
},300));

const printEmployeeFile = () => {
    loadingPrint.value = true;
    paperizeEmployeeFile();
    loadingPrint.value = false;
}

const printEmployeeCard = () => {
    loadingPrint.value = true;
    paperizeEmployeeCard();
    loadingPrint.value = false;
}

const formatCurrency = (value) => {
    return new Intl.NumberFormat('pt-MZ', {
        style: 'currency',
        currency: 'MZN'
    }).format(value);
}

const formatDate = (date) => {
    return moment(date).format('DD/MM/YYYY');
}

const formatDateTime = (dateTime) => {
    return moment(dateTime).format('DD/MM/YYYY HH:mm');
}

const getYearsOfWork = () => {
    if (!retrievedData.value.admission_date) return 0;
    return moment().diff(retrievedData.value.admission_date, 'years');
}

onMounted(()=>{

  getData();

})
</script>

<template>
    <div v-if="!loadingDiv">

        <h1 class="h3 mb-3">Técnico</h1>
        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h5 class="card-title mb-0">Técnico: {{ retrievedData.name }}</h5>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="float-end">
                                                    <button @click="printEmployeeFile" class="btn btn-outline-success me-2" :disabled="loadingPrint">
                                                        <div v-if="loadingPrint" class="spinner-border spinner-border-sm me-2"></div>
                                                        <vue-feather v-else type="printer" size="16" class="me-1"></vue-feather>
                                                        {{ loadingPrint ? 'Imprimindo...' : 'Imprimir Ficha' }}
                                                    </button>
                                                    <button @click="printEmployeeCard" class="btn btn-outline-info me-2" :disabled="loadingPrint">
                                                        <div v-if="loadingPrint" class="spinner-border spinner-border-sm me-2"></div>
                                                        <vue-feather v-else type="credit-card" size="16" class="me-1"></vue-feather>
                                                        {{ loadingPrint ? 'Imprimindo...' : 'Imprimir Cartão' }}
                                                    </button>
                                                    <router-link to="/admin/technicians" class="btn btn-primary">
                                                        <vue-feather type="arrow-left" size="16" class="me-1"></vue-feather>
                                                        Voltar
                                                    </router-link>
                                                </div>
                                            </div>
                                        </div>
								    </div>
                                    
                                    <div class="card-body">
                                          
                                        <div class="row">
                                            <div class="col-xl-8 col-xxl-8 d-flex">
                                                <div class="w-100">
                                                    <p>Nome do Técnico: {{ retrievedData.name }}</p>     
                                                    <p>Código: {{ retrievedData.code }}</p>     
                                                    <p>Documento: {{ retrievedData.document }}</p>   
                                                    <p>Telefone Celular: {{ retrievedData.mobile_phone }}</p>   
                                                    <p>Departamento: {{ retrievedData.department.name }}</p>  
                                                    <p>Área: {{ retrievedData.area.name }}</p>  
                                                    <p>Data de Admissão: {{ retrievedData.admission_date }}</p>  
                                                    <p>Anos de trabalho: {{ moment().diff(retrievedData.admission_date,'years')}} Anos</p>
                                                    <p>Estado: 
                                                        <span v-if="retrievedData.status == 1" class="badge bg-success">
                                                                Sem trabalho
                                                            </span>
                                                            <span v-if="retrievedData.status == 0" class="badge bg-danger">
                                                                Em trabalho
                                                            </span>
                                                    </p>   
                                                </div>
                                            </div>
                                            <div class="col-xl-4 col-xxl-4 d-flex">
                                                <div class="w-100">
                                                                
                                                                    <img :src='retrievedData.image' alt="" class="w-100">
                                                              
                                                </div>
                                            </div>
                                        </div>
                                    </div>
								</div>
                            </div>   
                        </div>

                        <!-- HR Separator -->
                        <hr class="my-4" style="border: 2px solid #dee2e6;">

                        <!-- Seção de Impressões -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">
                                            <vue-feather type="file-text" size="20" class="me-2"></vue-feather>
                                            Documentos para Impressão
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="card border-success">
                                                    <div class="card-body text-center">
                                                        <vue-feather type="file-text" size="48" class="text-success mb-3"></vue-feather>
                                                        <h6 class="card-title">Ficha do Funcionário</h6>
                                                        <p class="card-text small">Documento completo com todas as informações do técnico para arquivo de RH.</p>
                                                        <button @click="printEmployeeFile" class="btn btn-success" :disabled="loadingPrint">
                                                            <div v-if="loadingPrint" class="spinner-border spinner-border-sm me-2"></div>
                                                            <vue-feather v-else type="printer" size="16" class="me-1"></vue-feather>
                                                            Imprimir Ficha Completa
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="card border-info">
                                                    <div class="card-body text-center">
                                                        <vue-feather type="credit-card" size="48" class="text-info mb-3"></vue-feather>
                                                        <h6 class="card-title">Cartão do Funcionário</h6>
                                                        <p class="card-text small">Cartão de identificação compacto para uso diário e identificação.</p>
                                                        <button @click="printEmployeeCard" class="btn btn-info" :disabled="loadingPrint">
                                                            <div v-if="loadingPrint" class="spinner-border spinner-border-sm me-2"></div>
                                                            <vue-feather v-else type="credit-card" size="16" class="me-1"></vue-feather>
                                                            Imprimir Cartão ID
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Templates de Impressão (ocultos) -->
                        <div style="display: none;">
                            <!-- Template da Ficha do Funcionário -->
                            <div id="print-employee-file">
                                <!-- Header da Ficha -->
                                <div class="row text-center">
                                    <div class="col text-center" style="text-align: center">
                                        <h2>Dana Place</h2>
                                        <h3>Ficha do Funcionário</h3>
                                        <h4>{{ retrievedData.name }}</h4>
                                    </div>
                                </div>
                                
                                <!-- Logo e Foto do Funcionário -->
                                <div class="row">
                                    <div class="col text-left" style="text-align: left">
                                        <img
                                            src="/files/img/sys/companylogo.png"
                                            class="img-fluid"
                                            alt="image"
                                            width="120px"
                                            height="120px"
                                            style="text-align: left"
                                        />
                                    </div>
                                    <div class="col">
                                        <br />
                                    </div>
                                    <div class="col text-right" style="text-align: right">
                                        <img
                                            :src="retrievedData.image"
                                            alt="Foto do Funcionário"
                                            style="width: 120px; height: 150px; object-fit: cover; border: 2px solid #333;"
                                        />
                                        <p style="font-size: 8px; margin-top: 5px; text-align: center;">
                                            Foto do Funcionário
                                        </p>
                                    </div>
                                </div>

                                <!-- Informações da Empresa -->
                                <div class="row">
                                    <div class="col">
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
                                    </div>
                                    <div class="col">
                                        <br />
                                    </div>
                                </div>
                                
                                <!-- Cabeçalho da Ficha -->
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th style="font-size: 10px;">
                                                CÓDIGO:
                                                {{ retrievedData.code }}
                                            </th>
                                            <th style="font-size: 10px;">
                                                DATA ADMISSÃO:
                                                {{ formatDate(retrievedData.admission_date) }}
                                            </th>
                                            <th style="font-size: 10px;">
                                                ANOS DE SERVIÇO:
                                                {{ getYearsOfWork() }} Anos
                                            </th>
                                            <th style="font-size: 10px;">
                                                STATUS: 
                                                {{ retrievedData.status == 1 ? 'DISPONÍVEL' : 'EM TRABALHO' }}
                                            </th>
                                        </tr>
                                    </thead>
                                </table>

                                <!-- Dados Pessoais -->
                                <div class="row">
                                    <div class="col-6">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th colspan="2" class="bg-primary text-white" style="font-size: 10px;">
                                                        DADOS PESSOAIS
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td style="font-size: 10px; font-weight: bold;">Nome Completo</td>
                                                    <td style="font-size: 10px;">{{ retrievedData.name }}</td>
                                                </tr>
                                                <tr>
                                                    <td style="font-size: 10px; font-weight: bold;">Código de Funcionário</td>
                                                    <td style="font-size: 10px;">{{ retrievedData.code }}</td>
                                                </tr>
                                                <tr>
                                                    <td style="font-size: 10px; font-weight: bold;">Documento de Identidade</td>
                                                    <td style="font-size: 10px;">{{ retrievedData.document }}</td>
                                                </tr>
                                                <tr>
                                                    <td style="font-size: 10px; font-weight: bold;">Data de Admissão</td>
                                                    <td style="font-size: 10px;">{{ formatDate(retrievedData.admission_date) }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="col-6">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th colspan="2" class="bg-success text-white" style="font-size: 10px;">
                                                        DADOS PROFISSIONAIS
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td style="font-size: 10px; font-weight: bold;">Departamento</td>
                                                    <td style="font-size: 10px;">{{ retrievedData.department?.name || '-' }}</td>
                                                </tr>
                                                <tr>
                                                    <td style="font-size: 10px; font-weight: bold;">Área de Atuação</td>
                                                    <td style="font-size: 10px;">{{ retrievedData.area?.name || '-' }}</td>
                                                </tr>
                                                <tr>
                                                    <td style="font-size: 10px; font-weight: bold;">Tempo de Serviço</td>
                                                    <td style="font-size: 10px;">{{ getYearsOfWork() }} Anos</td>
                                                </tr>
                                                <tr>
                                                    <td style="font-size: 10px; font-weight: bold;">Status Atual</td>
                                                    <td style="font-size: 10px;">
                                                        <span v-if="retrievedData.status == 1" class="badge bg-success">DISPONÍVEL</span>
                                                        <span v-else class="badge bg-warning">EM TRABALHO</span>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Informações Adicionais -->
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th class="bg-info text-white" colspan="4" style="font-size: 10px;">
                                                INFORMAÇÕES ADMINISTRATIVAS
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td style="font-size: 10px; font-weight: bold;">Data de Cadastro</td>
                                            <td style="font-size: 10px;">{{ formatDate(retrievedData.created_at) }}</td>
                                            <td style="font-size: 10px; font-weight: bold;">Última Atualização</td>
                                            <td style="font-size: 10px;">{{ formatDate(retrievedData.updated_at) }}</td>
                                        </tr>
                                        <tr>
                                            <td style="font-size: 10px; font-weight: bold;">Observações</td>
                                            <td colspan="3" style="font-size: 10px;">
                                                Funcionário devidamente registrado no sistema de gestão do condomínio.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>

                                <!-- Campos para Assinaturas -->
                                <div class="row mt-4">
                                    <div class="col-4">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th class="bg-secondary text-white text-center" style="font-size: 10px;">
                                                        FUNCIONÁRIO
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td style="font-size: 9px; height: 60px; vertical-align: bottom; text-align: center;">
                                                        <div style="border-top: 1px solid #000; width: 150px; margin: 40px auto 0;">
                                                            {{ retrievedData.name }}<br>
                                                            Assinatura
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="col-4">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th class="bg-secondary text-white text-center" style="font-size: 10px;">
                                                        RECURSOS HUMANOS
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td style="font-size: 9px; height: 60px; vertical-align: bottom; text-align: center;">
                                                        <div style="border-top: 1px solid #000; width: 150px; margin: 40px auto 0;">
                                                            Assinatura e Carimbo
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="col-4">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th class="bg-secondary text-white text-center" style="font-size: 10px;">
                                                        ADMINISTRAÇÃO
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td style="font-size: 9px; height: 60px; vertical-align: bottom; text-align: center;">
                                                        <div style="border-top: 1px solid #000; width: 150px; margin: 40px auto 0;">
                                                            Assinatura e Carimbo
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Rodapé -->
                                <div class="row mt-4">
                                    <div class="col-12 text-center">
                                        <p style="font-size: 8px; color: #666;">
                                            Esta ficha de funcionário foi gerada automaticamente pelo sistema de gestão do Condomínio Dana Place.<br>
                                            Documento confidencial para uso exclusivo do departamento de recursos humanos.<br>
                                            <strong>Data de geração:</strong> {{ formatDateTime(new Date()) }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Template do Cartão do Funcionário -->
                            <div id="print-employee-card">
                                <!-- Cartão Frente -->
                                <div style="width: 350px; height: 220px; border: 2px solid #333; margin: 20px auto; padding: 15px; background-color: white; box-sizing: border-box;">
                                    <div style="display: flex; height: 100%; gap: 10px;">
                                        <!-- Lado Esquerdo - Informações -->
                                        <div style="flex: 2; display: flex; flex-direction: column; justify-content: space-between;">
                                            <!-- Logo e Nome da Empresa -->
                                            <div style="border: 1px solid #ddd; padding: 8px; border-radius: 5px; text-align: center;">
                                                <img
                                                    src="/files/img/sys/companylogo.png"
                                                    alt="Logo"
                                                    style="width: 40px; height: 40px;"
                                                />
                                                <h6 style="font-size: 11px; margin: 3px 0 1px 0; color: #333; font-weight: bold;">Dana Place</h6>
                                                <p style="font-size: 7px; margin: 0; color: #666;">CONDOMINIUM</p>
                                            </div>
                                            
                                            <!-- Informações do Funcionário -->
                                            <div style="border: 1px solid #ddd; padding: 8px; border-radius: 5px;">
                                                <h5 style="font-size: 13px; font-weight: bold; margin: 0 0 3px 0; color: #333;">{{ retrievedData.name }}</h5>
                                                <p style="font-size: 9px; margin: 1px 0; color: #666;">{{ retrievedData.department?.name }}</p>
                                                <p style="font-size: 8px; margin: 1px 0; color: #666;">{{ retrievedData.area?.name }}</p>
                                                <p style="font-size: 7px; margin: 3px 0 0 0; color: #333; font-weight: bold;">ID: {{ retrievedData.code }}</p>
                                            </div>
                                        </div>
                                        
                                        <!-- Lado Direito - Foto -->
                                        <div style="flex: 1; display: flex; align-items: center; justify-content: center; border: 1px solid #ddd; border-radius: 5px; padding: 10px;">
                                            <img
                                                :src="retrievedData.image"
                                                alt="Foto"
                                                style="width: 70px; height: 90px; object-fit: cover; border: 2px solid #333; border-radius: 6px;"
                                            />
                                        </div>
                                    </div>
                                </div>

                                <!-- Cartão Verso -->
                                <div style="width: 350px; height: 220px; border: 2px solid #333; margin: 20px auto; background-color: white; box-sizing: border-box; display: flex; flex-direction: column;">
                                    <!-- Cabeçalho -->
                                    <div style="border-bottom: 1px solid #ddd; padding: 10px; text-align: center;">
                                        <h6 style="font-size: 11px; margin: 0; color: #333; font-weight: bold;">INFORMAÇÕES DO FUNCIONÁRIO</h6>
                                    </div>
                                    
                                    <!-- Conteúdo Principal -->
                                    <div style="flex: 1; padding: 15px; display: flex; flex-direction: column; justify-content: space-between;">
                                        <!-- Dados do Funcionário -->
                                        <div style="border: 1px solid #ddd; padding: 10px; border-radius: 5px;">
                                            <table style="width: 100%; font-size: 9px; color: #333;">
                                                <tr>
                                                    <td style="font-weight: bold; padding: 3px 0; color: #666; width: 40%;">Documento:</td>
                                                    <td style="padding: 3px 0;">{{ retrievedData.document }}</td>
                                                </tr>
                                                <tr>
                                                    <td style="font-weight: bold; padding: 3px 0; color: #666;">Admissão:</td>
                                                    <td style="padding: 3px 0;">{{ formatDate(retrievedData.admission_date) }}</td>
                                                </tr>
                                            </table>
                                        </div>

                                        <!-- Informações da Empresa -->
                                        <div style="border: 1px solid #ddd; padding: 10px; text-align: center; border-radius: 5px;">
                                            <div style="font-size: 8px; color: #333;">
                                                <p style="margin: 2px 0; font-weight: bold;">Condomínio Dana Place Lda</p>
                                                <p style="margin: 1px 0;">Tel: +258 84 00 0000</p>
                                                <p style="margin: 1px 0; font-size: 7px;">Em caso de perda, contactar a administração</p>
                                            </div>
                                        </div>

                                        <!-- Assinatura -->
                                        <div style="text-align: center;">
                                            <div style="width: 100px; height: 25px; border: 1px solid #333; margin: 0 auto; font-size: 7px; line-height: 25px; color: #333; background-color: white; border-radius: 3px;">
                                                ASSINATURA
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

<style scoped>
.badge {
    font-size: 0.75em;
}

/* Print specific styles */
@media print {
    .table {
        font-size: 10px !important;
    }
    
    .bg-primary {
        background-color: #0d6efd !important;
        color: white !important;
    }
    
    .bg-success {
        background-color: #198754 !important;
        color: white !important;
    }
    
    .bg-info {
        background-color: #0dcaf0 !important;
        color: white !important;
    }
    
    .bg-warning {
        background-color: #ffc107 !important;
        color: black !important;
    }
    
    .bg-secondary {
        background-color: #6c757d !important;
        color: white !important;
    }
}

/* Card styles */
.card {
    transition: transform 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-2px);
}

.border-success {
    border-color: #198754 !important;
}

.border-info {
    border-color: #0dcaf0 !important;
}

/* HR separator */
hr {
    margin: 2rem 0;
    border: 0;
    border-top: 2px solid #dee2e6;
    opacity: 1;
}
</style>