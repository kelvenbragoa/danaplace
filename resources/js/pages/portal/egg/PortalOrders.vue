<script setup>

import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';
import { useToastr } from '../../../toastr';
import { Bootstrap4Pagination } from 'laravel-vue-pagination';
import VueFeather from 'vue-feather';

const router = useRouter();
const toastr = useToastr();

const customer = ref(null);
const orders = ref({ data: [], total: 0 });
const categories = ref([]);
const loading = ref(true);
const loadingOrder = ref(false);
const showForm = ref(false);

const today = new Date().toISOString().split('T')[0];
const form = ref({
    order_date: today,
    expected_delivery_date: '',
    category_id: '',
    quantity_dozens: 1,
    observations: '',
});

const statusLabels = {
    pending: 'Pendente',
    approved: 'Aprovado',
    picked: 'Separado',
    shipped: 'Expedido',
    canceled: 'Cancelado',
};

const statusBadgeClass = {
    pending: 'badge-warning',
    approved: 'badge-info',
    picked: 'badge-primary',
    shipped: 'badge-success',
    canceled: 'badge-secondary',
};

const pendingCount = computed(() => {
    return (orders.value.data || []).filter((order) =>
        ['pending', 'approved', 'picked'].includes(order.status)
    ).length;
});

const shippedCount = computed(() => {
    return (orders.value.data || []).filter((order) => order.status === 'shipped').length;
});

const loadSession = async () => {
    try {
        const [meResponse, ordersResponse, categoriesResponse] = await Promise.all([
            axios.get('/portal/ovos/me'),
            axios.get('/portal/ovos/orders'),
            axios.get('/portal/ovos/categories'),
        ]);
        customer.value = meResponse.data;
        orders.value = ordersResponse.data;
        categories.value = categoriesResponse.data;
    } catch {
        router.push({ path: '/portal/pedidos-ovos' });
    } finally {
        loading.value = false;
    }
};

const submitOrder = () => {
    loadingOrder.value = true;

    axios.post('/portal/ovos/orders', {
        ...form.value,
        quantity_dozens: Number(form.value.quantity_dozens),
    }).then(() => {
        toastr.success('Pedido submetido com sucesso');
        showForm.value = false;
        form.value = {
            order_date: today,
            expected_delivery_date: '',
            category_id: '',
            quantity_dozens: 1,
            observations: '',
        };
        return axios.get('/portal/ovos/orders');
    }).then((response) => {
        orders.value = response.data;
    }).catch((error) => {
        toastr.error(error.response?.data?.message || 'Erro ao submeter pedido');
    }).finally(() => {
        loadingOrder.value = false;
    });
};

const logout = () => {
    axios.post('/portal/ovos/logout').finally(() => {
        router.push({ path: '/portal/pedidos-ovos' });
    });
};

const getOrders = (page = 1) => {
    axios.get(`/portal/ovos/orders?page=${page}`).then((response) => {
        orders.value = response.data;
    });
};

const formatDate = (date) => {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('pt-PT');
};

onMounted(() => {
    loadSession();
});

</script>

<template>
    <div class="portal-wrapper wrapper">
        <div class="main portal-main">
            <nav class="navbar navbar-expand navbar-light navbar-bg">
                <a class="navbar-brand" href="#">
                    <img src="/files/img/sys/companylogo.png" alt="M+D InoGest" class="portal-navbar-logo" />
                </a>

                <div class="navbar-collapse collapse">
                    <ul class="navbar-nav navbar-align ms-auto">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-none d-sm-inline-block" href="#" data-toggle="dropdown">
                                <img src="/files/img/sys/logoinogesticon.png" class="avatar img-fluid rounded mr-2" :alt="customer?.name" />
                                <span class="text-dark">{{ customer?.name }}</span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right">
                                <div class="dropdown-item-text">
                                    <small class="text-muted">Código portal</small>
                                    <div><code>{{ customer?.portal_code }}</code></div>
                                </div>
                                <div class="dropdown-divider"></div>
                                <button type="button" class="btn btn-outline-primary mx-3 mt-2 d-block w-auto" @click="logout">
                                    Sair
                                </button>
                            </div>
                        </li>
                    </ul>
                </div>
            </nav>

            <main class="content">
                <div class="container-fluid p-0">
                    <div v-if="loading" class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-center">
                                <div class="spinner-border" role="status"><span class="sr-only"></span></div>
                            </div>
                            <br>
                            <div class="d-flex justify-content-center">Carregando Dados...</div>
                        </div>
                    </div>

                    <div v-else>
                        <h1 class="h3 mb-3">Meus Pedidos</h1>

                        <div class="row mb-3">
                            <div class="col-sm-6 col-xl-3">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col mt-0">
                                                <h5 class="card-title">Total de Pedidos</h5>
                                            </div>
                                            <div class="col-auto">
                                                <div class="stat text-primary">
                                                    <vue-feather type="shopping-cart"></vue-feather>
                                                </div>
                                            </div>
                                        </div>
                                        <h1 class="mt-1 mb-3">{{ orders.total || 0 }}</h1>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-xl-3">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col mt-0">
                                                <h5 class="card-title">Em Processamento</h5>
                                            </div>
                                            <div class="col-auto">
                                                <div class="stat text-warning">
                                                    <vue-feather type="clock"></vue-feather>
                                                </div>
                                            </div>
                                        </div>
                                        <h1 class="mt-1 mb-3">{{ pendingCount }}</h1>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-xl-3">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col mt-0">
                                                <h5 class="card-title">Expedidos</h5>
                                            </div>
                                            <div class="col-auto">
                                                <div class="stat text-success">
                                                    <vue-feather type="truck"></vue-feather>
                                                </div>
                                            </div>
                                        </div>
                                        <h1 class="mt-1 mb-3">{{ shippedCount }}</h1>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-xl-3">
                                <div class="card border-info">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col mt-0">
                                                <h5 class="card-title">Categorias</h5>
                                            </div>
                                            <div class="col-auto">
                                                <div class="stat text-info">
                                                    <vue-feather type="layers"></vue-feather>
                                                </div>
                                            </div>
                                        </div>
                                        <h1 class="mt-1 mb-3">{{ categories.length }}</h1>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row" v-if="showForm">
                            <div class="col-12">
                                <div class="card border-primary mb-3">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">
                                            <vue-feather type="plus-circle" class="align-middle me-1"></vue-feather>
                                            Novo Pedido
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <form @submit.prevent="submitOrder">
                                            <div class="row">
                                                <div class="mb-3 col-md-4">
                                                    <label class="form-label">Data do Pedido</label>
                                                    <input v-model="form.order_date" type="date" class="form-control" required />
                                                </div>
                                                <div class="mb-3 col-md-4">
                                                    <label class="form-label">Entrega Prevista</label>
                                                    <input v-model="form.expected_delivery_date" type="date" class="form-control" />
                                                </div>
                                                <div class="mb-3 col-md-4">
                                                    <label class="form-label">Categoria</label>
                                                    <select v-model="form.category_id" class="form-control" required>
                                                        <option value="">Selecione...</option>
                                                        <option v-for="category in categories" :key="category.id" :value="category.id">
                                                            {{ category.name }}
                                                        </option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="mb-3 col-md-4">
                                                    <label class="form-label">Quantidade (dúzias)</label>
                                                    <input v-model="form.quantity_dozens" type="number" min="1" class="form-control" required />
                                                </div>
                                                <div class="mb-3 col-md-8">
                                                    <label class="form-label">Observações</label>
                                                    <textarea v-model="form.observations" class="form-control" rows="2" placeholder="Instruções ou notas adicionais..."></textarea>
                                                </div>
                                            </div>
                                            <button type="submit" class="btn btn-primary" :disabled="loadingOrder">
                                                <div v-if="loadingOrder" class="spinner-border spinner-border-sm" role="status"></div>
                                                <span v-else>Submeter Pedido</span>
                                            </button>
                                            <button type="button" class="btn btn-secondary ms-2" @click="showForm = false">Cancelar</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Histórico de pedidos. {{ orders.total || 0 }} registros encontrados.</h5>

                                        <button type="button" class="btn btn-pill btn-primary mt-3" @click="showForm = !showForm">
                                            <vue-feather type="plus"></vue-feather>
                                            {{ showForm ? 'Fechar formulário' : 'Novo Pedido' }}
                                        </button>
                                    </div>

                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Data</th>
                                                        <th>Entrega Prevista</th>
                                                        <th>Categoria</th>
                                                        <th>Dúzias</th>
                                                        <th>Estado</th>
                                                        <th>Observações</th>
                                                    </tr>
                                                </thead>
                                                <tbody v-if="orders.data?.length">
                                                    <tr v-for="(order, index) in orders.data" :key="order.id">
                                                        <td>#{{ index + 1 }}</td>
                                                        <td>{{ formatDate(order.order_date) }}</td>
                                                        <td>{{ formatDate(order.expected_delivery_date) }}</td>
                                                        <td>{{ order.category?.name || '—' }}</td>
                                                        <td>{{ order.quantity_dozens }}</td>
                                                        <td>
                                                            <span class="badge" :class="statusBadgeClass[order.status] || 'badge-light'">
                                                                {{ statusLabels[order.status] || order.status }}
                                                            </span>
                                                        </td>
                                                        <td>{{ order.observations || '—' }}</td>
                                                    </tr>
                                                </tbody>
                                                <tbody v-else>
                                                    <tr>
                                                        <td colspan="7" align="center">Nenhum pedido encontrado. Clique em "Novo Pedido" para começar.</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <Bootstrap4Pagination v-if="orders.data?.length" :data="orders" @pagination-change-page="getOrders" />
                            </div>
                        </div>
                    </div>
                </div>
            </main>

            <footer class="footer">
                <div class="container-fluid">
                    <div class="row text-muted">
                        <div class="col-6 text-left">
                            <p class="mb-0">
                                <a href="#" class="text-muted"><strong>M+D - InoGest</strong></a> &copy; {{ new Date().getFullYear() }}
                            </p>
                        </div>
                        <div class="col-6 text-right">
                            <ul class="list-inline">
                                <li class="list-inline-item">
                                    <span class="text-muted">Portal de Pedidos — Ovos</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>
</template>

<style scoped>
.portal-main {
    margin-left: 0 !important;
    width: 100%;
    min-height: 100vh;
}

.portal-navbar-logo {
    height: 36px;
    width: auto;
}

.stat {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 2.5rem;
    height: 2.5rem;
}
</style>
