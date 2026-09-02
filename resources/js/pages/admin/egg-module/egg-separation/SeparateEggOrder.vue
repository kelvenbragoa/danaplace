<script setup>

import { ref, onMounted, computed, watch } from 'vue';
import axios from 'axios';
import { useToastr } from '../../../../toastr';
import { useRouter } from 'vue-router';
import VueFeather from 'vue-feather';
import moment from 'moment';

const loading = ref(false);
const loadingDiv = ref(true);
const toastr = useToastr();
const router = useRouter();

const order = ref({});
const inventory = ref([]);
const categories = ref([]);
const autoCategoryId = ref('');
const allocations = ref([{ inventory_id: '', quantity: '' }]);

const eggsNeeded = computed(() => Number(order.value.quantity_dozens || 0));

const allocatedTotal = computed(() =>
    allocations.value.reduce((sum, row) => sum + (Number(row.quantity) || 0), 0)
);

const remainingToAllocate = computed(() => eggsNeeded.value - allocatedTotal.value);

const allocationValid = computed(() => {
    if (!eggsNeeded.value) return false;
    if (allocations.value.length < 1) return false;
    if (allocatedTotal.value !== eggsNeeded.value) return false;

    const ids = allocations.value.map(a => String(a.inventory_id)).filter(Boolean);
    if (ids.length !== allocations.value.length) return false;
    if (new Set(ids).size !== ids.length) return false;

    return allocations.value.every((row) => {
        const stock = inventory.value.find(i => String(i.id) === String(row.inventory_id));
        const qty = Number(row.quantity) || 0;
        return stock && qty > 0 && qty <= stock.quantity;
    });
});

const availableOptionsFor = (rowIndex) => {
    const usedElsewhere = allocations.value
        .map((row, idx) => (idx !== rowIndex ? String(row.inventory_id) : null))
        .filter(Boolean);

    return inventory.value.filter(item => !usedElsewhere.includes(String(item.id)));
};

const stockFor = (inventoryId) => inventory.value.find(i => String(i.id) === String(inventoryId));

const categoriesForAuto = computed(() => {
    const fromInventory = inventory.value.map(item => item.egg?.category).filter(Boolean);
    const map = new Map();
    fromInventory.forEach((cat) => {
        if (!map.has(cat.id)) map.set(cat.id, cat);
    });
    categories.value.forEach((cat) => {
        if (!map.has(cat.id)) map.set(cat.id, cat);
    });
    return Array.from(map.values()).sort((a, b) => String(a.name).localeCompare(String(b.name)));
});

const inventoryByAutoCategory = computed(() => {
    if (!autoCategoryId.value) return [];
    return inventory.value.filter(item =>
        String(item.egg?.category_id) === String(autoCategoryId.value)
        || String(item.egg?.category?.id) === String(autoCategoryId.value)
    );
});

const inventoryLabel = (item) => {
    const trace = item.egg?.traceability_code || 'Sem rastreio';
    const category = item.egg?.category?.name ? ` — ${item.egg.category.name}` : '';
    return `#${item.id} — ${trace}${category} — ${item.quantity} ovos disponíveis`;
};

const loadData = () => {
    const orderId = router.currentRoute.value.params.id;

    Promise.all([
        axios.get(`/admin/egg-orders/${orderId}`),
        axios.get('/admin/egg-inventory/fifo-list'),
        axios.get('/admin/egg-categories-all'),
    ]).then(([orderResponse, inventoryResponse, categoriesResponse]) => {
        if (orderResponse.data.status !== 'approved') {
            toastr.warning('Este pedido não está aprovado para separação');
            router.push({ path: '/admin/separacao-ovos' });
            return;
        }

        order.value = orderResponse.data;
        inventory.value = inventoryResponse.data;
        categories.value = categoriesResponse.data;
        autoCategoryId.value = order.value.category_id || order.value.category?.id || '';
        allocations.value = [{ inventory_id: '', quantity: eggsNeeded.value || '' }];
        loadingDiv.value = false;
    }).catch(() => {
        toastr.error('Erro ao carregar dados');
        router.push({ path: '/admin/separacao-ovos' });
    });
};

const addAllocation = () => {
    allocations.value.push({
        inventory_id: '',
        quantity: remainingToAllocate.value > 0 ? remainingToAllocate.value : '',
    });
};

const removeAllocation = (index) => {
    if (allocations.value.length === 1) {
        allocations.value = [{ inventory_id: '', quantity: '' }];
        return;
    }
    allocations.value.splice(index, 1);
};

const autoAllocateFifo = () => {
    if (!eggsNeeded.value) return;

    if (!autoCategoryId.value) {
        toastr.warning('Selecione a categoria para autoalocar');
        return;
    }

    const stocks = inventoryByAutoCategory.value;
    if (stocks.length === 0) {
        toastr.error('Não há stock disponível nesta categoria');
        return;
    }

    let remaining = eggsNeeded.value;
    const result = [];

    for (const stock of stocks) {
        if (remaining <= 0) break;
        if (stock.quantity < 1) continue;
        const take = Math.min(stock.quantity, remaining);
        result.push({ inventory_id: stock.id, quantity: take });
        remaining -= take;
    }

    if (remaining > 0) {
        const categoryName = categoriesForAuto.value.find(c => String(c.id) === String(autoCategoryId.value))?.name || 'selecionada';
        toastr.error(`Stock insuficiente na categoria "${categoryName}". Faltam ${remaining} ovos.`);
        return;
    }

    allocations.value = result;
    toastr.success(`Reservado em ${result.length} lote(s)`);
};

const confirmSeparation = () => {
    if (!allocationValid.value) {
        toastr.error('Ajuste os stocks: a soma deve igualar o pedido.');
        return;
    }

    loading.value = true;

    axios.post(`/admin/egg-orders/${order.value.id}/pick`, {
        allocations: allocations.value.map(row => ({
            inventory_id: Number(row.inventory_id),
            quantity: Number(row.quantity),
        })),
    }).then(() => {
        toastr.success('Pedido separado e stock reservado com sucesso');
        router.push({ path: '/admin/separacao-ovos' });
    }).catch((error) => {
        toastr.error(error.response?.data?.message || 'Erro ao separar pedido');
    }).finally(() => {
        loading.value = false;
    });
};

watch(() => router.currentRoute.value.params.id, () => {
    if (router.currentRoute.value.params.id) {
        loadingDiv.value = true;
        loadData();
    }
});

onMounted(() => {
    loadData();
});

</script>

<template>
    <div v-if="!loadingDiv">
        <h1 class="h3 mb-3">Separar Pedido #{{ order.id }}</h1>

        <div class="card mb-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Cliente:</strong> {{ order.customer_name }}</p>
                        <p><strong>Categoria:</strong> {{ order.category?.name || '—' }}</p>
                        <p><strong>Quantidade:</strong> {{ order.quantity_dozens }} ovos</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Data pedido:</strong> {{ order.order_date ? moment(order.order_date).format('DD-MM-YYYY') : '—' }}</p>
                        <p><strong>Entrega prevista:</strong> {{ order.expected_delivery_date ? moment(order.expected_delivery_date).format('DD-MM-YYYY') : '—' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="card-title mb-0">Reservar stock (FIFO)</h5>
                <router-link to="/admin/separacao-ovos" class="btn btn-pill btn-outline-primary btn-sm">
                    <vue-feather type="arrow-left"></vue-feather> Voltar
                </router-link>
            </div>

            <div class="card-body">
                <div v-if="inventory.length === 0" class="alert alert-warning">
                    Não existe stock disponível para reservar.
                </div>

                <div class="d-flex align-items-center flex-wrap gap-2 mb-3">
                    <select class="form-control form-control-sm" style="min-width: 180px;" v-model="autoCategoryId">
                        <option value="">Categoria (Auto Alocar)</option>
                        <option v-for="cat in categoriesForAuto" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                    </select>
                    <button type="button" class="btn btn-sm btn-outline-info" @click.prevent="autoAllocateFifo" :disabled="!autoCategoryId">
                        Auto Alocar
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-primary" @click.prevent="addAllocation">
                        + Adicionar stock
                    </button>
                </div>

                <div v-for="(row, index) in allocations" :key="index" class="row align-items-end mb-2">
                    <div class="col-md-7 mb-2">
                        <label class="form-label">Lote / Stock</label>
                        <select class="form-control" v-model="row.inventory_id">
                            <option value="">Selecione...</option>
                            <option v-for="item in availableOptionsFor(index)" :key="item.id" :value="item.id">
                                {{ inventoryLabel(item) }}
                            </option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label">Quantidade</label>
                        <input type="number" min="1" class="form-control" :max="stockFor(row.inventory_id)?.quantity || undefined" v-model.number="row.quantity">
                        <small class="text-muted" v-if="row.inventory_id">Disp.: {{ stockFor(row.inventory_id)?.quantity ?? 0 }}</small>
                    </div>
                    <div class="col-md-2 mb-2">
                        <button type="button" class="btn btn-outline-danger w-100" @click.prevent="removeAllocation(index)">Remover</button>
                    </div>
                </div>

                <div class="alert mb-3" :class="allocationValid ? 'alert-info' : 'alert-warning'">
                    <div><strong>Pedido:</strong> {{ eggsNeeded }} ovos</div>
                    <div><strong>Reservado:</strong> {{ allocatedTotal }} ovos</div>
                    <div>
                        <strong>Falta:</strong>
                        <span :class="remainingToAllocate === 0 ? 'text-success' : 'text-danger'">{{ remainingToAllocate }}</span>
                    </div>
                    <div class="small text-muted mt-1">
                        O stock será <strong>reservado</strong> (não expedido). A expedição usa estes lotes depois.
                    </div>
                </div>

                <button type="button" class="btn btn-primary" :disabled="loading || !allocationValid || inventory.length === 0" @click="confirmSeparation">
                    <span v-if="loading" class="spinner-border spinner-border-sm"></span>
                    <span v-else>Confirmar separação</span>
                </button>
            </div>
        </div>
    </div>

    <div v-else class="card">
        <div class="card-body text-center py-5">
            <div class="spinner-border" role="status"></div>
            <div class="mt-2">Carregando...</div>
        </div>
    </div>
</template>
