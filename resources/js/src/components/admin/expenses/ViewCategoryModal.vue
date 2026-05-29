<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-[90vh] overflow-y-auto">
      <!-- Header -->
      <div class="flex items-center justify-between p-6 border-b border-gray-200">
        <div class="flex items-center gap-4">
          <div
            class="w-12 h-12 rounded-full flex items-center justify-center text-white"
            :style="{ backgroundColor: category.color }"
          >
            <i v-if="category.icon" :class="category.icon" class="text-xl"></i>
            <vue-feather v-else type="tag" size="24" />
          </div>
          <div>
            <h3 class="text-xl font-semibold text-gray-900">{{ category.name }}</h3>
            <div class="flex items-center gap-2 mt-1">
              <span
                class="px-2 py-1 text-xs font-medium rounded-full"
                :class="category.active 
                  ? 'bg-green-100 text-green-800' 
                  : 'bg-red-100 text-red-800'"
              >
                {{ category.active ? 'Ativo' : 'Inativo' }}
              </span>
            </div>
          </div>
        </div>
        
        <div class="flex items-center gap-2">
          <button
            @click="$emit('edit', category)"
            class="px-4 py-2 text-blue-600 hover:text-blue-700 border border-blue-600 rounded-lg hover:bg-blue-50 transition-colors flex items-center gap-2"
          >
            <vue-feather type="edit-2" size="16" />
            Editar
          </button>
          
          <button
            @click="$emit('close')"
            class="text-gray-400 hover:text-gray-600 transition-colors"
          >
            <vue-feather type="x" size="24" />
          </button>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="p-8 text-center">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        <p class="mt-2 text-gray-600">Carregando detalhes...</p>
      </div>

      <!-- Content -->
      <div v-else class="p-6 space-y-6">
        <!-- Description -->
        <div v-if="category.description" class="bg-gray-50 rounded-lg p-4">
          <h4 class="font-medium text-gray-900 mb-2">Descrição</h4>
          <p class="text-gray-700">{{ category.description }}</p>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div class="bg-white border border-gray-200 rounded-lg p-4 text-center">
            <div class="text-2xl font-bold text-gray-900">{{ statistics?.total_expenses || 0 }}</div>
            <div class="text-sm text-gray-500">Total de Despesas</div>
          </div>
          
          <div class="bg-white border border-gray-200 rounded-lg p-4 text-center">
            <div class="text-2xl font-bold text-green-600">{{ formatMoney(statistics?.total_amount || 0) }}</div>
            <div class="text-sm text-gray-500">Valor Total</div>
          </div>
          
          <div class="bg-white border border-gray-200 rounded-lg p-4 text-center">
            <div class="text-2xl font-bold text-blue-600">{{ statistics?.current_year_expenses || 0 }}</div>
            <div class="text-sm text-gray-500">Despesas {{ currentYear }}</div>
          </div>
          
          <div class="bg-white border border-gray-200 rounded-lg p-4 text-center">
            <div class="text-2xl font-bold text-orange-600">{{ formatMoney(statistics?.current_year_amount || 0) }}</div>
            <div class="text-sm text-gray-500">Valor {{ currentYear }}</div>
          </div>
        </div>

        <!-- Status Statistics -->
        <div class="bg-white border border-gray-200 rounded-lg p-6">
          <h4 class="font-semibold text-gray-900 mb-4">Status das Despesas</h4>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="text-center p-4 bg-yellow-50 rounded-lg">
              <div class="text-xl font-bold text-yellow-600">{{ statistics?.pending_expenses || 0 }}</div>
              <div class="text-sm text-yellow-700">Pendentes</div>
            </div>
            
            <div class="text-center p-4 bg-green-50 rounded-lg">
              <div class="text-xl font-bold text-green-600">{{ statistics?.approved_expenses || 0 }}</div>
              <div class="text-sm text-green-700">Aprovadas</div>
            </div>
            
            <div class="text-center p-4 bg-blue-50 rounded-lg">
              <div class="text-xl font-bold text-blue-600">{{ statistics?.paid_expenses || 0 }}</div>
              <div class="text-sm text-blue-700">Pagas</div>
            </div>
          </div>
        </div>

        <!-- Monthly Chart -->
        <div class="bg-white border border-gray-200 rounded-lg p-6">
          <h4 class="font-semibold text-gray-900 mb-4">Despesas Mensais - {{ currentYear }}</h4>
          
          <!-- Chart Container -->
          <div class="relative h-64">
            <svg class="w-full h-full" viewBox="0 0 800 200">
              <!-- Grid Lines -->
              <g class="text-gray-300" stroke="currentColor" stroke-width="1">
                <line x1="50" y1="20" x2="50" y2="180" />
                <line x1="50" y1="180" x2="750" y2="180" />
                
                <!-- Horizontal grid lines -->
                <line v-for="i in 5" :key="i" 
                      x1="50" :y1="20 + (i-1) * 40" 
                      x2="750" :y2="20 + (i-1) * 40" 
                      stroke-opacity="0.2" />
              </g>

              <!-- Month Labels -->
              <g class="text-xs text-gray-600 fill-current">
                <text v-for="(month, index) in monthlyExpenses" 
                      :key="month.month"
                      :x="70 + index * 58" 
                      y="195"
                      text-anchor="middle">
                  {{ month.month_name }}
                </text>
              </g>

              <!-- Value Labels -->
              <g class="text-xs text-gray-600 fill-current">
                <text x="45" y="25" text-anchor="end">{{ formatMoney(maxValue) }}</text>
                <text x="45" y="60" text-anchor="end">{{ formatMoney(maxValue * 0.75) }}</text>
                <text x="45" y="100" text-anchor="end">{{ formatMoney(maxValue * 0.5) }}</text>
                <text x="45" y="140" text-anchor="end">{{ formatMoney(maxValue * 0.25) }}</text>
                <text x="45" y="180" text-anchor="end">R$ 0</text>
              </g>

              <!-- Bars -->
              <g>
                <rect v-for="(month, index) in monthlyExpenses" 
                      :key="month.month"
                      :x="60 + index * 58" 
                      :y="180 - getBarHeight(month.total_amount)"
                      width="20" 
                      :height="getBarHeight(month.total_amount)"
                      :fill="category.color"
                      opacity="0.7"
                      class="hover:opacity-100 transition-opacity cursor-pointer">
                  <title>{{ month.month_name }}: {{ formatMoney(month.total_amount) }} ({{ month.expenses_count }} despesas)</title>
                </rect>
              </g>

              <!-- Values on bars -->
              <g class="text-xs text-gray-700 fill-current">
                <text v-for="(month, index) in monthlyExpenses" 
                      :key="month.month"
                      :x="70 + index * 58" 
                      :y="175 - getBarHeight(month.total_amount)"
                      text-anchor="middle"
                      v-if="month.total_amount > 0">
                  {{ month.expenses_count }}
                </text>
              </g>
            </svg>
          </div>
          
          <div class="mt-4 text-center text-sm text-gray-600">
            Valores em reais • Números nas barras indicam quantidade de despesas
          </div>
        </div>

        <!-- Category Details -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Category Info -->
          <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h4 class="font-semibold text-gray-900 mb-4">Informações da Categoria</h4>
            <div class="space-y-3">
              <div class="flex justify-between">
                <span class="text-gray-600">Nome:</span>
                <span class="font-medium">{{ category.name }}</span>
              </div>
              
              <div class="flex justify-between">
                <span class="text-gray-600">Cor:</span>
                <div class="flex items-center gap-2">
                  <div 
                    class="w-4 h-4 rounded border border-gray-200"
                    :style="{ backgroundColor: category.color }"
                  ></div>
                  <span class="font-mono text-sm">{{ category.color }}</span>
                </div>
              </div>
              
              <div v-if="category.icon" class="flex justify-between">
                <span class="text-gray-600">Ícone:</span>
                <div class="flex items-center gap-2">
                  <i :class="category.icon" class="text-gray-600"></i>
                  <span class="font-mono text-sm">{{ category.icon }}</span>
                </div>
              </div>
              
              <div class="flex justify-between">
                <span class="text-gray-600">Status:</span>
                <span class="font-medium" :class="category.active ? 'text-green-600' : 'text-red-600'">
                  {{ category.active ? 'Ativo' : 'Inativo' }}
                </span>
              </div>
            </div>
          </div>

          <!-- Recent Activity -->
          <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h4 class="font-semibold text-gray-900 mb-4">Resumo do Mês Atual</h4>
            <div class="space-y-3">
              <div class="flex justify-between">
                <span class="text-gray-600">Despesas:</span>
                <span class="font-medium">{{ statistics?.current_month_expenses || 0 }}</span>
              </div>
              
              <div class="flex justify-between">
                <span class="text-gray-600">Valor Total:</span>
                <span class="font-medium">{{ formatMoney(statistics?.current_month_amount || 0) }}</span>
              </div>
              
              <div class="pt-3 border-t border-gray-100">
                <div class="text-sm text-gray-600">
                  <strong>Comparação com o ano:</strong><br>
                  {{ getPercentageOfYear() }}% do total anual
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import { useToast } from '@/composables/useToast'
import { useFormatting } from '@/composables/useFormatting'
import VueFeather from 'vue-feather'

export default {
  name: 'ViewCategoryModal',
  components: {
    VueFeather
  },
  props: {
    category: {
      type: Object,
      required: true
    }
  },
  emits: ['close', 'edit'],

  setup(props, { emit }) {
    const { showToast } = useToast()
    const { formatMoney } = useFormatting()

    // State
    const loading = ref(true)
    const statistics = ref({})
    const monthlyExpenses = ref([])
    
    // Computed
    const currentYear = computed(() => new Date().getFullYear())

    const maxValue = computed(() => {
      if (!monthlyExpenses.value || monthlyExpenses.value.length === 0) return 1000
      
      const max = Math.max(...monthlyExpenses.value.map(m => m.total_amount))
      return max > 0 ? max : 1000
    })

    // Methods
    const loadCategoryDetails = async () => {
      try {
        loading.value = true
        
        const response = await fetch(`/expense-categories/${props.category.id}`)
        
        if (!response.ok) throw new Error('Erro ao carregar detalhes da categoria')
        
        const data = await response.json()
        statistics.value = data.statistics
        monthlyExpenses.value = data.monthly_expenses
      } catch (error) {
        console.error('Erro ao carregar detalhes:', error)
        showToast('Erro ao carregar detalhes da categoria', 'error')
      } finally {
        loading.value = false
      }
    }

    const getBarHeight = (amount) => {
      if (maxValue.value === 0) return 0
      return Math.max(2, (amount / maxValue.value) * 160)
    }

    const getPercentageOfYear = () => {
      const currentMonth = statistics.value?.current_month_amount || 0
      const yearTotal = statistics.value?.current_year_amount || 0
      
      if (yearTotal === 0) return 0
      
      return ((currentMonth / yearTotal) * 100).toFixed(1)
    }

    // Lifecycle
    onMounted(() => {
      loadCategoryDetails()
    })

    return {
      // State
      loading,
      statistics,
      monthlyExpenses,

      // Computed
      currentYear,
      maxValue,

      // Methods
      formatMoney,
      getBarHeight,
      getPercentageOfYear
    }
  }
}
</script>