<template>
    <div>
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">Detalhes da Despesa</h1>
                <p class="text-muted">Visualize informações detalhadas sobre o registro de despesa</p>
            </div>
            <div>
                <router-link 
                    v-if="expense?.status === 'pending'"
                    :to="`/admin/expenses/${route.params.id}/edit`" 
                    class="btn btn-outline-primary me-2"
                >
                    <vue-feather type="edit-2" size="16" class="me-2"></vue-feather>
                    Editar
                </router-link>
                <router-link 
                    to="/admin/expenses" 
                    class="btn btn-outline-secondary"
                >
                    <vue-feather type="arrow-left" size="16" class="me-2"></vue-feather>
                    Voltar
                </router-link>
            </div>
        </div>

        <!-- Loading -->
        <div v-if="loadingDiv" class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Carregando...</span>
            </div>
        </div>

        <!-- Content -->
        <div v-else class="row">
            <!-- Main Info -->
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Informações da Despesa</h5>
                        <span 
                            class="badge fs-6"
                            :class="{
                                'bg-warning': expense?.status === 'pending',
                                'bg-info': expense?.status === 'approved',
                                'bg-success': expense?.status === 'paid',
                                'bg-danger': expense?.status === 'rejected',
                                'bg-dark': expense?.status === 'overdue'
                            }"
                        >
                            {{ getStatusLabel(expense?.status) }}
                        </span>
                    </div>
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-sm-3">Título:</dt>
                            <dd class="col-sm-9">{{ expense?.title }}</dd>
                            
                            <dt class="col-sm-3">Categoria:</dt>
                            <dd class="col-sm-9">
                                <div class="d-flex align-items-center">
                                    <div 
                                        class="rounded-circle me-2" 
                                        style="width: 12px; height: 12px;"
                                        :style="{ backgroundColor: expense?.expense_category?.color || '#6c757d' }"
                                    ></div>
                                    <span>{{ expense?.expense_category?.name || '-' }}</span>
                                </div>
                            </dd>
                            
                            <dt class="col-sm-3">Valor:</dt>
                            <dd class="col-sm-9">
                                <strong class="text-danger fs-5">{{ formatMoney(expense?.amount) }}</strong>
                            </dd>
                            
                            <dt class="col-sm-3">Data Vencimento:</dt>
                            <dd class="col-sm-9">
                                <strong>{{ formatDate(expense?.due_date) }}</strong>
                                <small class="text-muted ms-2">({{ getDayOfWeek(expense?.due_date) }})</small>
                            </dd>
                            
                            <dt class="col-sm-3">Prioridade:</dt>
                            <dd class="col-sm-9">
                                <span 
                                    class="badge"
                                    :class="{
                                        'bg-light text-dark': expense?.priority === 'low',
                                        'bg-secondary': expense?.priority === 'medium',
                                        'bg-warning': expense?.priority === 'high',
                                        'bg-danger': expense?.priority === 'urgent'
                                    }"
                                >
                                    {{ getPriorityLabel(expense?.priority) }}
                                </span>
                            </dd>

                            <dt v-if="expense?.vendor_name" class="col-sm-3">Fornecedor:</dt>
                            <dd v-if="expense?.vendor_name" class="col-sm-9">{{ expense?.vendor_name }}</dd>
                            
                            <dt v-if="expense?.vendor_document" class="col-sm-3">CNPJ/CPF:</dt>
                            <dd v-if="expense?.vendor_document" class="col-sm-9">{{ expense?.vendor_document }}</dd>
                            
                            <dt v-if="expense?.invoice_number" class="col-sm-3">Nº Fatura/NF:</dt>
                            <dd v-if="expense?.invoice_number" class="col-sm-9">{{ expense?.invoice_number }}</dd>
                            
                            <dt v-if="expense?.recurrence_type" class="col-sm-3">Recorrência:</dt>
                            <dd v-if="expense?.recurrence_type" class="col-sm-9">{{ getRecurrenceLabel(expense?.recurrence_type) }}</dd>
                            
                            <dt class="col-sm-3">Descrição:</dt>
                            <dd class="col-sm-9">
                                <div class="bg-light p-3 rounded">
                                    {{ expense?.description || '-' }}
                                </div>
                            </dd>
                        </dl>
                    </div>
                </div>

                <!-- Payment Information -->
                <div v-if="expense?.status === 'paid' && expense?.payment_date" class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <vue-feather 
                                type="dollar-sign" 
                                size="16" 
                                class="me-2 text-success"
                            ></vue-feather>
                            Informações de Pagamento
                        </h5>
                    </div>
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-sm-3">Data Pagamento:</dt>
                            <dd class="col-sm-9">{{ formatDate(expense?.payment_date) }}</dd>
                            
                            <dt class="col-sm-3">Pago por:</dt>
                            <dd class="col-sm-9">{{ expense?.paid_by_user?.name || '-' }}</dd>
                            
                            <dt v-if="expense?.payment_method" class="col-sm-3">Método:</dt>
                            <dd v-if="expense?.payment_method" class="col-sm-9">{{ getPaymentMethodLabel(expense?.payment_method) }}</dd>
                            
                            <dt v-if="expense?.payment_notes" class="col-sm-3">Observações:</dt>
                            <dd v-if="expense?.payment_notes" class="col-sm-9">
                                <div class="bg-light p-3 rounded">
                                    {{ expense?.payment_notes }}
                                </div>
                            </dd>
                        </dl>
                    </div>
                </div>

                <!-- Attachments -->
                <div v-if="expense?.attachments && Array.isArray(expense.attachments) && expense.attachments.length > 0" class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <vue-feather type="paperclip" size="16" class="me-2"></vue-feather>
                            Anexos ({{ expense?.attachments.length }})
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div 
                                v-for="attachment in expense?.attachment_details" 
                                :key="attachment.id || attachment.name" 
                                class="col-md-6 mb-3"
                            >
                                <div class="d-flex align-items-center justify-content-between p-3 border rounded">
                                    <div class="d-flex align-items-center">
                                        <vue-feather :type="getFileIcon(attachment.original_name || attachment.name)" size="20" class="me-3 text-primary"></vue-feather>
                                        <div>
                                            <div class="fw-medium">{{ attachment.original_name || attachment.name || 'Arquivo' }}</div>
                                            <small class="text-muted">{{ formatFileSize(attachment.file_size || attachment.size) }}</small>
                                        </div>
                                    </div>
                                    <a 
                                        :href="attachment.file_url || attachment.url" 
                                        target="_blank" 
                                        class="btn btn-sm btn-outline-primary"
                                        v-if="attachment.file_url || attachment.url"
                                    >
                                        <vue-feather type="download" size="16"></vue-feather>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rejection Section -->
                <div v-if="expense?.status === 'rejected'" class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <vue-feather 
                                type="x-circle" 
                                size="16" 
                                class="me-2 text-danger"
                            ></vue-feather>
                            Rejeição
                        </h5>
                    </div>
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-sm-3">Rejeitado por:</dt>
                            <dd class="col-sm-9">{{ expense?.approved_by_user?.name || '-' }}</dd>
                            
                            <dt class="col-sm-3">Data:</dt>
                            <dd class="col-sm-9">{{ formatDateTime(expense?.approved_at) }}</dd>
                            
                            <dt v-if="expense?.rejection_reason" class="col-sm-3">Motivo:</dt>
                            <dd v-if="expense?.rejection_reason" class="col-sm-9">
                                <div class="bg-light p-3 rounded">
                                    {{ expense?.rejection_reason }}
                                </div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-md-4">
                <!-- Actions -->
                <div v-if="expense?.status === 'pending' || expense?.status === 'approved'" class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Ações</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button 
                                v-if="expense?.status === 'pending'"
                                @click="showApproveModal" 
                                class="btn btn-success"
                                :disabled="loading"
                            >
                                <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                                <vue-feather v-else type="check" size="16" class="me-2"></vue-feather>
                                Aprovar
                            </button>
                            <button 
                                v-if="expense?.status === 'approved'"
                                @click="showPayModal" 
                                class="btn btn-primary"
                                :disabled="loading"
                            >
                                <vue-feather type="dollar-sign" size="16" class="me-2"></vue-feather>
                                Marcar como Paga
                            </button>
                            <button 
                                v-if="expense?.status === 'pending'"
                                @click="showRejectModal" 
                                class="btn btn-warning"
                                :disabled="loading"
                            >
                                <vue-feather type="x" size="16" class="me-2"></vue-feather>
                                Rejeitar
                            </button>
                            <hr v-if="expense?.status === 'pending'">
                            <button 
                                v-if="expense?.status === 'pending'"
                                @click="showDeleteModal" 
                                class="btn btn-outline-danger"
                                :disabled="loading"
                            >
                                <vue-feather type="trash-2" size="16" class="me-2"></vue-feather>
                                Excluir
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Registration Info -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Informações do Registro</h5>
                    </div>
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-sm-5">Registrado por:</dt>
                            <dd class="col-sm-7">{{ expense?.created_by_user?.name || '-' }}</dd>
                            
                            <dt class="col-sm-5">Data de Registro:</dt>
                            <dd class="col-sm-7">{{ formatDateTime(expense?.created_at) }}</dd>
                            
                            <dt v-if="expense?.updated_at !== expense?.created_at" class="col-sm-5">Última Atualização:</dt>
                            <dd v-if="expense?.updated_at !== expense?.created_at" class="col-sm-7">{{ formatDateTime(expense?.updated_at) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Approve Modal -->
        <div 
            class="modal fade" 
            id="approveModal" 
            tabindex="-1" 
            aria-labelledby="approveModalLabel" 
            aria-hidden="true"
        >
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="approveModalLabel">
                            <vue-feather type="check-circle" size="20" class="me-2 text-success"></vue-feather>
                            Aprovar Despesa
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form @submit.prevent="approveExpense">
                        <div class="modal-body">
                            <div class="alert alert-success" role="alert">
                                <vue-feather type="info" size="16" class="me-2"></vue-feather>
                                <strong>Confirmação de Aprovação</strong>
                            </div>
                            <p>Você tem certeza que deseja aprovar esta despesa?</p>
                            <div class="bg-light p-3 rounded">
                                <p class="mb-1"><strong>Título:</strong> {{ expense?.title }}</p>
                                <p class="mb-1"><strong>Valor:</strong> {{ formatMoney(expense?.amount) }}</p>
                                <p class="mb-1"><strong>Vencimento:</strong> {{ formatDate(expense?.due_date) }}</p>
                                <p class="mb-0"><strong>Fornecedor:</strong> {{ expense?.vendor_name || '-' }}</p>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                Cancelar
                            </button>
                            <button type="submit" class="btn btn-success" :disabled="loading">
                                <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                                <vue-feather v-else type="check" size="16" class="me-2"></vue-feather>
                                Aprovar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Pay Modal -->
        <div 
            class="modal fade" 
            id="payModal" 
            tabindex="-1" 
            aria-labelledby="payModalLabel" 
            aria-hidden="true"
        >
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="payModalLabel">
                            <vue-feather type="dollar-sign" size="20" class="me-2 text-primary"></vue-feather>
                            Marcar como Paga
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form @submit.prevent="payExpense">
                        <div class="modal-body">
                            <p>Confirme o pagamento desta despesa:</p>
                            <div class="bg-light p-3 rounded mb-3">
                                <p class="mb-1"><strong>Título:</strong> {{ expense?.title }}</p>
                                <p class="mb-1"><strong>Valor:</strong> {{ formatMoney(expense?.amount) }}</p>
                                <p class="mb-0"><strong>Fornecedor:</strong> {{ expense?.vendor_name || '-' }}</p>
                            </div>
                            
                            <div class="mb-3">
                                <label for="payment_date" class="form-label">Data do Pagamento *</label>
                                <input 
                                    id="payment_date"
                                    v-model="paymentDate" 
                                    type="date" 
                                    class="form-control" 
                                    required
                                    :max="today"
                                />
                            </div>
                            
                            <div class="mb-3">
                                <label for="payment_method" class="form-label">Método de Pagamento</label>
                                <select id="payment_method" v-model="paymentMethod" class="form-select">
                                    <option value="">Selecione...</option>
                                    <option value="cash">Dinheiro</option>
                                    <option value="transfer">Transferência</option>
                                    <option value="check">Cheque</option>
                                    <option value="card">Cartão</option>
                                    
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="payment_notes" class="form-label">Observações do Pagamento</label>
                                <textarea 
                                    id="payment_notes"
                                    v-model="paymentNotes" 
                                    class="form-control" 
                                    rows="3" 
                                    placeholder="Observações sobre o pagamento..."
                                ></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                Cancelar
                            </button>
                            <button type="submit" class="btn btn-primary" :disabled="loading">
                                <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                                <vue-feather v-else type="dollar-sign" size="16" class="me-2"></vue-feather>
                                Confirmar Pagamento
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Reject Modal -->
        <div 
            class="modal fade" 
            id="rejectModal" 
            tabindex="-1" 
            aria-labelledby="rejectModalLabel" 
            aria-hidden="true"
        >
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="rejectModalLabel">Rejeitar Despesa</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form @submit.prevent="rejectExpense">
                        <div class="modal-body">
                            <p>Você tem certeza que deseja rejeitar esta despesa?</p>
                            
                            <div class="mb-3">
                                <label for="reject-reason" class="form-label">Motivo da Rejeição *</label>
                                <textarea 
                                    id="reject-reason"
                                    v-model="rejectReason" 
                                    class="form-control" 
                                    rows="3" 
                                    required
                                    placeholder="Descreva o motivo da rejeição..."
                                ></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                Cancelar
                            </button>
                            <button type="submit" class="btn btn-warning" :disabled="loading">
                                <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                                Rejeitar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Delete Modal -->
        <div 
            class="modal fade" 
            id="deleteModal" 
            tabindex="-1" 
            aria-labelledby="deleteModalLabel" 
            aria-hidden="true"
        >
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteModalLabel">
                            <vue-feather type="alert-triangle" size="20" class="me-2 text-danger"></vue-feather>
                            Excluir Despesa
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form @submit.prevent="confirmDelete">
                        <div class="modal-body">
                            <div class="alert alert-danger" role="alert">
                                <vue-feather type="alert-triangle" size="16" class="me-2"></vue-feather>
                                <strong>Atenção! Esta ação não pode ser desfeita.</strong>
                            </div>
                            <p>Você tem certeza que deseja excluir esta despesa?</p>
                            <div class="bg-light p-3 rounded">
                                <p class="mb-1"><strong>Título:</strong> {{ expense?.title }}</p>
                                <p class="mb-1"><strong>Valor:</strong> {{ formatMoney(expense?.amount) }}</p>
                                <p class="mb-1"><strong>Vencimento:</strong> {{ formatDate(expense?.due_date) }}</p>
                                <p class="mb-0"><strong>Fornecedor:</strong> {{ expense?.vendor_name || '-' }}</p>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                Cancelar
                            </button>
                            <button type="submit" class="btn btn-danger" :disabled="loading">
                                <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                                <vue-feather v-else type="trash-2" size="16" class="me-2"></vue-feather>
                                Excluir
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import VueFeather from 'vue-feather';
import { useToast } from '@/composables/useToast';
import { useFormatting } from '@/composables/useFormatting';

const { showToast } = useToast();
const { formatMoney, formatDate, formatDateTime } = useFormatting();
const route = useRoute();
const router = useRouter();

// Reactive data
const expense = ref(null);
const loadingDiv = ref(false);
const loading = ref(false);

// Modal data
const rejectReason = ref('');
const paymentDate = ref(new Date().toISOString().split('T')[0]);
const paymentMethod = ref('');
const paymentNotes = ref('');

// Computed
const today = computed(() => new Date().toISOString().split('T')[0]);

// Methods
const getData = async () => {
    loadingDiv.value = true;
    try {
        const response = await axios.get(`/expenses/${route.params.id}`);
        if (response.data && response.data.expense) {
            expense.value = response.data.expense;
        } else {
            throw new Error('Dados da despesa não encontrados');
        }
    } catch (error) {
        console.error('Erro ao buscar despesa:', error);
        if (error.response?.status === 404) {
            showToast('Despesa não encontrada', 'error');
        } else {
            showToast('Erro ao carregar dados da despesa', 'error');
        }
        router.push('/admin/expenses');
    } finally {
        loadingDiv.value = false;
    }
};

const approveExpense = async () => {
    loading.value = true;
    try {
        await axios.post(`/expenses/${route.params.id}/approve`);
        showToast('Despesa aprovada com sucesso!', 'success');
        getData(); // Reload data
        
        const modal = bootstrap.Modal.getInstance(document.getElementById('approveModal'));
        modal.hide();
    } catch (error) {
        console.error(error);
        if (error.response?.data?.error) {
            showToast(error.response.data.error, 'error');
        } else {
            showToast('Erro ao aprovar despesa', 'error');
        }
    } finally {
        loading.value = false;
    }
};

const payExpense = async () => {
    loading.value = true;
    try {
        await axios.post(`/expenses/${route.params.id}/pay`, {
            payment_date: paymentDate.value,
            payment_method: paymentMethod.value,
            payment_notes: paymentNotes.value
        });
        
        showToast('Despesa marcada como paga com sucesso!', 'success');
        getData(); // Reload data
        
        const modal = bootstrap.Modal.getInstance(document.getElementById('payModal'));
        modal.hide();
    } catch (error) {
        console.error(error);
        if (error.response?.data?.errors) {
            Object.values(error.response.data.errors).forEach(errorArray => {
                errorArray.forEach(message => showToast(message, 'error'));
            });
        } else if (error.response?.data?.error) {
            showToast(error.response.data.error, 'error');
        } else {
            showToast('Erro ao marcar despesa como paga', 'error');
        }
    } finally {
        loading.value = false;
    }
};

const rejectExpense = async () => {
    loading.value = true;
    try {
        await axios.post(`/expenses/${route.params.id}/reject`, {
            rejection_reason: rejectReason.value
        });
        
        showToast('Despesa rejeitada com sucesso!', 'success');
        getData(); // Reload data
        
        const modal = bootstrap.Modal.getInstance(document.getElementById('rejectModal'));
        modal.hide();
    } catch (error) {
        console.error(error);
        if (error.response?.data?.errors) {
            Object.values(error.response.data.errors).forEach(errorArray => {
                errorArray.forEach(message => showToast(message, 'error'));
            });
        } else if (error.response?.data?.error) {
            showToast(error.response.data.error, 'error');
        } else {
            showToast('Erro ao rejeitar despesa', 'error');
        }
    } finally {
        loading.value = false;
    }
};

const confirmDelete = async () => {
    loading.value = true;
    try {
        await axios.delete(`/expenses/${route.params.id}`);
        showToast('Despesa excluída com sucesso!', 'success');
        router.push('/admin/expenses');
    } catch (error) {
        console.error(error);
        if (error.response?.data?.error) {
            showToast(error.response.data.error, 'error');
        } else {
            showToast('Erro ao excluir despesa', 'error');
        }
    } finally {
        loading.value = false;
    }
};

const showApproveModal = () => {
    const modal = new bootstrap.Modal(document.getElementById('approveModal'));
    modal.show();
};

const showPayModal = () => {
    paymentDate.value = new Date().toISOString().split('T')[0];
    paymentMethod.value = '';
    paymentNotes.value = '';
    const modal = new bootstrap.Modal(document.getElementById('payModal'));
    modal.show();
};

const showRejectModal = () => {
    rejectReason.value = '';
    const modal = new bootstrap.Modal(document.getElementById('rejectModal'));
    modal.show();
};

const showDeleteModal = () => {
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
};

// Utility methods for additional formatting
const formatDateLocal = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('pt-BR');
};

const formatDateTimeLocal = (datetime) => {
    if (!datetime) return '-';
    return new Date(datetime).toLocaleString('pt-BR');
};

const getDayOfWeek = (date) => {
    if (!date) return '';
    const days = ['Domingo', 'Segunda-feira', 'Terça-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'Sábado'];
    return days[new Date(date).getDay()];
};

const getStatusLabel = (status) => {
    const labels = {
        'pending': 'Pendente',
        'approved': 'Aprovada',
        'paid': 'Paga',
        'rejected': 'Rejeitada',
        'overdue': 'Vencida'
    };
    return labels[status] || status;
};

const getPriorityLabel = (priority) => {
    const labels = {
        'low': 'Baixa',
        'medium': 'Média',
        'high': 'Alta',
        'urgent': 'Urgente'
    };
    return labels[priority] || priority;
};

const getRecurrenceLabel = (type) => {
    const labels = {
        'monthly': 'Mensal',
        'quarterly': 'Trimestral',
        'annually': 'Anual'
    };
    return labels[type] || type;
};

const getPaymentMethodLabel = (method) => {
    const labels = {
        'cash': 'Dinheiro',
        'transfer': 'Transferência',
        'check': 'Cheque',
        'card': 'Cartão',
        
    };
    return labels[method] || method;
};

const getFileIcon = (filename) => {
    if (!filename || typeof filename !== 'string') {
        return 'file';
    }
    const extension = filename.split('.').pop().toLowerCase();
    const icons = {
        pdf: 'file-text',
        doc: 'file-text',
        docx: 'file-text',
        xls: 'file-text',
        xlsx: 'file-text',
        jpg: 'image',
        jpeg: 'image',
        png: 'image',
        gif: 'image'
    };
    return icons[extension] || 'file';
};

const formatFileSize = (bytes) => {
    if (!bytes || bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

// Lifecycle
onMounted(() => {
    getData();
});
</script>

<style scoped>
.badge.fs-6 {
    font-size: 1rem !important;
}

dl.row dt {
    font-weight: 600;
    color: #6c757d;
}

.bg-light {
    background-color: #f8f9fa !important;
}

.card-header h5 {
    display: flex;
    align-items: center;
}
</style>