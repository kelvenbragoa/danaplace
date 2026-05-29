<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
      <!-- Header -->
      <div class="flex items-center justify-between p-6 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">Rejeitar Despesa</h3>
        <button
          @click="$emit('close')"
          class="text-gray-400 hover:text-gray-600 transition-colors"
        >
          <vue-feather type="x" size="24" />
        </button>
      </div>

      <!-- Content -->
      <div class="p-6">
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
          <div class="flex items-start gap-3">
            <vue-feather type="alert-triangle" size="20" class="text-red-500 mt-0.5 flex-shrink-0" />
            <div>
              <h4 class="font-medium text-red-800 mb-1">Atenção</h4>
              <p class="text-sm text-red-700">
                Você está prestes a rejeitar esta despesa. Esta ação mudará o status para "Rejeitada".
              </p>
            </div>
          </div>
        </div>

        <div class="mb-6">
          <h4 class="font-medium text-gray-900 mb-2">{{ expense.title }}</h4>
          <div class="text-sm text-gray-600">
            <p>Valor: <span class="font-semibold">{{ formatMoney(expense.amount) }}</span></p>
            <p>Fornecedor: <span class="font-medium">{{ expense.vendor_name || 'Não informado' }}</span></p>
          </div>
        </div>

        <form @submit.prevent="submitRejection">
          <!-- Rejection Reason -->
          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Motivo da Rejeição *
            </label>
            <textarea
              v-model="form.rejection_reason"
              required
              rows="4"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 resize-none"
              :class="{ 'border-red-500': errors.rejection_reason }"
              placeholder="Explique o motivo da rejeição da despesa..."
            ></textarea>
            <p v-if="errors.rejection_reason" class="mt-1 text-sm text-red-600">
              {{ errors.rejection_reason[0] }}
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
              class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
            >
              <div v-if="loading" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
              {{ loading ? 'Rejeitando...' : 'Rejeitar Despesa' }}
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
  name: 'RejectModal',
  components: {
    VueFeather
  },
  props: {
    expense: {
      type: Object,
      required: true
    }
  },
  emits: ['close', 'rejected'],

  setup(props, { emit }) {
    const { showToast } = useToast()
    const { formatMoney } = useFormatting()

    // State
    const loading = ref(false)
    const errors = ref({})

    // Form
    const form = reactive({
      rejection_reason: ''
    })

    // Methods
    const submitRejection = async () => {
      try {
        loading.value = true
        errors.value = {}

        const response = await fetch(`/expenses/${props.expense.id}/reject`, {
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
          throw new Error(result.message || 'Erro ao rejeitar despesa')
        }

        showToast(result.message)
        emit('rejected', result.expense)
      } catch (error) {
        console.error('Erro ao rejeitar despesa:', error)
        showToast(error.message || 'Erro ao rejeitar despesa', 'error')
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
      submitRejection,
      formatMoney
    }
  }
}
</script>