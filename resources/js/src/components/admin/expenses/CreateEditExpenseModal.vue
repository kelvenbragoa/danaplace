<template>
  <div v-if="show" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl max-h-[90vh] overflow-hidden">
      <!-- Header -->
      <div class="flex items-center justify-between p-6 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">
          {{ editMode ? 'Editar Despesa' : 'Nova Despesa' }}
        </h3>
        <button
          @click="$emit('close')"
          class="text-gray-400 hover:text-gray-600 transition-colors"
        >
          <vue-feather type="x" size="24" />
        </button>
      </div>

      <!-- Content -->
      <div class="p-6 overflow-y-auto max-h-[calc(90vh-140px)]">
        <form @submit.prevent="submitForm">
          <div class="space-y-4">
            <!-- Title -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Título *
              </label>
              <input
                v-model="form.title"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                :class="{ 'border-red-500': errors.title }"
                placeholder="Título da despesa"
              />
              <p v-if="errors.title" class="mt-1 text-sm text-red-600">
                {{ errors.title[0] }}
              </p>
            </div>

            <!-- Category -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Categoria *
              </label>
              <select
                v-model="form.category_id"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                :class="{ 'border-red-500': errors.category_id }"
              >
                <option value="">Selecione uma categoria</option>
                <option 
                  v-for="category in categories" 
                  :key="category.id" 
                  :value="category.id"
                >
                  {{ category.name }}
                </option>
              </select>
              <p v-if="errors.category_id" class="mt-1 text-sm text-red-600">
                {{ errors.category_id[0] }}
              </p>
            </div>

            <!-- Amount -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Valor *
              </label>
              <input
                v-model="form.amount"
                type="number"
                step="0.01"
                min="0"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                :class="{ 'border-red-500': errors.amount }"
                placeholder="0,00"
              />
              <p v-if="errors.amount" class="mt-1 text-sm text-red-600">
                {{ errors.amount[0] }}
              </p>
            </div>

            <!-- Due Date -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Data de Vencimento
              </label>
              <input
                v-model="form.due_date"
                type="date"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                :class="{ 'border-red-500': errors.due_date }"
              />
              <p v-if="errors.due_date" class="mt-1 text-sm text-red-600">
                {{ errors.due_date[0] }}
              </p>
            </div>

            <!-- Vendor -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Fornecedor
              </label>
              <input
                v-model="form.vendor_name"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                :class="{ 'border-red-500': errors.vendor_name }"
                placeholder="Nome do fornecedor"
              />
              <p v-if="errors.vendor_name" class="mt-1 text-sm text-red-600">
                {{ errors.vendor_name[0] }}
              </p>
            </div>

            <!-- Description -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Descrição
              </label>
              <textarea
                v-model="form.description"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
                :class="{ 'border-red-500': errors.description }"
                placeholder="Descrição da despesa"
              ></textarea>
              <p v-if="errors.description" class="mt-1 text-sm text-red-600">
                {{ errors.description[0] }}
              </p>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex gap-3 mt-6 pt-6 border-t border-gray-200">
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
              {{ loading ? 'Salvando...' : (editMode ? 'Atualizar' : 'Criar Despesa') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive, computed, watch, onMounted } from 'vue'
import { useToast } from '@/composables/useToast'
import VueFeather from 'vue-feather'

export default {
  name: 'CreateEditExpenseModal',
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
  emits: ['close', 'saved'],

  setup(props, { emit }) {
    const { showToast } = useToast()

    // State
    const loading = ref(false)
    const errors = ref({})
    const categories = ref([])

    // Computed
    const editMode = computed(() => !!props.expense?.id)

    // Form
    const form = reactive({
      title: '',
      category_id: '',
      amount: '',
      due_date: '',
      vendor_name: '',
      description: ''
    })

    // Methods
    const resetForm = () => {
      form.title = ''
      form.category_id = ''
      form.amount = ''
      form.due_date = ''
      form.vendor_name = ''
      form.description = ''
      errors.value = {}
    }

    const populateForm = () => {
      if (props.expense) {
        form.title = props.expense.title || ''
        form.category_id = props.expense.category_id || ''
        form.amount = props.expense.amount || ''
        form.due_date = props.expense.due_date || ''
        form.vendor_name = props.expense.vendor_name || ''
        form.description = props.expense.description || ''
      }
    }

    const loadCategories = async () => {
      try {
        const response = await fetch('/expense-categories-active')
        const data = await response.json()
        categories.value = data.categories || []
      } catch (error) {
        console.error('Erro ao carregar categorias:', error)
        showToast('Erro ao carregar categorias', 'error')
      }
    }

    const submitForm = async () => {
      try {
        loading.value = true
        errors.value = {}

        const url = editMode.value ? `/expenses/${props.expense.id}` : '/expenses'
        const method = editMode.value ? 'PUT' : 'POST'

        const response = await fetch(url, {
          method,
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
          throw new Error(result.message || 'Erro ao salvar despesa')
        }

        showToast(result.message)
        emit('saved', result.expense)
        resetForm()
      } catch (error) {
        console.error('Erro ao salvar despesa:', error)
        showToast(error.message || 'Erro ao salvar despesa', 'error')
      } finally {
        loading.value = false
      }
    }

    // Watchers
    watch(() => props.show, (newVal) => {
      if (newVal) {
        if (editMode.value) {
          populateForm()
        } else {
          resetForm()
        }
      }
    })

    // Lifecycle
    onMounted(() => {
      loadCategories()
    })

    return {
      // State
      loading,
      errors,
      categories,
      form,

      // Computed
      editMode,

      // Methods
      submitForm
    }
  }
}
</script>