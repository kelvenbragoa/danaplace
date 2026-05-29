<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-[90vh] overflow-hidden">
      <!-- Header -->
      <div class="flex items-center justify-between p-6 border-b border-gray-200">
        <h3 class="text-xl font-semibold text-gray-900">Estatísticas de Despesas</h3>
        <button
          @click="$emit('close')"
          class="text-gray-400 hover:text-gray-600 transition-colors"
        >
          <vue-feather type="x" size="24" />
        </button>
      </div>

      <!-- Content -->
      <div class="p-6 overflow-y-auto max-h-[calc(90vh-100px)]">
        <!-- Loading State -->
        <div v-if="loading" class="flex items-center justify-center py-12">
          <div class="text-center">
            <div class="w-8 h-8 border-4 border-blue-200 border-t-blue-600 rounded-full animate-spin mx-auto mb-4"></div>
            <p class="text-gray-600">Carregando estatísticas...</p>
          </div>
        </div>

        <!-- Statistics Content -->
        <div v-else-if="statistics" class="space-y-6">
          <!-- Summary Cards -->
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Total Expenses -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
              <div class="flex items-center gap-3">
                <div class="p-2 bg-blue-100 rounded-lg">
                  <vue-feather type="dollar-sign" size="20" class="text-blue-600" />
                </div>
                <div>
                  <p class="text-sm text-blue-600 font-medium">Total de Despesas</p>
                  <p class="text-xl font-bold text-blue-900">{{ formatMoney(statistics.total_amount) }}</p>
                </div>
              </div>
            </div>

            <!-- Pending Amount -->
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
              <div class="flex items-center gap-3">
                <div class="p-2 bg-yellow-100 rounded-lg">
                  <vue-feather type="clock" size="20" class="text-yellow-600" />
                </div>
                <div>
                  <p class="text-sm text-yellow-600 font-medium">Pendente</p>
                  <p class="text-xl font-bold text-yellow-900">{{ formatMoney(statistics.pending_amount) }}</p>
                </div>
              </div>
            </div>

            <!-- Approved Amount -->
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
              <div class="flex items-center gap-3">
                <div class="p-2 bg-green-100 rounded-lg">
                  <vue-feather type="check-circle" size="20" class="text-green-600" />
                </div>
                <div>
                  <p class="text-sm text-green-600 font-medium">Aprovado</p>
                  <p class="text-xl font-bold text-green-900">{{ formatMoney(statistics.approved_amount) }}</p>
                </div>
              </div>
            </div>

            <!-- Paid Amount -->
            <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
              <div class="flex items-center gap-3">
                <div class="p-2 bg-purple-100 rounded-lg">
                  <vue-feather type="credit-card" size="20" class="text-purple-600" />
                </div>
                <div>
                  <p class="text-sm text-purple-600 font-medium">Pago</p>
                  <p class="text-xl font-bold text-purple-900">{{ formatMoney(statistics.paid_amount) }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Status Distribution -->
          <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
            <h4 class="font-semibold text-gray-900 mb-4">Distribuição por Status</h4>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
              <div v-for="status in statistics.by_status" :key="status.status" class="text-center">
                <div class="text-2xl font-bold" :class="getStatusColor(status.status)">
                  {{ status.count }}
                </div>
                <div class="text-sm text-gray-600 capitalize">
                  {{ translateStatus(status.status) }}
                </div>
              </div>
            </div>
          </div>

          <!-- Categories -->
          <div v-if="statistics.by_category && statistics.by_category.length" class="bg-gray-50 border border-gray-200 rounded-lg p-4">
            <h4 class="font-semibold text-gray-900 mb-4">Por Categoria</h4>
            <div class="space-y-2">
              <div
                v-for="category in statistics.by_category"
                :key="category.category_id"
                class="flex items-center justify-between p-3 bg-white rounded-lg border"
              >
                <div class="flex items-center gap-3">
                  <div
                    class="w-4 h-4 rounded-full"
                    :style="{ backgroundColor: category.category?.color || '#6b7280' }"
                  ></div>
                  <span class="font-medium">{{ category.category?.name || 'Sem categoria' }}</span>
                </div>
                <div class="text-right">
                  <div class="font-semibold">{{ formatMoney(category.total_amount) }}</div>
                  <div class="text-sm text-gray-500">{{ category.count }} despesas</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Monthly Trend -->
          <div v-if="statistics.monthly_trend && statistics.monthly_trend.length" class="bg-gray-50 border border-gray-200 rounded-lg p-4">
            <h4 class="font-semibold text-gray-900 mb-4">Tendência Mensal</h4>
            <div class="space-y-2">
              <div
                v-for="month in statistics.monthly_trend"
                :key="month.month"
                class="flex items-center justify-between p-3 bg-white rounded-lg border"
              >
                <span class="font-medium">{{ formatMonth(month.month) }}</span>
                <div class="text-right">
                  <div class="font-semibold">{{ formatMoney(month.total_amount) }}</div>
                  <div class="text-sm text-gray-500">{{ month.count }} despesas</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Top Vendors -->
          <div v-if="statistics.top_vendors && statistics.top_vendors.length" class="bg-gray-50 border border-gray-200 rounded-lg p-4">
            <h4 class="font-semibold text-gray-900 mb-4">Principais Fornecedores</h4>
            <div class="space-y-2">
              <div
                v-for="vendor in statistics.top_vendors"
                :key="vendor.vendor_name"
                class="flex items-center justify-between p-3 bg-white rounded-lg border"
              >
                <span class="font-medium">{{ vendor.vendor_name }}</span>
                <div class="text-right">
                  <div class="font-semibold">{{ formatMoney(vendor.total_amount) }}</div>
                  <div class="text-sm text-gray-500">{{ vendor.count }} despesas</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="text-center py-12">
          <div class="text-red-600 mb-4">
            <vue-feather type="alert-triangle" size="48" class="mx-auto" />
          </div>
          <p class="text-gray-600 mb-4">{{ error }}</p>
          <button
            @click="loadStatistics"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
          >
            Tentar Novamente
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { useFormatting } from '@/composables/useFormatting'
import VueFeather from 'vue-feather'

export default {
  name: 'StatisticsModal',
  components: {
    VueFeather
  },
  emits: ['close'],

  setup() {
    const { formatMoney } = useFormatting()

    // State
    const loading = ref(true)
    const statistics = ref(null)
    const error = ref(null)

    // Methods
    const loadStatistics = async () => {
      try {
        loading.value = true
        error.value = null

        const response = await fetch('/expenses/statistics')
        const data = await response.json()

        if (!response.ok) {
          throw new Error(data.message || 'Erro ao carregar estatísticas')
        }

        statistics.value = data.statistics
      } catch (err) {
        console.error('Erro ao carregar estatísticas:', err)
        error.value = err.message || 'Erro ao carregar estatísticas'
      } finally {
        loading.value = false
      }
    }

    const getStatusColor = (status) => {
      const colors = {
        pending: 'text-yellow-600',
        approved: 'text-green-600',
        paid: 'text-purple-600',
        rejected: 'text-red-600'
      }
      return colors[status] || 'text-gray-600'
    }

    const translateStatus = (status) => {
      const translations = {
        pending: 'Pendente',
        approved: 'Aprovado',
        paid: 'Pago',
        rejected: 'Rejeitado'
      }
      return translations[status] || status
    }

    const formatMonth = (monthStr) => {
      const [year, month] = monthStr.split('-')
      const date = new Date(year, month - 1)
      return date.toLocaleDateString('pt-BR', { year: 'numeric', month: 'long' })
    }

    // Lifecycle
    onMounted(() => {
      loadStatistics()
    })

    return {
      // State
      loading,
      statistics,
      error,

      // Methods
      loadStatistics,
      getStatusColor,
      translateStatus,
      formatMonth,
      formatMoney
    }
  }
}
</script>