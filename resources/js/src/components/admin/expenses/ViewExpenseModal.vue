<template>
  <div v-if="show" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl max-h-[90vh] overflow-hidden">
      <!-- Header -->
      <div class="flex items-center justify-between p-6 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">Detalhes da Despesa</h3>
        <button
          @click="$emit('close')"
          class="text-gray-400 hover:text-gray-600 transition-colors"
        >
          <vue-feather type="x" size="24" />
        </button>
      </div>

      <!-- Content -->
      <div v-if="expense" class="p-6 overflow-y-auto max-h-[calc(90vh-140px)]">
        <!-- Basic Info -->
        <div class="mb-6">
          <h4 class="text-lg font-semibold text-gray-900 mb-4">Informações Básicas</h4>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-500 mb-1">Título</label>
              <p class="text-gray-900">{{ expense.title }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-500 mb-1">Categoria</label>
              <div class="flex items-center gap-2">
                <div
                  class="w-3 h-3 rounded-full"
                  :style="{ backgroundColor: expense.category?.color || '#6b7280' }"
                ></div>
                <span>{{ expense.category?.name || 'Sem categoria' }}</span>
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-500 mb-1">Valor</label>
              <p class="text-xl font-bold text-gray-900">{{ formatMoney(expense.amount) }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-500 mb-1">Status</label>
              <span 
                class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                :class="getStatusClass(expense.status)"
              >
                {{ getStatusLabel(expense.status) }}
              </span>
            </div>
            <div v-if="expense.due_date">
              <label class="block text-sm font-medium text-gray-500 mb-1">Data de Vencimento</label>
              <p class="text-gray-900">{{ formatDate(expense.due_date) }}</p>
            </div>
            <div v-if="expense.vendor_name">
              <label class="block text-sm font-medium text-gray-500 mb-1">Fornecedor</label>
              <p class="text-gray-900">{{ expense.vendor_name }}</p>
            </div>
          </div>
        </div>

        <!-- Description -->
        <div v-if="expense.description" class="mb-6">
          <h4 class="text-lg font-semibold text-gray-900 mb-2">Descrição</h4>
          <p class="text-gray-700">{{ expense.description }}</p>
        </div>

        <!-- Payment Info -->
        <div v-if="expense.status === 'paid' && expense.payment_date" class="mb-6">
          <h4 class="text-lg font-semibold text-gray-900 mb-4">Informações de Pagamento</h4>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 bg-green-50 rounded-lg">
            <div>
              <label class="block text-sm font-medium text-green-700 mb-1">Data do Pagamento</label>
              <p class="text-green-900">{{ formatDate(expense.payment_date) }}</p>
            </div>
            <div v-if="expense.payment_method">
              <label class="block text-sm font-medium text-green-700 mb-1">Método de Pagamento</label>
              <p class="text-green-900">{{ getPaymentMethodLabel(expense.payment_method) }}</p>
            </div>
            <div v-if="expense.reference_number" class="md:col-span-2">
              <label class="block text-sm font-medium text-green-700 mb-1">Número de Referência</label>
              <p class="text-green-900">{{ expense.reference_number }}</p>
            </div>
          </div>
        </div>

        <!-- Rejection Info -->
        <div v-if="expense.status === 'rejected' && expense.rejection_reason" class="mb-6">
          <h4 class="text-lg font-semibold text-gray-900 mb-4">Motivo da Rejeição</h4>
          <div class="p-4 bg-red-50 border border-red-200 rounded-lg">
            <p class="text-red-900">{{ expense.rejection_reason }}</p>
          </div>
        </div>

        <!-- Attachments -->
        <div v-if="expense.attachments && expense.attachments.length > 0" class="mb-6">
          <h4 class="text-lg font-semibold text-gray-900 mb-4">Anexos</h4>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div
              v-for="(attachment, index) in expense.attachments"
              :key="index"
              class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50"
            >
              <vue-feather type="file" size="20" class="text-gray-500" />
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate">
                  {{ attachment.original_name || attachment.name }}
                </p>
                <p class="text-xs text-gray-500">
                  {{ formatFileSize(attachment.size) }}
                </p>
              </div>
              <button
                @click="downloadAttachment(attachment, index)"
                class="text-blue-600 hover:text-blue-800 transition-colors"
              >
                <vue-feather type="download" size="16" />
              </button>
            </div>
          </div>
        </div>

        <!-- Audit Info -->
        <div class="border-t border-gray-200 pt-6">
          <h4 class="text-lg font-semibold text-gray-900 mb-4">Informações do Sistema</h4>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600">
            <div>
              <label class="block font-medium mb-1">Criado por</label>
              <p>{{ expense.created_by_user?.name || 'Sistema' }}</p>
            </div>
            <div>
              <label class="block font-medium mb-1">Data de Criação</label>
              <p>{{ formatDate(expense.created_at) }}</p>
            </div>
            <div v-if="expense.approved_by_user">
              <label class="block font-medium mb-1">Aprovado por</label>
              <p>{{ expense.approved_by_user.name }}</p>
            </div>
            <div v-if="expense.approved_at">
              <label class="block font-medium mb-1">Data de Aprovação</label>
              <p>{{ formatDate(expense.approved_at) }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="flex justify-end gap-3 p-6 border-t border-gray-200">
        <button
          @click="$emit('close')"
          class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors"
        >
          Fechar
        </button>
        <button
          v-if="expense && expense.status === 'pending'"
          @click="$emit('edit', expense)"
          class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors flex items-center gap-2"
        >
          <vue-feather type="edit-2" size="16" />
          Editar
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { useFormatting } from '@/composables/useFormatting'
import VueFeather from 'vue-feather'

export default {
  name: 'ViewExpenseModal',
  components: {
    VueFeather
  },
  props: {
    show: {
      type: Boolean,
      default: false
    },
    expense: {
      type: Object,
      default: null
    }
  },
  emits: ['close', 'edit'],

  setup() {
    const { formatMoney, formatDate } = useFormatting()

    const getStatusClass = (status) => {
      const classes = {
        pending: 'bg-yellow-100 text-yellow-800',
        approved: 'bg-green-100 text-green-800',
        paid: 'bg-blue-100 text-blue-800',
        rejected: 'bg-red-100 text-red-800'
      }
      return classes[status] || 'bg-gray-100 text-gray-800'
    }

    const getStatusLabel = (status) => {
      const labels = {
        pending: 'Pendente',
        approved: 'Aprovada',
        paid: 'Paga',
        rejected: 'Rejeitada'
      }
      return labels[status] || status
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

    const formatFileSize = (bytes) => {
      if (!bytes) return '0 B'
      const k = 1024
      const sizes = ['B', 'KB', 'MB', 'GB']
      const i = Math.floor(Math.log(bytes) / Math.log(k))
      return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
    }

    const downloadAttachment = (attachment, index) => {
      // Implement download logic here
      const url = `/expenses/${attachment.expense_id}/attachment/${index}/download`
      window.open(url, '_blank')
    }

    return {
      formatMoney,
      formatDate,
      getStatusClass,
      getStatusLabel,
      getPaymentMethodLabel,
      formatFileSize,
      downloadAttachment
    }
  }
}
</script>