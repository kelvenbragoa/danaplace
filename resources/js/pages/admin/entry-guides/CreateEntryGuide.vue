<template>
    <div class="container-fluid">
        <!-- Header -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Nova Guia de Entrada</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item">
                                <router-link to="/admin/dashboard">Dashboard</router-link>
                            </li>
                            <li class="breadcrumb-item">
                                <router-link to="/admin/entry-guides">Guias de Entrada</router-link>
                            </li>
                            <li class="breadcrumb-item active">Nova Guia</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Form -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Dados da Guia de Entrada</h4>
                    </div>
                    <div class="card-body">
                        <form @submit.prevent="submitForm">
                            <div class="row">
                                <!-- Dados do Visitante -->
                                <div class="col-md-6">
                                    <div class="card h-100">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">Dados do Visitante</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label class="form-label">Nome do Visitante *</label>
                                                <input 
                                                    type="text" 
                                                    class="form-control" 
                                                    v-model="form.visitor_name" 
                                                    :class="{ 'is-invalid': errors.visitor_name }"
                                                    required
                                                >
                                                <div v-if="errors.visitor_name" class="invalid-feedback">
                                                    {{ errors.visitor_name[0] }}
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Documento *</label>
                                                <input 
                                                    type="text" 
                                                    class="form-control" 
                                                    v-model="form.visitor_document" 
                                                    :class="{ 'is-invalid': errors.visitor_document }"
                                                    placeholder="BI, Passaporte..."
                                                    required
                                                >
                                                <div v-if="errors.visitor_document" class="invalid-feedback">
                                                    {{ errors.visitor_document[0] }}
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Telefone</label>
                                                <input 
                                                    type="text" 
                                                    class="form-control" 
                                                    v-model="form.visitor_phone" 
                                                    :class="{ 'is-invalid': errors.visitor_phone }"
                                                    placeholder="(00) 00000-0000"
                                                >
                                                <div v-if="errors.visitor_phone" class="invalid-feedback">
                                                    {{ errors.visitor_phone[0] }}
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Empresa/Origem</label>
                                                <input 
                                                    type="text" 
                                                    class="form-control" 
                                                    v-model="form.visitor_company" 
                                                    :class="{ 'is-invalid': errors.visitor_company }"
                                                >
                                                <div v-if="errors.visitor_company" class="invalid-feedback">
                                                    {{ errors.visitor_company[0] }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Dados do Anfitrião -->
                                <div class="col-md-6">
                                    <div class="card h-100">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">Dados do Anfitrião</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label class="form-label">Nome do Anfitrião *</label>
                                                <input 
                                                    type="text" 
                                                    class="form-control" 
                                                    v-model="form.host_name" 
                                                    :class="{ 'is-invalid': errors.host_name }"
                                                    required
                                                >
                                                <div v-if="errors.host_name" class="invalid-feedback">
                                                    {{ errors.host_name[0] }}
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Telefone do Anfitrião</label>
                                                <input 
                                                    type="text" 
                                                    class="form-control" 
                                                    v-model="form.host_phone" 
                                                    :class="{ 'is-invalid': errors.host_phone }"
                                                    placeholder="(00) 00000-0000"
                                                >
                                                <div v-if="errors.host_phone" class="invalid-feedback">
                                                    {{ errors.host_phone[0] }}
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Destino *</label>
                                                <select 
                                                    class="form-select" 
                                                    v-model="form.destination_id" 
                                                    :class="{ 'is-invalid': errors.destination_id }"
                                                    required
                                                >
                                                    <option value="">Selecione o destino</option>
                                                    <option v-for="destination in destinations" :key="destination.id" :value="destination.id">
                                                        {{ destination.name }}
                                                    </option>
                                                </select>
                                                <div v-if="errors.destination_id" class="invalid-feedback">
                                                    {{ errors.destination_id[0] }}
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Local Específico</label>
                                                <input 
                                                    type="text" 
                                                    class="form-control" 
                                                    v-model="form.specific_location" 
                                                    :class="{ 'is-invalid': errors.specific_location }"
                                                    placeholder="Sala, andar, departamento..."
                                                >
                                                <div v-if="errors.specific_location" class="invalid-feedback">
                                                    {{ errors.specific_location[0] }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Período de Validade -->
                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">Período de Validade</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label class="form-label">Data/Hora Início *</label>
                                                    <input 
                                                        type="datetime-local" 
                                                        class="form-control" 
                                                        v-model="form.valid_from" 
                                                        :class="{ 'is-invalid': errors.valid_from }"
                                                        required
                                                    >
                                                    <div v-if="errors.valid_from" class="invalid-feedback">
                                                        {{ errors.valid_from[0] }}
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Data/Hora Fim *</label>
                                                    <input 
                                                        type="datetime-local" 
                                                        class="form-control" 
                                                        v-model="form.valid_until" 
                                                        :class="{ 'is-invalid': errors.valid_until }"
                                                        required
                                                    >
                                                    <div v-if="errors.valid_until" class="invalid-feedback">
                                                        {{ errors.valid_until[0] }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Observações -->
                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">Observações</h5>
                                        </div>
                                        <div class="card-body">
                                            <textarea 
                                                class="form-control" 
                                                rows="3" 
                                                v-model="form.purpose" 
                                                :class="{ 'is-invalid': errors.purpose }"
                                                placeholder="Motivo da visita, observações adicionais..."
                                            ></textarea>
                                            <div v-if="errors.purpose" class="invalid-feedback">
                                                {{ errors.purpose[0] }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Botões -->
                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="d-flex justify-content-end gap-2">
                                        <router-link to="/admin/entry-guides" class="btn btn-secondary">
                                            <vue-feather type="x" size="16" class="me-1"></vue-feather>
                                            Cancelar
                                        </router-link>
                                        <button type="submit" class="btn btn-primary" :disabled="loading">
                                            <vue-feather v-if="loading" type="loader" size="16" class="me-1 spin"></vue-feather>
                                            <vue-feather v-else type="save" size="16" class="me-1"></vue-feather>
                                            {{ loading ? 'Salvando...' : 'Salvar Guia' }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted, ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import { useToastr } from '../../../toastr'
import VueFeather from 'vue-feather'

const router = useRouter()
const toastr = useToastr()

const destinations = ref([])
const loading = ref(false)
const errors = ref({})

const form = reactive({
    visitor_name: '',
    visitor_document: '',
    visitor_phone: '',
    visitor_company: '',
    host_name: '',
    host_phone: '',
    destination_id: '',
    specific_location: '',
    valid_from: '',
    valid_until: '',
    purpose: ''
})

const loadDestinations = async () => {
    try {
        const response = await axios.get('/entry-guides/create')
        destinations.value = response.data.destinations || []
    } catch (error) {
        toastr.error('Erro ao carregar destinos')
        console.error(error)
    }
}

const submitForm = async () => {
    loading.value = true
    errors.value = {}
    
    try {
        const response = await axios.post('/entry-guides', form)
        toastr.success('Guia de entrada criada com sucesso!')
        router.push({ path: `/admin/entry-guides/${response.data.entryGuide.id}` })
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors || {}
            toastr.error('Verifique os campos obrigatórios')
        } else {
            toastr.error('Erro ao criar guia de entrada')
        }
        console.error(error)
    } finally {
        loading.value = false
    }
}

// Definir valores padrão para as datas
const setDefaultDates = () => {
    const now = new Date()
    const tomorrow = new Date(now.getTime() + 24 * 60 * 60 * 1000)
    
    // Formato YYYY-MM-DDTHH:mm para datetime-local
    form.valid_from = now.toISOString().slice(0, 16)
    form.valid_until = tomorrow.toISOString().slice(0, 16)
}

onMounted(() => {
    loadDestinations()
    setDefaultDates()
})
</script>

<style scoped>
.card-title {
    color: #495057;
    font-weight: 600;
}

.form-label {
    font-weight: 500;
    margin-bottom: 0.5rem;
}

.is-invalid {
    border-color: #dc3545;
}

.invalid-feedback {
    display: block;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.spin {
    animation: spin 1s linear infinite;
}

.gap-2 {
    gap: 0.5rem;
}
</style>