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
const categoryFilter = ref('');
const farmFilter = ref('');
const loadingDiv = ref(true);
const loadingButtonDelete = ref(false);

const retriviedData = ref({ data: [] });
const dataIdBeingDeleted = ref(null);
const categories = ref({});
const farms = ref([]);
const summary = ref({ total: 0, count: 0 });

const formatMoney = (value) => {
    return Number(value || 0).toLocaleString('pt-PT', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const getMeta = () => {
    return axios.get('/admin/egg-expenses/meta').then((response) => {
        categories.value = response.data.categories || {};
    });
};

const getFarms = () => {
    return axios.get('/admin/farms-all').then((response) => {
        farms.value = response.data;
    });
};

const getSummary = () => {
    axios.get('/admin/egg-expenses/summary', {
        params: {
            farm_id: farmFilter.value || undefined,
            category: categoryFilter.value || undefined,
        }
    }).then((response) => {
        summary.value = response.data;
    });
};

const getData = async (page = 1) => {
    axios.get(`/admin/egg-expenses?page=${page}`, {
        params: {
            query: searchQuery.value,
            category: categoryFilter.value || undefined,
            farm_id: farmFilter.value || undefined,
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

    axios.delete(`/admin/egg-expenses/${dataIdBeingDeleted.value}`)
        .then(() => {
            retriviedData.value.data = retriviedData.value.data.filter(data => data.id !== dataIdBeingDeleted.value);
            $('#deleteModal').modal('hide');
            toastr.success('Registro apagado com sucesso');
            getSummary();
            getData(retriviedData.value.current_page || 1);
        }).catch(() => {
            toastr.error('Erro ao apagar');
            $('#deleteModal').modal('hide');
        }).finally(() => {
            loadingButtonDelete.value = false;
        });
};

watch(searchQuery, debounce(() => {
    getData();
    getSummary();
}, 300));

watch([categoryFilter, farmFilter], () => {
    getData();
    getSummary();
});

onMounted(async () => {
    await Promise.all([getMeta(), getFarms()]);
    getSummary();
    getData();
});

</script>

<template>
    <div v-if="!loadingDiv">
        <h1 class="h3 mb-3">Despesas de Ovos</h1>

        <div class="row mb-3">
            <div class="col-md-4 mb-3">
                <div class="card border-primary h-100">
                    <div class="card-body">
                        <h6 class="text-primary">Total filtrado</h6>
                        <h3>{{ formatMoney(summary.total) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card border-info h-100">
                    <div class="card-body">
                        <h6 class="text-info">Registos</h6>
                        <h3>{{ summary.count ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Despesas da produção avícola. {{ retriviedData.total }} registros encontrados.</h5>

                        <router-link to="/admin/despesas-ovos/create" class="btn btn-pill btn-primary mt-3">
                            <vue-feather type="plus"></vue-feather>Adicionar
                        </router-link>
                        <router-link to="/admin/despesas-ovos/dashboard" class="btn btn-pill btn-info mt-3 ms-2">
                            <vue-feather type="bar-chart-2"></vue-feather>Dashboard
                        </router-link>

                        <br>

                        <div class="row mt-3">
                            <div class="col-md-4 mb-2">
                                <input type="text" class="form-control" v-model="searchQuery" placeholder="Procurar título, fornecedor ou fatura...">
                            </div>
                            <div class="col-md-4 mb-2">
                                <select class="form-control" v-model="categoryFilter">
                                    <option value="">Todas as categorias</option>
                                    <option v-for="(label, key) in categories" :key="key" :value="key">{{ label }}</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-2">
                                <select class="form-control" v-model="farmFilter">
                                    <option value="">Todas as granjas</option>
                                    <option v-for="farm in farms" :key="farm.id" :value="farm.id">{{ farm.name }}</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Data</th>
                                        <th>Título</th>
                                        <th>Categoria</th>
                                        <th>Valor</th>
                                        <th>Granja</th>
                                        <th>Lote</th>
                                        <th>Fornecedor</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody v-if="retriviedData.data.length > 0">
                                    <tr v-for="(actualData, index) in retriviedData.data" :key="actualData.id">
                                        <td>#{{ index + 1 }}</td>
                                        <td>{{ moment(actualData.expense_date).format('DD-MM-YYYY') }}</td>
                                        <td>{{ actualData.title }}</td>
                                        <td>{{ actualData.category_label }}</td>
                                        <td>{{ formatMoney(actualData.amount) }}</td>
                                        <td>{{ actualData.farm?.name || '-' }}</td>
                                        <td>{{ actualData.flock?.code || '-' }}</td>
                                        <td>{{ actualData.vendor_name || '-' }}</td>
                                        <td>
                                            <router-link :to="'/admin/despesas-ovos/' + actualData.id + '/edit'"><vue-feather type="edit-2"></vue-feather></router-link>
                                            <router-link :to="'/admin/despesas-ovos/' + actualData.id"><vue-feather type="eye"></vue-feather></router-link>
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
                        Esta despesa será removida permanentemente.
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
