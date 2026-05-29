<template>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Editar Guia de Entrada</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item">
                                <router-link to="/admin/dashboard">Dashboard</router-link>
                            </li>
                            <li class="breadcrumb-item">
                                <router-link to="/admin/entry-guides">Guias de Entrada</router-link>
                            </li>
                            <li class="breadcrumb-item active">Editar</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Editar Guia Nº {{ form.guide_number }}</h4>
                    </div>
                    <div class="card-body">
                        <form @submit.prevent="submitForm">
                            <div class="row">
                                <div class="col-md-6">
                                    <h5>Dados do Visitante</h5>
                                    <div class="mb-3">
                                        <label class="form-label">Nome do Visitante *</label>
                                        <input type="text" class="form-control" v-model="form.visitor_name" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Documento *</label>
                                        <input type="text" class="form-control" v-model="form.visitor_document" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Telefone</label>
                                        <input type="text" class="form-control" v-model="form.visitor_phone">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h5>Dados do Anfitrião</h5>
                                    <div class="mb-3">
                                        <label class="form-label">Nome do Anfitrião *</label>
                                        <input type="text" class="form-control" v-model="form.host_name" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Telefone do Anfitrião</label>
                                        <input type="text" class="form-control" v-model="form.host_phone">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Destino *</label>
                                        <select class="form-select" v-model="form.destination_id" required>
                                            <option value="">Selecione o destino</option>
                                            <option v-for="destination in destinations" :key="destination.id" :value="destination.id">
                                                {{ destination.name }}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label">Data/Hora Início *</label>
                                    <input type="datetime-local" class="form-control" v-model="form.valid_from" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Data/Hora Fim *</label>
                                    <input type="datetime-local" class="form-control" v-model="form.valid_until" required>
                                </div>
                            </div>
                            
                            <div class="row mt-3">
                                <div class="col-12">
                                    <label class="form-label">Propósito da Visita</label>
                                    <textarea class="form-control" rows="3" v-model="form.purpose"></textarea>
                                </div>
                            </div>
                            
                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="d-flex justify-content-end gap-2">
                                        <router-link :to="`/admin/entry-guides/${$route.params.id}`" class="btn btn-secondary">
                                            Cancelar
                                        </router-link>
                                        <button type="submit" class="btn btn-primary" :disabled="loading">
                                            {{ loading ? 'Salvando...' : 'Atualizar' }}
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
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'
import { useToastr } from '../../../toastr'

const route = useRoute()
const router = useRouter()
const toastr = useToastr()

const destinations = ref([])
const loading = ref(false)

const form = reactive({
    guide_number: '',
    visitor_name: '',
    visitor_document: '',
    visitor_phone: '',
    host_name: '',
    host_phone: '',
    destination_id: '',
    valid_from: '',
    valid_until: '',
    purpose: ''
})

const loadData = async () => {
    try {
        const response = await axios.get(`/entry-guides/${route.params.id}/edit`)
        const data = response.data
        
        Object.assign(form, data.entryGuide)
        destinations.value = data.destinations
        
        // Converter datas para formato datetime-local
        if (form.valid_from) {
            form.valid_from = new Date(form.valid_from).toISOString().slice(0, 16)
        }
        if (form.valid_until) {
            form.valid_until = new Date(form.valid_until).toISOString().slice(0, 16)
        }
    } catch (error) {
        toastr.error('Erro ao carregar dados')
        console.error(error)
    }
}

const submitForm = async () => {
    loading.value = true
    try {
        await axios.put(`/entry-guides/${route.params.id}`, form)
        toastr.success('Guia de entrada atualizada com sucesso!')
        router.push({ path: `/admin/entry-guides/${route.params.id}` })
    } catch (error) {
        toastr.error('Erro ao atualizar guia de entrada')
        console.error(error)
    } finally {
        loading.value = false
    }
}

onMounted(() => {
    loadData()
})
</script>