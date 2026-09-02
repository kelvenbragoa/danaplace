<script setup>

import { onMounted, ref } from 'vue';
import axios from 'axios';
import { useToastr } from '../../../../toastr';
import VueFeather from 'vue-feather';
import moment from 'moment';

const toastr = useToastr();
const loadingDiv = ref(true);
const orders = ref({ data: [] });

const getData = (page = 1) => {
    loadingDiv.value = true;

    axios.get('/admin/egg-orders/for-separation', { params: { page } })
        .then((response) => {
            orders.value = { data: response.data, total: response.data.length };
            loadingDiv.value = false;
        })
        .catch(() => {
            toastr.error('Erro ao carregar pedidos');
            loadingDiv.value = false;
        });
};

onMounted(() => {
    getData();
});

</script>

<template>
    <div v-if="!loadingDiv">
        <h1 class="h3 mb-3">Separação de Ovos</h1>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Pedidos aprovados aguardando separação ({{ orders.data?.length || 0 }})</h5>
                        <router-link to="/admin/pedidos" class="btn btn-pill btn-outline-primary mt-3">
                            <vue-feather type="list"></vue-feather> Ver todos os pedidos
                        </router-link>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Cliente</th>
                                        <th>Categoria</th>
                                        <th>Quantidade</th>
                                        <th>Data pedido</th>
                                        <th>Entrega prevista</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody v-if="orders.data?.length">
                                    <tr v-for="order in orders.data" :key="order.id">
                                        <td>#{{ order.id }}</td>
                                        <td>{{ order.customer_name }}</td>
                                        <td>{{ order.category?.name || '-' }}</td>
                                        <td>{{ order.quantity_dozens }} ovos</td>
                                        <td>{{ moment(order.order_date).format('DD-MM-YYYY') }}</td>
                                        <td>{{ order.expected_delivery_date ? moment(order.expected_delivery_date).format('DD-MM-YYYY') : '—' }}</td>
                                        <td>
                                            <router-link
                                                :to="'/admin/separacao-ovos/' + order.id + '/separar'"
                                                class="btn btn-sm btn-primary"
                                            >
                                                Separar stock
                                            </router-link>
                                        </td>
                                    </tr>
                                </tbody>
                                <tbody v-else>
                                    <tr>
                                        <td colspan="7" class="text-center">Nenhum pedido aprovado aguardando separação</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
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
