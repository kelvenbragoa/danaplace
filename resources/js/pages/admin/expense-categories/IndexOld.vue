<template>
  <div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
      <div class="flex flex-col md:flex-row md:items-center md:justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Categorias de Despesas</h1>
          <p class="mt-2 text-gray-600">Gerencie as categorias para organização das despesas do condomínio</p>
        </div>
        <button
          @click="openCreateModal"
          class="mt-4 md:mt-0 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-colors"
        >
          <PlusIcon class="w-5 h-5" />
          Nova Categoria
        </button>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white p-6 rounded-lg shadow-sm border mb-6">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
          <input
            v-model="filters.query"
            @input="handleSearch"
            type="text"
            placeholder="Nome ou descrição..."
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
          <select
            v-model="filters.status"
            @change="loadCategories"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">Todos</option>
            <option value="active">Ativos</option>
            <option value="inactive">Inativos</option>
          </select>
        </div>

        <div class="flex items-end">
          <button
            @click="clearFilters"
            class="px-4 py-2 text-gray-600 hover:text-gray-800 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
          >
            Limpar Filtros
          </button>
        </div>
      </div>
    </div>

    <!-- Categories List -->
    <div class="bg-white rounded-lg shadow-sm border">
      <!-- Loading State -->
      <div v-if="loading" class="p-8 text-center">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        <p class="mt-2 text-gray-600">Carregando categorias...</p>
      </div>

      <!-- Empty State -->
      <div v-else-if="!categories.data || categories.data.length === 0" class="p-8 text-center">
        <div class="text-gray-400 mb-4">
          <TagIcon class="w-16 h-16 mx-auto" />
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhuma categoria encontrada</h3>
        <p class="text-gray-600 mb-6">
          {{ filters.query || filters.status ? 'Tente ajustar os filtros de busca' : 'Comece criando sua primeira categoria de despesa' }}
        </p>
        <button
          v-if="!filters.query && !filters.status"
          @click="openCreateModal"
          class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors"
        >
          Criar Primeira Categoria
        </button>
      </div>

      <!-- Categories Grid -->
      <div v-else class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div
            v-for="category in categories.data"
            :key="category.id"
            class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow"
            :class="{ 'opacity-60': !category.active }"
          >
            <!-- Category Header -->
            <div class="flex items-start justify-between mb-4">
              <div class="flex items-center gap-3">
                <div
                  class="w-10 h-10 rounded-full flex items-center justify-center text-white"
                  :style="{ backgroundColor: category.color }"
                >
                  <i v-if="category.icon" :class="category.icon" class="text-lg"></i>
                  <TagIcon v-else class="w-5 h-5" />
                </div>
                <div>
                  <h3 class="font-semibold text-gray-900">{{ category.name }}</h3>
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

              <!-- Actions Dropdown -->
              <div class="relative">
                <button
                  @click="toggleDropdown(category.id)"
                  class="p-1 rounded-full hover:bg-gray-100 transition-colors"
                >
                  <EllipsisVerticalIcon class="w-5 h-5 text-gray-500" />
                </button>
                
                <div
                  v-if="openDropdownId === category.id"
                  v-click-outside="closeDropdown"
                  class="absolute right-0 top-8 mt-1 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-10"
                >
                  <button
                    @click="viewCategory(category)"
                    class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2"
                  >
                    <EyeIcon class="w-4 h-4" />
                    Ver Detalhes
                  </button>
                  
                  <button
                    @click="editCategory(category)"
                    class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2"
                  >
                    <PencilIcon class="w-4 h-4" />
                    Editar
                  </button>
                  
                  <button
                    @click="toggleCategoryStatus(category)"
                    class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2"
                  >
                    <component
                      :is="category.active ? EyeSlashIcon : CheckCircleIcon"
                      class="w-4 h-4"
                    />
                    {{ category.active ? 'Desativar' : 'Ativar' }}
                  </button>
                  
                  <hr class="my-1" />
                  
                  <button
                    @click="confirmDelete(category)"
                    :disabled="category.total_expenses > 0"
                    class="w-full text-left px-4 py-2 text-sm hover:bg-red-50 flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                    :class="category.total_expenses > 0 ? 'text-gray-400' : 'text-red-600'"
                  >
                    <TrashIcon class="w-4 h-4" />
                    Excluir
                  </button>
                </div>
              </div>
            </div>

            <!-- Category Description -->
            <p v-if="category.description" class="text-gray-600 text-sm mb-4 line-clamp-2">
              {{ category.description }}
            </p>

            <!-- Category Stats -->
            <div class="grid grid-cols-2 gap-4 mt-auto">
              <div class="text-center p-3 bg-gray-50 rounded-lg">
                <div class="text-lg font-semibold text-gray-900">{{ category.total_expenses || 0 }}</div>
                <div class="text-xs text-gray-500">Total Despesas</div>
              </div>
              
              <div class="text-center p-3 bg-gray-50 rounded-lg">
                <div class="text-lg font-semibold text-gray-900">{{ formatMoney(category.total_amount || 0) }}</div>
                <div class="text-xs text-gray-500">Valor Total</div>
              </div>
            </div>

            <!-- Current Year Stats -->
            <div class="mt-3 pt-3 border-t border-gray-100">
              <div class="flex justify-between text-sm">
                <span class="text-gray-600">Este ano:</span>
                <span class="font-medium text-gray-900">
                  {{ category.current_year_expenses || 0 }} despesas
                  ({{ formatMoney(category.current_year_amount || 0) }})
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="categories.last_page > 1" class="mt-6 flex justify-center">
          <nav class="flex items-center gap-2">
            <button
              @click="changePage(categories.current_page - 1)"
              :disabled="categories.current_page <= 1"
              class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Anterior
            </button>
            
            <span class="px-4 py-2 text-sm text-gray-700">
              Página {{ categories.current_page }} de {{ categories.last_page }}
            </span>
            
            <button
              @click="changePage(categories.current_page + 1)"
              :disabled="categories.current_page >= categories.last_page"
              class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Próxima
            </button>
          </nav>
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <CreateEditCategoryModal
      v-if="showModal"
      :category="selectedCategory"
      @close="closeModal"
      @saved="handleCategorySaved"
    />

    <!-- View Category Modal -->
    <ViewCategoryModal
      v-if="showViewModal"
      :category="selectedCategory"
      @close="closeViewModal"
      @edit="editCategory"
    />

    <!-- Confirmation Modal -->
    <ConfirmationModal
      v-if="showDeleteModal"
      title="Excluir Categoria"
      :message="`Tem certeza que deseja excluir a categoria '${categoryToDelete?.name}'?`"
      confirm-text="Excluir"
      confirm-class="bg-red-600 hover:bg-red-700"
      @confirm="deleteCategory"
      @cancel="cancelDelete"
    />
  </div>
</template>

<script>
import { ref, reactive, onMounted } from 'vue'
import { useToast } from '@/composables/useToast'
import { useFormatting } from '@/composables/useFormatting'

import CreateEditCategoryModal from '@/components/admin/expenses/CreateEditCategoryModal.vue'
import ViewCategoryModal from '@/components/admin/expenses/ViewCategoryModal.vue'
import ConfirmationModal from '@/components/ConfirmationModal.vue'

export default {
  name: 'IndexExpenseCategory',
  components: {
    CreateEditCategoryModal,
    ViewCategoryModal,
    ConfirmationModal
  },

  setup() {
    const { showToast } = useToast()
    const { formatMoney } = useFormatting()

    // State
    const loading = ref(true)
    const categories = ref({ data: [] })
    const showModal = ref(false)
    const showViewModal = ref(false)
    const showDeleteModal = ref(false)
    const selectedCategory = ref(null)
    const categoryToDelete = ref(null)
    const openDropdownId = ref(null)

    // Filters
    const filters = reactive({
      query: '',
      status: ''
    })

    // Methods
    const loadCategories = async (page = 1) => {
      try {
        loading.value = true
        
        const params = new URLSearchParams({
          page: page.toString(),
          ...Object.fromEntries(
            Object.entries(filters).filter(([_, value]) => value !== '')
          )
        })

        const response = await fetch(`/expense-categories?${params}`)
        
        if (!response.ok) throw new Error('Erro ao carregar categorias')
        
        categories.value = await response.json()
      } catch (error) {
        console.error('Erro ao carregar categorias:', error)
        showToast('Erro ao carregar categorias', 'error')
      } finally {
        loading.value = false
      }
    }

    const handleSearch = (() => {
      let timeoutId
      return () => {
        clearTimeout(timeoutId)
        timeoutId = setTimeout(() => {
          loadCategories()
        }, 300)
      }
    })()

    const clearFilters = () => {
      filters.query = ''
      filters.status = ''
      loadCategories()
    }

    const changePage = (page) => {
      if (page >= 1 && page <= categories.value.last_page) {
        loadCategories(page)
      }
    }

    const openCreateModal = () => {
      selectedCategory.value = null
      showModal.value = true
    }

    const editCategory = (category) => {
      selectedCategory.value = { ...category }
      showModal.value = true
      closeDropdown()
    }

    const viewCategory = (category) => {
      selectedCategory.value = category
      showViewModal.value = true
      closeDropdown()
    }

    const closeModal = () => {
      showModal.value = false
      selectedCategory.value = null
    }

    const closeViewModal = () => {
      showViewModal.value = false
      selectedCategory.value = null
    }

    const handleCategorySaved = () => {
      closeModal()
      loadCategories()
    }

    const toggleDropdown = (categoryId) => {
      openDropdownId.value = openDropdownId.value === categoryId ? null : categoryId
    }

    const closeDropdown = () => {
      openDropdownId.value = null
    }

    const toggleCategoryStatus = async (category) => {
      try {
        closeDropdown()
        
        const response = await fetch(`/expense-categories/${category.id}/toggle-status`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          }
        })

        if (!response.ok) throw new Error('Erro ao alterar status da categoria')

        const result = await response.json()
        showToast(result.message)
        loadCategories()
      } catch (error) {
        console.error('Erro ao alterar status:', error)
        showToast('Erro ao alterar status da categoria', 'error')
      }
    }

    const confirmDelete = (category) => {
      if (category.total_expenses > 0) {
        showToast('Não é possível excluir uma categoria que possui despesas associadas', 'error')
        return
      }
      
      categoryToDelete.value = category
      showDeleteModal.value = true
      closeDropdown()
    }

    const cancelDelete = () => {
      showDeleteModal.value = false
      categoryToDelete.value = null
    }

    const deleteCategory = async () => {
      try {
        const response = await fetch(`/expense-categories/${categoryToDelete.value.id}`, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          }
        })

        if (!response.ok) {
          const error = await response.json()
          throw new Error(error.message || 'Erro ao excluir categoria')
        }

        const result = await response.json()
        showToast(result.message)
        loadCategories()
        cancelDelete()
      } catch (error) {
        console.error('Erro ao excluir categoria:', error)
        showToast(error.message || 'Erro ao excluir categoria', 'error')
      }
    }

    // Click outside directive
    const vClickOutside = {
      beforeMount(el, binding) {
        el.clickOutsideEvent = (event) => {
          if (!(el === event.target || el.contains(event.target))) {
            binding.value()
          }
        }
        document.addEventListener('click', el.clickOutsideEvent)
      },
      unmounted(el) {
        document.removeEventListener('click', el.clickOutsideEvent)
      }
    }

    // Lifecycle
    onMounted(() => {
      loadCategories()
    })

    return {
      // State
      loading,
      categories,
      showModal,
      showViewModal,
      showDeleteModal,
      selectedCategory,
      categoryToDelete,
      openDropdownId,
      filters,

      // Methods
      loadCategories,
      handleSearch,
      clearFilters,
      changePage,
      openCreateModal,
      editCategory,
      viewCategory,
      closeModal,
      closeViewModal,
      handleCategorySaved,
      toggleDropdown,
      closeDropdown,
      toggleCategoryStatus,
      confirmDelete,
      cancelDelete,
      deleteCategory,
      formatMoney,

      // Directives
      vClickOutside
    }
  }
}
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>