<script setup>
import { ref, reactive, onMounted } from 'vue';
import axios from 'axios';
import VueFeather from 'vue-feather';
import { useToast } from '@/composables/useToast';
import { useFormatting } from '@/composables/useFormatting';



const { showToast } = useToast();
const { formatMoney, formatDate } = useFormatting();

// Reactive data
const loading = ref(true);
const categories = ref({ data: [], current_page: 1, last_page: 1 });
const statistics = ref({
    total_categories: 0,
    active_categories: 0,
    total_expenses: 0,
    total_amount: 0
});
const showModal = ref(false);
const showViewModal = ref(false);
const selectedCategory = ref(null);
const categoryToDelete = ref(null);
const openDropdown = ref(null);
const openDropdownId = ref(null);
const showDeleteModal = ref(false);

// Category form
const categoryForm = reactive({
    name: '',
    description: '',
    color: '#007bff',
    icon: '',
    active: true
});

// Filters
const filters = reactive({
    query: '',
    status: ''
});

// Methods
const loadCategories = async (page = 1) => {
    try {
        loading.value = true;
        
        const params = {
            page: page.toString(),
            query: filters.query,
            status: filters.status
        };

        const response = await axios.get('/expense-categories', { params });
        categories.value = response.data || { data: [], current_page: 1, last_page: 1 };
        statistics.value = response.data.statistics || {
            total_categories: 0,
            active_categories: 0,
            total_expenses: 0,
            total_amount: 0
        };
    } catch (error) {
        console.error('Erro ao carregar categorias:', error);
        showToast('Erro ao carregar categorias', 'error');
    } finally {
        loading.value = false;
    }
};



const handleSearch = (() => {
    let timeoutId;
    return () => {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => {
            loadCategories(1);
        }, 300);
    };
})();

const clearFilters = () => {
    filters.query = '';
    filters.status = '';
    loadCategories(1);
};

const changePage = (page) => {
    loadCategories(page);
};

const toggleDropdown = (categoryId) => {
    openDropdownId.value = openDropdownId.value === categoryId ? null : categoryId;
};

const closeDropdown = () => {
    openDropdownId.value = null;
};

const openCreateModal = () => {
    selectedCategory.value = null;
    // Reset form
    categoryForm.name = '';
    categoryForm.description = '';
    categoryForm.color = '#007bff';
    categoryForm.icon = '';
    categoryForm.active = true;
    showModal.value = true;
};

const editCategory = (category) => {
    selectedCategory.value = { ...category };
    // Populate form
    categoryForm.name = category.name || '';
    categoryForm.description = category.description || '';
    categoryForm.color = category.color || '#007bff';
    categoryForm.icon = category.icon || '';
    categoryForm.active = category.active !== false;
    showModal.value = true;
    closeDropdown();
};

const viewCategory = (category) => {
    selectedCategory.value = category;
    showViewModal.value = true;
    closeDropdown();
};

const closeModal = () => {
    showModal.value = false;
    selectedCategory.value = null;
    // Reset form
    categoryForm.name = '';
    categoryForm.description = '';
    categoryForm.color = '#007bff';
    categoryForm.icon = '';
    categoryForm.active = true;
};

const closeViewModal = () => {
    showViewModal.value = false;
    selectedCategory.value = null;
};

const saveCategory = async () => {
    try {
        loading.value = true;
        
        const url = selectedCategory.value 
            ? `/expense-categories/${selectedCategory.value.id}` 
            : '/expense-categories';
        const method = selectedCategory.value ? 'put' : 'post';
        
        const response = await axios[method](url, categoryForm);
        
        showToast(
            response.data.message || 
            (selectedCategory.value ? 'Categoria atualizada com sucesso' : 'Categoria criada com sucesso'), 
            'success'
        );
        
        closeModal();
        loadCategories(categories.value.current_page);
    } catch (error) {
        console.error('Erro ao salvar categoria:', error);
        showToast(error.response?.data?.message || 'Erro ao salvar categoria', 'error');
    } finally {
        loading.value = false;
    }
};

const toggleCategoryStatus = async (category) => {
    try {
        closeDropdown();
        const response = await axios.patch(`/expense-categories/${category.id}/toggle-status`);
        showToast(response.data.message || 'Status alterado com sucesso', 'success');
        loadCategories(categories.value.current_page);
    } catch (error) {
        console.error('Erro ao alterar status:', error);
        showToast(error.response?.data?.message || 'Erro ao alterar status', 'error');
    }
};

const confirmDelete = (category) => {
    if (category.total_expenses > 0) {
        showToast('Não é possível excluir uma categoria que possui despesas associadas', 'error');
        return;
    }
    categoryToDelete.value = category;
    showDeleteModal.value = true;
    closeDropdown();
};

const prepareCategoryForDelete = (category) => {
    categoryToDelete.value = category;
    showDeleteModal.value = true;
};

const cancelDelete = () => {
    showDeleteModal.value = false;
    categoryToDelete.value = null;
};

const deleteCategory = async () => {
    try {
        const response = await axios.delete(`/expense-categories/${categoryToDelete.value.id}`);
        showToast(response.data.message || 'Categoria deletada com sucesso', 'success');
        loadCategories();
        cancelDelete();
    } catch (error) {
        console.error('Erro ao excluir categoria:', error);
        showToast(error.response?.data?.message || 'Erro ao excluir categoria', 'error');
    }
};

// Click outside directive
const vClickOutside = {
    beforeMount(el, binding) {
        el.clickOutsideEvent = (event) => {
            if (!(el === event.target || el.contains(event.target))) {
                binding.value();
            }
        };
        document.addEventListener('click', el.clickOutsideEvent);
    },
    unmounted(el) {
        document.removeEventListener('click', el.clickOutsideEvent);
    }
};

// Lifecycle
onMounted(() => {
    loadCategories();
});
</script>

<template>
    <div>
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">Categorias de Despesas</h1>
                <p class="text-muted">Gerencie as categorias para organização das despesas do condomínio</p>
            </div>
            <button 
                @click="openCreateModal" 
                class="btn btn-primary"
            >
                <vue-feather type="plus" size="16" class="me-2"></vue-feather>
                Nova Categoria
            </button>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-primary h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title text-primary mb-1">Total Categorias</h6>
                                <h3 class="card-text text-primary mb-0">{{ statistics.total_categories || 0 }}</h3>
                            </div>
                            <div class="align-self-center">
                                <vue-feather type="tag" size="32" class="text-primary"></vue-feather>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-success h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title text-success mb-1">Categorias Ativas</h6>
                                <h3 class="card-text text-success mb-0">{{ statistics.active_categories || 0 }}</h3>
                            </div>
                            <div class="align-self-center">
                                <vue-feather type="check-circle" size="32" class="text-success"></vue-feather>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-warning h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title text-warning mb-1">Total Despesas</h6>
                                <h3 class="card-text text-warning mb-0">{{ statistics.total_expenses || 0 }}</h3>
                            </div>
                            <div class="align-self-center">
                                <vue-feather type="credit-card" size="32" class="text-warning"></vue-feather>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-info h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title text-info mb-1">Valor Total</h6>
                                <h3 class="card-text text-info mb-0">{{ formatMoney(statistics.total_amount || 0) }}</h3>
                            </div>
                            <div class="align-self-center">
                                <vue-feather type="dollar-sign" size="32" class="text-info"></vue-feather>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="searchQuery" class="form-label">Buscar</label>
                        <input
                            id="searchQuery"
                            v-model="filters.query"
                            @input="handleSearch"
                            type="text"
                            placeholder="Nome ou descrição..."
                            class="form-control"
                        />
                    </div>

                    <div class="col-md-3">
                        <label for="statusFilter" class="form-label">Status</label>
                        <select
                            id="statusFilter"
                            v-model="filters.status"
                            @change="loadCategories"
                            class="form-select"
                        >
                            <option value="">Todos</option>
                            <option value="active">Ativos</option>
                            <option value="inactive">Inativos</option>
                        </select>
                    </div>

                    <div class="col-md-5 d-flex align-items-end">
                        <button
                            @click="clearFilters"
                            class="btn btn-outline-secondary"
                        >
                            <vue-feather type="x" size="16" class="me-2"></vue-feather>
                            Limpar Filtros
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Carregando...</span>
            </div>
        </div>

        <!-- Categories Table -->
        <div v-else-if="categories.data && categories.data.length > 0" class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Categoria</th>
                                <th>Status</th>
                                <th>Total Despesas</th>
                                <th>Valor Total</th>
                                <th>Criado em</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="category in categories.data" :key="category.id">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div 
                                            class="rounded-circle me-3 d-flex align-items-center justify-content-center"
                                            style="width: 40px; height: 40px;"
                                            :style="{ backgroundColor: category.color || '#6c757d' }"
                                        >
                                            <vue-feather 
                                                v-if="category.icon" 
                                                :type="category.icon" 
                                                size="20" 
                                                class="text-white"
                                            ></vue-feather>
                                            <vue-feather 
                                                v-else 
                                                type="tag" 
                                                size="20" 
                                                class="text-white"
                                            ></vue-feather>
                                        </div>
                                        <div>
                                            <div class="fw-medium">{{ category.name }}</div>
                                            <div v-if="category.description" class="text-muted small">
                                                {{ category.description.substring(0, 50) }}{{ category.description.length > 50 ? '...' : '' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span 
                                        class="badge"
                                        :class="category.active ? 'bg-success' : 'bg-secondary'"
                                    >
                                        {{ category.active ? 'Ativa' : 'Inativa' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="fw-medium">{{ category.total_expenses || 0 }}</span>
                                </td>
                                <td>
                                    <span class="fw-medium text-success">{{ formatMoney(category.total_amount || 0) }}</span>
                                </td>
                                <td>
                                    <span class="text-muted">{{ formatDate(category.created_at) }}</span>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button 
                                            @click="toggleDropdown(category.id)"
                                            class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                            type="button"
                                            data-bs-toggle="dropdown"
                                            aria-expanded="false"
                                        >
                                            <vue-feather type="more-vertical" size="16"></vue-feather>
                                        </button>
                                        <ul 
                                            v-if="openDropdownId === category.id"
                                            class="dropdown-menu dropdown-menu-end show"
                                            style="position: absolute; z-index: 1000;"
                                        >
                                            <li>
                                                <a @click="viewCategory(category)" class="dropdown-item" href="#" @click.prevent>
                                                    <vue-feather type="eye" size="16" class="me-2"></vue-feather>
                                                    Ver Detalhes
                                                </a>
                                            </li>
                                            <li>
                                                <a @click="editCategory(category)" class="dropdown-item" href="#" @click.prevent>
                                                    <vue-feather type="edit-2" size="16" class="me-2"></vue-feather>
                                                    Editar
                                                </a>
                                            </li>
                                            <li>
                                                <a @click="toggleCategoryStatus(category)" class="dropdown-item" href="#" @click.prevent>
                                                    <vue-feather 
                                                        :type="category.active ? 'eye-off' : 'check-circle'" 
                                                        size="16" 
                                                        class="me-2"
                                                    ></vue-feather>
                                                    {{ category.active ? 'Desativar' : 'Ativar' }}
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a 
                                                    @click="confirmDelete(category)" 
                                                    class="dropdown-item text-danger" 
                                                    href="#" 
                                                    @click.prevent
                                                    :class="{ 'disabled': category.total_expenses > 0 }"
                                                >
                                                    <vue-feather type="trash-2" size="16" class="me-2"></vue-feather>
                                                    Excluir
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="categories.last_page > 1" class="d-flex justify-content-center mt-4">
                    <nav aria-label="Navegação de páginas">
                        <ul class="pagination">
                            <li class="page-item" :class="{ disabled: categories.current_page <= 1 }">
                                <button 
                                    @click="changePage(categories.current_page - 1)"
                                    class="page-link"
                                    :disabled="categories.current_page <= 1"
                                >
                                    Anterior
                                </button>
                            </li>
                            <li class="page-item active">
                                <span class="page-link">
                                    {{ categories.current_page }} de {{ categories.last_page }}
                                </span>
                            </li>
                            <li class="page-item" :class="{ disabled: categories.current_page >= categories.last_page }">
                                <button 
                                    @click="changePage(categories.current_page + 1)"
                                    class="page-link"
                                    :disabled="categories.current_page >= categories.last_page"
                                >
                                    Próxima
                                </button>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else class="card">
            <div class="card-body text-center py-5">
                <vue-feather type="tag" size="64" class="text-muted mb-3"></vue-feather>
                <h5 class="text-muted">Nenhuma categoria encontrada</h5>
                <p class="text-muted mb-4">
                    {{ filters.query || filters.status ? 'Tente ajustar os filtros de busca' : '' }}
                </p>
                <button
                    v-if="!filters.query && !filters.status"
                    @click="openCreateModal"
                    class="btn btn-primary"
                >
                    <vue-feather type="plus" size="16" class="me-2"></vue-feather>
                    Criar Primeira Categoria
                </button>
            </div>
        </div>



        <!-- Create/Edit Category Modal -->
        <div 
            v-if="showModal"
            class="modal fade show" 
            id="categoryModal" 
            tabindex="-1" 
            aria-labelledby="categoryModalLabel" 
            aria-hidden="true"
            style="display: block; background-color: rgba(0,0,0,0.5);"
        >
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="categoryModalLabel">
                            <vue-feather :type="selectedCategory ? 'edit-2' : 'plus'" size="20" class="me-2"></vue-feather>
                            {{ selectedCategory ? 'Editar Categoria' : 'Nova Categoria' }}
                        </h5>
                        <button type="button" @click="closeModal" class="btn-close" aria-label="Close"></button>
                    </div>
                    <form @submit.prevent="saveCategory">
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="categoryName" class="form-label">Nome da Categoria *</label>
                                        <input
                                            id="categoryName"
                                            v-model="categoryForm.name"
                                            type="text"
                                            class="form-control"
                                            required
                                            placeholder="Ex: Manutenção, Limpeza, Segurança..."
                                        />
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="categoryColor" class="form-label">Cor</label>
                                        <input
                                            id="categoryColor"
                                            v-model="categoryForm.color"
                                            type="color"
                                            class="form-control form-control-color"
                                            title="Escolha a cor da categoria"
                                        />
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="categoryIcon" class="form-label">Ícone</label>
                                        <select
                                            id="categoryIcon"
                                            v-model="categoryForm.icon"
                                            class="form-select"
                                        >
                                            <option value="">Selecione um ícone</option>
                                            <option value="home">🏠 Casa/Moradia</option>
                                            <option value="tool">🔧 Manutenção</option>
                                            <option value="droplet">💧 Água</option>
                                            <option value="zap">⚡ Energia</option>
                                            <option value="shield">🛡️ Segurança</option>
                                            <option value="trash-2">🗑️ Limpeza</option>
                                            <option value="users">👥 Pessoal</option>
                                            <option value="wifi">📡 Internet</option>
                                            <option value="phone">📞 Telefone</option>
                                            <option value="car">🚗 Transporte</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Status</label>
                                        <div class="form-check form-switch">
                                            <input
                                                id="categoryActive"
                                                v-model="categoryForm.active"
                                                class="form-check-input"
                                                type="checkbox"
                                                role="switch"
                                            />
                                            <label class="form-check-label" for="categoryActive">
                                                {{ categoryForm.active ? 'Ativa' : 'Inativa' }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="categoryDescription" class="form-label">Descrição</label>
                                <textarea
                                    id="categoryDescription"
                                    v-model="categoryForm.description"
                                    class="form-control"
                                    rows="3"
                                    placeholder="Descreva o tipo de despesas que pertencem a esta categoria..."
                                ></textarea>
                            </div>

                            <!-- Preview da categoria -->
                            <div v-if="categoryForm.name" class="mb-3">
                                <label class="form-label">Prévia</label>
                                <div class="d-flex align-items-center p-3 border rounded">
                                    <div 
                                        class="rounded-circle me-3 d-flex align-items-center justify-content-center"
                                        style="width: 40px; height: 40px;"
                                        :style="{ backgroundColor: categoryForm.color || '#6c757d' }"
                                    >
                                        <vue-feather 
                                            v-if="categoryForm.icon" 
                                            :type="categoryForm.icon" 
                                            size="20" 
                                            class="text-white"
                                        ></vue-feather>
                                        <vue-feather 
                                            v-else 
                                            type="tag" 
                                            size="20" 
                                            class="text-white"
                                        ></vue-feather>
                                    </div>
                                    <div>
                                        <div class="fw-medium">{{ categoryForm.name }}</div>
                                        <div v-if="categoryForm.description" class="text-muted small">
                                            {{ categoryForm.description.substring(0, 50) }}{{ categoryForm.description.length > 50 ? '...' : '' }}
                                        </div>
                                        <span 
                                            class="badge mt-1"
                                            :class="categoryForm.active ? 'bg-success' : 'bg-secondary'"
                                        >
                                            {{ categoryForm.active ? 'Ativa' : 'Inativa' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" @click="closeModal" class="btn btn-secondary">
                                Cancelar
                            </button>
                            <button type="submit" class="btn btn-primary" :disabled="loading || !categoryForm.name">
                                <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                                <vue-feather :type="selectedCategory ? 'save' : 'plus'" size="16" class="me-2"></vue-feather>
                                {{ selectedCategory ? 'Atualizar' : 'Criar' }} Categoria
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- View Category Modal -->
        <div 
            v-if="showViewModal && selectedCategory"
            class="modal fade show" 
            id="viewCategoryModal" 
            tabindex="-1" 
            aria-labelledby="viewCategoryModalLabel" 
            aria-hidden="true"
            style="display: block; background-color: rgba(0,0,0,0.5);"
        >
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="viewCategoryModalLabel">
                            <vue-feather type="eye" size="20" class="me-2"></vue-feather>
                            Detalhes da Categoria
                        </h5>
                        <button type="button" @click="closeViewModal" class="btn-close" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12">
                                <!-- Category Header -->
                                <div class="d-flex align-items-center mb-4 p-3 bg-light rounded">
                                    <div 
                                        class="rounded-circle me-3 d-flex align-items-center justify-content-center"
                                        style="width: 60px; height: 60px;"
                                        :style="{ backgroundColor: selectedCategory.color || '#6c757d' }"
                                    >
                                        <vue-feather 
                                            v-if="selectedCategory.icon" 
                                            :type="selectedCategory.icon" 
                                            size="30" 
                                            class="text-white"
                                        ></vue-feather>
                                        <vue-feather 
                                            v-else 
                                            type="tag" 
                                            size="30" 
                                            class="text-white"
                                        ></vue-feather>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h4 class="mb-1">{{ selectedCategory.name }}</h4>
                                        <span 
                                            class="badge"
                                            :class="selectedCategory.active ? 'bg-success' : 'bg-secondary'"
                                        >
                                            {{ selectedCategory.active ? 'Ativa' : 'Inativa' }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Category Details -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6>Informações</h6>
                                        <table class="table table-sm table-borderless">
                                            <tr>
                                                <td class="text-muted" width="40%">Nome:</td>
                                                <td>{{ selectedCategory.name }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted">Status:</td>
                                                <td>
                                                    <span 
                                                        class="badge"
                                                        :class="selectedCategory.active ? 'bg-success' : 'bg-secondary'"
                                                    >
                                                        {{ selectedCategory.active ? 'Ativa' : 'Inativa' }}
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted">Cor:</td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div 
                                                            class="rounded me-2"
                                                            style="width: 20px; height: 20px; border: 1px solid #dee2e6;"
                                                            :style="{ backgroundColor: selectedCategory.color || '#6c757d' }"
                                                        ></div>
                                                        {{ selectedCategory.color || '#6c757d' }}
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted">Criada em:</td>
                                                <td>{{ formatDate(selectedCategory.created_at) }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <h6>Estatísticas</h6>
                                        <table class="table table-sm table-borderless">
                                            <tr>
                                                <td class="text-muted" width="40%">Total de Despesas:</td>
                                                <td><span class="fw-medium">{{ selectedCategory.total_expenses || 0 }}</span></td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted">Valor Total:</td>
                                                <td><span class="fw-medium text-success">{{ formatMoney(selectedCategory.total_amount || 0) }}</span></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>

                                <div v-if="selectedCategory.description" class="mt-3">
                                    <h6>Descrição</h6>
                                    <p class="text-muted">{{ selectedCategory.description }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" @click="closeViewModal" class="btn btn-secondary">
                            Fechar
                        </button>
                        <button type="button" @click="editCategory(selectedCategory)" class="btn btn-primary">
                            <vue-feather type="edit-2" size="16" class="me-2"></vue-feather>
                            Editar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Modal -->
        <div 
            v-if="showDeleteModal"
            class="modal fade show" 
            id="deleteModal" 
            tabindex="-1" 
            aria-labelledby="deleteModalLabel" 
            aria-hidden="true"
            style="display: block; background-color: rgba(0,0,0,0.5);"
        >
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteModalLabel">
                            <vue-feather type="alert-triangle" size="20" class="me-2 text-danger"></vue-feather>
                            Excluir Categoria
                        </h5>
                        <button type="button" @click="cancelDelete" class="btn-close" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger" role="alert">
                            <vue-feather type="alert-triangle" size="16" class="me-2"></vue-feather>
                            <strong>Atenção! Esta ação não pode ser desfeita.</strong>
                        </div>
                        <p>Você tem certeza que deseja excluir a categoria <strong>{{ categoryToDelete?.name }}</strong>?</p>
                        <div v-if="categoryToDelete?.total_expenses > 0" class="alert alert-warning" role="alert">
                            <vue-feather type="info" size="16" class="me-2"></vue-feather>
                            Esta categoria possui <strong>{{ categoryToDelete?.total_expenses }}</strong> despesas associadas e não pode ser excluída.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" @click="cancelDelete" class="btn btn-secondary">
                            Cancelar
                        </button>
                        <button 
                            type="button" 
                            @click="deleteCategory"
                            class="btn btn-danger" 
                            :disabled="categoryToDelete?.total_expenses > 0"
                        >
                            <vue-feather type="trash-2" size="16" class="me-2"></vue-feather>
                            Excluir
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>



<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>