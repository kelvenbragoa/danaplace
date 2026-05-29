<template>
  <div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
      <div class="flex items-center gap-4 mb-4">
        <router-link
          :to="{ name: 'admin.expenses.index' }"
          class="p-2 hover:bg-gray-100 rounded-lg transition-colors"
        >
          <vue-feather type="arrow-left" class="w-5 h-5 text-gray-600" />
        </router-link>
        <div class="flex-1">
          <div class="flex items-center gap-3 mb-2">
            <h1 class="text-3xl font-bold text-gray-900">{{ expense?.title || 'Carregando...' }}</h1>
            <span
              v-if="expense"
              class="px-3 py-1 text-sm font-medium rounded-full"
              :class="getStatusClass(expense.status)"
            >
              {{ getStatusLabel(expense.status) }}
            </span>
          </div>
          <div v-if="expense" class="flex items-center gap-4 text-sm text-gray-600">
            <span>ID: #{{ expense.id }}</span>
            <span>•</span>
            <span>Criado em {{ formatDate(expense.created_at) }}</span>
            <span v-if="expense.is_overdue" class="text-red-600 font-medium">• Vencida</span>
          </div>
        </div>

        <!-- Action Buttons -->
        <div v-if="expense" class="flex gap-2">
          <router-link
            v-if="canEdit"
            :to="{ name: 'admin.expenses.edit', params: { id: expense.id } }"
            class="px-4 py-2 text-blue-600 hover:text-blue-700 border border-blue-600 rounded-lg hover:bg-blue-50 transition-colors flex items-center gap-2"
          >
            <PencilIcon class="w-4 h-4" />
            Editar
          </router-link>

          <button
            v-if="canApprove"
            @click="approveExpense"
            :disabled="actionLoading"
            class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors disabled:opacity-50 flex items-center gap-2"
          >
            <CheckCircleIcon class="w-4 h-4" />
            Aprovar
          </button>

          <button
            v-if="canPay"
            @click="openPaymentModal"
            :disabled="actionLoading"
            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors disabled:opacity-50 flex items-center gap-2"
          >
            <CreditCardIcon class="w-4 h-4" />
            Marcar como Paga
          </button>

          <button
            v-if="canReject"
            @click="openRejectModal"
            :disabled="actionLoading"
            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors disabled:opacity-50 flex items-center gap-2"
          >
            <XCircleIcon class="w-4 h-4" />
            Rejeitar
          </button>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-12">
      <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
      <p class="mt-2 text-gray-600">Carregando detalhes da despesa...</p>
    </div>

    <!-- Content -->
    <div v-else-if="expense" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Main Content -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Basic Information -->
        <div class="bg-white rounded-lg shadow-sm border p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Informações Básicas</h3>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-500 mb-1">Categoria</label>
              <div class="flex items-center gap-2">
                <div
                  class="w-4 h-4 rounded-full"
                  :style="{ backgroundColor: expense.expense_category?.color }"
                ></div>
                <span class="font-medium text-gray-900">{{ expense.expense_category?.name }}</span>
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-500 mb-1">Prioridade</label>
              <span
                class="inline-flex px-2 py-1 text-sm font-medium rounded-full"
                :class="getPriorityClass(expense.priority)"
              >
                {{ getPriorityLabel(expense.priority) }}
              </span>
            </div>
          </div>

          <div class="mt-6">
            <label class="block text-sm font-medium text-gray-500 mb-1">Descrição</label>
            <p class="text-gray-900">{{ expense.description || 'Nenhuma descrição fornecida' }}</p>
          </div>
        </div>

        <!-- Financial Information -->
        <div class="bg-white rounded-lg shadow-sm border p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Informações Financeiras</h3>
          
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-500 mb-1">Valor</label>
              <p class="text-2xl font-bold text-gray-900">{{ formatMoney(expense.amount) }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-500 mb-1">Data da Despesa</label>
              <p class="font-medium text-gray-900">{{ formatDate(expense.expense_date) }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-500 mb-1">Data de Vencimento</label>
              <p class="font-medium" :class="expense.is_overdue ? 'text-red-600' : 'text-gray-900'">
                {{ expense.due_date ? formatDate(expense.due_date) : 'Não definida' }}
              </p>
            </div>
          </div>

          <div v-if="expense.payment_date || expense.payment_method" class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6 pt-6 border-t border-gray-200">
            <div v-if="expense.payment_date">
              <label class="block text-sm font-medium text-gray-500 mb-1">Data de Pagamento</label>
              <p class="font-medium text-gray-900">{{ formatDate(expense.payment_date) }}</p>
            </div>

            <div v-if="expense.payment_method">
              <label class="block text-sm font-medium text-gray-500 mb-1">Método de Pagamento</label>
              <p class="font-medium text-gray-900">{{ getPaymentMethodLabel(expense.payment_method) }}</p>
            </div>
          </div>
        </div>

        <!-- Vendor Information -->
        <div v-if="expense.vendor_name || expense.vendor_contact || expense.invoice_number" class="bg-white rounded-lg shadow-sm border p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Informações do Fornecedor</h3>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div v-if="expense.vendor_name">
              <label class="block text-sm font-medium text-gray-500 mb-1">Nome do Fornecedor</label>
              <p class="font-medium text-gray-900">{{ expense.vendor_name }}</p>
            </div>

            <div v-if="expense.vendor_contact">
              <label class="block text-sm font-medium text-gray-500 mb-1">Contato</label>
              <p class="font-medium text-gray-900">{{ expense.vendor_contact }}</p>
            </div>

            <div v-if="expense.invoice_number">
              <label class="block text-sm font-medium text-gray-500 mb-1">Número da Fatura</label>
              <p class="font-medium text-gray-900">{{ expense.invoice_number }}</p>
            </div>

            <div v-if="expense.reference_number">
              <label class="block text-sm font-medium text-gray-500 mb-1">Número de Referência</label>
              <p class="font-medium text-gray-900">{{ expense.reference_number }}</p>
            </div>
          </div>
        </div>

        <!-- Recurring Information -->
        <div v-if="expense.recurring" class="bg-blue-50 rounded-lg border p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
            <ArrowPathIcon class="w-5 h-5 text-blue-600" />
            Despesa Recorrente
          </h3>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-500 mb-1">Frequência</label>
              <p class="font-medium text-gray-900">{{ getRecurringFrequencyLabel(expense.recurring_frequency) }}</p>
            </div>

            <div v-if="expense.recurring_until">
              <label class="block text-sm font-medium text-gray-500 mb-1">Recorrer Até</label>
              <p class="font-medium text-gray-900">{{ formatDate(expense.recurring_until) }}</p>
            </div>
          </div>
        </div>

        <!-- Notes -->
        <div v-if="expense.notes" class="bg-white rounded-lg shadow-sm border p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Observações</h3>
          <p class="text-gray-700 whitespace-pre-line">{{ expense.notes }}</p>
        </div>

        <!-- Attachments -->
        <div v-if="expense.attachment_details && expense.attachment_details.length > 0" class="bg-white rounded-lg shadow-sm border p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Anexos</h3>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div
              v-for="attachment in expense.attachment_details"
              :key="attachment.path"
              class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:bg-gray-50"
            >
              <div class="flex items-center gap-3">
                <DocumentIcon class="w-5 h-5 text-gray-500" />
                <div>
                  <p class="font-medium text-gray-900">{{ attachment.name }}</p>
                  <p class="text-sm text-gray-500">{{ formatFileSize(attachment.size) }}</p>
                </div>
              </div>
              
              <div class="flex gap-2">
                <a
                  :href="attachment.url"
                  target="_blank"
                  class="p-2 text-blue-600 hover:text-blue-800 hover:bg-blue-100 rounded transition-colors"
                  title="Visualizar"
                >
                  <EyeIcon class="w-4 h-4" />
                </a>
                
                <a
                  :href="attachment.url"
                  :download="attachment.name"
                  class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded transition-colors"
                  title="Download"
                >
                  <ArrowDownTrayIcon class="w-4 h-4" />
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Sidebar -->
      <div class="space-y-6">
        <!-- Status Timeline -->
        <div class="bg-white rounded-lg shadow-sm border p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Status da Despesa</h3>
          
          <div class="space-y-4">
            <!-- Created -->
            <div class="flex items-start gap-3">
              <div class="w-3 h-3 bg-blue-600 rounded-full mt-2"></div>
              <div class="flex-1">
                <p class="font-medium text-gray-900">Despesa Criada</p>
                <p class="text-sm text-gray-500">{{ formatDateTime(expense.created_at) }}</p>
                <p v-if="expense.created_by" class="text-sm text-gray-500">
                  por {{ expense.created_by.firstName }} {{ expense.created_by.lastName }}
                </p>
              </div>
            </div>

            <!-- Approved -->
            <div v-if="expense.approved_at" class="flex items-start gap-3">
              <div class="w-3 h-3 bg-green-600 rounded-full mt-2"></div>
              <div class="flex-1">
                <p class="font-medium text-gray-900">
                  {{ expense.status === 'approved' ? 'Aprovada' : 'Rejeitada' }}
                </p>
                <p class="text-sm text-gray-500">{{ formatDateTime(expense.approved_at) }}</p>
                <p v-if="expense.approved_by" class="text-sm text-gray-500">
                  por {{ expense.approved_by.firstName }} {{ expense.approved_by.lastName }}
                </p>
              </div>
            </div>

            <!-- Paid -->
            <div v-if="expense.status === 'paid'" class="flex items-start gap-3">
              <div class="w-3 h-3 bg-blue-600 rounded-full mt-2"></div>
              <div class="flex-1">
                <p class="font-medium text-gray-900">Paga</p>
                <p v-if="expense.payment_date" class="text-sm text-gray-500">{{ formatDateTime(expense.payment_date) }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Quick Stats -->
        <div class="bg-white rounded-lg shadow-sm border p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Informações Rápidas</h3>
          
          <div class="space-y-3">
            <div class="flex justify-between">
              <span class="text-gray-600">Status:</span>
              <span
                class="px-2 py-1 text-xs font-medium rounded-full"
                :class="getStatusClass(expense.status)"
              >
                {{ getStatusLabel(expense.status) }}
              </span>
            </div>

            <div class="flex justify-between">
              <span class="text-gray-600">Prioridade:</span>
              <span
                class="px-2 py-1 text-xs font-medium rounded-full"
                :class="getPriorityClass(expense.priority)"
              >
                {{ getPriorityLabel(expense.priority) }}
              </span>
            </div>

            <div v-if="expense.recurring" class="flex justify-between">
              <span class="text-gray-600">Recorrente:</span>
              <span class="text-green-600 font-medium">Sim</span>
            </div>

            <div v-if="expense.is_overdue" class="flex justify-between">
              <span class="text-gray-600">Situação:</span>
              <span class="text-red-600 font-medium">Vencida</span>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="bg-gray-50 rounded-lg p-4">
          <h4 class="font-semibold text-gray-900 mb-3">Ações Rápidas</h4>
          <div class="space-y-2">
            <router-link
              v-if="canEdit"
              :to="{ name: 'admin.expenses.edit', params: { id: expense.id } }"
              class="w-full px-4 py-2 text-blue-600 hover:text-blue-700 border border-blue-600 rounded-lg hover:bg-blue-50 transition-colors flex items-center justify-center gap-2"
            >
              <PencilIcon class="w-4 h-4" />
              Editar Despesa
            </router-link>
            
            <button
              @click="window.print()"
              class="w-full px-4 py-2 text-gray-600 hover:text-gray-800 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors flex items-center justify-center gap-2"
            >
              <PrinterIcon class="w-4 h-4" />
              Imprimir
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Payment Modal -->
    <PaymentModal
      v-if="showPaymentModal"
      :expense="expense"
      @close="closePaymentModal"
      @paid="handleExpensePaid"
    />

    <!-- Reject Modal -->
    <RejectModal
      v-if="showRejectModal"
      :expense="expense"
      @close="closeRejectModal"
      @rejected="handleExpenseRejected"
    />
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useToast } from '@/composables/useToast'
import { useFormatting } from '@/composables/useFormatting'
import PaymentModal from '@/components/admin/expenses/PaymentModal.vue'
import RejectModal from '@/components/admin/expenses/RejectModal.vue'

export default {
  name: 'ShowExpense',
  components: {
    PaymentModal,
    RejectModal
  },

  setup() {
    const route = useRoute()
    const { showToast } = useToast()
    const { formatMoney, formatDate, formatDateTime } = useFormatting()

    // State
    const loading = ref(true)
    const actionLoading = ref(false)
    const expense = ref(null)
    const showPaymentModal = ref(false)
    const showRejectModal = ref(false)

    // Computed
    const canEdit = computed(() => expense.value?.can_edit)
    const canApprove = computed(() => expense.value?.can_approve)
    const canPay = computed(() => expense.value?.can_pay)
    const canReject = computed(() => expense.value?.status === 'pending')

    // Methods
    const loadExpense = async () => {
      try {
        loading.value = true
        
        const response = await fetch(`/expenses/${route.params.id}`)
        
        if (!response.ok) throw new Error('Erro ao carregar despesa')
        
        const data = await response.json()
        expense.value = data.expense
      } catch (error) {
        console.error('Erro ao carregar despesa:', error)
        showToast('Erro ao carregar despesa', 'error')
      } finally {
        loading.value = false
      }
    }

    const approveExpense = async () => {
      try {
        actionLoading.value = true
        
        const response = await fetch(`/expenses/${expense.value.id}/approve`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          }
        })

        if (!response.ok) throw new Error('Erro ao aprovar despesa')

        const result = await response.json()
        showToast(result.message)
        loadExpense()
      } catch (error) {
        console.error('Erro ao aprovar despesa:', error)
        showToast('Erro ao aprovar despesa', 'error')
      } finally {
        actionLoading.value = false
      }
    }

    const openPaymentModal = () => {
      showPaymentModal.value = true
    }

    const closePaymentModal = () => {
      showPaymentModal.value = false
    }

    const handleExpensePaid = () => {
      closePaymentModal()
      loadExpense()
    }

    const openRejectModal = () => {
      showRejectModal.value = true
    }

    const closeRejectModal = () => {
      showRejectModal.value = false
    }

    const handleExpenseRejected = () => {
      closeRejectModal()
      loadExpense()
    }

    const getStatusClass = (status) => {
      const classes = {
        pending: 'bg-yellow-100 text-yellow-800',
        approved: 'bg-green-100 text-green-800',
        paid: 'bg-blue-100 text-blue-800',
        rejected: 'bg-red-100 text-red-800',
        overdue: 'bg-red-100 text-red-800'
      }
      return classes[status] || 'bg-gray-100 text-gray-800'
    }

    const getStatusLabel = (status) => {
      const labels = {
        pending: 'Pendente',
        approved: 'Aprovada',
        paid: 'Paga',
        rejected: 'Rejeitada',
        overdue: 'Vencida'
      }
      return labels[status] || status
    }

    const getPriorityClass = (priority) => {
      const classes = {
        low: 'bg-green-100 text-green-800',
        medium: 'bg-yellow-100 text-yellow-800',
        high: 'bg-orange-100 text-orange-800',
        urgent: 'bg-red-100 text-red-800'
      }
      return classes[priority] || 'bg-gray-100 text-gray-800'
    }

    const getPriorityLabel = (priority) => {
      const labels = {
        low: 'Baixa',
        medium: 'Média',
        high: 'Alta',
        urgent: 'Urgente'
      }
      return labels[priority] || priority
    }

    const getPaymentMethodLabel = (method) => {
      const labels = {
        cash: 'Dinheiro',
        bank_transfer: 'Transferência Bancária',
        check: 'Cheque',
        card: 'Cartão',
        other: 'Outro'
      }
      return labels[method] || method
    }

    const getRecurringFrequencyLabel = (frequency) => {
      const labels = {
        monthly: 'Mensal',
        quarterly: 'Trimestral',
        semi_annual: 'Semestral',
        annual: 'Anual'
      }
      return labels[frequency] || frequency
    }

    const formatFileSize = (bytes) => {
      if (bytes === 0) return '0 Bytes'
      const k = 1024
      const sizes = ['Bytes', 'KB', 'MB', 'GB']
      const i = Math.floor(Math.log(bytes) / Math.log(k))
      return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
    }

    // Lifecycle
    onMounted(() => {
      loadExpense()
    })

    return {
      // State
      loading,
      actionLoading,
      expense,
      showPaymentModal,
      showRejectModal,

      // Computed
      canEdit,
      canApprove,
      canPay,
      canReject,

      // Methods
      approveExpense,
      openPaymentModal,
      closePaymentModal,
      handleExpensePaid,
      openRejectModal,
      closeRejectModal,
      handleExpenseRejected,
      getStatusClass,
      getStatusLabel,
      getPriorityClass,
      getPriorityLabel,
      getPaymentMethodLabel,
      getRecurringFrequencyLabel,
      formatFileSize,
      formatMoney,
      formatDate,
      formatDateTime
    }
  }
}
</script>

<style>
@media print {
  .print\:hidden {
    display: none !important;
  }
}
</style>