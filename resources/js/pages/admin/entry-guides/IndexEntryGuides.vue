<template>
    <div class="container-fluid">
        <!-- Header -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Guias de Entrada</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item">
                                <router-link to="/admin/dashboard">Dashboard</router-link>
                            </li>
                            <li class="breadcrumb-item active">Guias de Entrada</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Card -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-md-6">
                                <h4 class="card-title mb-0">Lista de Guias de Entrada</h4>
                            </div>
                            <div class="col-md-6">
                                <div class="float-end">
                                    <router-link to="/admin/entry-guides/create" class="btn btn-primary">
                                        <vue-feather type="plus" size="16" class="me-1"></vue-feather> Nova Guia
                                    </router-link>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <!-- Filters -->
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label>Pesquisar</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    placeholder="Nome do visitante, documento..."
                                    v-model="searchForm.search"
                                    @input="search"
                                >
                            </div>
                            <div class="col-md-3">
                                <label>Status</label>
                                <select class="form-select" v-model="searchForm.status" @change="search">
                                    <option value="">Todos</option>
                                    <option value="active">Ativa</option>
                                    <option value="used">Utilizada</option>
                                    <option value="expired">Expirada</option>
                                    <option value="cancelled">Cancelada</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>Destino</label>
                                <select class="form-select" v-model="searchForm.destination_id" @change="search">
                                    <option value="">Todos</option>
                                    <option v-for="destination in destinations" :key="destination.id" :value="destination.id">
                                        {{ destination.name }}
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>&nbsp;</label>
                                <div>
                                    <button class="btn btn-secondary" @click="clearFilters">Limpar</button>
                                </div>
                            </div>
                        </div>

                        <!-- Loading -->
                        <div v-if="loading" class="text-center p-3">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Carregando...</span>
                            </div>
                        </div>

                        <!-- Table -->
                        <div v-else class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nº Guia</th>
                                        <th>Visitante</th>
                                        <th>Documento</th>
                                        <th>Anfitrião</th>
                                        <th>Destino</th>
                                        <th>Validade</th>
                                        <th>Status</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="entryGuides.data.length === 0">
                                        <td colspan="8" class="text-center">Nenhuma guia de entrada encontrada</td>
                                    </tr>
                                    <tr v-for="guide in entryGuides.data" :key="guide.id">
                                        <td>{{ guide.guide_number }}</td>
                                        <td>{{ guide.visitor_name }}</td>
                                        <td>{{ guide.visitor_document }}</td>
                                        <td>{{ guide.host_name }}</td>
                                        <td>{{ guide.destination?.name }}</td>
                                        <td>
                                            <small>
                                                <strong>Início:</strong> {{ formatDate(guide.valid_from) }}<br>
                                                <strong>Fim:</strong> {{ formatDate(guide.valid_until) }}
                                            </small>
                                        </td>
                                        <td>
                                            <span :class="`badge bg-${getStatusColor(guide.status)}`">
                                                {{ getStatusText(guide.status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="dropdown" :class="{ 'show': dropdownOpen === guide.id }">
                                                <button 
                                                    class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                                    type="button" 
                                                    @click="toggleDropdown(guide.id)"
                                                    :aria-expanded="dropdownOpen === guide.id"
                                                >
                                                    Ações
                                                </button>
                                                <ul class="dropdown-menu" :class="{ 'show': dropdownOpen === guide.id }">
                                                    <li>
                                                        <router-link :to="`/admin/entry-guides/${guide.id}`" class="dropdown-item" @click="closeDropdown">
                                                            <vue-feather type="eye" size="14" class="me-1"></vue-feather> Visualizar
                                                        </router-link>
                                                    </li>
                                                    <li>
                                                        <button class="dropdown-item" @click="downloadPdf(guide.id); closeDropdown()">
                                                            <vue-feather type="download" size="14" class="me-1"></vue-feather> PDF
                                                        </button>
                                                    </li>
                                                    <li v-if="guide.status === 'active'">
                                                        <router-link :to="`/admin/entry-guides/${guide.id}/edit`" class="dropdown-item" @click="closeDropdown">
                                                            <vue-feather type="edit" size="14" class="me-1"></vue-feather> Editar
                                                        </router-link>
                                                    </li>
                                                    <li v-if="guide.status === 'active'">
                                                        <button class="dropdown-item" @click="cancel(guide.id); closeDropdown()">
                                                            <vue-feather type="x-circle" size="14" class="me-1"></vue-feather> Cancelar
                                                        </button>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <button class="dropdown-item text-danger" @click="destroy(guide.id); closeDropdown()">
                                                            <vue-feather type="trash" size="14" class="me-1"></vue-feather> Excluir
                                                        </button>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="row" v-if="entryGuides.total > 0">
                            <div class="col-sm-6">
                                <div>
                                    Mostrando {{ entryGuides.from || 0 }} a {{ entryGuides.to || 0 }} de {{ entryGuides.total }} registros
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="float-end">
                                    <Bootstrap4Pagination 
                                        :data="entryGuides" 
                                        @pagination-change-page="changePage"
                                        :limit="3"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted, onUnmounted, ref, reactive } from 'vue'
import axios from 'axios'
import { useToastr } from '../../../toastr'
import moment from 'moment'
import { debounce } from 'lodash'
import { Bootstrap4Pagination } from 'laravel-vue-pagination'
import VueFeather from 'vue-feather'

const toastr = useToastr()

const entryGuides = ref({ data: [] })
const destinations = ref([])
const loading = ref(false)
const dropdownOpen = ref(null)

const searchForm = reactive({
    search: '',
    status: '',
    destination_id: '',
    page: 1
})

const loadEntryGuides = async () => {
    loading.value = true
    try {
        const response = await axios.get('/entry-guides', { params: searchForm })
        entryGuides.value = response.data.entryGuides
        destinations.value = response.data.destinations || []
    } catch (error) {
        toastr.error('Erro ao carregar guias de entrada')
        console.error(error)
    } finally {
        loading.value = false
    }
}

const search = debounce(() => {
    searchForm.page = 1
    loadEntryGuides()
}, 300)

const clearFilters = () => {
    searchForm.search = ''
    searchForm.status = ''
    searchForm.destination_id = ''
    searchForm.page = 1
    loadEntryGuides()
}

const changePage = (page) => {
    searchForm.page = page
    loadEntryGuides()
}

const destroy = async (id) => {
    if (confirm('Tem certeza que deseja excluir esta guia de entrada?')) {
        try {
            await axios.delete(`/entry-guides/${id}`)
            toastr.success('Guia de entrada excluída com sucesso!')
            loadEntryGuides()
        } catch (error) {
            toastr.error('Erro ao excluir guia de entrada')
            console.error(error)
        }
    }
}

const cancel = async (id) => {
    if (confirm('Tem certeza que deseja cancelar esta guia de entrada?')) {
        try {
            await axios.patch(`/entry-guides/${id}/cancel`)
            toastr.success('Guia de entrada cancelada com sucesso!')
            loadEntryGuides()
        } catch (error) {
            toastr.error('Erro ao cancelar guia de entrada')
            console.error(error)
        }
    }
}

const downloadPdf = (id) => {
    window.open(`/entry-guides/${id}/pdf`, '_blank')
}

const formatDate = (date) => {
    return moment(date).format('DD/MM/YYYY HH:mm')
}

const getStatusColor = (status) => {
    const colors = {
        active: 'success',
        used: 'info',
        expired: 'warning',
        cancelled: 'danger'
    }
    return colors[status] || 'secondary'
}

const getStatusText = (status) => {
    const texts = {
        active: 'Ativa',
        used: 'Utilizada',
        expired: 'Expirada',
        cancelled: 'Cancelada'
    }
    return texts[status] || status
}

const toggleDropdown = (id) => {
    dropdownOpen.value = dropdownOpen.value === id ? null : id
}

const closeDropdown = () => {
    dropdownOpen.value = null
}

const handleClickOutside = (event) => {
    if (!event.target.closest('.dropdown')) {
        dropdownOpen.value = null
    }
}

onMounted(() => {
    loadEntryGuides()
    document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside)
})
</script>

<style scoped>
.table th {
    font-weight: 600;
    font-size: 0.875rem;
}

.card-title {
    color: #495057;
    font-weight: 600;
}

.badge {
    font-size: 0.75rem;
}

.dropdown-toggle::after {
    margin-left: 0.5rem;
}
</style>