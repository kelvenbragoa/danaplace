<template>
    <div>
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4" v-if="schedule">
            <div>
                <h1 class="h3 mb-0">{{ schedule.name }}</h1>
                <p class="text-muted">
                    {{ getMonthName(schedule.month) }} {{ schedule.year }} - 
                    <span class="badge" :class="getStatusBadgeClass(schedule.status)">
                        {{ getStatusLabel(schedule.status) }}
                    </span>
                </p>
            </div>
            <div class="d-flex gap-2">
                <router-link 
                    to="/admin/work-schedule/schedules" 
                    class="btn btn-outline-secondary"
                >
                    <vue-feather type="arrow-left" size="16" class="me-2"></vue-feather>
                    Voltar
                </router-link>
                <router-link 
                    :to="`/admin/work-schedule/schedules/${schedule.id}/edit`"
                    class="btn btn-outline-primary"
                    v-if="canEdit"
                >
                    <vue-feather type="edit" size="16" class="me-2"></vue-feather>
                    Editar
                </router-link>
                <button 
                    class="btn btn-success"
                    @click="toggleStatus"
                    :disabled="loadingStatus"
                    v-if="canToggleStatus"
                >
                    <span v-if="loadingStatus" class="spinner-border spinner-border-sm me-2"></span>
                    <vue-feather v-else :type="schedule.status === 'published' ? 'eye-off' : 'eye'" size="16" class="me-2"></vue-feather>
                    {{ schedule.status === 'published' ? 'Despublicar' : 'Publicar' }}
                </button>
            </div>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Carregando...</span>
            </div>
        </div>

        <!-- Schedule Content -->
        <div v-else-if="schedule">
            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card border-primary h-100">
                        <div class="card-body text-center">
                            <h3 class="text-primary mb-1">{{ schedule.stats?.total_days || 0 }}</h3>
                            <small class="text-muted">Dias com Escala</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card border-success h-100">
                        <div class="card-body text-center">
                            <h3 class="text-success mb-1">{{ schedule.stats?.total_shifts || 0 }}</h3>
                            <small class="text-muted">Total de Turnos</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card border-warning h-100">
                        <div class="card-body text-center">
                            <h3 class="text-warning mb-1">{{ schedule.stats?.total_assignments || 0 }}</h3>
                            <small class="text-muted">Escalações</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card border-info h-100">
                        <div class="card-body text-center">
                            <h3 class="text-info mb-1">{{ schedule.stats?.active_technicians || 0 }}</h3>
                            <small class="text-muted">Técnicos Escalados</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- View Toggle and Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <div class="btn-group" role="group">
                                <button 
                                    type="button" 
                                    class="btn" 
                                    :class="viewMode === 'calendar' ? 'btn-primary' : 'btn-outline-primary'"
                                    @click="viewMode = 'calendar'"
                                >
                                    <vue-feather type="calendar" size="16" class="me-2"></vue-feather>
                                    Calendário
                                </button>
                                <button 
                                    type="button" 
                                    class="btn" 
                                    :class="viewMode === 'list' ? 'btn-primary' : 'btn-outline-primary'"
                                    @click="viewMode = 'list'"
                                >
                                    <vue-feather type="list" size="16" class="me-2"></vue-feather>
                                    Lista
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row g-2">
                                <div class="col-6">
                                    <select v-model="filterShift" @change="applyFilters" class="form-select form-select-sm">
                                        <option value="">Todos os turnos</option>
                                        <option 
                                            v-for="shift in availableShifts" 
                                            :key="shift.id" 
                                            :value="shift.id"
                                        >
                                            {{ shift.name }}
                                        </option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <select v-model="filterTechnician" @change="applyFilters" class="form-select form-select-sm">
                                        <option value="">Todos os técnicos</option>
                                        <option 
                                            v-for="technician in availableTechnicians" 
                                            :key="technician.id" 
                                            :value="technician.id"
                                        >
                                            {{ technician.name }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Calendar View -->
            <div v-if="viewMode === 'calendar'" class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Calendário da Escala</h5>
                    <div class="d-flex gap-2">
                        <button 
                            class="btn btn-sm btn-outline-primary"
                            @click="addDayModal = true"
                            v-if="canEdit"
                        >
                            <vue-feather type="plus" size="14" class="me-1"></vue-feather>
                            Adicionar Dia
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Calendar Grid -->
                    <div class="calendar-grid">
                        <!-- Calendar Header -->
                        <div class="calendar-header">
                            <div class="calendar-day-header">Dom</div>
                            <div class="calendar-day-header">Seg</div>
                            <div class="calendar-day-header">Ter</div>
                            <div class="calendar-day-header">Qua</div>
                            <div class="calendar-day-header">Qui</div>
                            <div class="calendar-day-header">Sex</div>
                            <div class="calendar-day-header">Sáb</div>
                        </div>
                        
                        <!-- Calendar Body -->
                        <div class="calendar-body">
                            <div 
                                v-for="week in calendarWeeks" 
                                :key="week.weekNumber"
                                class="calendar-week"
                            >
                                <div 
                                    v-for="day in week.days" 
                                    :key="day.date"
                                    class="calendar-day"
                                    :class="{
                                        'other-month': !day.currentMonth,
                                        'today': day.isToday,
                                        'has-shifts': day.shifts.length > 0,
                                        'weekend': day.isWeekend
                                    }"
                                    @click="selectDay(day)"
                                >
                                    <div class="calendar-day-number">{{ day.dayNumber }}</div>
                                    <div class="calendar-day-shifts">
                                        <div 
                                            v-for="shift in day.shifts.slice(0, 3)" 
                                            :key="shift.id"
                                            class="calendar-shift-item"
                                            :class="`shift-${shift.shift_type || 'default'}`"
                                        >
                                            <small>{{ shift.shift_name }} ({{ shift.technicians_count }})</small>
                                        </div>
                                        <div v-if="day.shifts.length > 3" class="calendar-shift-more">
                                            <small>+{{ day.shifts.length - 3 }} mais</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- List View -->
            <div v-else class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Lista de Turnos</h5>
                    <div class="d-flex gap-2">
                        <button 
                            class="btn btn-sm btn-outline-primary"
                            @click="addShiftModal = true"
                            v-if="canEdit"
                        >
                            <vue-feather type="plus" size="14" class="me-1"></vue-feather>
                            Adicionar Turno
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div v-if="filteredShifts.length === 0" class="text-center py-4 text-muted">
                        <vue-feather type="calendar" size="48" class="mb-3 opacity-50"></vue-feather>
                        <p class="mb-0">Nenhum turno encontrado</p>
                    </div>
                    
                    <div v-else class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Turno</th>
                                    <th>Horário</th>
                                    <th>Técnicos</th>
                                    <th class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="shift in filteredShifts" :key="`${shift.date}-${shift.id}`">
                                    <td>
                                        <div class="fw-semibold">{{ formatDate(shift.date, 'DD/MM/YYYY') }}</div>
                                        <small class="text-muted">{{ formatDate(shift.date, 'dddd') }}</small>
                                    </td>
                                    <td>
                                        <span class="badge" :class="getShiftTypeBadgeClass(shift.shift_type)">
                                            {{ shift.shift_name }}
                                        </span>
                                    </td>
                                    <td>
                                        <vue-feather type="clock" size="14" class="me-1"></vue-feather>
                                        {{ shift.start_time }} - {{ shift.end_time }}
                                    </td>
                                    <td>
                                        <div v-if="shift.technicians.length === 0" class="text-muted">
                                            <small>Nenhum técnico escalado</small>
                                        </div>
                                        <div v-else>
                                            <span 
                                                v-for="technician in shift.technicians" 
                                                :key="technician.id"
                                                class="badge bg-light text-dark me-1"
                                            >
                                                {{ technician.name }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <button 
                                                class="btn btn-outline-primary"
                                                @click="editShift(shift)"
                                                v-if="canEdit"
                                            >
                                                <vue-feather type="edit" size="14"></vue-feather>
                                            </button>
                                            <button 
                                                class="btn btn-outline-danger"
                                                @click="deleteShift(shift)"
                                                v-if="canEdit"
                                            >
                                                <vue-feather type="trash-2" size="14"></vue-feather>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Selected Day Details Modal (for calendar view) -->
            <div 
                v-if="selectedDay" 
                class="modal fade show d-block" 
                tabindex="-1" 
                style="background-color: rgba(0,0,0,0.5);"
                @click.self="selectedDay = null"
            >
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                Turnos do dia {{ formatDate(selectedDay.date, 'DD/MM/YYYY') }}
                            </h5>
                            <button 
                                type="button" 
                                class="btn-close" 
                                @click="selectedDay = null"
                            ></button>
                        </div>
                        <div class="modal-body">
                            <div v-if="selectedDay.shifts.length === 0" class="text-center py-4 text-muted">
                                <vue-feather type="calendar" size="48" class="mb-3 opacity-50"></vue-feather>
                                <p class="mb-0">Nenhum turno programado para este dia</p>
                                <button 
                                    class="btn btn-primary mt-3"
                                    @click="addShiftToDay(selectedDay.date)"
                                    v-if="canEdit"
                                >
                                    <vue-feather type="plus" size="16" class="me-2"></vue-feather>
                                    Adicionar Turno
                                </button>
                            </div>
                            
                            <div v-else>
                                <div 
                                    v-for="shift in selectedDay.shifts" 
                                    :key="shift.id"
                                    class="border rounded p-3 mb-3"
                                >
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="mb-0">{{ shift.shift_name }}</h6>
                                        <span class="badge" :class="getShiftTypeBadgeClass(shift.shift_type)">
                                            {{ shift.start_time }} - {{ shift.end_time }}
                                        </span>
                                    </div>
                                    
                                    <div class="mb-2">
                                        <small class="text-muted me-2">Técnicos:</small>
                                        <div class="d-inline">
                                            <span 
                                                v-for="technician in shift.technicians" 
                                                :key="technician.id"
                                                class="badge bg-light text-dark me-1"
                                            >
                                                {{ technician.name }}
                                            </span>
                                            <span v-if="shift.technicians.length === 0" class="text-muted">
                                                Nenhum técnico escalado
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div v-if="canEdit" class="d-flex gap-2">
                                        <button 
                                            class="btn btn-sm btn-outline-primary"
                                            @click="editShift(shift)"
                                        >
                                            <vue-feather type="edit" size="14" class="me-1"></vue-feather>
                                            Editar
                                        </button>
                                        <button 
                                            class="btn btn-sm btn-outline-danger"
                                            @click="deleteShift(shift)"
                                        >
                                            <vue-feather type="trash-2" size="14" class="me-1"></vue-feather>
                                            Remover
                                        </button>
                                    </div>
                                </div>
                                
                                <div v-if="canEdit" class="text-center">
                                    <button 
                                        class="btn btn-outline-primary"
                                        @click="addShiftToDay(selectedDay.date)"
                                    >
                                        <vue-feather type="plus" size="16" class="me-2"></vue-feather>
                                        Adicionar Outro Turno
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Error State -->
        <div v-else class="text-center py-5">
            <vue-feather type="alert-circle" size="64" class="text-muted mb-3"></vue-feather>
            <h4 class="text-muted mb-2">Escala não encontrada</h4>
            <p class="text-muted">A escala solicitada não existe ou foi removida.</p>
            <router-link 
                to="/admin/work-schedule/schedules" 
                class="btn btn-primary"
            >
                <vue-feather type="arrow-left" size="16" class="me-2"></vue-feather>
                Voltar às Escalas
            </router-link>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import VueFeather from 'vue-feather';
import { useToast } from '@/composables/useToast';
import moment from 'moment';

// Configure moment to Portuguese
moment.locale('pt-br');

const { showToast } = useToast();
const route = useRoute();
const router = useRouter();

// Reactive data
const schedule = ref(null);
const loading = ref(true);
const loadingStatus = ref(false);
const viewMode = ref('calendar');
const filterShift = ref('');
const filterTechnician = ref('');
const selectedDay = ref(null);
const availableShifts = ref([]);
const availableTechnicians = ref([]);

// Computed
const canEdit = computed(() => {
    return schedule.value && ['draft', 'published'].includes(schedule.value.status);
});

const canToggleStatus = computed(() => {
    return schedule.value && ['draft', 'published'].includes(schedule.value.status);
});

const calendarWeeks = computed(() => {
    if (!schedule.value) return [];
    
    const year = schedule.value.year;
    const month = schedule.value.month;
    const firstDay = moment(`${year}-${month.toString().padStart(2, '0')}-01`);
    const lastDay = firstDay.clone().endOf('month');
    const startCalendar = firstDay.clone().startOf('week');
    const endCalendar = lastDay.clone().endOf('week');
    
    const weeks = [];
    let currentWeek = [];
    let current = startCalendar.clone();
    
    while (current.isSameOrBefore(endCalendar)) {
        const dayData = {
            date: current.format('YYYY-MM-DD'),
            dayNumber: current.date(),
            currentMonth: current.month() === firstDay.month(),
            isToday: current.isSame(moment(), 'day'),
            isWeekend: current.day() === 0 || current.day() === 6,
            shifts: getShiftsForDay(current.format('YYYY-MM-DD'))
        };
        
        currentWeek.push(dayData);
        
        if (current.day() === 6) {
            weeks.push({
                weekNumber: weeks.length + 1,
                days: [...currentWeek]
            });
            currentWeek = [];
        }
        
        current.add(1, 'day');
    }
    
    return weeks;
});

const filteredShifts = computed(() => {
    if (!schedule.value?.shifts) return [];
    
    let shifts = [...schedule.value.shifts];
    
    if (filterShift.value) {
        shifts = shifts.filter(shift => shift.shift_id === parseInt(filterShift.value));
    }
    
    if (filterTechnician.value) {
        shifts = shifts.filter(shift => 
            shift.technicians.some(tech => tech.id === parseInt(filterTechnician.value))
        );
    }
    
    return shifts.sort((a, b) => new Date(a.date) - new Date(b.date));
});

// Methods
const getSchedule = async () => {
    loading.value = true;
    try {
        const response = await axios.get(`/work-schedule/${route.params.id}`);
        schedule.value = response.data;
        
        // Extract available shifts and technicians for filters
        const shifts = new Map();
        const technicians = new Map();
        
        response.data.shifts?.forEach(shift => {
            shifts.set(shift.shift_id, {
                id: shift.shift_id,
                name: shift.shift_name
            });
            
            shift.technicians?.forEach(tech => {
                technicians.set(tech.id, tech);
            });
        });
        
        availableShifts.value = Array.from(shifts.values());
        availableTechnicians.value = Array.from(technicians.values());
        
    } catch (error) {
        console.error('Erro ao carregar escala:', error);
        showToast('Erro ao carregar escala', 'error');
        schedule.value = null;
    } finally {
        loading.value = false;
    }
};

const getShiftsForDay = (date) => {
    if (!schedule.value?.shifts) return [];
    return schedule.value.shifts.filter(shift => shift.date === date);
};

const selectDay = (day) => {
    if (day.currentMonth) {
        selectedDay.value = day;
    }
};

const toggleStatus = async () => {
    const newStatus = schedule.value.status === 'published' ? 'draft' : 'published';
    
    loadingStatus.value = true;
    try {
        await axios.patch(`/work-schedule/${schedule.value.id}/toggle-status`, {
            status: newStatus
        });
        
        schedule.value.status = newStatus;
        showToast(`Escala ${newStatus === 'published' ? 'publicada' : 'despublicada'} com sucesso!`, 'success');
    } catch (error) {
        console.error('Erro ao alterar status:', error);
        showToast('Erro ao alterar status da escala', 'error');
    } finally {
        loadingStatus.value = false;
    }
};

const editShift = (shift) => {
    // Navigate to shift edit page or open modal
    router.push(`/admin/work-schedule/shifts/${shift.id}/edit`);
};

const deleteShift = async (shift) => {
    if (!confirm(`Tem certeza que deseja remover este turno?`)) {
        return;
    }
    
    try {
        await axios.delete(`/work-schedule/shifts/${shift.id}`);
        
        // Remove shift from local data
        schedule.value.shifts = schedule.value.shifts.filter(s => s.id !== shift.id);
        selectedDay.value = null;
        
        showToast('Turno removido com sucesso!', 'success');
    } catch (error) {
        console.error('Erro ao remover turno:', error);
        showToast('Erro ao remover turno', 'error');
    }
};

const addShiftToDay = (date) => {
    router.push({
        path: '/admin/work-schedule/shifts/create',
        query: { 
            schedule_id: schedule.value.id,
            date: date
        }
    });
};

const applyFilters = () => {
    // Filters are applied automatically via computed property
};

const getMonthName = (month) => {
    const months = [
        'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
        'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'
    ];
    return months[month - 1] || month;
};

const getStatusLabel = (status) => {
    const labels = {
        draft: 'Rascunho',
        published: 'Publicada',
        archived: 'Arquivada'
    };
    return labels[status] || status;
};

const getStatusBadgeClass = (status) => {
    const classes = {
        draft: 'bg-secondary',
        published: 'bg-success',
        archived: 'bg-warning'
    };
    return classes[status] || 'bg-secondary';
};

const getShiftTypeBadgeClass = (type) => {
    const classes = {
        morning: 'bg-warning text-dark',
        afternoon: 'bg-info text-white',
        evening: 'bg-primary text-white',
        night: 'bg-dark text-white',
        default: 'bg-secondary text-white'
    };
    return classes[type] || classes.default;
};

const formatDate = (date, format = 'DD/MM/YYYY') => {
    return moment(date).format(format);
};

// Lifecycle
onMounted(() => {
    getSchedule();
});
</script>

<style scoped>
/* Calendar Styles */
.calendar-grid {
    width: 100%;
}

.calendar-header {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 1px;
    background-color: #dee2e6;
    border: 1px solid #dee2e6;
}

.calendar-day-header {
    background-color: #f8f9fa;
    padding: 0.75rem 0.5rem;
    text-align: center;
    font-weight: 600;
    font-size: 0.875rem;
    color: #495057;
}

.calendar-body {
    display: grid;
    grid-template-rows: repeat(auto-fit, 120px);
    gap: 1px;
    background-color: #dee2e6;
    border: 1px solid #dee2e6;
    border-top: none;
}

.calendar-week {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 1px;
}

.calendar-day {
    background-color: white;
    padding: 0.5rem;
    min-height: 120px;
    cursor: pointer;
    transition: background-color 0.2s ease;
    position: relative;
    display: flex;
    flex-direction: column;
}

.calendar-day:hover {
    background-color: #f8f9fa;
}

.calendar-day.other-month {
    background-color: #f8f9fa;
    color: #6c757d;
}

.calendar-day.today {
    background-color: #e3f2fd;
}

.calendar-day.has-shifts {
    border-left: 4px solid #0d6efd;
}

.calendar-day.weekend {
    background-color: #fff5f5;
}

.calendar-day-number {
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.calendar-day-shifts {
    flex: 1;
    overflow: hidden;
}

.calendar-shift-item {
    background-color: #0d6efd;
    color: white;
    padding: 0.125rem 0.25rem;
    margin-bottom: 0.125rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
}

.calendar-shift-item.shift-morning {
    background-color: #ffc107;
    color: #000;
}

.calendar-shift-item.shift-afternoon {
    background-color: #0dcaf0;
    color: #000;
}

.calendar-shift-item.shift-evening {
    background-color: #0d6efd;
}

.calendar-shift-item.shift-night {
    background-color: #212529;
}

.calendar-shift-more {
    font-size: 0.75rem;
    color: #6c757d;
    font-style: italic;
}

/* Modal Styles */
.modal.show {
    display: block;
}

.badge {
    font-size: 0.75rem;
}

.btn-group-sm > .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.775rem;
}

/* Table Styles */
.table th {
    border-top: none;
    font-weight: 600;
    color: #495057;
}

.table-responsive {
    border-radius: 0.375rem;
}

/* Card Styles */
.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid rgba(0, 0, 0, 0.125);
}

/* Button Styles */
.btn-group {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

/* Responsive */
@media (max-width: 768px) {
    .calendar-day {
        min-height: 80px;
        padding: 0.25rem;
    }
    
    .calendar-day-number {
        font-size: 0.875rem;
    }
    
    .calendar-shift-item {
        font-size: 0.65rem;
        padding: 0.125rem;
    }
}
</style>