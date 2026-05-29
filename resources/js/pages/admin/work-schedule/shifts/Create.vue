<template>
    <div>
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">Criar Novo Turno</h1>
                <p class="text-muted">Configure os detalhes do turno de trabalho</p>
            </div>
            <router-link 
                to="/admin/work-schedule/shifts" 
                class="btn btn-outline-secondary"
            >
                <vue-feather type="arrow-left" size="16" class="me-2"></vue-feather>
                Voltar para Lista
            </router-link>
        </div>

        <!-- Form -->
        <form @submit.prevent="submitForm" novalidate>
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
                                            v-model="formData.schedule_id"
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
                                            v-model="formData.date"
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
                                            v-model="formData.name"
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
                                            v-model="formData.shift_type"
                                            class="form-select"
                                            :class="{ 'is-invalid': errorMessage }"
                                            @change="onShiftTypeChange"
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
                                            v-model="formData.start_time"
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
                                            v-model="formData.end_time"
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
                                            v-model="formData.description"
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

                    <!-- Quick Copy -->
                    <div class="card mb-3" v-if="availableShifts.length > 0">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Copiar de Turno Existente</h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small mb-3">
                                Selecione um turno existente para copiar suas configurações
                            </p>
                            <select
                                v-model="copyFromShift"
                                @change="copyShiftData"
                                class="form-select mb-2"
                            >
                                <option value="">Selecione um turno para copiar</option>
                                <option
                                    v-for="shift in availableShifts"
                                    :key="shift.id"
                                    :value="shift.id"
                                >
                                    {{ shift.name }} - {{ formatDate(shift.date) }}
                                </option>
                            </select>
                            <small class="text-muted">
                                Isso copiará horários, tipo e técnicos atribuídos
                            </small>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="card">
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <button
                                    type="submit"
                                    class="btn btn-primary"
                                    :disabled="loading"
                                >
                                    <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                                    <vue-feather v-else type="save" size="16" class="me-2"></vue-feather>
                                    {{ loading ? 'Salvando...' : 'Salvar Turno' }}
                                </button>
                                
                                <button
                                    type="button"
                                    @click="resetForm"
                                    class="btn btn-outline-secondary"
                                    :disabled="loading"
                                >
                                    <vue-feather type="refresh-cw" size="16" class="me-2"></vue-feather>
                                    Limpar Formulário
                                </button>
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
import { useRouter } from 'vue-router';
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
const availableSchedules = ref([]);
const availableTechnicians = ref([]);
const allTechnicians = ref([]);
const availableShifts = ref([]);
const technicianSearch = ref('');
const copyFromShift = ref('');

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
const getInitialData = async () => {
    try {
        const response = await axios.get('/work-schedule/shifts/create');
        availableSchedules.value = response.data.schedules || [];
        allTechnicians.value = response.data.technicians || [];
        availableShifts.value = response.data.shifts || [];
        availableTechnicians.value = allTechnicians.value.slice(0, 10);
    } catch (error) {
        console.error('Erro ao carregar dados iniciais:', error);
        showToast('Erro ao carregar dados do formulário', 'error');
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
    
    // Load shifts for copying
    loadShiftsForCopy();
};

const onShiftTypeChange = () => {
    // Auto-fill times based on shift type
    const timePresets = {
        morning: { start: '07:00', end: '15:00' },
        afternoon: { start: '15:00', end: '23:00' },
        evening: { start: '19:00', end: '03:00' },
        night: { start: '23:00', end: '07:00' }
    };
    
    const preset = timePresets[formData.value.shift_type];
    if (preset) {
        formData.value.start_time = preset.start;
        formData.value.end_time = preset.end;
        setFieldValue('start_time', preset.start);
        setFieldValue('end_time', preset.end);
    }
};

const loadShiftsForCopy = async () => {
    if (!formData.value.schedule_id) {
        availableShifts.value = [];
        return;
    }
    
    try {
        const response = await axios.get(`/work-schedule/shifts`, {
            params: {
                schedule_id: formData.value.schedule_id,
                for_copy: true
            }
        });
        availableShifts.value = response.data.data || [];
    } catch (error) {
        console.error('Erro ao carregar turnos para cópia:', error);
        availableShifts.value = [];
    }
};

const copyShiftData = async () => {
    if (!copyFromShift.value) return;
    
    try {
        const response = await axios.get(`/work-schedule/shifts/${copyFromShift.value}`);
        const shift = response.data;
        
        // Copy shift data (except date and schedule)
        formData.value.name = shift.name;
        formData.value.shift_type = shift.shift_type;
        formData.value.start_time = shift.start_time;
        formData.value.end_time = shift.end_time;
        formData.value.description = shift.description;
        formData.value.technicians = shift.technicians.map(t => t.id);
        
        // Update form fields
        setFieldValue('name', shift.name);
        setFieldValue('shift_type', shift.shift_type);
        setFieldValue('start_time', shift.start_time);
        setFieldValue('end_time', shift.end_time);
        setFieldValue('description', shift.description);
        
        showToast('Dados copiados com sucesso!', 'success');
        copyFromShift.value = '';
    } catch (error) {
        console.error('Erro ao copiar turno:', error);
        showToast('Erro ao copiar dados do turno', 'error');
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

const resetForm = () => {
    veeResetForm();
    formData.value = {
        schedule_id: '',
        date: '',
        name: '',
        shift_type: '',
        start_time: '',
        end_time: '',
        description: '',
        technicians: []
    };
    copyFromShift.value = '';
    technicianSearch.value = '';
    availableTechnicians.value = allTechnicians.value.slice(0, 10);
};

const submitForm = handleSubmit(async () => {
    loading.value = true;
    
    try {
        const response = await axios.post('/work-schedule/shifts', {
            ...formData.value,
            technician_ids: formData.value.technicians
        });
        
        showToast('Turno criado com sucesso!', 'success');
        router.push('/admin/work-schedule/shifts');
    } catch (error) {
        console.error('Erro ao criar turno:', error);
        
        if (error.response?.data?.errors) {
            const errors = error.response.data.errors;
            Object.keys(errors).forEach(field => {
                showToast(errors[field][0], 'error');
            });
        } else {
            showToast('Erro ao criar turno', 'error');
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
    getInitialData();
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