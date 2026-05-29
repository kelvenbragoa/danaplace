<template>
    <div>
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">Gestão de Turnos</h1>
                <p class="text-muted">Configure os turnos de trabalho dos técnicos</p>
            </div>
            <div class="d-flex gap-2">
                <router-link 
                    to="/admin/work-schedule" 
                    class="btn btn-outline-secondary"
                >
                    <vue-feather type="arrow-left" size="16" class="me-2"></vue-feather>
                    Dashboard
                </router-link>
                <router-link 
                    to="/admin/work-schedule/shifts/create" 
                    class="btn btn-primary"
                >
                    <vue-feather type="plus" size="16" class="me-2"></vue-feather>
                    Novo Turno
                </router-link>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="filterSchedule" class="form-label">Escala</label>
                        <select 
                            id="filterSchedule"
                            v-model="filters.schedule_id" 
                            @change="applyFilters"
                            class="form-select"
                        >
                            <option value="">Todas as escalas</option>
                            <option 
                                v-for="schedule in availableSchedules" 
                                :key="schedule.id" 
                                :value="schedule.id"
                            >
                                {{ schedule.name }} - {{ getMonthName(schedule.month) }} {{ schedule.year }}
                            </option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="filterShiftType" class="form-label">Tipo de Turno</label>
                        <select 
                            id="filterShiftType"
                            v-model="filters.shift_type" 
                            @change="applyFilters"
                            class="form-select"
                        >
                            <option value="">Todos os tipos</option>
                            <option value="morning">Manhã</option>
                            <option value="afternoon">Tarde</option>
                            <option value="evening">Noite</option>
                            <option value="night">Madrugada</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="filterDate" class="form-label">Data</label>
                        <input
                            id="filterDate"
                            type="date"
                            v-model="filters.date"
                            @change="applyFilters"
                            class="form-control"
                        />
                    </div>
                    <div class="col-md-3">
                        <label for="search" class="form-label">Buscar</label>
                        <div class="input-group">
                            <input
                                id="search"
                                type="text"
                                v-model="searchQuery"
                                @input="debouncedSearch"
                                class="form-control"
                                placeholder="Buscar por nome..."
                            />
                            <button 
                                class="btn btn-outline-secondary" 
                                type="button"
                                @click="clearSearch"
                            >
                                <vue-feather type="x" size="16"></vue-feather>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-primary h-100">
                    <div class="card-body text-center">
                        <h3 class="text-primary mb-1">{{ stats.total_shifts || 0 }}</h3>
                        <small class="text-muted">Total de Turnos</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-success h-100">
                    <div class="card-body text-center">
                        <h3 class="text-success mb-1">{{ stats.shifts_today || 0 }}</h3>
                        <small class="text-muted">Turnos Hoje</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-warning h-100">
                    <div class="card-body text-center">
                        <h3 class="text-warning mb-1">{{ stats.active_shifts || 0 }}</h3>
                        <small class="text-muted">Ativos Agora</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-info h-100">
                    <div class="card-body text-center">
                        <h3 class="text-info mb-1">{{ stats.technicians_assigned || 0 }}</h3>
                        <small class="text-muted">Técnicos Escalados</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Shifts List -->
        <div v-if="loading" class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Carregando...</span>
            </div>
        </div>

        <div v-else-if="shifts.length === 0" class="text-center py-5">
            <vue-feather type="clock" size="64" class="text-muted mb-3"></vue-feather>
            <h4 class="text-muted mb-2">Nenhum turno encontrado</h4>
            <p class="text-muted mb-4">Crie um novo turno para começar a organizar as escalas</p>
            <router-link 
                to="/admin/work-schedule/shifts/create" 
                class="btn btn-primary"
            >
                <vue-feather type="plus" size="16" class="me-2"></vue-feather>
                Criar Primeiro Turno
            </router-link>
        </div>

        <div v-else class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Nome do Turno</th>
                                <th>Horário</th>
                                <th>Escala</th>
                                <th>Técnicos</th>
                                <th>Status</th>
                                <th class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="shift in shifts" :key="shift.id">
                                <td>
                                    <div class="fw-semibold">{{ formatDate(shift.date, 'DD/MM/YYYY') }}</div>
                                    <small class="text-muted">{{ formatDate(shift.date, 'dddd') }}</small>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span 
                                            class="badge me-2"
                                            :class="getShiftTypeBadgeClass(shift.shift_type)"
                                        >
                                            {{ getShiftTypeIcon(shift.shift_type) }}
                                        </span>
                                        <div>
                                            <div class="fw-semibold">{{ shift.name }}</div>
                                            <small class="text-muted">{{ getShiftTypeLabel(shift.shift_type) }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <vue-feather type="clock" size="14" class="me-1"></vue-feather>
                                    {{ shift.start_time }} - {{ shift.end_time }}
                                    <div class="text-muted small">
                                        {{ calculateDuration(shift.start_time, shift.end_time) }}
                                    </div>
                                </td>
                                <td>
                                    <router-link 
                                        :to="`/admin/work-schedule/schedules/${shift.schedule_id}`"
                                        class="text-decoration-none"
                                    >
                                        {{ shift.schedule_name }}
                                    </router-link>
                                    <div class="text-muted small">
                                        {{ getMonthName(shift.schedule_month) }} {{ shift.schedule_year }}
                                    </div>
                                </td>
                                <td>
                                    <div v-if="shift.technicians.length === 0" class="text-muted">
                                        <small>Nenhum técnico</small>
                                    </div>
                                    <div v-else>
                                        <div class="technicians-preview">
                                            <span 
                                                v-for="(technician, index) in shift.technicians.slice(0, 2)" 
                                                :key="technician.id"
                                                class="badge bg-light text-dark me-1"
                                            >
                                                {{ technician.name }}
                                            </span>
                                            <span 
                                                v-if="shift.technicians.length > 2"
                                                class="badge bg-secondary"
                                            >
                                                +{{ shift.technicians.length - 2 }}
                                            </span>
                                        </div>
                                        <small class="text-muted">{{ shift.technicians.length }} técnico{{ shift.technicians.length !== 1 ? 's' : '' }}</small>
                                    </div>
                                </td>
                                <td>
                                    <span 
                                        class="badge"
                                        :class="getStatusBadgeClass(shift)"
                                    >
                                        {{ getStatusLabel(shift) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <router-link 
                                            :to="`/admin/work-schedule/shifts/${shift.id}`"
                                            class="btn btn-outline-primary"
                                            title="Ver detalhes"
                                        >
                                            <vue-feather type="eye" size="14"></vue-feather>
                                        </router-link>
                                        <router-link 
                                            :to="`/admin/work-schedule/shifts/${shift.id}/edit`"
                                            class="btn btn-outline-secondary"
                                            title="Editar"
                                            v-if="canEdit(shift)"
                                        >
                                            <vue-feather type="edit" size="14"></vue-feather>
                                        </router-link>
                                        <button 
                                            @click="confirmDelete(shift)"
                                            class="btn btn-outline-danger"
                                            title="Excluir"
                                            v-if="canDelete(shift)"
                                            :disabled="loadingDelete === shift.id"
                                        >
                                            <span v-if="loadingDelete === shift.id" class="spinner-border spinner-border-sm"></span>
                                            <vue-feather v-else type="trash-2" size="14"></vue-feather>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="pagination.total > pagination.per_page" class="d-flex justify-content-center mt-4">
            <nav aria-label="Page navigation">
                <ul class="pagination">
                    <li class="page-item" :class="{ disabled: pagination.current_page === 1 }">
                        <button 
                            class="page-link" 
                            @click="changePage(pagination.current_page - 1)"
                            :disabled="pagination.current_page === 1"
                        >
                            <vue-feather type="chevron-left" size="16"></vue-feather>
                        </button>
                    </li>
                    <li 
                        v-for="page in paginationPages" 
                        :key="page"
                        class="page-item" 
                        :class="{ active: page === pagination.current_page }"
                    >
                        <button 
                            class="page-link" 
                            @click="changePage(page)"
                            v-if="page !== '...'"
                        >
                            {{ page }}
                        </button>
                        <span v-else class="page-link">...</span>
                    </li>
                    <li class="page-item" :class="{ disabled: pagination.current_page === pagination.last_page }">
                        <button 
                            class="page-link" 
                            @click="changePage(pagination.current_page + 1)"
                            :disabled="pagination.current_page === pagination.last_page"
                        >
                            <vue-feather type="chevron-right" size="16"></vue-feather>
                        </button>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import VueFeather from 'vue-feather';
import { useToast } from '@/composables/useToast';
import { debounce } from 'lodash';
import moment from 'moment';

// Configure moment to Portuguese
moment.locale('pt-br');

const { showToast } = useToast();
const router = useRouter();

// Reactive data
const shifts = ref([]);
const loading = ref(false);
const loadingDelete = ref(null);
const searchQuery = ref('');
const availableSchedules = ref([]);
const stats = ref({});
const pagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0
});

const filters = ref({
    schedule_id: '',
    shift_type: '',
    date: ''
});

// Computed
const months = computed(() => [
    'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
    'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'
]);

const paginationPages = computed(() => {
    const current = pagination.value.current_page;
    const last = pagination.value.last_page;
    const pages = [];
    
    if (last <= 7) {
        for (let i = 1; i <= last; i++) {
            pages.push(i);
        }
    } else {
        if (current <= 3) {
            pages.push(1, 2, 3, 4, '...', last);
        } else if (current >= last - 2) {
            pages.push(1, '...', last - 3, last - 2, last - 1, last);
        } else {
            pages.push(1, '...', current - 1, current, current + 1, '...', last);
        }
    }
    
    return pages;
});

// Methods
const getShifts = async (page = 1) => {
    loading.value = true;
    try {
        const response = await axios.get('/work-schedule/shifts', {
            params: {
                page,
                search: searchQuery.value,
                ...filters.value
            }
        });
        shifts.value = response.data.data;
        pagination.value = {
            current_page: response.data.current_page,
            last_page: response.data.last_page,
            per_page: response.data.per_page,
            total: response.data.total
        };
        
        // Update stats
        stats.value = response.data.stats || {};
    } catch (error) {
        console.error('Erro ao carregar turnos:', error);
        showToast('Erro ao carregar turnos', 'error');
    } finally {
        loading.value = false;
    }
};

const getInitialData = async () => {
    try {
        const response = await axios.get('/work-schedule/shifts/initial-data');
        availableSchedules.value = response.data.schedules || [];
    } catch (error) {
        console.error('Erro ao carregar dados iniciais:', error);
    }
};

const applyFilters = () => {
    getShifts(1);
};

const debouncedSearch = debounce(() => {
    getShifts(1);
}, 300);

const clearSearch = () => {
    searchQuery.value = '';
    getShifts(1);
};

const changePage = (page) => {
    if (page >= 1 && page <= pagination.value.last_page) {
        getShifts(page);
    }
};

const confirmDelete = (shift) => {
    if (confirm(`Tem certeza que deseja excluir o turno "${shift.name}"?`)) {
        deleteShift(shift);
    }
};

const deleteShift = async (shift) => {
    loadingDelete.value = shift.id;
    try {
        await axios.delete(`/work-schedule/shifts/${shift.id}`);
        shifts.value = shifts.value.filter(s => s.id !== shift.id);
        showToast('Turno excluído com sucesso!', 'success');
        
        // Refresh stats
        getShifts(pagination.value.current_page);
    } catch (error) {
        console.error('Erro ao excluir turno:', error);
        showToast('Erro ao excluir turno', 'error');
    } finally {
        loadingDelete.value = null;
    }
};

const getMonthName = (month) => {
    return months.value[month - 1] || month;
};

const getShiftTypeLabel = (type) => {
    const labels = {
        morning: 'Manhã',
        afternoon: 'Tarde',
        evening: 'Noite',
        night: 'Madrugada'
    };
    return labels[type] || 'Geral';
};

const getShiftTypeIcon = (type) => {
    const icons = {
        morning: '🌅',
        afternoon: '☀️',
        evening: '🌆',
        night: '🌙'
    };
    return icons[type] || '🕒';
};

const getShiftTypeBadgeClass = (type) => {
    const classes = {
        morning: 'bg-warning text-dark',
        afternoon: 'bg-info text-white',
        evening: 'bg-primary text-white',
        night: 'bg-dark text-white'
    };
    return classes[type] || 'bg-secondary text-white';
};

const getStatusLabel = (shift) => {
    const now = moment();
    const shiftDate = moment(shift.date);
    
    if (!shiftDate.isSame(now, 'day')) {
        return shiftDate.isBefore(now, 'day') ? 'Finalizado' : 'Programado';
    }
    
    const shiftStart = moment(`${shift.date} ${shift.start_time}`);
    const shiftEnd = moment(`${shift.date} ${shift.end_time}`);
    
    if (now.isBetween(shiftStart, shiftEnd, null, '[]')) {
        return 'Em Andamento';
    } else if (now.isAfter(shiftEnd)) {
        return 'Finalizado';
    } else {
        return 'Programado';
    }
};

const getStatusBadgeClass = (shift) => {
    const status = getStatusLabel(shift);
    const classes = {
        'Em Andamento': 'bg-success',
        'Programado': 'bg-warning text-dark',
        'Finalizado': 'bg-secondary'
    };
    return classes[status] || 'bg-secondary';
};

const calculateDuration = (startTime, endTime) => {
    const start = moment(startTime, 'HH:mm');
    const end = moment(endTime, 'HH:mm');
    
    // Handle overnight shifts
    if (end.isBefore(start)) {
        end.add(1, 'day');
    }
    
    const duration = moment.duration(end.diff(start));
    const hours = Math.floor(duration.asHours());
    const minutes = duration.minutes();
    
    if (minutes === 0) {
        return `${hours}h`;
    } else {
        return `${hours}h ${minutes}min`;
    }
};

const canEdit = (shift) => {
    // Can edit future shifts or shifts from today
    const shiftDate = moment(shift.date);
    const today = moment().startOf('day');
    return shiftDate.isSameOrAfter(today);
};

const canDelete = (shift) => {
    // Can only delete future shifts
    const shiftDate = moment(shift.date);
    const today = moment().startOf('day');
    return shiftDate.isAfter(today);
};

const formatDate = (date, format = 'DD/MM/YYYY') => {
    return moment(date).format(format);
};

// Lifecycle
onMounted(() => {
    getInitialData();
    getShifts();
});
</script>

<style scoped>
.card {
    border: 1px solid rgba(0, 0, 0, 0.125);
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.table th {
    border-top: none;
    font-weight: 600;
    color: #495057;
    font-size: 0.875rem;
}

.table td {
    vertical-align: middle;
}

.badge {
    font-size: 0.75rem;
}

.btn-group-sm > .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.775rem;
}

.technicians-preview {
    margin-bottom: 0.25rem;
}

.pagination .page-link {
    border: 1px solid #dee2e6;
    color: #6c757d;
}

.pagination .page-item.active .page-link {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.spinner-border-sm {
    width: 1rem;
    height: 1rem;
}

.form-control:focus,
.form-select:focus {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

/* Responsive */
@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.875rem;
    }
    
    .btn-group-sm > .btn {
        padding: 0.125rem 0.25rem;
    }
    
    .badge {
        font-size: 0.65rem;
    }
}
</style>