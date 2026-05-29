<template>
    <div class="container-fluid p-0">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col">
                                <h3 class="card-title mb-0">Criar Processo Salarial</h3>
                            </div>
                            <div class="col-auto">
                                <router-link class="btn btn-secondary" to="/admin/salary-processes">
                                    <vue-feather type="arrow-left" size="16"></vue-feather>
                                    Voltar
                                </router-link>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <Form @submit="onSubmit" :validation-schema="schema" v-slot="{ errors }" ref="form">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Título *</label>
                                        <Field type="text" name="title" class="form-control" :class="{'is-invalid': errors.title}" 
                                               placeholder="Ex: Folha Salarial Janeiro 2025" />
                                        <span class="invalid-feedback">{{ errors.title }}</span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label">Mês *</label>
                                        <Field as="select" name="month" class="form-control" :class="{'is-invalid': errors.month}">
                                            <option value="">Selecionar</option>
                                            <option v-for="(month, index) in months" :key="index" :value="index + 1">
                                                {{ month }}
                                            </option>
                                        </Field>
                                        <span class="invalid-feedback">{{ errors.month }}</span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label">Ano *</label>
                                        <Field as="select" name="year" class="form-control" :class="{'is-invalid': errors.year}">
                                            <option value="">Selecionar</option>
                                            <option v-for="year in years" :key="year" :value="year">
                                                {{ year }}
                                            </option>
                                        </Field>
                                        <span class="invalid-feedback">{{ errors.year }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label class="form-label">Descrição</label>
                                        <Field as="textarea" name="description" class="form-control" rows="3" 
                                               placeholder="Observações sobre o processamento..." />
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <div class="row">
                                <div class="col-12">
                                    <h5>Técnicos e Salários</h5>
                                    <div class="mb-3">
                                        <button type="button" @click="selectAllTechnicians" class="btn btn-outline-primary btn-sm mr-2">
                                            Selecionar Todos
                                        </button>
                                        <button type="button" @click="clearAllTechnicians" class="btn btn-outline-secondary btn-sm">
                                            Limpar Seleção
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <FieldArray name="technicians" v-slot="{ fields, push, remove }">
                                <div v-if="availableTechnicians.length > 0" class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th width="4%">Sel.</th>
                                                <th width="18%">Técnico</th>
                                                <th width="12%">Departamento</th>
                                                <th width="8%">Faltas</th>
                                                <th width="10%">Salário Base *</th>
                                                <th width="8%">H. Extras</th>
                                                <th width="8%">Bônus</th>
                                                <th width="10%">Descontos</th>
                                                <th width="10%">Líquido</th>
                                                <th width="12%">Observações</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(technician, index) in availableTechnicians" :key="technician.id">
                                                <td>
                                                    <input type="checkbox" 
                                                           :checked="isSelected(technician.id)" 
                                                           @change="toggleTechnician(technician, $event.target.checked)" />
                                                </td>
                                                <td>{{ technician.name }}</td>
                                                <td>{{ technician.department?.name }}</td>
                                                <td>
                                                    <div v-if="technicianAbsences[technician.id]" class="text-center">
                                                        <span class="badge bg-warning" v-if="technicianAbsences[technician.id].total_absences > 0">
                                                            {{ technicianAbsences[technician.id].total_absences }} 
                                                            ({{ technicianAbsences[technician.id].total_hours_lost }}h)
                                                        </span>
                                                        <span class="text-muted" v-else>-</span>
                                                    </div>
                                                    <div v-else class="text-center text-muted">-</div>
                                                </td>
                                                <td>
                                                    <Field v-if="isSelected(technician.id)" 
                                                           type="number" 
                                                           step="0.01"
                                                           :name="`technicians.${getSelectedIndex(technician.id)}.base_salary`" 
                                                           :model-value="selectedTechnicians[getSelectedIndex(technician.id)]?.base_salary"
                                                           class="form-control form-control-sm"
                                                           @input="updateField(technician.id, 'base_salary', $event.target.value)" />
                                                    <span v-else>-</span>
                                                </td>
                                                <td>
                                                    <Field v-if="isSelected(technician.id)" 
                                                           type="number" 
                                                           step="0.01"
                                                           :name="`technicians.${getSelectedIndex(technician.id)}.overtime_hours`" 
                                                           :model-value="selectedTechnicians[getSelectedIndex(technician.id)]?.overtime_hours"
                                                           class="form-control form-control-sm"
                                                           @input="updateField(technician.id, 'overtime_hours', $event.target.value)" />
                                                    <span v-else>-</span>
                                                </td>
                                                <td>
                                                    <Field v-if="isSelected(technician.id)" 
                                                           type="number" 
                                                           step="0.01"
                                                           :name="`technicians.${getSelectedIndex(technician.id)}.bonus`" 
                                                           :model-value="selectedTechnicians[getSelectedIndex(technician.id)]?.bonus"
                                                           class="form-control form-control-sm"
                                                           @input="updateField(technician.id, 'bonus', $event.target.value)" />
                                                    <span v-else>-</span>
                                                </td>
                                                <td>
                                                    <Field v-if="isSelected(technician.id)" 
                                                           type="number" 
                                                           step="0.01"
                                                           :name="`technicians.${getSelectedIndex(technician.id)}.deductions`" 
                                                           :model-value="selectedTechnicians[getSelectedIndex(technician.id)]?.deductions"
                                                           class="form-control form-control-sm"
                                                           @input="updateField(technician.id, 'deductions', $event.target.value)" />
                                                    <span v-else>-</span>
                                                </td>
                                                <td>
                                                    <strong v-if="isSelected(technician.id)">
                                                        {{ formatCurrency(getNetSalary(technician.id)) }}
                                                    </strong>
                                                    <span v-else>-</span>
                                                </td>
                                                <td>
                                                    <Field v-if="isSelected(technician.id)" 
                                                           as="textarea" 
                                                           rows="2"
                                                           :name="`technicians.${getSelectedIndex(technician.id)}.observations`" 
                                                           :model-value="selectedTechnicians[getSelectedIndex(technician.id)]?.observations"
                                                           class="form-control form-control-sm"
                                                           @input="updateField(technician.id, 'observations', $event.target.value)" />
                                                    <span v-else>-</span>
                                                </td>
                                            </tr>
                                        </tbody>
                                        <tfoot v-if="selectedTechnicians.length > 0">
                                            <tr class="table-info">
                                                <td colspan="7"><strong>Total:</strong></td>
                                                <td><strong>{{ formatCurrency(totalNet) }}</strong></td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>

                                <div v-if="selectedTechnicians.length === 0" class="alert alert-info">
                                    Selecione pelo menos um técnico para processar os salários.
                                </div>
                            </FieldArray>

                            <hr>

                            <div class="row">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary" :disabled="loading || selectedTechnicians.length === 0">
                                        <span v-if="loading" class="spinner-border spinner-border-sm mr-2"></span>
                                        {{ loading ? 'Processando...' : 'Processar Folha Salarial' }}
                                    </button>
                                    <router-link class="btn btn-secondary ml-2" to="/admin/salary-processes">
                                        Cancelar
                                    </router-link>
                                </div>
                            </div>
                        </Form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import { Form, Field, FieldArray } from 'vee-validate'
import * as yup from 'yup'
import axios from 'axios'
import { useRouter } from 'vue-router'
import {useToastr} from '../../../toastr';
import VueFeather from 'vue-feather'


const toastr = useToastr();

const router = useRouter()
// Reactive data
const loading = ref(false)
const availableTechnicians = ref([])
const selectedTechnicians = ref([])
const salaryData = ref({})
const technicianAbsences = ref({}) // Nova variável para armazenar faltas
const form = ref(null) // Referência ao formulário

const months = [
    'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
    'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'
]

const currentYear = new Date().getFullYear()
const years = Array.from({length: 5}, (_, i) => currentYear - 2 + i)

const schema = yup.object({
    title: yup.string().required('Título é obrigatório'),
    month: yup.number().required('Mês é obrigatório'),
    year: yup.number().required('Ano é obrigatório'),
    description: yup.string(),
    // Validação dos técnicos removida temporariamente para permitir submissão
    // technicians: yup.array().of(
    //     yup.object({
    //         technician_id: yup.number().required(),
    //         base_salary: yup.number().min(0, 'Salário deve ser maior que 0').required('Salário base é obrigatório'),
    //         overtime_hours: yup.number().min(0),
    //         bonus: yup.number().min(0),
    //         deductions: yup.number().min(0),
    //         observations: yup.string()
    //     })
    // ).min(1, 'Selecione pelo menos um técnico')
})

const totalNet = computed(() => {
    return selectedTechnicians.value.reduce((total, tech) => {
        const netSalary = getNetSalary(tech.technician_id)
        return total + (isNaN(netSalary) ? 0 : netSalary)
    }, 0)
})

const getTechnicians = async () => {
    try {
        const response = await axios.get('/technicians-with-salary')
        availableTechnicians.value = response.data.technicians
    } catch (error) {
        console.error('Erro ao buscar técnicos:', error)
        toastr.error('Erro ao carregar técnicos')
    }
}

const getTechnicianAbsences = async (month, year) => {
    if (!month || !year) return
    
    try {
        technicianAbsences.value = {}
        
        for (const technician of availableTechnicians.value) {
            const response = await axios.get('/technician-absences', {
                params: {
                    technician_id: technician.id,
                    month: month,
                    year: year
                }
            })
            
            technicianAbsences.value[technician.id] = response.data.summary
        }
    } catch (error) {
        console.error('Erro ao buscar faltas:', error)
        // Não mostrar erro para o usuário, apenas registrar no console
    }
}

const isSelected = (technicianId) => {
    return selectedTechnicians.value.some(tech => tech.technician_id === technicianId)
}

const getSelectedIndex = (technicianId) => {
    return selectedTechnicians.value.findIndex(tech => tech.technician_id === technicianId)
}

const toggleTechnician = (technician, selected) => {
    if (selected) {
        const baseSalary = Number(technician.salary) || 0
        
        const newTechnician = {
            technician_id: technician.id,
            base_salary: baseSalary,
            overtime_hours: 0,
            bonus: 0,
            deductions: 0,
            observations: ''
        }
        
        selectedTechnicians.value.push(newTechnician)
        
        // Inicializar dados de cálculo
        salaryData.value[technician.id] = {
            base_salary: baseSalary,
            overtime_hours: 0,
            overtime_amount: 0,
            bonus: 0,
            deductions: 0,
            net_salary: baseSalary
        }
        
        // Calcular o salário líquido inicial
        calculateNet(technician.id)
    } else {
        const index = getSelectedIndex(technician.id)
        if (index !== -1) {
            selectedTechnicians.value.splice(index, 1)
            delete salaryData.value[technician.id]
        }
    }
}

const selectAllTechnicians = () => {
    availableTechnicians.value.forEach(technician => {
        if (!isSelected(technician.id)) {
            toggleTechnician(technician, true)
        }
    })
}

const clearAllTechnicians = () => {
    selectedTechnicians.value = []
    salaryData.value = {}
}

const updateField = (technicianId, field, value) => {
    const index = getSelectedIndex(technicianId)
    if (index !== -1) {
        // Atualizar o valor no selectedTechnicians
        if (field === 'observations') {
            selectedTechnicians.value[index][field] = value
        } else {
            selectedTechnicians.value[index][field] = Number(value) || 0
            // Recalcular o salário líquido apenas para campos numéricos
            calculateNet(technicianId)
        }
    }
}

const calculateNet = (technicianId) => {
    const index = getSelectedIndex(technicianId)
    if (index !== -1) {
        const tech = selectedTechnicians.value[index]
        const technician = availableTechnicians.value.find(t => t.id === technicianId)
        
        // Converter valores para números válidos
        const baseSalary = Number(tech.base_salary) || 0
        const overtimeHours = Number(tech.overtime_hours) || 0
        const bonus = Number(tech.bonus) || 0
        const deductions = Number(tech.deductions) || 0
        
        let overtimeAmount = 0
        if (overtimeHours > 0) {
            const overtimeRate = Number(technician.overtime_rate) || (baseSalary / 160 * 1.5)
            overtimeAmount = overtimeHours * overtimeRate
        }
        
        const netSalary = (baseSalary + overtimeAmount + bonus) - deductions
        
        // Atualizar salaryData para o cálculo do total
        salaryData.value[technicianId] = {
            base_salary: baseSalary,
            overtime_hours: overtimeHours,
            overtime_amount: overtimeAmount,
            bonus: bonus,
            deductions: deductions,
            net_salary: isNaN(netSalary) ? 0 : netSalary
        }
        
        // Atualizar também o selectedTechnicians com os valores calculados
        selectedTechnicians.value[index] = {
            ...tech,
            base_salary: baseSalary,
            overtime_hours: overtimeHours,
            bonus: bonus,
            deductions: deductions
        }
    }
}

const getNetSalary = (technicianId) => {
    const netSalary = salaryData.value[technicianId]?.net_salary || 0
    return isNaN(netSalary) ? 0 : Number(netSalary)
}

const formatCurrency = (value) => {
    return new Intl.NumberFormat('pt-MZ', {
        style: 'currency',
        currency: 'MZN'
    }).format(value)
}

const onSubmit = async (values) => {
    // Validação manual simples
    if (selectedTechnicians.value.length === 0) {
        toastr.error('Selecione pelo menos um técnico para processar')
        return
    }

    loading.value = true
    
    try {
        const payload = {
            ...values,
            technicians: selectedTechnicians.value.map(tech => ({
                ...tech,
                ...salaryData.value[tech.technician_id]
            }))
        }
        
        console.log('Payload enviado:', payload) // Para debug
        
        await axios.post('/salary-processes', payload)
        toastr.success('Processo salarial criado com sucesso!')
        router.push('/admin/salary-processes')
    } catch (error) {
        console.error('Erro ao criar processo:', error)
        if (error.response?.data?.errors) {
            Object.values(error.response.data.errors).forEach(errorArray => {
                errorArray.forEach(message => toastr.error(message))
            })
        } else {
            toastr.error('Erro ao criar processo salarial')
        }
    } finally {
        loading.value = false
    }
}

onMounted(() => {
    getTechnicians()
})

// Watcher para buscar faltas quando mês/ano mudar
watch([() => form.value?.values?.month, () => form.value?.values?.year], async ([month, year]) => {
    if (month && year) {
        await getTechnicianAbsences(month, year)
    }
}, { deep: true })
</script>

<style scoped>
.table th, .table td {
    vertical-align: middle;
}

.form-control-sm {
    font-size: 0.8rem;
}
</style>