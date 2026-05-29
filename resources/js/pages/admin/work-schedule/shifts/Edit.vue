<template>
    <div>
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">Editar Turno</h1>
                <p class="text-muted" v-if="shift">
                    Editando: {{ shift.name }} - {{ formatDate(shift.date) }}
                </p>
            </div>
            <div class="d-flex gap-2">
                <router-link 
                    to="/admin/work-schedule/shifts" 
                    class="btn btn-outline-secondary"
                >
                    <vue-feather type="arrow-left" size="16" class="me-2"></vue-feather>
                    Voltar para Lista
                </router-link>
                <router-link 
                    :to="`/admin/work-schedule/shifts/${$route.params.id}`"
                    class="btn btn-outline-info"
                    v-if="shift"
                >
                    <vue-feather type="eye" size="16" class="me-2"></vue-feather>
                    Ver Detalhes
                </router-link>
            </div>
        </div>

        <!-- Loading -->
        <div v-if="loading && !shift" class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Carregando...</span>
            </div>
        </div>

        <!-- Form -->
        <form @submit.prevent="submitForm" novalidate v-if="shift">
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Informações do Turno</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <!-- Schedule Selection -->
                                <div class="col-md-6">
                                    <Field
                                        name="schedule_id"
                                        v-slot="{ field, errorMessage }"
                                    >
                                        <label for="schedule_id" class="form-label">
                                            Escala <span class="text-danger">*</span>
                                        </label>
                                        <select
                                            id="schedule_id"
                                            v-bind="field"
                                            :value="formData.schedule_id"
                                            :model-value="formData.schedule_id"
                                            @input="formData.schedule_id = $event.target.value"
                                            class="form-select"
                                            :class="{ 'is-invalid': errorMessage }"
                                            @change="onScheduleChange"
                                        >
                                            <option value="">Selecione uma escala</option>
                                            <option 
                                                v-for="schedule in availableSchedules" 
                                                :key="schedule.id" 
                                                :value="schedule.id"
                                            >
                                                {{ schedule.name }} - {{ getMonthName(schedule.month) }} {{ schedule.year }}
                                            </option>
                                        </select>
                                        <div v-if="errorMessage" class="invalid-feedback">
                                            {{ errorMessage }}
                                        </div>
                                    </Field>
                                </div>

                                <!-- Date -->
                                <div class="col-md-6">
                                    <Field
                                        name="date"
                                        v-slot="{ field, errorMessage }"
                                    >
                                        <label for="date" class="form-label">
                                            Data <span class="text-danger">*</span>
                                        </label>
                                        <input
                                            id="date"
                                            type="date"
                                            v-bind="field"
                                            :value="formatDateForInput(formData.date)"
                                            :model-value="formatDateForInput(formData.date)"
                                            @input="formData.date = $event.target.value"
                                            class="form-control"
                                            :class="{ 'is-invalid': errorMessage }"
                                            :min="minDate"
                                            :max="maxDate"
                                        />
                                        <div v-if="errorMessage" class="invalid-feedback">
                                            {{ errorMessage }}
                                        </div>
                                        <div v-if="selectedSchedule" class="form-text">
                                            Período da escala: {{ getDateRange() }}
                                        </div>
                                    </Field>
                                </div>

                                <!-- Shift Name -->
                                <div class="col-md-6">
                                    <Field
                                        name="name"
                                        v-slot="{ field, errorMessage }"
                                    >
                                        <label for="name" class="form-label">
                                            Nome do Turno <span class="text-danger">*</span>
                                        </label>
                                        <input
                                            id="name"
                                            type="text"
                                            v-bind="field"
                                            :value="formData.name"
                                            :model-value="formData.name"
                                            @input="formData.name = $event.target.value"
                                            class="form-control"
                                            :class="{ 'is-invalid': errorMessage }"
                                            placeholder="Ex: Turno da Manhã"
                                        />
                                        <div v-if="errorMessage" class="invalid-feedback">
                                            {{ errorMessage }}
                                        </div>
                                    </Field>
                                </div>

                                <!-- Shift Type -->
                                <div class="col-md-6">
                                    <Field
                                        name="shift_type"
                                        v-slot="{ field, errorMessage }"
                                    >
                                        <label for="shift_type" class="form-label">
                                            Tipo de Turno <span class="text-danger">*</span>
                                        </label>
                                        <select
                                            id="shift_type"
                                            v-bind="field"
                                            :value="formData.shift_type"
                                            :model-value="formData.shift_type"
                                            @input="formData.shift_type = $event.target.value"
                                            class="form-select"
                                            :class="{ 'is-invalid': errorMessage }"
                                        >
                                            <option value="">Selecione o tipo</option>
                                            <option value="morning">Manhã</option>
                                            <option value="afternoon">Tarde</option>
                                            <option value="evening">Noite</option>
                                            <option value="night">Madrugada</option>
                                        </select>
                                        <div v-if="errorMessage" class="invalid-feedback">
                                            {{ errorMessage }}
                                        </div>
                                    </Field>
                                </div>

                                <!-- Start Time -->
                                <div class="col-md-6">
                                    <Field
                                        name="start_time"
                                        v-slot="{ field, errorMessage }"
                                    >
                                        <label for="start_time" class="form-label">
                                            Horário de Início <span class="text-danger">*</span>
                                        </label>
                                        <input
                                            id="start_time"
                                            type="time"
                                            v-bind="field"
                                            :value="formData.start_time"
                                            :model-value="formData.start_time"
                                            @input="formData.start_time = $event.target.value"
                                            class="form-control"
                                            :class="{ 'is-invalid': errorMessage }"
                                        />
                                        <div v-if="errorMessage" class="invalid-feedback">
                                            {{ errorMessage }}
                                        </div>
                                    </Field>
                                </div>

                                <!-- End Time -->
                                <div class="col-md-6">
                                    <Field
                                        name="end_time"
                                        v-slot="{ field, errorMessage }"
                                    >
                                        <label for="end_time" class="form-label">
                                            Horário de Término <span class="text-danger">*</span>
                                        </label>
                                        <input
                                            id="end_time"
                                            type="time"
                                            v-bind="field"
                                            :value="formData.end_time"
                                            :model-value="formData.end_time"
                                            @input="formData.end_time = $event.target.value"
                                            class="form-control"
                                            :class="{ 'is-invalid': errorMessage }"
                                        />
                                        <div v-if="errorMessage" class="invalid-feedback">
                                            {{ errorMessage }}
                                        </div>
                                        <div v-if="formData.start_time && formData.end_time" class="form-text">
                                            Duração: {{ calculateDuration(formData.start_time, formData.end_time) }}
                                        </div>
                                    </Field>
                                </div>

                                <!-- Description -->
                                <div class="col-12">
                                    <Field
                                        name="description"
                                        v-slot="{ field, errorMessage }"
                                    >
                                        <label for="description" class="form-label">Descrição</label>
                                        <textarea
                                            id="description"
                                            v-bind="field"
                                            :value="formData.description"
                                            :model-value="formData.description"
                                            @input="formData.description = $event.target.value"
                                            class="form-control"
                                            :class="{ 'is-invalid': errorMessage }"
                                            rows="3"
                                            placeholder="Descreva as responsabilidades e atividades do turno..."
                                        ></textarea>
                                        <div v-if="errorMessage" class="invalid-feedback">
                                            {{ errorMessage }}
                                        </div>
                                    </Field>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Technician Assignment -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Atribuição de Técnicos</h5>
                        </div>
                        <div class="card-body">
                            <!-- Search Technicians -->
                            <div class="mb-3">
                                <label class="form-label">Buscar Técnicos</label>
                                <input
                                    type="text"
                                    v-model="technicianSearch"
                                    @input="debouncedTechnicianSearch"
                                    class="form-control"
                                    placeholder="Digite o nome do técnico..."
                                />
                            </div>

                            <!-- Available Technicians -->
                            <div v-if="availableTechnicians.length > 0" class="mb-3">
                                <label class="form-label">Técnicos Disponíveis</label>
                                <div class="available-technicians">
                                    <div
                                        v-for="technician in availableTechnicians"
                                        :key="technician.id"
                                        class="technician-item"
                                        @click="addTechnician(technician)"
                                    >
                                        <div class="d-flex align-items-center">
                                            <div class="technician-avatar me-2">
                                                {{ getInitials(technician.name) }}
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="fw-semibold">{{ technician.name }}</div>
                                                <small class="text-muted">{{ technician.department }}</small>
                                            </div>
                                            <vue-feather type="plus" size="16" class="text-primary"></vue-feather>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Assigned Technicians -->
                            <div v-if="formData.technicians.length > 0">
                                <label class="form-label">Técnicos Atribuídos</label>
                                <div class="assigned-technicians">
                                    <div
                                        v-for="technicianId in formData.technicians"
                                        :key="technicianId"
                                        class="assigned-technician-item"
                                    >
                                        <div class="d-flex align-items-center">
                                            <div class="technician-avatar me-2">
                                                {{ getInitials(getTechnicianName(technicianId)) }}
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="fw-semibold">{{ getTechnicianName(technicianId) }}</div>
                                            </div>
                                            <button
                                                type="button"
                                                @click="removeTechnician(technicianId)"
                                                class="btn btn-sm btn-outline-danger"
                                            >
                                                <vue-feather type="x" size="14"></vue-feather>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div v-if="formData.technicians.length === 0" class="text-muted text-center py-3">
                                <vue-feather type="users" size="32" class="mb-2"></vue-feather>
                                <div>Nenhum técnico atribuído</div>
                                <small>Busque e adicione técnicos acima</small>
                            </div>
                        </div>
                    </div>

                    <!-- Shift Status -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Status do Turno</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <span 
                                    class="badge me-2"
                                    :class="getStatusBadgeClass(shift)"
                                >
                                    {{ getStatusLabel(shift) }}
                                </span>
                                <span class="text-muted">
                                    {{ getStatusDescription(shift) }}
                                </span>
                            </div>
                            
                            <div v-if="canToggleStatus(shift)" class="d-grid">
                                <button
                                    type="button"
                                    @click="toggleShiftStatus"
                                    class="btn btn-outline-secondary"
                                    :disabled="loadingStatus"
                                >
                                    <span v-if="loadingStatus" class="spinner-border spinner-border-sm me-2"></span>
                                    <vue-feather v-else type="toggle-right" size="16" class="me-2"></vue-feather>
                                    {{ getToggleStatusText(shift) }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="card">
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <button
                                    type="submit"
                                    class="btn btn-primary"
                                    :disabled="loading || !canEdit(shift)"
                                >
                                    <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                                    <vue-feather v-else type="save" size="16" class="me-2"></vue-feather>
                                    {{ loading ? 'Salvando...' : 'Salvar Alterações' }}
                                </button>
                                
                                <button
                                    type="button"
                                    @click="resetChanges"
                                    class="btn btn-outline-secondary"
                                    :disabled="loading"
                                >
                                    <vue-feather type="refresh-cw" size="16" class="me-2"></vue-feather>
                                    Desfazer Alterações
                                </button>
                            </div>
                            
                            <div v-if="!canEdit(shift)" class="mt-3">
                                <small class="text-muted">
                                    <vue-feather type="info" size="14" class="me-1"></vue-feather>
                                    Turnos passados não podem ser editados
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useForm, Field } from 'vee-validate';
import * as yup from 'yup';
import axios from 'axios';
import VueFeather from 'vue-feather';
import { useToast } from '@/composables/useToast';
import { debounce } from 'lodash';
import moment from 'moment';

// Configure moment to Portuguese
moment.locale('pt-br');

const { showToast } = useToast();
const router = useRouter();
const route = useRoute();

// Form validation schema
const schema = yup.object({
    schedule_id: yup.string().required('A escala é obrigatória'),
    date: yup.date().required('A data é obrigatória'),
    name: yup.string().required('O nome do turno é obrigatório').min(3, 'O nome deve ter pelo menos 3 caracteres'),
    shift_type: yup.string().required('O tipo de turno é obrigatório'),
    start_time: yup.string().required('O horário de início é obrigatório'),
    end_time: yup.string().required('O horário de término é obrigatório'),
    description: yup.string()
});

const { handleSubmit, resetForm: veeResetForm, setFieldValue } = useForm({
    validationSchema: schema
});

// Reactive data
const loading = ref(false);
const loadingStatus = ref(false);
const shift = ref(null);
const originalShift = ref(null);
const availableSchedules = ref([]);
const availableTechnicians = ref([]);
const allTechnicians = ref([]);
const technicianSearch = ref('');

const formData = ref({
    schedule_id: '',
    date: '',
    name: '',
    shift_type: '',
    start_time: '',
    end_time: '',
    description: '',
    technicians: []
});

// Computed
const months = computed(() => [
    'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
    'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'
]);

const selectedSchedule = computed(() => {
    return availableSchedules.value.find(s => s.id == formData.value.schedule_id);
});

const minDate = computed(() => {
    if (!selectedSchedule.value) return '';
    return moment(`${selectedSchedule.value.year}-${selectedSchedule.value.month.toString().padStart(2, '0')}-01`).format('YYYY-MM-DD');
});

const maxDate = computed(() => {
    if (!selectedSchedule.value) return '';
    return moment(minDate.value).endOf('month').format('YYYY-MM-DD');
});

// Methods
const getShift = async () => {
    loading.value = true;
    try {
        const response = await axios.get(`/work-schedule/shifts/${route.params.id}/edit`);
        shift.value = response.data.shift;
        originalShift.value = JSON.parse(JSON.stringify(response.data.shift));
        availableSchedules.value = response.data.schedules || [];
        allTechnicians.value = response.data.technicians || [];
        
        // Populate form
        formData.value = {
            schedule_id: shift.value.schedule_id,
            date: shift.value.date,
            name: shift.value.name,
            shift_type: shift.value.shift_type,
            start_time: shift.value.start_time,
            end_time: shift.value.end_time,
            description: shift.value.description || '',
            technicians: shift.value.technicians?.map(t => t.id) || []
        };
        
        filterTechnicians();
        
        // Set form field values
        setFieldValue('schedule_id', formData.value.schedule_id);
        setFieldValue('date', formData.value.date);
        setFieldValue('name', formData.value.name);
        setFieldValue('shift_type', formData.value.shift_type);
        setFieldValue('start_time', formData.value.start_time);
        setFieldValue('end_time', formData.value.end_time);
        setFieldValue('description', formData.value.description);
        
    } catch (error) {
        console.error('Erro ao carregar turno:', error);
        showToast('Erro ao carregar dados do turno', 'error');
        router.push('/admin/work-schedule/shifts');
    } finally {
        loading.value = false;
    }
};

const onScheduleChange = () => {
    // Clear date when schedule changes
    if (formData.value.date && selectedSchedule.value) {
        const selectedDate = moment(formData.value.date);
        if (!selectedDate.isBetween(minDate.value, maxDate.value, 'day', '[]')) {
            formData.value.date = '';
            setFieldValue('date', '');
        }
    }
};

const debouncedTechnicianSearch = debounce(() => {
    filterTechnicians();
}, 300);

const filterTechnicians = () => {
    if (!technicianSearch.value) {
        availableTechnicians.value = allTechnicians.value
            .filter(t => !formData.value.technicians.includes(t.id))
            .slice(0, 10);
        return;
    }
    
    const searchTerm = technicianSearch.value.toLowerCase();
    availableTechnicians.value = allTechnicians.value
        .filter(t => 
            !formData.value.technicians.includes(t.id) &&
            t.name.toLowerCase().includes(searchTerm)
        )
        .slice(0, 10);
};

const addTechnician = (technician) => {
    if (!formData.value.technicians.includes(technician.id)) {
        formData.value.technicians.push(technician.id);
        filterTechnicians();
    }
};

const removeTechnician = (technicianId) => {
    formData.value.technicians = formData.value.technicians.filter(id => id !== technicianId);
    filterTechnicians();
};

const getTechnicianName = (technicianId) => {
    const technician = allTechnicians.value.find(t => t.id === technicianId);
    return technician ? technician.name : 'Desconhecido';
};

const getInitials = (name) => {
    return name
        .split(' ')
        .map(n => n.charAt(0))
        .join('')
        .toUpperCase()
        .substring(0, 2);
};

const calculateDuration = (startTime, endTime) => {
    if (!startTime || !endTime) return '';
    
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
        return `${hours} horas`;
    } else {
        return `${hours}h ${minutes}min`;
    }
};

const getMonthName = (month) => {
    return months.value[month - 1] || month;
};

const getDateRange = () => {
    if (!selectedSchedule.value) return '';
    
    const start = moment(minDate.value).format('DD/MM');
    const end = moment(maxDate.value).format('DD/MM');
    return `${start} a ${end}`;
};

const formatDate = (date) => {
    return moment(date).format('DD/MM/YYYY');
};

const formatDateForInput = (date) => {
    if (!date) return '';
    return moment(date).format('YYYY-MM-DD');
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

const getStatusDescription = (shift) => {
    const status = getStatusLabel(shift);
    const descriptions = {
        'Em Andamento': 'O turno está acontecendo agora',
        'Programado': 'O turno ainda não começou',
        'Finalizado': 'O turno já foi concluído'
    };
    return descriptions[status] || '';
};

const canEdit = (shift) => {
    // Can edit future shifts or shifts from today
    const shiftDate = moment(shift.date);
    const today = moment().startOf('day');
    return shiftDate.isSameOrAfter(today);
};

const canToggleStatus = (shift) => {
    // Can toggle status for today's shifts only
    const shiftDate = moment(shift.date);
    const today = moment().startOf('day');
    return shiftDate.isSame(today, 'day');
};

const getToggleStatusText = (shift) => {
    const status = getStatusLabel(shift);
    return status === 'Em Andamento' ? 'Finalizar Turno' : 'Iniciar Turno';
};

const toggleShiftStatus = async () => {
    loadingStatus.value = true;
    try {
        await axios.patch(`/work-schedule/shifts/${shift.value.id}/toggle-status`);
        
        // Refresh shift data
        await getShift();
        
        showToast('Status do turno atualizado!', 'success');
    } catch (error) {
        console.error('Erro ao alterar status do turno:', error);
        showToast('Erro ao alterar status do turno', 'error');
    } finally {
        loadingStatus.value = false;
    }
};

const resetChanges = () => {
    if (originalShift.value) {
        formData.value = {
            schedule_id: originalShift.value.schedule_id,
            date: originalShift.value.date,
            name: originalShift.value.name,
            shift_type: originalShift.value.shift_type,
            start_time: originalShift.value.start_time,
            end_time: originalShift.value.end_time,
            description: originalShift.value.description || '',
            technicians: originalShift.value.technicians?.map(t => t.id) || []
        };
        
        // Reset form field values
        setFieldValue('schedule_id', formData.value.schedule_id);
        setFieldValue('date', formData.value.date);
        setFieldValue('name', formData.value.name);
        setFieldValue('shift_type', formData.value.shift_type);
        setFieldValue('start_time', formData.value.start_time);
        setFieldValue('end_time', formData.value.end_time);
        setFieldValue('description', formData.value.description);
        
        filterTechnicians();
        showToast('Alterações desfeitas', 'info');
    }
};

const submitForm = handleSubmit(async () => {
    if (!canEdit(shift.value)) {
        showToast('Este turno não pode ser editado', 'error');
        return;
    }
    
    loading.value = true;
    
    try {
        const response = await axios.put(`/work-schedule/shifts/${shift.value.id}`, {
            ...formData.value,
            technician_ids: formData.value.technicians
        });
        
        showToast('Turno atualizado com sucesso!', 'success');
        router.push('/admin/work-schedule/shifts');
    } catch (error) {
        console.error('Erro ao atualizar turno:', error);
        
        if (error.response?.data?.errors) {
            const errors = error.response.data.errors;
            Object.keys(errors).forEach(field => {
                showToast(errors[field][0], 'error');
            });
        } else {
            showToast('Erro ao atualizar turno', 'error');
        }
    } finally {
        loading.value = false;
    }
});

// Watch for changes in assigned technicians to update available list
watch(() => formData.value.technicians, () => {
    filterTechnicians();
}, { deep: true });

// Lifecycle
onMounted(() => {
    getShift();
});
</script>

<style scoped>
.card {
    border: 1px solid rgba(0, 0, 0, 0.125);
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.technician-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: linear-gradient(45deg, #007bff, #0056b3);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.75rem;
}

.available-technicians {
    max-height: 200px;
    overflow-y: auto;
}

.technician-item {
    padding: 8px 12px;
    border: 1px solid #e9ecef;
    border-radius: 6px;
    margin-bottom: 6px;
    cursor: pointer;
    transition: all 0.2s;
}

.technician-item:hover {
    background-color: #f8f9fa;
    border-color: #007bff;
}

.assigned-technician-item {
    padding: 8px 12px;
    background-color: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 6px;
    margin-bottom: 6px;
}

.assigned-technicians {
    max-height: 250px;
    overflow-y: auto;
}

.form-control:focus,
.form-select:focus {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.spinner-border-sm {
    width: 1rem;
    height: 1rem;
}

.badge {
    font-size: 0.8rem;
    padding: 0.5rem 0.75rem;
}

/* Responsive */
@media (max-width: 992px) {
    .col-lg-4 {
        margin-top: 1rem;
    }
}

@media (max-width: 768px) {
    .technician-avatar {
        width: 28px;
        height: 28px;
        font-size: 0.7rem;
    }
    
    .btn {
        font-size: 0.875rem;
    }
}
</style>