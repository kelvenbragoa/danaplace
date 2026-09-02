<script setup>

import axios from 'axios';
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useToastr } from '../../../../toastr';
import VueFeather from 'vue-feather';
import moment from 'moment';

const retrievedData = ref({});
const loadingDiv = ref(true);
const loadingAction = ref(false);
const router = useRouter();
const toastr = useToastr();

const statusLabels = {
    pending: 'Pendente',
    approved: 'Aprovado',
    picked: 'Separado',
    shipped: 'Expedido',
    canceled: 'Cancelado',
};

const totalValue = computed(() => {
    if (!retrievedData.value.unit_price) return '-';
    return (retrievedData.value.quantity_dozens * retrievedData.value.unit_price).toFixed(2);
});

const getData = () => {
    axios.get(`/admin/egg-orders/${router.currentRoute.value.params.id}`)
        .then((response) => {
            loadingDiv.value = false;
            retrievedData.value = response.data;
        }).catch(() => {
            loadingDiv.value = false;
            toastr.error('Registo não encontrado');
            router.push({ path: '/admin/pedidos' });
        });
};

const runAction = (action) => {
    loadingAction.value = true;

    axios.post(`/admin/egg-orders/${retrievedData.value.id}/${action}`)
        .then((response) => {
            retrievedData.value = response.data;
            toastr.success('Estado atualizado com sucesso');
            getData();
        }).catch(() => {
            toastr.error('Erro ao atualizar pedido');
        }).finally(() => {
            loadingAction.value = false;
        });
};

onMounted(() => {
    getData();
});

</script>

<template>
    <div v-if="!loadingDiv">
        <h1 class="h3 mb-3">Pedido</h1>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Pedido #{{ retrievedData.id }} — {{ retrievedData.customer_name }}</h5>
                        <router-link to="/admin/pedidos" class="btn btn-pill btn-primary mt-3">
                            <vue-feather type="arrow-left"></vue-feather>Voltar
                        </router-link>
                        <router-link :to="'/admin/pedidos/' + retrievedData.id + '/edit'" class="btn btn-pill btn-secondary mt-3 ml-2">
                            <vue-feather type="edit-2"></vue-feather>Editar
                        </router-link>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Cliente</h6>
                                <p><strong>Nome:</strong> {{ retrievedData.customer_name }}</p>
                                <p><strong>NUIT:</strong> {{ retrievedData.customer_tax_id || '-' }}</p>
                                <p><strong>Email:</strong> {{ retrievedData.customer_email || '-' }}</p>
                                <p><strong>Telefone:</strong> {{ retrievedData.customer_phone || '-' }}</p>
                            </div>
                            <div class="col-md-6">
                                <h6>Pedido</h6>
                                <p><strong>Data:</strong> {{ moment(retrievedData.order_date).format('DD-MM-YYYY') }}</p>
                                <p><strong>Entrega Prevista:</strong> {{ moment(retrievedData.expected_delivery_date).format('DD-MM-YYYY') || '-' }}</p>
                                <p><strong>Categoria:</strong> {{ retrievedData.category?.name || '-' }}</p>
                                <p><strong>Quantidade:</strong> {{ retrievedData.quantity_dozens }}</p>
                                <p><strong>Preço Unitário:</strong> {{retrievedData.unit_price }}</p>
                                <p><strong>Total:</strong> {{ totalValue }}</p>
                                <p><strong>Estado:</strong> {{ statusLabels[retrievedData.status] || retrievedData.status }}</p>
                            </div>
                        </div>

                        <p v-if="retrievedData.observations"><strong>Observações:</strong> {{ retrievedData.observations }}</p>

                        <div v-if="retrievedData.items?.length" class="mt-3">
                            <h6>Stock reservado (separação)</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Stock</th>
                                            <th>Categoria</th>
                                            <th class="text-end">Qtd</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="item in retrievedData.items" :key="item.id">
                                            <td>#{{ item.inventory_id }}</td>
                                            <td>{{ item.inventory?.egg?.category?.name || '—' }}</td>
                                            <td class="text-end">{{ item.quantity }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <hr v-if="retrievedData.status !== 'shipped' && retrievedData.status !== 'canceled'">

                        <div class="mt-2" v-if="retrievedData.status === 'pending'">
                            <button class="btn btn-success mr-2" @click.prevent="runAction('approve')" :disabled="loadingAction">Aprovar</button>
                            <button class="btn btn-danger" @click.prevent="runAction('cancel')" :disabled="loadingAction">Cancelar</button>
                        </div>
                        <div class="mt-2" v-if="retrievedData.status === 'approved'">
                            <router-link :to="'/admin/separacao-ovos/' + retrievedData.id + '/separar'" class="btn btn-primary mr-2">
                                Separar stock
                            </router-link>
                            <button class="btn btn-danger" @click.prevent="runAction('cancel')" :disabled="loadingAction">Cancelar</button>
                        </div>
                        <div class="mt-2" v-if="retrievedData.status === 'picked'">
                            <button class="btn btn-danger" @click.prevent="runAction('cancel')" :disabled="loadingAction">Cancelar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div v-else>
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-center">
                    <div class="spinner-border" role="status"><span class="sr-only"></span></div>
                </div>
                <br>
                <div class="d-flex justify-content-center">Carregando Dados...</div>
            </div>
        </div>
    </div>
</template>
