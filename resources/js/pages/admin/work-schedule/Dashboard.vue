<template>
    <div>
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">Dashboard da Escala de Trabalho</h1>
                <p class="text-muted">Visão geral das escalas e turnos dos técnicos</p>
            </div>
            <div class="d-flex gap-2">
                <router-link 
                    to="/admin/work-schedule/schedules" 
                    class="btn btn-outline-primary"
                >
                    <vue-feather type="calendar" size="16" class="me-2"></vue-feather>
                    Ver Escalas
                </router-link>
                <router-link 
                    to="/admin/work-schedule/schedules/create" 
                    class="btn btn-primary"
                >
                    <vue-feather type="plus" size="16" class="me-2"></vue-feather>
                    Nova Escala
                </router-link>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-primary h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title text-primary mb-1">Técnicos Hoje</h6>
                                <h3 class="card-text text-primary mb-0">{{ dashboard.technicians_today || 0 }}</h3>
                                <small class="text-muted">Em serviço agora</small>
                            </div>
                            <div class="align-self-center">
                                <vue-feather type="users" size="32" class="text-primary"></vue-feather>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-success h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title text-success mb-1">Técnicos Amanhã</h6>
                                <h3 class="card-text text-success mb-0">{{ dashboard.technicians_tomorrow || 0 }}</h3>
                                <small class="text-muted">Escalados</small>
                            </div>
                            <div class="align-self-center">
                                <vue-feather type="user-check" size="32" class="text-success"></vue-feather>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-warning h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title text-warning mb-1">Turnos Este Mês</h6>
                                <h3 class="card-text text-warning mb-0">{{ dashboard.shifts_this_month || 0 }}</h3>
                                <small class="text-muted">Total de turnos</small>
                            </div>
                            <div class="align-self-center">
                                <vue-feather type="clock" size="32" class="text-warning"></vue-feather>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-info h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title text-info mb-1">Escalas Ativas</h6>
                                <h3 class="card-text text-info mb-0">{{ dashboard.active_schedules || 0 }}</h3>
                                <small class="text-muted">Este mês</small>
                            </div>
                            <div class="align-self-center">
                                <vue-feather type="calendar" size="32" class="text-info"></vue-feather>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Current Shifts Today -->
        <div class="row mb-4">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Turnos de Hoje</h5>
                        <span class="badge bg-primary">{{ formatDate(new Date(), 'DD/MM/YYYY') }}</span>
                    </div>
                    <div class="card-body">
                        <div v-if="loadingToday" class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Carregando...</span>
                            </div>
                        </div>
                        <div v-else-if="shiftsToday.length === 0" class="text-center py-4 text-muted">
                            <vue-feather type="calendar" size="48" class="mb-3 opacity-50"></vue-feather>
                            <p class="mb-0">Nenhum turno programado para hoje</p>
                        </div>
                        <div v-else>
                            <div 
                                v-for="shift in shiftsToday" 
                                :key="shift.id"
                                class="border rounded p-3 mb-3"
                                :class="getShiftStatusClass(shift)"
                            >
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">{{ shift.shift_name }}</h6>
                                        <small class="text-muted">
                                            <vue-feather type="clock" size="14" class="me-1"></vue-feather>
                                            {{ shift.start_time }} - {{ shift.end_time }}
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge" :class="getShiftBadgeClass(shift)">
                                            {{ getShiftStatus(shift) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="mt-2">
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
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Próximos Turnos</h5>
                    </div>
                    <div class="card-body">
                        <div v-if="loadingUpcoming" class="text-center py-3">
                            <div class="spinner-border spinner-border-sm text-primary" role="status">
                                <span class="visually-hidden">Carregando...</span>
                            </div>
                        </div>
                        <div v-else-if="upcomingShifts.length === 0" class="text-center py-3 text-muted">
                            <p class="mb-0 small">Nenhum turno próximo</p>
                        </div>
                        <div v-else>
                            <div 
                                v-for="shift in upcomingShifts.slice(0, 5)" 
                                :key="`${shift.date}-${shift.id}`"
                                class="d-flex justify-content-between align-items-center py-2 border-bottom"
                            >
                                <div>
                                    <div class="fw-semibold small">{{ shift.shift_name }}</div>
                                    <div class="text-muted small">
                                        {{ formatDate(shift.date, 'DD/MM') }} - {{ shift.start_time }}
                                    </div>
                                </div>
                                <div>
                                    <span class="badge bg-light text-dark small">
                                        {{ shift.technicians.length }} técnico{{ shift.technicians.length !== 1 ? 's' : '' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Ações Rápidas</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <router-link 
                                to="/admin/work-schedule/shifts" 
                                class="btn btn-outline-primary btn-sm"
                            >
                                <vue-feather type="clock" size="14" class="me-2"></vue-feather>
                                Gerenciar Turnos
                            </router-link>
                            <router-link 
                                to="/admin/work-schedule/technicians" 
                                class="btn btn-outline-success btn-sm"
                            >
                                <vue-feather type="users" size="14" class="me-2"></vue-feather>
                                Ver Técnicos
                            </router-link>
                            <router-link 
                                to="/admin/work-schedule/client-view" 
                                class="btn btn-outline-info btn-sm"
                            >
                                <vue-feather type="eye" size="14" class="me-2"></vue-feather>
                                Visão do Cliente
                            </router-link>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Overview -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Visão Geral do Mês</h5>
                        <div class="d-flex gap-2">
                            <select 
                                v-model="selectedMonth" 
                                @change="updateOverview"
                                class="form-select form-select-sm"
                                style="width: auto;"
                            >
                                <option 
                                    v-for="month in months" 
                                    :key="month.value" 
                                    :value="month.value"
                                >
                                    {{ month.label }}
                                </option>
                            </select>
                            <select 
                                v-model="selectedYear" 
                                @change="updateOverview"
                                class="form-select form-select-sm"
                                style="width: auto;"
                            >
                                <option 
                                    v-for="year in years" 
                                    :key="year" 
                                    :value="year"
                                >
                                    {{ year }}
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="card-body">
                        <div v-if="loadingOverview" class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Carregando...</span>
                            </div>
                        </div>
                        <div v-else class="row">
                            <div class="col-md-3 text-center">
                                <h4 class="text-primary">{{ monthlyOverview.total_days || 0 }}</h4>
                                <small class="text-muted">Dias com Escala</small>
                            </div>
                            <div class="col-md-3 text-center">
                                <h4 class="text-success">{{ monthlyOverview.total_shifts || 0 }}</h4>
                                <small class="text-muted">Total de Turnos</small>
                            </div>
                            <div class="col-md-3 text-center">
                                <h4 class="text-warning">{{ monthlyOverview.total_assignments || 0 }}</h4>
                                <small class="text-muted">Escalações</small>
                            </div>
                            <div class="col-md-3 text-center">
                                <h4 class="text-info">{{ monthlyOverview.active_technicians || 0 }}</h4>
                                <small class="text-muted">Técnicos Ativos</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import VueFeather from 'vue-feather';
import { useToast } from '@/composables/useToast';
import moment from 'moment';

const { showToast } = useToast();

// Reactive data
const dashboard = ref({});
const shiftsToday = ref([]);
const upcomingShifts = ref([]);
const monthlyOverview = ref({});
const loadingToday = ref(false);
const loadingUpcoming = ref(false);
const loadingOverview = ref(false);
const selectedMonth = ref(new Date().getMonth() + 1);
const selectedYear = ref(new Date().getFullYear());

// Computed
const months = computed(() => [
    { value: 1, label: 'Janeiro' },
    { value: 2, label: 'Fevereiro' },
    { value: 3, label: 'Março' },
    { value: 4, label: 'Abril' },
    { value: 5, label: 'Maio' },
    { value: 6, label: 'Junho' },
    { value: 7, label: 'Julho' },
    { value: 8, label: 'Agosto' },
    { value: 9, label: 'Setembro' },
    { value: 10, label: 'Outubro' },
    { value: 11, label: 'Novembro' },
    { value: 12, label: 'Dezembro' }
]);

const years = computed(() => {
    const currentYear = new Date().getFullYear();
    return Array.from({ length: 5 }, (_, i) => currentYear - 2 + i);
});

// Methods
const getDashboardData = async () => {
    try {
        const response = await axios.get('/work-schedule/dashboard');
        const data = response.data;
        
        // Atualizar todos os dados do dashboard
        dashboard.value = data;
        
        // Atualizar turnos de hoje baseado nos current_shifts
        shiftsToday.value = data.current_shifts || [];
        
        // Atualizar próximos turnos
        upcomingShifts.value = data.upcoming_shifts || [];
        
        // Se houver escala atual, criar visão geral mensal
        if (data.current_schedule) {
            monthlyOverview.value = {
                schedule: data.current_schedule,
                stats: data.stats
            };
        }
    } catch (error) {
        console.error('Erro ao carregar dashboard:', error);
        showToast('Erro ao carregar dados do dashboard', 'error');
    }
};

const getTodayShifts = async () => {
    // Método removido - dados vêm do getDashboardData
    await getDashboardData();
};

const getUpcomingShifts = async () => {
    // Método removido - dados vêm do getDashboardData
    await getDashboardData();
};

const updateOverview = async () => {
    // Recarrega o dashboard completo para obter dados atualizados
    await getDashboardData();
};

const formatDate = (date, format = 'DD/MM/YYYY') => {
    return moment(date).format(format);
};

const getShiftStatusClass = (shift) => {
    const now = moment();
    const shiftStart = moment(`${shift.date} ${shift.start_time}`);
    const shiftEnd = moment(`${shift.date} ${shift.end_time}`);
    
    if (now.isBetween(shiftStart, shiftEnd)) {
        return 'border-success bg-success bg-opacity-10';
    } else if (now.isAfter(shiftEnd)) {
        return 'border-secondary bg-secondary bg-opacity-10';
    } else {
        return 'border-warning bg-warning bg-opacity-10';
    }
};

const getShiftBadgeClass = (shift) => {
    const now = moment();
    const shiftStart = moment(`${shift.date} ${shift.start_time}`);
    const shiftEnd = moment(`${shift.date} ${shift.end_time}`);
    
    if (now.isBetween(shiftStart, shiftEnd)) {
        return 'bg-success';
    } else if (now.isAfter(shiftEnd)) {
        return 'bg-secondary';
    } else {
        return 'bg-warning';
    }
};

const getShiftStatus = (shift) => {
    const now = moment();
    const shiftStart = moment(`${shift.date} ${shift.start_time}`);
    const shiftEnd = moment(`${shift.date} ${shift.end_time}`);
    
    if (now.isBetween(shiftStart, shiftEnd)) {
        return 'Em Andamento';
    } else if (now.isAfter(shiftEnd)) {
        return 'Finalizado';
    } else {
        return 'Programado';
    }
};

// Lifecycle
onMounted(() => {
    getDashboardData();
    getTodayShifts();
    getUpcomingShifts();
    updateOverview();
});
</script>

<style scoped>
.card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.badge {
    font-size: 0.75rem;
}

.border-bottom:last-child {
    border-bottom: none !important;
}

/* Status colors */
.bg-opacity-10 {
    --bs-bg-opacity: 0.1;
}

.spinner-border-sm {
    width: 1rem;
    height: 1rem;
}
</style>