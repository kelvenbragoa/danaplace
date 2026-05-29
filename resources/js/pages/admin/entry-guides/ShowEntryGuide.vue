<template>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Visualizar Guia de Entrada</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item">
                                <router-link to="/admin/dashboard">Dashboard</router-link>
                            </li>
                            <li class="breadcrumb-item">
                                <router-link to="/admin/entry-guides">Guias de Entrada</router-link>
                            </li>
                            <li class="breadcrumb-item active">Visualizar</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-md-6">
                                <h4 class="card-title mb-0">Guia Nº {{ entryGuide.guide_number }}</h4>
                            </div>
                            <div class="col-md-6">
                                <div class="float-end">
                                    <!-- <button class="btn btn-success me-2" @click="downloadPdf">
                                        <vue-feather type="download" size="16" class="me-1"></vue-feather>
                                        Baixar PDF
                                    </button> -->
                                    <button class="btn btn-info me-2" @click="printGuide" :disabled="loadingPrint">
                                        <div v-if="loadingPrint" class="spinner-border spinner-border-sm me-2"></div>
                                        <vue-feather v-else type="printer" size="16" class="me-1"></vue-feather>
                                        {{ loadingPrint ? 'Imprimindo...' : 'Imprimir' }}
                                    </button>
                                    <router-link v-if="entryGuide.status === 'active'" :to="`/admin/entry-guides/${entryGuide.id}/edit`" class="btn btn-primary">
                                        <vue-feather type="edit" size="16" class="me-1"></vue-feather>
                                        Editar
                                    </router-link>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Dados do Visitante</h5>
                                <table class="table table-borderless">
                                    <tr><td><strong>Nome:</strong></td><td>{{ entryGuide.visitor_name }}</td></tr>
                                    <tr><td><strong>Documento:</strong></td><td>{{ entryGuide.visitor_document }}</td></tr>
                                    <tr><td><strong>Telefone:</strong></td><td>{{ entryGuide.visitor_phone || 'Não informado' }}</td></tr>
                                    <tr><td><strong>Empresa:</strong></td><td>{{ entryGuide.visitor_company || 'Não informado' }}</td></tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h5>Dados do Cliente</h5>
                                <table class="table table-borderless">
                                    <tr><td><strong>Nome:</strong></td><td>{{ entryGuide.host_name }}</td></tr>
                                    <tr><td><strong>Telefone:</strong></td><td>{{ entryGuide.host_phone || 'Não informado' }}</td></tr>
                                    <tr><td><strong>Destino:</strong></td><td>{{ entryGuide.destination?.name }}</td></tr>
                                    <tr><td><strong>Local:</strong></td><td>{{ entryGuide.specific_location || 'Não informado' }}</td></tr>
                                </table>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-8">
                                <h5>Informações da Visita</h5>
                                <table class="table table-borderless">
                                    <tr><td><strong>Status:</strong></td><td><span :class="`badge bg-${getStatusColor(entryGuide.status)}`">{{ getStatusText(entryGuide.status) }}</span></td></tr>
                                    <tr><td><strong>Válida de:</strong></td><td>{{ formatDate(entryGuide.valid_from) }}</td></tr>
                                    <tr><td><strong>Válida até:</strong></td><td>{{ formatDate(entryGuide.valid_until) }}</td></tr>
                                    <tr><td><strong>Propósito:</strong></td><td>{{ entryGuide.purpose || 'Não informado' }}</td></tr>
                                </table>
                            </div>
                            <div class="col-md-4">
                                <h5>QR Code de Verificação</h5>
                                <div class="text-center">
                                    <img 
                                        v-if="entryGuide.qr_code"
                                        :src="entryGuide.qr_code" 
                                        alt="QR Code" 
                                        style="width: 150px; height: 150px;"
                                        class="border rounded"
                                    />
                                    <img
                                        v-else
                                        :src="generateQRCodeUrl()"
                                        alt="QR Code" 
                                        style="width: 150px; height: 150px;"
                                        class="border rounded"
                                    />
                                    <p class="mt-2 small text-muted">
                                        Código para verificação na portaria
                                    </p>
                                    <p class="small text-muted">
                                        ID: {{ entryGuide.id }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Template de Impressão (oculto) -->
        <div style="display: none;">
            <div id="print-entry-guide" style="font-family: Arial, sans-serif; font-size: 9px; line-height: 1.3;">
                <!-- Header Compacto -->
                <div style="text-align: center; margin-bottom: 10px;">
                    <h3 style="margin: 0; font-size: 16px; font-weight: bold;">Areia Branca Condominium</h3>
                    <h4 style="margin: 3px 0; font-size: 14px;">Guia de Entrada Nº {{ entryGuide.guide_number }}</h4>
                </div>
                
                <!-- Header com Logo e QR Code -->
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
                    <div style="width: 30%;">
                        <img src="/files/img/sys/companylogo.png" alt="Logo" style="width: 80px; height: 80px;" />
                        <div style="font-size: 8px; margin-top: 4px;">
                            Condominio Areia Branca Lda<br/>
                            Cimento a Ponta de Ouro<br/>
                            Tel: +258 84 000 0000<br/>
                            info@areiabranca.com
                        </div>
                    </div>
                    <div style="width: 30%; text-align: center;">
                        <div style="font-size: 8px; margin-bottom: 4px;">
                            <strong>Válida:</strong> {{ formatDate(entryGuide.valid_from) }}<br/>
                            <strong>até:</strong> {{ formatDate(entryGuide.valid_until) }}<br/>
                            <strong>Status:</strong> {{ getStatusText(entryGuide.status) }}
                        </div>
                    </div>
                    <div style="width: 30%; text-align: right;">
                        <img 
                            v-if="entryGuide.qr_code"
                            :src="entryGuide.qr_code" 
                            alt="QR Code" 
                            style="width: 80px; height: 80px;"
                        />
                        <img
                            v-else
                            :src="generateQRCodeUrl()"
                            alt="QR Code" 
                            style="width: 80px; height: 80px;"
                        />
                        <div style="font-size: 7px; margin-top: 3px;">ID: {{ entryGuide.id }}</div>
                    </div>
                </div>
                <!-- Informações Principais - Layout Compacto -->
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px; font-size: 8px;">
                    <tr>
                        <td style="border: 1px solid #000; padding: 4px; background-color: #f0f0f0; font-weight: bold; width: 15%;">VISITANTE</td>
                        <td style="border: 1px solid #000; padding: 4px; width: 35%;">
                            <strong>{{ entryGuide.visitor_name || '-' }}</strong><br/>
                            Doc: {{ entryGuide.visitor_document || '-' }}<br/>
                            Tel: {{ entryGuide.visitor_phone || '-' }}<br/>
                            Empresa: {{ entryGuide.visitor_company || '-' }}
                        </td>
                        <td style="border: 1px solid #000; padding: 4px; background-color: #f0f0f0; font-weight: bold; width: 15%;">ANFITRIÃO</td>
                        <td style="border: 1px solid #000; padding: 4px; width: 35%;">
                            <strong>{{ entryGuide.host_name || '-' }}</strong><br/>
                            Tel: {{ entryGuide.host_phone || '-' }}<br/>
                            Destino: {{ entryGuide.destination?.name || '-' }}<br/>
                            Local: {{ entryGuide.specific_location || '-' }}
                        </td>
                    </tr>
                </table>

                <!-- Detalhes da Visita Compacto -->
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px; font-size: 8px;">
                    <tr>
                        <td style="border: 1px solid #000; padding: 4px; background-color: #f0f0f0; font-weight: bold; width: 15%;">PROPÓSITO</td>
                        <td style="border: 1px solid #000; padding: 4px; width: 50%;">{{ entryGuide.purpose || 'Não informado' }}</td>
                        <td style="border: 1px solid #000; padding: 4px; background-color: #f0f0f0; font-weight: bold; width: 15%;">CRIADO POR</td>
                        <td style="border: 1px solid #000; padding: 4px; width: 20%;">{{ entryGuide.creator?.name || 'Sistema' }}</td>
                    </tr>
                </table>

                <!-- Instruções de Segurança Compactas -->
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px; font-size: 7px;">
                    <tr>
                        <td style="border: 1px solid #000; padding: 4px; background-color: #ffeb3b; font-weight: bold; text-align: center;">
                            INSTRUÇÕES DE SEGURANÇA E ACESSO
                        </td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #000; padding: 5px; line-height: 1.2;">
                            • Esta guia é pessoal e intransferível • Apresentar documento na portaria • Respeitar normas do condomínio<br/>
                            • Devolver guia na saída • Válida apenas no período indicado • Emergência: +258 87 000 0000
                        </td>
                    </tr>
                </table>

                <!-- Campos de Controle Compactos -->
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px; font-size: 7px;">
                    <tr>
                        <td style="border: 1px solid #000; padding: 4px; background-color: #e0e0e0; font-weight: bold; text-align: center; width: 33.33%;">
                            ENTRADA
                        </td>
                        <td style="border: 1px solid #000; padding: 4px; background-color: #e0e0e0; font-weight: bold; text-align: center; width: 33.33%;">
                            SAÍDA
                        </td>
                        <td style="border: 1px solid #000; padding: 4px; background-color: #e0e0e0; font-weight: bold; text-align: center; width: 33.34%;">
                            OBSERVAÇÕES
                        </td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #000; padding: 4px; height: 40px; vertical-align: top;">
                            Data/Hora: _________<br/>
                            Segurança: _________<br/>
                            Assinatura: ________
                        </td>
                        <td style="border: 1px solid #000; padding: 4px; height: 40px; vertical-align: top;">
                            Data/Hora: _________<br/>
                            Segurança: _________<br/>
                            Assinatura: ________
                        </td>
                        <td style="border: 1px solid #000; padding: 4px; height: 40px; vertical-align: top;">
                            ___________________<br/>
                            ___________________<br/>
                            ___________________
                        </td>
                    </tr>
                </table>

                <!-- Rodapé Compacto -->
                <div style="text-align: center; font-size: 6px; color: #666; margin-top: 6px;">
                    Guia gerada automaticamente - Sistema Condomínio Areia Branca | 
                    Geração: {{ formatDate(new Date()) }} | 
                    Válida: {{ formatDate(entryGuide.valid_from) }} até {{ formatDate(entryGuide.valid_until) }}
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import axios from 'axios'
import { useToastr } from '../../../toastr'
import moment from 'moment'
import VueFeather from 'vue-feather'
import { usePaperizer } from "paperizer"

const route = useRoute()
const toastr = useToastr()

const entryGuide = ref({})
const loadingPrint = ref(false)

// Paperizer configuration
let { paperize } = usePaperizer("print-entry-guide", {
    styles: [
        "https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css",
    ],
    windowTitle: `Guia de Entrada - ${entryGuide.value.guide_number || ''}`,
    pageSize: 'A4',
    orientation: 'portrait',
    margins: {
        top: '1cm',
        right: '1cm',
        bottom: '1cm',
        left: '1cm'
    }
});

const loadEntryGuide = async () => {
    try {
        const response = await axios.get(`/entry-guides/${route.params.id}`)
        entryGuide.value = response.data.entryGuide
    } catch (error) {
        toastr.error('Erro ao carregar guia de entrada')
        console.error(error)
    }
}

const downloadPdf = () => {
    window.open(`/entry-guides/${route.params.id}/pdf`, '_blank')
}

const printGuide = () => {
    loadingPrint.value = true;
    paperize();
    loadingPrint.value = false;
}

const generateQRCodeUrl = () => {
    if (!entryGuide.value.id) return '';
    
    // Create QR code data
    const qrData = JSON.stringify({
        guide_id: entryGuide.value.id,
        guide_number: entryGuide.value.guide_number,
        guest_name: entryGuide.value.visitor_name,
        destination: entryGuide.value.destination?.name,
        valid_until: entryGuide.value.valid_until
    });
    
    // Use QR Server API to generate QR code
    const encodedData = encodeURIComponent(qrData);
    return `https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=${encodedData}`;
}

const formatDate = (date) => {
    return moment(date).format('DD/MM/YYYY HH:mm')
}

const getStatusColor = (status) => {
    const colors = {
        active: 'success',
        used: 'info', 
        expired: 'warning',
        cancelled: 'danger'
    }
    return colors[status] || 'secondary'
}

const getStatusText = (status) => {
    const texts = {
        active: 'Ativa',
        used: 'Utilizada',
        expired: 'Expirada',
        cancelled: 'Cancelada'
    }
    return texts[status] || status
}

onMounted(() => {
    loadEntryGuide()
})
</script>

<style scoped>
.badge {
    font-size: 0.75em;
}

.table-borderless td {
    border: none;
    padding: 0.5rem 0.75rem;
}

.table-borderless tr:first-child td {
    padding-top: 0;
}

/* Print specific styles */
@media print {
    #print-entry-guide {
        width: 100% !important;
        max-width: 21cm !important;
        margin: 0 !important;
        padding: 0.6cm !important;
        page-break-inside: avoid !important;
        font-size: 9px !important;
        line-height: 1.3 !important;
    }
    
    #print-entry-guide * {
        font-size: inherit !important;
        line-height: inherit !important;
    }
    
    #print-entry-guide table {
        width: 100% !important;
        border-collapse: collapse !important;
        margin-bottom: 6px !important;
    }
    
    #print-entry-guide td, 
    #print-entry-guide th {
        border: 1px solid #000 !important;
        padding: 3px !important;
        vertical-align: top !important;
    }
    
    #print-entry-guide img {
        max-width: 100% !important;
        height: auto !important;
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
    
    /* Remove quebras de página desnecessárias */
    * {
        page-break-inside: avoid !important;
    }
    
    /* Margem mínima para a página */
    @page {
        margin: 1cm !important;
        size: A4 !important;
    }
}
</style>