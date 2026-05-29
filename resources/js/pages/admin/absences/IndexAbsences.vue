<template>
    <div>
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">Faltas de Técnicos</h1>
                <p class="text-muted">Gerencie o registro de faltas, atrasos e saídas antecipadas</p>
            </div>
            <router-link 
                to="/admin/absences/create" 
                class="btn btn-primary"
            >
                <vue-feather type="plus" size="16" class="me-2"></vue-feather>
                Nova Falta
            </router-link>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Filtros</h5>
            </div>
            <div class="card-body">
                <form @submit.prevent="handleSearch">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Buscar</label>
                            <input 
                                v-model="searchQuery" 
                                type="text" 
                                class="form-control"
                                placeholder="Nome do técnico, motivo..."
                            />
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Técnico</label>
                            <select v-model="technicianFilter" class="form-select">
                                <option value="">Todos</option>
                                <option 
                                    v-for="technician in availableTechnicians" 
                                    :key="technician.id" 
                                    :value="technician.id"
                                >
                                    {{ technician.name }}
                                </option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Status</label>
                            <select v-model="statusFilter" class="form-select">
                                <option value="">Todos</option>
                                <option value="pending">Pendente</option>
                                <option value="approved">Aprovado</option>
                                <option value="rejected">Rejeitado</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Tipo</label>
                            <select v-model="typeFilter" class="form-select">
                                <option value="">Todos</option>
                                <option value="absence">Falta</option>
                                <option value="late_arrival">Atraso</option>
                                <option value="early_departure">Saída Antecipada</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Data Inicial</label>
                            <input 
                                v-model="dateFromFilter" 
                                type="date" 
                                class="form-control"
                            />
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Data Final</label>
                            <input 
                                v-model="dateToFilter" 
                                type="date" 
                                class="form-control"
                            />
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">Mês</label>
                            <select v-model="monthFilter" class="form-select">
                                <option value="">-</option>
                                <option value="1">Jan</option>
                                <option value="2">Fev</option>
                                <option value="3">Mar</option>
                                <option value="4">Abr</option>
                                <option value="5">Mai</option>
                                <option value="6">Jun</option>
                                <option value="7">Jul</option>
                                <option value="8">Ago</option>
                                <option value="9">Set</option>
                                <option value="10">Out</option>
                                <option value="11">Nov</option>
                                <option value="12">Dez</option>
                            </select>
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">Ano</label>
                            <select v-model="yearFilter" class="form-select">
                                <option value="">-</option>
                                <option value="2024">2024</option>
                                <option value="2025">2025</option>
                                <option value="2026">2026</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-outline-primary me-2">
                                <vue-feather type="search" size="16" class="me-1"></vue-feather>
                                Buscar
                            </button>
                            <button type="button" @click="clearFilters" class="btn btn-outline-secondary">
                                <vue-feather type="x" size="16" class="me-1"></vue-feather>
                                Limpar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- View Toggle -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="btn-group" role="group" aria-label="Visualização">
                    <button 
                        type="button" 
                        class="btn"
                        :class="currentView === 'table' ? 'btn-primary' : 'btn-outline-primary'"
                        @click="currentView = 'table'"
                    >
                        <vue-feather type="list" size="16" class="me-2"></vue-feather>
                        Lista
                    </button>
                    <button 
                        type="button" 
                        class="btn"
                        :class="currentView === 'calendar' ? 'btn-primary' : 'btn-outline-primary'"
                        @click="currentView = 'calendar'"
                    >
                        <vue-feather type="calendar" size="16" class="me-2"></vue-feather>
                        Calendário
                    </button>
                </div>
            </div>
        </div>

        <!-- Calendar View -->
        <div v-if="currentView === 'calendar'" class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Calendário de Faltas</h5>
                <div class="d-flex align-items-center">
                    <button 
                        @click="previousMonth" 
                        class="btn btn-sm btn-outline-secondary me-2"
                    >
                        <vue-feather type="chevron-left" size="16"></vue-feather>
                    </button>
                    <h6 class="mb-0 mx-3">{{ currentMonthYear }}</h6>
                    <button 
                        @click="nextMonth" 
                        class="btn btn-sm btn-outline-secondary"
                    >
                        <vue-feather type="chevron-right" size="16"></vue-feather>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <!-- Calendar Legend -->
                <div class="row mb-3">
                    <div class="col-12">
                        <small class="text-muted">Legenda:</small>
                        <span class="badge bg-danger ms-2">Falta</span>
                        <span class="badge bg-warning ms-2">Atraso</span>
                        <span class="badge bg-info ms-2">Saída Antecipada</span>
                        <span class="badge bg-light text-dark border ms-2">Pendente</span>
                        <span class="badge bg-success ms-2">Aprovado</span>
                        <span class="badge bg-secondary ms-2">Rejeitado</span>
                    </div>
                </div>

                <!-- Calendar Grid -->
                <div class="calendar-grid">
                    <!-- Day Headers -->
                    <div class="calendar-header">
                        <div v-for="day in dayHeaders" :key="day" class="calendar-day-header">
                            {{ day }}
                        </div>
                    </div>

                    <!-- Calendar Days -->
                    <div class="calendar-body">
                        <div 
                            v-for="day in calendarDays" 
                            :key="`${day.date}-${day.isCurrentMonth}`"
                            class="calendar-day"
                            :class="{
                                'other-month': !day.isCurrentMonth,
                                'today': day.isToday,
                                'has-absences': day.absences.length > 0
                            }"
                        >
                            <div class="day-number">{{ day.dayNumber }}</div>
                            <div class="day-absences">
                                <div 
                                    v-for="absence in day.absences" 
                                    :key="absence.id"
                                    class="absence-item"
                                    :class="{
                                        'absence-type-absence': absence.type === 'absence',
                                        'absence-type-late': absence.type === 'late_arrival',
                                        'absence-type-early': absence.type === 'early_departure',
                                        'absence-pending': absence.status === 'pending',
                                        'absence-approved': absence.status === 'approved',
                                        'absence-rejected': absence.status === 'rejected'
                                    }"
                                    @click="showAbsenceDetails(absence)"
                                    :title="`${absence.technician?.name} - ${getTypeLabel(absence.type)} - ${absence.hours_lost}h`"
                                >
                                    <span class="absence-technician">{{ getTechnicianInitials(absence.technician?.name) }}</span>
                                    <span class="absence-hours">{{ absence.hours_lost }}h</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table View -->
        <div v-if="currentView === 'table'" class="card">
            <div class="card-body">
                <div v-if="loadingDiv" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Carregando...</span>
                    </div>
                </div>

                <div v-else>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Técnico</th>
                                    <th>Departamento</th>
                                    <th>Tipo</th>
                                    <th>Horas Perdidas</th>
                                    <th>Status</th>
                                    <th>Criado por</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="absence in absences.data" :key="absence.id">
                                    <td>{{ formatDate(absence.date) }}</td>
                                    <td>{{ absence.technician?.name }}</td>
                                    <td>{{ absence.technician?.department?.name || '-' }}</td>
                                    <td>
                                        <span 
                                            class="badge"
                                            :class="{
                                                'bg-danger': absence.type === 'absence',
                                                'bg-warning': absence.type === 'late_arrival',
                                                'bg-info': absence.type === 'early_departure'
                                            }"
                                        >
                                            {{ getTypeLabel(absence.type) }}
                                        </span>
                                    </td>
                                    <td>{{ absence.hours_lost }}h</td>
                                    <td>
                                        <span 
                                            class="badge"
                                            :class="{
                                                'bg-warning': absence.status === 'pending',
                                                'bg-success': absence.status === 'approved',
                                                'bg-danger': absence.status === 'rejected'
                                            }"
                                        >
                                            {{ getStatusLabel(absence.status) }}
                                        </span>
                                    </td>
                                    <td>{{ absence.created_by_user?.name || '-' }}</td>
                                    <td>
                                        <div class="dropdown position-relative">
                                            <button 
                                                class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                                type="button"
                                                @click="toggleDropdown(absence.id, $event)"
                                                :aria-expanded="openDropdown === absence.id"
                                            >
                                                <vue-feather type="more-horizontal" size="16"></vue-feather>
                                            </button>
                                            <ul 
                                                class="dropdown-menu"
                                                :class="{ 'show': openDropdown === absence.id }"
                                            >
                                                <li>
                                                    <router-link 
                                                        :to="{ name: 'admin.absences.show', params: { id: absence.id } }"
                                                        class="dropdown-item"
                                                    >
                                                        <vue-feather type="eye" size="16" class="me-2"></vue-feather>
                                                        Visualizar
                                                    </router-link>
                                                </li>
                                                <li v-if="absence.status === 'pending'">
                                                    <router-link 
                                                        :to="{ name: 'admin.absences.edit', params: { id: absence.id } }"
                                                        class="dropdown-item"
                                                    >
                                                        <vue-feather type="edit-2" size="16" class="me-2"></vue-feather>
                                                        Editar
                                                    </router-link>
                                                </li>
                                                <li v-if="absence.status === 'pending'">
                                                    <hr class="dropdown-divider">
                                                </li>
                                                <li v-if="absence.status === 'pending'">
                                                    <button 
                                                        @click="showApproveModal(absence)" 
                                                        class="dropdown-item text-success"
                                                    >
                                                        <vue-feather type="check" size="16" class="me-2"></vue-feather>
                                                        Aprovar
                                                    </button>
                                                </li>
                                                <li v-if="absence.status === 'pending'">
                                                    <button 
                                                        @click="showRejectModal(absence)" 
                                                        class="dropdown-item text-warning"
                                                    >
                                                        <vue-feather type="x" size="16" class="me-2"></vue-feather>
                                                        Rejeitar
                                                    </button>
                                                </li>
                                                <li v-if="absence.status === 'pending'">
                                                    <hr class="dropdown-divider">
                                                </li>
                                                <li v-if="absence.status === 'pending'">
                                                    <button 
                                                        @click="showDeleteModal(absence)" 
                                                        class="dropdown-item text-danger"
                                                    >
                                                        <vue-feather type="trash-2" size="16" class="me-2"></vue-feather>
                                                        Excluir
                                                    </button>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <div v-if="absences.data && absences.data.length === 0" class="text-center py-4">
                            <vue-feather type="calendar" size="48" class="text-muted mb-3"></vue-feather>
                            <p class="text-muted">Nenhuma falta encontrada</p>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <nav v-if="absences.last_page > 1" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <li class="page-item" :class="{ disabled: absences.current_page === 1 }">
                                <button class="page-link" @click="changePage(absences.current_page - 1)">
                                    Anterior
                                </button>
                            </li>
                            
                            <li 
                                v-for="page in visiblePages" 
                                :key="page" 
                                class="page-item" 
                                :class="{ active: page === absences.current_page }"
                            >
                                <button class="page-link" @click="changePage(page)">
                                    {{ page }}
                                </button>
                            </li>
                            
                            <li class="page-item" :class="{ disabled: absences.current_page === absences.last_page }">
                                <button class="page-link" @click="changePage(absences.current_page + 1)">
                                    Próximo
                                </button>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>

        <!-- Absence Details Modal -->
        <div 
            class="modal fade" 
            id="absenceDetailsModal" 
            tabindex="-1" 
            aria-labelledby="absenceDetailsModalLabel" 
            aria-hidden="true"
        >
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="absenceDetailsModalLabel">
                            <vue-feather type="calendar" size="20" class="me-2"></vue-feather>
                            Detalhes da Falta
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" v-if="selectedAbsenceDetails">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Informações Básicas</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <td><strong>Técnico:</strong></td>
                                        <td>{{ selectedAbsenceDetails.technician?.name }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Departamento:</strong></td>
                                        <td>{{ selectedAbsenceDetails.technician?.department?.name || '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Data:</strong></td>
                                        <td>{{ formatDate(selectedAbsenceDetails.date) }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Tipo:</strong></td>
                                        <td>
                                            <span 
                                                class="badge"
                                                :class="{
                                                    'bg-danger': selectedAbsenceDetails.type === 'absence',
                                                    'bg-warning': selectedAbsenceDetails.type === 'late_arrival',
                                                    'bg-info': selectedAbsenceDetails.type === 'early_departure'
                                                }"
                                            >
                                                {{ getTypeLabel(selectedAbsenceDetails.type) }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Horas Perdidas:</strong></td>
                                        <td>{{ selectedAbsenceDetails.hours_lost }}h</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6>Status e Aprovação</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <td><strong>Status:</strong></td>
                                        <td>
                                            <span 
                                                class="badge"
                                                :class="{
                                                    'bg-warning': selectedAbsenceDetails.status === 'pending',
                                                    'bg-success': selectedAbsenceDetails.status === 'approved',
                                                    'bg-danger': selectedAbsenceDetails.status === 'rejected'
                                                }"
                                            >
                                                {{ getStatusLabel(selectedAbsenceDetails.status) }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Criado por:</strong></td>
                                        <td>{{ selectedAbsenceDetails.created_by_user?.name || '-' }}</td>
                                    </tr>
                                    <tr v-if="selectedAbsenceDetails.approved_by_user">
                                        <td><strong>Aprovado por:</strong></td>
                                        <td>{{ selectedAbsenceDetails.approved_by_user?.name }}</td>
                                    </tr>
                                    <tr v-if="selectedAbsenceDetails.approved_at">
                                        <td><strong>Data Aprovação:</strong></td>
                                        <td>{{ formatDate(selectedAbsenceDetails.approved_at) }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        
                        <div v-if="selectedAbsenceDetails.reason" class="mt-3">
                            <h6>Motivo</h6>
                            <p class="text-muted">{{ selectedAbsenceDetails.reason }}</p>
                        </div>
                        
                        <div v-if="selectedAbsenceDetails.observations" class="mt-3">
                            <h6>Observações</h6>
                            <p class="text-muted">{{ selectedAbsenceDetails.observations }}</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Fechar
                        </button>
                        <router-link 
                            v-if="selectedAbsenceDetails"
                            :to="{ name: 'admin.absences.show', params: { id: selectedAbsenceDetails.id } }"
                            class="btn btn-primary"
                        >
                            <vue-feather type="eye" size="16" class="me-2"></vue-feather>
                            Ver Detalhes Completos
                        </router-link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Approve Modal -->
        <div 
            class="modal fade" 
            id="approveModal" 
            tabindex="-1" 
            aria-labelledby="approveModalLabel" 
            aria-hidden="true"
        >
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="approveModalLabel">
                            <vue-feather type="check-circle" size="20" class="me-2 text-success"></vue-feather>
                            Aprovar Falta
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form @submit.prevent="approveAbsence">
                        <div class="modal-body">
                            <div class="alert alert-success" role="alert">
                                <vue-feather type="info" size="16" class="me-2"></vue-feather>
                                <strong>Confirmação de Aprovação</strong>
                            </div>
                            <p>Você tem certeza que deseja aprovar esta falta?</p>
                            <div class="bg-light p-3 rounded" v-if="selectedAbsence">
                                <p class="mb-1"><strong>Técnico:</strong> {{ selectedAbsence.technician?.name }}</p>
                                <p class="mb-1"><strong>Data:</strong> {{ formatDate(selectedAbsence.date) }}</p>
                                <p class="mb-1"><strong>Tipo:</strong> {{ getTypeLabel(selectedAbsence.type) }}</p>
                                <p class="mb-0"><strong>Horas Perdidas:</strong> {{ selectedAbsence.hours_lost }}h</p>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                Cancelar
                            </button>
                            <button type="submit" class="btn btn-success" :disabled="loading">
                                <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                                <vue-feather v-else type="check" size="16" class="me-2"></vue-feather>
                                Aprovar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Reject Modal -->
        <div 
            class="modal fade" 
            id="rejectModal" 
            tabindex="-1" 
            aria-labelledby="rejectModalLabel" 
            aria-hidden="true"
        >
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="rejectModalLabel">Rejeitar Falta</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form @submit.prevent="rejectAbsence">
                        <div class="modal-body">
                            <p>Você tem certeza que deseja rejeitar esta falta?</p>
                            <p><strong>Técnico:</strong> {{ selectedAbsence?.technician?.name }}</p>
                            <p><strong>Data:</strong> {{ formatDate(selectedAbsence?.date) }}</p>
                            
                            <div class="mb-3">
                                <label for="reject-observations" class="form-label">Motivo da Rejeição *</label>
                                <textarea 
                                    id="reject-observations"
                                    v-model="rejectObservations" 
                                    class="form-control" 
                                    rows="3" 
                                    required
                                    placeholder="Descreva o motivo da rejeição..."
                                ></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                Cancelar
                            </button>
                            <button type="submit" class="btn btn-warning" :disabled="loading">
                                <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                                Rejeitar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Delete Modal -->
        <div 
            class="modal fade" 
            id="deleteModal" 
            tabindex="-1" 
            aria-labelledby="deleteModalLabel" 
            aria-hidden="true"
        >
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteModalLabel">
                            <vue-feather type="alert-triangle" size="20" class="me-2 text-danger"></vue-feather>
                            Excluir Falta
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form @submit.prevent="confirmDelete">
                        <div class="modal-body">
                            <div class="alert alert-danger" role="alert">
                                <vue-feather type="alert-triangle" size="16" class="me-2"></vue-feather>
                                <strong>Atenção! Esta ação não pode ser desfeita.</strong>
                            </div>
                            <p>Você tem certeza que deseja excluir esta falta?</p>
                            <div class="bg-light p-3 rounded" v-if="selectedAbsence">
                                <p class="mb-1"><strong>Técnico:</strong> {{ selectedAbsence.technician?.name }}</p>
                                <p class="mb-1"><strong>Data:</strong> {{ formatDate(selectedAbsence.date) }}</p>
                                <p class="mb-1"><strong>Tipo:</strong> {{ getTypeLabel(selectedAbsence.type) }}</p>
                                <p class="mb-0"><strong>Horas Perdidas:</strong> {{ selectedAbsence.hours_lost }}h</p>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                Cancelar
                            </button>
                            <button type="submit" class="btn btn-danger" :disabled="loading">
                                <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                                <vue-feather v-else type="trash-2" size="16" class="me-2"></vue-feather>
                                Excluir
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed, nextTick, watch } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import VueFeather from 'vue-feather';
import {useToastr} from '../../../toastr';

const toastr = useToastr();
const router = useRouter();

// Reactive data
const absences = ref({ data: [], current_page: 1, last_page: 1 });
const availableTechnicians = ref([]);
const loadingDiv = ref(false);
const loading = ref(false);

// View management
const currentView = ref('table');

// Calendar data
const currentDate = ref(new Date());
const calendarAbsences = ref([]);
const selectedAbsenceDetails = ref(null);

// Calendar computed
const currentMonthYear = computed(() => {
    const months = [
        'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
        'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'
    ];
    return `${months[currentDate.value.getMonth()]} ${currentDate.value.getFullYear()}`;
});

const dayHeaders = computed(() => ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb']);

const calendarDays = computed(() => {
    const year = currentDate.value.getFullYear();
    const month = currentDate.value.getMonth();
    
    // First day of current month
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    
    // Start from previous month's sunday
    const startDate = new Date(firstDay);
    startDate.setDate(startDate.getDate() - firstDay.getDay());
    
    const days = [];
    const today = new Date();
    
    // Generate 42 days (6 weeks)
    for (let i = 0; i < 42; i++) {
        const currentDay = new Date(startDate);
        currentDay.setDate(startDate.getDate() + i);
        
        const dayAbsences = calendarAbsences.value.filter(absence => {
            const absenceDate = new Date(absence.date);
            return absenceDate.toDateString() === currentDay.toDateString();
        });
        
        days.push({
            date: currentDay.toISOString().split('T')[0],
            dayNumber: currentDay.getDate(),
            isCurrentMonth: currentDay.getMonth() === month,
            isToday: currentDay.toDateString() === today.toDateString(),
            absences: dayAbsences
        });
    }
    
    return days;
});

// Filtros
const searchQuery = ref('');
const technicianFilter = ref('');
const statusFilter = ref('');
const typeFilter = ref('');
const dateFromFilter = ref('');
const dateToFilter = ref('');
const monthFilter = ref('');
const yearFilter = ref('');

// Dropdown management
const openDropdown = ref(null);

// Modal data
const selectedAbsence = ref(null);
const rejectObservations = ref('');

// Computed
const visiblePages = computed(() => {
    const current = absences.value.current_page;
    const last = absences.value.last_page;
    const pages = [];
    
    for (let i = Math.max(1, current - 2); i <= Math.min(last, current + 2); i++) {
        pages.push(i);
    }
    
    return pages;
});

// Methods
const getData = async (page = 1) => {
    loadingDiv.value = true;
    try {
        const params = {
            page,
            query: searchQuery.value,
            technician_id: technicianFilter.value,
            status: statusFilter.value,
            type: typeFilter.value,
            date_from: dateFromFilter.value,
            date_to: dateToFilter.value,
            month: monthFilter.value,
            year: yearFilter.value
        };

        const response = await axios.get('/absences', { params });
        absences.value = response.data;
        
        await nextTick();
        initializeDropdowns();
        
        // Load calendar data if in calendar view
        if (currentView.value === 'calendar') {
            await getCalendarData();
        }
    } catch (error) {
        toastr.error('Erro ao carregar faltas');
        console.error(error);
    } finally {
        loadingDiv.value = false;
    }
};

const getCalendarData = async () => {
    try {
        const year = currentDate.value.getFullYear();
        const month = currentDate.value.getMonth() + 1;
        
        const params = {
            month,
            year,
            per_page: 1000 // Get all absences for the month
        };

        const response = await axios.get('/absences', { params });
        calendarAbsences.value = response.data.data || [];
    } catch (error) {
        console.error('Erro ao carregar dados do calendário:', error);
        toastr.error('Erro ao carregar dados do calendário');
    }
};

const previousMonth = () => {
    currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() - 1, 1);
    if (currentView.value === 'calendar') {
        getCalendarData();
    }
};

const nextMonth = () => {
    currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() + 1, 1);
    if (currentView.value === 'calendar') {
        getCalendarData();
    }
};

const showAbsenceDetails = (absence) => {
    selectedAbsenceDetails.value = absence;
    const modal = new bootstrap.Modal(document.getElementById('absenceDetailsModal'));
    modal.show();
};

const getTechnicianInitials = (name) => {
    if (!name) return '';
    return name.split(' ')
              .map(word => word.charAt(0))
              .join('')
              .toUpperCase()
              .substring(0, 2);
};

const getTechnicians = async () => {
    try {
        const response = await axios.get('/absences/technicians');
        availableTechnicians.value = response.data.technicians;
    } catch (error) {
        console.error('Erro ao buscar técnicos:', error);
        toastr.error('Erro ao carregar técnicos');
    }
};

const handleSearch = () => {
    getData(1);
};

const clearFilters = () => {
    searchQuery.value = '';
    technicianFilter.value = '';
    statusFilter.value = '';
    typeFilter.value = '';
    dateFromFilter.value = '';
    dateToFilter.value = '';
    monthFilter.value = '';
    yearFilter.value = '';
    getData(1);
};

const changePage = (page) => {
    if (page >= 1 && page <= absences.value.last_page) {
        getData(page);
    }
};

// Dropdown functionality
const toggleDropdown = (absenceId, event) => {
    event.preventDefault();
    event.stopPropagation();
    
    if (openDropdown.value === absenceId) {
        openDropdown.value = null;
    } else {
        openDropdown.value = absenceId;
    }
};

const closeDropdown = () => {
    openDropdown.value = null;
};

const handleOutsideClick = (event) => {
    if (!event.target.closest('.dropdown')) {
        closeDropdown();
    }
};

const initializeDropdowns = () => {
    // Method for future dropdown initialization if needed
};

// Actions
const approveAbsence = async () => {
    if (!selectedAbsence.value) return;
    
    loading.value = true;
    try {
        await axios.post(`/absences/${selectedAbsence.value.id}/approve`);
        toastr.success('Falta aprovada com sucesso!');
        getData(absences.value.current_page);
        
        const modal = bootstrap.Modal.getInstance(document.getElementById('approveModal'));
        modal.hide();
    } catch (error) {
        console.error(error);
        if (error.response?.data?.error) {
            toastr.error(error.response.data.error);
        } else {
            toastr.error('Erro ao aprovar falta');
        }
    } finally {
        loading.value = false;
    }
};

const showApproveModal = (absence) => {
    selectedAbsence.value = absence;
    closeDropdown();
    const modal = new bootstrap.Modal(document.getElementById('approveModal'));
    modal.show();
};

const showRejectModal = (absence) => {
    selectedAbsence.value = absence;
    rejectObservations.value = '';
    const modal = new bootstrap.Modal(document.getElementById('rejectModal'));
    modal.show();
};

const rejectAbsence = async () => {
    if (!selectedAbsence.value) return;
    
    loading.value = true;
    try {
        await axios.post(`/absences/${selectedAbsence.value.id}/reject`, {
            observations: rejectObservations.value
        });
        
        toastr.success('Falta rejeitada com sucesso!');
        getData(absences.value.current_page);
        
        const modal = bootstrap.Modal.getInstance(document.getElementById('rejectModal'));
        modal.hide();
    } catch (error) {
        console.error(error);
        if (error.response?.data?.errors) {
            Object.values(error.response.data.errors).forEach(errorArray => {
                errorArray.forEach(message => toastr.error(message));
            });
        } else if (error.response?.data?.error) {
            toastr.error(error.response.data.error);
        } else {
            toastr.error('Erro ao rejeitar falta');
        }
    } finally {
        loading.value = false;
    }
};

const confirmDelete = async () => {
    if (!selectedAbsence.value) return;
    
    loading.value = true;
    try {
        await axios.delete(`/admin/absences/${selectedAbsence.value.id}`);
        toastr.success('Falta excluída com sucesso!');
        getData(absences.value.current_page);
        
        const modal = bootstrap.Modal.getInstance(document.getElementById('deleteModal'));
        modal.hide();
    } catch (error) {
        console.error(error);
        if (error.response?.data?.error) {
            toastr.error(error.response.data.error);
        } else {
            toastr.error('Erro ao excluir falta');
        }
    } finally {
        loading.value = false;
    }
};

const showDeleteModal = (absence) => {
    selectedAbsence.value = absence;
    closeDropdown();
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
};

// Utility methods
const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('pt-BR');
};

const getTypeLabel = (type) => {
    const labels = {
        'absence': 'Falta',
        'late_arrival': 'Atraso',
        'early_departure': 'Saída Antecipada'
    };
    return labels[type] || type;
};

const getStatusLabel = (status) => {
    const labels = {
        'pending': 'Pendente',
        'approved': 'Aprovado',
        'rejected': 'Rejeitado'
    };
    return labels[status] || status;
};

// Lifecycle
onMounted(() => {
    getData();
    getTechnicians();
    document.addEventListener('click', handleOutsideClick);
});

onUnmounted(() => {
    document.removeEventListener('click', handleOutsideClick);
});

// Watch for view changes
watch(currentView, (newView) => {
    if (newView === 'calendar') {
        getCalendarData();
    }
});
</script>

<style scoped>
.table th, .table td {
    vertical-align: middle;
}

.dropdown-menu {
    min-width: 200px;
}

.badge {
    font-size: 0.75rem;
}

/* Calendar Styles */
.calendar-grid {
    display: flex;
    flex-direction: column;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
}

.calendar-header {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.calendar-day-header {
    padding: 0.75rem 0.5rem;
    text-align: center;
    font-weight: 600;
    font-size: 0.875rem;
    color: #495057;
    border-right: 1px solid #dee2e6;
}

.calendar-day-header:last-child {
    border-right: none;
}

.calendar-body {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
}

.calendar-day {
    min-height: 120px;
    padding: 0.5rem;
    border-right: 1px solid #dee2e6;
    border-bottom: 1px solid #dee2e6;
    background-color: #fff;
    transition: background-color 0.2s;
}

.calendar-day:nth-child(7n) {
    border-right: none;
}

.calendar-day:hover {
    background-color: #f8f9fa;
}

.calendar-day.other-month {
    background-color: #f8f9fa;
    color: #6c757d;
}

.calendar-day.other-month .day-number {
    color: #adb5bd;
}

.calendar-day.today {
    background-color: #e3f2fd;
}

.calendar-day.today .day-number {
    background-color: #2196f3;
    color: white;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
}

.calendar-day.has-absences {
    background-color: #fff3e0;
}

.day-number {
    font-size: 0.875rem;
    font-weight: 500;
    margin-bottom: 0.25rem;
}

.day-absences {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.absence-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 2px 4px;
    border-radius: 3px;
    font-size: 0.65rem;
    cursor: pointer;
    transition: all 0.2s;
    border: 1px solid transparent;
}

.absence-item:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

/* Absence Type Colors */
.absence-type-absence {
    background-color: #dc3545;
    color: white;
}

.absence-type-late {
    background-color: #ffc107;
    color: #212529;
}

.absence-type-early {
    background-color: #17a2b8;
    color: white;
}

/* Absence Status Modifiers */
.absence-pending {
    border: 1px solid #6c757d;
    opacity: 0.7;
}

.absence-approved {
    border: 1px solid #28a745;
}

.absence-rejected {
    background-color: #6c757d !important;
    color: white !important;
    text-decoration: line-through;
}

.absence-technician {
    font-weight: 600;
}

.absence-hours {
    font-size: 0.6rem;
    opacity: 0.9;
}

/* Responsive */
@media (max-width: 768px) {
    .calendar-day {
        min-height: 80px;
        padding: 0.25rem;
    }
    
    .calendar-day-header {
        padding: 0.5rem 0.25rem;
        font-size: 0.75rem;
    }
    
    .absence-item {
        font-size: 0.55rem;
        padding: 1px 2px;
    }
    
    .absence-technician {
        display: none;
    }
}
</style>