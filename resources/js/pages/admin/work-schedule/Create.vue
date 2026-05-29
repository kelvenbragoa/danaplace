<template>
    <div>
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">Nova Escala de Trabalho</h1>
                <p class="text-muted">Crie uma nova escala mensal para organizar os turnos dos técnicos</p>
            </div>
            <router-link 
                to="/admin/work-schedule/schedules" 
                class="btn btn-outline-secondary"
            >
                <vue-feather type="arrow-left" size="16" class="me-2"></vue-feather>
                Voltar
            </router-link>
        </div>

        <!-- Form -->
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Informações Básicas</h5>
                    </div>
                    <div class="card-body">
                        <Form 
                            @submit="onSubmit" 
                            :validation-schema="schema" 
                            v-slot="{ errors }"
                        >
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="name" class="form-label">Nome da Escala *</label>
                                    <Field
                                        name="name"
                                        type="text"
                                        class="form-control"
                                        :class="{ 'is-invalid': errors.name }"
                                        placeholder="Ex: Escala de Janeiro 2024"
                                    />
                                    <div v-if="errors.name" class="invalid-feedback">
                                        {{ errors.name }}
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="month" class="form-label">Mês *</label>
                                    <Field
                                        name="month"
                                        as="select"
                                        class="form-select"
                                        :class="{ 'is-invalid': errors.month }"
                                    >
                                        <option value="">Selecione o mês</option>
                                        <option 
                                            v-for="month in months" 
                                            :key="month.value" 
                                            :value="month.value"
                                        >
                                            {{ month.label }}
                                        </option>
                                    </Field>
                                    <div v-if="errors.month" class="invalid-feedback">
                                        {{ errors.month }}
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="year" class="form-label">Ano *</label>
                                    <Field
                                        name="year"
                                        as="select"
                                        class="form-select"
                                        :class="{ 'is-invalid': errors.year }"
                                    >
                                        <option value="">Selecione o ano</option>
                                        <option 
                                            v-for="year in availableYears" 
                                            :key="year" 
                                            :value="year"
                                        >
                                            {{ year }}
                                        </option>
                                    </Field>
                                    <div v-if="errors.year" class="invalid-feedback">
                                        {{ errors.year }}
                                    </div>
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label for="description" class="form-label">Descrição</label>
                                    <Field
                                        name="description"
                                        as="textarea"
                                        rows="3"
                                        class="form-control"
                                        :class="{ 'is-invalid': errors.description }"
                                        placeholder="Descrição opcional da escala..."
                                    />
                                    <div v-if="errors.description" class="invalid-feedback">
                                        {{ errors.description }}
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="status" class="form-label">Status Inicial *</label>
                                    <Field
                                        name="status"
                                        as="select"
                                        class="form-select"
                                        :class="{ 'is-invalid': errors.status }"
                                    >
                                        <option value="draft">Rascunho</option>
                                        <option value="published">Publicada</option>
                                    </Field>
                                    <div v-if="errors.status" class="invalid-feedback">
                                        {{ errors.status }}
                                    </div>
                                    <div class="form-text">
                                        Escalas em rascunho não são visíveis para os clientes
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Opções</label>
                                    <div class="form-check">
                                        <Field
                                            name="auto_generate_days"
                                            type="checkbox"
                                            class="form-check-input"
                                            id="auto_generate_days"
                                            :value="true"
                                            :unchecked-value="false"
                                        />
                                        <label class="form-check-label" for="auto_generate_days">
                                            Gerar todos os dias do mês automaticamente
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <Field
                                            name="copy_from_template"
                                            type="checkbox"
                                            class="form-check-input"
                                            id="copy_from_template"
                                            v-model="copyFromTemplate"
                                        />
                                        <label class="form-check-label" for="copy_from_template">
                                            Copiar de uma escala existente
                                        </label>
                                    </div>
                                </div>

                                <!-- Template Selection -->
                                <div v-if="copyFromTemplate" class="col-md-12 mb-3">
                                    <label for="template_schedule_id" class="form-label">Escala Modelo</label>
                                    <Field
                                        name="template_schedule_id"
                                        as="select"
                                        class="form-select"
                                        :class="{ 'is-invalid': errors.template_schedule_id }"
                                    >
                                        <option value="">Selecione uma escala para copiar</option>
                                        <option 
                                            v-for="schedule in availableSchedules" 
                                            :key="schedule.id" 
                                            :value="schedule.id"
                                        >
                                            {{ schedule.name }} - {{ getMonthName(schedule.month) }} {{ schedule.year }}
                                        </option>
                                    </Field>
                                    <div v-if="errors.template_schedule_id" class="invalid-feedback">
                                        {{ errors.template_schedule_id }}
                                    </div>
                                    
                                    <div class="mt-2">
                                        <div class="form-check form-check-inline">
                                            <Field
                                                name="copy_shifts_only"
                                                type="checkbox"
                                                class="form-check-input"
                                                id="copy_shifts_only"
                                            />
                                            <label class="form-check-label" for="copy_shifts_only">
                                                Copiar apenas turnos (sem técnicos)
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="d-flex justify-content-end gap-2 mt-4">
                                <router-link 
                                    to="/admin/work-schedule/schedules" 
                                    class="btn btn-outline-secondary"
                                >
                                    Cancelar
                                </router-link>
                                <button 
                                    type="submit" 
                                    class="btn btn-primary"
                                    :disabled="loading"
                                >
                                    <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                                    <vue-feather v-else type="save" size="16" class="me-2"></vue-feather>
                                    Criar Escala
                                </button>
                            </div>
                        </Form>
                    </div>
                </div>
            </div>

            <!-- Sidebar with help and preview -->
            <div class="col-lg-4">
                <!-- Help Card -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <vue-feather type="help-circle" size="16" class="me-2"></vue-feather>
                            Como funciona?
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <h6 class="text-primary">1. Informações Básicas</h6>
                            <small class="text-muted">
                                Defina o nome, mês e ano da escala. O status determina se será visível para os clientes.
                            </small>
                        </div>
                        <div class="mb-3">
                            <h6 class="text-success">2. Geração Automática</h6>
                            <small class="text-muted">
                                Marque "Gerar todos os dias" para criar automaticamente os dias do mês.
                            </small>
                        </div>
                        <div class="mb-0">
                            <h6 class="text-warning">3. Cópia de Modelo</h6>
                            <small class="text-muted">
                                Copie uma escala existente para reaproveitar turnos e técnicos.
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <vue-feather type="bar-chart" size="16" class="me-2"></vue-feather>
                            Estatísticas Atuais
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6">
                                <h5 class="text-primary mb-0">{{ stats.total_schedules || 0 }}</h5>
                                <small class="text-muted">Escalas</small>
                            </div>
                            <div class="col-6">
                                <h5 class="text-success mb-0">{{ stats.active_technicians || 0 }}</h5>
                                <small class="text-muted">Técnicos</small>
                            </div>
                        </div>
                        <hr>
                        <div class="row text-center">
                            <div class="col-6">
                                <h5 class="text-warning mb-0">{{ stats.total_shifts || 0 }}</h5>
                                <small class="text-muted">Turnos Ativos</small>
                            </div>
                            <div class="col-6">
                                <h5 class="text-info mb-0">{{ stats.this_month_assignments || 0 }}</h5>
                                <small class="text-muted">Escalações</small>
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
import { useRouter } from 'vue-router';
import { Form, Field } from 'vee-validate';
import * as yup from 'yup';
import axios from 'axios';
import VueFeather from 'vue-feather';
import { useToast } from '@/composables/useToast';

const { showToast } = useToast();
const router = useRouter();

// Reactive data
const loading = ref(false);
const copyFromTemplate = ref(false);
const availableSchedules = ref([]);
const stats = ref({});

// Validation schema
const schema = yup.object({
    name: yup.string().required('Nome da escala é obrigatório').max(255, 'Nome muito longo'),
    month: yup.number().required('Mês é obrigatório').min(1).max(12),
    year: yup.number().required('Ano é obrigatório').min(2020).max(2030),
    description: yup.string().nullable().max(1000, 'Descrição muito longa'),
    status: yup.string().required('Status é obrigatório').oneOf(['draft', 'published'], 'Status inválido'),
    auto_generate_days: yup.boolean(),
    copy_from_template: yup.boolean(),
    template_schedule_id: yup.number().nullable().when('copy_from_template', {
        is: true,
        then: yup.number().required('Selecione uma escala modelo')
    }),
    copy_shifts_only: yup.boolean()
});

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

const availableYears = computed(() => {
    const currentYear = new Date().getFullYear();
    return Array.from({ length: 5 }, (_, i) => currentYear - 1 + i);
});

// Methods
const getInitialData = async () => {
    try {
        // Buscar escalas existentes para usar como templates
        const schedulesResponse = await axios.get('/work-schedule', {
            params: { paginate: false }
        });
        
        availableSchedules.value = schedulesResponse.data;
        
        // Stats podem vir do dashboard
        const statsResponse = await axios.get('/work-schedule/dashboard');
        stats.value = statsResponse.data.stats;
    } catch (error) {
        console.error('Erro ao carregar dados iniciais:', error);
        showToast('Erro ao carregar dados iniciais', 'error');
    }
};

const getMonthName = (month) => {
    const monthObj = months.value.find(m => m.value === month);
    return monthObj ? monthObj.label : month;
};

const onSubmit = async (values) => {
    loading.value = true;
    
    try {
        const response = await axios.post('/work-schedule', values);
        showToast('Escala criada com sucesso!', 'success');
        
        // Redirect to the created schedule
        router.push(`/admin/work-schedule/schedules/${response.data.id}`);
    } catch (error) {
        console.error('Erro ao criar escala:', error);
        
        if (error.response?.data?.errors) {
            // Form validation errors are handled by vee-validate
            const firstError = Object.values(error.response.data.errors)[0];
            if (firstError && firstError[0]) {
                showToast(firstError[0], 'error');
            }
        } else if (error.response?.data?.message) {
            showToast(error.response.data.message, 'error');
        } else {
            showToast('Erro ao criar escala', 'error');
        }
    } finally {
        loading.value = false;
    }
};

// Lifecycle
onMounted(() => {
    getInitialData();
});
</script>

<style scoped>
.form-check {
    margin-bottom: 0.5rem;
}

.form-check-inline {
    margin-right: 1rem;
}

.card-title {
    font-size: 0.95rem;
    font-weight: 600;
}

.text-muted {
    font-size: 0.875rem;
}

hr {
    margin: 1rem 0;
}

.btn {
    border-radius: 0.375rem;
}

.form-text {
    font-size: 0.8rem;
    margin-top: 0.25rem;
}

/* Custom checkbox styling */
.form-check-input:checked {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.form-check-input:focus {
    border-color: #86b7fe;
    outline: 0;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.spinner-border-sm {
    width: 1rem;
    height: 1rem;
}
</style>