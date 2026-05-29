<template>
    <div>
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">Detalhes da Falta</h1>
                <p class="text-muted">Visualize informações detalhadas sobre o registro de falta</p>
            </div>
            <div>
                <router-link 
                    v-if="absence?.status === 'pending'"
                    :to="{ name: 'admin.absences.edit', params: { id: route.params.id } }" 
                    class="btn btn-outline-primary me-2"
                >
                    <vue-feather type="edit-2" size="16" class="me-2"></vue-feather>
                    Editar
                </router-link>
                <router-link 
                    to="/admin/absences" 
                    class="btn btn-outline-secondary"
                >
                    <vue-feather type="arrow-left" size="16" class="me-2"></vue-feather>
                    Voltar
                </router-link>
            </div>
        </div>

        <!-- Loading -->
        <div v-if="loadingDiv" class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Carregando...</span>
            </div>
        </div>

        <!-- Content -->
        <div v-else class="row">
            <!-- Main Info -->
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Informações da Falta</h5>
                        <span 
                            class="badge fs-6"
                            :class="{
                                'bg-warning': absence?.status === 'pending',
                                'bg-success': absence?.status === 'approved',
                                'bg-danger': absence?.status === 'rejected'
                            }"
                        >
                            {{ getStatusLabel(absence?.status) }}
                        </span>
                    </div>
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-sm-3">Técnico:</dt>
                            <dd class="col-sm-9">{{ absence?.technician?.name }}</dd>
                            
                            <dt class="col-sm-3">Departamento:</dt>
                            <dd class="col-sm-9">{{ absence?.technician?.department?.name || '-' }}</dd>
                            
                            <dt class="col-sm-3">Área:</dt>
                            <dd class="col-sm-9">{{ absence?.technician?.area?.name || '-' }}</dd>
                            
                            <dt class="col-sm-3">Data:</dt>
                            <dd class="col-sm-9">
                                <strong>{{ formatDate(absence?.date) }}</strong>
                                <small class="text-muted ms-2">({{ getDayOfWeek(absence?.date) }})</small>
                            </dd>
                            
                            <dt class="col-sm-3">Tipo:</dt>
                            <dd class="col-sm-9">
                                <span 
                                    class="badge"
                                    :class="{
                                        'bg-danger': absence?.type === 'absence',
                                        'bg-warning': absence?.type === 'late_arrival',
                                        'bg-info': absence?.type === 'early_departure'
                                    }"
                                >
                                    {{ getTypeLabel(absence?.type) }}
                                </span>
                            </dd>
                            
                            <dt class="col-sm-3">Horas Perdidas:</dt>
                            <dd class="col-sm-9">
                                <strong class="text-danger">{{ absence?.hours_lost }}h</strong>
                            </dd>
                            
                            <dt class="col-sm-3">Motivo:</dt>
                            <dd class="col-sm-9">
                                <div class="bg-light p-3 rounded">
                                    {{ absence?.reason }}
                                </div>
                            </dd>
                        </dl>
                    </div>
                </div>

                <!-- Approval Section -->
                <div v-if="absence?.status === 'approved' || absence?.status === 'rejected'" class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <vue-feather 
                                :type="absence?.status === 'approved' ? 'check-circle' : 'x-circle'" 
                                size="16" 
                                class="me-2"
                                :class="{
                                    'text-success': absence?.status === 'approved',
                                    'text-danger': absence?.status === 'rejected'
                                }"
                            ></vue-feather>
                            {{ absence?.status === 'approved' ? 'Aprovação' : 'Rejeição' }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-sm-3">
                                {{ absence?.status === 'approved' ? 'Aprovado por:' : 'Rejeitado por:' }}
                            </dt>
                            <dd class="col-sm-9">{{ absence?.approved_by_user?.name || '-' }}</dd>
                            
                            <dt class="col-sm-3">Data:</dt>
                            <dd class="col-sm-9">{{ formatDateTime(absence?.approved_at) }}</dd>
                            
                            <dt v-if="absence?.observations" class="col-sm-3">Observações:</dt>
                            <dd v-if="absence?.observations" class="col-sm-9">
                                <div class="bg-light p-3 rounded">
                                    {{ absence?.observations }}
                                </div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-md-4">
                <!-- Actions -->
                <div v-if="absence?.status === 'pending'" class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Ações</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button 
                                @click="showApproveModal" 
                                class="btn btn-success"
                                :disabled="loading"
                            >
                                <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                                <vue-feather v-else type="check" size="16" class="me-2"></vue-feather>
                                Aprovar
                            </button>
                            <button 
                                @click="showRejectModal" 
                                class="btn btn-warning"
                                :disabled="loading"
                            >
                                <vue-feather type="x" size="16" class="me-2"></vue-feather>
                                Rejeitar
                            </button>
                            <hr>
                            <button 
                                @click="showDeleteModal" 
                                class="btn btn-outline-danger"
                                :disabled="loading"
                            >
                                <vue-feather type="trash-2" size="16" class="me-2"></vue-feather>
                                Excluir
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Registration Info -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Informações do Registro</h5>
                    </div>
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-sm-5">Registrado por:</dt>
                            <dd class="col-sm-7">{{ absence?.created_by_user?.name || '-' }}</dd>
                            
                            <dt class="col-sm-5">Data de Registro:</dt>
                            <dd class="col-sm-7">{{ formatDateTime(absence?.created_at) }}</dd>
                            
                            <dt v-if="absence?.updated_at !== absence?.created_at" class="col-sm-5">Última Atualização:</dt>
                            <dd v-if="absence?.updated_at !== absence?.created_at" class="col-sm-7">{{ formatDateTime(absence?.updated_at) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reject Modal -->
        <div 
            class="modal fade" 
            id="rejectModal" 
            tabindex="-1" 
            aria-labelledby="rejectModalLabel" 
            aria-hidden="true"
        >
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="rejectModalLabel">Rejeitar Falta</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form @submit.prevent="rejectAbsence">
                        <div class="modal-body">
                            <p>Você tem certeza que deseja rejeitar esta falta?</p>
                            
                            <div class="mb-3">
                                <label for="reject-observations" class="form-label">Motivo da Rejeição *</label>
                                <textarea 
                                    id="reject-observations"
                                    v-model="rejectObservations" 
                                    class="form-control" 
                                    rows="3" 
                                    required
                                    placeholder="Descreva o motivo da rejeição..."
                                ></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                Cancelar
                            </button>
                            <button type="submit" class="btn btn-warning" :disabled="loading">
                                <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                                Rejeitar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Approve Modal -->
        <div 
            class="modal fade" 
            id="approveModal" 
            tabindex="-1" 
            aria-labelledby="approveModalLabel" 
            aria-hidden="true"
        >
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="approveModalLabel">
                            <vue-feather type="check-circle" size="20" class="me-2 text-success"></vue-feather>
                            Aprovar Falta
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form @submit.prevent="approveAbsence">
                        <div class="modal-body">
                            <div class="alert alert-success" role="alert">
                                <vue-feather type="info" size="16" class="me-2"></vue-feather>
                                <strong>Confirmação de Aprovação</strong>
                            </div>
                            <p>Você tem certeza que deseja aprovar esta falta?</p>
                            <div class="bg-light p-3 rounded">
                                <p class="mb-1"><strong>Técnico:</strong> {{ absence?.technician?.name }}</p>
                                <p class="mb-1"><strong>Data:</strong> {{ formatDate(absence?.date) }}</p>
                                <p class="mb-1"><strong>Tipo:</strong> {{ getTypeLabel(absence?.type) }}</p>
                                <p class="mb-0"><strong>Horas Perdidas:</strong> {{ absence?.hours_lost }}h</p>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                Cancelar
                            </button>
                            <button type="submit" class="btn btn-success" :disabled="loading">
                                <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                                <vue-feather v-else type="check" size="16" class="me-2"></vue-feather>
                                Aprovar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Delete Modal -->
        <div 
            class="modal fade" 
            id="deleteModal" 
            tabindex="-1" 
            aria-labelledby="deleteModalLabel" 
            aria-hidden="true"
        >
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteModalLabel">
                            <vue-feather type="alert-triangle" size="20" class="me-2 text-danger"></vue-feather>
                            Excluir Falta
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form @submit.prevent="confirmDelete">
                        <div class="modal-body">
                            <div class="alert alert-danger" role="alert">
                                <vue-feather type="alert-triangle" size="16" class="me-2"></vue-feather>
                                <strong>Atenção! Esta ação não pode ser desfeita.</strong>
                            </div>
                            <p>Você tem certeza que deseja excluir esta falta?</p>
                            <div class="bg-light p-3 rounded">
                                <p class="mb-1"><strong>Técnico:</strong> {{ absence?.technician?.name }}</p>
                                <p class="mb-1"><strong>Data:</strong> {{ formatDate(absence?.date) }}</p>
                                <p class="mb-1"><strong>Tipo:</strong> {{ getTypeLabel(absence?.type) }}</p>
                                <p class="mb-0"><strong>Horas Perdidas:</strong> {{ absence?.hours_lost }}h</p>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                Cancelar
                            </button>
                            <button type="submit" class="btn btn-danger" :disabled="loading">
                                <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                                <vue-feather v-else type="trash-2" size="16" class="me-2"></vue-feather>
                                Excluir
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import VueFeather from 'vue-feather';
import {useToastr} from '../../../toastr';

const toastr = useToastr();
const route = useRoute();
const router = useRouter();

// Reactive data
const absence = ref(null);
const loadingDiv = ref(false);
const loading = ref(false);
const rejectObservations = ref('');

// Methods
const getData = async () => {
    loadingDiv.value = true;
    try {
        const response = await axios.get(`/absences/${route.params.id}`);
        absence.value = response.data.absence;
    } catch (error) {
        console.error('Erro ao buscar falta:', error);
        toastr.error('Erro ao carregar dados da falta');
        router.push('/admin/absences');
    } finally {
        loadingDiv.value = false;
    }
};

const approveAbsence = async () => {
    loading.value = true;
    try {
        await axios.post(`/absences/${route.params.id}/approve`);
        toastr.success('Falta aprovada com sucesso!');
        getData(); // Reload data
        
        const modal = bootstrap.Modal.getInstance(document.getElementById('approveModal'));
        modal.hide();
    } catch (error) {
        console.error(error);
        if (error.response?.data?.error) {
            toastr.error(error.response.data.error);
        } else {
            toastr.error('Erro ao aprovar falta');
        }
    } finally {
        loading.value = false;
    }
};

const showApproveModal = () => {
    const modal = new bootstrap.Modal(document.getElementById('approveModal'));
    modal.show();
};

const showRejectModal = () => {
    rejectObservations.value = '';
    const modal = new bootstrap.Modal(document.getElementById('rejectModal'));
    modal.show();
};

const rejectAbsence = async () => {
    loading.value = true;
    try {
        await axios.post(`/absences/${route.params.id}/reject`, {
            observations: rejectObservations.value
        });
        
        toastr.success('Falta rejeitada com sucesso!');
        getData(); // Reload data
        
        const modal = bootstrap.Modal.getInstance(document.getElementById('rejectModal'));
        modal.hide();
    } catch (error) {
        console.error(error);
        if (error.response?.data?.errors) {
            Object.values(error.response.data.errors).forEach(errorArray => {
                errorArray.forEach(message => toastr.error(message));
            });
        } else if (error.response?.data?.error) {
            toastr.error(error.response.data.error);
        } else {
            toastr.error('Erro ao rejeitar falta');
        }
    } finally {
        loading.value = false;
    }
};

const confirmDelete = async () => {
    loading.value = true;
    try {
        await axios.delete(`/absences/${route.params.id}`);
        toastr.success('Falta excluída com sucesso!');
        router.push('/admin/absences');
    } catch (error) {
        console.error(error);
        if (error.response?.data?.error) {
            toastr.error(error.response.data.error);
        } else {
            toastr.error('Erro ao excluir falta');
        }
    } finally {
        loading.value = false;
    }
};

const showDeleteModal = () => {
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
};

// Utility methods
const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('pt-BR');
};

const formatDateTime = (datetime) => {
    if (!datetime) return '-';
    return new Date(datetime).toLocaleString('pt-BR');
};

const getDayOfWeek = (date) => {
    if (!date) return '';
    const days = ['Domingo', 'Segunda-feira', 'Terça-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'Sábado'];
    return days[new Date(date).getDay()];
};

const getTypeLabel = (type) => {
    const labels = {
        'absence': 'Falta',
        'late_arrival': 'Atraso',
        'early_departure': 'Saída Antecipada'
    };
    return labels[type] || type;
};

const getStatusLabel = (status) => {
    const labels = {
        'pending': 'Pendente',
        'approved': 'Aprovado',
        'rejected': 'Rejeitado'
    };
    return labels[status] || status;
};

// Lifecycle
onMounted(() => {
    getData();
});
</script>

<style scoped>
.badge.fs-6 {
    font-size: 1rem !important;
}

dl.row dt {
    font-weight: 600;
    color: #6c757d;
}

.bg-light {
    background-color: #f8f9fa !important;
}

.card-header h5 {
    display: flex;
    align-items: center;
}
</style>