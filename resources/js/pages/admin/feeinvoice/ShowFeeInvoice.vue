<script setup>
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import axios from 'axios';
import VueFeather from 'vue-feather';
import {useToastr} from '../../../toastr';
import { usePaperizer } from "paperizer";

const toastr = useToastr();

// Reactive data
const router = useRouter();
const route = useRoute();
const invoice = ref(null);
const loadingDiv = ref(true);
const loadingAction = ref(false);
const loadingPrint = ref(false);

// Paperizer configuration
let { paperize } = usePaperizer("print-fee-invoice", {
    styles: [
        "https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css",
    ],
    windowTitle: `Fatura de Taxas`,
});

// Utility functions
const formatCurrency = (amount) => {
    return new Intl.NumberFormat('pt-MZ', {
        style: 'currency',
        currency: 'MZN',
        minimumFractionDigits: 2
    }).format(amount);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
};

const formatDateTime = (datetime) => {
    return new Date(datetime).toLocaleString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const getStatusBadge = (status) => {
    const badges = {
        'draft': { class: 'badge-secondary', text: 'Rascunho' },
        'issued': { class: 'badge-primary', text: 'Emitida' },
        'partially_paid': { class: 'badge-warning', text: 'Parcialmente Paga' },
        'paid': { class: 'badge-success', text: 'Paga' },
        'overdue': { class: 'badge-danger', text: 'Vencida' },
        'cancelled': { class: 'badge-dark', text: 'Cancelada' }
    };
    return badges[status] || { class: 'badge-secondary', text: 'Desconhecido' };
};

const getProgressPercentage = () => {
    if (!invoice.value) return 0;
    
    const totalAmount = getSafeAmount(invoice.value.total_amount);
    const paidAmount = getSafeAmount(invoice.value.paid_amount);
    
    if (totalAmount === 0) return 0;
    
    const percentage = (paidAmount / totalAmount) * 100;
    return Math.round(Math.min(100, Math.max(0, percentage)));
};

// Calcular valor restante
const getRemainingAmount = () => {
    if (!invoice.value) return 0;
    
    const totalAmount = parseFloat(invoice.value.total_amount) || 0;
    const paidAmount = parseFloat(invoice.value.paid_amount) || 0;
    
    return Math.max(0, totalAmount - paidAmount);
};

// Garantir que valores monetários sejam sempre números
const getSafeAmount = (amount) => {
    const num = parseFloat(amount);
    return isNaN(num) ? 0 : num;
};

// Methods
const getData = async () => {
    try {
        loadingDiv.value = true;
        const response = await axios.get(`/fee-invoices/${route.params.id}`);
        invoice.value = response.data.invoice;
    } catch (error) {
        toastr.error('Erro ao carregar fatura');
        console.error(error);
        router.push('/admin/fee-invoices');
    } finally {
        loadingDiv.value = false;
    }
};

const toggleItemPayment = async (item) => {
    try {
        loadingAction.value = true;
        
        const response = await axios.post(
            `/fee-invoices/${invoice.value.id}/items/${item.id}/toggle-payment`, 
            {
                is_paid: !item.is_paid,
                payment_details: {
                    method: 'manual',
                    marked_at: new Date().toISOString(),
                    notes: `Marcado como ${!item.is_paid ? 'pago' : 'não pago'} manualmente`
                }
            }
        );

        // Atualizar o item na lista preservando o equipamento original
        const itemIndex = invoice.value.items.findIndex(i => i.id === item.id);
        if (itemIndex !== -1) {
            // Preservar o equipamento original caso o retorno não o inclua
            const originalEquipment = invoice.value.items[itemIndex].equipment;
            const updatedItem = response.data.item;
            
            // Se o item retornado não tem equipamento, usar o original
            if (!updatedItem.equipment && originalEquipment) {
                updatedItem.equipment = originalEquipment;
            }
            
            invoice.value.items[itemIndex] = updatedItem;
        }

        // Atualizar os totais da fatura
        invoice.value.paid_amount = response.data.invoice.paid_amount;
        invoice.value.status = response.data.invoice.status;

        toastr.success(response.data.message);
    } catch (error) {
        toastr.error('Erro ao atualizar pagamento');
        console.error(error);
    } finally {
        loadingAction.value = false;
    }
};

const approveInvoice = async () => {
    if (!confirm('Tem certeza que deseja aprovar esta fatura?')) return;

    try {
        loadingAction.value = true;
        const response = await axios.post(`/fee-invoices/${invoice.value.id}/approve`);
        invoice.value = response.data.invoice;
        toastr.success('Fatura aprovada com sucesso!');
    } catch (error) {
        const message = error.response?.data?.message || 'Erro ao aprovar fatura';
        toastr.error(message);
    } finally {
        loadingAction.value = false;
    }
};

const downloadReport = () => {
    const url = `/fee-invoices/${invoice.value.id}/report`;
    window.open(url, '_blank');
};

const printInvoice = () => {
    loadingPrint.value = true;
    paperize();
    loadingPrint.value = false;
};

// Download relatório por Cliente
const downloadDestinationReport = (destinationId) => {
    const url = `/fee-invoices/${invoice.value.id}/destination-report/${destinationId}`;
    window.open(url, '_blank');
};

const printDestinationInvoice = (destinationId, destinationName) => {
    loadingPrint.value = true;
    const { paperize } = usePaperizer(`print-destination-invoice-${destinationId}`, {
        styles: [
            "https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css",
        ],
        windowTitle: `Fatura ${destinationName}`,
    });
    paperize();
    loadingPrint.value = false;
};

// Group items by equipment
const getItemsByEquipment = () => {
    if (!invoice.value?.items) return [];
    
    const grouped = {};
    invoice.value.items.forEach(item => {
        // Verificar se item.equipment existe
        if (!item.equipment) {
            console.warn('Item sem equipamento encontrado:', item);
            return; // Pular este item se não tem equipamento
        }
        
        const equipmentId = item.equipment.id;
        if (!grouped[equipmentId]) {
            grouped[equipmentId] = {
                equipment: item.equipment,
                items: []
            };
        }
        grouped[equipmentId].items.push(item);
    });
    
    return Object.values(grouped);
};

// Group items by destination
const getItemsByDestination = () => {
    if (!invoice.value?.items) return [];
    
    const grouped = {};
    invoice.value.items.forEach(item => {
        // Verificar se item.equipment existe
        if (!item.equipment) {
            console.warn('Item sem equipamento encontrado:', item);
            return; // Pular este item se não tem equipamento
        }
        
        const destinationId = item.equipment.destination?.id || 'no-destination';
        const destinationName = item.equipment.destination?.name || 'Sem Cliente';
        
        if (!grouped[destinationId]) {
            grouped[destinationId] = {
                destination: {
                    id: destinationId,
                    name: destinationName
                },
                equipments: {},
                totalAmount: 0
            };
        }
        
        // Group by equipment within destination
        const equipmentId = item.equipment.id;
        if (!grouped[destinationId].equipments[equipmentId]) {
            grouped[destinationId].equipments[equipmentId] = {
                equipment: item.equipment,
                items: [],
                totalAmount: 0
            };
        }
        
        grouped[destinationId].equipments[equipmentId].items.push(item);
        grouped[destinationId].equipments[equipmentId].totalAmount += getSafeAmount(item.amount);
        grouped[destinationId].totalAmount += getSafeAmount(item.amount);
    });
    
    // Convert equipments object to array
    Object.values(grouped).forEach(destination => {
        destination.equipments = Object.values(destination.equipments);
    });
    
    return Object.values(grouped);
};

// Lifecycle
onMounted(() => {
    getData();
});
</script>

<template>
    <div v-if="!loadingDiv && invoice">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1">
                    <vue-feather type="file-text" class="align-middle me-2"></vue-feather>
                    Fatura {{ invoice.invoice_number }}
                </h1>
                <p class="text-muted mb-0">{{ invoice.period_description }}</p>
            </div>
            <div class="d-flex gap-2">
                <!-- <button 
                    class="btn btn-outline-primary"
                    @click="downloadReport"
                >
                    <vue-feather type="download" size="16" class="me-1"></vue-feather>
                    Download PDF
                </button> -->
                <router-link 
                    :to="{ name: 'admin.fee-invoices.edit', params: { id: invoice.id } }"
                    class="btn btn-outline-secondary"
                >
                    <vue-feather type="edit" size="16" class="me-1"></vue-feather>
                    Editar
                </router-link>
                <router-link 
                    to="/admin/fee-invoices" 
                    class="btn btn-secondary"
                >
                    <vue-feather type="arrow-left" size="16" class="me-1"></vue-feather>
                    Voltar
                </router-link>
            </div>
        </div>

        <div class="row">
            <!-- Informações da Fatura -->
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <vue-feather type="info" size="20" class="me-2"></vue-feather>
                            Informações da Fatura
                        </h5>
                    </div>
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-sm-5">Status:</dt>
                            <dd class="col-sm-7">
                                <span :class="`badge ${getStatusBadge(invoice.status).class}`">
                                    {{ getStatusBadge(invoice.status).text }}
                                </span>
                            </dd>

                            <dt class="col-sm-5">Período:</dt>
                            <dd class="col-sm-7">{{ invoice.period_description }}</dd>

                            <dt class="col-sm-5">Emissão:</dt>
                            <dd class="col-sm-7">{{ formatDate(invoice.issue_date) }}</dd>

                            <dt class="col-sm-5">Vencimento:</dt>
                            <dd class="col-sm-7">
                                {{ formatDate(invoice.due_date) }}
                                <small v-if="invoice.is_overdue" class="text-danger d-block">
                                    <vue-feather type="alert-triangle" size="12"></vue-feather>
                                    Vencida
                                </small>
                            </dd>

                            <dt class="col-sm-5">Criado por:</dt>
                            <dd class="col-sm-7">{{ invoice.creator?.name || 'N/A' }}</dd>

                            <dt class="col-sm-5">Criado em:</dt>
                            <dd class="col-sm-7">{{ formatDateTime(invoice.created_at) }}</dd>

                            <dt v-if="invoice.approved_by" class="col-sm-5">Aprovado por:</dt>
                            <dd v-if="invoice.approved_by" class="col-sm-7">{{ invoice.approver?.name || 'N/A' }}</dd>

                            <dt v-if="invoice.approved_at" class="col-sm-5">Aprovado em:</dt>
                            <dd v-if="invoice.approved_at" class="col-sm-7">{{ formatDateTime(invoice.approved_at) }}</dd>
                        </dl>

                        <div v-if="invoice.notes" class="mt-3">
                            <h6>Observações:</h6>
                            <p class="text-muted">{{ invoice.notes }}</p>
                        </div>

                        <!-- Ações -->
                        <div v-if="invoice.status === 'draft'" class="mt-3 pt-3 border-top">
                            <button 
                                class="btn btn-success w-100"
                                @click="approveInvoice"
                                :disabled="loadingAction"
                            >
                                <div v-if="loadingAction" class="spinner-border spinner-border-sm me-2"></div>
                                <vue-feather v-else type="check" size="16" class="me-1"></vue-feather>
                                {{ loadingAction ? 'Aprovando...' : 'Aprovar Fatura' }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Resumo Financeiro -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <vue-feather type="dollar-sign" size="20" class="me-2"></vue-feather>
                            Resumo Financeiro
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Valor Total:</span>
                            <span class="fw-bold">{{ formatCurrency(getSafeAmount(invoice.total_amount)) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Valor Pago:</span>
                            <span class="fw-bold text-success">{{ formatCurrency(getSafeAmount(invoice.paid_amount)) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span>Pendente:</span>
                            <span class="fw-bold text-warning">{{ formatCurrency(getRemainingAmount()) }}</span>
                        </div>

                        <!-- Progress Bar -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="small">Progresso do Pagamento</span>
                                <span class="small">{{ getProgressPercentage() }}%</span>
                            </div>
                            <div class="progress">
                                <div 
                                    class="progress-bar"
                                    :class="invoice.status === 'paid' ? 'bg-success' : 'bg-info'"
                                    :style="`width: ${getProgressPercentage()}%`"
                                ></div>
                            </div>
                        </div>

                        <!-- Estatísticas de Itens -->
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="border rounded p-2">
                                    <div class="h6 mb-1 text-success">{{ invoice.items.filter(i => i.is_paid).length }}</div>
                                    <div class="small text-muted">Pagos</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-2">
                                    <div class="h6 mb-1 text-warning">{{ invoice.items.filter(i => !i.is_paid).length }}</div>
                                    <div class="small text-muted">Pendentes</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Faturas por Cliente -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <vue-feather type="map-pin" size="20" class="me-2"></vue-feather>
                            Faturas por Cliente
                        </h5>
                    </div>
                    <div class="card-body">
                        <div v-for="(destination, index) in getItemsByDestination()" :key="index" class="border rounded p-3 mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <h6 class="mb-1">
                                        <vue-feather type="map-pin" size="16" class="me-1"></vue-feather>
                                        {{ destination.destination.name }}
                                    </h6>
                                    <p class="mb-0 text-muted small">
                                        {{ destination.equipments.length }} equipamento(s) | 
                                        {{ destination.equipments.reduce((total, eq) => total + eq.items.length, 0) }} item(s)
                                    </p>
                                </div>
                                <div class="text-end">
                                    <div class="h6 mb-1 text-primary">
                                        {{ formatCurrency(destination.totalAmount) }}
                                    </div>
                                    <div class="btn-group btn-group-sm">
                                        <!-- <button 
                                            class="btn btn-outline-primary"
                                            @click="downloadDestinationReport(destination.destination.id)"
                                            title="Baixar PDF deste Cliente"
                                        >
                                            <vue-feather type="download" size="14" class="me-1"></vue-feather>
                                            PDF
                                        </button> -->
                                        <button 
                                            class="btn btn-outline-primary"
                                            @click="printDestinationInvoice(destination.destination.id, destination.destination.name)"
                                            title="Imprimir fatura deste Cliente"
                                            :disabled="loadingPrint"
                                        >
                                            <vue-feather type="printer" size="14" class="me-1"></vue-feather>
                                            Imprimir
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Equipamentos do Cliente -->
                            <div class="ms-3">
                                <div v-for="equipment in destination.equipments" :key="equipment.equipment.id" class="border-start border-2 border-info ps-3 mb-2">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <strong class="small">{{ equipment.equipment.name }}</strong>
                                            <div class="text-muted small">{{ equipment.items.length }} taxa(s)</div>
                                        </div>
                                        <div class="text-end">
                                            <strong class="small">{{ formatCurrency(equipment.totalAmount) }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Itens da Fatura -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <vue-feather type="list" size="20" class="me-2"></vue-feather>
                            Itens da Fatura ({{ invoice.items.length }})
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div v-for="(group, index) in getItemsByEquipment()" :key="index" class="border-bottom">
                            <!-- Equipment Header -->
                            <div class="bg-light p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">
                                            <vue-feather type="truck" size="16" class="me-1"></vue-feather>
                                            {{ group.equipment.name }}
                                        </h6>
                                        <p class="mb-0 text-muted small">
                                            <vue-feather type="map-pin" size="12" class="me-1"></vue-feather>
                                            {{ group.equipment.destination?.name || 'N/A' }} |
                                            {{ group.equipment.type_equipment?.name || 'N/A' }}
                                        </p>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-primary">
                                            {{ group.items.length }} taxa{{ group.items.length !== 1 ? 's' : '' }}
                                        </span>
                                        <div class="fw-bold text-success mt-1">
                                            {{ formatCurrency(group.items.reduce((sum, item) => sum + getSafeAmount(item.amount), 0)) }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Fee Items -->
                            <div class="p-0">
                                <div 
                                    v-for="item in group.items" 
                                    :key="item.id"
                                    class="d-flex justify-content-between align-items-center p-3 border-bottom border-light"
                                    :class="{ 'bg-success-subtle': item.is_paid }"
                                >
                                    <div class="d-flex align-items-center flex-grow-1">
                                        <div class="form-check me-3">
                                            <input 
                                                :id="`item-${item.id}`"
                                                type="checkbox" 
                                                class="form-check-input"
                                                :checked="item.is_paid"
                                                @change="toggleItemPayment(item)"
                                                :disabled="loadingAction"
                                            />
                                        </div>
                                        
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h6 class="mb-1" :class="{ 'text-decoration-line-through text-muted': item.is_paid }">
                                                        {{ item.fee.name }}
                                                    </h6>
                                                    <div class="d-flex gap-3">
                                                        <span 
                                                            :class="`badge badge-${item.is_paid ? 'success' : 'warning'}`"
                                                        >
                                                            {{ item.is_paid ? 'Pago' : 'Pendente' }}
                                                        </span>
                                                        <span v-if="item.paid_at" class="small text-muted">
                                                            <vue-feather type="clock" size="12" class="me-1"></vue-feather>
                                                            Pago em {{ formatDateTime(item.paid_at) }}
                                                        </span>
                                                        <span v-if="item.marked_by_user" class="small text-muted">
                                                            <vue-feather type="user" size="12" class="me-1"></vue-feather>
                                                            por {{ item.marked_by_user.name }}
                                                        </span>
                                                    </div>
                                                    <div v-if="item.notes" class="small text-muted mt-1">
                                                        <vue-feather type="message-circle" size="12" class="me-1"></vue-feather>
                                                        {{ item.notes }}
                                                    </div>
                                                </div>
                                                <div class="text-end">
                                                    <div class="h6 mb-0" :class="{ 'text-success': item.is_paid }">
                                                        {{ formatCurrency(getSafeAmount(item.amount)) }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body m-sm-3 m-md-5 border">
                        <div class="mb-4">
                            <button @click="printInvoice" class="btn btn-pill btn-primary mt-3" :disabled="loadingPrint">
                                <div v-if="loadingPrint" class="spinner-border spinner-border-sm me-2"></div>
                                <vue-feather v-else type="printer"></vue-feather> 
                                {{ loadingPrint ? 'Imprimindo...' : 'Imprimir Fatura' }}
                            </button> 
                        </div>
                        
                        <div id="print-fee-invoice">
                            <!-- Header da Fatura -->
                            <div class="row text-center">
                                <div class="col text-center" style="text-align: center">
                                    <h2>Dana Place Fee Invoice</h2>
                                </div>
                            </div>
                            
                            <!-- Logo e Informações da Empresa -->
                            <div class="row">
                                <div class="col text-left" style="text-align: left">
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
                                <div class="col text-right" style="text-align: right">
                                    <!-- Espaço para logo adicional se necessário -->
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
                            
                            <!-- Cabeçalho da Fatura -->
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th style="font-size: 10px;">
                                            DATA:
                                            {{ formatDate(invoice.issue_date) }}
                                        </th>
                                        <th style="font-size: 10px;">
                                            PERÍODO:
                                            {{ invoice.period_description }}
                                        </th>
                                        <th style="font-size: 10px;">
                                            VENCIMENTO:
                                            {{ formatDate(invoice.due_date) }}
                                        </th>
                                        <th style="font-size: 10px;">
                                            FATURA Nº: {{ invoice.invoice_number }}
                                        </th>
                                    </tr>
                                </thead>
                            </table>

                            <!-- Resumo Financeiro -->
                            <div class="row">
                                <div class="col-6">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th colspan="3" class="bg-secondary" style="font-size: 10px;">
                                                    RESUMO FINANCEIRO DA FATURA
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td style="font-size: 10px;">Status</td>
                                                <td style="font-size: 10px;">
                                                    {{ getStatusBadge(invoice.status).text }}
                                                </td>
                                                <td style="font-size: 10px;">
                                                    -
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="font-size: 10px;">Valor Total</td>
                                                <td style="font-size: 10px;">
                                                    1
                                                </td>
                                                <td style="font-size: 10px;">
                                                    {{ formatCurrency(getSafeAmount(invoice.total_amount)) }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="font-size: 10px;">Valor Pago</td>
                                                <td style="font-size: 10px;">
                                                    1
                                                </td>
                                                <td style="font-size: 10px;">
                                                    {{ formatCurrency(getSafeAmount(invoice.paid_amount)) }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="font-size: 10px;">Valor Pendente</td>
                                                <td style="font-size: 10px;">
                                                    1
                                                </td>
                                                <td style="font-size: 10px;">
                                                    {{ formatCurrency(getRemainingAmount()) }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="font-size: 10px;">Progresso</td>
                                                <td style="font-size: 10px;">
                                                    {{ getProgressPercentage() }}%
                                                </td>
                                                <td style="font-size: 10px;">
                                                    -
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="col-6">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th colspan="3" class="bg-secondary" style="font-size: 10px;">
                                                    ESTATÍSTICAS DE PAGAMENTO
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td style="font-size: 10px;">Total de Itens</td>
                                                <td style="font-size: 10px;">
                                                    {{ invoice.items.length }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="font-size: 10px;">Itens Pagos</td>
                                                <td style="font-size: 10px;">
                                                    {{ invoice.items.filter(i => i.is_paid).length }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="font-size: 10px;">Itens Pendentes</td>
                                                <td style="font-size: 10px;">
                                                    {{ invoice.items.filter(i => !i.is_paid).length }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="font-size: 10px;">Clientes</td>
                                                <td style="font-size: 10px;">
                                                    {{ getItemsByDestination().length }}
                                                </td>
                                            </tr>
                                            <tr style="border-width: 0px;">
                                                <td colspan="2" style="border-width: 0px;">
                                                    <p style="font-size: 10px;">Fatura gerada automaticamente pelo sistema</p>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Detalhes por Cliente -->
                            <div v-for="(destination, index) in getItemsByDestination()" :key="index" class="mb-4">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th class="bg-primary text-white" colspan="6" style="font-size: 10px;">
                                                CLIENTE: {{ destination.destination.name.toUpperCase() }}
                                            </th>
                                        </tr>
                                        <tr>
                                            <th class="bg-secondary" style="font-size: 10px;">Equipamento</th>
                                            <th class="bg-secondary" style="font-size: 10px;">Taxa</th>
                                            <th class="bg-secondary" style="font-size: 10px;">Valor</th>
                                            <th class="bg-secondary" style="font-size: 10px;">Status</th>
                                            <th class="bg-secondary" style="font-size: 10px;">Pago em</th>
                                            <th class="bg-secondary" style="font-size: 10px;">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="equipment in destination.equipments" :key="equipment.equipment.id">
                                            <td style="font-size: 10px; vertical-align: top; font-weight: bold;">
                                                {{ equipment.equipment.name }}
                                                <br>
                                                <small class="text-muted">{{ equipment.equipment.type_equipment?.name || 'N/A' }}</small>
                                            </td>
                                            <td style="font-size: 10px; vertical-align: top;">
                                                <div v-for="item in equipment.items" :key="item.id" style="margin-bottom: 5px;">
                                                    {{ item.fee.name }}
                                                </div>
                                            </td>
                                            <td style="font-size: 10px; vertical-align: top;">
                                                <div v-for="item in equipment.items" :key="item.id" style="margin-bottom: 5px;">
                                                    {{ formatCurrency(getSafeAmount(item.amount)) }}
                                                </div>
                                            </td>
                                            <td style="font-size: 10px; vertical-align: top;">
                                                <div v-for="item in equipment.items" :key="item.id" style="margin-bottom: 5px;">
                                                    <span :class="item.is_paid ? 'text-success' : 'text-warning'">
                                                        {{ item.is_paid ? 'PAGO' : 'PENDENTE' }}
                                                    </span>
                                                </div>
                                            </td>
                                            <td style="font-size: 10px; vertical-align: top;">
                                                <div v-for="item in equipment.items" :key="item.id" style="margin-bottom: 5px;">
                                                    {{ item.paid_at ? formatDate(item.paid_at) : '-' }}
                                                </div>
                                            </td>
                                            <td style="font-size: 10px; vertical-align: top; font-weight: bold;">
                                                {{ formatCurrency(equipment.totalAmount) }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="5" class="text-end" style="font-size: 10px; font-weight: bold;">
                                                SUBTOTAL {{ destination.destination.name.toUpperCase() }}:
                                            </td>
                                            <td style="font-size: 10px; font-weight: bold; background-color: #f8f9fa;">
                                                {{ formatCurrency(destination.totalAmount) }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Total Geral -->
                            <table class="table table-bordered">
                                <tbody>
                                    <tr class="bg-success text-white">
                                        <td colspan="5" class="text-end" style="font-size: 12px; font-weight: bold;">
                                            TOTAL GERAL DA FATURA:
                                        </td>
                                        <td style="font-size: 12px; font-weight: bold;">
                                            {{ formatCurrency(getSafeAmount(invoice.total_amount)) }}
                                        </td>
                                    </tr>
                                    <tr v-if="getRemainingAmount() > 0" class="bg-warning">
                                        <td colspan="5" class="text-end" style="font-size: 12px; font-weight: bold;">
                                            VALOR A PAGAR:
                                        </td>
                                        <td style="font-size: 12px; font-weight: bold;">
                                            {{ formatCurrency(getRemainingAmount()) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <!-- Observações -->
                            <div v-if="invoice.notes" class="mt-4">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th class="bg-info text-white" style="font-size: 10px;">
                                                OBSERVAÇÕES
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td style="font-size: 10px;">
                                                {{ invoice.notes }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Rodapé -->
                            <div class="row mt-4">
                                <div class="col-12 text-center">
                                    <p style="font-size: 8px; color: #666;">
                                        Esta fatura foi gerada automaticamente pelo sistema de gestão do Condomínio Dana Place.<br>
                                        Para dúvidas ou esclarecimentos, entre em contato através dos canais oficiais.<br>
                                        <strong>Data de geração:</strong> {{ formatDateTime(new Date()) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Templates de Impressão por Cliente (ocultos) -->
                <div v-for="(destination, index) in getItemsByDestination()" :key="index" style="display: none;">
                    <div :id="`print-destination-invoice-${destination.destination.id}`">
                        <!-- Header da Fatura do Cliente -->
                        <div class="row text-center">
                            <div class="col text-center" style="text-align: center">
                                <h2>Dana Place Fee Invoice</h2>
                                <h4>{{ destination.destination.name }}</h4>
                            </div>
                        </div>
                        
                        <!-- Logo e Informações da Empresa -->
                        <div class="row">
                            <div class="col text-left" style="text-align: left">
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
                            <div class="col text-right" style="text-align: right">
                                <!-- Espaço para logo adicional se necessário -->
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
                        
                        <!-- Cabeçalho da Fatura -->
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th style="font-size: 10px;">
                                        DATA:
                                        {{ formatDate(invoice.issue_date) }}
                                    </th>
                                    <th style="font-size: 10px;">
                                        PERÍODO:
                                        {{ invoice.period_description }}
                                    </th>
                                    <th style="font-size: 10px;">
                                        VENCIMENTO:
                                        {{ formatDate(invoice.due_date) }}
                                    </th>
                                    <th style="font-size: 10px;">
                                        FATURA Nº: {{ invoice.invoice_number }}
                                    </th>
                                </tr>
                            </thead>
                        </table>

                        <!-- Resumo Financeiro do Cliente -->
                        <div class="row">
                            <div class="col-6">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th colspan="3" class="bg-secondary" style="font-size: 10px;">
                                                RESUMO FINANCEIRO - {{ destination.destination.name.toUpperCase() }}
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td style="font-size: 10px;">Cliente</td>
                                            <td colspan="2" style="font-size: 10px;">
                                                {{ destination.destination.name }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="font-size: 10px;">Equipamentos</td>
                                            <td style="font-size: 10px;">
                                                {{ destination.equipments.length }}
                                            </td>
                                            <td style="font-size: 10px;">
                                                -
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="font-size: 10px;">Total de Itens</td>
                                            <td style="font-size: 10px;">
                                                {{ destination.equipments.reduce((total, eq) => total + eq.items.length, 0) }}
                                            </td>
                                            <td style="font-size: 10px;">
                                                -
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="font-size: 10px;">Valor Total</td>
                                            <td style="font-size: 10px;">
                                                1
                                            </td>
                                            <td style="font-size: 10px;">
                                                {{ formatCurrency(destination.totalAmount) }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="col-6">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th colspan="2" class="bg-secondary" style="font-size: 10px;">
                                                ESTATÍSTICAS DE PAGAMENTO
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td style="font-size: 10px;">Itens Pagos</td>
                                            <td style="font-size: 10px;">
                                                {{ destination.equipments.reduce((total, eq) => total + eq.items.filter(i => i.is_paid).length, 0) }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="font-size: 10px;">Itens Pendentes</td>
                                            <td style="font-size: 10px;">
                                                {{ destination.equipments.reduce((total, eq) => total + eq.items.filter(i => !i.is_paid).length, 0) }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="font-size: 10px;">Valor Pago</td>
                                            <td style="font-size: 10px;">
                                                {{ formatCurrency(destination.equipments.reduce((total, eq) => total + eq.items.filter(i => i.is_paid).reduce((sum, item) => sum + getSafeAmount(item.amount), 0), 0)) }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="font-size: 10px;">Valor Pendente</td>
                                            <td style="font-size: 10px;">
                                                {{ formatCurrency(destination.equipments.reduce((total, eq) => total + eq.items.filter(i => !i.is_paid).reduce((sum, item) => sum + getSafeAmount(item.amount), 0), 0)) }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Detalhes do Cliente -->
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th class="bg-primary text-white" colspan="6" style="font-size: 10px;">
                                        DETALHES DA FATURA - {{ destination.destination.name.toUpperCase() }}
                                    </th>
                                </tr>
                                <tr>
                                    <th class="bg-secondary" style="font-size: 10px;">Equipamento</th>
                                    <th class="bg-secondary" style="font-size: 10px;">Taxa</th>
                                    <th class="bg-secondary" style="font-size: 10px;">Valor</th>
                                    <th class="bg-secondary" style="font-size: 10px;">Status</th>
                                    <th class="bg-secondary" style="font-size: 10px;">Pago em</th>
                                    <th class="bg-secondary" style="font-size: 10px;">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="equipment in destination.equipments" :key="equipment.equipment.id">
                                    <td style="font-size: 10px; vertical-align: top; font-weight: bold;">
                                        {{ equipment.equipment.name }}
                                        <br>
                                        <small class="text-muted">{{ equipment.equipment.type_equipment?.name || 'N/A' }}</small>
                                    </td>
                                    <td style="font-size: 10px; vertical-align: top;">
                                        <div v-for="item in equipment.items" :key="item.id" style="margin-bottom: 5px;">
                                            {{ item.fee.name }}
                                        </div>
                                    </td>
                                    <td style="font-size: 10px; vertical-align: top;">
                                        <div v-for="item in equipment.items" :key="item.id" style="margin-bottom: 5px;">
                                            {{ formatCurrency(getSafeAmount(item.amount)) }}
                                        </div>
                                    </td>
                                    <td style="font-size: 10px; vertical-align: top;">
                                        <div v-for="item in equipment.items" :key="item.id" style="margin-bottom: 5px;">
                                            <span :class="item.is_paid ? 'text-success' : 'text-warning'">
                                                {{ item.is_paid ? 'PAGO' : 'PENDENTE' }}
                                            </span>
                                        </div>
                                    </td>
                                    <td style="font-size: 10px; vertical-align: top;">
                                        <div v-for="item in equipment.items" :key="item.id" style="margin-bottom: 5px;">
                                            {{ item.paid_at ? formatDate(item.paid_at) : '-' }}
                                        </div>
                                    </td>
                                    <td style="font-size: 10px; vertical-align: top; font-weight: bold;">
                                        {{ formatCurrency(equipment.totalAmount) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="5" class="text-end" style="font-size: 10px; font-weight: bold;">
                                        TOTAL {{ destination.destination.name.toUpperCase() }}:
                                    </td>
                                    <td class="bg-success text-white" style="font-size: 12px; font-weight: bold;">
                                        {{ formatCurrency(destination.totalAmount) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Observações específicas do cliente (se houver) -->
                        <div v-if="invoice.notes" class="mt-4">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th class="bg-info text-white" style="font-size: 10px;">
                                            OBSERVAÇÕES
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="font-size: 10px;">
                                            {{ invoice.notes }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Rodapé do Cliente -->
                        <div class="row mt-4">
                            <div class="col-12 text-center">
                                <p style="font-size: 8px; color: #666;">
                                    Esta fatura foi gerada automaticamente pelo sistema de gestão do Condomínio Dana Place.<br>
                                    Fatura específica para: <strong>{{ destination.destination.name }}</strong><br>
                                    Para dúvidas ou esclarecimentos, entre em contato através dos canais oficiais.<br>
                                    <strong>Data de geração:</strong> {{ formatDateTime(new Date()) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div v-else class="text-center p-5">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Carregando...</span>
        </div>
        <p class="mt-2">Carregando fatura...</p>
    </div>
</template>

<style scoped>
.badge {
    font-size: 0.75em;
}

.bg-success-subtle {
    background-color: rgba(25, 135, 84, 0.1) !important;
}

.text-decoration-line-through {
    text-decoration: line-through !important;
}

dl.row dt {
    font-weight: 600;
}

.progress {
    height: 8px;
    background-color: #e9ecef;
}

.form-check-input:checked {
    background-color: #198754;
    border-color: #198754;
}

.border-light {
    border-color: #f8f9fa !important;
}

.card-body {
    scrollbar-width: thin;
}

.card-body::-webkit-scrollbar {
    width: 8px;
}

.card-body::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.card-body::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 4px;
}

.card-body::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}
</style>