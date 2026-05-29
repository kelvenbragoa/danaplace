<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
      <!-- Header -->
      <div class="flex items-center justify-between p-6 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">Marcar como Paga</h3>
        <button
          @click="$emit('close')"
          class="text-gray-400 hover:text-gray-600 transition-colors"
        >
          <vue-feather type="x" size="24" />
        </button>
      </div>

      <!-- Content -->
      <div class="p-6">
        <div class="mb-4">
          <h4 class="font-medium text-gray-900 mb-2">{{ expense.title }}</h4>
          <div class="text-sm text-gray-600">
            <p>Valor: <span class="font-semibold">{{ formatMoney(expense.amount) }}</span></p>
            <p>Fornecedor: <span class="font-medium">{{ expense.vendor_name || 'Não informado' }}</span></p>
          </div>
        </div>

        <form @submit.prevent="submitPayment">
          <!-- Payment Date -->
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Data de Pagamento *
            </label>
            <input
              v-model="form.payment_date"
              type="date"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              :class="{ 'border-red-500': errors.payment_date }"
            />
            <p v-if="errors.payment_date" class="mt-1 text-sm text-red-600">
              {{ errors.payment_date[0] }}
            </p>
          </div>

          <!-- Payment Method -->
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Método de Pagamento *
            </label>
            <select
              v-model="form.payment_method"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              :class="{ 'border-red-500': errors.payment_method }"
            >
              <option value="">Selecione o método</option>
              <option value="cash">Dinheiro</option>
              <option value="bank_transfer">Transferência Bancária</option>
              <option value="check">Cheque</option>
              <option value="card">Cartão</option>
              <option value="other">Outro</option>
            </select>
            <p v-if="errors.payment_method" class="mt-1 text-sm text-red-600">
              {{ errors.payment_method[0] }}
            </p>
          </div>

          <!-- Reference Number -->
          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Número de Referência
            </label>
            <input
              v-model="form.reference_number"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              :class="{ 'border-red-500': errors.reference_number }"
              placeholder="Número do comprovante, transação, etc."
            />
            <p v-if="errors.reference_number" class="mt-1 text-sm text-red-600">
              {{ errors.reference_number[0] }}
            </p>
          </div>

          <!-- Actions -->
          <div class="flex gap-3">
            <button
              type="button"
              @click="$emit('close')"
              class="flex-1 px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors"
            >
              Cancelar
            </button>
            <button
              type="submit"
              :disabled="loading"
              class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
            >
              <div v-if="loading" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
              {{ loading ? 'Salvando...' : 'Confirmar Pagamento' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive } from 'vue'
import { useToast } from '@/composables/useToast'
import { useFormatting } from '@/composables/useFormatting'
import VueFeather from 'vue-feather'

export default {
  name: 'PaymentModal',
  components: {
    VueFeather
  },
  props: {
    expense: {
      type: Object,
      required: true
    }
  },
  emits: ['close', 'paid'],

  setup(props, { emit }) {
    const { showToast } = useToast()
    const { formatMoney } = useFormatting()

    // State
    const loading = ref(false)
    const errors = ref({})

    // Form
    const form = reactive({
      payment_date: new Date().toISOString().split('T')[0],
      payment_method: '',
      reference_number: ''
    })

    // Methods
    const submitPayment = async () => {
      try {
        loading.value = true
        errors.value = {}

        const response = await fetch(`/expenses/${props.expense.id}/mark-as-paid`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          },
          body: JSON.stringify(form)
        })

        const result = await response.json()

        if (!response.ok) {
          if (response.status === 422) {
            errors.value = result.errors || {}
            return
          }
          throw new Error(result.message || 'Erro ao marcar pagamento')
        }

        showToast(result.message)
        emit('paid', result.expense)
      } catch (error) {
        console.error('Erro ao marcar pagamento:', error)
        showToast(error.message || 'Erro ao marcar pagamento', 'error')
      } finally {
        loading.value = false
      }
    }

    return {
      // State
      loading,
      errors,
      form,

      // Methods
      submitPayment,
      formatMoney
    }
  }
}
</script>