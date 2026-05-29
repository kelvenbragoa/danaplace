<template>
    <div>
        <!-- Header -->
        <div class="bg-primary text-white py-4 mb-4">
            <div class="container">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h3 mb-0">Escala de Trabalho</h1>
                        <p class="mb-0 opacity-75">Consulte os técnicos em serviço</p>
                    </div>
                    <div class="text-end">
                        <div class="h5 mb-0">{{ currentDate }}</div>
                        <small class="opacity-75">{{ currentTime }}</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="container">
            <!-- Date Navigation -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <vue-feather type="calendar" size="16"></vue-feather>
                                </span>
                                <input
                                    type="date"
                                    v-model="selectedDate"
                                    @change="loadScheduleForDate"
                                    class="form-control"
                                />
                            </div>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="btn-group" role="group">
                                <button 
                                    type="button" 
                                    class="btn btn-outline-primary"
                                    @click="changeDate(-1)"
                                >
                                    <vue-feather type="chevron-left" size="16"></vue-feather>
                                    Anterior
                                </button>
                                <button 
                                    type="button" 
                                    class="btn btn-outline-primary"
                                    @click="setToday"
                                >
                                    Hoje
                                </button>
                                <button 
                                    type="button" 
                                    class="btn btn-outline-primary"
                                    @click="changeDate(1)"
                                >
                                    Próximo
                                    <vue-feather type="chevron-right" size="16"></vue-feather>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <h5 class="mb-0 text-primary">{{ selectedDateFormatted }}</h5>
                            <small class="text-muted">{{ selectedDateWeekday }}</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Current Status -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card border-success h-100">
                        <div class="card-body text-center">
                            <vue-feather type="users" size="32" class="text-success mb-2"></vue-feather>
                            <h3 class="text-success mb-1">{{ currentStatus.technicians_on_duty || 0 }}</h3>
                            <p class="mb-0 text-muted">Técnicos em Serviço</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-info h-100">
                        <div class="card-body text-center">
                            <vue-feather type="clock" size="32" class="text-info mb-2"></vue-feather>
                            <h3 class="text-info mb-1">{{ currentStatus.active_shifts || 0 }}</h3>
                            <p class="mb-0 text-muted">Turnos Ativos</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Loading -->
            <div v-if="loading" class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Carregando...</span>
                </div>
                <p class="mt-3 text-muted">Carregando escalas...</p>
            </div>

            <!-- No Schedule -->
            <div v-else-if="!daySchedule || daySchedule.shifts.length === 0" class="text-center py-5">
                <vue-feather type="calendar" size="64" class="text-muted mb-3"></vue-feather>
                <h4 class="text-muted mb-2">Nenhum turno programado</h4>
                <p class="text-muted mb-4">
                    Não há turnos escalados para {{ selectedDateFormatted.toLowerCase() }}.
                </p>
                <div class="d-flex justify-content-center gap-2">
                    <button 
                        class="btn btn-outline-primary"
                        @click="setToday"
                    >
                        <vue-feather type="calendar" size="16" class="me-2"></vue-feather>
                        Ver Hoje
                    </button>
                    <button 
                        class="btn btn-outline-secondary"
                        @click="changeDate(1)"
                    >
                        <vue-feather type="chevron-right" size="16" class="me-2"></vue-feather>
                        Próximo Dia
                    </button>
                </div>
            </div>

            <!-- Shifts Schedule -->
            <div v-else>
                <!-- Time-based Status -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <vue-feather type="clock" size="16" class="me-2"></vue-feather>
                                    Status Atual - {{ currentTimeFormatted }}
                                </h5>
                            </div>
                            <div class="card-body">
                                <div v-if="currentShift" class="alert alert-success">
                                    <div class="d-flex align-items-center">
                                        <vue-feather type="play-circle" size="24" class="me-3"></vue-feather>
                                        <div>
                                            <h6 class="alert-heading mb-1">{{ currentShift.shift_name }} em Andamento</h6>
                                            <p class="mb-0">
                                                {{ currentShift.start_time }} às {{ currentShift.end_time }} - 
                                                {{ currentShift.technicians.length }} técnico{{ currentShift.technicians.length !== 1 ? 's' : '' }} em serviço
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div v-else-if="nextShift" class="alert alert-warning">
                                    <div class="d-flex align-items-center">
                                        <vue-feather type="clock" size="24" class="me-3"></vue-feather>
                                        <div>
                                            <h6 class="alert-heading mb-1">Próximo Turno: {{ nextShift.shift_name }}</h6>
                                            <p class="mb-0">
                                                Início às {{ nextShift.start_time }} - 
                                                {{ nextShift.technicians.length }} técnico{{ nextShift.technicians.length !== 1 ? 's' : '' }} escalado{{ nextShift.technicians.length !== 1 ? 's' : '' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div v-else class="alert alert-info">
                                    <div class="d-flex align-items-center">
                                        <vue-feather type="pause-circle" size="24" class="me-3"></vue-feather>
                                        <div>
                                            <h6 class="alert-heading mb-1">Nenhum Turno Ativo</h6>
                                            <p class="mb-0">Atualmente não há técnicos em serviço.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Shifts List -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    Todos os Turnos - {{ selectedDateFormatted }}
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-4">
                                    <div 
                                        v-for="shift in daySchedule.shifts" 
                                        :key="shift.id"
                                        class="col-lg-6"
                                    >
                                        <div 
                                            class="card h-100 shift-card"
                                            :class="{
                                                'border-success': isCurrentShift(shift),
                                                'border-warning': isUpcomingShift(shift),
                                                'border-secondary': isFinishedShift(shift)
                                            }"
                                        >
                                            <div class="card-header d-flex justify-content-between align-items-center">
                                                <h6 class="card-title mb-0">{{ shift.shift_name }}</h6>
                                                <span 
                                                    class="badge"
                                                    :class="{
                                                        'bg-success': isCurrentShift(shift),
                                                        'bg-warning text-dark': isUpcomingShift(shift),
                                                        'bg-secondary': isFinishedShift(shift)
                                                    }"
                                                >
                                                    {{ getShiftStatusText(shift) }}
                                                </span>
                                            </div>
                                            <div class="card-body">
                                                <!-- Time Info -->
                                                <div class="d-flex align-items-center mb-3">
                                                    <vue-feather type="clock" size="16" class="text-muted me-2"></vue-feather>
                                                    <strong>{{ shift.start_time }} - {{ shift.end_time }}</strong>
                                                </div>

                                                <!-- Technicians -->
                                                <div class="mb-3">
                                                    <div class="d-flex align-items-center mb-2">
                                                        <vue-feather type="users" size="16" class="text-muted me-2"></vue-feather>
                                                        <strong>Técnicos Escalados ({{ shift.technicians.length }})</strong>
                                                    </div>
                                                    
                                                    <div v-if="shift.technicians.length === 0" class="text-muted">
                                                        <small>Nenhum técnico escalado para este turno</small>
                                                    </div>
                                                    
                                                    <div v-else class="technicians-list">
                                                        <div 
                                                            v-for="technician in shift.technicians" 
                                                            :key="technician.id"
                                                            class="technician-item d-flex align-items-center mb-2"
                                                        >
                                                            <div class="technician-avatar me-2">
                                                                <div class="avatar-circle">
                                                                    {{ getInitials(technician.name) }}
                                                                </div>
                                                            </div>
                                                            <div>
                                                                <div class="fw-semibold">{{ technician.name }}</div>
                                                                <small class="text-muted">{{ technician.specialization || 'Técnico Geral' }}</small>
                                                            </div>
                                                            <div class="ms-auto">
                                                                <span 
                                                                    v-if="isCurrentShift(shift)"
                                                                    class="badge bg-success"
                                                                >
                                                                    Em Serviço
                                                                </span>
                                                                <span 
                                                                    v-else-if="isUpcomingShift(shift)"
                                                                    class="badge bg-warning text-dark"
                                                                >
                                                                    Escalado
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Additional Info -->
                                                <div v-if="shift.description" class="mb-3">
                                                    <div class="d-flex align-items-center mb-2">
                                                        <vue-feather type="info" size="16" class="text-muted me-2"></vue-feather>
                                                        <strong>Observações</strong>
                                                    </div>
                                                    <p class="text-muted small mb-0">{{ shift.description }}</p>
                                                </div>

                                                <!-- Progress Bar for Current Shift -->
                                                <div v-if="isCurrentShift(shift)" class="progress-container">
                                                    <div class="d-flex justify-content-between mb-1">
                                                        <small class="text-muted">Progresso do turno</small>
                                                        <small class="text-muted">{{ getShiftProgress(shift) }}%</small>
                                                    </div>
                                                    <div class="progress" style="height: 6px;">
                                                        <div 
                                                            class="progress-bar bg-success" 
                                                            role="progressbar" 
                                                            :style="{ width: getShiftProgress(shift) + '%' }"
                                                        ></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Info -->
            <div class="mt-5 py-4 border-top">
                <div class="row align-items-center text-muted">
                    <div class="col-md-6">
                        <small>
                            <vue-feather type="refresh-cw" size="14" class="me-1"></vue-feather>
                            Atualizado automaticamente a cada 5 minutos
                        </small>
                    </div>
                    <div class="col-md-6 text-end">
                        <small>
                            Última atualização: {{ lastUpdated }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import axios from 'axios';
import VueFeather from 'vue-feather';
import moment from 'moment';

// Configure moment to Portuguese
moment.locale('pt-br');

// Reactive data
const selectedDate = ref(moment().format('YYYY-MM-DD'));
const daySchedule = ref(null);
const currentStatus = ref({});
const loading = ref(false);
const currentTime = ref(moment().format('HH:mm:ss'));
const lastUpdated = ref(moment().format('HH:mm:ss'));
let intervalId = null;
let refreshIntervalId = null;

// Computed
const selectedDateFormatted = computed(() => {
    return moment(selectedDate.value).format('DD [de] MMMM [de] YYYY');
});

const selectedDateWeekday = computed(() => {
    return moment(selectedDate.value).format('dddd').charAt(0).toUpperCase() + 
           moment(selectedDate.value).format('dddd').slice(1);
});

const currentDate = computed(() => {
    return moment().format('DD [de] MMMM [de] YYYY');
});

const currentTimeFormatted = computed(() => {
    return currentTime.value;
});

const currentShift = computed(() => {
    if (!daySchedule.value?.shifts) return null;
    
    const now = moment();
    const today = moment(selectedDate.value);
    
    // Only show current shift if viewing today
    if (!today.isSame(now, 'day')) return null;
    
    return daySchedule.value.shifts.find(shift => {
        const shiftStart = moment(`${selectedDate.value} ${shift.start_time}`);
        const shiftEnd = moment(`${selectedDate.value} ${shift.end_time}`);
        return now.isBetween(shiftStart, shiftEnd, null, '[]');
    });
});

const nextShift = computed(() => {
    if (!daySchedule.value?.shifts || currentShift.value) return null;
    
    const now = moment();
    const today = moment(selectedDate.value);
    
    // Only show next shift if viewing today
    if (!today.isSame(now, 'day')) return null;
    
    const upcomingShifts = daySchedule.value.shifts
        .filter(shift => {
            const shiftStart = moment(`${selectedDate.value} ${shift.start_time}`);
            return shiftStart.isAfter(now);
        })
        .sort((a, b) => {
            const timeA = moment(`${selectedDate.value} ${a.start_time}`);
            const timeB = moment(`${selectedDate.value} ${b.start_time}`);
            return timeA.diff(timeB);
        });
    
    return upcomingShifts[0] || null;
});

// Methods
const loadScheduleForDate = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/work-schedule/client-view', {
            params: { date: selectedDate.value }
        });
        daySchedule.value = response.data;
        updateCurrentStatus();
        lastUpdated.value = moment().format('HH:mm:ss');
    } catch (error) {
        console.error('Erro ao carregar escala:', error);
        daySchedule.value = null;
    } finally {
        loading.value = false;
    }
};

const updateCurrentStatus = () => {
    if (!daySchedule.value?.shifts) {
        currentStatus.value = {
            technicians_on_duty: 0,
            active_shifts: 0
        };
        return;
    }
    
    const now = moment();
    const today = moment(selectedDate.value);
    
    if (today.isSame(now, 'day')) {
        const activeShifts = daySchedule.value.shifts.filter(shift => {
            const shiftStart = moment(`${selectedDate.value} ${shift.start_time}`);
            const shiftEnd = moment(`${selectedDate.value} ${shift.end_time}`);
            return now.isBetween(shiftStart, shiftEnd, null, '[]');
        });
        
        const techniciansOnDuty = activeShifts.reduce((total, shift) => {
            return total + (shift.technicians?.length || 0);
        }, 0);
        
        currentStatus.value = {
            technicians_on_duty: techniciansOnDuty,
            active_shifts: activeShifts.length
        };
    } else {
        currentStatus.value = {
            technicians_on_duty: 0,
            active_shifts: 0
        };
    }
};

const changeDate = (days) => {
    selectedDate.value = moment(selectedDate.value).add(days, 'days').format('YYYY-MM-DD');
    loadScheduleForDate();
};

const setToday = () => {
    selectedDate.value = moment().format('YYYY-MM-DD');
    loadScheduleForDate();
};

const isCurrentShift = (shift) => {
    const now = moment();
    const today = moment(selectedDate.value);
    
    if (!today.isSame(now, 'day')) return false;
    
    const shiftStart = moment(`${selectedDate.value} ${shift.start_time}`);
    const shiftEnd = moment(`${selectedDate.value} ${shift.end_time}`);
    return now.isBetween(shiftStart, shiftEnd, null, '[]');
};

const isUpcomingShift = (shift) => {
    const now = moment();
    const today = moment(selectedDate.value);
    
    if (!today.isSame(now, 'day')) return false;
    
    const shiftStart = moment(`${selectedDate.value} ${shift.start_time}`);
    return shiftStart.isAfter(now);
};

const isFinishedShift = (shift) => {
    const now = moment();
    const today = moment(selectedDate.value);
    
    if (!today.isSame(now, 'day')) return false;
    
    const shiftEnd = moment(`${selectedDate.value} ${shift.end_time}`);
    return shiftEnd.isBefore(now);
};

const getShiftStatusText = (shift) => {
    if (isCurrentShift(shift)) return 'Em Andamento';
    if (isUpcomingShift(shift)) return 'Programado';
    if (isFinishedShift(shift)) return 'Finalizado';
    return 'Programado';
};

const getShiftProgress = (shift) => {
    const now = moment();
    const shiftStart = moment(`${selectedDate.value} ${shift.start_time}`);
    const shiftEnd = moment(`${selectedDate.value} ${shift.end_time}`);
    
    const totalDuration = shiftEnd.diff(shiftStart);
    const elapsed = now.diff(shiftStart);
    
    return Math.min(Math.max((elapsed / totalDuration) * 100, 0), 100).toFixed(0);
};

const getInitials = (name) => {
    return name
        .split(' ')
        .map(word => word.charAt(0).toUpperCase())
        .slice(0, 2)
        .join('');
};

const updateTime = () => {
    currentTime.value = moment().format('HH:mm:ss');
    updateCurrentStatus();
};

// Lifecycle
onMounted(() => {
    loadScheduleForDate();
    
    // Update time every second
    intervalId = setInterval(updateTime, 1000);
    
    // Refresh schedule every 5 minutes
    refreshIntervalId = setInterval(() => {
        loadScheduleForDate();
    }, 5 * 60 * 1000);
});

onUnmounted(() => {
    if (intervalId) clearInterval(intervalId);
    if (refreshIntervalId) clearInterval(refreshIntervalId);
});
</script>

<style scoped>
/* Header Styles */
.bg-primary {
    background: linear-gradient(135deg, #0d6efd 0%, #0056b3 100%) !important;
}

/* Card Styles */
.card {
    border: 1px solid rgba(0, 0, 0, 0.125);
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.shift-card {
    cursor: pointer;
}

.shift-card.border-success {
    border-color: #198754 !important;
    background-color: rgba(25, 135, 84, 0.05);
}

.shift-card.border-warning {
    border-color: #ffc107 !important;
    background-color: rgba(255, 193, 7, 0.05);
}

.shift-card.border-secondary {
    border-color: #6c757d !important;
    background-color: rgba(108, 117, 125, 0.05);
}

/* Technician Styles */
.technicians-list {
    max-height: 200px;
    overflow-y: auto;
}

.technician-item {
    padding: 0.5rem;
    border-radius: 0.375rem;
    background-color: rgba(0, 0, 0, 0.03);
    border: 1px solid rgba(0, 0, 0, 0.1);
}

.avatar-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #0d6efd 0%, #0056b3 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.875rem;
}

/* Progress Bar Styles */
.progress {
    border-radius: 10px;
    background-color: rgba(0, 0, 0, 0.1);
}

.progress-bar {
    border-radius: 10px;
    transition: width 0.3s ease;
}

/* Alert Styles */
.alert {
    border: none;
    border-radius: 0.5rem;
}

.alert-success {
    background-color: rgba(25, 135, 84, 0.1);
    color: #0f5132;
}

.alert-warning {
    background-color: rgba(255, 193, 7, 0.1);
    color: #664d03;
}

.alert-info {
    background-color: rgba(13, 110, 253, 0.1);
    color: #055160;
}

/* Button Styles */
.btn-group .btn {
    border-color: #0d6efd;
}

.btn-outline-primary:hover {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

/* Badge Styles */
.badge {
    font-size: 0.75rem;
    padding: 0.375rem 0.75rem;
}

/* Input Styles */
.form-control:focus {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

/* Loading Styles */
.spinner-border {
    width: 2rem;
    height: 2rem;
}

/* Responsive Styles */
@media (max-width: 768px) {
    .container {
        padding: 0 1rem;
    }
    
    .btn-group {
        width: 100%;
    }
    
    .btn-group .btn {
        flex: 1;
    }
    
    .technician-item {
        padding: 0.75rem 0.5rem;
    }
    
    .avatar-circle {
        width: 35px;
        height: 35px;
        font-size: 0.8rem;
    }
    
    .card-header {
        padding: 1rem 0.75rem;
    }
    
    .card-body {
        padding: 1rem 0.75rem;
    }
}

/* Animation */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.card {
    animation: fadeIn 0.3s ease;
}

/* Custom Scrollbar */
.technicians-list::-webkit-scrollbar {
    width: 6px;
}

.technicians-list::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.technicians-list::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 10px;
}

.technicians-list::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style>