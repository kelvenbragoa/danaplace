<template>
    <div class="container-fluid p-0">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col">
                                <h3 class="card-title mb-0">Editar Processo Salarial</h3>
                            </div>
                            <div class="col-auto">
                                <router-link class="btn btn-secondary" :to="`/admin/salary-processes/${$route.params.id}`">
                                    <vue-feather type="arrow-left" size="16"></vue-feather>
                                    Voltar
                                </router-link>
                            </div>
                        </div>
                    </div>
                    <div class="card-body" v-if="salaryProcess.id">
                        <div v-if="salaryProcess.status === 'approved' || salaryProcess.status === 'paid'" 
                             class="alert alert-warning">
                            <strong>Atenção:</strong> Este processo já foi aprovado ou pago e não pode ser editado.
                        </div>

                        <Form v-else @submit="onSubmit" :validation-schema="schema" v-slot="{ errors, setFieldValue, values }" ref="form">
                            <!-- Informações Gerais -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Título *</label>
                                        <Field type="text" name="title" class="form-control" :class="{'is-invalid': errors.title}" />
                                        <span class="invalid-feedback">{{ errors.title }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Período</label>
                                        <input type="text" class="form-control" 
                                               :value="formatMonthYear(salaryProcess.month, salaryProcess.year)" 
                                               readonly />
                                        <small class="form-text text-muted">O período não pode ser alterado</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label class="form-label">Descrição</label>
                                        <Field as="textarea" name="description" class="form-control" rows="3" />
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <!-- Técnicos e Salários -->
                            <div class="row">
                                <div class="col-12">
                                    <h5>Técnicos e Salários</h5>
                                    <div class="mb-3">
                                        <button type="button" @click="addTechnician" class="btn btn-outline-primary btn-sm">
                                            <vue-feather type="plus" size="16"></vue-feather>
                                            Adicionar Técnico
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <FieldArray name="technicians" v-slot="{ fields, push, remove }">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th width="20%">Técnico</th>
                                                <th width="12%">Salário Base *</th>
                                                <th width="10%">H. Extras</th>
                                                <th width="10%">Valor H. Extras</th>
                                                <th width="10%">Bônus</th>
                                                <th width="10%">Descontos</th>
                                                <th width="12%">Líquido</th>
                                                <th width="12%">Observações</th>
                                                <th width="4%">Ação</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(field, index) in fields" :key="field.key">
                                                <td>
                                                    <Field as="select" :name="`technicians[${index}].technician_id`" 
                                                           class="form-control form-control-sm"
                                                           :class="{'is-invalid': errors[`technicians[${index}].technician_id`]}">
                                                        <option value="">Selecionar</option>
                                                        <option v-for="tech in availableTechnicians" 
                                                                :key="tech.id" 
                                                                :value="tech.id"
                                                                :disabled="isAlreadySelected(tech.id, index)">
                                                            {{ tech.name }}
                                                        </option>
                                                    </Field>
                                                    <span class="invalid-feedback">{{ errors[`technicians[${index}].technician_id`] }}</span>
                                                </td>
                                                <td>
                                                    <Field type="number" step="0.01" :name="`technicians[${index}].base_salary`" 
                                                           class="form-control form-control-sm"
                                                           :class="{'is-invalid': errors[`technicians[${index}].base_salary`]}"
                                                           @input="calculateValues(index)" />
                                                    <span class="invalid-feedback">{{ errors[`technicians[${index}].base_salary`] }}</span>
                                                </td>
                                                <td>
                                                    <Field type="number" step="0.01" :name="`technicians[${index}].overtime_hours`" 
                                                           class="form-control form-control-sm"
                                                           @input="calculateValues(index)" />
                                                </td>
                                                <td>
                                                    <Field type="number" step="0.01" :name="`technicians[${index}].overtime_amount`" 
                                                           class="form-control form-control-sm" readonly />
                                                </td>
                                                <td>
                                                    <Field type="number" step="0.01" :name="`technicians[${index}].bonus`" 
                                                           class="form-control form-control-sm"
                                                           @input="calculateValues(index)" />
                                                </td>
                                                <td>
                                                    <Field type="number" step="0.01" :name="`technicians[${index}].deductions`" 
                                                           class="form-control form-control-sm"
                                                           @input="calculateValues(index)" />
                                                </td>
                                                <td>
                                                    <Field type="number" step="0.01" :name="`technicians[${index}].net_salary`" 
                                                           class="form-control form-control-sm text-success fw-bold" readonly />
                                                </td>
                                                <td>
                                                    <Field as="textarea" rows="2" :name="`technicians[${index}].observations`" 
                                                           class="form-control form-control-sm" />
                                                </td>
                                                <td>
                                                    <button type="button" @click="remove(index)" 
                                                            class="btn btn-sm btn-outline-danger">
                                                        <vue-feather type="trash-2" size="14"></vue-feather>
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                        <tfoot v-if="fields.length > 0" class="table-info">
                                            <tr>
                                                <td><strong>Total:</strong></td>
                                                <td><strong>{{ formatCurrency(totals.base_salary) }}</strong></td>
                                                <td><strong>{{ totals.overtime_hours }}h</strong></td>
                                                <td><strong>{{ formatCurrency(totals.overtime_amount) }}</strong></td>
                                                <td><strong>{{ formatCurrency(totals.bonus) }}</strong></td>
                                                <td><strong>{{ formatCurrency(totals.deductions) }}</strong></td>
                                                <td><strong>{{ formatCurrency(totals.net_salary) }}</strong></td>
                                                <td colspan="2"></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>

                                <div v-if="fields.length === 0" class="alert alert-info">
                                    Nenhum técnico adicionado. Clique em "Adicionar Técnico" para começar.
                                </div>
                            </FieldArray>

                            <hr>

                            <div class="row">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary" :disabled="loading">
                                        <span v-if="loading" class="spinner-border spinner-border-sm mr-2"></span>
                                        {{ loading ? 'Salvando...' : 'Salvar Alterações' }}
                                    </button>
                                    <router-link class="btn btn-secondary ml-2" :to="`/admin/salary-processes/${$route.params.id}`">
                                        Cancelar
                                    </router-link>
                                </div>
                            </div>
                        </Form>
                    </div>
                    <div v-else class="card-body">
                        <div class="text-center">
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">Carregando...</span>
                            </div>
                            <p class="mt-2">Carregando dados...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal para adicionar técnico -->
        <div class="modal fade" id="addTechnicianModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Selecionar Técnico</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Técnico</label>
                            <select v-model="selectedTechnicianId" class="form-control">
                                <option value="">Selecionar</option>
                                <option v-for="tech in availableForAdd" :key="tech.id" :value="tech.id">
                                    {{ tech.name }} - {{ tech.department?.name }}
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" @click="confirmAddTechnician" class="btn btn-primary">Adicionar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed, nextTick } from 'vue'
import { Form, Field, FieldArray } from 'vee-validate'
import * as yup from 'yup'
import axios from 'axios'
import { useRoute, useRouter } from 'vue-router'
import {useToastr} from '../../../toastr';
import VueFeather from 'vue-feather'


const toastr = useToastr();

const route = useRoute()
const router = useRouter()
const form = ref(null)
const loading = ref(false)
const salaryProcess = ref({})
const availableTechnicians = ref([])
const selectedTechnicianId = ref('')

const schema = yup.object({
    title: yup.string().required('Título é obrigatório'),
    description: yup.string(),
    technicians: yup.array().of(
        yup.object({
            technician_id: yup.number().required('Selecione um técnico'),
            base_salary: yup.number().min(0).required('Salário base é obrigatório'),
            overtime_hours: yup.number().min(0),
            overtime_amount: yup.number().min(0),
            bonus: yup.number().min(0),
            deductions: yup.number().min(0),
            net_salary: yup.number().min(0),
            observations: yup.string()
        })
    ).min(1, 'Adicione pelo menos um técnico')
})

const totals = computed(() => {
    const values = form.value?.values
    if (!values?.technicians) return { base_salary: 0, overtime_hours: 0, overtime_amount: 0, bonus: 0, deductions: 0, net_salary: 0 }
    
    return values.technicians.reduce((acc, tech) => {
        acc.base_salary += parseFloat(tech.base_salary || 0)
        acc.overtime_hours += parseFloat(tech.overtime_hours || 0)
        acc.overtime_amount += parseFloat(tech.overtime_amount || 0)
        acc.bonus += parseFloat(tech.bonus || 0)
        acc.deductions += parseFloat(tech.deductions || 0)
        acc.net_salary += parseFloat(tech.net_salary || 0)
        return acc
    }, { base_salary: 0, overtime_hours: 0, overtime_amount: 0, bonus: 0, deductions: 0, net_salary: 0 })
})

const availableForAdd = computed(() => {
    const values = form.value?.values
    const usedIds = values?.technicians?.map(t => parseInt(t.technician_id)) || []
    return availableTechnicians.value.filter(tech => !usedIds.includes(tech.id))
})

const getData = async () => {
    try {
        const [processResponse, techniciansResponse] = await Promise.all([
            axios.get(`/salary-processes/${route.params.id}`),
            axios.get('/technicians-with-salary')
        ])
        
        salaryProcess.value = processResponse.data.salary_process
        availableTechnicians.value = techniciansResponse.data.technicians

        // Preencher formulário
        await nextTick()
        if (form.value) {
            form.value.setValues({
                title: salaryProcess.value.title,
                description: salaryProcess.value.description || '',
                technicians: salaryProcess.value.items.map(item => ({
                    technician_id: item.technician_id,
                    base_salary: parseFloat(item.base_salary),
                    overtime_hours: parseFloat(item.overtime_hours),
                    overtime_amount: parseFloat(item.overtime_amount),
                    bonus: parseFloat(item.bonus),
                    deductions: parseFloat(item.deductions),
                    net_salary: parseFloat(item.net_salary),
                    observations: item.observations || ''
                }))
            })
        }
    } catch (error) {
        console.error('Erro ao buscar dados:', error)
        toastr.error('Erro ao carregar processo salarial')
        router.push('/admin/salary-processes')
    }
}

const isAlreadySelected = (technicianId, currentIndex) => {
    const values = form.value?.values
    if (!values?.technicians) return false
    
    return values.technicians.some((tech, index) => 
        index !== currentIndex && parseInt(tech.technician_id) === technicianId
    )
}

const calculateValues = (index) => {
    setTimeout(() => {
        const values = form.value?.values
        if (!values?.technicians?.[index]) return

        const tech = values.technicians[index]
        const technician = availableTechnicians.value.find(t => t.id === parseInt(tech.technician_id))
        
        let overtimeAmount = 0
        if (tech.overtime_hours > 0) {
            const overtimeRate = technician?.overtime_rate || (tech.base_salary / 160 * 1.5)
            overtimeAmount = tech.overtime_hours * overtimeRate
        }

        const netSalary = (parseFloat(tech.base_salary || 0) + overtimeAmount + parseFloat(tech.bonus || 0)) - parseFloat(tech.deductions || 0)

        form.value.setFieldValue(`technicians[${index}].overtime_amount`, overtimeAmount.toFixed(2))
        form.value.setFieldValue(`technicians[${index}].net_salary`, netSalary.toFixed(2))
    }, 100)
}

const addTechnician = () => {
    if (availableForAdd.value.length === 0) {
        toastr.warning('Todos os técnicos já foram adicionados')
        return
    }
    
    const modal = new bootstrap.Modal(document.getElementById('addTechnicianModal'))
    modal.show()
}

const confirmAddTechnician = () => {
    if (!selectedTechnicianId.value) {
        toastr.error('Selecione um técnico')
        return
    }

    const technician = availableTechnicians.value.find(t => t.id === parseInt(selectedTechnicianId.value))
    
    const values = form.value?.values
    const technicians = values?.technicians || []
    
    technicians.push({
        technician_id: technician.id,
        base_salary: technician.salary || 0,
        overtime_hours: 0,
        overtime_amount: 0,
        bonus: 0,
        deductions: 0,
        net_salary: technician.salary || 0,
        observations: ''
    })

    form.value.setFieldValue('technicians', technicians)
    
    selectedTechnicianId.value = ''
    const modal = bootstrap.Modal.getInstance(document.getElementById('addTechnicianModal'))
    modal.hide()

    nextTick(() => {
        calculateValues(technicians.length - 1)
    })
}

const formatMonthYear = (month, year) => {
    const monthNames = [
        'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
        'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'
    ]
    return `${monthNames[month - 1]} ${year}`
}

const formatCurrency = (value) => {
    return new Intl.NumberFormat('pt-MZ', {
        style: 'currency',
        currency: 'MZN'
    }).format(value)
}

const onSubmit = async (values) => {
    loading.value = true
    
    try {
        await axios.put(`/salary-processes/${route.params.id}`, values)
        toastr.success('Processo salarial atualizado com sucesso!')
        router.push(`/admin/salary-processes/${route.params.id}`)
    } catch (error) {
        console.error('Erro ao atualizar:', error)
        if (error.response?.data?.errors) {
            Object.values(error.response.data.errors).forEach(errorArray => {
                errorArray.forEach(message => toastr.error(message))
            })
        } else {
            toastr.error('Erro ao atualizar processo salarial')
        }
    } finally {
        loading.value = false
    }
}

onMounted(() => {
    getData()
})
</script>

<style scoped>
.table th, .table td {
    vertical-align: middle;
}

.form-control-sm {
    font-size: 0.8rem;
}

.text-success {
    color: #28a745 !important;
}

.fw-bold {
    font-weight: bold !important;
}
</style>