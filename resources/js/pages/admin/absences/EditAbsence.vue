<template>
    <div>
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">Editar Falta</h1>
                <p class="text-muted">Edite informações do registro de falta</p>
            </div>
            <router-link 
                :to="{ name: 'admin.absences.show', params: { id: route.params.id } }" 
                class="btn btn-outline-secondary"
            >
                <vue-feather type="arrow-left" size="16" class="me-2"></vue-feather>
                Voltar
            </router-link>
        </div>

        <!-- Loading -->
        <div v-if="loadingDiv" class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Carregando...</span>
            </div>
        </div>

        <!-- Form -->
        <div v-else class="card">
            <div class="card-body">
                <Form 
                    @submit="onSubmit" 
                    :validation-schema="schema" 
                    v-slot="{ errors }"
                    ref="form"
                >
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="technician_id" class="form-label">Técnico *</label>
                                <Field
                                    name="technician_id"
                                    as="select"
                                    class="form-select"
                                    :class="{ 'is-invalid': errors.technician_id }"
                                >
                                    <option value="">Selecione um técnico</option>
                                    <option 
                                        v-for="technician in technicians" 
                                        :key="technician.id" 
                                        :value="technician.id"
                                    >
                                        {{ technician.name }} - {{ technician.department?.name }}
                                    </option>
                                </Field>
                                <div v-if="errors.technician_id" class="invalid-feedback">
                                    {{ errors.technician_id }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="date" class="form-label">Data *</label>
                                <Field
                                    name="date"
                                    type="date"
                                    class="form-control"
                                    :class="{ 'is-invalid': errors.date }"
                                    :max="today"
                                />
                                <div v-if="errors.date" class="invalid-feedback">
                                    {{ errors.date }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="type" class="form-label">Tipo de Ocorrência *</label>
                                <Field
                                    name="type"
                                    as="select"
                                    class="form-select"
                                    :class="{ 'is-invalid': errors.type }"
                                    @change="handleTypeChange"
                                >
                                    <option value="">Selecione o tipo</option>
                                    <option value="absence">Falta (dia completo)</option>
                                    <option value="late_arrival">Atraso</option>
                                    <option value="early_departure">Saída Antecipada</option>
                                </Field>
                                <div v-if="errors.type" class="invalid-feedback">
                                    {{ errors.type }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="hours_lost" class="form-label">
                                    Horas Perdidas *
                                    <small class="text-muted">({{ getHoursHint() }})</small>
                                </label>
                                <Field
                                    name="hours_lost"
                                    type="number"
                                    step="0.5"
                                    min="0"
                                    max="24"
                                    class="form-control"
                                    :class="{ 'is-invalid': errors.hours_lost }"
                                    placeholder="Ex: 8.0"
                                />
                                <div v-if="errors.hours_lost" class="invalid-feedback">
                                    {{ errors.hours_lost }}
                                </div>
                                <div class="form-text">
                                    {{ getHoursDescription() }}
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="mb-3">
                                <label for="reason" class="form-label">Motivo da Falta *</label>
                                <Field
                                    name="reason"
                                    as="textarea"
                                    rows="4"
                                    class="form-control"
                                    :class="{ 'is-invalid': errors.reason }"
                                    placeholder="Descreva o motivo da falta, atraso ou saída antecipada..."
                                />
                                <div v-if="errors.reason" class="invalid-feedback">
                                    {{ errors.reason }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="d-flex justify-content-end gap-2">
                        <router-link 
                            :to="{ name: 'admin.absences.show', params: { id: route.params.id } }" 
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
                            Salvar Alterações
                        </button>
                    </div>
                </Form>
            </div>
        </div>

        <!-- Current Status Alert -->
        <div v-if="absence && absence.status !== 'pending'" class="alert alert-warning mt-4">
            <vue-feather type="alert-triangle" size="16" class="me-2"></vue-feather>
            <strong>Atenção:</strong> 
            Esta falta está com status "{{ getStatusLabel(absence.status) }}" e só pode ser editada se estiver pendente.
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed, nextTick } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { Form, Field } from 'vee-validate';
import * as yup from 'yup';
import axios from 'axios';
import VueFeather from 'vue-feather';
import {useToastr} from '../../../toastr';

const toastr = useToastr();
const route = useRoute();
const router = useRouter();

// Reactive data
const absence = ref(null);
const technicians = ref([]);
const loadingDiv = ref(false);
const loading = ref(false);
const selectedType = ref('');
const form = ref(null);

// Computed
const today = computed(() => {
    return new Date().toISOString().split('T')[0];
});

// Validation schema
const schema = yup.object({
    technician_id: yup.number().required('Técnico é obrigatório').positive('Selecione um técnico válido'),
    date: yup.date().required('Data é obrigatória').max(new Date(), 'Data não pode ser futura'),
    type: yup.string().required('Tipo é obrigatório').oneOf(['absence', 'late_arrival', 'early_departure'], 'Tipo inválido'),
    hours_lost: yup.number().required('Horas perdidas é obrigatório').min(0, 'Mínimo 0 horas').max(24, 'Máximo 24 horas'),
    reason: yup.string().required('Motivo é obrigatório').min(10, 'Motivo deve ter pelo menos 10 caracteres').max(1000, 'Motivo muito longo')
});

// Methods
const getData = async () => {
    loadingDiv.value = true;
    try {
        const response = await axios.get(`/absences/${route.params.id}/edit`);
        absence.value = response.data.absence;
        technicians.value = response.data.technicians;
        
        // Check if can be edited
        if (absence.value.status !== 'pending') {
            toastr.warning('Esta falta não pode ser editada pois já foi aprovada ou rejeitada.');
            router.push({ name: 'admin.absences.show', params: { id: route.params.id } });
            return;
        }
        
        // Fill form
        await nextTick();
        if (form.value) {
            selectedType.value = absence.value.type;
            form.value.setValues({
                technician_id: absence.value.technician_id,
                date: absence.value.date,
                type: absence.value.type,
                hours_lost: parseFloat(absence.value.hours_lost),
                reason: absence.value.reason
            });
        }
    } catch (error) {
        console.error('Erro ao buscar dados:', error);
        if (error.response?.data?.error) {
            toastr.error(error.response.data.error);
        } else {
            toastr.error('Erro ao carregar dados da falta');
        }
        router.push('/admin/absences');
    } finally {
        loadingDiv.value = false;
    }
};

const handleTypeChange = (event) => {
    selectedType.value = event.target.value;
};

const getHoursHint = () => {
    switch (selectedType.value) {
        case 'absence':
            return 'geralmente 8h para dia completo';
        case 'late_arrival':
            return 'horas de atraso';
        case 'early_departure':
            return 'horas não trabalhadas';
        default:
            return 'quantidade de horas';
    }
};

const getHoursDescription = () => {
    switch (selectedType.value) {
        case 'absence':
            return 'Para falta de dia completo, informe a carga horária diária (ex: 8.0 para 8 horas).';
        case 'late_arrival':
            return 'Informe quantas horas o técnico se atrasou (ex: 1.5 para 1 hora e 30 minutos).';
        case 'early_departure':
            return 'Informe quantas horas deixaram de ser trabalhadas devido à saída antecipada.';
        default:
            return 'Informe a quantidade de horas perdidas com precisão de 0,5 horas.';
    }
};

const getStatusLabel = (status) => {
    const labels = {
        'pending': 'Pendente',
        'approved': 'Aprovado',
        'rejected': 'Rejeitado'
    };
    return labels[status] || status;
};

const onSubmit = async (values) => {
    loading.value = true;
    
    try {
        await axios.put(`/absences/${route.params.id}`, values);
        toastr.success('Falta atualizada com sucesso!');
        router.push({ name: 'admin.absences.show', params: { id: route.params.id } });
    } catch (error) {
        console.error('Erro ao atualizar falta:', error);
        
        if (error.response?.data?.errors) {
            Object.values(error.response.data.errors).forEach(errorArray => {
                errorArray.forEach(message => toastr.error(message));
            });
        } else if (error.response?.data?.error) {
            toastr.error(error.response.data.error);
        } else {
            toastr.error('Erro ao atualizar falta');
        }
    } finally {
        loading.value = false;
    }
};

// Lifecycle
onMounted(() => {
    getData();
});
</script>

<style scoped>
.form-text {
    font-size: 0.875rem;
}

.alert {
    border-left: 4px solid;
}

.alert-warning {
    border-left-color: #ffc107;
}
</style>