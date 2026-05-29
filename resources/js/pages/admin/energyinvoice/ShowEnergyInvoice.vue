<script setup>
import axios from "axios";
import {
    ref,
    onMounted,
    reactive,
    defineEmits,
    defineComponent,
    watch,
    computed,
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
import * as bootstrap from 'bootstrap';

let retrievedData = ref({});
let loadingSubmit = ref(false);
let loadingDiv = ref(true);
const loadingprint = ref(false);
const router = useRouter();
const toastr = useToastr();

// Variáveis para leituras diárias
const readingsData = ref({
    readings: [],
    equipments: [],
    technicians: []
});

const showReadingModal = ref(false);
const editingReading = ref(null);

const readingForm = ref({
    equipment_id: '',
    reading_date: '',
    reading_value: '',
    notes: ''
});

const readingFilters = ref({
    equipment_id: '',
    start_date: '',
    end_date: ''
});

// Computed para filtrar leituras
const filteredReadings = computed(() => {
    let filtered = readingsData.value.readings || [];
    
    if (readingFilters.value.equipment_id) {
        filtered = filtered.filter(reading => reading.equipment_id == readingFilters.value.equipment_id);
    }
    
    if (readingFilters.value.start_date) {
        filtered = filtered.filter(reading => reading.reading_date >= readingFilters.value.start_date);
    }
    
    if (readingFilters.value.end_date) {
        filtered = filtered.filter(reading => reading.reading_date <= readingFilters.value.end_date);
    }
    
    return filtered.sort((a, b) => new Date(b.reading_date) - new Date(a.reading_date));
});

let { paperize } = usePaperizer("print-me", {
    styles: [
        "https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css",
    ],
    windowTitle: `REF: ${retrievedData.value?.id || 0} - ENERGY INVOICE Nº: #${router.currentRoute.value.params.id}`,
});

const { paperize: paperizeIndividual } = usePaperizer("print-individual", {
    styles: [
        "https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css",
    ],
    windowTitle: `INDIVIDUAL INVOICE ITEM`,
});

const printIndividualItem = (item) => {
    const currentItem = ref(item);
    paperizeIndividual();
};

const formatCurrency = (value) => {
    if (isNaN(value) || value === null || value === undefined) {
        return '0,00';
    }
    return new Intl.NumberFormat('pt-BR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(Number(value));
};

const getStatusBadge = (status) => {
    const badges = {
        'issued': { class: 'bg-primary', text: 'Emitida' },
        'partially_paid': { class: 'bg-warning text-dark', text: 'Parcialmente Paga' },
        'paid': { class: 'bg-success', text: 'Paga' },
    };
    return badges[status] || { class: 'bg-secondary', text: 'Desconhecido' };
};

const getProgressPercentage = (invoice) => {
    const total = parseFloat(invoice.total_amount || invoice.invoice_total_cost || 0);
    const paid = parseFloat(invoice.paid_amount || 0);
    
    if (total === 0) return 0;
    
    const percentage = Math.round((paid / total) * 100);
    console.log('Progress calculation:', { total, paid, percentage }); // Debug
    return Math.min(100, Math.max(0, percentage));
};

const downloadMcscr = () => {
    loadingprint.value = true;
    paperize();
    loadingprint.value = false;
};

const toggleItemPayment = async (item) => {
    try {
        loadingSubmit.value = true;
        
        const response = await axios.post(
            `/energyinvoice/${retrievedData.value.id}/items/${item.id}/toggle-payment`,
            {
                is_paid: !item.is_paid,
                payment_details: {
                    marked_at: new Date().toISOString(),
                    amount: item.total_to_invoice
                }
            }
        );
        
        // Update item in the local data
        const itemIndex = retrievedData.value.energy_invoice_items.findIndex(i => i.id === item.id);
        if (itemIndex !== -1) {
            retrievedData.value.energy_invoice_items[itemIndex] = response.data.item;
        }
        
        // Recalculate totals
        const paidAmount = retrievedData.value.energy_invoice_items
            .filter(i => i.is_paid)
            .reduce((sum, i) => sum + parseFloat(i.total_to_invoice || 0), 0);
        
        const totalAmount = parseFloat(retrievedData.value.total_amount || retrievedData.value.invoice_total_cost || 0);
            
        retrievedData.value.paid_amount = paidAmount;
        retrievedData.value.total_amount = totalAmount;
        retrievedData.value.remaining_amount = totalAmount - paidAmount;
        
        console.log('Recalculated totals:', { totalAmount, paidAmount, remaining: totalAmount - paidAmount }); // Debug
        
        // Update status
        if (paidAmount === 0) {
            retrievedData.value.status = 'issued';
        } else if (paidAmount >= retrievedData.value.total_amount) {
            retrievedData.value.status = 'paid';
        } else {
            retrievedData.value.status = 'partially_paid';
        }
        
        toastr.success(response.data.message);
    } catch (error) {
        toastr.error('Erro ao atualizar status de pagamento');
        console.error(error);
    } finally {
        loadingSubmit.value = false;
    }
};

const getData = () => {
    axios
        .get(`/energyinvoice/${router.currentRoute.value.params.id}`)
        .then((response) => {
            loadingDiv.value = false;
            retrievedData.value = response.data.energyinvoice;
        })
        .catch((error) => {
            loadingDiv.value = false;
            toastr.error('Erro ao carregar dados da fatura');
            console.error(error);
        });
};

// Métodos para leituras diárias
const getReadings = async () => {
    try {
        const response = await axios.get(`/energyinvoice/${router.currentRoute.value.params.id}/readings`);
        readingsData.value = response.data;
    } catch (error) {
        console.error('Erro ao carregar leituras:', error);
        toastr.error('Erro ao carregar leituras');
    }
};

const resetReadingForm = () => {
    readingForm.value = {
        equipment_id: '',
        reading_date: new Date().toISOString().split('T')[0],
        reading_value: '',
        notes: ''
    };
    editingReading.value = null;
};

const closeReadingModal = () => {
    showReadingModal.value = false;
    resetReadingForm();
};

const editReading = (reading) => {
    editingReading.value = reading;
    readingForm.value = {
        equipment_id: reading.equipment_id,
        reading_date: reading.reading_date,
        reading_value: reading.reading_value,
        notes: reading.notes || ''
    };
    showReadingModal.value = true;
};

const saveReading = async () => {
    try {
        loadingSubmit.value = true;
        
        if (editingReading.value) {
            // Atualizar leitura existente
            const response = await axios.put(
                `/energyinvoice/${router.currentRoute.value.params.id}/readings/${editingReading.value.id}`,
                readingForm.value
            );
            toastr.success('Leitura atualizada com sucesso');
        } else {
            // Criar nova leitura
            const response = await axios.post(
                `/energyinvoice/${router.currentRoute.value.params.id}/readings`,
                readingForm.value
            );
            toastr.success('Leitura registrada com sucesso');
        }
        
        closeReadingModal();
        await getReadings(); // Recarregar leituras
        
    } catch (error) {
        console.error('Erro ao salvar leitura:', error);
        if (error.response?.data?.message) {
            toastr.error(error.response.data.message);
        } else {
            toastr.error('Erro ao salvar leitura');
        }
    } finally {
        loadingSubmit.value = false;
    }
};

const deleteReading = async (reading) => {
    if (!confirm('Tem certeza que deseja excluir esta leitura?')) return;
    
    try {
        loadingSubmit.value = true;
        
        await axios.delete(
            `/energyinvoice/${router.currentRoute.value.params.id}/readings/${reading.id}`
        );
        
        toastr.success('Leitura excluída com sucesso');
        await getReadings(); // Recarregar leituras
        
    } catch (error) {
        console.error('Erro ao excluir leitura:', error);
        toastr.error('Erro ao excluir leitura');
    } finally {
        loadingSubmit.value = false;
    }
};

const applyReadingFilters = () => {
    // Os filtros são aplicados pelo computed filteredReadings
};

const clearReadingFilters = () => {
    readingFilters.value = {
        equipment_id: '',
        start_date: '',
        end_date: ''
    };
};

const formatDateRelative = (date) => {
    return moment(date).fromNow();
};

// Watchers
watch(showReadingModal, (newVal) => {
    const modalElement = document.getElementById('readingModal');
    if (modalElement) {
        const modal = new bootstrap.Modal(modalElement);
        if (newVal) {
            modal.show();
        } else {
            modal.hide();
        }
    }
});

onMounted(() => {
    getData();
    getReadings();
});
</script>

<template>
    <div v-if="!loadingDiv">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1">
                    <vue-feather type="zap" class="align-middle me-2"></vue-feather>
                    Fatura de Energia #{{ retrievedData.id }}
                </h1>
                <p class="text-muted mb-0">Período: {{ retrievedData.start_date_period }} - {{ retrievedData.end_date_period }}</p>
            </div>
            <div class="d-flex gap-2">
                <button 
                    @click="downloadMcscr"
                    class="btn btn-outline-secondary"
                    :disabled="loadingprint"
                >
                    <vue-feather type="printer" size="16" class="me-1"></vue-feather>
                    Imprimir
                </button>
                <router-link 
                    :to="`/admin/energyinvoice/${retrievedData.id}/edit`" 
                    class="btn btn-outline-primary"
                >
                    <vue-feather type="edit" size="16" class="me-1"></vue-feather>
                    Editar
                </router-link>
                <router-link 
                    to="/admin/energyinvoice" 
                    class="btn btn-secondary"
                >
                    <vue-feather type="arrow-left" size="16" class="me-1"></vue-feather>
                    Voltar
                </router-link>
            </div>
        </div>

        <!-- Status e Progresso da Fatura -->
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title mb-0">Status do Pagamento</h5>
                            <span class="badge" :class="getStatusBadge(retrievedData.status).class">
                                {{ getStatusBadge(retrievedData.status).text }}
                            </span>
                        </div>
                        
                        <div class="progress mb-3" style="height: 10px;">
                            <div 
                                class="progress-bar" 
                                :class="{
                                    'bg-success': getProgressPercentage(retrievedData) === 100,
                                    'bg-warning': getProgressPercentage(retrievedData) > 0 && getProgressPercentage(retrievedData) < 100,
                                    'bg-secondary': getProgressPercentage(retrievedData) === 0
                                }"
                                :style="`width: ${getProgressPercentage(retrievedData)}%`"
                            ></div>
                        </div>
                        
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="text-muted small">Valor Total</div>
                                <div class="fw-bold">{{ formatCurrency(retrievedData.total_amount || 0) }}</div>
                            </div>
                            <div class="col-4">
                                <div class="text-muted small">Pago</div>
                                <div class="fw-bold text-success">{{ formatCurrency(retrievedData.paid_amount || 0) }}</div>
                            </div>
                            <div class="col-4">
                                <div class="text-muted small">Pendente</div>
                                <div class="fw-bold text-warning">{{ formatCurrency(retrievedData.remaining_amount || (retrievedData.total_amount || retrievedData.invoice_total_cost || 0) - (retrievedData.paid_amount || 0)) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title">Informações Gerais</h6>
                        <dl class="row mb-0">
                            <dt class="col-6 small">Consumo Ativo:</dt>
                            <dd class="col-6 small">{{ retrievedData.active_energy_consumption }} kWh</dd>
                            
                            <dt class="col-6 small">Consumo Reativo:</dt>
                            <dd class="col-6 small">{{ retrievedData.reactive_energy_consumption }} kWh</dd>
                            
                            <dt class="col-6 small">IVA:</dt>
                            <dd class="col-6 small">{{ formatCurrency(retrievedData.tax_iva || 0) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lista de Itens da Fatura -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    Itens da Fatura
                    <span class="badge bg-primary ms-2">{{ retrievedData.energy_invoice_items?.length || 0 }}</span>
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Equipamento</th>
                                <th>Destino</th>
                                <th>Consumo (kWh)</th>
                                <th>Custo Base</th>
                                <th>IVA</th>
                                <th>Total a Faturar</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in retrievedData.energy_invoice_items" :key="item.id">
                                <td>
                                    <div class="fw-bold">{{ item.equipment?.name || 'N/A' }}</div>
                                    <small class="text-muted">ID: {{ item.equipment?.id }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ item.destination?.name || 'N/A' }}</span>
                                </td>
                                <td>{{ formatCurrency(item.apr_consumption) }}</td>
                                <td>{{ formatCurrency(item.cost) }}</td>
                                <td>{{ formatCurrency(item.tax_iva) }}</td>
                                <td>
                                    <div class="fw-bold">{{ formatCurrency(item.total_to_invoice) }}</div>
                                </td>
                                <td>
                                    <span 
                                        class="badge" 
                                        :class="item.is_paid ? 'bg-success' : 'bg-warning text-dark'"
                                    >
                                        {{ item.is_paid ? 'Pago' : 'Pendente' }}
                                    </span>
                                    <div v-if="item.is_paid && item.paid_at" class="small text-muted">
                                        {{ new Date(item.paid_at).toLocaleDateString('pt-BR') }}
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <button 
                                            @click="toggleItemPayment(item)"
                                            class="btn btn-sm"
                                            :class="item.is_paid ? 'btn-outline-warning' : 'btn-outline-success'"
                                            :disabled="loadingSubmit"
                                        >
                                            <vue-feather 
                                                :type="item.is_paid ? 'x-circle' : 'check-circle'" 
                                                size="14" 
                                                class="me-1"
                                            ></vue-feather>
                                            {{ item.is_paid ? 'Desmarcar' : 'Marcar Pago' }}
                                        </button>
                                        <button 
                                            @click="printIndividualItem(item)"
                                            class="btn btn-sm btn-outline-secondary"
                                            title="Imprimir item individual"
                                        >
                                            <vue-feather type="printer" size="14"></vue-feather>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Seção de Leituras Diárias -->
        <div class="card mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <vue-feather type="activity" class="me-2"></vue-feather>
                    Leituras Diárias de Consumo
                    <span class="badge bg-info ms-2">{{ readingsData.readings?.length || 0 }}</span>
                </h5>
                <button 
                    class="btn btn-primary btn-sm" 
                    @click="showReadingModal = true"
                    :disabled="loadingDiv"
                >
                    <vue-feather type="plus" size="16" class="me-1"></vue-feather>
                    Nova Leitura
                </button>
            </div>
            <div class="card-body">
                <!-- Filtros de Leituras -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Equipamento</label>
                        <select class="form-select form-select-sm" v-model="readingFilters.equipment_id">
                            <option value="">Todos os equipamentos</option>
                            <option 
                                v-for="equipment in readingsData.equipments" 
                                :key="equipment.id" 
                                :value="equipment.id"
                            >
                                {{ equipment.name }}
                            </option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Data Inicial</label>
                        <input type="date" class="form-control form-control-sm" v-model="readingFilters.start_date">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Data Final</label>
                        <input type="date" class="form-control form-control-sm" v-model="readingFilters.end_date">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline-primary btn-sm" @click="applyReadingFilters">
                                <vue-feather type="filter" size="14" class="me-1"></vue-feather>
                                Filtrar
                            </button>
                            <button class="btn btn-outline-secondary btn-sm" @click="clearReadingFilters">
                                <vue-feather type="x" size="14" class="me-1"></vue-feather>
                                Limpar
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Tabela de Leituras -->
                <div class="table-responsive">
                    <table class="table table-hover table-sm">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Equipamento</th>
                                <th>Leitura Atual</th>
                                <th>Leitura Anterior</th>
                                <th>Consumo</th>
                                <th>Técnico</th>
                                <th>Observações</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody v-if="filteredReadings.length > 0">
                            <tr v-for="reading in filteredReadings" :key="reading.id">
                                <td>
                                    <div class="fw-bold">{{ reading.reading_date_formatted }}</div>
                                    <small class="text-muted">{{ formatDateRelative(reading.reading_date) }}</small>
                                </td>
                                <td>
                                    <div class="fw-bold">{{ reading.equipment?.name }}</div>
                                    <small class="text-muted">ID: {{ reading.equipment?.id }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-primary">{{ formatCurrency(reading.reading_value) }} kWh</span>
                                </td>
                                <td>
                                    <span v-if="reading.previous_reading" class="text-muted">
                                        {{ formatCurrency(reading.previous_reading) }} kWh
                                    </span>
                                    <span v-else class="text-muted fst-italic">N/A</span>
                                </td>
                                <td>
                                    <span 
                                        v-if="reading.consumption" 
                                        class="fw-bold"
                                        :class="reading.consumption > 0 ? 'text-success' : 'text-danger'"
                                    >
                                        {{ reading.consumption > 0 ? '+' : '' }}{{ formatCurrency(reading.consumption) }} kWh
                                    </span>
                                    <span v-else class="text-muted fst-italic">N/A</span>
                                </td>
                                <td>
                                    <div class="fw-bold">{{ reading.technician.firstName }} {{ reading.technician.lastName }}</div>
                                    <small class="text-muted">{{ formatDateRelative(reading.created_at) }}</small>
                                </td>
                                <td>
                                    <span v-if="reading.notes" class="text-truncate d-inline-block" style="max-width: 150px;" :title="reading.notes">
                                        {{ reading.notes }}
                                    </span>
                                    <span v-else class="text-muted fst-italic">—</span>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <button 
                                            @click="editReading(reading)"
                                            class="btn btn-sm btn-outline-primary"
                                            title="Editar leitura"
                                        >
                                            <vue-feather type="edit" size="14"></vue-feather>
                                        </button>
                                        <button 
                                            @click="deleteReading(reading)"
                                            class="btn btn-sm btn-outline-danger"
                                            title="Excluir leitura"
                                            :disabled="loadingSubmit"
                                        >
                                            <vue-feather type="trash-2" size="14"></vue-feather>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                        <tbody v-else>
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <div class="text-muted">
                                        <vue-feather type="activity" size="48" class="mb-2 opacity-50"></vue-feather>
                                        <div>Nenhuma leitura registrada</div>
                                        <small>Clique em "Nova Leitura" para adicionar a primeira leitura</small>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal para Nova/Editar Leitura -->
        <div class="modal fade" id="readingModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            {{ editingReading ? 'Editar Leitura' : 'Nova Leitura' }}
                        </h5>
                        <button type="button" class="btn-close" @click="closeReadingModal"></button>
                    </div>
                    <div class="modal-body">
                        <form @submit.prevent="saveReading">
                            <div class="mb-3">
                                <label class="form-label">Equipamento *</label>
                                <select 
                                    class="form-select" 
                                    v-model="readingForm.equipment_id"
                                    :disabled="editingReading"
                                    required
                                >
                                    <option value="">Selecione um equipamento</option>
                                    <option 
                                        v-for="equipment in readingsData.equipments" 
                                        :key="equipment.id" 
                                        :value="equipment.id"
                                    >
                                        {{ equipment.name }}
                                    </option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Data da Leitura *</label>
                                <input 
                                    type="date" 
                                    class="form-control" 
                                    v-model="readingForm.reading_date"
                                    :max="new Date().toISOString().split('T')[0]"
                                    required
                                >
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Valor da Leitura (kWh) *</label>
                                <input 
                                    type="number" 
                                    step="0.01" 
                                    min="0"
                                    class="form-control" 
                                    v-model="readingForm.reading_value"
                                    placeholder="Ex: 1250.50"
                                    required
                                >
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Observações</label>
                                <textarea 
                                    class="form-control" 
                                    v-model="readingForm.notes"
                                    rows="3"
                                    placeholder="Observações sobre a leitura..."
                                    maxlength="500"
                                ></textarea>
                                <div class="form-text">{{ readingForm.notes?.length || 0 }}/500 caracteres</div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="closeReadingModal">
                            Cancelar
                        </button>
                        <button 
                            type="button" 
                            class="btn btn-primary" 
                            @click="saveReading"
                            :disabled="loadingSubmit || !readingForm.equipment_id || !readingForm.reading_date || !readingForm.reading_value"
                        >
                            <span v-if="loadingSubmit" class="spinner-border spinner-border-sm me-2"></span>
                            {{ editingReading ? 'Atualizar' : 'Salvar' }} Leitura
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Versão para Impressão Geral -->
        <div id="print-me" class="d-none d-print-block">
            <div class="row text-center">
                <div class="col text-center" style="text-align: center">
                    <h2>Areia Branca Condominium Energy Bill</h2>
                </div>
            </div>
            <div class="row">
                <br />
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
                </div>
            </div>

            <div class="row">
                <div class="col">
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
            </div>
            
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th style="font-size: 10px;">
                            DATE:
                            {{ new Date(retrievedData.created_at).toLocaleDateString('pt-BR') }}
                        </th>
                        <th style="font-size: 10px;">
                            FROM:
                            {{ new Date(retrievedData.start_date_period).toLocaleDateString('pt-BR') }}
                        </th>
                        <th style="font-size: 10px;">
                            TO:
                            {{ new Date(retrievedData.end_date_period).toLocaleDateString('pt-BR') }}
                        </th>
                        <th style="font-size: 10px;">
                            INVOICE Nº: #{{ retrievedData.id }}
                        </th>
                    </tr>
                </thead>
            </table>
            
            <div class="row">
                <div class="col-6">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th colspan="3" class="bg-secondary" style="font-size: 10px;">
                                    INFORMATION FROM THE GENERAL EDM INVOICE
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="font-size: 10px;">Consumo de Energia Ativa</td>
                                <td style="font-size: 10px;">{{ formatCurrency(retrievedData.active_energy_consumption) }}</td>
                                <td style="font-size: 10px;">{{ formatCurrency(retrievedData.active_energy_consumption_cost) }} MT</td>
                            </tr>
                            <tr>
                                <td style="font-size: 10px;">Consumo de Energia Reativa</td>
                                <td style="font-size: 10px;">{{ formatCurrency(retrievedData.reactive_energy_consumption) }}</td>
                                <td style="font-size: 10px;">{{ formatCurrency(retrievedData.reactive_energy_consumption_cost) }} MT</td>
                            </tr>
                            <tr>
                                <td style="font-size: 10px;">Perda</td>
                                <td style="font-size: 10px;">{{ formatCurrency(retrievedData.loss) }}</td>
                                <td style="font-size: 10px;">{{ formatCurrency(retrievedData.loss_cost) }} MT</td>
                            </tr>
                            <tr>
                                <td style="font-size: 10px;">Ponta</td>
                                <td style="font-size: 10px;">{{ formatCurrency(retrievedData.ponta) }}</td>
                                <td style="font-size: 10px;">{{ formatCurrency(retrievedData.ponta_cost) }} MT</td>
                            </tr>
                            <tr>
                                <td style="font-size: 10px;">Taxa Fixa</td>
                                <td style="font-size: 10px;">{{ formatCurrency(retrievedData.fix_rate) }}</td>
                                <td style="font-size: 10px;">{{ formatCurrency(retrievedData.fix_rate_cost) }} MT</td>
                            </tr>
                            <tr>
                                <td style="font-size: 10px;">Taxa Iva</td>
                                <td style="font-size: 10px;">1</td>
                                <td style="font-size: 10px;">{{ formatCurrency(retrievedData.tax_iva) }} MT</td>
                            </tr>
                            <tr>
                                <td style="font-size: 10px;">Total</td>
                                <td style="font-size: 10px;"></td>
                                <td style="font-size: 10px;">{{ formatCurrency(retrievedData.invoice_total_cost) }} MT</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="col-6">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th colspan="3" class="bg-secondary" style="font-size: 10px;">
                                    INFORMATION OF THE COEFFICIENTS AND RATES OF THE CALCULATIONS
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="font-size: 10px;">Apartamentos</td>
                                <td style="font-size: 10px;">{{ retrievedData.quantity_houses }}</td>
                            </tr>
                            <tr>
                                <td style="font-size: 10px;">Ponta + Fix Rate</td>
                                <td style="font-size: 10px;">{{ formatCurrency(retrievedData.ponta_plus_fix_rate) }}</td>
                            </tr>
                            <tr>
                                <td style="font-size: 10px;">Fix Rate + Fix Rate per House *</td>
                                <td style="font-size: 10px;">{{ formatCurrency(retrievedData.fix_rate_plus_fix_rate_per_house) }}</td>
                            </tr>
                            <tr>
                                <td style="font-size: 10px;">Rate per Active Consumption **</td>
                                <td style="font-size: 10px;">{{ formatCurrency(retrievedData.rate_per_active_consumption) }}</td>
                            </tr>
                            <tr>
                                <td style="font-size: 10px;">Difference ***</td>
                                <td style="font-size: 10px;">{{ formatCurrency(retrievedData.difference) }}</td>
                            </tr>
                            <tr style="border-width: 0px;">
                                <td colspan="3" style="border-width: 0px;">
                                    <p style="font-size: 10px;">* Value that is added to the value regardless of consumption</p>
                                    <p style="font-size: 10px;">** Value used to calculate the cost relative to individual consumption</p>
                                    <p style="font-size: 10px;">*** Correction coefficient for the total EDM invoice</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th class="bg-secondary" style="font-size: 10px;">Name:</th>
                        <th class="bg-secondary" style="font-size: 10px;">Cliente:</th>
                        <th class="bg-secondary" style="font-size: 10px;">Meter:</th>
                        <th class="bg-secondary" style="font-size: 10px;">Custo:</th>
                        <th class="bg-secondary" style="font-size: 10px;">62%</th>
                        <th class="bg-secondary" style="font-size: 10px;">Iva:</th>
                        <th class="bg-secondary" style="font-size: 10px;">Total:</th>
                        <th class="bg-secondary" style="font-size: 10px;">Total Fatura:</th>
                        <th class="bg-secondary" style="font-size: 10px;">Status:</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in retrievedData.energy_invoice_items" :key="item.id">
                        <td style="font-size: 10px;">{{ item.equipment?.name }} - {{ item.equipment?.ref }}</td>
                        <td style="font-size: 10px;">{{ item.destination?.name }}</td>
                        <td style="font-size: 10px;">{{ formatCurrency(item.apr_consumption) }}</td>
                        <td style="font-size: 10px;">{{ formatCurrency(item.cost) }} MT</td>
                        <td style="font-size: 10px;">{{ formatCurrency(item.percentage_value) }} MT</td>
                        <td style="font-size: 10px;">{{ formatCurrency(item.tax_iva) }} MT</td>
                        <td style="font-size: 10px;">{{ formatCurrency(item.total) }} MT</td>
                        <td style="font-size: 10px;">{{ formatCurrency(item.total_to_invoice) }} MT</td>
                        <td style="font-size: 10px;">{{ item.is_paid ? 'PAGO' : 'PENDENTE' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Versão para Impressão Individual -->
        <div id="print-individual" class="d-none d-print-block">
            <div class="row text-center">
                <div class="col text-center" style="text-align: center">
                    <h2>Individual Energy Bill Item</h2>
                </div>
            </div>
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
                    <p style="font-size:10px">
                        Condominio Areia Branca Lda<br />
                        Cimento a Ponta de Ouro<br />
                        Matutuine, Moçambique<br />
                        Tel: +258 87 914 1774<br />
                        Email: info@ieareiabranca.com<br />
                        www.areiabranca.com
                    </p>
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

<style scoped>
@media print {
    .d-print-block {
        display: block !important;
    }
}

.progress {
    background-color: #e9ecef;
}

.badge {
    font-size: 0.75em;
}
</style>