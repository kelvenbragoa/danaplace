<template>
  <div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
      <div class="flex items-center gap-4 mb-4">
        <router-link
          :to="{ name: 'admin.expenses.show', params: { id: $route.params.id } }"
          class="p-2 hover:bg-gray-100 rounded-lg transition-colors"
        >
          <vue-feather type="arrow-left" class="w-5 h-5 text-gray-600" />
        </router-link>
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Editar Despesa</h1>
          <p class="text-gray-600">{{ form.title || 'Carregando...' }}</p>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-12">
      <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
      <p class="mt-2 text-gray-600">Carregando dados da despesa...</p>
    </div>

    <!-- Form -->
    <div v-else class="bg-white rounded-lg shadow-sm border">
      <form @submit.prevent="submitForm" class="p-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Left Column - Main Info -->
          <div class="lg:col-span-2 space-y-6">
            <!-- Basic Information -->
            <div class="border-b border-gray-200 pb-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Informações Básicas</h3>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Category -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Categoria *
                  </label>
                  <select
                    v-model="form.expense_category_id"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    :class="{ 'border-red-500': errors.expense_category_id }"
                  >
                    <option value="">Selecione uma categoria</option>
                    <option
                      v-for="category in expenseCategories"
                      :key="category.id"
                      :value="category.id"
                    >
                      {{ category.name }}
                    </option>
                  </select>
                  <p v-if="errors.expense_category_id" class="mt-1 text-sm text-red-600">
                    {{ errors.expense_category_id[0] }}
                  </p>
                </div>

                <!-- Priority -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Prioridade *
                  </label>
                  <select
                    v-model="form.priority"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    :class="{ 'border-red-500': errors.priority }"
                  >
                    <option
                      v-for="priority in priorities"
                      :key="priority.value"
                      :value="priority.value"
                    >
                      {{ priority.label }}
                    </option>
                  </select>
                  <p v-if="errors.priority" class="mt-1 text-sm text-red-600">
                    {{ errors.priority[0] }}
                  </p>
                </div>
              </div>

              <!-- Title -->
              <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Título da Despesa *
                </label>
                <input
                  v-model="form.title"
                  type="text"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                  :class="{ 'border-red-500': errors.title }"
                  placeholder="Ex: Manutenção do elevador, Limpeza das áreas comuns..."
                />
                <p v-if="errors.title" class="mt-1 text-sm text-red-600">
                  {{ errors.title[0] }}
                </p>
              </div>

              <!-- Description -->
              <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Descrição
                </label>
                <textarea
                  v-model="form.description"
                  rows="3"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                  :class="{ 'border-red-500': errors.description }"
                  placeholder="Detalhes sobre a despesa..."
                ></textarea>
                <p v-if="errors.description" class="mt-1 text-sm text-red-600">
                  {{ errors.description[0] }}
                </p>
              </div>
            </div>

            <!-- Financial Information -->
            <div class="border-b border-gray-200 pb-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Informações Financeiras</h3>
              
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Amount -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Valor *
                  </label>
                  <div class="relative">
                    <span class="absolute left-3 top-2 text-gray-500">R$</span>
                    <input
                      v-model="form.amount"
                      type="number"
                      step="0.01"
                      min="0"
                      required
                      class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                      :class="{ 'border-red-500': errors.amount }"
                      placeholder="0,00"
                    />
                  </div>
                  <p v-if="errors.amount" class="mt-1 text-sm text-red-600">
                    {{ errors.amount[0] }}
                  </p>
                </div>

                <!-- Expense Date -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Data da Despesa *
                  </label>
                  <input
                    v-model="form.expense_date"
                    type="date"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    :class="{ 'border-red-500': errors.expense_date }"
                  />
                  <p v-if="errors.expense_date" class="mt-1 text-sm text-red-600">
                    {{ errors.expense_date[0] }}
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
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <!-- Payment Method -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Método de Pagamento
                  </label>
                  <select
                    v-model="form.payment_method"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    :class="{ 'border-red-500': errors.payment_method }"
                  >
                    <option value="">Selecione um método</option>
                    <option
                      v-for="method in paymentMethods"
                      :key="method.value"
                      :value="method.value"
                    >
                      {{ method.label }}
                    </option>
                  </select>
                  <p v-if="errors.payment_method" class="mt-1 text-sm text-red-600">
                    {{ errors.payment_method[0] }}
                  </p>
                </div>

                <!-- Payment Date -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Data de Pagamento
                  </label>
                  <input
                    v-model="form.payment_date"
                    type="date"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    :class="{ 'border-red-500': errors.payment_date }"
                  />
                  <p v-if="errors.payment_date" class="mt-1 text-sm text-red-600">
                    {{ errors.payment_date[0] }}
                  </p>
                </div>
              </div>
            </div>

            <!-- Vendor Information -->
            <div class="border-b border-gray-200 pb-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Informações do Fornecedor</h3>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Vendor Name -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Nome do Fornecedor
                  </label>
                  <input
                    v-model="form.vendor_name"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    :class="{ 'border-red-500': errors.vendor_name }"
                    placeholder="Nome da empresa ou pessoa"
                  />
                  <p v-if="errors.vendor_name" class="mt-1 text-sm text-red-600">
                    {{ errors.vendor_name[0] }}
                  </p>
                </div>

                <!-- Vendor Contact -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Contato do Fornecedor
                  </label>
                  <input
                    v-model="form.vendor_contact"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    :class="{ 'border-red-500': errors.vendor_contact }"
                    placeholder="Telefone, email ou contato"
                  />
                  <p v-if="errors.vendor_contact" class="mt-1 text-sm text-red-600">
                    {{ errors.vendor_contact[0] }}
                  </p>
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <!-- Invoice Number -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Número da Fatura
                  </label>
                  <input
                    v-model="form.invoice_number"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    :class="{ 'border-red-500': errors.invoice_number }"
                    placeholder="Número da nota fiscal ou fatura"
                  />
                  <p v-if="errors.invoice_number" class="mt-1 text-sm text-red-600">
                    {{ errors.invoice_number[0] }}
                  </p>
                </div>

                <!-- Reference Number -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Número de Referência
                  </label>
                  <input
                    v-model="form.reference_number"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    :class="{ 'border-red-500': errors.reference_number }"
                    placeholder="Número de referência interno"
                  />
                  <p v-if="errors.reference_number" class="mt-1 text-sm text-red-600">
                    {{ errors.reference_number[0] }}
                  </p>
                </div>
              </div>
            </div>

            <!-- Additional Information -->
            <div>
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Informações Adicionais</h3>
              
              <!-- Notes -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Observações
                </label>
                <textarea
                  v-model="form.notes"
                  rows="3"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                  :class="{ 'border-red-500': errors.notes }"
                  placeholder="Observações importantes sobre esta despesa..."
                ></textarea>
                <p v-if="errors.notes" class="mt-1 text-sm text-red-600">
                  {{ errors.notes[0] }}
                </p>
              </div>
            </div>
          </div>

          <!-- Right Column - Recurring & Attachments -->
          <div class="space-y-6">
            <!-- Current Status -->
            <div class="bg-gray-50 rounded-lg p-4">
              <h4 class="font-semibold text-gray-900 mb-3">Status Atual</h4>
              <div class="space-y-2">
                <div class="flex justify-between">
                  <span class="text-gray-600">Status:</span>
                  <span
                    class="px-2 py-1 text-xs font-medium rounded-full"
                    :class="getStatusClass(originalExpense?.status)"
                  >
                    {{ getStatusLabel(originalExpense?.status) }}
                  </span>
                </div>
                <div v-if="!canEdit" class="text-sm text-amber-600 bg-amber-50 p-2 rounded">
                  <strong>Atenção:</strong> Apenas despesas pendentes ou rejeitadas podem ser editadas.
                </div>
              </div>
            </div>

            <!-- Recurring Configuration -->
            <div class="bg-gray-50 rounded-lg p-4">
              <h4 class="font-semibold text-gray-900 mb-4">Despesa Recorrente</h4>
              
              <!-- Recurring Toggle -->
              <div class="flex items-center justify-between mb-4">
                <label class="text-sm font-medium text-gray-700">
                  Despesa Recorrente
                </label>
                <button
                  type="button"
                  @click="form.recurring = !form.recurring"
                  class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                  :class="form.recurring ? 'bg-blue-600' : 'bg-gray-200'"
                >
                  <span
                    class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                    :class="form.recurring ? 'translate-x-5' : 'translate-x-0'"
                  ></span>
                </button>
              </div>

              <!-- Recurring Options -->
              <div v-if="form.recurring" class="space-y-4">
                <!-- Frequency -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Frequência *
                  </label>
                  <select
                    v-model="form.recurring_frequency"
                    :required="form.recurring"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    :class="{ 'border-red-500': errors.recurring_frequency }"
                  >
                    <option value="">Selecione a frequência</option>
                    <option
                      v-for="frequency in recurringFrequencies"
                      :key="frequency.value"
                      :value="frequency.value"
                    >
                      {{ frequency.label }}
                    </option>
                  </select>
                  <p v-if="errors.recurring_frequency" class="mt-1 text-sm text-red-600">
                    {{ errors.recurring_frequency[0] }}
                  </p>
                </div>

                <!-- Until Date -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Recorrer Até
                  </label>
                  <input
                    v-model="form.recurring_until"
                    type="date"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    :class="{ 'border-red-500': errors.recurring_until }"
                  />
                  <p v-if="errors.recurring_until" class="mt-1 text-sm text-red-600">
                    {{ errors.recurring_until[0] }}
                  </p>
                </div>
              </div>
            </div>

            <!-- Current Attachments -->
            <div v-if="currentAttachments.length > 0" class="bg-gray-50 rounded-lg p-4">
              <h4 class="font-semibold text-gray-900 mb-4">Anexos Atuais</h4>
              
              <div class="space-y-2">
                <div
                  v-for="attachment in currentAttachments"
                  :key="attachment.path"
                  class="flex items-center justify-between p-2 bg-white rounded border"
                >
                  <div class="flex items-center gap-2">
                    <DocumentIcon class="w-4 h-4 text-gray-500" />
                    <span class="text-sm text-gray-700 truncate">{{ attachment.name }}</span>
                  </div>
                  
                  <div class="flex gap-1">
                    <a
                      :href="attachment.url"
                      target="_blank"
                      class="p-1 text-blue-600 hover:text-blue-800 hover:bg-blue-100 rounded transition-colors"
                      title="Visualizar"
                    >
                      <EyeIcon class="w-3 h-3" />
                    </a>
                    
                    <button
                      type="button"
                      @click="removeAttachment(attachment.path)"
                      class="p-1 text-red-600 hover:text-red-800 hover:bg-red-100 rounded transition-colors"
                      title="Remover"
                    >
                      <XMarkIcon class="w-3 h-3" />
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- New Attachments -->
            <div class="bg-gray-50 rounded-lg p-4">
              <h4 class="font-semibold text-gray-900 mb-4">Adicionar Anexos</h4>
              
              <!-- File Upload -->
              <div
                @drop="handleDrop"
                @dragover.prevent
                @dragenter.prevent
                class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-blue-400 transition-colors cursor-pointer"
                :class="{ 'border-blue-400 bg-blue-50': isDragging }"
              >
                <input
                  ref="fileInput"
                  type="file"
                  multiple
                  accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif"
                  @change="handleFileSelect"
                  class="hidden"
                />
                
                <DocumentIcon class="w-8 h-8 text-gray-400 mx-auto mb-2" />
                
                <p class="text-sm text-gray-600 mb-2">
                  <button
                    type="button"
                    @click="$refs.fileInput.click()"
                    class="text-blue-600 hover:text-blue-800 font-medium"
                  >
                    Selecionar arquivos
                  </button>
                </p>
                
                <p class="text-xs text-gray-500">
                  PDF, DOC, DOCX, JPG, PNG, GIF (máx. 10MB cada)
                </p>
              </div>

              <!-- Selected Files -->
              <div v-if="selectedFiles.length > 0" class="mt-4 space-y-2">
                <div
                  v-for="(file, index) in selectedFiles"
                  :key="index"
                  class="flex items-center justify-between p-2 bg-white rounded border"
                >
                  <div class="flex items-center gap-2">
                    <DocumentIcon class="w-4 h-4 text-gray-500" />
                    <span class="text-sm text-gray-700 truncate">{{ file.name }}</span>
                    <span class="text-xs text-gray-500">({{ formatFileSize(file.size) }})</span>
                  </div>
                  <button
                    type="button"
                    @click="removeFile(index)"
                    class="text-red-600 hover:text-red-800"
                  >
                    <XMarkIcon class="w-4 h-4" />
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Form Actions -->
        <div class="flex gap-4 pt-6 border-t border-gray-200 mt-8">
          <button
            type="button"
            @click="$router.push({ name: 'admin.expenses.show', params: { id: $route.params.id } })"
            class="px-6 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors"
          >
            Cancelar
          </button>
          
          <button
            type="submit"
            :disabled="loading || saveLoading || !canEdit"
            class="flex-1 px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
          >
            <div v-if="saveLoading" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
            {{ saveLoading ? 'Salvando...' : 'Atualizar Despesa' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useToast } from '@/composables/useToast'

export default {
  name: 'EditExpense',


  setup() {
    const route = useRoute()
    const router = useRouter()
    const { showToast } = useToast()

    // State
    const loading = ref(true)
    const saveLoading = ref(false)
    const isDragging = ref(false)
    const selectedFiles = ref([])
    const currentAttachments = ref([])
    const originalExpense = ref(null)
    const expenseCategories = ref([])
    const priorities = ref([])
    const paymentMethods = ref([])
    const recurringFrequencies = ref([])
    const errors = ref({})

    // Form
    const form = reactive({
      expense_category_id: '',
      title: '',
      description: '',
      amount: '',
      expense_date: '',
      due_date: '',
      payment_date: '',
      payment_method: '',
      vendor_name: '',
      vendor_contact: '',
      invoice_number: '',
      reference_number: '',
      priority: 'medium',
      recurring: false,
      recurring_frequency: '',
      recurring_until: '',
      notes: ''
    })

    // Computed
    const canEdit = computed(() => originalExpense.value?.can_edit)

    // Methods
    const loadExpenseData = async () => {
      try {
        loading.value = true
        
        const response = await fetch(`/expenses/${route.params.id}/edit`)
        
        if (!response.ok) throw new Error('Erro ao carregar dados da despesa')
        
        const data = await response.json()
        
        // Populate form
        Object.keys(form).forEach(key => {
          if (data.expense[key] !== undefined) {
            form[key] = data.expense[key]
          }
        })

        // Set other data
        originalExpense.value = data.expense
        expenseCategories.value = data.expense_categories
        priorities.value = data.priorities
        paymentMethods.value = data.payment_methods
        recurringFrequencies.value = data.recurring_frequencies

        // Set current attachments
        if (data.expense.attachment_details) {
          currentAttachments.value = data.expense.attachment_details
        }
      } catch (error) {
        console.error('Erro ao carregar despesa:', error)
        showToast('Erro ao carregar dados da despesa', 'error')
        router.push({ name: 'admin.expenses.index' })
      } finally {
        loading.value = false
      }
    }

    const handleFileSelect = (event) => {
      const files = Array.from(event.target.files)
      addFiles(files)
    }

    const handleDrop = (event) => {
      event.preventDefault()
      isDragging.value = false
      
      const files = Array.from(event.dataTransfer.files)
      addFiles(files)
    }

    const addFiles = (files) => {
      const validTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'image/jpeg', 'image/png', 'image/gif']
      const maxSize = 10 * 1024 * 1024 // 10MB

      files.forEach(file => {
        if (!validTypes.includes(file.type)) {
          showToast(`Arquivo ${file.name} não é um tipo válido`, 'error')
          return
        }

        if (file.size > maxSize) {
          showToast(`Arquivo ${file.name} excede o tamanho máximo de 10MB`, 'error')
          return
        }

        selectedFiles.value.push(file)
      })
    }

    const removeFile = (index) => {
      selectedFiles.value.splice(index, 1)
    }

    const removeAttachment = async (attachmentPath) => {
      try {
        const response = await fetch(`/expenses/${route.params.id}/attachment`, {
          method: 'DELETE',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          },
          body: JSON.stringify({ attachment_path: attachmentPath })
        })

        if (!response.ok) throw new Error('Erro ao remover anexo')

        const result = await response.json()
        showToast(result.message)
        
        // Remove from current attachments
        currentAttachments.value = currentAttachments.value.filter(att => att.path !== attachmentPath)
      } catch (error) {
        console.error('Erro ao remover anexo:', error)
        showToast('Erro ao remover anexo', 'error')
      }
    }

    const formatFileSize = (bytes) => {
      if (bytes === 0) return '0 Bytes'
      const k = 1024
      const sizes = ['Bytes', 'KB', 'MB', 'GB']
      const i = Math.floor(Math.log(bytes) / Math.log(k))
      return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
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

    const submitForm = async () => {
      try {
        if (!canEdit.value) {
          showToast('Esta despesa não pode ser editada', 'error')
          return
        }

        saveLoading.value = true
        errors.value = {}

        const formData = new FormData()
        
        // Add form fields
        Object.keys(form).forEach(key => {
          if (form[key] !== '' && form[key] !== null) {
            formData.append(key, form[key])
          }
        })

        // Add files
        selectedFiles.value.forEach((file, index) => {
          formData.append(`attachments[${index}]`, file)
        })

        // Add method override for PUT
        formData.append('_method', 'PUT')

        const response = await fetch(`/expenses/${route.params.id}`, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          },
          body: formData
        })

        const result = await response.json()

        if (!response.ok) {
          if (response.status === 422) {
            errors.value = result.errors || {}
            return
          }
          throw new Error(result.message || 'Erro ao atualizar despesa')
        }

        showToast(result.message)
        router.push({ name: 'admin.expenses.show', params: { id: route.params.id } })
      } catch (error) {
        console.error('Erro ao atualizar despesa:', error)
        showToast(error.message || 'Erro ao atualizar despesa', 'error')
      } finally {
        saveLoading.value = false
      }
    }

    // Lifecycle
    onMounted(() => {
      loadExpenseData()
    })

    return {
      // State
      loading,
      saveLoading,
      isDragging,
      selectedFiles,
      currentAttachments,
      originalExpense,
      expenseCategories,
      priorities,
      paymentMethods,
      recurringFrequencies,
      errors,
      form,

      // Computed
      canEdit,

      // Methods
      handleFileSelect,
      handleDrop,
      removeFile,
      removeAttachment,
      formatFileSize,
      getStatusClass,
      getStatusLabel,
      submitForm
    }
  }
}
</script>

<style scoped>
.truncate {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
</style>