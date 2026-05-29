<template>
    <div>
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">Editar Despesa</h1>
                <p class="text-muted">Edite informações do registro de despesa</p>
            </div>
            <router-link 
                :to="{ name: 'admin.expenses.show', params: { id: route.params.id } }" 
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
                                <label for="title" class="form-label">Título *</label>
                                <Field
                                    name="title"
                                    type="text"
                                    class="form-control"
                                    :class="{ 'is-invalid': errors.title }"
                                    placeholder="Ex: Conta de energia elétrica"
                                    :value="expense?.title || ''"
                                />
                                <div v-if="errors.title" class="invalid-feedback">
                                    {{ errors.title }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="expense_category_id" class="form-label">Categoria *</label>
                                <Field
                                    name="expense_category_id"
                                    as="select"
                                    class="form-select"
                                    :class="{ 'is-invalid': errors.expense_category_id }"
                                    :model-value="expense?.expense_category_id || ''"
                                >
                                    <option value="">Selecione uma categoria</option>
                                    <option 
                                        v-for="category in expenseCategories" 
                                        :key="category.id" 
                                        :value="category.id"
                                        :selected="category.id === expense?.expense_category_id"
                                    >
                                        {{ category.name }}
                                    </option>
                                </Field>
                                <div v-if="errors.expense_category_id" class="invalid-feedback">
                                    {{ errors.expense_category_id }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="amount" class="form-label">Valor (MT) *</label>
                                <Field
                                    name="amount"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="form-control"
                                    :class="{ 'is-invalid': errors.amount }"
                                    placeholder="0,00"
                                    :value="expense?.amount || ''"
                                />
                                <div v-if="errors.amount" class="invalid-feedback">
                                    {{ errors.amount }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="expense_date" class="form-label">Data da Despesa *</label>
                                <Field
                                    name="expense_date"
                                    type="date"
                                    class="form-control"
                                    :class="{ 'is-invalid': errors.expense_date }"
                                    :value="formatDateForInput(expense?.expense_date)"
                                />
                                <div v-if="errors.expense_date" class="invalid-feedback">
                                    {{ errors.expense_date }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="due_date" class="form-label">Data de Vencimento</label>
                                <Field
                                    name="due_date"
                                    type="date"
                                    class="form-control"
                                    :class="{ 'is-invalid': errors.due_date }"
                                    :value="formatDateForInput(expense?.due_date)"
                                />
                                <div v-if="errors.due_date" class="invalid-feedback">
                                    {{ errors.due_date }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="priority" class="form-label">Prioridade *</label>
                                <Field
                                    name="priority"
                                    as="select"
                                    class="form-select"
                                    :class="{ 'is-invalid': errors.priority }"
                                    :model-value="expense?.priority || ''"
                                >
                                    <option value="">Selecione a prioridade</option>
                                    <option 
                                        v-for="priority in priorities" 
                                        :key="priority.value" 
                                        :value="priority.value"
                                        :selected="priority.value === expense?.priority"
                                    >
                                        {{ priority.label }}
                                    </option>
                                </Field>
                                <div v-if="errors.priority" class="invalid-feedback">
                                    {{ errors.priority }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="vendor_name" class="form-label">Nome do Fornecedor</label>
                                <Field
                                    name="vendor_name"
                                    type="text"
                                    class="form-control"
                                    :class="{ 'is-invalid': errors.vendor_name }"
                                    placeholder="Nome da empresa ou pessoa"
                                    :value="expense?.vendor_name || ''"
                                />
                                <div v-if="errors.vendor_name" class="invalid-feedback">
                                    {{ errors.vendor_name }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="vendor_contact" class="form-label">Contato do Fornecedor</label>
                                <Field
                                    name="vendor_contact"
                                    type="text"
                                    class="form-control"
                                    :class="{ 'is-invalid': errors.vendor_contact }"
                                    placeholder="Telefone ou email"
                                    :value="expense?.vendor_contact || ''"
                                />
                                <div v-if="errors.vendor_contact" class="invalid-feedback">
                                    {{ errors.vendor_contact }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="invoice_number" class="form-label">Número da Fatura/NF</label>
                                <Field
                                    name="invoice_number"
                                    type="text"
                                    class="form-control"
                                    :class="{ 'is-invalid': errors.invoice_number }"
                                    placeholder="Ex: NF-123456"
                                    :value="expense?.invoice_number || ''"
                                />
                                <div v-if="errors.invoice_number" class="invalid-feedback">
                                    {{ errors.invoice_number }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="reference_number" class="form-label">Número de Referência</label>
                                <Field
                                    name="reference_number"
                                    type="text"
                                    class="form-control"
                                    :class="{ 'is-invalid': errors.reference_number }"
                                    placeholder="Número de referência interno"
                                    :value="expense?.reference_number || ''"
                                />
                                <div v-if="errors.reference_number" class="invalid-feedback">
                                    {{ errors.reference_number }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="payment_method" class="form-label">Método de Pagamento</label>
                                <Field
                                    name="payment_method"
                                    as="select"
                                    class="form-select"
                                    :class="{ 'is-invalid': errors.payment_method }"
                                    :model-value="expense?.payment_method || ''"
                                >
                                    <option value="">Selecione o método</option>
                                    <option 
                                        v-for="method in paymentMethods" 
                                        :key="method.value" 
                                        :value="method.value"
                                        :selected="method.value === expense?.payment_method"
                                    >
                                        {{ method.label }}
                                    </option>
                                </Field>
                                <div v-if="errors.payment_method" class="invalid-feedback">
                                    {{ errors.payment_method }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="payment_date" class="form-label">Data de Pagamento</label>
                                <Field
                                    name="payment_date"
                                    type="date"
                                    class="form-control"
                                    :class="{ 'is-invalid': errors.payment_date }"
                                    :value="formatDateForInput(expense?.payment_date)"
                                />
                                <div v-if="errors.payment_date" class="invalid-feedback">
                                    {{ errors.payment_date }}
                                </div>
                            </div>
                        </div>

                        <!-- Recurring Options -->
                        <div class="col-12">
                            <div class="mb-3">
                                <div class="form-check">
                                    <Field
                                        name="recurring"
                                        type="checkbox"
                                        class="form-check-input"
                                        :class="{ 'is-invalid': errors.recurring }"
                                        id="recurring"
                                        v-model="isRecurring"
                                        :checked="expense?.recurring || false"
                                    />
                                    <label class="form-check-label" for="recurring">
                                        Despesa Recorrente
                                    </label>
                                </div>
                                <div v-if="errors.recurring" class="invalid-feedback d-block">
                                    {{ errors.recurring }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6" v-if="isRecurring">
                            <div class="mb-3">
                                <label for="recurring_frequency" class="form-label">Frequência *</label>
                                <Field
                                    name="recurring_frequency"
                                    as="select"
                                    class="form-select"
                                    :class="{ 'is-invalid': errors.recurring_frequency }"
                                    :model-value="expense?.recurring_frequency || ''"
                                >
                                    <option value="">Selecione a frequência</option>
                                    <option 
                                        v-for="frequency in recurringFrequencies" 
                                        :key="frequency.value" 
                                        :value="frequency.value"
                                        :selected="frequency.value === expense?.recurring_frequency"
                                    >
                                        {{ frequency.label }}
                                    </option>
                                </Field>
                                <div v-if="errors.recurring_frequency" class="invalid-feedback">
                                    {{ errors.recurring_frequency }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6" v-if="isRecurring">
                            <div class="mb-3">
                                <label for="recurring_until" class="form-label">Repetir até</label>
                                <Field
                                    name="recurring_until"
                                    type="date"
                                    class="form-control"
                                    :class="{ 'is-invalid': errors.recurring_until }"
                                    :value="formatDateForInput(expense?.recurring_until)"
                                />
                                <div v-if="errors.recurring_until" class="invalid-feedback">
                                    {{ errors.recurring_until }}
                                </div>
                                <div class="form-text">
                                    Deixe em branco para repetir indefinidamente
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="mb-3">
                                <label for="description" class="form-label">Descrição</label>
                                <Field
                                    name="description"
                                    as="textarea"
                                    rows="3"
                                    class="form-control"
                                    :class="{ 'is-invalid': errors.description }"
                                    placeholder="Descrição detalhada da despesa..."
                                    :value="expense?.description || ''"
                                />
                                <div v-if="errors.description" class="invalid-feedback">
                                    {{ errors.description }}
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="mb-3">
                                <label for="notes" class="form-label">Observações</label>
                                <Field
                                    name="notes"
                                    as="textarea"
                                    rows="3"
                                    class="form-control"
                                    :class="{ 'is-invalid': errors.notes }"
                                    placeholder="Observações adicionais..."
                                    :value="expense?.notes && expense.notes !== 'undefined' ? expense.notes : ''"
                                />
                                <div v-if="errors.notes" class="invalid-feedback">
                                    {{ errors.notes }}
                                </div>
                            </div>
                        </div>

                        <!-- Anexos Existentes -->
                        <div class="col-12" v-if="expense && expense.attachments && expense.attachments.length > 0">
                            <div class="mb-3">
                                <label class="form-label">Anexos Atuais</label>
                                <div class="row g-3">
                                    <div 
                                        v-for="(attachment, index) in expense.attachments" 
                                        :key="index"
                                        class="col-md-4"
                                    >
                                        <div class="card border">
                                            <div class="card-body p-3">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <vue-feather 
                                                            :type="getFileIcon(attachment)" 
                                                            size="20" 
                                                            class="me-2 text-primary"
                                                        ></vue-feather>
                                                        <small class="text-muted">{{ getFileName(attachment) }}</small>
                                                    </div>
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-outline-danger"
                                                        @click="removeAttachment(attachment)"
                                                        :disabled="loadingAttachments"
                                                    >
                                                        <vue-feather type="trash-2" size="14"></vue-feather>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Upload de Novos Anexos -->
                        <div class="col-12">
                            <div class="mb-3">
                                <label for="attachments" class="form-label">Adicionar Novos Anexos</label>
                                <input
                                    type="file"
                                    class="form-control"
                                    id="attachments"
                                    multiple
                                    accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif"
                                    @change="handleFileUpload"
                                />
                                <div class="form-text">
                                    Formatos aceitos: PDF, DOC, DOCX, JPG, JPEG, PNG, GIF (máx. 10MB cada)
                                </div>
                                <!-- Preview dos novos arquivos -->
                                <div v-if="newAttachments.length > 0" class="mt-3">
                                    <small class="text-muted d-block mb-2">Novos arquivos a serem adicionados:</small>
                                    <div class="row g-2">
                                        <div 
                                            v-for="(file, index) in newAttachments" 
                                            :key="index"
                                            class="col-md-4"
                                        >
                                            <div class="card border border-success">
                                                <div class="card-body p-2">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <vue-feather 
                                                                :type="getFileIcon(file.name)" 
                                                                size="16" 
                                                                class="me-2 text-success"
                                                            ></vue-feather>
                                                            <small class="text-muted">{{ file.name }}</small>
                                                        </div>
                                                        <button
                                                            type="button"
                                                            class="btn btn-sm btn-outline-danger"
                                                            @click="removeNewAttachment(index)"
                                                        >
                                                            <vue-feather type="x" size="12"></vue-feather>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="d-flex justify-content-end gap-2">
                        <router-link 
                            :to="{ name: 'admin.expenses.show', params: { id: route.params.id } }" 
                            class="btn btn-outline-secondary"
                        >
                            Cancelar
                        </router-link>
                        <button 
                            type="submit" 
                            class="btn btn-primary"
                            :disabled="loading || loadingAttachments"
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
        <div v-if="expense && expense.status !== 'pending'" class="alert alert-warning mt-4">
            <vue-feather type="alert-triangle" size="16" class="me-2"></vue-feather>
            <strong>Atenção:</strong> 
            Esta despesa está com status "{{ getStatusLabel(expense.status) }}" e só pode ser editada se estiver pendente.
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
import { useToast } from '@/composables/useToast';

const { showToast } = useToast();
const route = useRoute();
const router = useRouter();

// Reactive data
const expense = ref(null);
const expenseCategories = ref([]);
const paymentMethods = ref([]);
const priorities = ref([]);
const recurringFrequencies = ref([]);
const loadingDiv = ref(false);
const loading = ref(false);
const loadingAttachments = ref(false);
const form = ref(null);
const isRecurring = ref(false);
const newAttachments = ref([]);

// Computed
const formatDateForInput = (date) => {
    if (!date) return '';
    
    // Se a data já está no formato YYYY-MM-DD, retorna como está
    if (typeof date === 'string' && date.match(/^\d{4}-\d{2}-\d{2}/)) {
        return date.split('T')[0];
    }
    
    // Caso contrário, formata a data
    try {
        const dateObj = new Date(date);
        if (isNaN(dateObj.getTime())) return '';
        return dateObj.toISOString().split('T')[0];
    } catch (error) {
        return '';
    }
};

// Validation schema
const schema = yup.object({
    title: yup.string().required('Título é obrigatório').max(255, 'Título muito longo'),
    expense_category_id: yup.number().required('Categoria é obrigatória').positive('Selecione uma categoria válida'),
    amount: yup.number().required('Valor é obrigatório').min(0.01, 'Valor deve ser maior que zero'),
    expense_date: yup.date().required('Data da despesa é obrigatória'),
    due_date: yup.date().nullable(),
    payment_date: yup.date().nullable(),
    priority: yup.string().required('Prioridade é obrigatória').oneOf(['low', 'medium', 'high', 'urgent'], 'Prioridade inválida'),
    vendor_name: yup.string().nullable().max(255, 'Nome muito longo'),
    vendor_contact: yup.string().nullable().max(255, 'Contato muito longo'),
    invoice_number: yup.string().nullable().max(100, 'Número da fatura muito longo'),
    reference_number: yup.string().nullable().max(100, 'Número de referência muito longo'),
    payment_method: yup.string().nullable().oneOf(['cash', 'bank_transfer', 'check', 'card', 'other'], 'Método de pagamento inválido'),
    recurring: yup.boolean(),
    recurring_frequency: yup.string().nullable().when('recurring', {
        is: true,
        then: yup.string().required('Frequência é obrigatória para despesas recorrentes')
    }),
    recurring_until: yup.date().nullable(),
    description: yup.string().nullable().max(1000, 'Descrição muito longa'),
    notes: yup.string().nullable().max(1000, 'Observações muito longas')
});

// Methods
const getData = async () => {
    loadingDiv.value = true;
    try {
        const response = await axios.get(`/expenses/${route.params.id}/edit`);
        
        expense.value = response.data.expense;
        expenseCategories.value = response.data.expense_categories;
        paymentMethods.value = response.data.payment_methods;
        priorities.value = response.data.priorities;
        recurringFrequencies.value = response.data.recurring_frequencies;
        
        // Check if can be edited
        if (!response.data.can_edit) {
            showToast('Esta despesa não pode ser editada pois já foi aprovada, paga ou rejeitada.', 'warning');
            router.push({ name: 'admin.expenses.show', params: { id: route.params.id } });
            return;
        }
        
        // Set recurring state
        isRecurring.value = expense.value.recurring || false;
        
        console.log('Dados carregados:', expense.value);
    } catch (error) {
        console.error('Erro ao buscar dados:', error);
        if (error.response?.data?.message) {
            showToast(error.response.data.message, 'error');
        } else {
            showToast('Erro ao carregar dados da despesa', 'error');
        }
        router.push('/admin/expenses');
    } finally {
        loadingDiv.value = false;
    }
};

const getStatusLabel = (status) => {
    const labels = {
        'pending': 'Pendente',
        'approved': 'Aprovada',
        'paid': 'Paga',
        'rejected': 'Rejeitada'
    };
    return labels[status] || status;
};

const handleFileUpload = (event) => {
    const files = Array.from(event.target.files);
    newAttachments.value = [...newAttachments.value, ...files];
};

const removeNewAttachment = (index) => {
    newAttachments.value.splice(index, 1);
};

const removeAttachment = async (attachmentPath) => {
    if (!confirm('Tem certeza que deseja remover este anexo?')) {
        return;
    }
    
    loadingAttachments.value = true;
    try {
        await axios.delete(`/expenses/${route.params.id}/attachment`, {
            data: { attachment_path: attachmentPath }
        });
        
        // Remove do array local
        expense.value.attachments = expense.value.attachments.filter(
            attachment => attachment !== attachmentPath
        );
        
        showToast('Anexo removido com sucesso!', 'success');
    } catch (error) {
        console.error('Erro ao remover anexo:', error);
        showToast('Erro ao remover anexo', 'error');
    } finally {
        loadingAttachments.value = false;
    }
};

const getFileName = (filePath) => {
    return filePath.split('/').pop();
};

const getFileIcon = (fileName) => {
    const extension = fileName.split('.').pop().toLowerCase();
    
    const iconMap = {
        'pdf': 'file-text',
        'doc': 'file-text',
        'docx': 'file-text',
        'jpg': 'image',
        'jpeg': 'image',
        'png': 'image',
        'gif': 'image'
    };
    
    return iconMap[extension] || 'file';
};

const onSubmit = async (values) => {
    loading.value = true;
    
    try {
        const formData = new FormData();
        
        // Adicionar dados do formulário
        Object.keys(values).forEach(key => {
            if (values[key] !== null && values[key] !== undefined && values[key] !== '') {
                formData.append(key, values[key]);
            }
        });
        
        // Adicionar novos anexos
        newAttachments.value.forEach((file, index) => {
            formData.append(`attachments[${index}]`, file);
        });
        
        await axios.post(`/expenses/${route.params.id}`, formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
                'X-HTTP-Method-Override': 'PUT'
            }
        });
        
        showToast('Despesa atualizada com sucesso!', 'success');
        router.push({ name: 'admin.expenses.show', params: { id: route.params.id } });
    } catch (error) {
        console.error('Erro ao atualizar despesa:', error);
        
        if (error.response?.data?.errors) {
            Object.values(error.response.data.errors).forEach(errorArray => {
                errorArray.forEach(message => showToast(message, 'error'));
            });
        } else if (error.response?.data?.message) {
            showToast(error.response.data.message, 'error');
        } else {
            showToast('Erro ao atualizar despesa', 'error');
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

/* Attachment styles */
.card.border {
    transition: all 0.2s ease;
}

.card.border:hover {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.card.border-success {
    border-color: #28a745 !important;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.775rem;
}

/* File upload area */
#attachments {
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

#attachments:focus {
    border-color: #80bdff;
    outline: 0;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

/* Form sections */
.mb-3 {
    margin-bottom: 1.5rem;
}

/* Loading states */
.spinner-border-sm {
    width: 1rem;
    height: 1rem;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .d-flex.gap-2 {
        flex-direction: column;
    }
    
    .d-flex.gap-2 .btn {
        margin-bottom: 0.5rem;
    }
}
</style>