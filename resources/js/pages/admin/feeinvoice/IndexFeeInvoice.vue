<script setup>
import { ref, onMounted, onUnmounted, computed, watch, nextTick } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import VueFeather from 'vue-feather';
import {useToastr} from '../../../toastr';
import { Dropdown } from 'bootstrap';

const toastr = useToastr();

// Reactive data
const router = useRouter();
const invoices = ref({ data: [], total: 0, per_page: 15, current_page: 1 });
const statistics = ref({});
const loadingDiv = ref(true);
const searchQuery = ref('');
const statusFilter = ref('');
const monthFilter = ref('');
const yearFilter = ref('');

// Computed
const filteredInvoicesCount = computed(() => invoices.value.total);

const formatCurrency = (amount) => {
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
        'partially_paid': { class: 'bg-warning', text: 'Parcialmente Paga' },
        'paid': { class: 'bg-success', text: 'Paga' },
        'overdue': { class: 'bg-danger', text: 'Vencida' },
        'cancelled': { class: 'bg-dark', text: 'Cancelada' }
    };
    return badges[status] || { class: 'bg-secondary', text: 'Desconhecido' };
};

const getProgressPercentage = (invoice) => {
    if (invoice.total_amount === 0) return 0;
    return Math.round((invoice.paid_amount / invoice.total_amount) * 100);
};

// Initialize Bootstrap dropdowns
const initializeDropdowns = async () => {
  await nextTick();
  // Force Bootstrap to reinitialize all dropdowns
  document.querySelectorAll('[data-bs-toggle="dropdown"]').forEach(button => {
    // Remove existing listeners
    const existingDropdown = Dropdown.getInstance(button);
    if (existingDropdown) {
      existingDropdown.dispose();
    }
    
    // Create new dropdown
    new Dropdown(button, {
      boundary: 'viewport',
      popperConfig: {
        placement: 'bottom-end'
      }
    });
  });
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

        const response = await axios.get(`/fee-invoices?${params.toString()}`);
        invoices.value = response.data.invoices;
        statistics.value = response.data.statistics;
        
        // Initialize dropdowns after data is loaded
        initializeDropdowns();
    } catch (error) {
        toastr.error('Erro ao carregar faturas');
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

const deleteInvoice = async (id) => {
    if (!confirm('Tem certeza que deseja excluir esta fatura?')) return;

    try {
        await axios.delete(`/fee-invoices/${id}`);
        toastr.success('Fatura excluída com sucesso');
        getData();
    } catch (error) {
        const message = error.response?.data?.message || 'Erro ao excluir fatura';
        toastr.error(message);
    }
};

const approveInvoice = async (id) => {
    if (!confirm('Tem certeza que deseja aprovar esta fatura?')) return;

    try {
        await axios.post(`/fee-invoices/${id}/approve`);
        toastr.success('Fatura aprovada com sucesso');
        getData();
    } catch (error) {
        const message = error.response?.data?.message || 'Erro ao aprovar fatura';
        toastr.error(message);
    }
};

// Watchers
watch([searchQuery, statusFilter, monthFilter, yearFilter], () => {
    getData();
}, { debounce: 500 });

// Lifecycle
// Dropdown state management
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

onMounted(() => {
    getData();
    document.addEventListener('click', handleOutsideClick);
});

// Clean up event listener
onUnmounted(() => {
    document.removeEventListener('click', handleOutsideClick);
});

// Watch for changes in invoices data and reinitialize dropdowns
watch(() => invoices.data, () => {
    nextTick(() => {
        initializeDropdowns();
    });
}, { flush: 'post' });
</script>

<template>
    <div v-if="!loadingDiv">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">
                <vue-feather type="file-text" class="align-middle me-2"></vue-feather>
                Faturas de Taxas
            </h1>
            <router-link 
                :to="{ name: 'admin.fee-invoices.create' }" 
                class="btn btn-primary"
            >
                <vue-feather type="plus" size="16" class="me-1"></vue-feather>
                Nova Fatura
            </router-link>
        </div>

        <!-- Dashboard Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6">
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
                                <vue-feather type="file-text" size="32" class="text-primary"></vue-feather>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
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
                                <vue-feather type="dollar-sign" size="32" class="text-success"></vue-feather>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Valor Pago
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ formatCurrency(statistics.paid_amount || 0) }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <vue-feather type="check-circle" size="32" class="text-info"></vue-feather>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Faturas Vencidas
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ statistics.overdue_invoices || 0 }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <vue-feather type="clock" size="32" class="text-warning"></vue-feather>
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
                            <input 
                                type="text" 
                                class="form-control" 
                                v-model="searchQuery"
                                placeholder="Número da fatura, observações..."
                            />
                            <button class="btn btn-outline-secondary" @click="handleSearch">
                                <vue-feather type="search" size="16"></vue-feather>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select v-model="statusFilter" class="form-select">
                            <option value="">Todos</option>
                            <option value="draft">Rascunho</option>
                            <option value="issued">Emitida</option>
                            <option value="partially_paid">Parcialmente Paga</option>
                            <option value="paid">Paga</option>
                            <option value="overdue">Vencida</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Mês</label>
                        <select v-model="monthFilter" class="form-select">
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
                        <select v-model="yearFilter" class="form-select">
                            <option value="">Todos</option>
                            <option value="2024">2024</option>
                            <option value="2025">2025</option>
                            <option value="2026">2026</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button class="btn btn-secondary me-2" @click="clearFilters">
                            <vue-feather type="x" size="16" class="me-1"></vue-feather>
                            Limpar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lista de Faturas -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    Faturas ({{ filteredInvoicesCount }})
                </h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Número</th>
                            <th>Período</th>
                            <th>Vencimento</th>
                            <th>Status</th>
                            <th>Valor Total</th>
                            <th>Progresso</th>
                            <th>Itens</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="invoice in invoices.data" :key="invoice.id">
                            <td>
                                <strong>{{ invoice.invoice_number }}</strong>
                            </td>
                            <td>{{ invoice.year }}</td>
                            <td>
                                {{ formatDate(invoice.due_date) }}
                                <small v-if="invoice.is_overdue" class="text-danger d-block">
                                    <vue-feather type="alert-triangle" size="12"></vue-feather>
                                    Vencida
                                </small>
                            </td>
                            <td>
                                <span 
                                    :class="`badge ${getStatusBadge(invoice.status).class}`"
                                >
                                    {{ getStatusBadge(invoice.status).text }}
                                </span>
                            </td>
                            <td>{{ formatCurrency(invoice.total_amount) }}</td>
                            <td>
                                <div class="progress mb-1" style="height: 6px;">
                                    <div 
                                        class="progress-bar"
                                        :class="invoice.status === 'paid' ? 'bg-success' : 'bg-info'"
                                        :style="`width: ${getProgressPercentage(invoice)}%`"
                                    ></div>
                                </div>
                                <small class="text-muted">
                                    {{ getProgressPercentage(invoice) }}% pago
                                </small>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <span class="badge bg-success">{{ invoice.paid_items_count }}</span>
                                    <span class="badge bg-warning">{{ invoice.unpaid_items_count }}</span>
                                </div>
                                <small class="text-muted d-block">
                                    {{ invoice.items_count }} total
                                </small>
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
                                                :to="{ name: 'admin.fee-invoices.show', params: { id: invoice.id } }"
                                                class="dropdown-item"
                                            >
                                                <vue-feather type="eye" size="16" class="me-2"></vue-feather>
                                                Visualizar
                                            </router-link>
                                        </li>
                                        <li>
                                            <router-link 
                                                :to="{ name: 'admin.fee-invoices.edit', params: { id: invoice.id } }"
                                                class="dropdown-item"
                                            >
                                                <vue-feather type="edit" size="16" class="me-2"></vue-feather>
                                                Editar
                                            </router-link>
                                        </li>
                                        <li v-if="invoice.status === 'draft'">
                                            <button 
                                                class="dropdown-item"
                                                @click="approveInvoice(invoice.id)"
                                            >
                                                <vue-feather type="check" size="16" class="me-2"></vue-feather>
                                                Aprovar
                                            </button>
                                        </li>
                                        <li>
                                            <a 
                                                :href="`/fee-invoices/${invoice.id}/report`"
                                                class="dropdown-item"
                                                target="_blank"
                                            >
                                                <vue-feather type="download" size="16" class="me-2"></vue-feather>
                                                Relatório PDF
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li v-if="invoice.status === 'draft'">
                                            <button 
                                                class="dropdown-item text-danger"
                                                @click="deleteInvoice(invoice.id)"
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
                </table>
            </div>
        </div>

        <!-- Paginação -->
        <nav v-if="invoices.last_page > 1" class="mt-4">
            <ul class="pagination justify-content-center">
                <li class="page-item" :class="{ disabled: invoices.current_page === 1 }">
                    <button class="page-link" @click="getData(invoices.current_page - 1)">
                        Anterior
                    </button>
                </li>
                <li 
                    v-for="page in Math.min(invoices.last_page, 5)" 
                    :key="page"
                    class="page-item" 
                    :class="{ active: page === invoices.current_page }"
                >
                    <button class="page-link" @click="getData(page)">{{ page }}</button>
                </li>
                <li class="page-item" :class="{ disabled: invoices.current_page === invoices.last_page }">
                    <button class="page-link" @click="getData(invoices.current_page + 1)">
                        Próxima
                    </button>
                </li>
            </ul>
        </nav>
    </div>

    <div v-else class="text-center p-5">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Carregando...</span>
        </div>
        <p class="mt-2">Carregando faturas...</p>
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