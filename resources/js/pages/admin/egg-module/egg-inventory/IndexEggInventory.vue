<script setup>

import { onMounted, ref, watch } from 'vue';
import axios from 'axios';
import { useToastr } from '../../../../toastr';
import { debounce } from 'lodash';
import { Bootstrap4Pagination } from 'laravel-vue-pagination';
import VueFeather from 'vue-feather';

const toastr = useToastr();
const searchQuery = ref(null);
const statusFilter = ref('');
const stockAlerts = ref({});
const loadingDiv = ref(true);
const loadingButtonDelete = ref(false);

const retriviedData = ref({ data: [] });
const dataIdBeingDeleted = ref(null);

const statusLabels = {
    available: 'Disponível',
    reserved: 'Reservado',
    shipped: 'Expedido',
};

const statusBadgeClass = {
    available: 'badge-success',
    reserved: 'badge-warning',
    shipped: 'badge-secondary',
};

const lowStockCount = () => {
    if (!stockAlerts.value.low_stock) return 0;
    return Object.keys(stockAlerts.value.low_stock).length;
};

const expiringCount = () => stockAlerts.value.expiring_soon?.length || 0;

const getStockAlerts = () => {
    axios.get('/admin/egg-inventory/stock-alerts').then((response) => {
        stockAlerts.value = response.data;
    });
};

const getData = async (page = 1) => {
    axios.get(`/admin/egg-inventory?page=${page}`, {
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

    axios.delete(`/admin/egg-inventory/${dataIdBeingDeleted.value}`)
        .then(() => {
            retriviedData.value.data = retriviedData.value.data.filter(data => data.id !== dataIdBeingDeleted.value);
            $('#deleteModal').modal('hide');
            toastr.success('Registro apagado com sucesso');
            getStockAlerts();
        }).catch(() => {
            toastr.error('Erro ao apagar');
            $('#deleteModal').modal('hide');
        }).finally(() => {
            loadingButtonDelete.value = false;
        });
};

watch(searchQuery, debounce(() => {
    getData();
}, 300));

watch(statusFilter, () => {
    getData();
});

onMounted(() => {
    getStockAlerts();
    getData();
});

</script>

<template>
    <div v-if="!loadingDiv">
        <h1 class="h3 mb-3">Estoque de Ovos</h1>

        <div class="row mb-3" v-if="lowStockCount() > 0 || expiringCount() > 0">
            <div class="col-md-6" v-if="lowStockCount() > 0">
                <div class="card border-warning">
                    <div class="card-body">
                        <h6 class="text-warning mb-2">Stock baixo</h6>
                        <p class="mb-0"><strong>{{ lowStockCount() }}</strong> categoria(s) com menos de 1000 ovos</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6" v-if="expiringCount() > 0">
                <div class="card border-danger">
                    <div class="card-body">
                        <h6 class="text-danger mb-2">Ovos antigos</h6>
                        <p class="mb-0"><strong>{{ expiringCount() }}</strong> registo(s) com mais de 21 dias</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Tabela de estoque. {{ retriviedData.total }} registros encontrados.</h5>

                        <router-link to="/admin/estoque-ovos/create" class="btn btn-pill btn-primary mt-3">
                            <vue-feather type="plus"></vue-feather>Adicionar
                        </router-link>

                        <br>

                        <form class="d-none d-sm-inline-block mt-3">
                            <div class="input-group input-group-navbar">
                                <input type="text" class="form-control" v-model="searchQuery" placeholder="Procurar localização, rastreio ou galpão..." aria-label="Search">
                            </div>
                        </form>

                        <select class="form-control mt-3" style="max-width: 220px;" v-model="statusFilter">
                            <option value="">Todos os estados</option>
                            <option value="available">Disponível</option>
                            <option value="reserved">Reservado</option>
                            <option value="shipped">Expedido</option>
                        </select>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Rastreio</th>
                                        <th>Categoria</th>
                                        <th>Galpão</th>
                                        <th>Quantidade</th>
                                        <th>Entrada</th>
                                        <th>Localização</th>
                                        <th>Estado</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody v-if="retriviedData.data.length > 0">
                                    <tr v-for="(actualData, index) in retriviedData.data" :key="actualData.id">
                                        <td>#{{ index + 1 }}</td>
                                        <td>{{ actualData.egg?.traceability_code || '-' }}</td>
                                        <td>{{ actualData.egg?.category?.name || '-' }}</td>
                                        <td>{{ actualData.house?.name || '-' }}</td>
                                        <td>{{ actualData.quantity }}</td>
                                        <td>{{ actualData.entry_date }}</td>
                                        <td>{{ actualData.location || '-' }}</td>
                                        <td>
                                            <span class="badge" :class="statusBadgeClass[actualData.status] || 'badge-light'">
                                                {{ statusLabels[actualData.status] || actualData.status }}
                                            </span>
                                        </td>
                                        <td>
                                            <router-link :to="'/admin/estoque-ovos/' + actualData.id + '/edit'"><vue-feather type="edit-2"></vue-feather></router-link>
                                            <router-link :to="'/admin/estoque-ovos/' + actualData.id"><vue-feather type="eye"></vue-feather></router-link>
                                            <a href="#" @click.prevent="confirmDeletion(actualData)"><vue-feather type="trash"></vue-feather></a>
                                        </td>
                                    </tr>
                                </tbody>
                                <tbody v-else>
                                    <tr>
                                        <td colspan="9" align="center">Nenhum resultado encontrado</td>
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
                        Ao apagar este registo de estoque, a informação de rastreabilidade pode ser afetada.
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
                    <div class="spinner-border" role="status">
                        <span class="sr-only"></span>
                    </div>
                </div>
                <br>
                <div class="d-flex justify-content-center">Carregando Dados...</div>
            </div>
        </div>
    </div>
</template>
