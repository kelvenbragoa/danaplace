<template>
    <div class="container-fluid p-0">
        <div class="row">
            <div class="col-12 col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col">
                                <h3 class="card-title mb-0">Processos Salariais</h3>
                            </div>
                            <div class="col-auto">
                                <router-link class="btn btn-primary" to="/admin/salary-processes/create">
                                    Novo Processo
                                </router-link>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <input v-model="searchQuery" @keyup.enter="searchData" type="text" class="form-control" placeholder="Pesquisar...">
                            </div>
                            <div class="col-md-6">
                                <button @click="searchData" class="btn btn-secondary">Pesquisar</button>
                                <button @click="clearSearch" class="btn btn-outline-secondary ml-2">Limpar</button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Título</th>
                                            <th>Mês/Ano</th>
                                            <th>Total Técnicos</th>
                                            <th>Total Valor</th>
                                            <th>Status</th>
                                            <th>Processado Por</th>
                                            <th>Data Criação</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody v-if="retrievedData.data && retrievedData.data.length > 0">
                                        <tr v-for="(actualData, index) in retrievedData.data" :key="actualData.id">
                                            <td>{{ actualData.id }}</td>
                                            <td>{{ actualData.title }}</td>
                                            <td>{{ formatMonthYear(actualData.month, actualData.year) }}</td>
                                            <td>{{ actualData.total_technicians }}</td>
                                            <td>{{ formatCurrency(actualData.total_amount) }}</td>
                                            <td>
                                                <span v-if="normalizeStatus(actualData.status) === 'pending'" class="badge bg-warning">
                                                    Pendente
                                                </span>
                                                <span v-if="normalizeStatus(actualData.status) === 'processed'" class="badge bg-info">
                                                    Processado
                                                </span>
                                                <span v-if="normalizeStatus(actualData.status) === 'approved'" class="badge bg-success">
                                                    Aprovado
                                                </span>
                                                <span v-if="normalizeStatus(actualData.status) === 'paid'" class="badge bg-primary">
                                                    Pago
                                                </span>
                                            </td>
                                            <td>{{ actualData.processed_by_user?.name || '-' }}</td>
                                            <td>{{ moment(actualData.created_at).format('DD-MM-YYYY H:mm') }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <router-link :to="'/admin/salary-processes/'+actualData.id" class="btn btn-sm btn-outline-primary">
                                                        <VueFeather type="eye" size="16"></VueFeather>
                                                    </router-link>
                                                    <router-link v-if="['pending','processed'].includes(normalizeStatus(actualData.status))" 
                                                        :to="'/admin/salary-processes/'+actualData.id+'/edit'" 
                                                        class="btn btn-sm btn-outline-secondary">
                                                        <VueFeather type="edit-2" size="16"></VueFeather>
                                                    </router-link>
                                                    <button v-if="normalizeStatus(actualData.status) === 'processed'" 
                                                        @click="approveProcess(actualData.id)" 
                                                        class="btn btn-sm btn-outline-success">
                                                        <VueFeather type="check" size="16"></VueFeather>
                                                    </button>
                                                    <button v-if="normalizeStatus(actualData.status) === 'approved'" 
                                                        @click="markAsPaid(actualData.id)" 
                                                        class="btn btn-sm btn-outline-primary">
                                                        <VueFeather type="dollar-sign" size="16"></VueFeather>
                                                    </button>
                                                    <button v-if="!['approved','paid'].includes(normalizeStatus(actualData.status))" 
                                                        @click="confirmDelete(actualData.id)" 
                                                        class="btn btn-sm btn-outline-danger">
                                                        <VueFeather type="trash-2" size="16"></VueFeather>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tbody v-else>
                                        <tr>
                                            <td colspan="9" align="center">Nenhum resultado encontrado</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Paginação -->
                        <div class="row" v-if="retrievedData.last_page > 1">
                            <div class="col-12">
                                <nav>
                                    <ul class="pagination justify-content-center">
                                        <li class="page-item" :class="{ disabled: retrievedData.current_page <= 1 }">
                                            <a class="page-link" @click="changePage(retrievedData.current_page - 1)">Anterior</a>
                                        </li>
                                        <li class="page-item" 
                                            v-for="page in visiblePages" 
                                            :key="page"
                                            :class="{ active: page === retrievedData.current_page }">
                                            <a class="page-link" @click="changePage(page)">{{ page }}</a>
                                        </li>
                                        <li class="page-item" :class="{ disabled: retrievedData.current_page >= retrievedData.last_page }">
                                            <a class="page-link" @click="changePage(retrievedData.current_page + 1)">Próximo</a>
                                        </li>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de Aprovação -->
        <div class="modal" id="approveModal" tabindex="-1" role="dialog" aria-labelledby="approveModalTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Aprovar Processo Salarial</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        Tem certeza que deseja aprovar este processo salarial? Após a aprovação, não será mais possível editar.
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button @click="executeApprove" type="button" class="btn btn-success" :disabled="loadingApprove">
                            <div v-if="loadingApprove" class="spinner-border spinner-border-sm" role="status"></div>
                            <span v-else>Aprovar Processo</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de Marcar como Pago -->
        <div class="modal" id="paidModal" tabindex="-1" role="dialog" aria-labelledby="paidModalTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Marcar como Pago</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        Tem certeza que deseja marcar este processo salarial como pago? Esta ação indica que os salários foram pagos aos técnicos.
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button @click="executePaid" type="button" class="btn btn-primary" :disabled="loadingPaid">
                            <div v-if="loadingPaid" class="spinner-border spinner-border-sm" role="status"></div>
                            <span v-else>Marcar como Pago</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de Exclusão -->
        <div class="modal" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Excluir Processo Salarial</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        Deseja mesmo excluir este processo salarial? Ao apagar este item, irá apagar todos os registros relacionados a ele.
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button @click="executeDelete" type="button" class="btn btn-danger" :disabled="loadingDelete">
                            <div v-if="loadingDelete" class="spinner-border spinner-border-sm" role="status"></div>
                            <span v-else>Excluir Processo</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'
import moment from 'moment'
import VueFeather from 'vue-feather'
import {useToastr} from '../../../toastr';
const toastr = useToastr();

const retrievedData = ref({})
const searchQuery = ref('')
const loading = ref(false)
const loadingApprove = ref(false)
const loadingPaid = ref(false)
const loadingDelete = ref(false)
const selectedItemId = ref(null)



const visiblePages = computed(() => {
    const current = retrievedData.value.current_page || 1
    const last = retrievedData.value.last_page || 1
    const pages = []
    
    for (let i = Math.max(1, current - 2); i <= Math.min(last, current + 2); i++) {
        pages.push(i)
    }
    
    return pages
})

const getData = async (page = 1, query = '') => {
    loading.value = true
    try {
        const response = await axios.get('/salary-processes', {
            params: {
                page: page,
                query: query
            }
        })
        retrievedData.value = response.data
    } catch (error) {
        console.error('Erro ao buscar dados:', error)
        toastr.error('Erro ao carregar processos salariais')
    } finally {
        loading.value = false
    }
}

const searchData = () => {
    getData(1, searchQuery.value)
}

const clearSearch = () => {
    searchQuery.value = ''
    getData(1, '')
}

const changePage = (page) => {
    if (page >= 1 && page <= retrievedData.value.last_page) {
        getData(page, searchQuery.value)
    }
}

const approveProcess = (id) => {
    selectedItemId.value = id
    $('#approveModal').modal('show')
}

const executeApprove = async () => {
    loadingApprove.value = true
    try {
        await axios.post(`/salary-processes/${selectedItemId.value}/approve`)
        toastr.success('Processo salarial aprovado com sucesso!')
        $('#approveModal').modal('hide')
        
        getData(retrievedData.value.current_page, searchQuery.value)
    } catch (error) {
        toastr.error('Erro ao aprovar processo salarial')
    } finally {
        loadingApprove.value = false
    }
}

const markAsPaid = (id) => {
    selectedItemId.value = id
    $('#paidModal').modal('show')
}

const executePaid = async () => {
    loadingPaid.value = true
    try {
        await axios.post(`/salary-processes/${selectedItemId.value}/mark-as-paid`)
        toastr.success('Processo salarial marcado como pago!')
        $('#paidModal').modal('hide')
        getData(retrievedData.value.current_page, searchQuery.value)
    } catch (error) {
        toastr.error('Erro ao marcar processo como pago')
    } finally {
        loadingPaid.value = false
    }
}

const confirmDelete = (id) => {
    selectedItemId.value = id
    $('#deleteModal').modal('show')
}

const executeDelete = async () => {
    loadingDelete.value = true
    try {
        await axios.delete(`/salary-processes/${selectedItemId.value}`)
        toastr.success('Processo salarial excluído com sucesso!')
        $('#deleteModal').modal('hide')
        getData(retrievedData.value.current_page, searchQuery.value)
    } catch (error) {
        toastr.error('Erro ao excluir processo salarial')
    } finally {
        loadingDelete.value = false
    }
}

const formatMonthYear = (month, year) => {
    const monthNames = [
        'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
        'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'
    ]
    return `${monthNames[month - 1]} ${year}`
}


const formatCurrency = (value) => {
    return new Intl.NumberFormat('pt-MZ', {
        style: 'currency',
        currency: 'MZN'
    }).format(value)
}

// Função para normalizar o status
const normalizeStatus = (status) => {
    if (!status) return ''
    return String(status).toLowerCase().trim()
}

onMounted(() => {
    getData()
})
</script>