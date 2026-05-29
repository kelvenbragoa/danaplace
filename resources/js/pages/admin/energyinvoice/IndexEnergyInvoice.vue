<script setup>

import {onMounted, ref, reactive,watch, computed, nextTick, onUnmounted} from 'vue';
import axios from 'axios';
import {useToastr} from '../../../toastr';
import moment from 'moment'
import {debounce} from 'lodash';
import { Bootstrap4Pagination } from 'laravel-vue-pagination';
import VueFeather from 'vue-feather';
import { useRouter } from 'vue-router';
import * as bootstrap from 'bootstrap';

const router = useRouter();
const toastr = useToastr();

// Reactive data
const invoices = ref({ data: [], total: 0, per_page: 15, current_page: 1 });
const statistics = ref({});
const loadingDiv = ref(true);
const searchQuery = ref('');
const statusFilter = ref('');
const monthFilter = ref('');
const yearFilter = ref('');
const loadingButtonDelete = ref(false);

let dataIdBeingDeleted = ref(null);

// Computed
const filteredInvoicesCount = computed(() => invoices.value.total);

const formatCurrency = (amount) => {
    if (isNaN(amount) || amount === null || amount === undefined) {
        return '0,00 MT';
    }
    return new Intl.NumberFormat('pt-MZ', {
        style: 'currency',
        currency: 'MZN',
        minimumFractionDigits: 2
    }).format(amount);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('pt-BR');
};

const getStatusBadge = (status) => {
    const badges = {
        'draft': { class: 'bg-secondary', text: 'Rascunho' },
        'issued': { class: 'bg-primary', text: 'Emitida' },
        'partially_paid': { class: 'bg-warning text-dark', text: 'Parcialmente Paga' },
        'paid': { class: 'bg-success', text: 'Paga' },
        'overdue': { class: 'bg-danger', text: 'Vencida' },
        'cancelled': { class: 'bg-dark', text: 'Cancelada' }
    };
    return badges[status] || { class: 'bg-secondary', text: 'Desconhecido' };
};

const getProgressPercentage = (invoice) => {
    const total = parseFloat(invoice.total_amount || invoice.invoice_total_cost || 0);
    const paid = parseFloat(invoice.paid_amount || 0);
    
    if (total === 0) return 0;
    
    const percentage = Math.round((paid / total) * 100);
    return Math.min(100, Math.max(0, percentage)); // Garantir que está entre 0 e 100
};



// Methods
const getData = async (page = 1) => {
    try {
        loadingDiv.value = true;
        const params = new URLSearchParams();
        
        if (searchQuery.value) params.append('query', searchQuery.value);
        if (statusFilter.value) params.append('status', statusFilter.value);
        if (monthFilter.value) params.append('month', monthFilter.value);
        if (yearFilter.value) params.append('year', yearFilter.value);
        params.append('page', page);

        const response = await axios.get(`/energyinvoice?${params.toString()}`);
        invoices.value = response.data.invoices;
        statistics.value = response.data.statistics;
    } catch (error) {
        toastr.error('Erro ao carregar faturas de energia');
        console.error(error);
    } finally {
        loadingDiv.value = false;
    }
};

const handleSearch = () => {
    getData();
};

const clearFilters = () => {
    searchQuery.value = '';
    statusFilter.value = '';
    monthFilter.value = '';
    yearFilter.value = '';
    getData();
};

const confirmDeletion = (data) => {
    dataIdBeingDeleted.value = data.id;
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
};

const deleteData = () => {
    loadingButtonDelete.value = true;

    axios.delete(`/energyinvoice/${dataIdBeingDeleted.value}`)
    .then(() => {
        invoices.value.data = invoices.value.data.filter(data => data.id !== dataIdBeingDeleted.value); 
        const modal = bootstrap.Modal.getInstance(document.getElementById('deleteModal'));
        modal.hide();
        toastr.success('Fatura apagada com sucesso');
    }).catch((error) => {
        const message = error.response?.data?.message || 'Erro ao apagar fatura';
        toastr.error(message);
        loadingButtonDelete.value = false;
        const modal = bootstrap.Modal.getInstance(document.getElementById('deleteModal'));
        modal.hide();
    }).finally(() => {
        loadingButtonDelete.value = false;
    });
};

// Dropdown state management (same as FeeInvoice)
const openDropdown = ref(null);

const toggleDropdown = (invoiceId, event) => {
    event.preventDefault();
    event.stopPropagation();
    
    if (openDropdown.value === invoiceId) {
        openDropdown.value = null;
    } else {
        openDropdown.value = invoiceId;
    }
};

const closeDropdown = () => {
    openDropdown.value = null;
};

// Close dropdown when clicking outside
const handleOutsideClick = (event) => {
    if (!event.target.closest('.dropdown')) {
        closeDropdown();
    }
};

// Watchers
watch([searchQuery, statusFilter, monthFilter, yearFilter], () => {
    getData();
}, { debounce: 500 });

// Lifecycle
onMounted(() => {
    getData();
    document.addEventListener('click', handleOutsideClick);
});

// Clean up event listener
onUnmounted(() => {
    document.removeEventListener('click', handleOutsideClick);
});



</script>

<template>
    <div v-if="!loadingDiv">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1">
                    <vue-feather type="zap" class="align-middle me-2"></vue-feather>
                    Faturas de Energia
                </h1>
                <p class="text-muted mb-0">Gerencie faturas de energia e controle pagamentos</p>
            </div>
            <div class="d-flex gap-2">
                <router-link to="/admin/energyinvoice/create" class="btn btn-primary">
                    <vue-feather type="plus" size="16" class="me-1"></vue-feather>
                    Nova Fatura
                </router-link>
            </div>
        </div>

        <!-- Dashboard Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Total de Faturas
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ statistics.total_invoices || 0 }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <vue-feather type="file-text" class="text-primary" size="24"></vue-feather>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Valor Total
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ formatCurrency(statistics.total_amount || 0) }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <vue-feather type="dollar-sign" class="text-success" size="24"></vue-feather>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Faturas Pagas
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ statistics.paid_invoices || 0 }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <vue-feather type="check-circle" class="text-info" size="24"></vue-feather>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Faturas Pendentes
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ statistics.pending_invoices || 0 }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <vue-feather type="clock" class="text-warning" size="24"></vue-feather>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Buscar</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <vue-feather type="search" size="16"></vue-feather>
                            </span>
                            <input 
                                type="text" 
                                class="form-control" 
                                v-model="searchQuery" 
                                placeholder="ID da fatura ou equipamento..."
                            >
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select class="form-select" v-model="statusFilter">
                            <option value="">Todos</option>
                            <option value="issued">Emitidas</option>
                            <option value="partially_paid">Parcialmente Pagas</option>
                            <option value="paid">Pagas</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Mês</label>
                        <select class="form-select" v-model="monthFilter">
                            <option value="">Todos</option>
                            <option value="1">Janeiro</option>
                            <option value="2">Fevereiro</option>
                            <option value="3">Março</option>
                            <option value="4">Abril</option>
                            <option value="5">Maio</option>
                            <option value="6">Junho</option>
                            <option value="7">Julho</option>
                            <option value="8">Agosto</option>
                            <option value="9">Setembro</option>
                            <option value="10">Outubro</option>
                            <option value="11">Novembro</option>
                            <option value="12">Dezembro</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Ano</label>
                        <select class="form-select" v-model="yearFilter">
                            <option value="">Todos</option>
                            <option value="2024">2024</option>
                            <option value="2025">2025</option>
                            <option value="2026">2026</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button 
                                type="button" 
                                class="btn btn-outline-secondary"
                                @click="clearFilters"
                            >
                                <vue-feather type="x" size="16"></vue-feather>
                                Limpar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lista de Faturas -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    Faturas de Energia
                    <span class="badge bg-secondary ms-2">{{ filteredInvoicesCount }}</span>
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Período</th>
                                <th>Valor Total</th>
                                <th>Status</th>
                                <th>Progresso</th>
                                <th>Itens</th>
                                <th>Data</th>
                                <th class="text-end">Ações</th>
                            </tr>
                        </thead>
                        <tbody v-if="invoices.data && invoices.data.length > 0">
                            <tr v-for="invoice in invoices.data" :key="invoice.id">
                                <td>
                                    <span class="fw-bold text-primary">#{{ invoice.id }}</span>
                                </td>
                                <td>
                                    <div class="fw-bold">{{ formatDate(invoice.start_date_period) }}</div>
                                    <small class="text-muted">até {{ formatDate(invoice.end_date_period) }}</small>
                                </td>
                                <td>
                                    <div class="fw-bold">{{ formatCurrency(invoice.total_amount || 0) }}</div>
                                    <small class="text-muted">Pago: {{ formatCurrency(invoice.paid_amount || 0) }}</small>
                                    <!-- Debug info -->
                                    <!-- <small class="text-info d-block" style="font-size: 0.7em;">
                                        T:{{ invoice.total_amount || 0 }} P:{{ invoice.paid_amount || 0 }} %:{{ getProgressPercentage(invoice) }}
                                    </small> -->
                                </td>
                                <td>
                                    <span class="badge" :class="getStatusBadge(invoice.status).class">
                                        {{ getStatusBadge(invoice.status).text }}
                                    </span>
                                </td>
                                <td style="width: 150px;">
                                    <div class="progress mb-1" style="height: 6px;">
                                        <div 
                                            class="progress-bar" 
                                            :class="{
                                                'bg-success': getProgressPercentage(invoice) === 100,
                                                'bg-warning': getProgressPercentage(invoice) > 0 && getProgressPercentage(invoice) < 100,
                                                'bg-secondary': getProgressPercentage(invoice) === 0
                                            }"
                                            :style="`width: ${getProgressPercentage(invoice)}%`"
                                        ></div>
                                    </div>
                                    <small class="text-muted">{{ getProgressPercentage(invoice) }}%</small>
                                </td>
                                <td>
                                    <span class="badge bg-primary">{{ invoice.energy_invoice_items_count || 0 }}</span>
                                </td>
                                <td>
                                    <small class="text-muted">{{ formatDate(invoice.created_at) }}</small>
                                </td>
                                <td class="text-end">
                                    <div class="dropdown position-relative">
                                        <button 
                                            :id="`dropdown-${invoice.id}`"
                                            class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                            type="button"
                                            @click="toggleDropdown(invoice.id, $event)"
                                            :aria-expanded="openDropdown === invoice.id"
                                        >
                                            <vue-feather type="more-horizontal" size="16"></vue-feather>
                                        </button>
                                        <ul 
                                            class="dropdown-menu"
                                            :class="{ 'show': openDropdown === invoice.id }"
                                            :aria-labelledby="`dropdown-${invoice.id}`"
                                        >
                                            <li>
                                                <router-link 
                                                    :to="`/admin/energyinvoice/${invoice.id}`" 
                                                    class="dropdown-item"
                                                >
                                                    <vue-feather type="eye" size="16" class="me-2"></vue-feather>
                                                    Visualizar
                                                </router-link>
                                            </li>
                                            <li>
                                                <router-link 
                                                    :to="`/admin/energyinvoice/${invoice.id}/edit`" 
                                                    class="dropdown-item"
                                                >
                                                    <vue-feather type="edit" size="16" class="me-2"></vue-feather>
                                                    Editar
                                                </router-link>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <button 
                                                    class="dropdown-item text-danger"
                                                    @click="confirmDeletion(invoice)"
                                                >
                                                    <vue-feather type="trash-2" size="16" class="me-2"></vue-feather>
                                                    Excluir
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                        <tbody v-else>
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <vue-feather type="inbox" size="48" class="text-muted mb-2"></vue-feather>
                                    <p class="text-muted">Nenhuma fatura encontrada</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Paginação -->
        <nav v-if="invoices.last_page > 1" class="mt-4">
            <Bootstrap4Pagination :data="invoices" @pagination-change-page="getData"/>
        </nav>
    </div>

    <div v-else class="text-center p-5">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Carregando...</span>
        </div>
        <p class="mt-2">Carregando faturas...</p>
    </div>

    <!-- Modal de confirmação de exclusão -->
    <div class="modal" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">Confirmar Exclusão</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Tem certeza que deseja excluir esta fatura de energia? Esta ação não poderá ser desfeita.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button @click.prevent="deleteData" type="button" class="btn btn-danger" :disabled="loadingButtonDelete">
                        <div v-if="loadingButtonDelete" class="spinner-border spinner-border-sm" role="status"></div>
                        <span v-else>Excluir Fatura</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.card-body .row.no-gutters {
    margin-right: 0;
    margin-left: 0;
}
.card-body .row.no-gutters > [class*="col-"] {
    padding-right: 0;
    padding-left: 0;
}
.progress {
    background-color: #e9ecef;
}
.badge {
    font-size: 0.75em;
}
</style>