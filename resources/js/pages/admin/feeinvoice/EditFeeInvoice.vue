<script setup>
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { Form, Field, ErrorMessage, useForm } from 'vee-validate';
import * as yup from 'yup';
import axios from 'axios';
import VueFeather from 'vue-feather';

import {useToastr} from '../../../toastr';

const toastr = useToastr();
// Form validation schema
const schema = yup.object({
    due_date: yup.date().required('Data de vencimento é obrigatória'),
    notes: yup.string().max(1000, 'Máximo 1000 caracteres')
});

// Reactive data
const router = useRouter();
const route = useRoute();
const invoice = ref(null);
const loadingDiv = ref(true);
const loadingSubmit = ref(false);

// Form composable
const { errors, handleSubmit, setErrors, setValues } = useForm({
    validationSchema: schema
});

// Methods
const getData = async () => {
    try {
        loadingDiv.value = true;
        const response = await axios.get(`/fee-invoices/${route.params.id}/edit`);
        invoice.value = response.data.invoice;
        
        // Set form values
        setValues({
            due_date: invoice.value.due_date.split('T')[0], // Format for input[type="date"]
            notes: invoice.value.notes || ''
        });
    } catch (error) {
        toastr.error('Erro ao carregar fatura');
        console.error(error);
        router.push('/admin/fee-invoices');
    } finally {
        loadingDiv.value = false;
    }
};

const onSubmit = handleSubmit(async (values) => {
    loadingSubmit.value = true;

    try {
        const response = await axios.patch(`/fee-invoices/${invoice.value.id}`, values);
        toastr.success('Fatura atualizada com sucesso!');
        router.push(`/admin/fee-invoices/${invoice.value.id}`);
    } catch (error) {
        loadingSubmit.value = false;
        toastr.error('Erro ao atualizar fatura');
        if (error.response?.data?.errors) {
            setErrors(error.response.data.errors);
        }
        if (error.response?.data?.message) {
            toastr.error(error.response.data.message);
        }
    }
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('pt-MZ', {
        style: 'currency',
        currency: 'MZN',
        minimumFractionDigits: 2
    }).format(amount);
};

const getStatusBadge = (status) => {
    const badges = {
        'draft': { class: 'badge-secondary', text: 'Rascunho' },
        'issued': { class: 'badge-primary', text: 'Emitida' },
        'partially_paid': { class: 'badge-warning', text: 'Parcialmente Paga' },
        'paid': { class: 'badge-success', text: 'Paga' },
        'overdue': { class: 'badge-danger', text: 'Vencida' },
        'cancelled': { class: 'badge-dark', text: 'Cancelada' }
    };
    return badges[status] || { class: 'badge-secondary', text: 'Desconhecido' };
};

// Lifecycle
onMounted(() => {
    getData();
});
</script>

<template>
    <div v-if="!loadingDiv && invoice">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1">
                    <vue-feather type="edit" class="align-middle me-2"></vue-feather>
                    Editar Fatura {{ invoice.invoice_number }}
                </h1>
                <p class="text-muted mb-0">{{ invoice.period_description }}</p>
            </div>
            <div class="d-flex gap-2">
                <router-link 
                    :to="{ name: 'admin.fee-invoices.show', params: { id: invoice.id } }"
                    class="btn btn-outline-primary"
                >
                    <vue-feather type="eye" size="16" class="me-1"></vue-feather>
                    Visualizar
                </router-link>
                <router-link 
                    to="/admin/fee-invoices" 
                    class="btn btn-secondary"
                >
                    <vue-feather type="arrow-left" size="16" class="me-1"></vue-feather>
                    Voltar
                </router-link>
            </div>
        </div>

        <div class="row">
            <!-- Formulário de Edição -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <vue-feather type="settings" size="20" class="me-2"></vue-feather>
                            Dados da Fatura
                        </h5>
                    </div>
                    <div class="card-body">
                        <Form @submit="onSubmit">
                            <div class="mb-3">
                                <label class="form-label">Data de Vencimento *</label>
                                <Field name="due_date" v-slot="{ field }">
                                    <input 
                                        type="date" 
                                        v-bind="field"
                                        class="form-control" 
                                        :class="{ 'is-invalid': errors.due_date }"
                                    />
                                </Field>
                                <ErrorMessage name="due_date" class="invalid-feedback" />
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Observações</label>
                                <Field name="notes" v-slot="{ field }">
                                    <textarea 
                                        v-bind="field"
                                        class="form-control" 
                                        :class="{ 'is-invalid': errors.notes }"
                                        rows="4"
                                        placeholder="Observações sobre a fatura..."
                                    ></textarea>
                                </Field>
                                <ErrorMessage name="notes" class="invalid-feedback" />
                                <div class="form-text">Máximo 1000 caracteres</div>
                            </div>

                            <button 
                                type="submit" 
                                class="btn btn-primary"
                                :disabled="loadingSubmit"
                            >
                                <div v-if="loadingSubmit" class="spinner-border spinner-border-sm me-2"></div>
                                <vue-feather v-else type="save" size="16" class="me-1"></vue-feather>
                                {{ loadingSubmit ? 'Salvando...' : 'Salvar Alterações' }}
                            </button>
                        </Form>
                    </div>
                </div>
            </div>

            <!-- Informações da Fatura -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <vue-feather type="info" size="20" class="me-2"></vue-feather>
                            Informações Atuais
                        </h5>
                    </div>
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-sm-4">Número:</dt>
                            <dd class="col-sm-8">{{ invoice.invoice_number }}</dd>

                            <dt class="col-sm-4">Status:</dt>
                            <dd class="col-sm-8">
                                <span :class="`badge ${getStatusBadge(invoice.status).class}`">
                                    {{ getStatusBadge(invoice.status).text }}
                                </span>
                            </dd>

                            <dt class="col-sm-4">Período:</dt>
                            <dd class="col-sm-8">{{ invoice.period_description }}</dd>

                            <dt class="col-sm-4">Valor Total:</dt>
                            <dd class="col-sm-8 fw-bold">{{ formatCurrency(invoice.total_amount) }}</dd>

                            <dt class="col-sm-4">Valor Pago:</dt>
                            <dd class="col-sm-8 text-success">{{ formatCurrency(invoice.paid_amount) }}</dd>

                            <dt class="col-sm-4">Total de Itens:</dt>
                            <dd class="col-sm-8">{{ invoice.items.length }}</dd>

                            <dt class="col-sm-4">Itens Pagos:</dt>
                            <dd class="col-sm-8">{{ invoice.items.filter(i => i.is_paid).length }}</dd>
                        </dl>

                        <div class="alert alert-info mt-3">
                            <vue-feather type="info" size="16" class="me-2"></vue-feather>
                            <strong>Nota:</strong> Apenas a data de vencimento e observações podem ser editadas. Para alterar os itens, use a visualização da fatura.
                        </div>
                    </div>
                </div>

                <!-- Resumo dos Itens -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <vue-feather type="list" size="20" class="me-2"></vue-feather>
                            Resumo dos Itens
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Equipamento</th>
                                        <th class="text-end">Taxas</th>
                                        <th class="text-end">Valor</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="item in invoice.items" :key="item.id">
                                        <td>
                                            <small>{{ item.equipment.name }}</small>
                                        </td>
                                        <td class="text-end">
                                            <span class="badge" :class="item.is_paid ? 'bg-success' : 'bg-warning'">
                                                {{ item.fee.name }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            {{ formatCurrency(item.amount) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div v-else class="text-center p-5">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Carregando...</span>
        </div>
        <p class="mt-2">Carregando fatura...</p>
    </div>
</template>

<style scoped>
dl.row dt {
    font-weight: 600;
}

.badge {
    font-size: 0.75em;
}

.table-sm {
    font-size: 0.875rem;
}

.alert-info {
    border-left: 4px solid #0dcaf0;
}
</style>