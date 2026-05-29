<template>
    <div>
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">Gestão de Despesas</h1>
                <p class="text-muted">Gerencie todas as despesas do condomínio</p>
            </div>
            <router-link 
                to="/admin/expenses/create" 
                class="btn btn-primary"
            >
                <vue-feather type="plus" size="16" class="me-2"></vue-feather>
                Nova Despesa
            </router-link>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-primary h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title text-primary mb-1">Total de Despesas</h6>
                                <h3 class="card-text text-primary mb-0">{{ statistics.total_expenses || 0 }}</h3>
                            </div>
                            <div class="align-self-center">
                                <vue-feather type="credit-card" size="32" class="text-primary"></vue-feather>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-warning h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title text-warning mb-1">Valor Total</h6>
                                <h3 class="card-text text-warning mb-0">{{ formatMoney(statistics.total_amount || 0) }}</h3>
                            </div>
                            <div class="align-self-center">
                                <vue-feather type="dollar-sign" size="32" class="text-warning"></vue-feather>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-info h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title text-info mb-1">Pendentes</h6>
                                <h3 class="card-text text-info mb-0">{{ statistics.pending_count || 0 }}</h3>
                            </div>
                            <div class="align-self-center">
                                <vue-feather type="clock" size="32" class="text-info"></vue-feather>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-success h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title text-success mb-1">Pagas</h6>
                                <h3 class="card-text text-success mb-0">{{ statistics.paid_count || 0 }}</h3>
                            </div>
                            <div class="align-self-center">
                                <vue-feather type="check-circle" size="32" class="text-success"></vue-feather>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Filtros</h5>
            </div>
            <div class="card-body">
                <form @submit.prevent="handleSearch">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Buscar</label>
                            <input 
                                v-model="searchQuery" 
                                type="text" 
                                class="form-control"
                                placeholder="Título, fornecedor, número..."
                            />
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Categoria</label>
                            <select v-model="categoryFilter" class="form-select">
                                <option value="">Todas</option>
                                <option 
                                    v-for="category in availableCategories" 
                                    :key="category.id" 
                                    :value="category.id"
                                >
                                    {{ category.name }}
                                </option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Status</label>
                            <select v-model="statusFilter" class="form-select">
                                <option value="">Todos</option>
                                <option value="pending">Pendente</option>
                                <option value="approved">Aprovada</option>
                                <option value="paid">Paga</option>
                                <option value="rejected">Rejeitada</option>
                                <option value="overdue">Vencida</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Prioridade</label>
                            <select v-model="priorityFilter" class="form-select">
                                <option value="">Todas</option>
                                <option value="low">Baixa</option>
                                <option value="medium">Média</option>
                                <option value="high">Alta</option>
                                <option value="urgent">Urgente</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Data Inicial</label>
                            <input 
                                v-model="dateFromFilter" 
                                type="date" 
                                class="form-control"
                            />
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Data Final</label>
                            <input 
                                v-model="dateToFilter" 
                                type="date" 
                                class="form-control"
                            />
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">Mês</label>
                            <select v-model="monthFilter" class="form-select">
                                <option value="">-</option>
                                <option value="1">Jan</option>
                                <option value="2">Fev</option>
                                <option value="3">Mar</option>
                                <option value="4">Abr</option>
                                <option value="5">Mai</option>
                                <option value="6">Jun</option>
                                <option value="7">Jul</option>
                                <option value="8">Ago</option>
                                <option value="9">Set</option>
                                <option value="10">Out</option>
                                <option value="11">Nov</option>
                                <option value="12">Dez</option>
                            </select>
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">Ano</label>
                            <select v-model="yearFilter" class="form-select">
                                <option value="">-</option>
                                <option value="2024">2024</option>
                                <option value="2025">2025</option>
                                <option value="2026">2026</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-outline-primary me-2">
                                <vue-feather type="search" size="16" class="me-1"></vue-feather>
                                Buscar
                            </button>
                            <button type="button" @click="clearFilters" class="btn btn-outline-secondary">
                                <vue-feather type="x" size="16" class="me-1"></vue-feather>
                                Limpar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Expenses Table -->
        <div class="card">
            <div class="card-body">
                <div v-if="loadingDiv" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Carregando...</span>
                    </div>
                </div>

                <div v-else>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Despesa</th>
                                    <th>Categoria</th>
                                    <th>Valor</th>
                                    <th>Data Vencimento</th>
                                    <th>Status</th>
                                    <th>Prioridade</th>
                                    <th>Criado por</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="expense in expenses.data" :key="expense.id">
                                    <td>
                                        <div class="fw-medium">{{ expense.title }}</div>
                                        <div v-if="expense.description" class="text-muted small">
                                            {{ expense.description.substring(0, 50) }}{{ expense.description.length > 50 ? '...' : '' }}
                                        </div>
                                        <div v-if="expense.vendor_name" class="text-muted small">
                                            <vue-feather type="building" size="12" class="me-1"></vue-feather>
                                            {{ expense.vendor_name }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div 
                                                class="rounded-circle me-2" 
                                                style="width: 12px; height: 12px;"
                                                :style="{ backgroundColor: expense.expense_category?.color || '#6c757d' }"
                                            ></div>
                                            <span>{{ expense.expense_category?.name || '-' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-bold">{{ formatMoney(expense.amount) }}</div>
                                        <div v-if="expense.invoice_number" class="text-muted small">
                                            Fatura: {{ expense.invoice_number }}
                                        </div>
                                    </td>
                                    <td>{{ formatDate(expense.due_date) }}</td>
                                    <td>
                                        <span 
                                            class="badge"
                                            :class="{
                                                'bg-warning': expense.status === 'pending',
                                                'bg-info': expense.status === 'approved',
                                                'bg-success': expense.status === 'paid',
                                                'bg-danger': expense.status === 'rejected',
                                                'bg-dark': expense.status === 'overdue'
                                            }"
                                        >
                                            {{ getStatusLabel(expense.status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span 
                                            class="badge"
                                            :class="{
                                                'bg-light text-dark': expense.priority === 'low',
                                                'bg-secondary': expense.priority === 'medium',
                                                'bg-warning': expense.priority === 'high',
                                                'bg-danger': expense.priority === 'urgent'
                                            }"
                                        >
                                            {{ getPriorityLabel(expense.priority) }}
                                        </span>
                                    </td>
                                    <td>{{ expense.created_by_user?.name || '-' }}</td>
                                    <td>
                                        <div class="dropdown position-relative">
                                            <button 
                                                class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                                type="button"
                                                @click="toggleDropdown(expense.id, $event)"
                                                :aria-expanded="openDropdown === expense.id"
                                            >
                                                <vue-feather type="more-horizontal" size="16"></vue-feather>
                                            </button>
                                            <ul 
                                                class="dropdown-menu"
                                                :class="{ 'show': openDropdown === expense.id }"
                                            >
                                                <li>
                                                    <router-link 
                                                        :to="{ name: 'admin.expenses.show', params: { id: expense.id } }"
                                                        class="dropdown-item"
                                                    >
                                                        <vue-feather type="eye" size="16" class="me-2"></vue-feather>
                                                        Visualizar
                                                    </router-link>
                                                </li>
                                                <li v-if="expense.status === 'pending'">
                                                    <router-link 
                                                        :to="{ name: 'admin.expenses.edit', params: { id: expense.id } }"
                                                        class="dropdown-item"
                                                    >
                                                        <vue-feather type="edit-2" size="16" class="me-2"></vue-feather>
                                                        Editar
                                                    </router-link>
                                                </li>
                                                <li v-if="expense.status === 'pending'">
                                                    <hr class="dropdown-divider">
                                                </li>
                                                <li v-if="expense.status === 'pending'">
                                                    <button 
                                                        @click="showApproveModal(expense)" 
                                                        class="dropdown-item text-success"
                                                    >
                                                        <vue-feather type="check" size="16" class="me-2"></vue-feather>
                                                        Aprovar
                                                    </button>
                                                </li>
                                                <li v-if="expense.status === 'approved'">
                                                    <button 
                                                        @click="showPayModal(expense)" 
                                                        class="dropdown-item text-primary"
                                                    >
                                                        <vue-feather type="dollar-sign" size="16" class="me-2"></vue-feather>
                                                        Marcar como Paga
                                                    </button>
                                                </li>
                                                <li v-if="expense.status === 'pending'">
                                                    <button 
                                                        @click="showRejectModal(expense)" 
                                                        class="dropdown-item text-warning"
                                                    >
                                                        <vue-feather type="x" size="16" class="me-2"></vue-feather>
                                                        Rejeitar
                                                    </button>
                                                </li>
                                                <li v-if="expense.status === 'pending'">
                                                    <hr class="dropdown-divider">
                                                </li>
                                                <li v-if="expense.status === 'pending'">
                                                    <button 
                                                        @click="showDeleteModal(expense)" 
                                                        class="dropdown-item text-danger"
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

                        <div v-if="expenses.data && expenses.data.length === 0" class="text-center py-4">
                            <vue-feather type="dollar-sign" size="48" class="text-muted mb-3"></vue-feather>
                            <p class="text-muted">Nenhuma despesa encontrada</p>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <nav v-if="expenses.last_page > 1" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <li class="page-item" :class="{ disabled: expenses.current_page === 1 }">
                                <button class="page-link" @click="changePage(expenses.current_page - 1)">
                                    Anterior
                                </button>
                            </li>
                            
                            <li 
                                v-for="page in visiblePages" 
                                :key="page" 
                                class="page-item" 
                                :class="{ active: page === expenses.current_page }"
                            >
                                <button class="page-link" @click="changePage(page)">
                                    {{ page }}
                                </button>
                            </li>
                            
                            <li class="page-item" :class="{ disabled: expenses.current_page === expenses.last_page }">
                                <button class="page-link" @click="changePage(expenses.current_page + 1)">
                                    Próximo
                                </button>
                            </li>
                        </ul>
                    </nav>
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
                            <div class="bg-light p-3 rounded" v-if="selectedExpense">
                                <p class="mb-1"><strong>Título:</strong> {{ selectedExpense.title }}</p>
                                <p class="mb-1"><strong>Valor:</strong> {{ formatMoney(selectedExpense.amount) }}</p>
                                <p class="mb-1"><strong>Vencimento:</strong> {{ formatDate(selectedExpense.due_date) }}</p>
                                <p class="mb-0"><strong>Fornecedor:</strong> {{ selectedExpense.vendor_name || '-' }}</p>
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
                            <div class="bg-light p-3 rounded mb-3" v-if="selectedExpense">
                                <p class="mb-1"><strong>Título:</strong> {{ selectedExpense.title }}</p>
                                <p class="mb-1"><strong>Valor:</strong> {{ formatMoney(selectedExpense.amount) }}</p>
                                <p class="mb-0"><strong>Fornecedor:</strong> {{ selectedExpense.vendor_name || '-' }}</p>
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
                                    <option value="pix">PIX</option>
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
                            <p><strong>Título:</strong> {{ selectedExpense?.title }}</p>
                            <p><strong>Valor:</strong> {{ formatMoney(selectedExpense?.amount || 0) }}</p>
                            
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
                            <div class="bg-light p-3 rounded" v-if="selectedExpense">
                                <p class="mb-1"><strong>Título:</strong> {{ selectedExpense.title }}</p>
                                <p class="mb-1"><strong>Valor:</strong> {{ formatMoney(selectedExpense.amount) }}</p>
                                <p class="mb-1"><strong>Vencimento:</strong> {{ formatDate(selectedExpense.due_date) }}</p>
                                <p class="mb-0"><strong>Fornecedor:</strong> {{ selectedExpense.vendor_name || '-' }}</p>
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
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import VueFeather from 'vue-feather';
import { useToast } from '@/composables/useToast';
import { useFormatting } from '@/composables/useFormatting';

const { showToast } = useToast();
const { formatMoney, formatDate } = useFormatting();
const router = useRouter();

// Reactive data
const expenses = ref({ data: [], current_page: 1, last_page: 1 });
const statistics = ref({});
const availableCategories = ref([]);
const loadingDiv = ref(false);
const loading = ref(false);

// Filtros
const searchQuery = ref('');
const categoryFilter = ref('');
const statusFilter = ref('');
const priorityFilter = ref('');
const dateFromFilter = ref('');
const dateToFilter = ref('');
const monthFilter = ref('');
const yearFilter = ref('');

// Dropdown management
const openDropdown = ref(null);

// Modal data
const selectedExpense = ref(null);
const rejectReason = ref('');
const paymentDate = ref(new Date().toISOString().split('T')[0]);
const paymentMethod = ref('');
const paymentNotes = ref('');

// Computed
const today = computed(() => new Date().toISOString().split('T')[0]);

const visiblePages = computed(() => {
    const current = expenses.value.current_page;
    const last = expenses.value.last_page;
    const pages = [];
    
    for (let i = Math.max(1, current - 2); i <= Math.min(last, current + 2); i++) {
        pages.push(i);
    }
    
    return pages;
});

// Methods
const getData = async (page = 1) => {
    loadingDiv.value = true;
    try {
        const params = {
            page,
            query: searchQuery.value,
            category_id: categoryFilter.value,
            status: statusFilter.value,
            priority: priorityFilter.value,
            date_from: dateFromFilter.value,
            date_to: dateToFilter.value,
            month: monthFilter.value,
            year: yearFilter.value
        };

        const response = await axios.get('/admin/expenses', { params });
        expenses.value = response.data.expenses;
        statistics.value = response.data.statistics;
    } catch (error) {
        showToast('Erro ao carregar despesas', 'error');
        console.error(error);
    } finally {
        loadingDiv.value = false;
    }
};

const getCategories = async () => {
    try {
        const response = await axios.get('/admin/expense-categories');
        availableCategories.value = response.data.data;
    } catch (error) {
        console.error('Erro ao buscar categorias:', error);
    }
};

const handleSearch = () => {
    getData(1);
};

const clearFilters = () => {
    searchQuery.value = '';
    categoryFilter.value = '';
    statusFilter.value = '';
    priorityFilter.value = '';
    dateFromFilter.value = '';
    dateToFilter.value = '';
    monthFilter.value = '';
    yearFilter.value = '';
    getData(1);
};

const changePage = (page) => {
    if (page >= 1 && page <= expenses.value.last_page) {
        getData(page);
    }
};

// Dropdown functionality
const toggleDropdown = (expenseId, event) => {
    event.preventDefault();
    event.stopPropagation();
    
    if (openDropdown.value === expenseId) {
        openDropdown.value = null;
    } else {
        openDropdown.value = expenseId;
    }
};

const closeDropdown = () => {
    openDropdown.value = null;
};

const handleOutsideClick = (event) => {
    if (!event.target.closest('.dropdown')) {
        closeDropdown();
    }
};

// Actions
const approveExpense = async () => {
    if (!selectedExpense.value) return;
    
    loading.value = true;
    try {
        await axios.post(`/admin/expenses/${selectedExpense.value.id}/approve`);
        showToast('Despesa aprovada com sucesso!', 'success');
        getData(expenses.value.current_page);
        
        const modal = bootstrap.Modal.getInstance(document.getElementById('approveModal'));
        modal.hide();
    } catch (error) {
        console.error(error);
        showToast('Erro ao aprovar despesa', 'error');
    } finally {
        loading.value = false;
    }
};

const payExpense = async () => {
    if (!selectedExpense.value) return;
    
    loading.value = true;
    try {
        await axios.post(`/admin/expenses/${selectedExpense.value.id}/pay`, {
            payment_date: paymentDate.value,
            payment_method: paymentMethod.value,
            payment_notes: paymentNotes.value
        });
        
        showToast('Despesa marcada como paga com sucesso!', 'success');
        getData(expenses.value.current_page);
        
        const modal = bootstrap.Modal.getInstance(document.getElementById('payModal'));
        modal.hide();
    } catch (error) {
        console.error(error);
        showToast('Erro ao marcar despesa como paga', 'error');
    } finally {
        loading.value = false;
    }
};

const rejectExpense = async () => {
    if (!selectedExpense.value) return;
    
    loading.value = true;
    try {
        await axios.post(`/admin/expenses/${selectedExpense.value.id}/reject`, {
            rejection_reason: rejectReason.value
        });
        
        showToast('Despesa rejeitada com sucesso!', 'success');
        getData(expenses.value.current_page);
        
        const modal = bootstrap.Modal.getInstance(document.getElementById('rejectModal'));
        modal.hide();
    } catch (error) {
        console.error(error);
        showToast('Erro ao rejeitar despesa', 'error');
    } finally {
        loading.value = false;
    }
};

const confirmDelete = async () => {
    if (!selectedExpense.value) return;
    
    loading.value = true;
    try {
        await axios.delete(`/admin/expenses/${selectedExpense.value.id}`);
        showToast('Despesa excluída com sucesso!', 'success');
        getData(expenses.value.current_page);
        
        const modal = bootstrap.Modal.getInstance(document.getElementById('deleteModal'));
        modal.hide();
    } catch (error) {
        console.error(error);
        showToast('Erro ao excluir despesa', 'error');
    } finally {
        loading.value = false;
    }
};

const showApproveModal = (expense) => {
    selectedExpense.value = expense;
    closeDropdown();
    const modal = new bootstrap.Modal(document.getElementById('approveModal'));
    modal.show();
};

const showPayModal = (expense) => {
    selectedExpense.value = expense;
    paymentDate.value = new Date().toISOString().split('T')[0];
    paymentMethod.value = '';
    paymentNotes.value = '';
    closeDropdown();
    const modal = new bootstrap.Modal(document.getElementById('payModal'));
    modal.show();
};

const showRejectModal = (expense) => {
    selectedExpense.value = expense;
    rejectReason.value = '';
    closeDropdown();
    const modal = new bootstrap.Modal(document.getElementById('rejectModal'));
    modal.show();
};

const showDeleteModal = (expense) => {
    selectedExpense.value = expense;
    closeDropdown();
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
};

// Utility methods
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

// Lifecycle
onMounted(() => {
    getData();
    getCategories();
    document.addEventListener('click', handleOutsideClick);
});

onUnmounted(() => {
    document.removeEventListener('click', handleOutsideClick);
});
</script>

<style scoped>
.table th, .table td {
    vertical-align: middle;
}

.dropdown-menu {
    min-width: 200px;
}

.badge {
    font-size: 0.75rem;
}
</style>