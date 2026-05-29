<script setup>
import {onMounted, ref} from 'vue';
import axios from 'axios';
import {useToastr} from '../../../toastr';
import { useRoute } from "vue-router";
import moment from 'moment';
import VueFeather from 'vue-feather';

const toastr = useToastr();
const loadingDiv = ref(true);
const vacationPlan = ref({});
const workingDays = ref(0);
const isFuture = ref(false);
const isActive = ref(false);
const isPast = ref(false);
const loading = ref({
    approve: false,
    reject: false,
    execute: false
});

const route = useRoute();
const id = route.params.id;

const getVacationPlan = async () => {
    try {
        const response = await axios.get(`/vacation-plans/${id}`);
        vacationPlan.value = response.data.vacation_plan;
        workingDays.value = response.data.working_days;
        isFuture.value = response.data.is_future;
        isActive.value = response.data.is_active;
        isPast.value = response.data.is_past;
        loadingDiv.value = false;
    } catch (error) {
        toastr.error('Erro ao carregar dados do plano de férias');
        loadingDiv.value = false;
    }
};

const getStatusLabel = (status) => {
    const labels = {
        'pending': 'Pendente',
        'approved': 'Aprovado',
        'rejected': 'Rejeitado',
        'executed': 'Executado'
    };
    return labels[status] || 'Desconhecido';
};

const getStatusBadge = (status) => {
    const badges = {
        'pending': 'bg-warning text-dark',
        'approved': 'bg-success',
        'rejected': 'bg-danger',
        'executed': 'bg-info'
    };
    return badges[status] || 'bg-secondary';
};

const getPeriodStatus = () => {
    if (isFuture.value) return { text: 'Futuro', class: 'text-info' };
    if (isActive.value) return { text: 'Em andamento', class: 'text-success' };
    if (isPast.value) return { text: 'Finalizado', class: 'text-muted' };
    return { text: '-', class: 'text-muted' };
};

const canApprove = () => {
    return vacationPlan.value.status === 'pending';
};

const canReject = () => {
    return vacationPlan.value.status === 'pending';
};

const canExecute = () => {
    return vacationPlan.value.status === 'approved';
};

const canEdit = () => {
    return vacationPlan.value.status === 'pending';
};

const approvePlan = () => {
    $('#approveModal').modal('show');
};

const confirmApproval = () => {
    loading.value.approve = true;
    
    const daysApproved = document.getElementById('daysApproved').value;
    
    axios.post(`/vacation-plans/${id}/approve`, {
        days_approved: daysApproved
    })
    .then(() => {
        getVacationPlan();
        $('#approveModal').modal('hide');
        toastr.success('Plano de férias aprovado com sucesso');
    })
    .catch((error) => {
        toastr.error(error.response?.data?.message || 'Erro ao aprovar plano de férias');
    })
    .finally(() => {
        loading.value.approve = false;
    });
};

const rejectPlan = () => {
    $('#rejectModal').modal('show');
};

const confirmRejection = () => {
    loading.value.reject = true;
    
    const notes = document.getElementById('rejectionNotes').value;
    
    axios.post(`/vacation-plans/${id}/reject`, {
        notes: notes
    })
    .then(() => {
        getVacationPlan();
        $('#rejectModal').modal('hide');
        toastr.success('Plano de férias rejeitado');
    })
    .catch((error) => {
        toastr.error(error.response?.data?.message || 'Erro ao rejeitar plano de férias');
    })
    .finally(() => {
        loading.value.reject = false;
    });
};

const executePlan = () => {
    $('#executeModal').modal('show');
};

const confirmExecution = () => {
    loading.value.execute = true;
    
    axios.post(`/vacation-plans/${id}/execute`)
    .then(() => {
        getVacationPlan();
        $('#executeModal').modal('hide');
        toastr.success('Plano de férias executado com sucesso');
    })
    .catch((error) => {
        toastr.error(error.response?.data?.message || 'Erro ao executar plano de férias');
    })
    .finally(() => {
        loading.value.execute = false;
    });
};

const formatDate = (date) => {
    return moment(date).format('DD/MM/YYYY');
};

const formatDateTime = (datetime) => {
    return moment(datetime).format('DD/MM/YYYY HH:mm');
};

onMounted(() => {
    getVacationPlan();
});
</script>

<template>
    <div v-if="!loadingDiv">
        <div class="d-flex justify-content-between w-100 flex-wrap">
            <div class="mb-3 mb-lg-0">
                <h1 class="h3 d-inline align-middle">Detalhes do Plano de Férias</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <router-link to="/admin">Dashboard</router-link>
                        </li>
                        <li class="breadcrumb-item">
                            <router-link to="/admin/vacation-plans">Planos de Férias</router-link>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Detalhes</li>
                    </ol>
                </nav>
            </div>

            <!-- Botões de Ação -->
            <div class="mb-3 mb-lg-0">
                <div class="btn-group" role="group">
                    <router-link v-if="canEdit()" :to="`/admin/vacation-plans/${id}/edit`" class="btn btn-outline-primary">
                        <vue-feather type="edit-2"></vue-feather>
                        Editar
                    </router-link>
                    
                    <button v-if="canApprove()" @click="approvePlan" class="btn btn-outline-success">
                        <vue-feather type="check"></vue-feather>
                        Aprovar
                    </button>
                    
                    <button v-if="canReject()" @click="rejectPlan" class="btn btn-outline-warning">
                        <vue-feather type="x"></vue-feather>
                        Rejeitar
                    </button>
                    
                    <button v-if="canExecute()" @click="executePlan" class="btn btn-outline-info" :disabled="loading.execute">
                        <div v-if="loading.execute" class="spinner-border spinner-border-sm me-2" role="status"></div>
                        <vue-feather v-else type="play"></vue-feather>
                        Executar
                    </button>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Informações Principais -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Informações do Plano de Férias</h5>
                            <span :class="'badge ' + getStatusBadge(vacationPlan.status)">
                                {{ getStatusLabel(vacationPlan.status) }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <!-- Dados do Técnico -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h6 class="border-bottom pb-2 mb-3">
                                    <vue-feather type="user" class="me-2"></vue-feather>
                                    Dados do Técnico
                                </h6>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Nome:</strong> {{ vacationPlan.technician?.name }}</p>
                                <p><strong>Código:</strong> {{ vacationPlan.technician?.code }}</p>
                                <p><strong>Cargo:</strong> {{ vacationPlan.technician?.position || '-' }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Departamento:</strong> {{ vacationPlan.technician?.department?.name || '-' }}</p>
                                <p><strong>Área:</strong> {{ vacationPlan.technician?.area?.name || '-' }}</p>
                            </div>
                        </div>

                        <!-- Dados do Plano -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h6 class="border-bottom pb-2 mb-3">
                                    <vue-feather type="calendar" class="me-2"></vue-feather>
                                    Dados do Plano de Férias
                                </h6>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Ano:</strong> {{ vacationPlan.year }}</p>
                                <p><strong>Data de Início:</strong> {{ formatDate(vacationPlan.start_date) }}</p>
                                <p><strong>Data de Fim:</strong> {{ formatDate(vacationPlan.end_date) }}</p>
                                <p><strong>Status do Período:</strong> 
                                    <span :class="getPeriodStatus().class">
                                        {{ getPeriodStatus().text }}
                                    </span>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Dias Solicitados:</strong> {{ vacationPlan.days_requested }} dias</p>
                                <p><strong>Dias Aprovados:</strong> {{ vacationPlan.days_approved || 'Não aprovado' }} 
                                    <span v-if="vacationPlan.days_approved">dias</span>
                                </p>
                                <p><strong>Dias Úteis no Período:</strong> {{ workingDays }} dias</p>
                                <p><strong>Substituto:</strong> {{ vacationPlan.replacement_technician?.name || 'Nenhum' }}</p>
                            </div>
                        </div>

                        <!-- Observações -->
                        <div class="row mb-4" v-if="vacationPlan.notes">
                            <div class="col-md-12">
                                <h6 class="border-bottom pb-2 mb-3">
                                    <vue-feather type="file-text" class="me-2"></vue-feather>
                                    Observações
                                </h6>
                                <div class="alert alert-light">
                                    {{ vacationPlan.notes }}
                                </div>
                            </div>
                        </div>

                        <!-- Informações de Processamento -->
                        <div class="row" v-if="vacationPlan.status !== 'pending'">
                            <div class="col-md-12">
                                <h6 class="border-bottom pb-2 mb-3">
                                    <vue-feather type="info" class="me-2"></vue-feather>
                                    Informações de Processamento
                                </h6>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Processado por:</strong> {{ vacationPlan.approved_by_user?.firstName || '-' }}</p>
                                <p><strong>Data de Processamento:</strong> 
                                    {{ vacationPlan.approved_at ? formatDateTime(vacationPlan.approved_at) : '-' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Painel Lateral -->
            <div class="col-md-4">
                <!-- Timeline de Status -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Timeline do Plano</h5>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            <!-- Solicitação -->
                            <div class="timeline-item active">
                                <div class="timeline-marker bg-success"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Solicitado</h6>
                                    <p class="text-muted mb-0">{{ formatDateTime(vacationPlan.created_at) }}</p>
                                    <small>por {{ vacationPlan.requested_by_user?.firstName }}</small>
                                </div>
                            </div>
                            
                            <!-- Processamento -->
                            <div class="timeline-item" :class="{ active: vacationPlan.status !== 'pending' }">
                                <div class="timeline-marker" :class="{
                                    'bg-success': vacationPlan.status === 'approved',
                                    'bg-danger': vacationPlan.status === 'rejected',
                                    'bg-info': vacationPlan.status === 'executed',
                                    'bg-muted': vacationPlan.status === 'pending'
                                }"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">{{ getStatusLabel(vacationPlan.status) }}</h6>
                                    <p class="text-muted mb-0">
                                        {{ vacationPlan.approved_at ? formatDateTime(vacationPlan.approved_at) : 'Aguardando' }}
                                    </p>
                                    <small v-if="vacationPlan.approved_by_user">
                                        por {{ vacationPlan.approved_by_user.firstName }}
                                    </small>
                                </div>
                            </div>
                            
                            <!-- Execução (apenas se executado) -->
                            <div v-if="vacationPlan.status === 'executed'" class="timeline-item active">
                                <div class="timeline-marker bg-info"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Executado</h6>
                                    <p class="text-muted mb-0">Férias em andamento</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Informações Adicionais -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Informações Adicionais</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Criado em:</span>
                            <span>{{ formatDate(vacationPlan.created_at) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Última atualização:</span>
                            <span>{{ formatDate(vacationPlan.updated_at) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>ID do Plano:</span>
                            <span>#{{ vacationPlan.id }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de Aprovação -->
        <div class="modal" id="approveModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Aprovar Plano de Férias</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Técnico: <strong>{{ vacationPlan.technician?.name }}</strong></p>
                        <p>Período: <strong>{{ formatDate(vacationPlan.start_date) }} - {{ formatDate(vacationPlan.end_date) }}</strong></p>
                        <p>Dias solicitados: <strong>{{ vacationPlan.days_requested }}</strong></p>
                        
                        <div class="form-group">
                            <label for="daysApproved">Dias aprovados:</label>
                            <input type="number" 
                                   id="daysApproved" 
                                   class="form-control" 
                                   :max="vacationPlan.days_requested"
                                   :value="vacationPlan.days_requested"
                                   min="1">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button @click="confirmApproval" type="button" class="btn btn-success" :disabled="loading.approve">
                            <div v-if="loading.approve" class="spinner-border spinner-border-sm" role="status"></div>
                            <span v-else>Aprovar</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de Rejeição -->
        <div class="modal" id="rejectModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Rejeitar Plano de Férias</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Técnico: <strong>{{ vacationPlan.technician?.name }}</strong></p>
                        
                        <div class="form-group">
                            <label for="rejectionNotes">Motivo da rejeição:</label>
                            <textarea id="rejectionNotes" 
                                      class="form-control" 
                                      rows="3"
                                      :value="vacationPlan.notes"
                                      placeholder="Descreva o motivo da rejeição..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button @click="confirmRejection" type="button" class="btn btn-warning" :disabled="loading.reject">
                            <div v-if="loading.reject" class="spinner-border spinner-border-sm" role="status"></div>
                            <span v-else>Rejeitar</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de Execução -->
        <div class="modal" id="executeModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Executar Plano de Férias</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <vue-feather type="alert-triangle" class="me-2"></vue-feather>
                            <strong>Atenção:</strong> Esta ação não pode ser desfeita.
                        </div>
                        
                        <p><strong>Técnico:</strong> {{ vacationPlan.technician?.name }}</p>
                        <p><strong>Período:</strong> {{ formatDate(vacationPlan.start_date) }} - {{ formatDate(vacationPlan.end_date) }}</p>
                        <p><strong>Dias aprovados:</strong> {{ vacationPlan.days_approved }} dias</p>
                        
                        <p class="mb-0">Deseja realmente executar este plano de férias? O técnico será marcado como em férias.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button @click="confirmExecution" type="button" class="btn btn-info" :disabled="loading.execute">
                            <div v-if="loading.execute" class="spinner-border spinner-border-sm me-2" role="status"></div>
                            <vue-feather v-else type="play" class="me-1"></vue-feather>
                            Executar Férias
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div v-else>
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-center">
                    <div class="spinner-border" role="status">
                        <span class="sr-only"></span>
                    </div>
                </div>
                <br>
                <div class="d-flex justify-content-center">
                    Carregando Dados...
                </div>
            </div> 
        </div>
    </div>
</template>

<style scoped>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-item:before {
    content: '';
    position: absolute;
    left: -22px;
    top: 8px;
    bottom: -12px;
    width: 2px;
    background-color: #e9ecef;
}

.timeline-item:last-child:before {
    display: none;
}

.timeline-marker {
    position: absolute;
    left: -30px;
    top: 6px;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    border: 2px solid white;
    box-shadow: 0 0 0 2px #e9ecef;
}

.timeline-item.active .timeline-marker {
    box-shadow: 0 0 0 2px currentColor;
}

.timeline-content {
    padding-left: 15px;
}

.border-bottom {
    border-bottom: 1px solid #e9ecef !important;
}

.me-1, .me-2 {
    margin-right: 0.25rem;
}

.me-2 {
    margin-right: 0.5rem;
}

.mb-0 {
    margin-bottom: 0 !important;
}

.mb-1 {
    margin-bottom: 0.25rem !important;
}

.mb-2 {
    margin-bottom: 0.5rem !important;
}

.mb-3 {
    margin-bottom: 1rem !important;
}

.mb-4 {
    margin-bottom: 1.5rem !important;
}

.pb-2 {
    padding-bottom: 0.5rem !important;
}
</style>