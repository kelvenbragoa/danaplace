<script setup>

import { onMounted, ref, watch } from 'vue';
import axios from 'axios';
import { useToastr } from '../../../../toastr';
import { debounce } from 'lodash';
import { Bootstrap4Pagination } from 'laravel-vue-pagination';
import VueFeather from 'vue-feather';
import moment from 'moment';

const toastr = useToastr();
const searchQuery = ref(null);
const statusFilter = ref('');
const pendingCount = ref(0);
const loadingDiv = ref(true);
const loadingButtonDelete = ref(false);

const retriviedData = ref({ data: [] });
const dataIdBeingDeleted = ref(null);

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

const getPendingCount = () => {
    axios.get('/admin/egg-orders/pending-orders').then((response) => {
        pendingCount.value = response.data.length;
    });
};

const getData = async (page = 1) => {
    axios.get(`/admin/egg-orders?page=${page}`, {
        params: {
            query: searchQuery.value,
            status: statusFilter.value || undefined,
        }
    }).then((response) => {
        retriviedData.value = response.data;
        loadingDiv.value = false;
    });
};

const confirmDeletion = (data) => {
    dataIdBeingDeleted.value = data.id;
    $('#deleteModal').modal('show');
};

const deleteData = () => {
    loadingButtonDelete.value = true;

    axios.delete(`/admin/egg-orders/${dataIdBeingDeleted.value}`)
        .then(() => {
            retriviedData.value.data = retriviedData.value.data.filter(data => data.id !== dataIdBeingDeleted.value);
            $('#deleteModal').modal('hide');
            toastr.success('Registro apagado com sucesso');
            getPendingCount();
        }).catch(() => {
            toastr.error('Erro ao apagar');
            $('#deleteModal').modal('hide');
        }).finally(() => {
            loadingButtonDelete.value = false;
        });
};

const formatTotal = (order) => {
    if (!order.unit_price) return '-';
    return (order.quantity_dozens * order.unit_price).toFixed(2);
};

watch(searchQuery, debounce(() => {
    getData();
}, 300));

watch(statusFilter, () => {
    getData();
});

onMounted(() => {
    getPendingCount();
    getData();
});

</script>

<template>
    <div v-if="!loadingDiv">
        <h1 class="h3 mb-3">Pedidos</h1>

        <div class="row mb-3" v-if="pendingCount > 0">
            <div class="col-md-12">
                <div class="card border-info">
                    <div class="card-body">
                        <h6 class="text-info mb-2">Pedidos em aberto</h6>
                        <p class="mb-0"><strong>{{ pendingCount }}</strong> pedido(s) pendente(s), aprovado(s) ou em separação</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Tabela de pedidos. {{ retriviedData.total }} registros encontrados.</h5>

                        <router-link to="/admin/pedidos/create" class="btn btn-pill btn-primary mt-3">
                            <vue-feather type="plus"></vue-feather>Adicionar
                        </router-link>

                        <br>

                        <form class="d-none d-sm-inline-block mt-3">
                            <div class="input-group input-group-navbar">
                                <input type="text" class="form-control" v-model="searchQuery" placeholder="Procurar cliente, email ou telefone..." aria-label="Search">
                            </div>
                        </form>

                        <select class="form-control mt-3" style="max-width: 220px;" v-model="statusFilter">
                            <option value="">Todos os estados</option>
                            <option value="pending">Pendente</option>
                            <option value="approved">Aprovado</option>
                            <option value="picked">Separado</option>
                            <option value="shipped">Expedido</option>
                            <option value="canceled">Cancelado</option>
                        </select>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Cliente</th>
                                        <th>Data</th>
                                        <th>Categoria</th>
                                        <th>Quantidade</th>
                                        <th>Total</th>
                                        <th>Estado</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody v-if="retriviedData.data.length > 0">
                                    <tr v-for="(actualData, index) in retriviedData.data" :key="actualData.id">
                                        <td>#{{ index + 1 }}</td>
                                        <td>{{ actualData.customer_name }}</td>
                                        <td>{{ moment(actualData.order_date).format('DD-MM-YYYY') }}</td>
                                        <td>{{ actualData.category?.name || '-' }}</td>
                                        <td>{{ actualData.quantity_dozens }}</td>
                                        <td>{{ formatTotal(actualData) }}</td>
                                        <td>
                                            <span class="badge" :class="statusBadgeClass[actualData.status] || 'badge-light'">
                                                {{ statusLabels[actualData.status] || actualData.status }}
                                            </span>
                                        </td>
                                        <td>
                                            <router-link :to="'/admin/pedidos/' + actualData.id + '/edit'"><vue-feather type="edit-2"></vue-feather></router-link>
                                            <router-link :to="'/admin/pedidos/' + actualData.id"><vue-feather type="eye"></vue-feather></router-link>
                                            <a href="#" @click.prevent="confirmDeletion(actualData)"><vue-feather type="trash"></vue-feather></a>
                                        </td>
                                    </tr>
                                </tbody>
                                <tbody v-else>
                                    <tr>
                                        <td colspan="8" align="center">Nenhum resultado encontrado</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <Bootstrap4Pagination :data="retriviedData" @pagination-change-page="getData"/>
            </div>
        </div>

        <div class="modal" id="deleteModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Deseja mesmo eliminar este item.</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        Ao apagar este pedido, a expedição relacionada pode ser afetada.
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                        <button @click.prevent="deleteData" type="button" class="btn btn-danger" :disabled="loadingButtonDelete">
                            <div v-if="loadingButtonDelete" class="spinner-border spinner-border-sm" role="status"></div>
                            <span v-else>Apagar registro</span>
                        </button>
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
