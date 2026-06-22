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
const todayCount = ref(0);
const loadingDiv = ref(true);
const loadingButtonDelete = ref(false);

const retriviedData = ref({ data: [] });
const dataIdBeingDeleted = ref(null);

const getTodayCount = () => {
    axios.get('/admin/egg-shipping/today-shipping').then((response) => {
        todayCount.value = response.data.length;
    });
};

const getData = async (page = 1) => {
    axios.get(`/admin/egg-shipping?page=${page}`, {
        params: { query: searchQuery.value }
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

    axios.delete(`/admin/egg-shipping/${dataIdBeingDeleted.value}`)
        .then(() => {
            retriviedData.value.data = retriviedData.value.data.filter(data => data.id !== dataIdBeingDeleted.value);
            $('#deleteModal').modal('hide');
            toastr.success('Registro apagado com sucesso');
            getTodayCount();
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

onMounted(() => {
    getTodayCount();
    getData();
});

</script>

<template>
    <div v-if="!loadingDiv">
        <h1 class="h3 mb-3">Expedição de Ovos</h1>

        <div class="row mb-3" v-if="todayCount > 0">
            <div class="col-md-12">
                <div class="card border-info">
                    <div class="card-body">
                        <h6 class="text-info mb-2">Expedições de hoje</h6>
                        <p class="mb-0"><strong>{{ todayCount }}</strong> expedição(ões) registada(s) para hoje</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Tabela de expedições. {{ retriviedData.total }} registros encontrados.</h5>

                        <router-link to="/admin/expedicao-ovos/create" class="btn btn-pill btn-primary mt-3">
                            <vue-feather type="plus"></vue-feather>Adicionar
                        </router-link>

                        <br>

                        <form class="d-none d-sm-inline-block mt-3">
                            <div class="input-group input-group-navbar">
                                <input type="text" class="form-control" v-model="searchQuery" placeholder="Procurar fatura, transportadora, motorista..." aria-label="Search">
                            </div>
                        </form>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Fatura</th>
                                        <th>Cliente</th>
                                        <th>Data</th>
                                        <th>Transportadora</th>
                                        <th>Motorista</th>
                                        <th>Matrícula</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody v-if="retriviedData.data.length > 0">
                                    <tr v-for="(actualData, index) in retriviedData.data" :key="actualData.id">
                                        <td>#{{ index + 1 }}</td>
                                        <td>{{ actualData.invoice_number }}</td>
                                        <td>{{ actualData.order?.customer_name || '-' }}</td>
                                        <td>{{ moment(actualData.shipping_date).format('DD-MM-YYYY') }}</td>
                                        <td>{{ actualData.carrier }}</td>
                                        <td>{{ actualData.driver_name }}</td>
                                        <td>{{ actualData.vehicle_plate }}</td>
                                        <td>
                                            <router-link :to="'/admin/expedicao-ovos/' + actualData.id + '/edit'"><vue-feather type="edit-2"></vue-feather></router-link>
                                            <router-link :to="'/admin/expedicao-ovos/' + actualData.id"><vue-feather type="eye"></vue-feather></router-link>
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
                        Ao apagar esta expedição, o pedido e o estoque serão repostos ao estado anterior.
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
