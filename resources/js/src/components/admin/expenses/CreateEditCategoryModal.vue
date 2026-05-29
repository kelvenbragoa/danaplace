<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md max-h-[90vh] overflow-y-auto">
      <!-- Header -->
      <div class="flex items-center justify-between p-6 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">
          {{ isEditing ? 'Editar Categoria' : 'Nova Categoria' }}
        </h3>
        <button
          @click="$emit('close')"
          class="text-gray-400 hover:text-gray-600 transition-colors"
        >
          <vue-feather type="x" size="24" />
        </button>
      </div>

      <!-- Form -->
      <form @submit.prevent="submitForm" class="p-6 space-y-6">
        <!-- Nome -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Nome da Categoria *
          </label>
          <input
            v-model="form.name"
            type="text"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            :class="{ 'border-red-500': errors.name }"
            placeholder="Ex: Manutenção, Limpeza, Segurança..."
          />
          <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name[0] }}</p>
        </div>

        <!-- Descrição -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Descrição
          </label>
          <textarea
            v-model="form.description"
            rows="3"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            :class="{ 'border-red-500': errors.description }"
            placeholder="Descreva o tipo de despesas desta categoria..."
          ></textarea>
          <p v-if="errors.description" class="mt-1 text-sm text-red-600">{{ errors.description[0] }}</p>
        </div>

        <!-- Cor e Ícone -->
        <div class="grid grid-cols-2 gap-4">
          <!-- Cor -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Cor *
            </label>
            <div class="flex items-center gap-3">
              <div
                class="w-10 h-10 rounded-lg border-2 border-gray-200 cursor-pointer flex-shrink-0"
                :style="{ backgroundColor: form.color }"
                @click="showColorPicker = !showColorPicker"
              ></div>
              <input
                v-model="form.color"
                type="text"
                class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                :class="{ 'border-red-500': errors.color }"
                placeholder="#FF6B6B"
                pattern="^#[0-9A-Fa-f]{6}$"
              />
            </div>
            
            <!-- Color Picker -->
            <div v-if="showColorPicker" class="mt-3 p-3 border border-gray-200 rounded-lg bg-gray-50">
              <p class="text-sm font-medium text-gray-700 mb-2">Cores Sugeridas:</p>
              <div class="grid grid-cols-5 gap-2">
                <button
                  v-for="color in suggestedColors"
                  :key="color"
                  type="button"
                  @click="selectColor(color)"
                  class="w-8 h-8 rounded border-2 hover:scale-110 transition-transform"
                  :style="{ backgroundColor: color }"
                  :class="form.color === color ? 'border-gray-800' : 'border-gray-200'"
                ></button>
              </div>
            </div>
            
            <p v-if="errors.color" class="mt-1 text-sm text-red-600">{{ errors.color[0] }}</p>
          </div>

          <!-- Ícone -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Ícone
            </label>
            <div class="flex items-center gap-3">
              <div
                class="w-10 h-10 rounded-lg border-2 border-gray-200 flex items-center justify-center cursor-pointer"
                :style="{ backgroundColor: form.color, color: 'white' }"
                @click="showIconPicker = !showIconPicker"
              >
                <i v-if="form.icon" :class="form.icon"></i>
                <vue-feather v-else type="tag" size="20" />
              </div>
              <input
                v-model="form.icon"
                type="text"
                class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                :class="{ 'border-red-500': errors.icon }"
                placeholder="fas fa-home"
              />
            </div>
            
            <!-- Icon Picker -->
            <div v-if="showIconPicker" class="mt-3 p-3 border border-gray-200 rounded-lg bg-gray-50 max-h-40 overflow-y-auto">
              <p class="text-sm font-medium text-gray-700 mb-2">Ícones Sugeridos:</p>
              <div class="grid grid-cols-6 gap-2">
                <button
                  v-for="icon in suggestedIcons"
                  :key="icon"
                  type="button"
                  @click="selectIcon(icon)"
                  class="w-8 h-8 rounded border hover:bg-blue-100 flex items-center justify-center transition-colors"
                  :class="form.icon === icon ? 'bg-blue-100 border-blue-500' : 'border-gray-200'"
                >
                  <i :class="icon" class="text-gray-600"></i>
                </button>
              </div>
            </div>
            
            <p v-if="errors.icon" class="mt-1 text-sm text-red-600">{{ errors.icon[0] }}</p>
          </div>
        </div>

        <!-- Preview -->
        <div class="p-4 bg-gray-50 rounded-lg">
          <p class="text-sm font-medium text-gray-700 mb-2">Preview:</p>
          <div class="flex items-center gap-3">
            <div
              class="w-10 h-10 rounded-full flex items-center justify-center text-white"
              :style="{ backgroundColor: form.color }"
            >
              <i v-if="form.icon" :class="form.icon" class="text-lg"></i>
              <vue-feather v-else type="tag" size="20" />
            </div>
            <div>
              <div class="font-medium text-gray-900">
                {{ form.name || 'Nome da Categoria' }}
              </div>
              <div class="text-sm text-gray-500">
                {{ form.description || 'Descrição da categoria' }}
              </div>
            </div>
          </div>
        </div>

        <!-- Status -->
        <div class="flex items-center justify-between">
          <label class="text-sm font-medium text-gray-700">
            Categoria Ativa
          </label>
          <button
            type="button"
            @click="form.active = !form.active"
            class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
            :class="form.active ? 'bg-blue-600' : 'bg-gray-200'"
          >
            <span
              class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
              :class="form.active ? 'translate-x-5' : 'translate-x-0'"
            ></span>
          </button>
        </div>

        <!-- Actions -->
        <div class="flex gap-3 pt-4 border-t border-gray-200">
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
            {{ loading ? 'Salvando...' : (isEditing ? 'Atualizar' : 'Criar Categoria') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { ref, reactive, computed, onMounted } from 'vue'
import { useToast } from '@/composables/useToast'
import VueFeather from 'vue-feather'

export default {
  name: 'CreateEditCategoryModal',
  components: {
    VueFeather
  },
  props: {
    category: {
      type: Object,
      default: null
    }
  },
  emits: ['close', 'saved'],

  setup(props, { emit }) {
    const { showToast } = useToast()

    // State
    const loading = ref(false)
    const showColorPicker = ref(false)
    const showIconPicker = ref(false)
    const suggestedColors = ref([])
    const suggestedIcons = ref([])
    const errors = ref({})

    // Form
    const form = reactive({
      name: '',
      description: '',
      color: '#3B82F6',
      icon: '',
      active: true
    })

    // Computed
    const isEditing = computed(() => props.category !== null)

    // Methods
    const loadSuggestions = async () => {
      try {
        // Load color suggestions
        const colorResponse = await fetch('/expense-categories-colors')
        if (colorResponse.ok) {
          suggestedColors.value = await colorResponse.json()
        }

        // Load icon suggestions
        const iconResponse = await fetch('/expense-categories-icons')
        if (iconResponse.ok) {
          suggestedIcons.value = await iconResponse.json()
        }
      } catch (error) {
        console.error('Erro ao carregar sugestões:', error)
      }
    }

    const selectColor = (color) => {
      form.color = color
      showColorPicker.value = false
    }

    const selectIcon = (icon) => {
      form.icon = icon
      showIconPicker.value = false
    }

    const resetForm = () => {
      form.name = ''
      form.description = ''
      form.color = '#3B82F6'
      form.icon = ''
      form.active = true
      errors.value = {}
    }

    const populateForm = () => {
      if (props.category) {
        form.name = props.category.name
        form.description = props.category.description || ''
        form.color = props.category.color
        form.icon = props.category.icon || ''
        form.active = props.category.active
      }
    }

    const submitForm = async () => {
      try {
        loading.value = true
        errors.value = {}

        const url = isEditing.value 
          ? `/expense-categories/${props.category.id}`
          : '/expense-categories'
        
        const method = isEditing.value ? 'PUT' : 'POST'

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
          throw new Error(result.message || 'Erro ao salvar categoria')
        }

        showToast(result.message)
        emit('saved', result.category)
      } catch (error) {
        console.error('Erro ao salvar categoria:', error)
        showToast(error.message || 'Erro ao salvar categoria', 'error')
      } finally {
        loading.value = false
      }
    }

    // Lifecycle
    onMounted(() => {
      loadSuggestions()
      populateForm()
    })

    return {
      // State
      loading,
      showColorPicker,
      showIconPicker,
      suggestedColors,
      suggestedIcons,
      errors,
      form,

      // Computed
      isEditing,

      // Methods
      selectColor,
      selectIcon,
      submitForm
    }
  }
}
</script>