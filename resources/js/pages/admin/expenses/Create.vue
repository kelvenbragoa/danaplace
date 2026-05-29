<template>
    <div>
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">Registrar Nova Despesa</h1>
                <p class="text-muted">Cadastre uma nova despesa do condomínio</p>
            </div>
            <router-link 
                to="/admin/expenses" 
                class="btn btn-outline-secondary"
            >
                <vue-feather type="arrow-left" size="16" class="me-2"></vue-feather>
                Voltar
            </router-link>
        </div>

        <!-- Form -->
        <div class="card">
            <div class="card-body">
                <Form @submit="onSubmit" :validation-schema="schema" v-slot="{ errors }">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="expense_category_id" class="form-label">Categoria *</label>
                                <Field
                                    name="expense_category_id"
                                    as="select"
                                    class="form-select"
                                    :class="{ 'is-invalid': errors.expense_category_id }"
                                >
                                    <option value="">Selecione uma categoria</option>
                                    <option 
                                        v-for="category in categories" 
                                        :key="category.id" 
                                        :value="category.id"
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
                                <label for="priority" class="form-label">Prioridade *</label>
                                <Field
                                    name="priority"
                                    as="select"
                                    class="form-select"
                                    :class="{ 'is-invalid': errors.priority }"
                                >
                                    <option value="">Selecione a prioridade</option>
                                    <option value="low">Baixa</option>
                                    <option value="medium">Média</option>
                                    <option value="high">Alta</option>
                                    <option value="urgent">Urgente</option>
                                </Field>
                                <div v-if="errors.priority" class="invalid-feedback">
                                    {{ errors.priority }}
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="mb-3">
                                <label for="title" class="form-label">Título da Despesa *</label>
                                <Field
                                    name="title"
                                    type="text"
                                    class="form-control"
                                    :class="{ 'is-invalid': errors.title }"
                                    placeholder="Ex: Manutenção do elevador, Limpeza das áreas comuns..."
                                />
                                <div v-if="errors.title" class="invalid-feedback">
                                    {{ errors.title }}
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="mb-3">
                                <label for="description" class="form-label">Descrição</label>
                                <Field
                                    name="description"
                                    as="textarea"
                                    rows="4"
                                    class="form-control"
                                    :class="{ 'is-invalid': errors.description }"
                                    placeholder="Descreva detalhadamente a despesa..."
                                />
                                <div v-if="errors.description" class="invalid-feedback">
                                    {{ errors.description }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="amount" class="form-label">Valor *</label>
                                <Field
                                    name="amount"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="form-control"
                                    :class="{ 'is-invalid': errors.amount }"
                                    placeholder="0,00"
                                />
                                <div v-if="errors.amount" class="invalid-feedback">
                                    {{ errors.amount }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="due_date" class="form-label">Data de Vencimento *</label>
                                <Field
                                    name="due_date"
                                    type="date"
                                    class="form-control"
                                    :class="{ 'is-invalid': errors.due_date }"
                                    :min="today"
                                />
                                <div v-if="errors.due_date" class="invalid-feedback">
                                    {{ errors.due_date }}
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
                                    placeholder="Ex: Empresa ABC Ltda"
                                />
                                <div v-if="errors.vendor_name" class="invalid-feedback">
                                    {{ errors.vendor_name }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="vendor_document" class="form-label">NUIT do Fornecedor</label>
                                <Field
                                    name="vendor_document"
                                    type="text"
                                    class="form-control"
                                    :class="{ 'is-invalid': errors.vendor_document }"
                                    placeholder="00.000.000/0000-00"
                                />
                                <div v-if="errors.vendor_document" class="invalid-feedback">
                                    {{ errors.vendor_document }}
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
                                    placeholder="Ex: NF-001234"
                                />
                                <div v-if="errors.invoice_number" class="invalid-feedback">
                                    {{ errors.invoice_number }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="recurrence_type" class="form-label">Recorrência</label>
                                <Field
                                    name="recurrence_type"
                                    as="select"
                                    class="form-select"
                                    :class="{ 'is-invalid': errors.recurrence_type }"
                                    @change="handleRecurrenceChange"
                                >
                                    <option value="">Despesa única</option>
                                    <option value="monthly">Mensal</option>
                                    <option value="quarterly">Trimestral</option>
                                    <option value="annually">Anual</option>
                                </Field>
                                <div v-if="errors.recurrence_type" class="invalid-feedback">
                                    {{ errors.recurrence_type }}
                                </div>
                            </div>
                        </div>

                        <div class="col-12" v-if="showRecurrenceEnd">
                            <div class="mb-3">
                                <label for="recurrence_end_date" class="form-label">Data Fim da Recorrência</label>
                                <Field
                                    name="recurrence_end_date"
                                    type="date"
                                    class="form-control"
                                    :class="{ 'is-invalid': errors.recurrence_end_date }"
                                    :min="today"
                                />
                                <div v-if="errors.recurrence_end_date" class="invalid-feedback">
                                    {{ errors.recurrence_end_date }}
                                </div>
                                <div class="form-text">
                                    Deixe em branco para recorrência indefinida
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="mb-3">
                                <label for="attachments" class="form-label">Anexos</label>
                                <input
                                    ref="fileInput"
                                    type="file"
                                    multiple
                                    accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx"
                                    class="form-control"
                                    @change="handleFileChange"
                                />
                                <div class="form-text">
                                    Formatos aceitos: PDF, JPG, PNG, DOC, DOCX, XLS, XLSX (máx. 5MB cada)
                                </div>
                            </div>

                            <!-- File Preview -->
                            <div v-if="selectedFiles.length > 0" class="mb-3">
                                <h6>Arquivos Selecionados:</h6>
                                <div class="row">
                                    <div 
                                        v-for="(file, index) in selectedFiles" 
                                        :key="index" 
                                        class="col-md-6 mb-2"
                                    >
                                        <div class="d-flex align-items-center justify-content-between p-2 bg-light rounded">
                                            <div class="d-flex align-items-center">
                                                <vue-feather :type="getFileIcon(file.name)" size="16" class="me-2"></vue-feather>
                                                <small>{{ file.name }}</small>
                                            </div>
                                            <button
                                                type="button"
                                                @click="removeFile(index)"
                                                class="btn btn-sm btn-outline-danger"
                                            >
                                                <vue-feather type="x" size="12"></vue-feather>
                                            </button>
                                        </div>
                                    </div>
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
                                    placeholder="Observações adicionais sobre a despesa..."
                                />
                                <div v-if="errors.notes" class="invalid-feedback">
                                    {{ errors.notes }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="d-flex justify-content-end gap-2">
                        <router-link 
                            to="/admin/expenses" 
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
                            Registrar Despesa
                        </button>
                    </div>
                </Form>
            </div>
        </div>

        <!-- Info Card -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <vue-feather type="info" size="16" class="me-2"></vue-feather>
                    Informações Importantes
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <h6 class="text-primary">Prioridades</h6>
                        <p class="small text-muted mb-3">
                            <strong>Urgente:</strong> Requer ação imediata<br>
                            <strong>Alta:</strong> Importante, mas pode aguardar alguns dias<br>
                            <strong>Média:</strong> Situação normal<br>
                            <strong>Baixa:</strong> Pode ser programada
                        </p>
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-warning">Recorrência</h6>
                        <p class="small text-muted mb-3">
                            Despesas recorrentes são automaticamente criadas nos intervalos definidos. 
                            Útil para contas mensais, trimestrais ou anuais.
                        </p>
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-info">Anexos</h6>
                        <p class="small text-muted mb-3">
                            Anexe faturas, orçamentos e comprovantes para facilitar 
                            a aprovação e auditoria das despesas.
                        </p>
                    </div>
                </div>
                <div class="alert alert-info">
                    <vue-feather type="info" size="16" class="me-2"></vue-feather>
                    <strong>Lembrete:</strong> Todas as despesas precisam ser aprovadas antes do pagamento. 
                    Despesas urgentes são priorizadas no processo de aprovação.
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
const categories = ref([]);
const loading = ref(false);
const selectedFiles = ref([]);
const fileInput = ref(null);
const showRecurrenceEnd = ref(false);

// Computed
const today = computed(() => new Date().toISOString().split('T')[0]);

// Validation Schema
const schema = yup.object({
    expense_category_id: yup.string().required('A categoria é obrigatória'),
    priority: yup.string().required('A prioridade é obrigatória'),
    title: yup.string().required('O título é obrigatório').min(3, 'O título deve ter pelo menos 3 caracteres'),
    description: yup.string(),
    amount: yup.number().required('O valor é obrigatório').min(0.01, 'O valor deve ser maior que zero'),
    due_date: yup.date().required('A data de vencimento é obrigatória').min(new Date(), 'A data deve ser futura'),
    vendor_name: yup.string(),
    vendor_document: yup.string(),
    invoice_number: yup.string(),
    recurrence_type: yup.string(),
    recurrence_end_date: yup.date().when('recurrence_type', {
        is: (val) => val && val !== '',
        then: (schema) => schema.min(new Date(), 'A data fim deve ser futura'),
        otherwise: (schema) => schema.nullable()
    }),
    notes: yup.string()
});

// Methods
const getCategories = async () => {
    try {
        const response = await axios.get('/expense-categories');
        categories.value = response.data.data || [];
    } catch (error) {
        console.error('Erro ao buscar categorias:', error);
        showToast('Erro ao carregar categorias', 'error');
        categories.value = [];
    }
};

const handleRecurrenceChange = (event) => {
    const value = event.target.value;
    showRecurrenceEnd.value = value && value !== '';
};

const handleFileChange = (event) => {
    const files = Array.from(event.target.files);
    
    // Validate file sizes
    const maxSize = 5 * 1024 * 1024; // 5MB
    const validFiles = files.filter(file => {
        if (file.size > maxSize) {
            showToast(`Arquivo ${file.name} é muito grande (máx. 5MB)`, 'error');
            return false;
        }
        return true;
    });
    
    selectedFiles.value = [...selectedFiles.value, ...validFiles];
    
    // Clear input
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

const removeFile = (index) => {
    selectedFiles.value.splice(index, 1);
};

const getFileIcon = (filename) => {
    const extension = filename.split('.').pop().toLowerCase();
    const icons = {
        pdf: 'file-text',
        doc: 'file-text',
        docx: 'file-text',
        xls: 'file-text',
        xlsx: 'file-text',
        jpg: 'image',
        jpeg: 'image',
        png: 'image',
        gif: 'image'
    };
    return icons[extension] || 'file';
};

const onSubmit = async (values) => {
    loading.value = true;
    
    try {
        const formData = new FormData();
        
        // Add form fields
        Object.keys(values).forEach(key => {
            if (values[key] !== null && values[key] !== '') {
                formData.append(key, values[key]);
            }
        });
        
        // Add files
        selectedFiles.value.forEach((file, index) => {
            formData.append(`attachments[${index}]`, file);
        });
        
        await axios.post('/expenses', formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });
        
        showToast('Despesa registrada com sucesso!', 'success');
        router.push({ name: 'admin.expenses.index' });
        
    } catch (error) {
        console.error(error);
        if (error.response?.data?.errors) {
            Object.values(error.response.data.errors).forEach(errorArray => {
                errorArray.forEach(message => showToast(message, 'error'));
            });
        } else if (error.response?.data?.error) {
            showToast(error.response.data.error, 'error');
        } else {
            showToast('Erro ao registrar despesa', 'error');
        }
    } finally {
        loading.value = false;
    }
};

// Lifecycle
onMounted(() => {
    getCategories();
});
</script>

<style scoped>
.table th, .table td {
    vertical-align: middle;
}
</style>