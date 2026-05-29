<script setup>
import {onMounted, ref, reactive} from 'vue';
import axios from 'axios';
import {useToastr} from '../../../toastr';
import {Form, Field} from 'vee-validate';
import * as yup from 'yup';
import { useRouter } from "vue-router";
import moment from 'moment';
import VueFeather from 'vue-feather';

const loading = ref(false);
const toastr = useToastr();
const loadingDiv = ref(true);
const technicians = ref([]);
const currentYear = new Date().getFullYear();
const years = ref([]);

// Gerar lista de anos (atual + próximos 2 anos)
for (let year = currentYear; year <= currentYear + 2; year++) {
    years.value.push(year);
}

const router = useRouter();

const schema = yup.object({
    technician_id: yup.string().required('Técnico é obrigatório'),
    year: yup.number().required('Ano é obrigatório').min(currentYear, 'Ano deve ser atual ou futuro'),
    start_date: yup.date().required('Data de início é obrigatória')
        .min(new Date(), 'Data deve ser no futuro'),
    end_date: yup.date().required('Data de fim é obrigatória')
        .min(yup.ref('start_date'), 'Data de fim deve ser após a data de início'),
    days_requested: yup.number().required('Dias solicitados é obrigatório')
        .min(1, 'Mínimo 1 dia').max(30, 'Máximo 30 dias'),
    replacement_technician_id: yup.string()
        .notOneOf([yup.ref('technician_id')], 'Substituto deve ser diferente do técnico principal'),
    notes: yup.string().max(500, 'Máximo 500 caracteres')
});

const calculateWorkingDays = (startDate, endDate) => {
    if (!startDate || !endDate) return 0;
    
    const start = moment(startDate);
    const end = moment(endDate);
    let workingDays = 0;
    
    const current = start.clone();
    while (current.isSameOrBefore(end)) {
        if (current.isoWeekday() <= 5) { // Segunda a sexta (1-5)
            workingDays++;
        }
        current.add(1, 'day');
    }
    
    return workingDays;
};

const validateDates = (values) => {
    if (values.start_date && values.end_date) {
        const workingDays = calculateWorkingDays(values.start_date, values.end_date);
        
        if (values.days_requested > workingDays) {
            toastr.warning(`Período selecionado tem apenas ${workingDays} dias úteis. Ajuste os dias solicitados.`);
            return false;
        }
    }
    return true;
};

const createRecordFunction = (values, actions) => {
    if (!validateDates(values)) {
        return;
    }

    loading.value = true;

    axios.post('/vacation-plans', values)
        .then((response) => {
            actions.resetForm();
            router.push({ path: '/admin/vacation-plans' });
            toastr.success('Plano de férias criado com sucesso');
        })
        .catch((error) => {
            loading.value = false;
            
            if (error.response?.status === 422) {
                const errorMsg = error.response.data.message || 'Erro de validação';
                toastr.error(errorMsg);
                
                if (error.response.data.errors) {
                    actions.setErrors(error.response.data.errors);
                }
            } else {
                toastr.error('Erro ao criar plano de férias');
            }
        })
        .finally(() => {
            loading.value = false;
        });
};

const getTechnicians = async () => {
    try {
        const response = await axios.get('/vacation-plans/create');
        technicians.value = response.data.technicians;
        loadingDiv.value = false;
    } catch (error) {
        toastr.error('Erro ao carregar técnicos');
        loadingDiv.value = false;
    }
};

onMounted(() => {
    getTechnicians();
});
</script>

<template>
    <div v-if="!loadingDiv">
        <div class="d-flex justify-content-between w-100 flex-wrap">
            <div class="mb-3 mb-lg-0">
                <h1 class="h3 d-inline align-middle">Criar Plano de Férias</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <router-link to="/admin">Dashboard</router-link>
                        </li>
                        <li class="breadcrumb-item">
                            <router-link to="/admin/vacation-plans">Planos de Férias</router-link>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Criar</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Novo Plano de Férias</h5>
                        <small class="text-muted">Preencha os dados para criar um novo plano de férias</small>
                    </div>
                    
                    <div class="card-body">
                        <Form @submit="createRecordFunction" :validation-schema="schema" v-slot="{ errors, values }">
                            <div class="row">
                                <!-- Técnico -->
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Técnico <span class="text-danger">*</span></label>
                                        <Field name="technician_id" as="select" 
                                               class="form-control" 
                                               :class="{'is-invalid': errors.technician_id}">
                                            <option value="">Selecione o técnico</option>
                                            <option v-for="technician in technicians" 
                                                    :key="technician.id" 
                                                    :value="technician.id">
                                                {{ technician.name }} - {{ technician.code }} ({{ technician.department?.name }})
                                            </option>
                                        </Field>
                                        <div v-if="errors.technician_id" class="invalid-feedback">
                                            {{ errors.technician_id }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Ano -->
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Ano <span class="text-danger">*</span></label>
                                        <Field name="year" as="select" 
                                               class="form-control" 
                                               :class="{'is-invalid': errors.year}">
                                            <option v-for="year in years" 
                                                    :key="year" 
                                                    :value="year">
                                                {{ year }}
                                            </option>
                                        </Field>
                                        <div v-if="errors.year" class="invalid-feedback">
                                            {{ errors.year }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Data de Início -->
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Data de Início <span class="text-danger">*</span></label>
                                        <Field name="start_date" type="date" 
                                               class="form-control" 
                                               :min="moment().format('YYYY-MM-DD')"
                                               :class="{'is-invalid': errors.start_date}" />
                                        <div v-if="errors.start_date" class="invalid-feedback">
                                            {{ errors.start_date }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Data de Fim -->
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Data de Fim <span class="text-danger">*</span></label>
                                        <Field name="end_date" type="date" 
                                               class="form-control"
                                               :min="values.start_date || moment().format('YYYY-MM-DD')"
                                               :class="{'is-invalid': errors.end_date}" />
                                        <div v-if="errors.end_date" class="invalid-feedback">
                                            {{ errors.end_date }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Dias Solicitados -->
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Dias Solicitados <span class="text-danger">*</span></label>
                                        <Field name="days_requested" type="number" 
                                               class="form-control" 
                                               min="1" max="30"
                                               :class="{'is-invalid': errors.days_requested}" />
                                        <div v-if="errors.days_requested" class="invalid-feedback">
                                            {{ errors.days_requested }}
                                        </div>
                                        <small class="text-muted">
                                            <span v-if="values.start_date && values.end_date">
                                                Período tem {{ calculateWorkingDays(values.start_date, values.end_date) }} dias úteis
                                            </span>
                                        </small>
                                    </div>
                                </div>

                                <!-- Técnico Substituto -->
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Técnico Substituto</label>
                                        <Field name="replacement_technician_id" as="select" 
                                               class="form-control" 
                                               :class="{'is-invalid': errors.replacement_technician_id}">
                                            <option value="">Selecione o substituto (opcional)</option>
                                            <option v-for="technician in technicians" 
                                                    :key="technician.id" 
                                                    :value="technician.id"
                                                    :disabled="technician.id == values.technician_id">
                                                {{ technician.name }} - {{ technician.code }}
                                            </option>
                                        </Field>
                                        <div v-if="errors.replacement_technician_id" class="invalid-feedback">
                                            {{ errors.replacement_technician_id }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Observações -->
                            <div class="form-group mb-3">
                                <label class="form-label">Observações</label>
                                <Field name="notes" as="textarea" 
                                       class="form-control" 
                                       rows="3"
                                       maxlength="500"
                                       :class="{'is-invalid': errors.notes}"
                                       placeholder="Observações sobre o plano de férias..." />
                                <div v-if="errors.notes" class="invalid-feedback">
                                    {{ errors.notes }}
                                </div>
                                <small class="text-muted">Máximo 500 caracteres</small>
                            </div>

                            <!-- Informações Importantes -->
                            <div class="alert alert-info">
                                <h6><vue-feather type="info"></vue-feather> Informações Importantes:</h6>
                                <ul class="mb-0">
                                    <li>O plano será criado com status "Pendente" e precisará de aprovação</li>
                                    <li>Apenas dias úteis (segunda a sexta) são contabilizados</li>
                                    <li>Máximo de 30 dias de férias por solicitação</li>
                                    <li>Não é possível criar planos conflitantes para o mesmo técnico</li>
                                </ul>
                            </div>

                            <!-- Botões -->
                            <div class="text-end">
                                <router-link to="/admin/vacation-plans" class="btn btn-secondary me-2">
                                    Cancelar
                                </router-link>
                                <button type="submit" class="btn btn-primary" :disabled="loading">
                                    <div v-if="loading" class="spinner-border spinner-border-sm me-2" role="status"></div>
                                    <vue-feather v-else type="save" class="me-1"></vue-feather>
                                    Criar Plano de Férias
                                </button>
                            </div>
                        </Form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div v-else>
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-center">
                    <div class="spinner-border" role="status">
                        <span class="sr-only"></span>
                    </div>
                </div>
                <br>
                <div class="d-flex justify-content-center">
                    Carregando Dados...
                </div>
            </div> 
        </div>
    </div>
</template>

<style scoped>
.alert-info {
    border-left: 4px solid #17a2b8;
}

.form-label {
    font-weight: 500;
}

.text-danger {
    color: #dc3545 !important;
}

.me-1, .me-2 {
    margin-right: 0.25rem;
}

.me-2 {
    margin-right: 0.5rem;
}
</style>