<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { useRouter } from 'vue-router';
import { Form, Field, ErrorMessage, useForm, useField } from 'vee-validate';
import * as yup from 'yup';
import axios from 'axios';
import VueFeather from 'vue-feather';
import {useToastr} from '../../../toastr';

const toastr = useToastr();

// Form validation schema
const schema = yup.object({
    month: yup.number().required('Mês é obrigatório').min(1).max(12),
    year: yup.number().required('Ano é obrigatório').min(2020).max(2050),
    due_date: yup.date().required('Data de vencimento é obrigatória').min(new Date(), 'Data deve ser futura'),
    notes: yup.string().max(1000, 'Máximo 1000 caracteres')
});

// Reactive data
const router = useRouter();
const loadingDiv = ref(true);
const loadingSubmit = ref(false);
const equipments = ref([]);
const selectedEquipments = ref([]);
const searchEquipment = ref('');

// Form composable
const { errors, handleSubmit, setErrors, resetForm, values } = useForm({
    validationSchema: schema,
    initialValues: {
        month: '',
        year: '',
        due_date: new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
        notes: ''
    }
});

// Definir campos individuais usando useField
const { value: month, errorMessage: monthError } = useField('month');
const { value: year, errorMessage: yearError } = useField('year');
const { value: due_date, errorMessage: dueDateError } = useField('due_date');
const { value: notes, errorMessage: notesError } = useField('notes');

// Watch para monitorar mudanças nos valores do formulário
watch([month, year], ([newMonth, newYear]) => {
    console.log('Mês selecionado:', newMonth);
    console.log('Ano selecionado:', newYear);
});

// Computed properties
const filteredEquipments = computed(() => {
    if (!searchEquipment.value) return equipments.value;
    return equipments.value.filter(equipment => 
        equipment.name.toLowerCase().includes(searchEquipment.value.toLowerCase()) ||
        equipment.destination?.name?.toLowerCase().includes(searchEquipment.value.toLowerCase())
    );
});

const selectedEquipmentsData = computed(() => {
    return equipments.value.filter(equipment => 
        selectedEquipments.value.includes(equipment.id)
    );
});

const totalEstimatedAmount = computed(() => {
    return selectedEquipmentsData.value.reduce((total, equipment) => {
        return total + equipment.active_fees.reduce((equipTotal, fee) => {
            return equipTotal + parseFloat(fee.amount);
        }, 0);
    }, 0);
});

const totalItemsCount = computed(() => {
    return selectedEquipmentsData.value.reduce((total, equipment) => {
        return total + equipment.active_fees.length;
    }, 0);
});

// Utility functions
const formatCurrency = (amount) => {
    return new Intl.NumberFormat('pt-MZ', {
        style: 'currency',
        currency: 'MZN',
        minimumFractionDigits: 2
    }).format(amount);
};

const getMonthName = (month) => {
    const months = [
        'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
        'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'
    ];
    return months[month - 1] || '';
};

// Methods
const getData = async () => {
    try {
        loadingDiv.value = true;
        const response = await axios.get('/fee-invoices/equipments-with-fees');
        equipments.value = response.data.equipments;
    } catch (error) {
        toastr.error('Erro ao carregar equipamentos');
        console.error(error);
        router.push('/admin/fee-invoices');
    } finally {
        loadingDiv.value = false;
    }
};

const toggleEquipment = (equipmentId) => {
    const index = selectedEquipments.value.indexOf(equipmentId);
    if (index > -1) {
        selectedEquipments.value.splice(index, 1);
    } else {
        selectedEquipments.value.push(equipmentId);
    }
};

const toggleAllEquipments = () => {
    if (selectedEquipments.value.length === filteredEquipments.value.length) {
        // Desmarcar todos os visíveis
        filteredEquipments.value.forEach(equipment => {
            const index = selectedEquipments.value.indexOf(equipment.id);
            if (index > -1) {
                selectedEquipments.value.splice(index, 1);
            }
        });
    } else {
        // Marcar todos os visíveis
        filteredEquipments.value.forEach(equipment => {
            if (!selectedEquipments.value.includes(equipment.id)) {
                selectedEquipments.value.push(equipment.id);
            }
        });
    }
};

const isEquipmentSelected = (equipmentId) => {
    return selectedEquipments.value.includes(equipmentId);
};

const getEquipmentTotalFees = (equipment) => {
    return equipment.active_fees.reduce((total, fee) => total + parseFloat(fee.amount), 0);
};

const onSubmit = handleSubmit(async (values) => {
    console.log('=== DEBUGGING SUBMIT ===');
    console.log('Valores dos campos individuais:');
    console.log('- month:', month.value);
    console.log('- year:', year.value);
    console.log('- due_date:', due_date.value);
    console.log('- notes:', notes.value);
    console.log('Valores recebidos do handleSubmit:', values);
    
    // Construir valores manualmente se necessário
    const formData = {
        month: month.value || values.month,
        year: year.value || values.year,
        due_date: due_date.value || values.due_date,
        notes: notes.value || values.notes
    };
    
    console.log('Dados finais para envio:', formData);
    
    if (selectedEquipments.value.length === 0) {
        toastr.error('Selecione pelo menos um equipamento para processar');
        return;
    }

    // Validação adicional dos campos obrigatórios
    if (!formData.month || !formData.year) {
        toastr.error('Mês e ano são obrigatórios');
        console.log('Validação falhou - mês:', formData.month, 'ano:', formData.year);
        return;
    }

    loadingSubmit.value = true;

    const payload = {
        ...formData,
        selected_equipments: selectedEquipments.value
    };

    console.log('Payload final enviado:', payload);

    try {
        const response = await axios.post('/fee-invoices', payload);
        toastr.success('Fatura criada com sucesso!');
        router.push('/admin/fee-invoices');
    } catch (error) {
        loadingSubmit.value = false;
        toastr.error('Erro ao criar fatura');
        console.error('Erro completo:', error);
        if (error.response?.data?.errors) {
            setErrors(error.response.data.errors);
        }
        if (error.response?.data?.message) {
            toastr.error(error.response.data.message);
        }
    }
});

// Lifecycle
onMounted(() => {
    getData();
});
</script>

<template>
    <div v-if="!loadingDiv">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">
                <vue-feather type="plus-circle" class="align-middle me-2"></vue-feather>
                Nova Fatura de Taxas
            </h1>
            <router-link to="/admin/fee-invoices" class="btn btn-secondary">
                <vue-feather type="arrow-left" size="16" class="me-1"></vue-feather>
                Voltar
            </router-link>
        </div>

        <Form @submit="onSubmit">
            <div class="row">
                <!-- Formulário Principal -->
                <div class="col-md-4">
                    <div class="card sticky-top" style="top: 20px;">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <vue-feather type="settings" size="20" class="me-2"></vue-feather>
                                Configurações da Fatura
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6">
                                    <div class="mb-3">
                                        <label class="form-label">Mês *</label>
                                        <select 
                                            v-model="month" 
                                            class="form-select" 
                                            :class="{ 'is-invalid': errors.month || monthError }"
                                        >
                                            <option value="">Selecione</option>
                                            <option v-for="n in 12" :key="n" :value="n">{{ getMonthName(n) }}</option>
                                        </select>
                                        <ErrorMessage name="month" class="invalid-feedback" />
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="mb-3">
                                        <label class="form-label">Ano *</label>
                                        <select 
                                            v-model="year" 
                                            class="form-select" 
                                            :class="{ 'is-invalid': errors.year || yearError }"
                                        >
                                            <option value="">Selecione</option>
                                            <option :value="2024">2024</option>
                                            <option :value="2025">2025</option>
                                            <option :value="2026">2026</option>
                                            <option :value="2027">2027</option>
                                        </select>
                                        <ErrorMessage name="year" class="invalid-feedback" />
                                    </div>
                                </div>
                            </div>

                            <!-- Debug: Mostrar valores atuais -->
                            <div class="alert alert-info mb-3">
                                <strong>Debug - Valores atuais:</strong><br>
                                Mês: {{ month || 'não selecionado' }}<br>
                                Ano: {{ year || 'não selecionado' }}<br>
                                Data vencimento: {{ due_date }}<br>
                                Notas: {{ notes || 'vazio' }}
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Data de Vencimento *</label>
                                <input 
                                    type="date" 
                                    v-model="due_date"
                                    class="form-control" 
                                    :class="{ 'is-invalid': errors.due_date || dueDateError }"
                                />
                                <ErrorMessage name="due_date" class="invalid-feedback" />
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Observações</label>
                                <textarea 
                                    v-model="notes"
                                    class="form-control" 
                                    :class="{ 'is-invalid': errors.notes || notesError }"
                                    rows="3"
                                    placeholder="Observações sobre a fatura..."
                                ></textarea>
                                <ErrorMessage name="notes" class="invalid-feedback" />
                            </div>

                            <!-- Resumo -->
                            <div class="border-top pt-3 mt-3">
                                <h6 class="text-muted mb-3">Resumo da Seleção</h6>
                                
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Equipamentos selecionados:</span>
                                    <span class="fw-bold">{{ selectedEquipments.length }}</span>
                                </div>
                                
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Total de itens:</span>
                                    <span class="fw-bold">{{ totalItemsCount }}</span>
                                </div>
                                
                                <div class="d-flex justify-content-between mb-3">
                                    <span>Valor estimado:</span>
                                    <span class="fw-bold text-success">{{ formatCurrency(totalEstimatedAmount) }}</span>
                                </div>

                                <button 
                                    type="submit" 
                                    class="btn btn-primary w-100"
                                    :disabled="loadingSubmit || selectedEquipments.length === 0"
                                >
                                    <div v-if="loadingSubmit" class="spinner-border spinner-border-sm me-2"></div>
                                    <vue-feather v-else type="save" size="16" class="me-1"></vue-feather>
                                    {{ loadingSubmit ? 'Criando...' : 'Criar Fatura' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Seleção de Equipamentos -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">
                                    <vue-feather type="truck" size="20" class="me-2"></vue-feather>
                                    Equipamentos com Taxas ({{ equipments.length }})
                                </h5>
                                <button 
                                    type="button" 
                                    class="btn btn-sm btn-outline-primary"
                                    @click="toggleAllEquipments"
                                >
                                    {{ selectedEquipments.length === filteredEquipments.length ? 'Desmarcar Todos' : 'Marcar Todos' }}
                                </button>
                            </div>
                            
                            <!-- Busca -->
                            <div class="mt-3">
                                <div class="input-group">
                                    <input 
                                        type="text" 
                                        v-model="searchEquipment"
                                        class="form-control" 
                                        placeholder="Buscar equipamento ou cliente..."
                                    />
                                    <span class="input-group-text">
                                        <vue-feather type="search" size="16"></vue-feather>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="card-body p-0" style="max-height: 600px; overflow-y: auto;">
                            <div v-if="filteredEquipments.length === 0" class="text-center p-5">
                                <vue-feather type="inbox" size="48" class="text-muted mb-3"></vue-feather>
                                <h6 class="text-muted">Nenhum equipamento encontrado</h6>
                                <p class="text-muted mb-0">Verifique se existem equipamentos com taxas cadastradas.</p>
                            </div>

                            <div v-else class="list-group list-group-flush">
                                <div 
                                    v-for="equipment in filteredEquipments" 
                                    :key="equipment.id"
                                    class="list-group-item"
                                    :class="{ 'list-group-item-success': isEquipmentSelected(equipment.id) }"
                                >
                                    <div class="d-flex align-items-start">
                                        <div class="form-check mt-1 me-3">
                                            <input 
                                                :id="`equipment-${equipment.id}`"
                                                type="checkbox" 
                                                class="form-check-input"
                                                :checked="isEquipmentSelected(equipment.id)"
                                                @change="toggleEquipment(equipment.id)"
                                            />
                                        </div>

                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h6 class="mb-1">{{ equipment.name }}</h6>
                                                    <p class="mb-1 text-muted">
                                                        <vue-feather type="map-pin" size="14" class="me-1"></vue-feather>
                                                        {{ equipment.destination?.name || 'N/A' }}
                                                    </p>
                                                    <p class="mb-2 text-muted">
                                                        <vue-feather type="truck" size="14" class="me-1"></vue-feather>
                                                        {{ equipment.type_equipment?.name || 'N/A' }}
                                                    </p>
                                                </div>
                                                <div class="text-end">
                                                    <span class="badge bg-primary">
                                                        {{ equipment.active_fees.length }} taxa{{ equipment.active_fees.length !== 1 ? 's' : '' }}
                                                    </span>
                                                    <div class="fw-bold text-success mt-1">
                                                        {{ formatCurrency(getEquipmentTotalFees(equipment)) }}
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Lista de Taxas -->
                                            <div class="mt-2">
                                                <div class="row g-2">
                                                    <div 
                                                        v-for="fee in equipment.active_fees" 
                                                        :key="`${equipment.id}-${fee.id}`"
                                                        class="col-md-6"
                                                    >
                                                        <div class="border rounded p-2 bg-light">
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                <span class="small fw-medium">{{ fee.name }}</span>
                                                                <span class="small text-success">{{ formatCurrency(fee.amount) }}</span>
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
                </div>
            </div>
        </Form>
    </div>

    <div v-else class="text-center p-5">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Carregando...</span>
        </div>
        <p class="mt-2">Carregando equipamentos...</p>
    </div>
</template>

<style scoped>
.list-group-item {
    transition: all 0.2s ease;
}

.list-group-item:hover {
    background-color: #f8f9fa;
}

.list-group-item-success {
    background-color: #d1e7dd !important;
    border-color: #badbcc !important;
}

.sticky-top {
    z-index: 1020;
}

.card-body {
    scrollbar-width: thin;
}

.card-body::-webkit-scrollbar {
    width: 8px;
}

.card-body::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.card-body::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 4px;
}

.card-body::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

.form-check-input:checked {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.badge {
    font-size: 0.75em;
}

.text-success {
    color: #198754 !important;
}
</style>