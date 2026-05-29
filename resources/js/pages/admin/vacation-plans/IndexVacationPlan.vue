<script setup>
import {onMounted, ref, reactive, watch} from 'vue';
import axios from 'axios';
import {useToastr} from '../../../toastr';
import moment from 'moment'
import {debounce} from 'lodash';
import { Bootstrap4Pagination } from 'laravel-vue-pagination';
import VueFeather from 'vue-feather';

const toastr = useToastr();
const searchQuery = ref(null);
const statusFilter = ref('');
const yearFilter = ref(new Date().getFullYear());
const technicianFilter = ref('');
const loadingDiv = ref(true);
const loadingButtonDelete = ref(false);
const loadingButtonApprove = ref(false);
const loadingButtonReject = ref(false);
const loadingButtonExecute = ref(false);

const retrievedData = ref({'data': []});
const technicians = ref([]);
const statistics = ref({});

let dataIdBeingDeleted = ref(null);
let planBeingProcessed = ref(null);

const years = ref([]);
for (let year = new Date().getFullYear(); year <= new Date().getFullYear() + 2; year++) {
    years.value.push(year);
}

const statusOptions = [
    { value: '', label: 'Todos os Status' },
    { value: 'pending', label: 'Pendente' },
    { value: 'approved', label: 'Aprovado' },
    { value: 'rejected', label: 'Rejeitado' },
    { value: 'executed', label: 'Executado' }
];

const getData = async (page = 1) => {
    axios.get(`/vacation-plans?page=${page}`, {
        params: {
            query: searchQuery.value,
            status: statusFilter.value,
            year: yearFilter.value,
            technician: technicianFilter.value
        }
    })
    .then((response) => {
        retrievedData.value = response.data;
        loadingDiv.value = false;
    })
    .catch(() => {
        toastr.error('Erro ao carregar dados');
        loadingDiv.value = false;
    });
};

const getTechnicians = async () => {
    try {
        const response = await axios.get('/technicians');
        technicians.value = response.data.data;
    } catch (error) {
        console.error('Erro ao carregar técnicos:', error);
    }
};

const getStatistics = async () => {
    try {
        const response = await axios.get('/vacation-plans-statistics');
        statistics.value = response.data;
    } catch (error) {
        console.error('Erro ao carregar estatísticas:', error);
    }
};

const confirmDeletion = (data) => {
    dataIdBeingDeleted.value = data.id;
    $('#deleteModal').modal('show');
};

const deleteData = () => {
    loadingButtonDelete.value = true;
    
    axios.delete(`/vacation-plans/${dataIdBeingDeleted.value}`)
    .then(() => {
        retrievedData.value.data = retrievedData.value.data.filter(data => data.id !== dataIdBeingDeleted.value); 
        $('#deleteModal').modal('hide');
        toastr.success('Plano de férias removido com sucesso');
        getStatistics();
    })
    .catch((error) => {
        toastr.error(error.response?.data?.message || 'Erro ao remover plano de férias');
    })
    .finally(() => {
        loadingButtonDelete.value = false;
    });
};

const approvePlan = (plan) => {
    planBeingProcessed.value = plan;
    $('#approveModal').modal('show');
};

const confirmApproval = () => {
    loadingButtonApprove.value = true;
    
    const daysApproved = document.getElementById('daysApproved').value;
    
    axios.post(`/vacation-plans/${planBeingProcessed.value.id}/approve`, {
        days_approved: daysApproved
    })
    .then(() => {
        getData();
        $('#approveModal').modal('hide');
        toastr.success('Plano de férias aprovado com sucesso');
        getStatistics();
    })
    .catch((error) => {
        toastr.error(error.response?.data?.message || 'Erro ao aprovar plano de férias');
    })
    .finally(() => {
        loadingButtonApprove.value = false;
    });
};

const rejectPlan = (plan) => {
    planBeingProcessed.value = plan;
    $('#rejectModal').modal('show');
};

const confirmRejection = () => {
    loadingButtonReject.value = true;
    
    const notes = document.getElementById('rejectionNotes').value;
    
    axios.post(`/vacation-plans/${planBeingProcessed.value.id}/reject`, {
        notes: notes
    })
    .then(() => {
        getData();
        $('#rejectModal').modal('hide');
        toastr.success('Plano de férias rejeitado');
        getStatistics();
    })
    .catch((error) => {
        toastr.error(error.response?.data?.message || 'Erro ao rejeitar plano de férias');
    })
    .finally(() => {
        loadingButtonReject.value = false;
    });
};

const executePlan = (plan) => {
    planBeingProcessed.value = plan;
    $('#executeModal').modal('show');
};

const confirmExecution = () => {
    loadingButtonExecute.value = true;
    
    axios.post(`/vacation-plans/${planBeingProcessed.value.id}/execute`)
    .then(() => {
        getData();
        $('#executeModal').modal('hide');
        toastr.success('Plano de férias executado com sucesso');
        getStatistics();
    })
    .catch((error) => {
        toastr.error(error.response?.data?.message || 'Erro ao executar plano de férias');
    })
    .finally(() => {
        loadingButtonExecute.value = false;
    });
};

const getStatusBadge = (status) => {
    const badges = {
        'pending': 'bg-warning',
        'approved': 'bg-success',
        'rejected': 'bg-danger',
        'executed': 'bg-info'
    };
    return badges[status] || 'bg-secondary';
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

const canApprove = (plan) => {
    return plan.status === 'pending';
};

const canReject = (plan) => {
    return plan.status === 'pending';
};

const canExecute = (plan) => {
    return plan.status === 'approved';
};

const canEdit = (plan) => {
    return plan.status === 'pending';
};

const canDelete = (plan) => {
    return plan.status !== 'executed';
};

watch(searchQuery, debounce(() => {
    getData();
}, 300));

watch([statusFilter, yearFilter, technicianFilter], () => {
    getData();
});

onMounted(() => {
    getData();
    getTechnicians();
    getStatistics();
});
</script>

<template>
    <div v-if="!loadingDiv">
        <h1 class="h3 mb-3">Planos de Férias</h1>

        <!-- Estatísticas -->
        <div class="row mb-4">
            <div class="col-xl-3 col-sm-6">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col mt-0">
                                <h5 class="card-title">Total de Planos</h5>
                            </div>
                            <div class="col-auto">
                                <div class="stat text-primary">
                                    <vue-feather type="calendar"></vue-feather>
                                </div>
                            </div>
                        </div>
                        <h1 class="mt-1 mb-3">{{ statistics.total_plans || 0 }}</h1>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col mt-0">
                                <h5 class="card-title">Pendentes</h5>
                            </div>
                            <div class="col-auto">
                                <div class="stat text-warning">
                                    <vue-feather type="clock"></vue-feather>
                                </div>
                            </div>
                        </div>
                        <h1 class="mt-1 mb-3">{{ statistics.pending_plans || 0 }}</h1>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col mt-0">
                                <h5 class="card-title">Aprovados</h5>
                            </div>
                            <div class="col-auto">
                                <div class="stat text-success">
                                    <vue-feather type="check-circle"></vue-feather>
                                </div>
                            </div>
                        </div>
                        <h1 class="mt-1 mb-3">{{ statistics.approved_plans || 0 }}</h1>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col mt-0">
                                <h5 class="card-title">Executados</h5>
                            </div>
                            <div class="col-auto">
                                <div class="stat text-info">
                                    <vue-feather type="check"></vue-feather>
                                </div>
                            </div>
                        </div>
                        <h1 class="mt-1 mb-3">{{ statistics.executed_plans || 0 }}</h1>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Planos de Férias. {{ retrievedData.total }} registros encontrados.</h5>
                        <h6 class="card-subtitle text-muted">Gerencie os planos de férias dos técnicos</h6>

                        <router-link to="/admin/vacation-plans/create" class="btn btn-pill btn-primary mt-3">
                            <vue-feather type="plus"></vue-feather>
                            Adicionar Plano
                        </router-link> 

                        <br>

                        <!-- Filtros -->
                        <div class="row mt-3">
                            <div class="col-md-3">
                                <form class="d-inline-block">
                                    <div class="input-group">
                                        <input type="text" class="form-control" v-model="searchQuery" 
                                               placeholder="Procurar técnico..." aria-label="Search">
                                    </div>
                                </form>
                            </div>
                            <div class="col-md-2">
                                <select v-model="statusFilter" class="form-control">
                                    <option v-for="status in statusOptions" :key="status.value" :value="status.value">
                                        {{ status.label }}
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select v-model="yearFilter" class="form-control">
                                    <option v-for="year in years" :key="year" :value="year">
                                        {{ year }}
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select v-model="technicianFilter" class="form-control">
                                    <option value="">Todos os Técnicos</option>
                                    <option v-for="tech in technicians" :key="tech.id" :value="tech.id">
                                        {{ tech.name }}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Técnico</th>
                                        <th>Ano</th>
                                        <th>Período</th>
                                        <th>Dias Solicitados</th>
                                        <th>Dias Aprovados</th>
                                        <th>Status</th>
                                        <th>Substituto</th>
                                        <th>Solicitado por</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody v-if="retrievedData.data.length > 0">
                                    <tr v-for="(plan, index) in retrievedData.data" :key="plan.id">
                                        <td>#{{ index + 1 }}</td>
                                        <td>
                                            <strong>{{ plan.technician.name }}</strong><br>
                                            <small class="text-muted">{{ plan.technician.code }} - {{ plan.technician.department?.name }}</small>
                                        </td>
                                        <td>{{ plan.year }}</td>
                                        <td>
                                            {{ moment(plan.start_date).format('DD/MM/YYYY') }} - 
                                            {{ moment(plan.end_date).format('DD/MM/YYYY') }}
                                        </td>
                                        <td>{{ plan.days_requested }} dias</td>
                                        <td>{{ plan.days_approved || '-' }} dias</td>
                                        <td>
                                            <span :class="'badge ' + getStatusBadge(plan.status)">
                                                {{ getStatusLabel(plan.status) }}
                                            </span>
                                        </td>
                                        <td>{{ plan.replacement_technician?.name || '-' }}</td>
                                        <td>{{ plan.requested_by_user?.firstName || '-' }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <!-- Ver detalhes -->
                                                <router-link :to="'/admin/vacation-plans/'+plan.id" class="btn btn-sm btn-outline-info">
                                                    <vue-feather type="eye"></vue-feather>
                                                </router-link>
                                                
                                                <!-- Editar (apenas se pendente) -->
                                                <router-link v-if="canEdit(plan)" :to="'/admin/vacation-plans/'+plan.id+'/edit'" class="btn btn-sm btn-outline-primary">
                                                    <vue-feather type="edit-2"></vue-feather>
                                                </router-link>
                                                
                                                <!-- Aprovar (apenas se pendente) -->
                                                <button v-if="canApprove(plan)" @click="approvePlan(plan)" class="btn btn-sm btn-outline-success">
                                                    <vue-feather type="check"></vue-feather>
                                                </button>
                                                
                                                <!-- Rejeitar (apenas se pendente) -->
                                                <button v-if="canReject(plan)" @click="rejectPlan(plan)" class="btn btn-sm btn-outline-warning">
                                                    <vue-feather type="x"></vue-feather>
                                                </button>
                                                
                                                <!-- Executar (apenas se aprovado) -->
                                                <button v-if="canExecute(plan)" @click="executePlan(plan)" class="btn btn-sm btn-outline-info">
                                                    <vue-feather type="play"></vue-feather>
                                                </button>
                                                
                                                <!-- Remover (exceto se executado) -->
                                                <button v-if="canDelete(plan)" @click="confirmDeletion(plan)" class="btn btn-sm btn-outline-danger">
                                                    <vue-feather type="trash"></vue-feather>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                                <tbody v-else>
                                    <tr>
                                        <td colspan="10" align="center">Nenhum plano de férias encontrado</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <Bootstrap4Pagination :data="retrievedData" @pagination-change-page="getData"/>
            </div>
        </div>

        <!-- Modal de Exclusão -->
        <div class="modal" id="deleteModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirmar Exclusão</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        Deseja realmente remover este plano de férias?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button @click="deleteData" type="button" class="btn btn-danger" :disabled="loadingButtonDelete">
                            <div v-if="loadingButtonDelete" class="spinner-border spinner-border-sm" role="status"></div>
                            <span v-else>Remover</span>
                        </button>
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
                        <p>Técnico: <strong>{{ planBeingProcessed?.technician?.name }}</strong></p>
                        <p>Dias solicitados: <strong>{{ planBeingProcessed?.days_requested }}</strong></p>
                        
                        <div class="form-group">
                            <label for="daysApproved">Dias aprovados:</label>
                            <input type="number" 
                                   id="daysApproved" 
                                   class="form-control" 
                                   :max="planBeingProcessed?.days_requested"
                                   :value="planBeingProcessed?.days_requested"
                                   min="1">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button @click="confirmApproval" type="button" class="btn btn-success" :disabled="loadingButtonApprove">
                            <div v-if="loadingButtonApprove" class="spinner-border spinner-border-sm" role="status"></div>
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
                        <p>Técnico: <strong>{{ planBeingProcessed?.technician?.name }}</strong></p>
                        
                        <div class="form-group">
                            <label for="rejectionNotes">Motivo da rejeição:</label>
                            <textarea id="rejectionNotes" 
                                      class="form-control" 
                                      rows="3"
                                      placeholder="Descreva o motivo da rejeição..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button @click="confirmRejection" type="button" class="btn btn-warning" :disabled="loadingButtonReject">
                            <div v-if="loadingButtonReject" class="spinner-border spinner-border-sm" role="status"></div>
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
                        
                        <p><strong>Técnico:</strong> {{ planBeingProcessed?.technician?.name }}</p>
                        <p><strong>Código:</strong> {{ planBeingProcessed?.technician?.code }}</p>
                        <p><strong>Período:</strong> 
                            {{ planBeingProcessed?.start_date ? moment(planBeingProcessed.start_date).format('DD/MM/YYYY') : '' }} - 
                            {{ planBeingProcessed?.end_date ? moment(planBeingProcessed.end_date).format('DD/MM/YYYY') : '' }}
                        </p>
                        <p><strong>Dias aprovados:</strong> {{ planBeingProcessed?.days_approved }} dias</p>
                        
                        <p class="mb-0">Deseja realmente executar este plano de férias? O técnico será marcado como em férias.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button @click="confirmExecution" type="button" class="btn btn-info" :disabled="loadingButtonExecute">
                            <div v-if="loadingButtonExecute" class="spinner-border spinner-border-sm me-2" role="status"></div>
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