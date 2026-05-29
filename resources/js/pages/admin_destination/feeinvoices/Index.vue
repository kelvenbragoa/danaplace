<template>
  <div class="container-fluid">
    <div class="row">
      <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
          <h4 class="mb-sm-0 font-size-18">Minhas Faturas de Taxas</h4>
          <div class="page-title-right">
            <ol class="breadcrumb m-0">
              <li class="breadcrumb-item"><a href="javascript: void(0);">Dashboard</a></li>
              <li class="breadcrumb-item active">Faturas de Taxas</li>
            </ol>
          </div>
        </div>
      </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4" v-if="statistics">
      <div class="col-xl-3 col-md-6">
        <div class="card mini-stats-wid">
          <div class="card-body">
            <div class="d-flex">
              <div class="flex-grow-1">
                <p class="text-muted fw-medium">Total de Faturas</p>
                <h4 class="mb-0">{{ statistics.total_invoices }}</h4>
              </div>
              <div class="flex-shrink-0 align-self-center">
                <div class="mini-stat-icon avatar-sm rounded-circle bg-primary">
                  <span class="avatar-title">
                    <vue-feather type="file-text" size="24"></vue-feather>
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-xl-3 col-md-6">
        <div class="card mini-stats-wid">
          <div class="card-body">
            <div class="d-flex">
              <div class="flex-grow-1">
                <p class="text-muted fw-medium">Faturas Pagas</p>
                <h4 class="mb-0 text-success">{{ statistics.paid_invoices }}</h4>
              </div>
              <div class="flex-shrink-0 align-self-center">
                <div class="mini-stat-icon avatar-sm rounded-circle bg-success">
                  <span class="avatar-title">
                    <vue-feather type="check-circle" size="24"></vue-feather>
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-xl-3 col-md-6">
        <div class="card mini-stats-wid">
          <div class="card-body">
            <div class="d-flex">
              <div class="flex-grow-1">
                <p class="text-muted fw-medium">Faturas Pendentes</p>
                <h4 class="mb-0 text-warning">{{ statistics.pending_invoices }}</h4>
              </div>
              <div class="flex-shrink-0 align-self-center">
                <div class="mini-stat-icon avatar-sm rounded-circle bg-warning">
                  <span class="avatar-title">
                    <vue-feather type="clock" size="24"></vue-feather>
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-xl-3 col-md-6">
        <div class="card mini-stats-wid">
          <div class="card-body">
            <div class="d-flex">
              <div class="flex-grow-1">
                <p class="text-muted fw-medium">Valor Total</p>
                <h4 class="mb-0">{{ formatCurrency(statistics.total_amount) }}</h4>
              </div>
              <div class="flex-shrink-0 align-self-center">
                <div class="mini-stat-icon avatar-sm rounded-circle bg-info">
                  <span class="avatar-title">
                    <vue-feather type="dollar-sign" size="24"></vue-feather>
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="row mb-3">
      <div class="col-12">
        <div class="card">
          <div class="card-body">
            <form @submit.prevent="loadInvoices" class="row g-3">
              <div class="col-md-3">
                <label class="form-label">Buscar</label>
                <input type="text" v-model="filters.search" class="form-control" placeholder="Número da fatura...">
              </div>
              <div class="col-md-2">
                <label class="form-label">Status</label>
                <select v-model="filters.status" class="form-select">
                  <option value="">Todos</option>
                  <option value="draft">Rascunho</option>
                  <option value="issued">Emitida</option>
                  <option value="paid">Paga</option>
                  <option value="partially_paid">Parcialmente Paga</option>
                  <option value="overdue">Vencida</option>
                  <option value="cancelled">Cancelada</option>
                </select>
              </div>
              <div class="col-md-2">
                <label class="form-label">Mês</label>
                <select v-model="filters.period_month" class="form-select">
                  <option value="">Todos</option>
                  <option v-for="month in months" :key="month.value" :value="month.value">
                    {{ month.label }}
                  </option>
                </select>
              </div>
              <div class="col-md-2">
                <label class="form-label">Ano</label>
                <select v-model="filters.period_year" class="form-select">
                  <option value="">Todos</option>
                  <option v-for="year in years" :key="year" :value="year">{{ year }}</option>
                </select>
              </div>
              <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <div class="d-flex gap-2">
                  <button type="submit" class="btn btn-primary" :disabled="loading">
                    <vue-feather type="search" size="16" class="me-1"></vue-feather>
                    {{ loading ? 'Buscando...' : 'Buscar' }}
                  </button>
                  <button type="button" @click="clearFilters" class="btn btn-secondary">
                    <vue-feather type="x" size="16" class="me-1"></vue-feather>
                    Limpar
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Invoices Table -->
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-body">
            <h4 class="card-title mb-4">Lista de Faturas</h4>
            
            <div v-if="loading" class="text-center py-4">
              <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Carregando...</span>
              </div>
              <p class="mt-2">Carregando faturas...</p>
            </div>

            <div v-else-if="invoices.data && invoices.data.length === 0" class="text-center py-4">
              <vue-feather type="file-text" size="48" class="text-muted"></vue-feather>
              <p class="mt-2 text-muted">Nenhuma fatura encontrada</p>
            </div>

            <div v-else class="table-responsive">
              <table class="table table-hover mb-0">
                <thead class="table-light">
                  <tr>
                    <th>Número</th>
                    <th>Período</th>
                    <th>Status</th>
                    <th>Data Emissão</th>
                    <th>Data Vencimento</th>
                    <th>Valor Total</th>
                    <th>Valor Pago</th>
                    <th>Ações</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="invoice in invoices.data" :key="invoice.id">
                    <td>
                      <span class="fw-bold text-primary">{{ invoice.invoice_number }}</span>
                    </td>
                    <td>{{ invoice.period_description }}</td>
                    <td>
                      <span :class="getStatusBadgeClass(invoice.status)">
                        {{ getStatusText(invoice.status) }}
                      </span>
                    </td>
                    <td>{{ formatDate(invoice.issue_date) }}</td>
                    <td :class="{ 'text-danger fw-bold': invoice.is_overdue }">
                      {{ formatDate(invoice.due_date) }}
                      <small v-if="invoice.is_overdue" class="d-block text-danger">VENCIDA</small>
                    </td>
                    <td class="fw-bold">{{ formatCurrency(invoice.total_amount) }}</td>
                    <td class="text-success fw-bold">{{ formatCurrency(invoice.paid_amount) }}</td>
                    <td>
                      <div class="btn-group" role="group">
                        <button 
                          type="button" 
                          class="btn btn-sm btn-outline-primary"
                          @click="viewInvoice(invoice.id)"
                          title="Visualizar"
                        >
                          <vue-feather type="eye" size="16"></vue-feather>
                        </button>
                        <button 
                          type="button" 
                          class="btn btn-sm btn-outline-success"
                          @click="downloadPDF(invoice.id)"
                          title="Download PDF"
                        >
                          <vue-feather type="download" size="16"></vue-feather>
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Pagination -->
            <div v-if="invoices.data && invoices.data.length > 0" class="row mt-4">
              <div class="col-sm-6">
                <div class="text-muted">
                  Mostrando {{ invoices.from }} até {{ invoices.to }} de {{ invoices.total }} faturas
                </div>
              </div>
              <div class="col-sm-6">
                <nav>
                  <ul class="pagination justify-content-end mb-0">
                    <li class="page-item" :class="{ disabled: !invoices.prev_page_url }">
                      <button class="page-link" @click="goToPage(invoices.current_page - 1)" :disabled="!invoices.prev_page_url">
                        Anterior
                      </button>
                    </li>
                    <li 
                      v-for="page in getVisiblePages()" 
                      :key="page" 
                      class="page-item" 
                      :class="{ active: page === invoices.current_page }"
                    >
                      <button class="page-link" @click="goToPage(page)">{{ page }}</button>
                    </li>
                    <li class="page-item" :class="{ disabled: !invoices.next_page_url }">
                      <button class="page-link" @click="goToPage(invoices.current_page + 1)" :disabled="!invoices.next_page_url">
                        Próximo
                      </button>
                    </li>
                  </ul>
                </nav>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- View Invoice Modal -->
    <div class="modal fade" id="viewInvoiceModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-xl">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Detalhes da Fatura {{ selectedInvoice?.invoice_number }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div v-if="loadingInvoice" class="text-center py-4">
              <div class="spinner-border text-primary"></div>
              <p class="mt-2">Carregando detalhes...</p>
            </div>
            <div v-else-if="selectedInvoice">
              <!-- Invoice Details -->
              <div class="row mb-4">
                <div class="col-md-6">
                  <h6 class="text-muted mb-3">Informações Gerais</h6>
                  <p><strong>Período:</strong> {{ selectedInvoice.period_description }}</p>
                  <p><strong>Status:</strong> 
                    <span :class="getStatusBadgeClass(selectedInvoice.status)">
                      {{ getStatusText(selectedInvoice.status) }}
                    </span>
                  </p>
                  <p><strong>Criado por:</strong> {{ selectedInvoice.creator?.name || 'Sistema' }}</p>
                </div>
                <div class="col-md-6">
                  <h6 class="text-muted mb-3">Datas</h6>
                  <p><strong>Data de Emissão:</strong> {{ formatDate(selectedInvoice.issue_date) }}</p>
                  <p><strong>Data de Vencimento:</strong> {{ formatDate(selectedInvoice.due_date) }}</p>
                  <p><strong>Data de Criação:</strong> {{ formatDateTime(selectedInvoice.created_at) }}</p>
                </div>
              </div>

              <!-- Financial Summary -->
              <div class="row mb-4">
                <div class="col-12">
                  <h6 class="text-muted mb-3">Resumo Financeiro</h6>
                  <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                      <thead class="table-light">
                        <tr>
                          <th>Descrição</th>
                          <th>Quantidade</th>
                          <th>Valor</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr>
                          <td>Valor Total</td>
                          <td>{{ selectedInvoice.items?.length || 0 }} itens</td>
                          <td class="fw-bold text-primary">{{ formatCurrency(selectedInvoice.total_amount) }}</td>
                        </tr>
                        <tr>
                          <td>Valor Pago</td>
                          <td>{{ selectedInvoice.items?.filter(item => item.is_paid).length || 0 }} pagos</td>
                          <td class="fw-bold text-success">{{ formatCurrency(selectedInvoice.paid_amount) }}</td>
                        </tr>
                        <tr>
                          <td>Valor Pendente</td>
                          <td>{{ selectedInvoice.items?.filter(item => !item.is_paid).length || 0 }} pendentes</td>
                          <td class="fw-bold text-warning">{{ formatCurrency(selectedInvoice.remaining_amount) }}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>

              <!-- Items Detail -->
              <div class="row">
                <div class="col-12">
                  <h6 class="text-muted mb-3">Itens da Fatura</h6>
                  <div class="table-responsive">
                    <table class="table table-sm table-hover">
                      <thead class="table-dark">
                        <tr>
                          <th>Equipamento / Taxa</th>
                          <th>Cliente</th>
                          <th>Status</th>
                          <th class="text-end">Valor</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="item in selectedInvoice.items" :key="item.id">
                          <td>
                            <div>
                              <strong>{{ item.equipment?.name || 'N/A' }}</strong>
                              <br>
                              <small class="text-muted">{{ item.fee?.name || 'Taxa não especificada' }}</small>
                            </div>
                          </td>
                          <td>{{ item.equipment?.destination?.name || 'N/A' }}</td>
                          <td>
                            <span :class="item.is_paid ? 'badge bg-success' : 'badge bg-warning'">
                              {{ item.is_paid ? 'Pago' : 'Pendente' }}
                            </span>
                          </td>
                          <td class="text-end fw-bold">{{ formatCurrency(item.amount) }}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            <button 
              v-if="selectedInvoice" 
              type="button" 
              class="btn btn-success"
              @click="downloadPDF(selectedInvoice.id)"
            >
              <vue-feather type="download" size="16" class="me-1"></vue-feather>
              Download PDF
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { Modal } from 'bootstrap'
import VueFeather from 'vue-feather'

export default {
  name: 'DestinationFeeInvoices',
  components: {
    VueFeather
  },
  data() {
    return {
      invoices: { data: [] },
      statistics: null,
      selectedInvoice: null,
      loading: false,
      loadingInvoice: false,
      filters: {
        search: '',
        status: '',
        period_month: '',
        period_year: ''
      },
      months: [
        { value: 1, label: 'Janeiro' },
        { value: 2, label: 'Fevereiro' },
        { value: 3, label: 'Março' },
        { value: 4, label: 'Abril' },
        { value: 5, label: 'Maio' },
        { value: 6, label: 'Junho' },
        { value: 7, label: 'Julho' },
        { value: 8, label: 'Agosto' },
        { value: 9, label: 'Setembro' },
        { value: 10, label: 'Outubro' },
        { value: 11, label: 'Novembro' },
        { value: 12, label: 'Dezembro' }
      ],
      years: []
    }
  },
  mounted() {
    this.generateYears()
    this.loadStatistics()
    this.loadInvoices()
  },
  methods: {
    generateYears() {
      const currentYear = new Date().getFullYear()
      for (let year = currentYear; year >= currentYear - 5; year--) {
        this.years.push(year)
      }
    },
    
    async loadStatistics() {
      try {
        const response = await axios.get('/destination/fee-invoices/statistics')
        this.statistics = response.data
      } catch (error) {
        console.error('Erro ao carregar estatísticas:', error)
        this.$toast.error('Erro ao carregar estatísticas')
      }
    },

    async loadInvoices(page = 1) {
      this.loading = true
      try {
        const params = new URLSearchParams()
        if (page > 1) params.append('page', page)
        if (this.filters.search) params.append('search', this.filters.search)
        if (this.filters.status) params.append('status', this.filters.status)
        if (this.filters.period_month) params.append('period_month', this.filters.period_month)
        if (this.filters.period_year) params.append('period_year', this.filters.period_year)

        const response = await axios.get(`/destination/fee-invoices?${params}`)
        this.invoices = response.data
      } catch (error) {
        console.error('Erro ao carregar faturas:', error)
        this.$toast.error('Erro ao carregar faturas')
      } finally {
        this.loading = false
      }
    },

    async viewInvoice(id) {
      this.loadingInvoice = true
      try {
        const response = await axios.get(`/destination/fee-invoices/${id}`)
        this.selectedInvoice = response.data
        
        const modalElement = document.getElementById('viewInvoiceModal')
        const modal = new Modal(modalElement)
        modal.show()
      } catch (error) {
        console.error('Erro ao carregar fatura:', error)
        this.$toast.error('Erro ao carregar detalhes da fatura')
      } finally {
        this.loadingInvoice = false
      }
    },

    async downloadPDF(id) {
      try {
        const response = await axios.get(`/destination/fee-invoices/${id}/report`, {
          responseType: 'blob'
        })
        
        const blob = new Blob([response.data], { type: 'application/pdf' })
        const url = window.URL.createObjectURL(blob)
        const link = document.createElement('a')
        link.href = url
        link.download = `fatura-taxas-${id}.pdf`
        document.body.appendChild(link)
        link.click()
        document.body.removeChild(link)
        window.URL.revokeObjectURL(url)
        
        this.$toast.success('PDF baixado com sucesso!')
      } catch (error) {
        console.error('Erro ao baixar PDF:', error)
        this.$toast.error('Erro ao gerar PDF')
      }
    },

    clearFilters() {
      this.filters = {
        search: '',
        status: '',
        period_month: '',
        period_year: ''
      }
      this.loadInvoices()
    },

    goToPage(page) {
      if (page >= 1 && page <= this.invoices.last_page) {
        this.loadInvoices(page)
      }
    },

    getVisiblePages() {
      const current = this.invoices.current_page
      const last = this.invoices.last_page
      const pages = []
      
      let start = Math.max(1, current - 2)
      let end = Math.min(last, current + 2)
      
      if (end - start < 4) {
        if (start === 1) {
          end = Math.min(last, start + 4)
        } else {
          start = Math.max(1, end - 4)
        }
      }
      
      for (let i = start; i <= end; i++) {
        pages.push(i)
      }
      
      return pages
    },

    getStatusBadgeClass(status) {
      const classes = {
        'draft': 'badge bg-secondary',
        'issued': 'badge bg-primary',
        'paid': 'badge bg-success',
        'partially_paid': 'badge bg-warning',
        'overdue': 'badge bg-danger',
        'cancelled': 'badge bg-dark'
      }
      return classes[status] || 'badge bg-secondary'
    },

    getStatusText(status) {
      const texts = {
        'draft': 'Rascunho',
        'issued': 'Emitida',
        'paid': 'Paga',
        'partially_paid': 'Parcialmente Paga',
        'overdue': 'Vencida',
        'cancelled': 'Cancelada'
      }
      return texts[status] || 'Desconhecido'
    },

    formatCurrency(value) {
      if (!value) return '0,00 MZN'
      return new Intl.NumberFormat('pt-MZ', {
        style: 'currency',
        currency: 'MZN',
        minimumFractionDigits: 2
      }).format(value).replace('MZN', 'MZN')
    },

    formatDate(date) {
      if (!date) return '-'
      return new Date(date).toLocaleDateString('pt-PT')
    },

    formatDateTime(date) {
      if (!date) return '-'
      return new Date(date).toLocaleString('pt-PT')
    }
  }
}
</script>

<style scoped>
.mini-stats-wid {
  transition: transform 0.2s;
}

.mini-stats-wid:hover {
  transform: translateY(-2px);
}

.table th {
  font-weight: 600;
  font-size: 0.875rem;
}

.btn-group .btn {
  margin-right: 0;
}

.modal-xl {
  max-width: 1200px;
}
</style>