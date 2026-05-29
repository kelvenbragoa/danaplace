<template>
    <div>
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">Escalas de Trabalho</h1>
                <p class="text-muted">Gerencie as escalas mensais dos técnicos</p>
            </div>
            <div class="d-flex gap-2">
                <router-link 
                    to="/admin/work-schedule" 
                    class="btn btn-outline-secondary"
                >
                    <vue-feather type="arrow-left" size="16" class="me-2"></vue-feather>
                    Dashboard
                </router-link>
                <button 
                    class="btn btn-outline-primary"
                    @click="showCopyModal = true"
                    :disabled="schedules.length === 0"
                >
                    <vue-feather type="copy" size="16" class="me-2"></vue-feather>
                    Copiar Escala
                </button>
                <router-link 
                    to="/admin/work-schedule/schedules/create" 
                    class="btn btn-primary"
                >
                    <vue-feather type="plus" size="16" class="me-2"></vue-feather>
                    Nova Escala
                </router-link>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="filterYear" class="form-label">Ano</label>
                        <select 
                            id="filterYear"
                            v-model="filters.year" 
                            @change="applyFilters"
                            class="form-select"
                        >
                            <option value="">Todos os anos</option>
                            <option 
                                v-for="year in availableYears" 
                                :key="year" 
                                :value="year"
                            >
                                {{ year }}
                            </option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="filterMonth" class="form-label">Mês</label>
                        <select 
                            id="filterMonth"
                            v-model="filters.month" 
                            @change="applyFilters"
                            class="form-select"
                        >
                            <option value="">Todos os meses</option>
                            <option 
                                v-for="month in months" 
                                :key="month.value" 
                                :value="month.value"
                            >
                                {{ month.label }}
                            </option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="filterStatus" class="form-label">Status</label>
                        <select 
                            id="filterStatus"
                            v-model="filters.status" 
                            @change="applyFilters"
                            class="form-select"
                        >
                            <option value="">Todos os status</option>
                            <option value="draft">Rascunho</option>
                            <option value="published">Publicada</option>
                            <option value="archived">Arquivada</option>
                        </select>
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

        <!-- Schedules List -->
        <div v-if="loading" class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Carregando...</span>
            </div>
        </div>

        <div v-else-if="schedules.length === 0" class="text-center py-5">
            <vue-feather type="calendar" size="64" class="text-muted mb-3"></vue-feather>
            <h4 class="text-muted mb-2">Nenhuma escala encontrada</h4>
            <p class="text-muted mb-4">Crie uma nova escala para começar a organizar os turnos dos técnicos</p>
            <router-link 
                to="/admin/work-schedule/schedules/create" 
                class="btn btn-primary"
            >
                <vue-feather type="plus" size="16" class="me-2"></vue-feather>
                Criar Primeira Escala
            </router-link>
        </div>

        <div v-else class="row">
            <div 
                v-for="schedule in schedules" 
                :key="schedule.id"
                class="col-lg-4 col-md-6 mb-4"
            >
                <div class="card h-100 schedule-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">{{ schedule.name }}</h6>
                            <small class="text-muted">
                                {{ getMonthName(schedule.month) }} {{ schedule.year }}
                            </small>
                        </div>
                        <span class="badge" :class="getStatusBadgeClass(schedule.status)">
                            {{ getStatusLabel(schedule.status) }}
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="row text-center mb-3">
                            <div class="col-4">
                                <h5 class="text-primary mb-0">{{ schedule.total_days || 0 }}</h5>
                                <small class="text-muted">Dias</small>
                            </div>
                            <div class="col-4">
                                <h5 class="text-success mb-0">{{ schedule.total_shifts || 0 }}</h5>
                                <small class="text-muted">Turnos</small>
                            </div>
                            <div class="col-4">
                                <h5 class="text-warning mb-0">{{ schedule.total_technicians || 0 }}</h5>
                                <small class="text-muted">Técnicos</small>
                            </div>
                        </div>

                        <!-- Progress Bar -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <small class="text-muted">Progresso do mês</small>
                                <small class="text-muted">{{ schedule.completion_percentage || 0 }}%</small>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div 
                                    class="progress-bar" 
                                    role="progressbar" 
                                    :style="{ width: (schedule.completion_percentage || 0) + '%' }"
                                    :class="getProgressBarClass(schedule.completion_percentage)"
                                ></div>
                            </div>
                        </div>

                        <!-- Recent Activity -->
                        <div v-if="schedule.last_updated_by" class="mb-3">
                            <small class="text-muted">
                                <vue-feather type="user" size="12" class="me-1"></vue-feather>
                                Atualizada por {{ schedule.last_updated_by }} em {{ formatDate(schedule.updated_at) }}
                            </small>
                        </div>

                        <!-- Quick Stats -->
                        <div class="row text-center">
                            <div class="col-6">
                                <small class="text-muted d-block">Próximo Turno</small>
                                <span class="fw-semibold small">
                                    {{ schedule.next_shift || 'Nenhum' }}
                                </span>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Última Atualização</small>
                                <span class="fw-semibold small">
                                    {{ formatDateRelative(schedule.updated_at) }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent">
                        <div class="d-flex gap-1">
                            <router-link 
                                :to="`/admin/work-schedule/schedules/${schedule.id}`"
                                class="btn btn-outline-primary btn-sm flex-fill"
                            >
                                <vue-feather type="eye" size="14" class="me-1"></vue-feather>
                                Ver
                            </router-link>
                            <router-link 
                                :to="`/admin/work-schedule/schedules/${schedule.id}/edit`"
                                class="btn btn-outline-secondary btn-sm flex-fill"
                                v-if="canEdit(schedule)"
                            >
                                <vue-feather type="edit" size="14" class="me-1"></vue-feather>
                                Editar
                            </router-link>
                            <button 
                                @click="confirmDelete(schedule)"
                                class="btn btn-outline-danger btn-sm"
                                v-if="canDelete(schedule)"
                                :disabled="loadingDelete === schedule.id"
                            >
                                <span v-if="loadingDelete === schedule.id" class="spinner-border spinner-border-sm"></span>
                                <vue-feather v-else type="trash-2" size="14"></vue-feather>
                            </button>
                        </div>
                    </div>
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

        <!-- Copy Schedule Modal -->
        <div 
            v-if="showCopyModal" 
            class="modal fade show d-block" 
            tabindex="-1" 
            style="background-color: rgba(0,0,0,0.5);"
            @click.self="showCopyModal = false"
        >
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Copiar Escala</h5>
                        <button 
                            type="button" 
                            class="btn-close" 
                            @click="showCopyModal = false"
                        ></button>
                    </div>
                    <div class="modal-body">
                        <form @submit.prevent="copySchedule">
                            <div class="mb-3">
                                <label for="sourceSchedule" class="form-label">Escala Origem</label>
                                <select 
                                    id="sourceSchedule"
                                    v-model="copyForm.source_id" 
                                    class="form-select"
                                    required
                                >
                                    <option value="">Selecione a escala para copiar</option>
                                    <option 
                                        v-for="schedule in schedules" 
                                        :key="schedule.id" 
                                        :value="schedule.id"
                                    >
                                        {{ schedule.name }} - {{ getMonthName(schedule.month) }} {{ schedule.year }}
                                    </option>
                                </select>
                            </div>

                            <div class="row">
                                <div class="col-6">
                                    <label for="targetMonth" class="form-label">Mês Destino</label>
                                    <select 
                                        id="targetMonth"
                                        v-model="copyForm.target_month" 
                                        class="form-select"
                                        required
                                    >
                                        <option 
                                            v-for="month in months" 
                                            :key="month.value" 
                                            :value="month.value"
                                        >
                                            {{ month.label }}
                                        </option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label for="targetYear" class="form-label">Ano Destino</label>
                                    <select 
                                        id="targetYear"
                                        v-model="copyForm.target_year" 
                                        class="form-select"
                                        required
                                    >
                                        <option 
                                            v-for="year in availableYears" 
                                            :key="year" 
                                            :value="year"
                                        >
                                            {{ year }}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3 mt-3">
                                <label for="copyName" class="form-label">Nome da Nova Escala</label>
                                <input
                                    id="copyName"
                                    type="text"
                                    v-model="copyForm.name"
                                    class="form-control"
                                    required
                                    placeholder="Ex: Escala de Janeiro 2024"
                                />
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Opções de Cópia</label>
                                <div class="form-check">
                                    <input
                                        id="copyTechnicians"
                                        type="checkbox"
                                        v-model="copyForm.copy_technicians"
                                        class="form-check-input"
                                    />
                                    <label for="copyTechnicians" class="form-check-label">
                                        Copiar técnicos escalados
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input
                                        id="copyShifts"
                                        type="checkbox"
                                        v-model="copyForm.copy_shifts"
                                        class="form-check-input"
                                        checked
                                        disabled
                                    />
                                    <label for="copyShifts" class="form-check-label">
                                        Copiar turnos (obrigatório)
                                    </label>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button 
                            type="button" 
                            class="btn btn-secondary" 
                            @click="showCopyModal = false"
                        >
                            Cancelar
                        </button>
                        <button 
                            type="button" 
                            class="btn btn-primary" 
                            @click="copySchedule"
                            :disabled="loadingCopy"
                        >
                            <span v-if="loadingCopy" class="spinner-border spinner-border-sm me-2"></span>
                            <vue-feather v-else type="copy" size="16" class="me-2"></vue-feather>
                            Copiar Escala
                        </button>
                    </div>
                </div>
            </div>
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

const { showToast } = useToast();
const router = useRouter();

// Reactive data
const schedules = ref([]);
const loading = ref(false);
const loadingDelete = ref(null);
const loadingCopy = ref(false);
const searchQuery = ref('');
const showCopyModal = ref(false);
const pagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 12,
    total: 0
});

const filters = ref({
    year: new Date().getFullYear(),
    month: '',
    status: ''
});

const copyForm = ref({
    source_id: '',
    target_month: new Date().getMonth() + 1,
    target_year: new Date().getFullYear(),
    name: '',
    copy_technicians: true,
    copy_shifts: true
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
    return Array.from({ length: 5 }, (_, i) => currentYear - 2 + i);
});

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
const getSchedules = async (page = 1) => {
    loading.value = true;
    try {
        const response = await axios.get('/work-schedule', {
            params: {
                page,
                search: searchQuery.value,
                ...filters.value
            }
        });
        schedules.value = response.data.data;
        pagination.value = {
            current_page: response.data.current_page,
            last_page: response.data.last_page,
            per_page: response.data.per_page,
            total: response.data.total
        };
    } catch (error) {
        console.error('Erro ao carregar escalas:', error);
        showToast('Erro ao carregar escalas', 'error');
    } finally {
        loading.value = false;
    }
};

const applyFilters = () => {
    getSchedules(1);
};

const debouncedSearch = debounce(() => {
    getSchedules(1);
}, 300);

const clearSearch = () => {
    searchQuery.value = '';
    getSchedules(1);
};

const changePage = (page) => {
    if (page >= 1 && page <= pagination.value.last_page) {
        getSchedules(page);
    }
};

const confirmDelete = (schedule) => {
    if (confirm(`Tem certeza que deseja excluir a escala "${schedule.name}"?`)) {
        deleteSchedule(schedule);
    }
};

const deleteSchedule = async (schedule) => {
    loadingDelete.value = schedule.id;
    try {
        await axios.delete(`/work-schedule/${schedule.id}`);
        schedules.value = schedules.value.filter(s => s.id !== schedule.id);
        showToast('Escala excluída com sucesso!', 'success');
    } catch (error) {
        console.error('Erro ao excluir escala:', error);
        showToast('Erro ao excluir escala', 'error');
    } finally {
        loadingDelete.value = null;
    }
};

const copySchedule = async () => {
    if (!copyForm.value.source_id || !copyForm.value.name) {
        showToast('Preencha todos os campos obrigatórios', 'warning');
        return;
    }

    loadingCopy.value = true;
    try {
        await axios.post('/work-schedule/copy', copyForm.value);
        showToast('Escala copiada com sucesso!', 'success');
        showCopyModal.value = false;
        getSchedules();
        
        // Reset form
        copyForm.value = {
            source_id: '',
            target_month: new Date().getMonth() + 1,
            target_year: new Date().getFullYear(),
            name: '',
            copy_technicians: true,
            copy_shifts: true
        };
    } catch (error) {
        console.error('Erro ao copiar escala:', error);
        showToast('Erro ao copiar escala', 'error');
    } finally {
        loadingCopy.value = false;
    }
};

const getMonthName = (month) => {
    const monthObj = months.value.find(m => m.value === month);
    return monthObj ? monthObj.label : month;
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

const getProgressBarClass = (percentage) => {
    if (percentage >= 80) return 'bg-success';
    if (percentage >= 50) return 'bg-warning';
    return 'bg-danger';
};

const canEdit = (schedule) => {
    return ['draft', 'published'].includes(schedule.status);
};

const canDelete = (schedule) => {
    return schedule.status === 'draft';
};

const formatDate = (date) => {
    return moment(date).format('DD/MM/YYYY');
};

const formatDateRelative = (date) => {
    return moment(date).fromNow();
};

// Lifecycle
onMounted(() => {
    getSchedules();
});
</script>

<style scoped>
.schedule-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    cursor: pointer;
}

.schedule-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.progress {
    border-radius: 10px;
}

.progress-bar {
    border-radius: 10px;
}

.badge {
    font-size: 0.75rem;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.775rem;
}

.modal.show {
    display: block;
}

.modal-backdrop {
    background-color: rgba(0, 0, 0, 0.5);
}

.pagination .page-link {
    border: 1px solid #dee2e6;
    color: #6c757d;
}

.pagination .page-item.active .page-link {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.card-footer {
    padding: 0.75rem 1rem;
}

.form-check-input:disabled {
    opacity: 0.5;
}
</style>